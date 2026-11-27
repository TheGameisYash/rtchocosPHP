<?php
// list_all_products_dir.php
header('Content-Type: text/plain; charset=UTF-8');

$dir = '/home/u219698334/domains/rtchocos.com/public_html/assets/products';
echo "=== Listing ALL files in $dir ===\n";

if (!is_dir($dir)) {
    echo "Directory does not exist!\n";
    exit;
}

$files = scandir($dir);
echo "Total files count: " . count($files) . "\n\n";
foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $full = $dir . '/' . $f;
    $size = filesize($full);
    $mtime = date('Y-m-d H:i:s', filemtime($full));
    echo "$mtime | " . sprintf("%10d", $size) . " bytes | $f\n";
}
