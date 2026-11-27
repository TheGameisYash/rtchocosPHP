<?php
// test_blog_diag.php - Check exact 11 files on disk
header('Content-Type: text/plain; charset=UTF-8');

$paths = [
    'assets/products/prod-almond-halwa-spread-1795592478.png',
    'assets/products/prod-cashew-coconut-spread-1795592748.png',
    'assets/products/prod-almond-spread-1795605000.png',
    'assets/products/prod-hazelnut-spread-clean-label-product-healthy-gifting-1795605136.png',
    'assets/products/prod-classic-almond-spread-1795605240.png',
    'assets/products/prod-gourmet-stuffed-dates-gift-box-1795606821.png',
    'assets/products/prod-the-rocher-collection-1795606988.png',
    'assets/products/prod-almond-bark-1795607224.png',
    'assets/products/prod-handcrafted-chocolates-1795607705.png',
    'assets/products/prod-dates-powder-1795607837.png',
    'assets/products/prod-customized-chocolate-inclusion-bar-1795608230.png',
];

$roots = [
    'main' => '/home/u219698334/domains/rtchocos.com/public_html',
    'beta' => '/home/u219698334/domains/rtchocos.com/public_html/beta',
    'account_beta' => '/home/u219698334/beta',
    'uploads_products' => '/home/u219698334/domains/rtchocos.com/uploads_products',
    'uploads_blogs' => '/home/u219698334/domains/rtchocos.com/uploads_blogs',
];

echo "=== Checking Exact 11 Image Paths on Hostinger ===\n";
foreach ($roots as $label => $root) {
    echo "\n--- Checking Root: $label ($root) ---\n";
    if (!is_dir($root)) {
        echo "Root dir does not exist!\n";
        continue;
    }
    foreach ($paths as $p) {
        $full = $root . '/' . $p;
        if (file_exists($full)) {
            echo "EXISTS: $full (" . filesize($full) . " bytes)\n";
        } else {
            // Check basename
            $base = basename($p);
            $fullBase = $root . '/' . $base;
            if (file_exists($fullBase)) {
                echo "EXISTS AS BASENAME: $fullBase (" . filesize($fullBase) . " bytes)\n";
            }
        }
    }
}

echo "\n=== Check Completed ===\n";
