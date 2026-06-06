<?php
header('Content-Type: application/json');

// Load secure environment variables from .env
require_once __DIR__ . '/includes/env_loader.php';

// --- SMTP CONFIGURATION (Loaded dynamically from .env) ----------
define('SMTP_ENABLED', filter_var(getenv('SMTP_ENABLED') ?: ($_ENV['SMTP_ENABLED'] ?? false), FILTER_VALIDATE_BOOLEAN));
define('SMTP_HOST', getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com'));
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 465)));
define('SMTP_USER', getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? 'hello@rtchocos.com'));
define('SMTP_PASS', getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? ''));
// ----------------------------------------------------------------

// Prevent direct access via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    exit;
}

// Read JSON input or fallback to POST variables
$input = json_decode(file_get_contents('php://input'), true);

$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$subject = isset($input['subject']) ? trim($input['subject']) : '';
$message = isset($input['message']) ? trim($input['message']) : '';

if (empty($name) && empty($email)) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
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

// 1. SAVE LOCALLY (Fail-safe backup database on Hostinger server)
$dir = __DIR__ . '/data';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Create secure .htaccess inside data folder to prevent public file browser reads
$htaccessFile = $dir . '/.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, "Deny from all");
}

$csvFile = $dir . '/contact_messages.csv';
$fileExisted = file_exists($csvFile);

$file = fopen($csvFile, 'a');
if ($file) {
    if (!$fileExisted) {
        fputcsv($file, ['Date Received', 'Name', 'Email Address', 'Phone Number', 'Subject', 'Message Details']);
    }
    fputcsv($file, [date('Y-m-d H:i:s'), $name, $email, $phone, $subject, $message]);
    fclose($file);
}

// 2. OUTGOING EMAIL ROUTER
$to = "hello@rtchocos.com";
$email_subject = "New Contact Form Submission: " . $subject;

$email_content = "You have received a new message from the contact form on RT Chocos.\n\n";
$email_content .= "Submission Details:\n";
$email_content .= "----------------------------------------\n";
$email_content .= "Name:     " . $name . "\n";
$email_content .= "Email:    " . $email . "\n";
$email_content .= "Phone:    " . ($phone ? $phone : 'Not provided') . "\n";
$email_content .= "Subject:  " . $subject . "\n";
$email_content .= "Date:     " . date('Y-m-d H:i:s') . "\n";
$email_content .= "----------------------------------------\n\n";
$email_content .= "Message Body:\n" . $message . "\n";

$mail_sent = false;

if (SMTP_ENABLED) {
    $mail_sent = send_smtp_email(
        $to, 
        $email_subject, 
        $email_content, 
        SMTP_USER, 
        SMTP_HOST, 
        SMTP_PORT, 
        SMTP_USER, 
        SMTP_PASS
    );
} else {
    // Fallback to native PHP mail if SMTP is not configured/enabled
    $headers = "From: " . SMTP_USER . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $mail_sent = @mail($to, $email_subject, $email_content, $headers);
}

if ($mail_sent) {
    echo json_encode([
        "status" => "success",
        "message" => "Your message has been sent successfully. We'll be in touch soon!"
    ]);
} else {
    // If mail sending failed, return success since we successfully stored it in the CSV backup!
    echo json_encode([
        "status" => "success",
        "message" => "Your message was recorded successfully. We'll be in touch soon!"
    ]);
}

// --- SECURE SOCKET-BASED SMTP CLIENT FUNCTION -------------------
function send_smtp_email($to, $subject, $body, $from, $host, $port, $user, $pass) {
    $timeout = 15;
    $socket_prefix = ($port == 465) ? 'ssl://' : '';
    
    $sh = @fsockopen($socket_prefix . $host, $port, $errno, $errstr, $timeout);
    if (!$sh) {
        return false;
    }

    $get_lines = function($socket) {
        $data = "";
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    };

    $get_lines($sh);
    fwrite($sh, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n");
    $get_lines($sh);
    
    fwrite($sh, "AUTH LOGIN\r\n");
    $get_lines($sh);
    
    fwrite($sh, base64_encode($user) . "\r\n");
    $get_lines($sh);
    
    fwrite($sh, base64_encode($pass) . "\r\n");
    $get_lines($sh);
    
    fwrite($sh, "MAIL FROM:<" . $from . ">\r\n");
    $get_lines($sh);
    
    fwrite($sh, "RCPT TO:<" . $to . ">\r\n");
    $get_lines($sh);
    
    fwrite($sh, "DATA\r\n");
    $get_lines($sh);
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: <" . $from . ">\r\n";
    $headers .= "To: <" . $to . ">\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    fwrite($sh, $headers . "\r\n" . $body . "\r\n.\r\n");
    $get_lines($sh);
    
    fwrite($sh, "QUIT\r\n");
    $get_lines($sh);
    
    fclose($sh);
    return true;
}
exit;
