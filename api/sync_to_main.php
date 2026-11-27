<?php
// api/sync_to_main.php - Sync studio images and components from beta to main
header('Content-Type: application/json');

$key = $_GET['key'] ?? '';
if ($key !== 'rtbeta2026') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$betaDir = dirname(__DIR__); // .../beta
$mainDir = dirname($betaDir); // .../public_html

$results = [
    'betaDir' => $betaDir,
    'mainDir' => $mainDir,
    'files_copied' => [],
    'errors' => []
];

// 1. Sync product images
$betaImagesDir = $betaDir . '/assets/products';
$mainImagesDir = $mainDir . '/assets/products';

if (!is_dir($mainImagesDir)) {
    @mkdir($mainImagesDir, 0755, true);
}

$images = glob($betaImagesDir . '/prod-*.png');
if ($images) {
    foreach ($images as $img) {
        $basename = basename($img);
        $dest = $mainImagesDir . '/' . $basename;
        if (!file_exists($dest) || filesize($dest) !== filesize($img)) {
            if (@copy($img, $dest)) {
                $results['files_copied'][] = 'assets/products/' . $basename . ' (' . filesize($dest) . ' bytes)';
            } else {
                $results['errors'][] = 'Failed to copy image: ' . $basename;
            }
        } else {
            $results['files_copied'][] = 'assets/products/' . $basename . ' (already exists & verified)';
        }
    }
}

// 2. Sync product_image.php
if (file_exists($betaDir . '/product_image.php')) {
    if (@copy($betaDir . '/product_image.php', $mainDir . '/product_image.php')) {
        $results['files_copied'][] = 'product_image.php';
    } else {
        $results['errors'][] = 'Failed to copy product_image.php';
    }
}

// 3. Sync shop.php and product.php image resolver updates
if (file_exists($betaDir . '/shop.php')) {
    if (@copy($betaDir . '/shop.php', $mainDir . '/shop.php')) {
        $results['files_copied'][] = 'shop.php';
    }
}
if (file_exists($betaDir . '/product.php')) {
    if (@copy($betaDir . '/product.php', $mainDir . '/product.php')) {
        $results['files_copied'][] = 'product.php';
    }
}

echo json_encode(['status' => 'success', 'data' => $results], JSON_PRETTY_PRINT);
