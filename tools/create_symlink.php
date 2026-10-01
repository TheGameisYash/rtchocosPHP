<?php
// tools/create_symlink.php
// Visit this file once on your live server to link your uploads folder outside public_html.

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/blog-uploads.php';

echo "<h2>RT Chocos Symbolic Link Setup</h2>";

$rootDir = dirname(__DIR__);
$safeUploadsDir = get_blog_uploads_dir();
$symlinkPath = $rootDir . '/assets/blogs';

echo "<strong>Detected safe uploads directory:</strong> <code>" . htmlspecialchars($safeUploadsDir) . "</code><br>";
echo "<strong>Target symlink location:</strong> <code>" . htmlspecialchars($symlinkPath) . "</code><br><br>";

// 1. Create the safe uploads directory outside public_html if it doesn't exist
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

// 2. Handle existing assets/blogs folder
if (file_exists($symlinkPath)) {
    if (is_link($symlinkPath)) {
        $currentTarget = readlink($symlinkPath);
        echo "✓ Symbolic link already exists at <code>$symlinkPath</code> &rarr; <code>$currentTarget</code><br>";
        if (realpath($symlinkPath) === realpath($safeUploadsDir)) {
            echo "<h3>Setup is already complete and correctly linked!</h3>";
            exit;
        } else {
            echo "⚠️ Link points to a different directory. Removing old link to recreate...<br>";
            @unlink($symlinkPath);
        }
    } else {
        $doMigrate = isset($_GET['action']) && $_GET['action'] === 'migrate';
        $items = array_diff(scandir($symlinkPath), ['.', '..']);
        
        if (empty($items)) {
            @rmdir($symlinkPath);
        } elseif ($doMigrate) {
            echo "Migrating existing files into <code>$safeUploadsDir</code>...<br>";
            foreach ($items as $item) {
                $src = $symlinkPath . '/' . $item;
                $dst = $safeUploadsDir . '/' . $item;
                if (is_dir($src)) {
                    @mkdir($dst, 0755, true);
                    $subItems = array_diff(scandir($src), ['.', '..']);
                    foreach ($subItems as $si) {
                        @copy($src . '/' . $si, $dst . '/' . $si);
                    }
                } else {
                    @copy($src, $dst);
                }
            }
            $backupPath = $symlinkPath . '_backup_' . time();
            if (@rename($symlinkPath, $backupPath)) {
                echo "✓ Backed up old folder to <code>$backupPath</code><br>";
            } else {
                echo "⚠️ Could not auto-rename old folder. Please delete or rename <code>$symlinkPath</code> in File Manager.<br>";
                exit;
            }
        } else {
            echo "⚠️ An actual folder exists at <code>$symlinkPath</code> with " . count($items) . " files/folders.<br><br>";
            echo "<p><a href='?action=migrate' style='background:#10b981;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;font-weight:600;display:inline-block;'>⚡ Automatically Migrate Files & Create Symlink</a></p>";
            echo "Or manually move any images inside <code>$symlinkPath</code> to <code>$safeUploadsDir</code>, delete the folder, and refresh this page.<br>";
            exit;
        }
    }
}

// 3. Create the symlink
if (!file_exists($symlinkPath)) {
    if (@symlink($safeUploadsDir, $symlinkPath)) {
        echo "✓ <strong>Successfully created symbolic link:</strong> <code>$symlinkPath</code> &rarr; <code>$safeUploadsDir</code><br>";
        echo "<h3>Setup Completed Successfully!</h3>";
    } else {
        echo "⚠️ <code>symlink()</code> function is not permitted on this server configuration.<br>";
        echo "Good news: <strong>The system includes automatic dynamic streaming via <code>blog_image.php</code></strong>. All images stored in <code>$safeUploadsDir</code> will still display perfectly without needing a symlink!<br>";
    }
}
