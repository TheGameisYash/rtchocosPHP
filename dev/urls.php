<?php
// dev/urls.php - Master URL & Routing Configurations
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed. Please reload.';
    } else {
        try {
            if ($action === 'update_urls') {
                $siteUrl = trim($_POST['site_url'] ?? 'https://www.rtchocos.com');
                $betaUrl = trim($_POST['beta_url'] ?? 'https://www.rtchocos.com/beta');

                // Validate URLs
                if (!filter_var($siteUrl, FILTER_VALIDATE_URL)) {
                    $errorMsg = 'Primary Site URL is not a valid URL.';
                } elseif (!filter_var($betaUrl, FILTER_VALIDATE_URL)) {
                    $errorMsg = 'Beta Staging URL is not a valid URL.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute(['site_url', $siteUrl, $siteUrl]);
                    $stmt->execute(['beta_url', $betaUrl, $betaUrl]);

                    $successMsg = 'Site URLs updated successfully! Canonical URLs and sitemaps will now use this base domain.';
                }
            } elseif ($action === 'toggle_beta_lock') {
                $currLock = get_site_setting('beta_locked', '1');
                $newLock = ($currLock === '1') ? '0' : '1';
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('beta_locked', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$newLock, $newLock]);
                clear_site_settings_cache();
                $successMsg = ($newLock === '1')
                    ? 'Beta Staging Lockdown is now ACTIVE! Public visitors are strictly blocked by the 403 lock screen.'
                    : 'Beta Staging Lockdown is now DEACTIVATED. Beta website is open for public testing.';
            } elseif ($action === 'update_beta_settings') {
                $betaCode = trim($_POST['beta_access_code'] ?? 'rtbeta2026');
                $betaLock = isset($_POST['beta_locked']) ? '1' : '0';
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute(['beta_access_code', $betaCode, $betaCode]);
                $stmt->execute(['beta_locked', $betaLock, $betaLock]);
                clear_site_settings_cache();
                $successMsg = 'Beta access security parameters updated successfully!';
            } elseif ($action === 'flush_cache') {
                $cacheDir = __DIR__ . '/../data/cache';
                $cleared = 0;
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '/*.json');
                    foreach ($files as $f) {
                        if (is_file($f)) {
                            unlink($f);
                            $cleared++;
                        }
                    }
                }
                $successMsg = "Flushed {$cleared} cache files successfully.";
            }
        } catch (Exception $e) {
            $errorMsg = 'Update failed: ' . $e->getMessage();
        }
    }
}

$siteUrl = get_site_setting('site_url', 'https://www.rtchocos.com');
$betaUrl = get_site_setting('beta_url', 'https://www.rtchocos.com/beta');
$betaLocked = get_site_setting('beta_locked', '1');
$betaAccessCode = get_site_setting('beta_access_code', 'rtbeta2026');
require_once __DIR__ . '/../includes/beta_lock.php';
$isBetaEnv = is_beta_environment();

// Route inspection list
$routes = [
    ['path' => '/', 'label' => 'Front Page / Landing', 'type' => 'Public'],
    ['path' => '/shop', 'label' => 'Chocolate Catalog & Shop', 'type' => 'Public'],
    ['path' => '/blog', 'label' => 'Cocoa Science & Articles', 'type' => 'Public'],
    ['path' => '/about', 'label' => 'Brand Story & Artisan Heritage', 'type' => 'Public'],
    ['path' => '/workshops', 'label' => 'Academy & Tasting Masterclasses', 'type' => 'Public'],
    ['path' => '/chocopedia', 'label' => 'Chocolate Encyclopedia', 'type' => 'Public'],
    ['path' => '/sitemap.xml', 'label' => 'Dynamic Search Index Sitemap', 'type' => 'SEO'],
    ['path' => '/admin', 'label' => 'Store & Content Management CMS', 'type' => 'Staff'],
    ['path' => '/dev', 'label' => 'Apex Developer Root Portal', 'type' => 'Root Dev'],
    ['path' => '/maintenance.php?preview=1', 'label' => 'Maintenance Screen Preview', 'type' => 'System']
];

$csrfToken = generate_csrf();
render_dev_header("URL & Route Control", "urls");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<div class="dev-page-header">
    <div class="dev-page-title">
        <h2>URL & Routing Master Configuration</h2>
        <p>Control website base domain, canonical URLs, beta staging addresses, and inspect core clean routes.</p>
    </div>

    <form action="urls.php" method="POST" style="margin: 0;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="action" value="flush_cache">
        <button type="submit" class="dev-btn dev-btn-outline">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Flush Routing Cache</span>
        </button>
    </form>
</div>

