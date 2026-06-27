<?php
// admin/settings.php - Site Settings and Password Management
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

// Handle Settings Update or Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } else {
        try {
            if ($action === 'update_settings') {
                $settings = $_POST['settings'] ?? [];
                
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                foreach ($settings as $key => $val) {
                    $stmt->execute([trim($val), $key]);
                }
                $pdo->commit();
                
                $success = 'Site settings updated successfully!';
            } elseif ($action === 'change_password') {
                $currentPwd = $_POST['current_password'] ?? '';
                $newPwd = $_POST['new_password'] ?? '';
                $confirmPwd = $_POST['confirm_password'] ?? '';
                
                $username = $_SESSION['admin_user'] ?? 'admin';
                
                // Fetch hash
                $stmt = $pdo->prepare("SELECT password FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                $hash = $stmt->fetchColumn();
                
                if (empty($currentPwd) || empty($newPwd) || empty($confirmPwd)) {
                    $error = 'Please fill out all password fields.';
                } elseif (!password_verify($currentPwd, $hash)) {
                    $error = 'Current password is incorrect.';
                } elseif ($newPwd !== $confirmPwd) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($newPwd) < 8) {
                    $error = 'New password must be at least 8 characters long.';
                } else {
                    $newHash = password_hash($newPwd, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
                    $stmt->execute([$newHash, $username]);
                    $success = 'Password changed successfully!';
                }
            }
        } catch (Exception $e) {
            if ($action === 'update_settings') {
                $pdo->rollBack();
            }
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Load all current settings
$settings = [];
try {
    $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    die("Error loading configurations: " . $e->getMessage());
}

$csrfToken = generate_csrf();
render_admin_header("Site Settings", "settings");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<div class="editor-layout" style="align-items: start;">
    <!-- Site Settings Form Card (Left Pane) -->
    <div class="form-card" style="margin-bottom:0; flex-grow:2;">
        <div class="editor-title">General Settings</div>
        
        <form action="settings.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="update_settings">
            
            <div class="form-row">
                <div class="form-col form-group">
                    <label class="static-label" for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="settings[site_name]" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'RT Chocos'); ?>" required>
                </div>
                <div class="form-col form-group">
                    <label class="static-label" for="site_tagline">Site Tagline</label>
                    <input type="text" id="site_tagline" name="settings[site_tagline]" value="<?php echo htmlspecialchars($settings['site_tagline'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="static-label" for="meta_description">Meta Description (SEO)</label>
                <textarea id="meta_description" name="settings[meta_description]" rows="3" required placeholder="Description that appears in search engines..."><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
            </div>

            <div class="editor-title" style="margin-top: 32px; font-size: 18px;">Contact details</div>
            <div class="form-row">
                <div class="form-col form-group">
                    <label class="static-label" for="contact_email">Email Address</label>
                    <input type="email" id="contact_email" name="settings[contact_email]" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" style="width:100%; padding:14px; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-app); color:var(--text-main); font-family:var(--font-sans); outline:none;">
                </div>
                <div class="form-col form-group">
                    <label class="static-label" for="contact_phone">Phone Number</label>
                    <input type="text" id="contact_phone" name="settings[contact_phone]" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                </div>
            </div>

            <div class="editor-title" style="margin-top: 32px; font-size: 18px;">Social Media Links</div>
            <div class="form-row">
                <div class="form-col form-group">
                    <label class="static-label" for="social_instagram">Instagram URL</label>
                    <input type="url" id="social_instagram" name="settings[social_instagram]" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                </div>
                <div class="form-col form-group">
                    <label class="static-label" for="social_facebook">Facebook URL</label>
                    <input type="url" id="social_facebook" name="settings[social_facebook]" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-col form-group">
                    <label class="static-label" for="social_youtube">YouTube Channel</label>
                    <input type="url" id="social_youtube" name="settings[social_youtube]" value="<?php echo htmlspecialchars($settings['social_youtube'] ?? ''); ?>">
                </div>
                <div class="form-col form-group">
                    <label class="static-label" for="social_linkedin">LinkedIn Page</label>
                    <input type="url" id="social_linkedin" name="settings[social_linkedin]" value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>">
                </div>
            </div>

            <div class="editor-title" style="margin-top: 32px; font-size: 18px;">Newsletter Banner Content</div>
            <div class="form-group">
                <label class="static-label" for="newsletter_text">Newsletter Callout Text</label>
                <textarea id="newsletter_text" name="settings[newsletter_text]" rows="2"><?php echo htmlspecialchars($settings['newsletter_text'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 12px; padding: 12px 24px; min-width: 180px;">Save Settings</button>
        </form>
    </div>

    <!-- Security & Password Change Card (Right Pane) -->
    <div class="form-card" style="margin-bottom:0; flex-grow:1; min-width: 320px;">
        <div class="editor-title">Security & Password</div>
        
        <form action="settings.php" method="POST" id="pwdForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label class="static-label" for="current_password">Current Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('current_password', this)">
                        <svg class="eye-open" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="eye-closed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L3 3m12 12l6 6M21 12a9 9 0 01-3.35 5.646m-2.298-1.424A9 9 0 0021 12c-1.274-4.057-5.064-7-9.542-7-1.298 0-2.522.25-3.645.703"></path></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="static-label" for="new_password">New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password', this)">
                        <svg class="eye-open" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="eye-closed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L3 3m12 12l6 6M21 12a9 9 0 01-3.35 5.646m-2.298-1.424A9 9 0 0021 12c-1.274-4.057-5.064-7-9.542-7-1.298 0-2.522.25-3.645.703"></path></svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="static-label" for="confirm_password">Confirm New Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)">
                        <svg class="eye-open" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="eye-closed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L3 3m12 12l6 6M21 12a9 9 0 01-3.35 5.646m-2.298-1.424A9 9 0 0021 12c-1.274-4.057-5.064-7-9.542-7-1.298 0-2.522.25-3.645.703"></path></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-secondary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 12px;">Change Password</button>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const eyeOpen = btn.querySelector('.eye-open');
        const eyeClosed = btn.querySelector('.eye-closed');
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.style.display = 'none';
            eyeClosed.style.display = 'block';
        } else {
            input.type = 'password';
            eyeOpen.style.display = 'block';
            eyeClosed.style.display = 'none';
        }
    }
</script>
