<?php
// router.php — Dev-only router for PHP built-in server.
// Emulates .htaccess rewrite: blog/{slug} → blog/article.php?slug={slug}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Explicitly handle clean blog list page requests since 'blog' is a physical directory
if ($uri === '/blog' || $uri === '/blog/') {
    include __DIR__ . '/blog.php';
    return true;
}

// If it's a real file or directory, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Local clean URL mapping (e.g. /about -> about.php)
if ($uri !== '/' && preg_match('#^/([^/]+)$#', $uri, $m)) {
    $file = __DIR__ . '/' . $m[1] . '.php';
    if (file_exists($file)) {
        include $file;
        return true;
    }
}

// Blog clean URL rewrite: /blog/{slug} → set slug and include article logic
if (preg_match('#^/blog/([^/]+)$#', $uri, $m)) {
    $articleKey = $m[1];
    // Directly include the article template (which blog/article.php would include)
    include __DIR__ . '/blog-article.php';
    return true;
}

// Shop category/main routing: /shop
if (preg_match('#^/shop/?$#', $uri)) {
    include __DIR__ . '/shop.php';
    return true;
}

// Product detail routing: /shop/{slug} → set slug and include product logic
if (preg_match('#^/shop/([^/]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    include __DIR__ . '/product.php';
    return true;
}

// Route missing blog images to blog_image.php
if (preg_match('#^/assets/blogs/(.+)$#', $uri, $m)) {
    if (!file_exists(__DIR__ . $uri)) {
        $_GET['file'] = $m[1];
        include __DIR__ . '/blog_image.php';
        return true;
    }
}

// Route clean listicle URL
if (preg_match('#^/indian-chocolate-brands/?$#', $uri)) {
    include __DIR__ . '/brand-listicle.php';
    return true;
}

// Clean API routing: /api/{endpoint} -> api/{endpoint}.php
if (preg_match('#^/api/([^/]+)$#', $uri, $m)) {
    $apiFile = __DIR__ . '/api/' . $m[1] . '.php';
    if (file_exists($apiFile)) {
        include $apiFile;
        return true;
    }
}

// Default: serve the requested URI as-is via the built-in server
return false;
?>
