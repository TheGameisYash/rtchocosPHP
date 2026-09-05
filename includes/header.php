<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cartCount = (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;

require_once __DIR__ . '/db.php';
// Canonicals always point to the public HTTPS URL, never to a preview host or query string.
$siteUrl = "https://www.rtchocos.com";
if (empty($canonicalUrl)) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $requestPath = $requestPath === '/index.php' ? '/' : $requestPath;
    $canonicalUrl = $siteUrl . $requestPath;
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

$adminSiteTheme = 'theme-cream-forest';
try {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'default_site_theme' LIMIT 1");
    $dbTheme = $stmt->fetchColumn();
    if (!empty($dbTheme)) {
        $adminSiteTheme = $dbTheme;
    }
} catch (Exception $e) {
    // Fallback
}
?>
<!DOCTYPE html>
<html lang="en-IN" class="<?php echo htmlspecialchars($adminSiteTheme, ENT_QUOTES, 'UTF-8'); ?>">
<head>
<script>
  var pathPrefix = "<?php echo isset($pathPrefix) ? addslashes($pathPrefix) : ''; ?>";
  (function() {
    const savedTheme = localStorage.getItem('rtchocos-color-theme');
    if (savedTheme) {
      document.documentElement.className = savedTheme;
    }
  })();
</script>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>" />
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />
<?php if (!empty($pageKeywords)): ?>
<meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords, ENT_QUOTES, 'UTF-8'); ?>" />
<?php else: ?>
<meta name="keywords" content="chocolate blog india, chocolate academy india, indian bean to bar chocolate, bean to bar chocolate, chocolate course india, chocolate workshops india, learn chocolate making india, cocoa science blog india, craft chocolate india, chocolate education india, chocolate blogging india, tempering chocolate course, chocolate consultant Mumbai, RT Chocos" />
<?php endif; ?>
<?php if (!empty($pageType) && $pageType === 'article'): ?>
<meta name="author" content="Aarti Saluja Sahni" />
<?php endif; ?>
<link rel="icon" type="image/png" href="<?php echo $pathPrefix; ?>assets/favicon.png" />
<link rel="apple-touch-icon" href="<?php echo $pathPrefix; ?>assets/favicon.png" />
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>" />

<meta name="geo.region" content="IN-MH" />
<meta name="geo.placename" content="Mumbai" />
<meta property="og:locale" content="en_IN" />

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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css?v=<?php echo filemtime(__DIR__ . '/../style.css'); ?>">
<script src="<?php echo $pathPrefix; ?>js/anime.min.js"></script>

<!-- JSON-LD Structured Data -->
<?php
$graph = [
    [
        "@type" => "Organization",
        "@id" => "https://www.rtchocos.com/#organization",
        "name" => "RT Chocos",
        "url" => "https://www.rtchocos.com/",
        "description" => "An independent Indian chocolate learning platform covering bean-to-bar craft, cocoa science, recipes and workshops.",
        "logo" => [
            "@type" => "ImageObject",
            "url" => "https://www.rtchocos.com/assets/logo.png"
        ],
        "founder" => ["@id" => "https://www.rtchocos.com/#aarti-saluja-sahni"],
        "contactPoint" => [
            "@type" => "ContactPoint",
            "contactType" => "customer support",
            "telephone" => "+91-91402-38741",
            "email" => "hello@rtchocos.com",
            "areaServed" => "IN"
        ],
        "sameAs" => [
            "https://www.instagram.com/rt.chocos/",
            "https://www.youtube.com/@RTCHOCOS",
            "https://www.facebook.com/rtchocos"
        ]
    ],
    [
        "@type" => "Person",
        "@id" => "https://www.rtchocos.com/#aarti-saluja-sahni",
        "name" => "Aarti Saluja Sahni",
        "url" => "https://www.rtchocos.com/about",
        "image" => "https://www.rtchocos.com/assets/myphoto.jpg",
        "jobTitle" => "Chocolate maker, recipe developer and educator",
        "worksFor" => ["@id" => "https://www.rtchocos.com/#organization"],
        "sameAs" => ["https://www.linkedin.com/in/aarti-saluja-sahni-8304637/"]
    ],
    [
        "@type" => "WebSite",
        "@id" => "https://www.rtchocos.com/#website",
        "url" => "https://www.rtchocos.com/",
        "name" => "RT Chocos",
        "alternateName" => "RT Chocos Chocolate Blog",
        "inLanguage" => "en-IN",
        "publisher" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ],
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => "https://www.rtchocos.com/blog?s={search_term_string}",
            "query-input" => "required name=search_term_string"
        ]
    ]
];

