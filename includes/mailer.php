<?php
// includes/mailer.php - Email Notification Service for RT Chocos

$rootDir = dirname(__DIR__);
require_once $rootDir . '/includes/env_loader.php';

if (!function_exists('rt_get_env')) {
    function rt_get_env($key, $default = null) {
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        return $default;
    }
}

/**
 * Sends an email notification to the site administrator when a contact message is received.
 *
 * @param string $name
 * @param string $email
 * @param string $phone
 * @param string $subject
 * @param string $message
 * @return bool
 */
function send_contact_notification($name, $email, $phone, $subject, $message) {
    $recipientEmail = rt_get_env('NOTIFICATION_EMAIL', rt_get_env('SMTP_USER', 'hello@rtchocos.com'));
    $smtpHost       = rt_get_env('SMTP_HOST', 'smtp.hostinger.com');
    $smtpPort       = rt_get_env('SMTP_PORT', '465');
    $smtpUser       = rt_get_env('SMTP_USER', 'hello@rtchocos.com');
    $smtpPass       = rt_get_env('SMTP_PASS', 'Admin@rtchocos1');
    $rawSmtpEnabled = rt_get_env('SMTP_ENABLED');
    $smtpEnabled    = ($rawSmtpEnabled === null || $rawSmtpEnabled === '') ? true : filter_var($rawSmtpEnabled, FILTER_VALIDATE_BOOLEAN);

    $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePhone   = htmlspecialchars($phone ?: 'N/A', ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject ?: 'General Inquiry', ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $dateStr     = date('Y-m-d H:i:s T');

    $emailSubject = "New Website Inquiry: " . ($subject ? $subject : "Message from $name");

    // HTML Email Template
    $bodyHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Website Inquiry</title>
</head>
<body style="margin:0; padding:0; background-color:#FAF7F2; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#2C1810;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAF7F2; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border:1px solid #E8DFD8;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#2C1810; padding: 30px; text-align: center; border-bottom: 3px solid #D4AF37;">
                            <h1 style="color:#F4ECE1; margin:0; font-size: 24px; font-weight:600; letter-spacing:1px;">RT CHOCOS</h1>
                            <p style="color:#D4AF37; margin:6px 0 0 0; font-size: 13px; text-transform:uppercase; letter-spacing:2px;">New Contact Notification</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="margin:0 0 20px 0; font-size:16px; color:#2C1810; font-weight:600;">
                                You have received a new inquiry from the website contact form:
                            </p>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FDFBF7; border-radius:8px; padding:20px; border:1px solid #EFEAE4; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; width:110px; font-weight:600;">Date:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;">{$dateStr}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Sender:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810; font-weight:600;">{$safeName}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Email:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;"><a href="mailto:{$safeEmail}" style="color:#8B4513; text-decoration:underline;">{$safeEmail}</a></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Phone:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;">{$safePhone}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Subject:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810; font-weight:600;">{$safeSubject}</td>
                                </tr>
                            </table>

                            <div style="background-color:#FFFFFF; border-left:4px solid #D4AF37; padding:18px 20px; margin-bottom:28px; border-radius:0 8px 8px 0; background-color:#FFFDF8; border-top:1px solid #F3EDE7; border-right:1px solid #F3EDE7; border-bottom:1px solid #F3EDE7;">
                                <p style="margin:0 0 8px 0; font-size:13px; color:#6B5347; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Message Content:</p>
                                <p style="margin:0; font-size:15px; line-height:1.7; color:#2C1810; white-space:pre-wrap;">{$safeMessage}</p>
                            </div>

                            <div style="text-align: center;">
                                <a href="mailto:{$safeEmail}?subject=Re: {$safeSubject}" style="display:inline-block; background-color:#2C1810; color:#F4ECE1; text-decoration:none; padding:14px 28px; border-radius:6px; font-weight:600; font-size:14px; border:1px solid #D4AF37;">Reply to {$safeName}</a>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#F5EFE8; padding: 20px 30px; text-align: center; font-size: 12px; color: #8C766B; border-top:1px solid #E8DFD8;">
                            This automated message was sent from your website RT Chocos (<a href="https://www.rtchocos.com" style="color:#8B4513;">rtchocos.com</a>).
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    // Attempt 1: Direct SSL Socket SMTP implementation (Primary when SMTP_ENABLED is true)
    if ($smtpEnabled && !empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ]);

            $socket = @stream_socket_client("ssl://{$smtpHost}:{$smtpPort}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
            if ($socket) {
                $read = function($s) {
                    $res = "";
                    while ($str = fgets($s, 515)) {
                        $res .= $str;
                        if (substr($str, 3, 1) == " ") break;
                    }
                    return $res;
                };

                $read($socket);
                fputs($socket, "EHLO " . gethostname() . "\r\n"); $read($socket);
                fputs($socket, "AUTH LOGIN\r\n"); $read($socket);
                fputs($socket, base64_encode($smtpUser) . "\r\n"); $read($socket);
                fputs($socket, base64_encode($smtpPass) . "\r\n"); $read($socket);
                fputs($socket, "MAIL FROM: <{$smtpUser}>\r\n"); $read($socket);
                fputs($socket, "RCPT TO: <{$recipientEmail}>\r\n"); $read($socket);
                fputs($socket, "DATA\r\n"); $read($socket);

                $msgId = "<" . time() . "." . md5(uniqid(microtime(), true)) . "@rtchocos.com>";
                $data  = "Date: " . date('r') . "\r\n";
                $data .= "Message-ID: {$msgId}\r\n";
                $data .= "Subject: {$emailSubject}\r\n";
                $data .= "To: {$recipientEmail}\r\n";
                $data .= "From: RT Chocos Website <{$smtpUser}>\r\n";
                $data .= "Reply-To: {$name} <{$email}>\r\n";
                $data .= "MIME-Version: 1.0\r\n";
                $data .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
                $data .= $bodyHtml . "\r\n.\r\n";

                fputs($socket, $data); $read($socket);
                fputs($socket, "QUIT\r\n"); fclose($socket);

                error_log("Contact notification email sent via Hostinger SMTP socket to $recipientEmail");
                return true;
            }
        } catch (Exception $e) {
            error_log("SMTP socket exception: " . $e->getMessage());
        }
    }

    // Attempt 2: Standard PHP mail() (Fallback for native mail servers)
    try {
        $msgId = "<" . time() . "." . md5(uniqid(microtime(), true)) . "@rtchocos.com>";
        $headers  = "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: {$msgId}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: RT Chocos Website <$smtpUser>\r\n";
        $headers .= "Reply-To: $name <$email>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        if (@mail($recipientEmail, $emailSubject, $bodyHtml, $headers, "-f " . $smtpUser)) {
            error_log("Contact notification email sent via mail() to $recipientEmail");
            return true;
        }
    } catch (Exception $e) {
        error_log("mail() exception: " . $e->getMessage());
    }

    error_log("Contact notification email dispatch finished (logged for fallback)");
    return false;
}
?>
