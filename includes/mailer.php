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
 * Retrieves the list of admin recipient email addresses.
 * Prioritizes DB setting (site_settings.notification_email), then .env NOTIFICATION_EMAIL, then fallback defaults.
 *
 * @return array
 */
function get_notification_recipients() {
    $rawRecipient = null;

    // Check DB site_settings first if available
    if (function_exists('get_site_setting')) {
        $dbVal = get_site_setting('notification_email');
        if (!empty($dbVal)) {
            $rawRecipient = $dbVal;
        }
    }

    if (empty($rawRecipient)) {
        $rawRecipient = rt_get_env('NOTIFICATION_EMAIL', 'hello@rtchocos.com, Rtchocos@gmail.com, yash237yash@gmail.com');
    }

    $recipients = array_filter(array_map('trim', explode(',', $rawRecipient)));
    if (empty($recipients)) {
        $recipients = ['hello@rtchocos.com', 'Rtchocos@gmail.com', 'yash237yash@gmail.com'];
    }

    return array_values($recipients);
}

/**
 * Dispatches an HTML email via direct SSL socket SMTP (Hostinger) with standard mail() fallback.
 *
 * @param array|string $recipients
 * @param string $emailSubject
 * @param string $bodyHtml
 * @param string $replyToName
 * @param string $replyToEmail
 * @return bool
 */
function send_rt_email($recipients, $emailSubject, $bodyHtml, $replyToName = 'RT Chocos', $replyToEmail = '') {
    if (!is_array($recipients)) {
        $recipients = array_filter(array_map('trim', explode(',', $recipients)));
    }
    if (empty($recipients)) {
        $recipients = get_notification_recipients();
    }
    $recipientString = implode(', ', $recipients);

    $smtpHost       = rt_get_env('SMTP_HOST', 'smtp.hostinger.com');
    $smtpPort       = rt_get_env('SMTP_PORT', '465');
    $smtpUser       = rt_get_env('SMTP_USER', 'hello@rtchocos.com');
    $smtpPass       = rt_get_env('SMTP_PASS', 'Admin@rtchocos1');
    $rawSmtpEnabled = rt_get_env('SMTP_ENABLED');
    $smtpEnabled    = ($rawSmtpEnabled === null || $rawSmtpEnabled === '') ? true : filter_var($rawSmtpEnabled, FILTER_VALIDATE_BOOLEAN);

    if (empty($replyToEmail)) {
        $replyToEmail = $smtpUser;
    }

    // Attempt 1: Direct SSL Socket SMTP implementation (Hostinger Port 465)
    if ($smtpEnabled && !empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ]);

            $socket = @stream_socket_client("ssl://{$smtpHost}:{$smtpPort}", $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $context);
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

                foreach ($recipients as $rcpt) {
                    fputs($socket, "RCPT TO: <{$rcpt}>\r\n"); $read($socket);
                }

                fputs($socket, "DATA\r\n"); $read($socket);

                $msgId = "<" . time() . "." . md5(uniqid(microtime(), true)) . "@rtchocos.com>";
                $data  = "Date: " . date('r') . "\r\n";
                $data .= "Message-ID: {$msgId}\r\n";
                $data .= "Subject: {$emailSubject}\r\n";
                $data .= "To: {$recipientString}\r\n";
                $data .= "From: RT Chocos Website <{$smtpUser}>\r\n";
                $data .= "Reply-To: {$replyToName} <{$replyToEmail}>\r\n";
                $data .= "MIME-Version: 1.0\r\n";
                $data .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
                $data .= $bodyHtml . "\r\n.\r\n";

                fputs($socket, $data); $read($socket);
                fputs($socket, "QUIT\r\n"); fclose($socket);

                error_log("Email sent via Hostinger SMTP socket to $recipientString (Subject: $emailSubject)");
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
        $headers .= "Reply-To: $replyToName <$replyToEmail>\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        if (@mail($recipientString, $emailSubject, $bodyHtml, $headers, "-f " . $smtpUser)) {
            error_log("Email sent via mail() fallback to $recipientString");
            return true;
        }
    } catch (Exception $e) {
        error_log("mail() fallback exception: " . $e->getMessage());
    }

    error_log("Email dispatch failed for $recipientString (Subject: $emailSubject)");
    return false;
}

/**
 * Sends an email notification to the site administrator when a contact message or enquiry is received.
 *
 * @param string $name
 * @param string $email
 * @param string $phone
 * @param string $subject
 * @param string $message
 * @param string $formType (e.g. 'Website Contact Form', 'Shop Bulk Enquiry', 'Work With Us Consultation')
 * @return bool
 */
