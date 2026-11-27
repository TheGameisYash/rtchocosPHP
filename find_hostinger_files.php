<?php
// find_hostinger_files.php - Diagnostic scanner for files uploaded to Hostinger
header('Content-Type: text/plain; charset=UTF-8');

$cutoff = time() - (14 * 86400); // 14 days ago
echo "=== Hostinger Filesystem Search for Recently Updated Images ===\n";
echo "Current __DIR__: " . __DIR__ . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n\n";

$extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$found = [];

function scan_dir_recursive($dir, &$found, $cutoff, $extensions, $maxDepth = 4, $currentDepth = 0) {
    if ($currentDepth > $maxDepth || !is_dir($dir) || !is_readable($dir)) {
        return;
    }
    
    $items = @scandir($dir);
    if ($items === false) return;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === 'node_modules') {
            continue;
        }
        $fullPath = $dir . '/' . $item;
        if (is_dir($fullPath)) {
            scan_dir_recursive($fullPath, $found, $cutoff, $extensions, $maxDepth, $currentDepth + 1);
        } elseif (is_file($fullPath)) {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $extensions)) {
                $mtime = filemtime($fullPath);
                $isTarget = (strpos($item, '1795') !== false) || (stripos($item, 'halwa') !== false) || (stripos($item, 'spread') !== false) || (stripos($item, 'final') !== false);
                if ($mtime >= $cutoff || $isTarget) {
                    $found[] = [
                        'path' => $fullPath,
                        'mtime' => date('Y-m-d H:i:s', $mtime),
                        'timestamp' => $mtime,
                        'size' => filesize($fullPath),
                        'name' => $item
                    ];
                }
            }
        }
    }
}

// Search locations
$searchDirs = [
    __DIR__ . '/../assets/products',
    __DIR__ . '/assets/products',
    dirname(__DIR__, 2) . '/uploads_blogs',
    dirname(__DIR__, 3) . '/uploads_blogs',
    dirname(__DIR__, 2) . '/public_html/assets',
    dirname(__DIR__, 3) . '/.trash',
];

foreach ($searchDirs as $sd) {
    if (is_dir($sd)) {
        echo "Scanning directory: $sd\n";
        scan_dir_recursive($sd, $found, $cutoff, $extensions, 3);
    } else {
        echo "Directory not found: $sd\n";
    }
}

// Broad scan in public_html
$pubHtml = realpath(__DIR__ . '/..');
if ($pubHtml && is_dir($pubHtml)) {
    echo "Scanning public_html root: $pubHtml\n";
    scan_dir_recursive($pubHtml . '/assets', $found, $cutoff, $extensions, 3);
}

echo "\n--- Total Matches Found: " . count($found) . " ---\n";
usort($found, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

foreach ($found as $f) {
    echo "{$f['mtime']} | " . sprintf("%10d", $f['size']) . " bytes | {$f['path']}\n";
}
