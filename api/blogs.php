<?php
// api/blogs.php - Blogs JSON API endpoint
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

$rootDir = dirname(__DIR__);
require_once $rootDir . '/includes/db.php';
require_once $rootDir . '/includes/blog-cache.php';

try {
    $pdo = get_db();
    
    // Fetch published blogs ordered by created_at DESC
    $stmt = $pdo->query("SELECT id, slug, title, category, created_at, excerpt, image_path, thumbnail_path, youtube_url, body_class, read_time FROM blogs WHERE is_published = 1 ORDER BY created_at DESC");
    $blogs = $stmt->fetchAll();
    
    $response = [];
    foreach ($blogs as $blog) {
        // Date formatting: e.g. "Jun 2026"
        $formattedDate = date('M Y', strtotime($blog['created_at']));
        
        $response[] = [
            'id' => (int)$blog['id'],
            'slug' => $blog['slug'],
            'title' => $blog['title'],
            'category' => $blog['category'],
            'date' => $formattedDate,
            'read_time' => $blog['read_time'] ?: '5 min',
            'excerpt' => $blog['excerpt'],
            'image' => $blog['image_path'],
            'thumbnail' => $blog['thumbnail_path'] ?: $blog['image_path'],
            'youtube_url' => $blog['youtube_url'],
            'body_class' => $blog['body_class'] ?: ''
        ];
    }
    
    // Save to cache
    cache_blog_list($response);
    header('X-Data-Source: database');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    error_log("Failed to fetch blog list from database: " . $e->getMessage() . ". Checking file cache fallback.");
    
    $cached = get_cached_blog_list();
    if ($cached !== null) {
        header('X-Data-Source: cache');
        echo json_encode($cached, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // Fall back to static array
    require_once $rootDir . '/includes/blog-data.php';
    header('X-Data-Source: static');
    
    $response = [];
    $idx = 1;
    $reversedBlogs = array_reverse($BLOGS, true);
    
    foreach ($reversedBlogs as $slug => $meta) {
        $response[] = [
            'id' => $idx++,
            'slug' => $slug,
            'title' => $meta['title'],
            'category' => $meta['category'],
            'date' => $meta['date'],
            'read_time' => $meta['read'] ?? '5 min',
            'excerpt' => $meta['excerpt'],
            'image' => $meta['image'],
            'thumbnail' => $meta['image'],
            'youtube_url' => $meta['youtube_url'] ?? null,
            'body_class' => $meta['bodyClass'] ?? ''
        ];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
?>
