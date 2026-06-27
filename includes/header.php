<?php
// Determine canonical URL if not set
if (empty($canonicalUrl)) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Fallback OG description and image
$ogDescription = !empty($pageDescription) ? $pageDescription : "India's chocolate blog for makers, learners, and enthusiasts. Bean-to-bar making, cocoa science, recipes, and workshops.";
$ogImage = !empty($pageImage) ? $pageImage : (isset($pathPrefix) ? $pathPrefix : "") . "assets/logo.png";
if (strpos($ogImage, 'http') !== 0) {
    // Make absolute URL
    $ogImage = "https://www.rtchocos.com/" . ltrim($ogImage, '/.');
}
$ogTitle = !empty($pageTitle) ? $pageTitle : "RT Chocos | India's Chocolate Blog & Learning";
$ogType = !empty($pageType) ? $pageType : "website";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $pageTitle; ?></title>
<meta name="description" content="<?php echo $pageDescription; ?>" />
<link rel="icon" type="image/png" href="<?php echo $pathPrefix; ?>assets/favicon.png" />
<link rel="apple-touch-icon" href="<?php echo $pathPrefix; ?>assets/favicon.png" />
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>" />

<meta name="keywords" content="chocolate blog India, bean to bar chocolate India, craft chocolate articles, cocoa science India, chocolate making blog, RT Chocos blog" />
<meta property="og:title" content="<?php echo htmlspecialchars($ogTitle); ?>" />
<meta property="og:description" content="<?php echo htmlspecialchars($ogDescription); ?>" />
<meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>" />
<meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>" />
<meta property="og:type" content="<?php echo htmlspecialchars($ogType); ?>" />
<meta property="og:site_name" content="RT Chocos" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?php echo htmlspecialchars($ogTitle); ?>" />
<meta name="twitter:description" content="<?php echo htmlspecialchars($ogDescription); ?>" />
<meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>" />

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link rel="dns-prefetch" href="https://fonts.googleapis.com" />
<link rel="dns-prefetch" href="https://www.youtube.com" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Dancing+Script:wght@700&family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css">

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://www.rtchocos.com/#organization",
      "name": "RT Chocos",
      "url": "https://www.rtchocos.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://www.rtchocos.com/assets/logo.png"
      },
      "sameAs": [
        "https://www.instagram.com/rt.chocos/",
        "https://www.youtube.com/@RTCHOCOS",
        "https://www.facebook.com/rtchocos"
      ]
    },
    {
      "@type": "WebSite",
      "@id": "https://www.rtchocos.com/#website",
      "url": "https://www.rtchocos.com/",
      "name": "RT Chocos",
      "publisher": {
        "@id": "https://www.rtchocos.com/#organization"
      }
    }
    <?php if (!empty($pageType) && $pageType === 'article' && !empty($post)): ?>
    ,{
      "@type": "BlogPosting",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?php echo htmlspecialchars($canonicalUrl); ?>"
      },
      "headline": "<?php echo htmlspecialchars($post['title']); ?>",
      "description": "<?php echo htmlspecialchars($post['excerpt']); ?>",
      "image": "<?php echo htmlspecialchars($ogImage); ?>",
      "datePublished": "<?php echo date('c', strtotime($dbPost['created_at'] ?? 'now')); ?>",
      "dateModified": "<?php echo date('c', strtotime($dbPost['updated_at'] ?? 'now')); ?>",
      "author": {
        "@type": "Person",
        "name": "Aarti Saluja Sahni",
        "url": "https://www.rtchocos.com/about.php"
      },
      "publisher": {
        "@id": "https://www.rtchocos.com/#organization"
      }
    }
    <?php endif; ?>
  ]
}
</script>
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>
<!-- --- HEADER --- -->
<header id="site-header">
  <div class="header-inner">
    <a href="<?php echo $pathPrefix; ?>index.php" class="logo">
      <img src="<?php echo $pathPrefix; ?>assets/logo.png" class="logo-img logo-img-header" alt="RT Chocos Logo" />
    </a>
    <div class="header-nav-left">
      <a class="nav-link" data-page="home" href="<?php echo $pathPrefix; ?>index.php">Home</a>
      <a class="nav-link" data-page="about" href="<?php echo $pathPrefix; ?>about.php">About</a>
      <a class="nav-link" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php">Workshops</a>
    </div>
    <div class="header-nav-right">
      <a class="nav-link" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php">Blog</a>
      <a class="nav-link" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php">Recipes</a>
      <a class="nav-link" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
      <button class="search-btn" aria-label="Search" onclick="openSearch()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon-svg">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>
    </div>
    <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div id="mobile-menu">
    <a class="mobile-nav-link" data-page="home" href="<?php echo $pathPrefix; ?>index.php">Home</a>
    <a class="mobile-nav-link" data-page="about" href="<?php echo $pathPrefix; ?>about.php">About</a>
    <a class="mobile-nav-link" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php">Workshops</a>
    <a class="mobile-nav-link" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php">Blog</a>
    <a class="mobile-nav-link" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php">Recipes</a>
    <a class="mobile-nav-link" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
  </div>
</header>