$graph[] = [
    "@type" => !empty($schemaType) ? $schemaType : "WebPage",
    "@id" => $canonicalUrl . "#webpage",
    "url" => $canonicalUrl,
    "name" => $pageTitle,
    "description" => $pageDescription,
    "inLanguage" => "en-IN",
    "isPartOf" => ["@id" => "https://www.rtchocos.com/#website"],
    "about" => ["@id" => "https://www.rtchocos.com/#organization"]
];

if (!empty($pageType) && $pageType === 'article' && !empty($post)) {
    $graph[] = [
        "@type" => "BlogPosting",
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => $canonicalUrl
        ],
        "headline" => $post['title'],
        "description" => $post['excerpt'],
        "image" => $ogImage,
        "datePublished" => date('c', strtotime($post['published'] ?? $dbPost['created_at'] ?? '2026-01-01')),
        "dateModified" => date('c', strtotime($post['modified'] ?? $dbPost['updated_at'] ?? $post['published'] ?? '2026-01-01')),
        "author" => [
            "@id" => "https://www.rtchocos.com/#aarti-saluja-sahni"
        ],
        "publisher" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ]
    ];
}

if (!empty($itemList)) {
    $graph[] = [
        "@type" => "ItemList",
        "name" => $itemList['name'],
        "itemListElement" => array_map(function($item, $index) {
            return [
                "@type" => "ListItem",
                "position" => $index + 1,
                "name" => $item['name'],
                "url" => $item['url']
            ];
        }, $itemList['items'], array_keys($itemList['items']))
    ];
}

if (!empty($faqItems)) {
    $graph[] = [
        "@type" => "FAQPage",
        "mainEntity" => array_map(function($faq) {
            return [
                "@type" => "Question",
                "name" => $faq['question'],
                "acceptedAnswer" => ["@type" => "Answer", "text" => $faq['answer']]
            ];
        }, $faqItems)
    ];
}

if (!empty($breadcrumbs)) {
    $elements = [];
    foreach ($breadcrumbs as $idx => $bc) {
        $elements[] = [
            "@type" => "ListItem",
            "position" => $idx + 1,
            "name" => $bc['name'],
            "item" => $bc['item']
        ];
    }
    $graph[] = [
        "@type" => "BreadcrumbList",
        "itemListElement" => $elements
    ];
}

if (!empty($courseData)) {
    $graph[] = [
        "@type" => "Course",
        "name" => $courseData['name'],
        "description" => $courseData['description'],
        "provider" => [
            "@type" => "Organization",
            "name" => "RT Chocos",
            "sameAs" => "https://www.rtchocos.com/"
        ],
        "hasCourseInstance" => [
            "@type" => "CourseInstance",
            "courseMode" => $courseData['mode'] ?? 'blended',
            "location" => $courseData['location'] ?? 'Mumbai & Online',
            "offers" => [
                "@type" => "Offer",
                "category" => "Fees",
                "price" => $courseData['price'] ?? '0',
                "priceCurrency" => "INR"
            ]
        ]
    ];
}

if (!empty($recipeData)) {
    $graph[] = [
        "@type" => "Recipe",
        "name" => $recipeData['name'],
        "image" => $recipeData['image'] ?? $ogImage,
        "description" => $recipeData['description'],
        "recipeCategory" => "Dessert",
        "cuisine" => "Indian / Western Fusion",
        "prepTime" => $recipeData['prepTime'] ?? 'PT15M',
        "cookTime" => $recipeData['cookTime'] ?? 'PT30M',
        "totalTime" => $recipeData['totalTime'] ?? 'PT45M',
        "recipeYield" => $recipeData['yield'] ?? '1 batch',
        "recipeIngredient" => $recipeData['ingredients'] ?? [],
        "recipeInstructions" => array_map(function($step) {
            return [
                "@type" => "HowToStep",
                "text" => $step
            ];
        }, $recipeData['instructions'] ?? []),
        "author" => [
            "@type" => "Person",
            "name" => "Aarti Saluja Sahni"
        ]
    ];
}

