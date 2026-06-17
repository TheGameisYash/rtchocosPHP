<?php
// api_blogs.php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
require_once __DIR__ . '/includes/db.php';

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
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    error_log("Failed to fetch blog list from database: " . $e->getMessage() . ". Falling back to static array.");
    require_once __DIR__ . '/includes/blog-data.php';
    
    $response = [];
    $idx = 1;
    // Reverse the static list so latest blogs (freeze-dried-fruits, lecithin, etc.) appear first
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
