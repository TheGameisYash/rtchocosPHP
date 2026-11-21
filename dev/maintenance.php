<?php
// dev/maintenance.php - Maintenance Studio with Video & Picture Support
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed. Please refresh the page.';
    } else {
        try {
            $mode = isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] === '1' ? '1' : '0';
            $title = trim($_POST['maintenance_title'] ?? '');
            $subtitle = trim($_POST['maintenance_subtitle'] ?? '');
            $message = trim($_POST['maintenance_message'] ?? '');
            $eta = trim($_POST['maintenance_eta'] ?? '');
            $mediaType = $_POST['maintenance_media_type'] ?? 'both';
            $imageUrl = trim($_POST['maintenance_image'] ?? '');
            $videoUrl = trim($_POST['maintenance_video'] ?? '');
            $bypassKey = trim($_POST['maintenance_bypass_key'] ?? 'rtdev2026');
            $notifyEnabled = isset($_POST['maintenance_notify_enabled']) && $_POST['maintenance_notify_enabled'] === '1' ? '1' : '0';

            // Handle optional uploaded picture file
            if (!empty($_FILES['maintenance_image_file']['name']) && $_FILES['maintenance_image_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['maintenance_image_file'];
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, $allowedMimes)) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $newFilename = 'maint_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
                    $targetDir = __DIR__ . '/../assets/maintenance';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $targetPath = $targetDir . '/' . $newFilename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $imageUrl = 'assets/maintenance/' . $newFilename;
                    }
                } else {
                    $errorMsg = 'Uploaded image must be JPG, PNG, WEBP, or GIF.';
                }
            }

            if (empty($errorMsg)) {
                $settingsToUpdate = [
                    'maintenance_mode' => $mode,
                    'maintenance_title' => $title,
                    'maintenance_subtitle' => $subtitle,
                    'maintenance_message' => $message,
                    'maintenance_eta' => $eta,
                    'maintenance_media_type' => $mediaType,
                    'maintenance_image' => $imageUrl,
                    'maintenance_video' => $videoUrl,
                    'maintenance_bypass_key' => $bypassKey,
                    'maintenance_notify_enabled' => $notifyEnabled
                ];

                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                foreach ($settingsToUpdate as $k => $v) {
                    $stmt->execute([$k, $v, $v]);
                }

                $successMsg = 'Maintenance settings and media assets updated successfully!';
            }

        } catch (Exception $e) {
            $errorMsg = 'Failed to update settings: ' . $e->getMessage();
        }
    }
}

// Load current maintenance values
$settings = [];
try {
    $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'maintenance_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
    $settings = $rows;
} catch (Exception $e) {
    // non-fatal
}

$mode = $settings['maintenance_mode'] ?? '0';
$title = $settings['maintenance_title'] ?? "We're Perfecting Something Delicious";
$subtitle = $settings['maintenance_subtitle'] ?? 'Our chocolate laboratory is currently undergoing planned upgrades.';
$message = $settings['maintenance_message'] ?? "We are crafting exceptional new chocolate recipes and upgrading our platform. We will return online shortly!";
$eta = $settings['maintenance_eta'] ?? '';
$mediaType = $settings['maintenance_media_type'] ?? 'both';
$imageUrl = $settings['maintenance_image'] ?? 'assets/ph.png';
$videoUrl = $settings['maintenance_video'] ?? 'https://www.youtube.com/watch?v=kY3Pvdq0YpY';
$bypassKey = $settings['maintenance_bypass_key'] ?? 'rtdev2026';
$notifyEnabled = $settings['maintenance_notify_enabled'] ?? '1';

$siteUrl = get_site_setting('site_url', 'https://www.rtchocos.com');
$bypassUrl = rtrim($siteUrl, '/') . '/?bypass=' . urlencode($bypassKey);