if (!empty($pageSchema)) {
    if (isset($pageSchema['@type'])) {
        $graph[] = $pageSchema;
    } else if (is_array($pageSchema)) {
        foreach ($pageSchema as $s) {
            $graph[] = $s;
        }
    }
}
?>
<script type="application/ld+json">
<?php echo json_encode(["@context" => "https://schema.org", "@graph" => $graph], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>

<!-- --- HEADER --- -->
<?php
  if (session_status() === PHP_SESSION_NONE) {
    @session_start();
  }
  $currentURI = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
  $activeNav = 'home';
  if (strpos($currentURI, 'about') !== false) $activeNav = 'about';
  elseif (strpos($currentURI, 'shop') !== false || strpos($currentURI, 'product') !== false || strpos($currentURI, 'cart') !== false || strpos($currentURI, 'checkout') !== false) $activeNav = 'shop';
  elseif (strpos($currentURI, 'workshops') !== false) $activeNav = 'workshops';
  elseif (strpos($currentURI, 'blog') !== false) $activeNav = 'blog';
  elseif (strpos($currentURI, 'chocopedia') !== false) $activeNav = 'chocopedia';
  elseif (strpos($currentURI, 'gallery') !== false) $activeNav = 'gallery';
  elseif (strpos($currentURI, 'contact') !== false) $activeNav = 'contact';

  $cartCount = 0;
  if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
  }
