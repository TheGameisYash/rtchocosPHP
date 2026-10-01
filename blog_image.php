<?php
// blog_image.php - Serves blog images stored outside public_html (live) or in assets/blogs (local)
// This file is dynamically called by .htaccess when a requested image is missing in assets/blogs/

$file = $_GET['file'] ?? '';

// Prevent directory traversal attacks
$file = str_replace(['../', '..\\'], '', $file);
$file = ltrim($file, '/\\');

if (empty($file)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

require_once __DIR__ . '/includes/blog-uploads.php';

$uploadsDir = get_blog_uploads_dir();
$livePath = $uploadsDir . '/' . $file;
$localPath = __DIR__ . '/assets/blogs/' . $file;

$path = '';
if (file_exists($livePath) && !is_dir($livePath)) {
    // Cache it into localPath so future requests are served directly by web server!
    @mkdir(dirname($localPath), 0755, true);
    @copy($livePath, $localPath);
    $path = $livePath;
} elseif (file_exists($localPath) && !is_dir($localPath)) {
    $path = $localPath;
}

if (empty($path) || !file_exists($path) || is_dir($path)) {
    // Attempt to download and cache from live production website
    $liveUrl = 'https://www.rtchocos.com/assets/blogs/' . $file;
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'Mozilla/5.0 RTChocos-MediaProxy/1.0',
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $remoteData = @file_get_contents($liveUrl, false, $ctx);
    if ($remoteData !== false && strlen($remoteData) > 300 && !str_starts_with($remoteData, '<!DOCTYPE') && !str_starts_with($remoteData, '<html')) {
        @mkdir(dirname($localPath), 0777, true);
        @file_put_contents($localPath, $remoteData);
        $path = $localPath;
    } else {
        // Fallback to placeholder image if available
        $placeholder = __DIR__ . '/assets/images/placeholder.jpg';
        if (file_exists($placeholder)) {
            $path = $placeholder;
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "Image not found.";
            exit;
        }
    }
}

// Get the file extension to set appropriate Content-Type header
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
    'ico'  => 'image/x-icon'
];

$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

header("Content-Type: $contentType");
header("Content-Length: " . filesize($path));
header("Cache-Control: public, max-age=31536000"); // Cache for 1 year
readfile($path);
exit;
?>