function send_contact_notification($name, $email, $phone, $subject, $message, $formType = 'Website Contact Form') {
    $recipients  = get_notification_recipients();
    $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePhone   = htmlspecialchars($phone ?: 'N/A', ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject ?: 'General Inquiry', ENT_QUOTES, 'UTF-8');
    $safeType    = htmlspecialchars($formType, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $dateStr     = date('d M Y, h:i A T');

    $emailSubject = "[RT Chocos] {$safeType}: " . ($safeSubject ?: "New message from {$safeName}");

    // Luxury HTML Email Template
    $bodyHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$safeType}</title>
</head>
<body style="margin:0; padding:0; background-color:#FAF7F2; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#2C1810;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAF7F2; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="620" border="0" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border:1px solid #E8DFD8;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#2C1810; padding: 28px 30px; text-align: center; border-bottom: 3px solid #D4AF37;">
                            <h1 style="color:#F4ECE1; margin:0; font-size: 24px; font-weight:700; letter-spacing:2px; font-family:'Playfair Display', Georgia, serif;">RT CHOCOS</h1>
                            <p style="color:#D4AF37; margin:6px 0 0 0; font-size: 12.5px; text-transform:uppercase; letter-spacing:2px; font-weight:600;">{$safeType}</p>
                        </td>
                    </tr>
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 30px;">
                            <p style="margin:0 0 20px 0; font-size:16px; color:#2C1810; font-weight:600;">
                                You have received a new inquiry from <strong>{$safeType}</strong>:
                            </p>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FDFBF7; border-radius:8px; padding:20px; border:1px solid #EFEAE4; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; width:120px; font-weight:600;">Date:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;">{$dateStr}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Sender:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810; font-weight:700;">{$safeName}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Email:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;"><a href="mailto:{$safeEmail}" style="color:#8B4513; text-decoration:underline; font-weight:600;">{$safeEmail}</a></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Phone:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;"><a href="tel:{$safePhone}" style="color:#2C1810; text-decoration:none;">{$safePhone}</a></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Form Source:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810;">{$safeType}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; font-size:14px; color:#6B5347; font-weight:600;">Subject:</td>
                                    <td style="padding:8px 0; font-size:14px; color:#2C1810; font-weight:600;">{$safeSubject}</td>
                                </tr>
                            </table>

                            <div style="background-color:#FFFDF8; border-left:4px solid #D4AF37; padding:18px 20px; margin-bottom:28px; border-radius:0 8px 8px 0; border-top:1px solid #F3EDE7; border-right:1px solid #F3EDE7; border-bottom:1px solid #F3EDE7;">
                                <p style="margin:0 0 8px 0; font-size:12.5px; color:#6B5347; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Message Details:</p>
                                <p style="margin:0; font-size:14.5px; line-height:1.7; color:#2C1810;">{$safeMessage}</p>
                            </div>

                            <div style="text-align: center;">
                                <a href="mailto:{$safeEmail}?subject=Re: {$safeSubject}" style="display:inline-block; background-color:#2C1810; color:#F4ECE1; text-decoration:none; padding:13px 26px; border-radius:6px; font-weight:600; font-size:14px; border:1px solid #D4AF37;">Reply Directly to {$safeName}</a>
                            </div>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#F5EFE8; padding: 18px 30px; text-align: center; font-size: 12px; color: #8C766B; border-top:1px solid #E8DFD8;">
                            This automated alert was sent from your website (<a href="https://www.rtchocos.com" style="color:#8B4513;">rtchocos.com</a>).
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    return send_rt_email($recipients, $emailSubject, $bodyHtml, $name, $email);
}

/**
 * Sends an email notification when a new user subscribes to the newsletter or workshop waitlist.
 *
 * @param string $email
 * @param string $source (e.g. 'Website Footer Newsletter', 'Workshop Waitlist')
 * @return bool
 */
