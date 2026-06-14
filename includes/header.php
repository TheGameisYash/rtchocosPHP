<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $pageTitle; ?></title>
<meta name="description" content="<?php echo $pageDescription; ?>" />
<link rel="icon" type="image/png" href="<?php echo $pathPrefix; ?>assets/favicon.png" />
<link rel="apple-touch-icon" href="<?php echo $pathPrefix; ?>assets/favicon.png" />

<meta name="keywords" content="chocolate blog India, bean to bar chocolate India, craft chocolate articles, cocoa science India, chocolate making blog, RT Chocos blog" />
<meta property="og:title" content="Chocolate Blog India | RT Chocos" />
<meta property="og:description" content="Expert chocolate articles on bean-to-bar making, cocoa origins, and craft chocolate in India." />
<meta property="og:url" content="https://www.rtchocos.com/blog.html" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Dancing+Script:wght@700&family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css?v=<?php echo filemtime(__DIR__ . '/../style.css'); ?>">
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
