<?php
// product_image.php - Serves product images and auto-recovers missing files between main and beta
$file = $_GET['file'] ?? '';

// Prevent directory traversal attacks
$file = str_replace(['../', '..\\'], '', $file);
$file = ltrim($file, '/\\');

if (empty($file)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$localPath = __DIR__ . '/assets/products/' . $file;
$betaPath  = __DIR__ . '/beta/assets/products/' . $file;
$mainPath  = dirname(__DIR__) . '/assets/products/' . $file;

$path = '';

if (file_exists($localPath) && !is_dir($localPath)) {
    $path = $localPath;
} elseif (file_exists($betaPath) && !is_dir($betaPath)) {
    // Found in beta, copy to local
    @mkdir(dirname($localPath), 0755, true);
    @copy($betaPath, $localPath);
    $path = $betaPath;
} elseif (file_exists($mainPath) && !is_dir($mainPath)) {
    // Found in main, copy to local
    @mkdir(dirname($localPath), 0755, true);
    @copy($mainPath, $localPath);
    $path = $mainPath;
}

if (empty($path) || !file_exists($path) || is_dir($path)) {
    // Try to fetch from beta URL if running on main
    $remoteUrl = 'https://www.rtchocos.com/beta/assets/products/' . $file;
    $ctx = stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $remoteData = @file_get_contents($remoteUrl, false, $ctx);
    if ($remoteData !== false && strlen($remoteData) > 500 && !str_starts_with($remoteData, '<!DOCTYPE') && !str_starts_with($remoteData, '<html')) {
        @mkdir(dirname($localPath), 0755, true);
        @file_put_contents($localPath, $remoteData);
        $path = $localPath;
    }
}

if (!empty($path) && file_exists($path) && !is_dir($path)) {
    $mime = mime_content_type($path);
    if (!$mime) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimes = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp'];
        $mime = $mimes[$ext] ?? 'application/octet-stream';
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: public, max-age=31536000');
    readfile($path);
    exit;
}

header("HTTP/1.0 404 Not Found");
echo "Image not found";
