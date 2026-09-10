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
