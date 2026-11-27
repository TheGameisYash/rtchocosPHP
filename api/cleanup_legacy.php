<?php
// api/cleanup_legacy.php - One-time removal of orphaned pre-refactor directories on Hostinger beta
// (blog/, experiment/, failed-batch/, ingredient/) left behind by SFTP sync without --delete.
header('Content-Type: application/json');

if (($_GET['key'] ?? '') !== 'rtbeta2026') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function rmdir_recursive_safe($dir) {
    if (!is_dir($dir)) return false;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path) && !is_link($path)) {
            rmdir_recursive_safe($path);
        } else {
            @unlink($path);
        }
    }
    return @rmdir($dir);
}

$root = dirname(__DIR__);
$removed = [];
$skipped = [];

foreach (['blog', 'experiment', 'failed-batch', 'ingredient'] as $dir) {
    $target = $root . '/' . $dir;
    if (is_dir($target)) {
        if (rmdir_recursive_safe($target)) {
            $removed[] = $dir;
        } else {
            $skipped[] = $dir;
        }
    }
}

echo json_encode(['status' => 'ok', 'removed' => $removed, 'failed' => $skipped], JSON_PRETTY_PRINT);
