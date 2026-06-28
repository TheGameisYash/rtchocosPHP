<?php
// router.php — Dev-only router for PHP built-in server.
// Emulates .htaccess rewrite: blog/{slug} → blog/article.php?slug={slug}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If it's a real file or directory, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Blog clean URL rewrite: /blog/{slug} → set slug and include article logic
if (preg_match('#^/blog/([^/]+)$#', $uri, $m)) {
    $articleKey = $m[1];
    // Directly include the article template (which blog/article.php would include)
    include __DIR__ . '/blog-article.php';
    return true;
}

// Default: serve the requested URI as-is via the built-in server
return false;
?>