?>
<header id="site-header" class="<?php echo ($isHome ?? false) ? '' : 'not-home'; ?>">
  <?php
    $showBanner = get_site_setting('show_announcement_banner', '1');
    $headerStoreMode = get_site_setting('store_mode', 'retail');
    $announcementText = get_site_setting('store_announcement_text', '');
    $announcementLinkText = get_site_setting('store_announcement_link_text', null);
    $announcementLinkUrl = get_site_setting('store_announcement_link_url', '');

    // Fallback message if banner is enabled and text is empty
    if ($showBanner !== '0' && empty($announcementText)) {
      if ($headerStoreMode === 'bulk') {
        $announcementText = '📦 B2B Bulk Supplies, Private Labeling & Corporate Gifting Solutions | Pan-India Delivery';
      } else {
        $freeShipMin = get_site_setting('retail_free_shipping_min', '999');
        $announcementText = '🍫 Handcrafted Artisanal Chocolates & Spreads — Free Pan-India Delivery on Orders Above ₹' . htmlspecialchars($freeShipMin) . '!';
      }
    }

    // Determine link text: if explicitly set in settings, respect it (even if empty to hide link). If null/never set, use default.
    if ($announcementLinkText === null) {
      $announcementLinkText = ($headerStoreMode === 'bulk') ? 'Bulk Inquiry →' : 'Shop Now →';
    }

    // Determine link URL destination
    if (empty($announcementLinkUrl)) {
      $announcementLinkUrl = ($headerStoreMode === 'bulk') ? ($pathPrefix . 'shop.php#bulk-enquiry') : ($pathPrefix . 'shop.php');
    }
  ?>
  <?php if ($showBanner !== '0' && !empty($announcementText)): ?>
  <div class="site-announcement-strip" style="background: linear-gradient(90deg, #07150E, #11281A, #07150E); color: #E5B358; font-size: 11.5px; font-weight: 600; text-align: center; padding: 6px 16px; letter-spacing: 0.4px; border-bottom: 1px solid rgba(229,179,88,0.2); display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
    <span><?php echo htmlspecialchars($announcementText); ?></span>
    <?php if (!empty($announcementLinkText)): ?>
      <?php 
        $isModalTrigger = (strpos($announcementLinkUrl, '#bulk-enquiry') !== false || strpos($announcementLinkUrl, '#enquiryModal') !== false);
        $onclickAction = $isModalTrigger ? 'onclick="if(typeof openEnquiryModal===\'function\'){event.preventDefault();openEnquiryModal(\'Announcement Banner\');}"' : '';
      ?>
      <a href="<?php echo htmlspecialchars($announcementLinkUrl); ?>" <?php echo $onclickAction; ?> style="color: #FFFFFF; text-decoration: underline; font-size: 11px; margin-left: 6px; font-weight: 700; transition: opacity 0.2s ease;">
        <?php echo htmlspecialchars($announcementLinkText); ?>
      </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="header-inner">
    <a href="<?php echo $pathPrefix ?: './'; ?>" class="logo" title="RT CHOCOS — Artisanal Cacao Academy & Journal">
      <svg class="logo-svg-emblem" width="26" height="34" viewBox="0 0 40 50" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Top stem -->
        <path d="M19 1.5C19 0.7 21 0.7 21 1.5V7.5H19V1.5Z" fill="#D4AF37"/>
        <!-- Center segment -->
        <path d="M20 7.5C21.8 17 21.8 35.5 20 45C18.2 35.5 18.2 17 20 7.5Z" fill="#D4AF37"/>
        <!-- Inner right segment -->
        <path d="M20 7.5C22.6 10 26 19 24.6 33C23.8 38 22.2 42 20 45C21.5 41.5 23.2 36.5 23.6 31.5C24.6 19 21.8 11.2 20 7.5Z" fill="#D4AF37"/>
        <!-- Inner left segment -->
        <path d="M20 7.5C17.4 10 14 19 15.4 33C16.2 38 17.8 42 20 45C18.5 41.5 16.8 36.5 16.4 31.5C15.4 19 18.2 11.2 20 7.5Z" fill="#D4AF37"/>
        <!-- Outer right segment -->
        <path d="M20 7.5C25.5 10 31.2 19 30.5 31C30 37.5 26.5 42.5 20 45C25.2 42 28.2 36.5 28.7 30.5C29.3 19.8 24.5 11.5 20 7.5Z" fill="#D4AF37"/>
        <!-- Outer left segment -->
        <path d="M20 7.5C14.5 10 8.8 19 9.5 31C10 37.5 13.5 42.5 20 45C14.8 42 11.8 36.5 11.3 30.5C10.7 19.8 15.5 11.5 20 7.5Z" fill="#D4AF37"/>
      </svg>
      <span class="logo-title">RT CHOCOS</span>
    </a>

    <div class="header-divider"></div>

    <nav class="header-nav" aria-label="Primary navigation">
      <!-- 1. EXPLORE -->
      <a class="nav-item-link <?php echo ($activeNav === 'home' || $activeNav === 'about') ? 'active' : ''; ?>" data-page="home" href="<?php echo $pathPrefix ?: 'index.php'; ?>" title="Explore RT Chocos">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/>
          <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
        </svg>
        <span class="nav-label">EXPLORE</span>
      </a>

      <!-- 2. PRODUCTS / BOUTIQUE (MEGA-MENU) -->
      <div class="nav-item-dropdown nav-item-products">
        <a class="nav-item-link dropdown-toggle <?php echo ($activeNav === 'shop') ? 'active' : ''; ?>" data-page="shop" href="<?php echo $pathPrefix; ?>shop.php" title="Artisanal Chocolate Boutique & Shop">
          <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2.5"/>
            <line x1="3" y1="9" x2="21" y2="9"/>
            <line x1="3" y1="15" x2="21" y2="15"/>
            <line x1="9" y1="3" x2="9" y2="21"/>
            <line x1="15" y1="3" x2="15" y2="21"/>
          </svg>
          <span class="nav-label">PRODUCTS</span>
        </a>

        <div class="nav-dropdown-menu products-mega-menu">
          <div class="mega-menu-grid">
            <!-- Left Panel: Curated Collections -->
            <div class="mega-categories-col">
              <div class="mega-col-header">
                <span class="mega-col-tag">ARTISANAL CREATIONS</span>
                <h4 class="mega-col-title">Curated Collections</h4>
              </div>

              <div class="mega-categories-list">
                <!-- 1. Bean-to-Bar Bars -->
                <a href="<?php echo $pathPrefix; ?>shop.php?category=Chocolates" class="mega-product-item">
                  <div class="mega-item-icon-box">
                    <span class="mega-item-emoji">🍫</span>
                  </div>
                  <div class="mega-item-info">
                    <div class="mega-item-top">
                      <span class="mega-item-name">Bean-to-Bar Chocolate Bars</span>
                      <span class="mega-badge-pill">Single Estate</span>
                    </div>
                    <p class="mega-item-desc">72% Idukki Dark, 55% Craft Milk &amp; roasted nib inclusions</p>
                  </div>
                  <span class="mega-item-arrow">→</span>
                </a>

                <!-- 2. Starter & Masterclass Kits -->
                <a href="<?php echo $pathPrefix; ?>shop.php?category=Kits" class="mega-product-item">
                  <div class="mega-item-icon-box">
                    <span class="mega-item-emoji">🎁</span>
                  </div>
                  <div class="mega-item-info">
                    <div class="mega-item-top">
                      <span class="mega-item-name">Starter &amp; Tempering Kits</span>
                      <span class="mega-badge-pill gold">Academy Pick</span>
                    </div>
                    <p class="mega-item-desc">Polycarbonate moulds, thermometers &amp; bean-to-bar maker sets</p>
                  </div>
                  <span class="mega-item-arrow">→</span>
                </a>

                <!-- 3. Raw Cacao & Pantry -->
                <a href="<?php echo $pathPrefix; ?>shop.php?category=Cacao" class="mega-product-item">
                  <div class="mega-item-icon-box">
                    <span class="mega-item-emoji">🌱</span>
                  </div>
                  <div class="mega-item-info">
                    <div class="mega-item-top">
                      <span class="mega-item-name">Pure Cacao &amp; Pantry</span>
                      <span class="mega-badge-pill">Direct Farm</span>
                    </div>
                    <p class="mega-item-desc">Single-origin roasted nibs, pure cacao butter &amp; husk tea</p>
                  </div>
                  <span class="mega-item-arrow">→</span>
                </a>

                <!-- 4. Bonbons & Truffles -->
                <a href="<?php echo $pathPrefix; ?>shop.php?category=Bonbons" class="mega-product-item">
                  <div class="mega-item-icon-box">
                    <span class="mega-item-emoji">🍬</span>
                  </div>
                  <div class="mega-item-info">
                    <div class="mega-item-top">
                      <span class="mega-item-name">Artisan Bonbons &amp; Truffles</span>
                      <span class="mega-badge-pill">Fresh Made</span>
                    </div>
                    <p class="mega-item-desc">Hand-painted cocoa butter gems with passion fruit &amp; spice ganache</p>
                  </div>
                  <span class="mega-item-arrow">→</span>
                </a>
              </div>
            </div>

            <!-- Right Panel: Featured Product Spotlight -->
            <div class="mega-featured-col">
              <div class="mega-featured-card">
                <div class="mega-featured-img-wrap">
                  <img src="<?php echo $pathPrefix; ?>assets/recipe_single_origin.png" alt="Signature 72% Bean-to-Bar Dark Chocolate Bar" loading="lazy" />
                  <span class="mega-featured-badge">⭐ BESTSELLER</span>
                </div>
                <div class="mega-featured-body">
                  <div class="mega-featured-origin">🇮🇳 Kerala Single-Estate Cacao</div>
                  <h5 class="mega-featured-title">Signature 72% Bean-to-Bar Dark Chocolate</h5>
                  <p class="mega-featured-desc">Stone-ground for 48 hours. Rich notes of dark raisins, vanilla pod &amp; roasted hazelnuts.</p>
                  <div class="mega-featured-pricing">
                    <span class="mega-price-current">₹250</span>
                    <span class="mega-price-old">₹295</span>
                    <span class="mega-discount-tag">Save 15%</span>
                  </div>
                  <a href="<?php echo $pathPrefix; ?>shop/signature-dark-chocolate-72" class="mega-order-btn">
                    <span>Order Now</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Trust & Perks Bar -->
          <div class="mega-menu-footer">
            <div class="mega-trust-badges">
              <span class="trust-item"><span class="trust-icon">🌿</span> 100% Indian Estate Cacao</span>
              <span class="trust-item"><span class="trust-icon">🚚</span> Free Shipping above ₹999</span>
              <span class="trust-item"><span class="trust-icon">❄️</span> Insulated Fresh Delivery</span>
            </div>
            <div class="mega-footer-actions">
              <a href="<?php echo $pathPrefix; ?>shop.php" class="mega-all-link">
                <span>View Full Boutique (All Items)</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. ACADEMY -->
      <div class="nav-item-dropdown">
        <a class="nav-item-link dropdown-toggle <?php echo ($activeNav === 'workshops' || $activeNav === 'gallery') ? 'active' : ''; ?>" data-page="academy" href="<?php echo $pathPrefix; ?>workshops.php" title="RT Chocos Academy">
          <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/>
          </svg>
          <span class="nav-label">ACADEMY</span>
        </a>
        <div class="nav-dropdown-menu">
          <a class="dropdown-item <?php echo $activeNav === 'workshops' ? 'active' : ''; ?>" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php" title="Chocolate Academy & Masterclasses">
            🎓 Workshops &amp; Masterclasses
          </a>
          <a class="dropdown-item <?php echo $activeNav === 'gallery' ? 'active' : ''; ?>" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php" title="Tested Recipes & Formulations">
            🍫 Recipes &amp; Formulations
          </a>
        </div>
      </div>

      <!-- 4. THE CACAO JOURNAL -->
      <a class="nav-item-link <?php echo $activeNav === 'blog' ? 'active' : ''; ?>" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php" title="The Cacao Journal & Blog">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
        <span class="nav-label">THE CACAO JOURNAL</span>
      </a>

      <!-- 5. INNOVATION LAB -->
      <a class="nav-item-link <?php echo $activeNav === 'chocopedia' ? 'active' : ''; ?>" data-page="chocopedia" href="<?php echo $pathPrefix; ?>chocopedia.php" title="Chocolate Innovation Lab & Encyclopedia">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
          <line x1="8.5" y1="2" x2="15.5" y2="2"/>
          <line x1="7" y1="14.5" x2="17" y2="14.5"/>
        </svg>
        <span class="nav-label">INNOVATION LAB</span>
      </a>

      <!-- 6. CHOCOLATE AI -->
      <button class="nav-item-link nav-ai-trigger" aria-label="Ask CocoaGenius AI" onclick="toggleAiDrawer()">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2a4 4 0 0 1 4 4c0 1.5-.8 2.8-2 3.5V11a2 2 0 0 1-2 2h-1"/>
          <path d="M9.5 4.5A3.5 3.5 0 0 0 6 8c0 1.4.8 2.6 2 3.1V12a2 2 0 0 0 2 2h1"/>
          <circle cx="12" cy="6" r="1"/>
          <path d="M12 13v8"/>
          <path d="M8 17h8"/>
          <circle cx="6" cy="8" r="1"/>
          <circle cx="18" cy="8" r="1"/>
          <circle cx="8" cy="17" r="1"/>
          <circle cx="16" cy="17" r="1"/>
          <circle cx="12" cy="21" r="1"/>
        </svg>
        <span class="nav-label">CHOCOLATE AI</span>
      </button>

      <!-- 7. FUNZONE -->
      <a class="nav-item-link <?php echo $activeNav === 'contact' ? 'active' : ''; ?>" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php" title="Funzone & Interactive Features">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <line x1="6" y1="12" x2="10" y2="12"/>
          <line x1="8" y1="10" x2="8" y2="14"/>
          <circle cx="15" cy="11" r="0.75" fill="currentColor"/>
          <circle cx="17" cy="13" r="0.75" fill="currentColor"/>
          <path d="M17.8 2a2 2 0 0 1 1.8 1.1l1.8 3.6a2 2 0 0 1 .6 2.3l-2.4 7.2a3 3 0 0 1-2.8 2.1h-9.6a3 3 0 0 1-2.8-2.1l-2.4-7.2a2 2 0 0 1 .6-2.3l1.8-3.6A2 2 0 0 1 6.2 2h11.6z"/>
        </svg>
        <span class="nav-label">FUNZONE</span>
      </a>
    </nav>

    <div class="header-divider"></div>

    <div class="header-actions">
      <!-- Search Button -->
      <button class="search-btn" aria-label="Search RT Chocos chocolate articles" onclick="openSearch()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>

      <!-- Cart Icon Button with dynamic badge -->
      <a href="<?php echo $pathPrefix; ?>cart.php" class="nav-cart-btn" title="View Shopping Cart (<?php echo $cartCount; ?> items)" aria-label="Shopping Cart">
        <svg class="cart-icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        <span class="cart-count-badge <?php echo $cartCount > 0 ? '' : 'hidden'; ?>" id="header-cart-count"><?php echo $cartCount; ?></span>
      </a>

      <!-- Work with Us CTA -->
      <a href="<?php echo $pathPrefix; ?>work-with-us.php" class="signin-join-btn work-with-us-btn" title="Work With RT Chocos — Consulting, R&D & Workshops">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
          <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
        </svg>
        <span>WORK WITH US</span>
      </a>

      <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Open navigation menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <nav id="mobile-menu" aria-label="Mobile navigation">
    <a class="mobile-nav-link <?php echo ($activeNav === 'home' || $activeNav === 'about') ? 'active' : ''; ?>" data-page="home" href="<?php echo $pathPrefix ?: 'index.php'; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
      <span>EXPLORE</span>
    </a>

    <!-- Mobile Products Group -->
    <div class="mobile-nav-group">
      <div class="mobile-nav-group-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="2.5"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
        <span>BOUTIQUE &amp; PRODUCTS</span>
      </div>
      <a class="mobile-nav-sublink <?php echo ($activeNav === 'shop' && empty($_GET['category'])) ? 'active' : ''; ?>" data-page="shop" href="<?php echo $pathPrefix; ?>shop.php">🍫 All Products &amp; Boutique</a>
      <a class="mobile-nav-sublink" href="<?php echo $pathPrefix; ?>shop.php?category=Chocolates">🏷️ Bean-to-Bar Chocolate Bars</a>
      <a class="mobile-nav-sublink" href="<?php echo $pathPrefix; ?>shop.php?category=Kits">🎁 Starter &amp; Tempering Kits</a>
      <a class="mobile-nav-sublink" href="<?php echo $pathPrefix; ?>shop.php?category=Cacao">🌱 Single-Origin Cacao Nibs &amp; Butter</a>
      <a class="mobile-nav-sublink" href="<?php echo $pathPrefix; ?>shop.php?category=Bonbons">🍬 Hand-Painted Bonbons &amp; Truffles</a>
      <a class="mobile-nav-sublink <?php echo $currentURI === '/cart.php' ? 'active' : ''; ?>" href="<?php echo $pathPrefix; ?>cart.php">🛒 Shopping Cart (<?php echo $cartCount; ?>)</a>
    </div>

    <!-- Mobile Academy Group -->
    <div class="mobile-nav-group">
      <div class="mobile-nav-group-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/></svg>
        <span>ACADEMY</span>
      </div>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'workshops' ? 'active' : ''; ?>" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php">🎓 Workshops &amp; Masterclasses</a>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'gallery' ? 'active' : ''; ?>" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php">🍫 Recipes &amp; Formulations</a>
    </div>

    <a class="mobile-nav-link <?php echo $activeNav === 'blog' ? 'active' : ''; ?>" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
      <span>THE CACAO JOURNAL</span>
    </a>

    <a class="mobile-nav-link <?php echo $activeNav === 'chocopedia' ? 'active' : ''; ?>" data-page="chocopedia" href="<?php echo $pathPrefix; ?>chocopedia.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/><line x1="7" y1="14.5" x2="17" y2="14.5"/></svg>
      <span>INNOVATION LAB</span>
    </a>

    <button class="mobile-nav-link mobile-nav-ai-btn" onclick="toggleAiDrawer(); toggleMobileMenu();">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><path d="M12 2a4 4 0 0 1 4 4c0 1.5-.8 2.8-2 3.5V11a2 2 0 0 1-2 2h-1"/><path d="M9.5 4.5A3.5 3.5 0 0 0 6 8c0 1.4.8 2.6 2 3.1V12a2 2 0 0 0 2 2h1"/><circle cx="12" cy="6" r="1"/><path d="M12 13v8"/><path d="M8 17h8"/><circle cx="6" cy="8" r="1"/><circle cx="18" cy="8" r="1"/><circle cx="8" cy="17" r="1"/><circle cx="16" cy="17" r="1"/><circle cx="12" cy="21" r="1"/></svg>
      <span>CHOCOLATE AI</span>
    </button>

    <a class="mobile-nav-link <?php echo $activeNav === 'contact' ? 'active' : ''; ?>" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><line x1="6" y1="12" x2="10" y2="12"/><line x1="8" y1="10" x2="8" y2="14"/><circle cx="15" cy="11" r="0.75" fill="currentColor"/><circle cx="17" cy="13" r="0.75" fill="currentColor"/><path d="M17.8 2a2 2 0 0 1 1.8 1.1l1.8 3.6a2 2 0 0 1 .6 2.3l-2.4 7.2a3 3 0 0 1-2.8 2.1h-9.6a3 3 0 0 1-2.8-2.1l-2.4-7.2a2 2 0 0 1 .6-2.3l1.8-3.6A2 2 0 0 1 6.2 2h11.6z"/></svg>
      <span>FUNZONE</span>
    </a>

    <div style="padding: 16px 20px;">
      <a href="<?php echo $pathPrefix; ?>admin/login.php" class="signin-join-btn" style="width: 100%; justify-content: center;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>SIGN IN / JOIN</span>
      </a>
    </div>
  </nav>
</header>

<?php require_once __DIR__ . '/components/ai_drawer.php'; ?>
