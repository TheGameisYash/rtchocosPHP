<?php
// test_blog_diag.php - Deep scanner across all Hostinger directories
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Deep Hostinger User Directory Scan ===\n";

$accountHome = '/home/u219698334';
echo "Account Home: $accountHome\n";

$extensions = ['jpg', 'jpeg', 'png', 'webp'];
$targetPatterns = ['1795', 'halwa', 'coconut', 'crunchy', 'rocher', 'dates', 'bark', 'chocolates', 'final'];

function search_folder($dir, $maxDepth = 4, $currentDepth = 0) {
    global $extensions, $targetPatterns;
    if ($currentDepth > $maxDepth || !is_dir($dir) || !is_readable($dir)) return;
    
    $items = @scandir($dir);
    if ($items === false) return;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.git' || $item === 'node_modules' || $item === 'cache') continue;
        $full = $dir . '/' . $item;
        if (is_dir($full)) {
            search_folder($full, $maxDepth, $currentDepth + 1);
        } elseif (is_file($full)) {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $extensions)) {
                $isMatch = false;
                $mtime = filemtime($full);
                // check if modified in last 7 days
                if ($mtime >= (time() - 7 * 86400)) {
                    $isMatch = true;
                } else {
                    foreach ($targetPatterns as $p) {
                        if (stripos($item, $p) !== false) {
                            $isMatch = true;
                            break;
                        }
                    }
                }
                if ($isMatch) {
                    echo date('Y-m-d H:i:s', $mtime) . " | " . sprintf("%10d", filesize($full)) . " bytes | $full\n";
                }
            }
        }
    }
}

search_folder($accountHome, 5);
echo "\n=== Scan Completed ===\n";