<div class="dev-grid-2">
    <!-- URL Configuration Card -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <span>Base URLs Configuration</span>
            </h3>
        </div>

        <form action="urls.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="update_urls">

            <div class="dev-form-group">
                <label for="site_url">Primary Production Site URL</label>
                <input type="url" id="site_url" name="site_url" class="dev-input" value="<?php echo htmlspecialchars($siteUrl); ?>" required placeholder="https://www.rtchocos.com">
                <div class="help-text">Used for canonical tags, dynamic XML sitemap generation, OpenGraph previews, and public links.</div>
            </div>

            <div class="dev-form-group">
                <label for="beta_url">Beta Staging URL</label>
                <input type="url" id="beta_url" name="beta_url" class="dev-input" value="<?php echo htmlspecialchars($betaUrl); ?>" required placeholder="https://www.rtchocos.com/beta">
                <div class="help-text">Used for staging and continuous deployment environment references.</div>
            </div>

            <button type="submit" class="dev-btn dev-btn-primary" style="margin-top: 10px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                <span>Save Site URLs</span>
            </button>
        </form>
    </div>

    <!-- Beta Staging Environment Lockdown Control -->
    <div class="dev-card" style="border-color: rgba(212, 175, 55, 0.35);">
        <div class="dev-card-header" style="justify-content: space-between;">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                <span>Beta Staging Lockdown &amp; Privacy Shield</span>
            </h3>
            <div style="display: flex; gap: 8px; align-items: center;">
                <?php if ($isBetaEnv): ?>
                    <span class="dev-pill dev-pill-cyan" title="Currently running inside Beta Staging host/path">Active on Beta</span>
                <?php else: ?>
                    <span class="dev-pill dev-pill-purple" title="Currently on Production environment">Production Host</span>
                <?php endif; ?>

                <?php if ($betaLocked === '1'): ?>
                    <span class="dev-pill dev-pill-crimson" style="font-weight: 700;">LOCKED (403 Shield)</span>
                <?php else: ?>
                    <span class="dev-pill dev-pill-emerald" style="font-weight: 700;">PUBLIC UNLOCKED</span>
                <?php endif; ?>
            </div>
        </div>

        <p style="font-size: 13px; color: var(--dev-text-sub); line-height: 1.5; margin-bottom: 18px;">
            Enforces strict access isolation on the Beta staging site (<code>beta.rtchocos.com</code> / subfolder <code>/beta</code>). When locked, any unauthorized visitor sees an ultra-luxury lock screen with HTTP 403 and <code>noindex</code> headers. Only people with the passcode or Admin/Dev login can access the beta platform.
        </p>

        <!-- Quick Lockdown Toggle Form -->
        <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--dev-border-subtle); border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
                <strong style="display: block; font-size: 14px; color: #fff;">Lockdown Status: <?php echo ($betaLocked === '1') ? '<span style="color: var(--dev-crimson);">LOCKED (No Public Access)</span>' : '<span style="color: var(--dev-emerald);">OPEN (Public Accessible)</span>'; ?></strong>
                <span style="font-size: 12px; color: var(--dev-text-muted);">
                    <?php echo ($betaLocked === '1') ? 'Visitors are stopped by the private passcode screen.' : 'Anyone with the beta URL can view unfinished staging features.'; ?>
                </span>
            </div>
            <form action="urls.php" method="POST" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="toggle_beta_lock">
                <?php if ($betaLocked === '1'): ?>
                    <button type="submit" class="dev-btn dev-btn-danger">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        <span>Deactivate Beta Lock (Make Public)</span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="dev-btn dev-btn-primary" style="background: linear-gradient(135deg, #d4af37, #996515); color: #000; font-weight: 700;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0110 0v4"></path></svg>
                        <span>Activate Beta Lock (Lock Website Now)</span>
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Detailed Configuration Form -->
        <form action="urls.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="update_beta_settings">

            <div class="dev-form-group">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="beta_locked" value="1" <?php echo ($betaLocked === '1') ? 'checked' : ''; ?> style="accent-color: var(--dev-cyan); width: 16px; height: 16px;">
                    <span style="font-weight: 600; font-size: 13.5px; color: #fff;">Enable Beta Staging Lockdown Shield</span>
                </label>
                <div class="help-text">When checked, all unauthenticated visits to beta will be intercepted.</div>
            </div>

            <div class="dev-form-group">
                <label for="beta_access_code">Beta Team Access Passcode</label>
                <div style="display: flex; gap: 10px; max-width: 460px;">
                    <input type="text" id="beta_access_code" name="beta_access_code" class="dev-input" value="<?php echo htmlspecialchars($betaAccessCode); ?>" required placeholder="e.g. rtbeta2026">
                    <button type="submit" class="dev-btn dev-btn-outline" style="white-space: nowrap;">Save Passcode</button>
                </div>
                <div class="help-text">Team members or client reviewers type this passcode on the lock screen to enter.</div>
            </div>
        </form>

        <!-- Direct Instant-Unlock Link for Testers -->
        <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--dev-border-subtle);">
            <label style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: var(--dev-text-sub); display: block; margin-bottom: 6px;">Direct One-Click Bypass URL (Share with Client / Testers)</label>
            <div style="display: flex; gap: 10px; align-items: center; max-width: 580px;">
                <input type="text" readonly class="dev-input" value="<?php echo htmlspecialchars(rtrim($betaUrl, '/') . '/?unlock=' . $betaAccessCode); ?>" style="font-family: monospace; font-size: 12px; color: var(--dev-cyan); background: rgba(0,0,0,0.4);" id="directBypassInput">
                <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('directBypassInput').value); this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy Link', 2000);">Copy Link</button>
                <a href="<?php echo htmlspecialchars(rtrim($betaUrl, '/') . '/?lock_beta=1'); ?>" target="_blank" class="dev-btn dev-btn-outline dev-btn-sm" title="Open lock screen in new tab">Test Lock Screen &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Active Route Inspector -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span>Route Resolution Directory</span>
            </h3>
        </div>

        <div class="dev-table-wrap">
            <table class="dev-table">
                <thead>
                    <tr>
                        <th>Path</th>
                        <th>Classification</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routes as $r): ?>
                        <tr>
                            <td>
                                <code style="color: var(--dev-cyan); font-weight: 600;"><?php echo htmlspecialchars($r['path']); ?></code>
                                <div style="font-size: 11px; color: var(--dev-text-sub);"><?php echo htmlspecialchars($r['label']); ?></div>
                            </td>
                            <td>
                                <span class="dev-pill dev-pill-cyan"><?php echo htmlspecialchars($r['type']); ?></span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?php echo htmlspecialchars($r['path']); ?>" target="_blank" class="dev-btn dev-btn-outline dev-btn-sm" style="padding: 3px 8px; font-size: 11px;">
                                    Test &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
render_dev_footer();
?>
