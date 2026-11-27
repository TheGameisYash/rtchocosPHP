<?php
// test_blog_diag.php - List all files in public_html/assets/products and beta/assets/products
header('Content-Type: text/plain; charset=UTF-8');

$dirs = [
    'main' => '/home/u219698334/domains/rtchocos.com/public_html/assets/products',
    'beta' => '/home/u219698334/domains/rtchocos.com/public_html/beta/assets/products',
];

foreach ($dirs as $label => $d) {
    echo "=== Listing $label: $d ===\n";
    if (!is_dir($d)) {
        echo "Dir does not exist!\n\n";
        continue;
    }
    $files = scandir($d);
    echo "Count: " . count($files) . "\n";
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $full = $d . '/' . $f;
        echo date('Y-m-d H:i:s', filemtime($full)) . " | " . sprintf("%10d", filesize($full)) . " bytes | $f\n";
    }
    echo "\n";
}