function send_subscription_notification($email, $source = 'Website Footer Newsletter') {
    $recipients  = get_notification_recipients();
    $safeEmail   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeSource  = htmlspecialchars($source, ENT_QUOTES, 'UTF-8');
    $dateStr     = date('d M Y, h:i A T');

    $emailSubject = "[RT Chocos] New Subscriber: {$safeEmail}";

    $bodyHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Subscriber Alert</title>
</head>
<body style="margin:0; padding:0; background-color:#FAF7F2; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#2C1810;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAF7F2; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="560" border="0" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border:1px solid #E8DFD8;">
                    <tr>
                        <td style="background-color:#2C1810; padding: 26px 30px; text-align: center; border-bottom: 3px solid #D4AF37;">
                            <h1 style="color:#F4ECE1; margin:0; font-size: 22px; font-weight:700; letter-spacing:2px; font-family:'Playfair Display', Georgia, serif;">RT CHOCOS</h1>
                            <p style="color:#D4AF37; margin:5px 0 0 0; font-size: 12px; text-transform:uppercase; letter-spacing:2px; font-weight:600;">New Subscriber Alert</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin:0 0 16px 0; font-size:15px; color:#2C1810;">
                                A new visitor has subscribed to receive updates from RT Chocos:
                            </p>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FDFBF7; border-radius:8px; padding:18px; border:1px solid #EFEAE4; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:6px 0; font-size:13.5px; color:#6B5347; width:120px; font-weight:600;">Date:</td>
                                    <td style="padding:6px 0; font-size:13.5px; color:#2C1810;">{$dateStr}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; font-size:13.5px; color:#6B5347; font-weight:600;">Email:</td>
                                    <td style="padding:6px 0; font-size:13.5px; color:#2C1810; font-weight:700;"><a href="mailto:{$safeEmail}" style="color:#8B4513;">{$safeEmail}</a></td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; font-size:13.5px; color:#6B5347; font-weight:600;">Source:</td>
                                    <td style="padding:6px 0; font-size:13.5px; color:#2C1810;">{$safeSource}</td>
                                </tr>
                            </table>

                            <div style="text-align: center;">
                                <a href="mailto:{$safeEmail}?subject=Welcome to RT Chocos" style="display:inline-block; background-color:#2C1810; color:#F4ECE1; text-decoration:none; padding:11px 22px; border-radius:6px; font-weight:600; font-size:13px; border:1px solid #D4AF37;">Email Subscriber</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#F5EFE8; padding: 16px 30px; text-align: center; font-size: 11.5px; color: #8C766B; border-top:1px solid #E8DFD8;">
                            RT Chocos Notification System • <a href="https://www.rtchocos.com" style="color:#8B4513;">rtchocos.com</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    return send_rt_email($recipients, $emailSubject, $bodyHtml, 'RT Chocos Subscriber', $email);
}

/**
 * Sends an email notification when a new comment is submitted on a blog article.
 *
 * @param string $name
 * @param string $comment
 * @param string $blogSlug
 * @return bool
 */
function send_comment_notification($name, $comment, $blogSlug) {
    $recipients  = get_notification_recipients();
    $safeName    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeComment = nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'));
    $safeSlug    = htmlspecialchars($blogSlug, ENT_QUOTES, 'UTF-8');
    $dateStr     = date('d M Y, h:i A T');

    $emailSubject = "[RT Chocos] New Article Comment from {$safeName}";

    $bodyHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Comment Alert</title>
</head>
<body style="margin:0; padding:0; background-color:#FAF7F2; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color:#2C1810;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FAF7F2; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table width="560" border="0" cellspacing="0" cellpadding="0" style="background-color:#FFFFFF; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border:1px solid #E8DFD8;">
                    <tr>
                        <td style="background-color:#2C1810; padding: 26px 30px; text-align: center; border-bottom: 3px solid #D4AF37;">
                            <h1 style="color:#F4ECE1; margin:0; font-size: 22px; font-weight:700; letter-spacing:2px; font-family:'Playfair Display', Georgia, serif;">RT CHOCOS</h1>
                            <p style="color:#D4AF37; margin:5px 0 0 0; font-size: 12px; text-transform:uppercase; letter-spacing:2px; font-weight:600;">New Article Comment</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin:0 0 16px 0; font-size:15px; color:#2C1810;">
                                A new comment has been posted on <strong>/blog/{$safeSlug}</strong>:
                            </p>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#FDFBF7; border-radius:8px; padding:18px; border:1px solid #EFEAE4; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:6px 0; font-size:13.5px; color:#6B5347; width:120px; font-weight:600;">Date:</td>
                                    <td style="padding:6px 0; font-size:13.5px; color:#2C1810;">{$dateStr}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; font-size:13.5px; color:#6B5347; font-weight:600;">Commenter:</td>
                                    <td style="padding:6px 0; font-size:13.5px; color:#2C1810; font-weight:700;">{$safeName}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; font-size:13.5px; color:#6B5347; font-weight:600;">Article:</td>
                                    <td style="padding:6px 0; font-size:13.5px; color:#2C1810;"><a href="https://www.rtchocos.com/blog/{$safeSlug}" style="color:#8B4513;">/blog/{$safeSlug}</a></td>
                                </tr>
                            </table>

                            <div style="background-color:#FFFDF8; border-left:4px solid #D4AF37; padding:16px 18px; margin-bottom:20px; border-radius:0 8px 8px 0; border-top:1px solid #F3EDE7; border-right:1px solid #F3EDE7; border-bottom:1px solid #F3EDE7;">
                                <p style="margin:0; font-size:14px; line-height:1.6; color:#2C1810;">{$safeComment}</p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#F5EFE8; padding: 16px 30px; text-align: center; font-size: 11.5px; color: #8C766B; border-top:1px solid #E8DFD8;">
                            RT Chocos Notification System • <a href="https://www.rtchocos.com" style="color:#8B4513;">rtchocos.com</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    return send_rt_email($recipients, $emailSubject, $bodyHtml, $name, '');
}
?>
