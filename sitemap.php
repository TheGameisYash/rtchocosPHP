<?php
// sitemap.php - Dynamic XML Sitemap Generator
header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/includes/db.php';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$baseUrl = "https://www.rtchocos.com/";

// Static pages
$staticPages = [
    "" => ["priority" => "1.0", "changefreq" => "daily"],
    "about.php" => ["priority" => "0.8", "changefreq" => "monthly"],
    "workshops.php" => ["priority" => "0.9", "changefreq" => "weekly"],
    "blog.php" => ["priority" => "0.9", "changefreq" => "daily"],
    "gallery.php" => ["priority" => "0.8", "changefreq" => "weekly"],
    "contact.php" => ["priority" => "0.7", "changefreq" => "monthly"]
];

foreach ($staticPages as $page => $meta) {
    echo "  <url>\n";
    echo "    <loc>" . $baseUrl . $page . "</loc>\n";
    echo "    <changefreq>" . $meta['changefreq'] . "</changefreq>\n";
    echo "    <priority>" . $meta['priority'] . "</priority>\n";
    echo "  </url>\n";
}

// Blog articles
try {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT slug, updated_at FROM blogs WHERE is_published = 1 ORDER BY updated_at DESC");
    $dbBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dbBlogs as $blog) {
        $lastmod = date('Y-m-d', strtotime($blog['updated_at']));
        echo "  <url>\n";
        echo "    <loc>" . $baseUrl . "blog/" . htmlspecialchars($blog['slug']) . "</loc>\n";
        echo "    <lastmod>" . $lastmod . "</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        echo "  </url>\n";
    }
} catch (Exception $e) {
    // Database failed fallback: static array
    require_once __DIR__ . '/includes/blog-data.php';
    foreach ($BLOGS as $slug => $meta) {
        echo "  <url>\n";
        echo "    <loc>" . $baseUrl . "blog/" . htmlspecialchars($slug) . "</loc>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        echo "  </url>\n";
    }
}

echo '</urlset>' . "\n";
?>
