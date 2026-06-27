<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Load secure environment variables from .env
require_once __DIR__ . '/includes/env_loader.php';

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

// Honeypot check
$honeypot = isset($input['nickname']) ? trim($input['nickname']) : '';
if (empty($honeypot)) {
    $honeypot = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
}
if (!empty($honeypot)) {
    // Silent discard/fail
    echo json_encode(["status" => "success", "message" => "Your message has been sent successfully. We'll be in touch soon!"]);
    exit;
}

// Session-based rate limiting (max 5 per hour)
$now = time();
if (!isset($_SESSION['last_contact_times'])) {
    $_SESSION['last_contact_times'] = [];
}
$_SESSION['last_contact_times'] = array_filter($_SESSION['last_contact_times'], function($t) use ($now) {
    return ($now - $t) < 3600;
});
if (count($_SESSION['last_contact_times']) >= 5) {
    http_response_code(429);
    echo json_encode(["status" => "error", "message" => "Too many message attempts. Please try again in an hour."]);
    exit;
}
$_SESSION['last_contact_times'][] = $now;

$name    = isset($input['name'])    ? trim($input['name'])    : '';
$email   = isset($input['email'])   ? trim($input['email'])   : '';
$phone   = isset($input['phone'])   ? trim($input['phone'])   : '';
$subject = isset($input['subject']) ? trim($input['subject']) : '';
$message = isset($input['message']) ? trim($input['message']) : '';

if (empty($name) && empty($email)) {
    $name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
    $email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
    $phone   = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
}

// Validate inputs
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please fill out all required fields."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Please enter a valid email address."]);
    exit;
}

// 1. SAVE TO DATABASE (Primary)
try {
    require_once __DIR__ . '/includes/db.php';
    $pdo = get_db();
    $stmt = $pdo->prepare(
        'INSERT INTO contacts (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([$name, $email, $phone ?: null, $subject ?: null, $message]);
} catch (Exception $e) {
    // Log but continue — CSV backup will still save
    error_log("Database contact insert failure: " . $e->getMessage());
}

// 2. SAVE LOCALLY (Fail-safe CSV backup)
$dir = __DIR__ . '/data';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Secure .htaccess inside data folder
$htaccessFile = $dir . '/.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, "Order Deny,Allow\nDeny from all");
}

$csvFile    = $dir . '/contact_messages.csv';
$fileExisted = file_exists($csvFile);

$file = fopen($csvFile, 'a');
if ($file) {
    if (!$fileExisted) {
        fputcsv($file, ['Date Received', 'Name', 'Email Address', 'Phone Number', 'Subject', 'Message Details']);
    }
    fputcsv($file, [date('Y-m-d H:i:s'), $name, $email, $phone, $subject, $message]);
    fclose($file);
}

// Always return success — message is stored
echo json_encode([
    "status"  => "success",
    "message" => "Your message has been sent successfully. We'll be in touch soon!"
]);
exit;
