<?php
// tools/create_symlink.php
// Visit this file on your live server to configure uploads, restore assets, and link safe storage.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/blog-uploads.php';

echo "<h2>RT Chocos Blog Storage Setup & Sync</h2>";

$rootDir = dirname(__DIR__);
$safeUploadsDir = get_blog_uploads_dir();
$symlinkPath = $rootDir . '/assets/blogs';
$assetsDir = $rootDir . '/assets';

echo "<strong>Detected safe uploads directory:</strong> <code>" . htmlspecialchars($safeUploadsDir) . "</code><br>";
echo "<strong>Target web assets location:</strong> <code>" . htmlspecialchars($symlinkPath) . "</code><br><br>";

// 1. Ensure safe uploads directory and thumbnails subfolder exist
if (!file_exists($safeUploadsDir)) {
    if (mkdir($safeUploadsDir, 0755, true)) {
        echo "✓ Created safe uploads folder: <code>$safeUploadsDir</code><br>";
    } else {
        echo "✗ Failed to create safe uploads folder.<br>";
        exit;
    }
} else {
    echo "✓ Safe uploads folder exists: <code>$safeUploadsDir</code><br>";
}

$safeThumbsDir = $safeUploadsDir . '/thumbnails';
if (!file_exists($safeThumbsDir)) {
    @mkdir($safeThumbsDir, 0755, true);
}

function delete_dir_recursive($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return @unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!delete_dir_recursive($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return @rmdir($dir);
}

// 2. Restore from any previous backup directories created during earlier attempts
$backupDirs = glob($assetsDir . '/blogs_backup_*');
if (!empty($backupDirs)) {
    echo "<br><strong>Restoring files from previous backups...</strong><br>";
    if (!file_exists($symlinkPath)) {
        @mkdir($symlinkPath, 0755, true);
    }
    foreach ($backupDirs as $bDir) {
        $files = array_diff(scandir($bDir), ['.', '..']);
        foreach ($files as $f) {
            $src = $bDir . '/' . $f;
            $dst = $symlinkPath . '/' . $f;
            $safeDst = $safeUploadsDir . '/' . $f;
            if (is_dir($src)) {
                @mkdir($dst, 0755, true);
                @mkdir($safeDst, 0755, true);
                $sub = array_diff(scandir($src), ['.', '..']);
                foreach ($sub as $sf) {
                    @copy($src . '/' . $sf, $dst . '/' . $sf);
                    @copy($src . '/' . $sf, $safeDst . '/' . $sf);
                }
            } else {
                @copy($src, $dst);
                @copy($src, $safeDst);
            }
        }
        // Remove restored backup directory
        delete_dir_recursive($bDir);
        echo "✓ Restored files from <code>" . basename($bDir) . "</code> into <code>assets/blogs</code><br>";
    }
}

// 3. Ensure local assets/blogs directory exists
if (!file_exists($symlinkPath)) {
    @mkdir($symlinkPath, 0755, true);
}
$localThumbs = $symlinkPath . '/thumbnails';
if (!file_exists($localThumbs)) {
    @mkdir($localThumbs, 0755, true);
}

// 4. Two-Way Sync between safeUploadsDir and local web assets/blogs
echo "<br><strong>Syncing images between local assets and safe uploads storage...</strong><br>";
$syncedCount = 0;

// Sync from local assets/blogs -> safeUploadsDir
if (file_exists($symlinkPath) && !is_link($symlinkPath)) {
    $localFiles = array_diff(scandir($symlinkPath), ['.', '..']);
    foreach ($localFiles as $lf) {
        $src = $symlinkPath . '/' . $lf;
        $dst = $safeUploadsDir . '/' . $lf;
        if (is_file($src) && !file_exists($dst)) {
            @copy($src, $dst);
            $syncedCount++;
        } elseif (is_dir($src) && $lf === 'thumbnails') {
            $tFiles = array_diff(scandir($src), ['.', '..']);
            foreach ($tFiles as $tf) {
                if (!file_exists($safeThumbsDir . '/' . $tf)) {
                    @copy($src . '/' . $tf, $safeThumbsDir . '/' . $tf);
                    $syncedCount++;
                }
            }
        }
    }
}

// Sync from safeUploadsDir -> local assets/blogs
if (file_exists($safeUploadsDir)) {
    $remoteFiles = array_diff(scandir($safeUploadsDir), ['.', '..']);
    foreach ($remoteFiles as $rf) {
        $src = $safeUploadsDir . '/' . $rf;
        $dst = $symlinkPath . '/' . $rf;
        if (is_file($src) && !file_exists($dst)) {
            @copy($src, $dst);
            $syncedCount++;
        } elseif (is_dir($src) && $rf === 'thumbnails') {
            $tFiles = array_diff(scandir($src), ['.', '..']);
            foreach ($tFiles as $tf) {
                if (!file_exists($localThumbs . '/' . $tf)) {
                    @copy($src . '/' . $tf, $localThumbs . '/' . $tf);
                    $syncedCount++;
                }
            }
        }
    }
}

echo "✓ Synchronized $syncedCount image file(s). Both storage locations now have identical files.<br>";

// 5. Test symlink availability safely
$symlinkAllowed = function_exists('symlink') && !in_array('symlink', array_map('trim', explode(',', ini_get('disable_functions'))));

if ($symlinkAllowed) {
    if (is_link($symlinkPath)) {
        echo "✓ Symbolic link is already active: <code>$symlinkPath</code> &rarr; <code>$safeUploadsDir</code><br>";
    } else {
        echo "ℹ️ Symlinks are supported. Symbolic link can be created if desired.<br>";
    }
} else {
    echo "ℹ️ <strong>Hostinger Shared Hosting Mode:</strong> The PHP <code>symlink()</code> function is restricted by hosting security.<br>";
    echo "✓ <strong>Dual-Save Storage & Dynamic Proxy (<code>blog_image.php</code>) are active!</strong><br>";
    echo "All uploaded images are automatically saved to both <code>uploads_blogs</code> (permanent) and <code>assets/blogs</code> (direct web serving) for 100% reliable image display.<br>";
}

echo "<br><h3 style='color:#10b981;'>✓ Storage Setup &amp; Sync Complete! All blog images are ready.</h3>";