$csrfToken = generate_csrf();
render_dev_header("Maintenance Studio", "maintenance");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<form action="maintenance.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

    <div class="dev-page-header">
        <div class="dev-page-title">
            <h2>Maintenance Mode Studio</h2>
            <p>Configure public maintenance lock, custom messaging, countdown timer, and high-impact video & photo showcases</p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="/maintenance.php?preview=1" target="_blank" class="dev-btn dev-btn-outline" title="View the actual maintenance page without enabling it">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Live Preview Page</span>
            </a>
            <button type="submit" class="dev-btn dev-btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                <span>Save Configuration</span>
            </button>
        </div>
    </div>

    <!-- Master Switch Status Banner -->
    <div class="dev-card" style="margin-bottom: 24px; border-color: <?php echo ($mode === '1') ? 'var(--dev-crimson)' : 'var(--dev-emerald)'; ?>; background: <?php echo ($mode === '1') ? 'rgba(239, 68, 68, 0.08)' : 'rgba(0, 245, 160, 0.05)'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: <?php echo ($mode === '1') ? 'rgba(239, 68, 68, 0.2)' : 'rgba(0, 245, 160, 0.2)'; ?>; display: flex; align-items: center; justify-content: center; color: <?php echo ($mode === '1') ? 'var(--dev-crimson)' : 'var(--dev-emerald)'; ?>;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div style="font-size: 16px; font-weight: 800; color: #fff;">
                        Maintenance Lock Status: 
                        <span style="color: <?php echo ($mode === '1') ? 'var(--dev-crimson)' : 'var(--dev-emerald)'; ?>;">
                            <?php echo ($mode === '1') ? 'ACTIVE (SITE LOCKED)' : 'DEACTIVATED (SITE LIVE)'; ?>
                        </span>
                    </div>
                    <div style="font-size: 12.5px; color: var(--dev-text-muted); margin-top: 2px;">
                        <?php if ($mode === '1'): ?>
                            Public visitors are currently receiving HTTP 503 and redirected to the luxury maintenance screen.
                        <?php else: ?>
                            The website is freely accessible to all public visitors.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Animated Toggle Checkbox -->
            <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                <input type="checkbox" name="maintenance_mode" value="1" <?php echo ($mode === '1') ? 'checked' : ''; ?> style="width: 20px; height: 20px; accent-color: var(--dev-crimson);">
                <span style="font-size: 14px; font-weight: 700; color: #fff;">Enable Maintenance Mode</span>
            </label>
        </div>
    </div>

    <div class="dev-grid-2">
        <!-- Left Column: Content & Messaging -->
        <div class="dev-card">
            <div class="dev-card-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Branding & Messaging</span>
                </h3>
            </div>

            <div class="dev-form-group">
                <label for="maintenance_title">Headline / Title</label>
                <input type="text" id="maintenance_title" name="maintenance_title" class="dev-input" value="<?php echo htmlspecialchars($title); ?>" required placeholder="e.g. We're Crafting Something Extraordinary">
                <div class="help-text">Prominently displayed as the primary banner on the maintenance screen.</div>
            </div>

            <div class="dev-form-group">
                <label for="maintenance_subtitle">Subtitle</label>
                <input type="text" id="maintenance_subtitle" name="maintenance_subtitle" class="dev-input" value="<?php echo htmlspecialchars($subtitle); ?>" placeholder="e.g. Planned Chocolate Laboratory Enhancements">
            </div>

            <div class="dev-form-group">
                <label for="maintenance_message">Detailed Notice / Explanation</label>
                <textarea id="maintenance_message" name="maintenance_message" class="dev-textarea" rows="4" placeholder="Detailed explanation for customers and chocolate enthusiasts..."><?php echo htmlspecialchars($message); ?></textarea>
                <div class="help-text">Supports paragraph text describing reasons for downtime or exciting upcoming features.</div>
            </div>

            <div class="dev-form-group">
                <label for="maintenance_eta">Estimated Return Time (Live Countdown)</label>
                <input type="text" id="maintenance_eta" name="maintenance_eta" class="dev-input" value="<?php echo htmlspecialchars($eta); ?>" placeholder="YYYY-MM-DD HH:MM:SS or ISO datetime">
                <div class="help-text">If set to a future date/time (e.g. <code><?php echo date('Y-m-d 20:00:00', strtotime('+1 day')); ?></code>), a live interactive countdown clock will display on the screen!</div>
            </div>
        </div>

        <!-- Right Column: Video & Pictures Studio -->
        <div class="dev-card">
            <div class="dev-card-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Media Studio (Photos & Video)</span>
                </h3>
            </div>

            <!-- Media Type Selector -->
            <div class="dev-form-group">
                <label for="maintenance_media_type">Media Display Format</label>
                <select id="maintenance_media_type" name="maintenance_media_type" class="dev-select">
                    <option value="both" <?php echo ($mediaType === 'both') ? 'selected' : ''; ?>>Both Picture & Video (Recommended)</option>
                    <option value="video" <?php echo ($mediaType === 'video') ? 'selected' : ''; ?>>Video Only</option>
                    <option value="image" <?php echo ($mediaType === 'image') ? 'selected' : ''; ?>>Picture Only</option>
                    <option value="none" <?php echo ($mediaType === 'none') ? 'selected' : ''; ?>>Text Only (No Media)</option>
                </select>
                <div class="help-text">Choose which media elements should be showcased on the maintenance page.</div>
            </div>

            <!-- Picture Controls -->
            <div style="background: rgba(7, 10, 16, 0.5); border: 1px solid var(--dev-border-subtle); border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Featured Picture</span>
                </div>

                <div class="dev-form-group" style="margin-bottom: 12px;">
                    <label for="maintenance_image">Image Path or URL</label>
                    <input type="text" id="maintenance_image" name="maintenance_image" class="dev-input" value="<?php echo htmlspecialchars($imageUrl); ?>" placeholder="assets/ph.png or https://...">
                </div>

                <div class="dev-form-group" style="margin-bottom: 0;">
                    <label for="maintenance_image_file">Or Upload New Picture</label>
                    <input type="file" id="maintenance_image_file" name="maintenance_image_file" accept="image/*" class="dev-input" style="padding: 7px 10px;">
                </div>

                <?php if (!empty($imageUrl)): ?>
                    <div style="margin-top: 12px; display: flex; align-items: center; gap: 12px;">
                        <img src="/<?php echo ltrim($imageUrl, '/'); ?>" alt="Preview" style="width: 80px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid var(--dev-border-subtle);">
                        <span style="font-size: 11.5px; color: var(--dev-text-muted);">Current Active Image</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Video Controls -->
            <div style="background: rgba(7, 10, 16, 0.5); border: 1px solid var(--dev-border-subtle); border-radius: 10px; padding: 16px;">
                <div style="font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Featured Video</span>
                </div>

                <div class="dev-form-group" style="margin-bottom: 0;">
                    <label for="maintenance_video">Video URL (YouTube, Vimeo, or MP4)</label>
                    <input type="text" id="maintenance_video" name="maintenance_video" class="dev-input" value="<?php echo htmlspecialchars($videoUrl); ?>" placeholder="https://www.youtube.com/watch?v=... or .mp4 URL">
                    <div class="help-text">Supports YouTube video URLs, YouTube Shorts, Vimeo links, or direct MP4 video files.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security, Bypass Key & Email Capture -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                <span>Bypass Keys & Visitor Subscriptions</span>
            </h3>
        </div>

        <div class="dev-grid-2" style="margin-bottom: 0;">
            <div>
                <div class="dev-form-group">
                    <label for="maintenance_bypass_key">Secret Bypass Key</label>
                    <input type="text" id="maintenance_bypass_key" name="maintenance_bypass_key" class="dev-input" value="<?php echo htmlspecialchars($bypassKey); ?>" required>
                    <div class="help-text">Visitors passing <code>?bypass=<?php echo htmlspecialchars($bypassKey); ?></code> can access the live website even during maintenance.</div>
                </div>

                <div style="background: rgba(0, 240, 255, 0.05); border: 1px solid rgba(0, 240, 255, 0.15); border-radius: 8px; padding: 12px; font-size: 12px; color: var(--dev-text-muted);">
                    <div style="color: #fff; font-weight: 600; margin-bottom: 4px;">Shareable Client Preview Link:</div>
                    <code style="color: var(--dev-cyan); word-break: break-all;"><?php echo htmlspecialchars($bypassUrl); ?></code>
                </div>
            </div>

            <div>
                <div class="dev-form-group">
                    <label>Visitor Email Notification</label>
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-top: 10px;">
                        <input type="checkbox" name="maintenance_notify_enabled" value="1" <?php echo ($notifyEnabled === '1') ? 'checked' : ''; ?> style="accent-color: var(--dev-cyan);">
                        <span style="font-size: 13.5px; color: #fff;">Show "Notify me when live" email subscribe form</span>
                    </label>
                    <div class="help-text">Allows visitors to leave their email address to be automatically added to the subscribers database.</div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="dev-btn dev-btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        <span>Save All Maintenance Settings</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
render_dev_footer();
?>
