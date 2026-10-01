<?php
// tools/create_symlink.php
// Visit this file once on your live server to link your uploads folder outside public_html.

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>RT Chocos Symbolic Link Setup</h2>";

$rootDir = dirname(__DIR__);
$parentDir = dirname($rootDir);

// Target directory outside public_html (where images will be safely stored)
$safeUploadsDir = $parentDir . '/uploads_blogs';
// Symlink path inside public_html
$symlinkPath = $rootDir . '/assets/blogs';

// 1. Create the safe uploads directory outside public_html if it doesn't exist
if (!file_exists($safeUploadsDir)) {
    if (mkdir($safeUploadsDir, 0755, true)) {
        echo "✓ Created safe uploads folder outside public_html: <code>$safeUploadsDir</code><br>";
    } else {
        echo "✗ Failed to create safe uploads folder.<br>";
        exit;
    }
} else {
    echo "✓ Safe uploads folder already exists: <code>$safeUploadsDir</code><br>";
}

// 2. Handle existing assets/blogs folder
if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        echo "✓ Symbolic link already exists at <code>$symlinkPath</code><br>";
        echo "<h3>Setup is already complete!</h3>";
        exit;
    } else {
        echo "⚠️ An actual folder exists at <code>$symlinkPath</code>.<br>";
        echo "Please log in to your Hostinger File Manager, move any images inside <code>public_html/assets/blogs/</code> to <code>$safeUploadsDir</code>, and then delete the empty <code>blogs</code> folder from <code>public_html/assets/</code>.<br>";
        echo "After deleting the folder, refresh this page to complete the setup.<br>";
        exit;
    }
}

// 3. Create the symlink
if (symlink($safeUploadsDir, $symlinkPath)) {
    echo "✓ Successfully created symbolic link: <code>$symlinkPath</code> → <code>$safeUploadsDir</code><br>";
    echo "<h3>Setup Completed Successfully!</h3>";
} else {
    echo "✗ Failed to create symbolic link. Please ensure your hosting account supports symlinks.<br>";
}
?>
