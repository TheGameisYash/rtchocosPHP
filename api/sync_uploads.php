<?php
// api/sync_uploads.php - One-time initializer on Hostinger to create uploads_products/ outside public_html
// and back up all existing product photos into it cleanly.
header('Content-Type: application/json');

$key = $_GET['key'] ?? '';
if ($key !== 'rtbeta2026') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/product-uploads.php';
require_once __DIR__ . '/../includes/db.php';

$uploadsDir = get_product_uploads_dir();
$betaAssets = dirname(__DIR__) . '/assets/products';
$mainAssets = dirname(dirname(__DIR__)) . '/assets/products';

if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}

$copied = [];
foreach ([$mainAssets, $betaAssets] as $srcDir) {
    if (!is_dir($srcDir)) continue;
    foreach (glob($srcDir . '/*') as $file) {
        if (!is_file($file)) continue;
        $base = basename($file);
        $dest = $uploadsDir . '/' . $base;
        if (!file_exists($dest) || filesize($dest) !== filesize($file)) {
            if (@copy($file, $dest)) {
                $copied[] = $base . ' (' . filesize($dest) . ' bytes)';
            }
        }
    }
}

// Also ensure site_settings cache on this server reflects updated beta_url
clear_site_settings_cache();

echo json_encode([
    'status' => 'ok',
    'uploads_products_dir' => $uploadsDir,
    'writable' => is_writable($uploadsDir),
    'total_files_in_uploads_products' => count(glob($uploadsDir . '/*') ?: []),
    'newly_backed_up' => $copied,
    'beta_url_setting' => get_site_setting('beta_url', '', true),
    'htaccess_synced' => file_exists(dirname(__DIR__) . '/.htaccess')
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
