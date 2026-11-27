<?php
// product_image.php - Serves product images from persistent uploads_products/ or syncs between main and beta.rtchocos.com
$file = $_GET['file'] ?? '';

// Prevent directory traversal attacks
$file = str_replace('\\', '/', $file);
if (strpos($file, '..') !== false) {
    header("HTTP/1.0 404 Not Found");
    exit;
}
$file = ltrim($file, '/');

if (empty($file)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

require_once __DIR__ . '/includes/product-uploads.php';

$uploadsDir = get_product_uploads_dir();
$persistentPath = $uploadsDir . '/' . $file;
$localPath = __DIR__ . '/assets/products/' . $file;
$betaPath = __DIR__ . '/beta/assets/products/' . $file;
$mainPath = dirname(__DIR__) . '/assets/products/' . $file;

$path = '';

if (file_exists($persistentPath) && !is_dir($persistentPath)) {
    // Found in persistent uploads_products/, cache into local web dir for fast direct static serving
    if (realpath($persistentPath) !== realpath($localPath)) {
        @mkdir(dirname($localPath), 0755, true);
        @copy($persistentPath, $localPath);
    }
    $path = $persistentPath;
} elseif (file_exists($localPath) && !is_dir($localPath)) {
    // Backfill into persistent uploads_products/ if not already there
    if (realpath($persistentPath) !== realpath($localPath) && !file_exists($persistentPath)) {
        @mkdir(dirname($persistentPath), 0755, true);
        @copy($localPath, $persistentPath);
    }
    $path = $localPath;
} elseif (file_exists($betaPath) && !is_dir($betaPath)) {
    @mkdir(dirname($localPath), 0755, true);
    @copy($betaPath, $localPath);
    @mkdir(dirname($persistentPath), 0755, true);
    @copy($betaPath, $persistentPath);
    $path = $betaPath;
} elseif (file_exists($mainPath) && !is_dir($mainPath)) {
    @mkdir(dirname($localPath), 0755, true);
    @copy($mainPath, $localPath);
    @mkdir(dirname($persistentPath), 0755, true);
    @copy($mainPath, $persistentPath);
    $path = $mainPath;
}

if (empty($path) || !file_exists($path) || is_dir($path)) {
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
    $isBetaContext = (strpos($host, 'beta.') === 0 || preg_match('#/public_html/beta(/|$)#i', str_replace('\\', '/', __DIR__)));
    $encodedFile = implode('/', array_map('rawurlencode', explode('/', $file)));
    $remoteBase = $isBetaContext ? 'https://www.rtchocos.com/assets/products/' : 'https://beta.rtchocos.com/assets/products/';

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 8,
            'user_agent' => 'Mozilla/5.0 RTChocos-MediaProxy/1.0',
            'ignore_errors' => true
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $remoteData = @file_get_contents($remoteBase . $encodedFile, false, $ctx);
    if ($remoteData !== false && strlen($remoteData) > 300 && !str_starts_with($remoteData, '<!DOCTYPE') && !str_starts_with($remoteData, '<html')) {
        @mkdir(dirname($localPath), 0755, true);
        @file_put_contents($localPath, $remoteData);
        if (realpath($persistentPath) !== realpath($localPath)) {
            @mkdir(dirname($persistentPath), 0755, true);
            @file_put_contents($persistentPath, $remoteData);
        }
        $path = $localPath;
    }
}

if (!empty($path) && file_exists($path) && !is_dir($path)) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimes = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml'
    ];
    $mime = $mimes[$ext] ?? (function_exists('mime_content_type') ? @mime_content_type($path) : '') ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=31536000');
    readfile($path);
    exit;
}

header("HTTP/1.0 404 Not Found");
echo "Image not found";
