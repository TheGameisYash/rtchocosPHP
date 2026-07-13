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

// Find document root parent
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
$parentDir = dirname($docRoot);
$livePath = $parentDir . '/uploads_blogs/' . $file;
$localPath = $docRoot . '/assets/blogs/' . $file;

$path = '';
if (file_exists($livePath)) {
    $path = $livePath;
} elseif (file_exists($localPath)) {
    $path = $localPath;
}

if (empty($path) || !file_exists($path) || is_dir($path)) {
    header("HTTP/1.0 404 Not Found");
    echo "Image not found.";
    exit;
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
