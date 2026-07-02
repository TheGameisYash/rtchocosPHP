<?php
// sitemap.php - Dynamic XML Sitemap Generator
header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/includes/db.php';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

$baseUrl = "https://www.rtchocos.com/";

// Static pages
$staticPages = [
    "" => ["priority" => "1.0", "changefreq" => "daily"],
    "about.php" => ["priority" => "0.8", "changefreq" => "monthly"],
    "workshops.php" => ["priority" => "0.9", "changefreq" => "weekly"],
    "blog.php" => ["priority" => "0.9", "changefreq" => "daily"],
    "gallery.php" => ["priority" => "0.8", "changefreq" => "weekly"],
    "assets/Recipes/Almondbutterchocolate.html" => ["priority" => "0.8", "changefreq" => "monthly"],
    "assets/Recipes/LimeChilliTruffles.html" => ["priority" => "0.8", "changefreq" => "monthly"],
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
    $stmt = $pdo->query("SELECT slug, title, image_path, updated_at FROM blogs WHERE is_published = 1 ORDER BY updated_at DESC");
    $dbBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($dbBlogs as $blog) {
        $lastmod = date('Y-m-d', strtotime($blog['updated_at']));
        echo "  <url>\n";
        echo "    <loc>" . $baseUrl . "blog/" . htmlspecialchars($blog['slug']) . "</loc>\n";
        echo "    <lastmod>" . $lastmod . "</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        if (!empty($blog['image_path'])) {
            $imgUrl = $baseUrl . ltrim($blog['image_path'], '/.');
            echo "    <image:image>\n";
            echo "      <image:loc>" . htmlspecialchars($imgUrl) . "</image:loc>\n";
            echo "      <image:title>" . htmlspecialchars($blog['title']) . "</image:title>\n";
            echo "    </image:image>\n";
        }
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
        if (!empty($meta['image'])) {
            $imgUrl = $baseUrl . ltrim($meta['image'], '/.');
            echo "    <image:image>\n";
            echo "      <image:loc>" . htmlspecialchars($imgUrl) . "</image:loc>\n";
            echo "      <image:title>" . htmlspecialchars($meta['title']) . "</image:title>\n";
            echo "    </image:image>\n";
        }
        echo "  </url>\n";
    }
}

echo '</urlset>' . "\n";
?>
