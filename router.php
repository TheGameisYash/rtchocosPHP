<?php
// router.php — Dev-only router for PHP built-in server.
// Emulates .htaccess rewrite rules locally for clean URLs, sitemap, blog articles, and listicles.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Enforce trailing slash on /admin directory for correct relative path resolution
if ($uri === '/admin') {
    header('Location: /admin/');
    return true;
}

// Route direct /login and /login.php navigation to admin login
if ($uri === '/login' || $uri === '/login.php') {
    header('Location: /admin/login.php');
    return true;
}

// If it's a real file or directory (except physical /blog folder root request), serve it directly
if ($uri !== '/' && $uri !== '/blog' && $uri !== '/blog/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route physical /blog folder request to blog.php
if ($uri === '/blog' || $uri === '/blog/') {
    include __DIR__ . '/blog.php';
    return true;
}

// Route physical /shop folder request or clean /shop to shop.php
if ($uri === '/shop' || $uri === '/shop/') {
    include __DIR__ . '/shop.php';
    return true;
}

// Match the production sitemap rewrite during local verification.
if ($uri === '/sitemap.xml') {
    include __DIR__ . '/sitemap.php';
    return true;
}

// Serve static files requested from subpaths like /shop/style.css, /blog/style.css, /shop/assets/..., /blog/js/...
if (preg_match('#^/(?:shop|blog)/((?:style\.css|script\.js|favicon\.[a-z]+|(?:css|js|assets|data)/.+))$#', $uri, $m)) {
    $realStaticPath = __DIR__ . '/' . $m[1];
    if (file_exists($realStaticPath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'ico' => 'image/x-icon'
        ];
        $ext = strtolower(pathinfo($realStaticPath, PATHINFO_EXTENSION));
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($realStaticPath);
        return true;
    }
}

// Route API & contact requests from subpaths like /shop/api_cart.php, /shop/send_contact.php
if (preg_match('#^/(?:shop|blog)/((?:api_[a-zA-Z0-9_\-]+|send_contact)\.php)$#', $uri, $m)) {
    $apiFile = __DIR__ . '/' . $m[1];
    if (file_exists($apiFile)) {
        include $apiFile;
        return true;
    }
}

// Route clean /cart and /checkout URLs
if ($uri === '/cart' || $uri === '/cart/') {
    include __DIR__ . '/cart.php';
    return true;
}

if ($uri === '/checkout' || $uri === '/checkout/') {
    include __DIR__ . '/checkout.php';
    return true;
}

if (preg_match('#^/(?:shop|blog)/(cart|checkout)\.php$#', $uri, $m)) {
    include __DIR__ . '/' . $m[1] . '.php';
    return true;
}

// Local clean URL mapping (e.g. /about -> about.php, /workshops -> workshops.php, /chocopedia -> chocopedia.php)
if ($uri !== '/' && preg_match('#^/([^/]+)$#', $uri, $m)) {
    $file = __DIR__ . '/' . $m[1] . '.php';
    if (file_exists($file)) {
        include $file;
        return true;
    }
}

// Route clean blog URLs: /blog/{slug} → set slug and include article logic
if (preg_match('#^/blog/([^/]+)/?$#', $uri, $m)) {
    $articleKey = $m[1];
    include __DIR__ . '/blog-article.php';
    return true;
}

// Route clean shop product URLs: /shop/{slug}
if (preg_match('#^/shop/([a-zA-Z0-9_\-]+)/?$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    include __DIR__ . '/product.php';
    return true;
}

// Route clean listicle URL
if (preg_match('#^/indian-chocolate-brands/?$#', $uri)) {
    include __DIR__ . '/brand-listicle.php';
    return true;
}

// Route missing blog images to blog_image.php
if (preg_match('#^/assets/blogs/(.+)$#', $uri, $m)) {
    $_GET['file'] = $m[1];
    include __DIR__ . '/blog_image.php';
    return true;
}

// Default: serve the requested URI as-is via the built-in server
return false;
?>
