<?php
// restore_studio_images_live.php
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Restoring Studio Images on Hostinger ===\n";

$srcDir = '/home/u219698334/domains/rtchocos.com/public_html/beta/assets/products';
$destDir = '/home/u219698334/domains/rtchocos.com/public_html/assets/products';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

// 1. Map of products to their high-res studio files found in beta
$mapping = [
    6  => 'prod-almond-halwa-spread-1795591052.png',
    7  => 'prod-cashew-coconut-spread-1789237418.png',
    9  => 'prod-almond-spread-1789238078.png',
    18 => 'prod-hazelnut-spread-clean-label-product-healthy-gifting-1789410992.png',
    19 => 'prod-gourmet-stuffed-dates-gift-box-1789411226.png',
    20 => 'prod-the-rocher-collection-1789469648.png',
    21 => 'prod-almond-bark-1789238647.png',
    17 => 'prod-customized-chocolate-inclusion-bar-1789409649.png',
];

// 2. Copy each file to main public_html/assets/products/
foreach ($mapping as $productId => $fileName) {
    $src = $srcDir . '/' . $fileName;
    $dest = $destDir . '/' . $fileName;
    if (file_exists($src)) {
        if (copy($src, $dest)) {
            echo "COPIED: $fileName -> $dest (" . filesize($dest) . " bytes)\n";
        } else {
            echo "FAILED TO COPY: $src\n";
        }
    } else {
        echo "SRC NOT FOUND: $src\n";
    }
}

// 3. Connect to Database and Update products.image_main
require_once __DIR__ . '/includes/db.php';
try {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE products SET image_main = ? WHERE id = ?");
    foreach ($mapping as $productId => $fileName) {
        $relPath = 'assets/products/' . $fileName;
        $stmt->execute([$relPath, $productId]);
        echo "DB UPDATED: Product ID $productId -> $relPath\n";
    }
    echo "\n=== ALL STUDIO IMAGES RESTORED AND DB UPDATED SUCCESSFULLY! ===\n";
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
