<?php
// dev/system.php - System Diagnostics, Live SMTP Mailer Studio & API Integrations
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/mailer.php';

$successMsg = '';
$errorMsg = '';
$smtpDebugLog = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security token invalid. Please reload.';
    } else {
        if ($action === 'test_smtp') {
            $testRecipient = filter_var(trim($_POST['test_email'] ?? ''), FILTER_VALIDATE_EMAIL);
            if (!$testRecipient) {
                $errorMsg = 'Please provide a valid recipient email address.';
            } else {
                try {
                    // Send a diagnostic test email
                    $sent = send_contact_notification(
                        'Developer Master Diagnostic',
                        $testRecipient,
                        '+91 00000 00000',
                        'Diagnostic Test Message',
                        "This is an automated SMTP verification sent from RT Chocos Developer Master Console at " . date('Y-m-d H:i:s T') . ".\n\nAll mail routing through Hostinger SMTP (Port 465 SSL) is verified functional."
                    );

                    if ($sent) {
                        $successMsg = "SMTP test message successfully dispatched to {$testRecipient}!";
                    } else {
                        $errorMsg = "Mailer reported failure dispatching test email. Check server outbound port 465 connectivity.";
                    }
                } catch (Exception $e) {
                    $errorMsg = "SMTP error: " . $e->getMessage();
                }
            }
        }
    }
}

// Check PHP extensions
$requiredExts = ['pdo', 'pdo_mysql', 'curl', 'openssl', 'mbstring', 'json', 'fileinfo'];
$extStatus = [];
foreach ($requiredExts as $ext) {
    $extStatus[$ext] = extension_loaded($ext);
}

// Check AI API configurations
$hasGemini = !empty($_ENV['GEMINI_API_KEY']);
$hasOpenRouter = !empty($_ENV['OPENROUTER_API_KEY']);
$smtpHost = $_ENV['SMTP_HOST'] ?? 'smtp.hostinger.com';
$smtpUser = $_ENV['SMTP_USER'] ?? 'hello@rtchocos.com';
$smtpPort = $_ENV['SMTP_PORT'] ?? '465';

$csrfToken = generate_csrf();
render_dev_header("System & Diagnostics", "system");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<div class="dev-page-header">
    <div class="dev-page-title">
        <h2>System Diagnostics & SMTP Mailer Center</h2>
        <p>Live server specifications, outbound email dispatch diagnostics, and AI API connector health</p>
    </div>
</div>

<div class="dev-grid-2">
    <!-- Live SMTP Tester -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Live SMTP Mailer Test</span>
            </h3>
            <span class="dev-pill dev-pill-success">SSL : <?php echo htmlspecialchars($smtpPort); ?></span>
        </div>

        <div style="background: rgba(7, 10, 16, 0.6); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 12px; margin-bottom: 18px; font-size: 12px; font-family: 'Fira Code', monospace;">
            <div style="color: var(--dev-text-muted);">Host: <span style="color: #fff;"><?php echo htmlspecialchars($smtpHost); ?></span></div>
            <div style="color: var(--dev-text-muted); margin-top: 4px;">Sender: <span style="color: var(--dev-cyan);"><?php echo htmlspecialchars($smtpUser); ?></span></div>
        </div>

        <form action="system.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="test_smtp">

            <div class="dev-form-group">
                <label for="test_email">Recipient Test Email</label>
                <input type="email" id="test_email" name="test_email" class="dev-input" required placeholder="your.email@example.com" value="<?php echo htmlspecialchars($_ENV['NOTIFICATION_EMAIL'] ? explode(',', $_ENV['NOTIFICATION_EMAIL'])[0] : ''); ?>">
                <div class="help-text">Sends a genuine test email using RT Chocos's Hostinger SMTP pipeline to verify inbox deliverability.</div>
            </div>

            <button type="submit" class="dev-btn dev-btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                <span>Send Diagnostic Test Email</span>
            </button>
        </form>
    </div>

    <!-- AI & External API Health -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>AI & Integrations Health</span>
            </h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(7, 10, 16, 0.6); padding: 14px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
                <div>
                    <div style="font-weight: 700; color: #fff;">Google Gemini AI</div>
                    <div style="font-size: 11.5px; color: var(--dev-text-muted);">Powers chocolate science insights & class facts</div>
                </div>
                <div>
                    <?php if ($hasGemini): ?>
                        <span class="dev-pill dev-pill-success">CONFIGURED</span>
                    <?php else: ?>
                        <span class="dev-pill dev-pill-danger">NOT SET</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(7, 10, 16, 0.6); padding: 14px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
                <div>
                    <div style="font-weight: 700; color: #fff;">OpenRouter AI Gateway</div>
                    <div style="font-size: 11.5px; color: var(--dev-text-muted);">Secondary LLM redundancy pipeline</div>
                </div>
                <div>
                    <?php if ($hasOpenRouter): ?>
                        <span class="dev-pill dev-pill-success">CONFIGURED</span>
                    <?php else: ?>
                        <span class="dev-pill dev-pill-danger">NOT SET</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(7, 10, 16, 0.6); padding: 14px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
                <div>
                    <div style="font-weight: 700; color: #fff;">Hostinger SMTP Gateway</div>
                    <div style="font-size: 11.5px; color: var(--dev-text-muted);">Contact alerts & bulk quote notifications</div>
                </div>
                <div>
                    <span class="dev-pill dev-pill-success">ONLINE</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Server Environment Specs -->
<div class="dev-card">
    <div class="dev-card-header">
        <h3>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
            <span>Server Runtime Environment</span>
        </h3>
    </div>

    <div class="dev-grid-4" style="margin-bottom: 20px;">
        <div style="background: rgba(7, 10, 16, 0.6); padding: 12px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
            <div style="font-size: 11px; color: var(--dev-text-sub); font-family: 'Fira Code', monospace; text-transform: uppercase;">PHP Version</div>
            <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;"><?php echo phpversion(); ?></div>
        </div>
        <div style="background: rgba(7, 10, 16, 0.6); padding: 12px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
            <div style="font-size: 11px; color: var(--dev-text-sub); font-family: 'Fira Code', monospace; text-transform: uppercase;">Memory Limit</div>
            <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;"><?php echo ini_get('memory_limit'); ?></div>
        </div>
        <div style="background: rgba(7, 10, 16, 0.6); padding: 12px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
            <div style="font-size: 11px; color: var(--dev-text-sub); font-family: 'Fira Code', monospace; text-transform: uppercase;">Upload Max Size</div>
            <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;"><?php echo ini_get('upload_max_filesize'); ?></div>
        </div>
        <div style="background: rgba(7, 10, 16, 0.6); padding: 12px; border-radius: 8px; border: 1px solid var(--dev-border-subtle);">
            <div style="font-size: 11px; color: var(--dev-text-sub); font-family: 'Fira Code', monospace; text-transform: uppercase;">Max Execution Time</div>
            <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;"><?php echo ini_get('max_execution_time'); ?>s</div>
        </div>
    </div>

    <div>
        <div style="font-size: 12px; font-weight: 700; color: var(--dev-text-sub); text-transform: uppercase; margin-bottom: 10px;">Core Extensions Status</div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php foreach ($extStatus as $extName => $isLoaded): ?>
                <span class="dev-pill <?php echo $isLoaded ? 'dev-pill-success' : 'dev-pill-danger'; ?>">
                    <?php echo htmlspecialchars($extName); ?>: <?php echo $isLoaded ? 'INSTALLED' : 'MISSING'; ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
render_dev_footer();
?>
