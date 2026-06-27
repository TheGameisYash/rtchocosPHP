<?php
// includes/blog-cache.php - Cache management for blogs fallback

function get_cache_dir() {
    $dir = __DIR__ . '/../data/cache';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        
        // Add .htaccess to secure cache directory
        $htaccessFile = $dir . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Deny from all");
        }
    }
    return $dir;
}

function cache_blog_list($blogs) {
    try {
        $filePath = get_cache_dir() . '/blogs.json';
        file_put_contents($filePath, json_encode($blogs, JSON_UNESCAPED_SLASHES));
        return true;
    } catch (Exception $e) {
        error_log("Failed to write blog list cache: " . $e->getMessage());
        return false;
    }
}

function get_cached_blog_list($ttl = 3600) {
    try {
        $filePath = get_cache_dir() . '/blogs.json';
        if (file_exists($filePath) && (time() - filemtime($filePath) < $ttl)) {
            $data = file_get_contents($filePath);
            return json_decode($data, true);
        }
    } catch (Exception $e) {
        error_log("Failed to read blog list cache: " . $e->getMessage());
    }
    return null;
}

function cache_blog_article($slug, $data) {
    try {
        $filePath = get_cache_dir() . '/article-' . preg_replace('/[^a-z0-9\-]/', '', $slug) . '.json';
        file_put_contents($filePath, json_encode($data, JSON_UNESCAPED_SLASHES));
        return true;
    } catch (Exception $e) {
        error_log("Failed to write article cache for '$slug': " . $e->getMessage());
        return false;
    }
}

function get_cached_blog_article($slug, $ttl = 3600) {
    try {
        $filePath = get_cache_dir() . '/article-' . preg_replace('/[^a-z0-9\-]/', '', $slug) . '.json';
        if (file_exists($filePath) && (time() - filemtime($filePath) < $ttl)) {
            $data = file_get_contents($filePath);
            return json_decode($data, true);
        }
    } catch (Exception $e) {
        error_log("Failed to read article cache for '$slug': " . $e->getMessage());
    }
    return null;
}
?>
