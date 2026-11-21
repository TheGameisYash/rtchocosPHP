<?php
// api/subscribe.php - Newsletter subscription processor
error_reporting(E_ALL & ~E_DEPRECATED);
ob_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$rootDir = dirname(__DIR__);
require_once $rootDir . '/includes/env_loader.php';

// Prevent direct access via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Read JSON input or fallback to POST variables
$input = json_decode(file_get_contents('php://input'), true);

$source = !empty($input['source']) ? trim($input['source']) : (!empty($_POST['source']) ? trim($_POST['source']) : 'Website Footer Newsletter');
$workshopTitle = !empty($input['workshop_title']) ? trim($input['workshop_title']) : (!empty($_POST['workshop_title']) ? trim($_POST['workshop_title']) : '');

// Honeypot check
$honeypot = isset($input['nickname']) ? trim($input['nickname']) : '';
if (empty($honeypot)) {
    $honeypot = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
}
if (!empty($honeypot)) {
    // Silent discard/fail to fool bots
    echo json_encode(["status" => "success", "message" => "Thank you for subscribing! Your email has been registered."]);
    exit;
}

// Session-based rate limiting (max 5 per hour)
$now = time();
if (!isset($_SESSION['last_sub_times'])) {
    $_SESSION['last_sub_times'] = [];
}
$_SESSION['last_sub_times'] = array_filter($_SESSION['last_sub_times'], function($t) use ($now) {
    return ($now - $t) < 3600;
});
if (count($_SESSION['last_sub_times']) >= 5) {
    http_response_code(429);
    echo json_encode(["status" => "error", "message" => "Too many subscription attempts. Please try again in an hour."]);
    exit;
}
$_SESSION['last_sub_times'][] = $now;
$email = isset($input['email']) ? trim($input['email']) : '';

if (empty($email)) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
}

if (empty($email)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Email address is required."]);
    exit;
}

$email = filter_var($email, FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit;
}

// 1. SAVE TO DATABASE (Primary)
try {
    require_once $rootDir . '/includes/db.php';
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT IGNORE INTO subscribers (email, created_at) VALUES (?, NOW())');
    $stmt->execute([$email]);
} catch (Exception $e) {
    // Log failure but let execution continue for CSV backup fallback
    error_log("Database subscriber insert failure: " . $e->getMessage());
}

// 2. SAVE LOCALLY (Fail-safe backup database on Hostinger server)
$dir = $rootDir . '/data';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Create secure .htaccess inside data folder to prevent public file browser reads
$htaccessFile = $dir . '/.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, "Deny from all");
}

$csvFile = $dir . '/subscribers.csv';
$fileExisted = file_exists($csvFile);

$file = fopen($csvFile, 'a');
if ($file) {
    if (!$fileExisted) {
        fputcsv($file, ['Date Added', 'Email Address', 'Source'], ',', '"', "\\");
    }
    fputcsv($file, [date('Y-m-d H:i:s'), $email, $source], ',', '"', "\\");
    fclose($file);
}

// 3. SEND INSTANT EMAIL NOTIFICATION TO ADMIN
require_once $rootDir . '/includes/mailer.php';
try {
    send_subscription_notification($email, $source);
} catch (Exception $e) {
    error_log("Subscription notification error: " . $e->getMessage());
}

if (ob_get_length()) {
    ob_clean();
}

$isWorkshop = stripos($source, 'Workshop Waitlist') !== false;
$successMsg = $isWorkshop
    ? ($workshopTitle 
        ? "You're on the priority waitlist! We'll notify you as soon as registrations open for {$workshopTitle}."
        : "You're on the priority waitlist! We'll notify you as soon as this workshop opens.")
    : "Thank you for subscribing! Your email has been registered.";

echo json_encode([
    "status" => "success", 
    "message" => $successMsg
]);
exit;
?>
