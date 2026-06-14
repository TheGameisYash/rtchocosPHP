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

// Read JSON input or fallback to POST variables
$input = json_decode(file_get_contents('php://input'), true);
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
    require_once __DIR__ . '/includes/db.php';
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT IGNORE INTO subscribers (email, created_at) VALUES (?, NOW())');
    $stmt->execute([$email]);
} catch (Exception $e) {
    // Log failure but let execution continue for CSV backup fallback
    error_log("Database subscriber insert failure: " . $e->getMessage());
}

// 2. SAVE LOCALLY (Fail-safe backup database on Hostinger server)
$dir = __DIR__ . '/data';
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
        fputcsv($file, ['Date Added', 'Email Address']);
    }
    fputcsv($file, [date('Y-m-d H:i:s'), $email]);
    fclose($file);
}

// 2. THIRD-PARTY NEWSLETTER API SYNC (Optional: MailerLite / Mailchimp Setup)
/*
// MAILERLITE SYNC (Reads from .env):
$apiKey = getenv('MAILERLITE_API_KEY') ?: ($_ENV['MAILERLITE_API_KEY'] ?? '');
$groupId = getenv('MAILERLITE_GROUP_ID') ?: ($_ENV['MAILERLITE_GROUP_ID'] ?? ''); // Optional

if (!empty($apiKey)) {
    $xhr = curl_init('https://connect.mailerlite.com/api/v1/subscribers');
    $payload = json_encode([
        'email' => $email,
        'groups' => !empty($groupId) ? [$groupId] : []
    ]);

    curl_setopt($xhr, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($xhr, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($xhr, CURLOPT_POST, true);
    curl_setopt($xhr, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($xhr);
    $httpStatus = curl_getinfo($xhr, CURLINFO_HTTP_CODE);
    curl_close($xhr);
}
*/

echo json_encode([
    "status" => "success", 
    "message" => "Thank you for subscribing! Your email has been registered."
]);
exit;
