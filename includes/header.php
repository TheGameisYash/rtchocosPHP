<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/maintenance_check.php';

$cartCount = (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;

// Canonicals always point to the public HTTPS URL, dynamically resolved from site_settings
$siteUrl = rtrim(get_site_setting('site_url', 'https://www.rtchocos.com'), '/');
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

$adminSiteTheme = get_site_setting('default_site_theme', 'theme-cream-forest');
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
<link rel="alternate" hreflang="en-IN" href="<?php echo htmlspecialchars($canonicalUrl); ?>" />

<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
<meta name="author" content="Aarti Saluja Sahni" />
<meta name="publisher" content="RT Chocos" />
<meta name="theme-color" content="#3B2A22" />
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
<link rel="preconnect" href="https://www.googletagmanager.com" crossorigin />
<link rel="preconnect" href="https://www.youtube.com" crossorigin />
<link rel="dns-prefetch" href="https://fonts.googleapis.com" />
<link rel="dns-prefetch" href="https://www.googletagmanager.com" />
<link rel="dns-prefetch" href="https://www.youtube.com" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
<?php
  $cssFiles = [__DIR__ . '/../style.css', __DIR__ . '/../css/pages.css', __DIR__ . '/../css/mobile.css'];
  $cssVersion = max(array_map('filemtime', array_filter($cssFiles, 'file_exists')));
?>
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css?v=<?php echo $cssVersion; ?>">
<script src="<?php echo $pathPrefix; ?>js/anime.min.js"></script>

<!-- JSON-LD Structured Data -->
<?php
$graph = [
    [
        "@type" => "EducationalOrganization",
        "@id" => "https://www.rtchocos.com/#organization",
        "name" => "RT Chocos",
        "alternateName" => "RT Chocos Chocolate Academy",
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
            "https://www.facebook.com/rtchocos",
            "https://www.linkedin.com/in/aarti-saluja-sahni-8304637/"
        ],
        "knowsAbout" => [
            "Bean to bar chocolate making",
            "Cocoa science",
            "Chocolate tempering",
            "Cacao fermentation",
            "Craft chocolate",
            "Chocolate recipe development",
            "Indian cacao farming"
        ]
    ],
    [
        "@type" => "Person",
        "@id" => "https://www.rtchocos.com/#founder",
        "name" => "Aarti Saluja Sahni",
        "url" => "https://www.rtchocos.com/about.php",
        "image" => "https://www.rtchocos.com/assets/myphoto.jpg",
        "jobTitle" => "Founder & Chocolate Educator",
        "description" => "India's pioneering chocolate educator with 10+ years of bean-to-bar expertise. Founder of RT Chocos — India's first chocolate blog. Recipe developer, chocolate consultant, and professional trainer with 2,000+ students trained.",
        "worksFor" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ],
        "knowsAbout" => [
            "Bean to bar chocolate making",
            "Chocolate tempering science",
            "Cocoa science and chemistry",
            "Chocolate recipe formulation",
            "Indian craft chocolate",
            "Cacao farming and fermentation"
        ],
        "sameAs" => [
            "https://www.instagram.com/rt.chocos/",
            "https://www.linkedin.com/in/aarti-saluja-sahni-8304637/"
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
<?php
// Developer Custom Head Scripts & CSS Injection
$customCss = get_site_setting('custom_css', '');
if (!empty(trim($customCss))) {
    echo "\n<!-- Developer Custom CSS -->\n<style>\n" . $customCss . "\n</style>\n";
}
$customHeadScripts = get_site_setting('custom_head_scripts', '');
if (!empty(trim($customHeadScripts))) {
    echo "\n<!-- Developer Custom Head Scripts -->\n" . $customHeadScripts . "\n";
}
?>
<!-- Dedicated Mobile Isolated Presentation Layer -->
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>css/mobile.css?v=<?php echo file_exists(__DIR__ . '/../css/mobile.css') ? filemtime(__DIR__ . '/../css/mobile.css') : time(); ?>">
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>

<?php if (get_site_setting('maintenance_mode', '0') === '1'): ?>
  <div style="background: linear-gradient(90deg, #dc2626, #b91c1c); color: #fff; text-align: center; padding: 10px 16px; font-size: 13px; font-weight: 600; position: sticky; top: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <span>⚠️ <strong>MAINTENANCE MODE ACTIVE:</strong> Public visitors are currently seeing the 503 screen. You are viewing via authorized bypass.</span>
    <?php if (!empty($_SESSION['dev_logged_in'])): ?>
      <a href="/dev/maintenance.php" style="background: #fff; color: #991b1b; padding: 3px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Dev Controls &rarr;</a>
    <?php elseif (!empty($_SESSION['admin_logged_in'])): ?>
      <a href="/admin/dashboard.php" style="background: #fff; color: #991b1b; padding: 3px 10px; border-radius: 6px; text-decoration: none; font-size: 11.5px; font-weight: 700;">Admin CMS &rarr;</a>
    <?php endif; ?>
  </div>
<?php elseif (function_exists('is_beta_environment') && is_beta_environment() && function_exists('is_beta_lock_enabled') && is_beta_lock_enabled()): ?>
  <div id="beta-staging-banner" style="background: linear-gradient(90deg, #161009, #26170d); color: #fcf8f2; border-bottom: 1px solid rgba(212, 175, 55, 0.45); text-align: center; padding: 7px 16px; font-size: 12px; font-weight: 500; position: fixed; top: 0; left: 0; right: 0; z-index: 10001; display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; height: 34px; box-sizing: border-box;">
    <span>🔒 <strong>BETA STAGING LOCKED:</strong> Public visitors cannot access this platform. You are viewing via authorized session.</span>
    <a href="?lock_beta=1" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.6); color: #fca5a5; padding: 2px 8px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600;" title="Lock this browser session again to view the lock screen">Lock Again</a>
    <?php if (!empty($_SESSION['dev_logged_in'])): ?>
      <a href="/dev/urls.php" style="background: #d4af37; color: #000; padding: 2px 9px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 700;">Beta Controls &rarr;</a>
    <?php endif; ?>
  </div>
  <style>
    #site-header { top: 34px !important; }
    #hero { padding-top: 106px !important; }
    #page-blog,
    #page-workshops,
    .shop-page-wrapper,
    #page-about,
    #page-gallery,
    #page-chocopedia,
    #page-contact {
      padding-top: 114px !important;
    }
    .lab-cs-page {
      padding-top: 150px !important;
    }
    @media (max-width: 768px) {
      #page-blog,
      #page-workshops,
      .shop-page-wrapper,
      #page-about,
      #page-gallery,
      #page-chocopedia,
      #page-contact {
        padding-top: 34px !important;
      }
      .lab-cs-page {
        padding-top: 110px !important;
      }
    }
  </style>
<?php endif; ?>

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
  elseif (strpos($currentURI, 'innovation-lab') !== false) $activeNav = 'innovation-lab';
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
    <div class="header-mobile-spacer" style="display: none;"></div>
    <a href="<?php echo $pathPrefix ?: './'; ?>" class="logo" title="RT CHOCOS — Artisanal Cacao Academy & Journal">
      <svg class="logo-svg-emblem" width="23" height="48" viewBox="0 0 147 307" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M59 41L42 53L32 63L20 80L11 98L5 115L2 133L1 134L1 140L0 141L0 176L1 177L1 184L2 185L6 207L13 227L24 249L32 262L45 279L67 302L42 268L28 243L28 241L24 234L21 224L19 221L15 208L14 200L11 190L11 185L10 184L10 176L9 175L9 146L10 145L10 137L11 136L11 131L13 126L14 118L19 102L32 75L45 57L60 42ZM72 40L71 64L70 65L70 89L69 90L69 208L70 209L70 243L71 244L71 277L72 278L72 299L71 300L60 273L50 240L50 236L48 231L45 211L44 210L44 202L43 201L43 193L42 192L42 177L41 176L41 150L42 149L42 135L43 134L45 113L53 81L68 41L65 44L57 57L49 73L44 86L44 89L42 92L36 118L35 132L34 133L33 174L34 175L34 186L35 187L37 209L45 241L61 283L72 304L72 306L74 306L77 301L79 302L95 286L111 266L121 251L133 227L135 219L139 210L144 189L144 184L145 183L145 176L146 175L146 142L145 141L143 124L139 112L139 109L127 82L119 70L109 58L96 47L86 41L101 57L110 69L120 86L120 88L125 97L133 122L135 138L136 139L137 173L136 174L135 190L134 191L131 208L120 239L106 265L90 288L78 302L77 300L95 259L103 235L104 227L107 219L109 202L111 195L111 187L112 186L113 149L112 148L111 126L110 125L109 114L104 93L94 67L86 52L78 41L81 51L89 69L99 104L101 119L102 120L102 127L103 128L103 137L104 138L105 173L104 174L103 202L102 203L102 210L101 211L98 231L94 244L94 248L89 262L89 265L87 268L85 277L76 299L75 298L75 259L76 258L76 211L77 210L76 209L77 208L77 101L76 100L75 54L74 53L74 43L73 40ZM71 0L67 2L68 15L69 16L68 37L78 37L77 35L78 2L75 0Z" fill="#E2BC55" fill-rule="evenodd" />
      </svg>
      <span class="logo-title">RT CHOCOS</span>
    </a>

    <div class="header-divider"></div>

    <nav class="header-nav" aria-label="Primary navigation">
      <!-- 1. EXPLORE -->
      <div class="nav-item-dropdown">
        <a class="nav-item-link dropdown-toggle <?php echo ($activeNav === 'home' || $activeNav === 'about' || $activeNav === 'chocopedia') ? 'active' : ''; ?>" data-page="explore" href="<?php echo $pathPrefix ?: './'; ?>" title="Explore RT Chocos">
          <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9"/>
            <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
          </svg>
          <span class="nav-label">EXPLORE</span>
        </a>
        <div class="nav-dropdown-menu">
          <a class="dropdown-item <?php echo $activeNav === 'home' ? 'active' : ''; ?>" data-page="home" href="<?php echo $pathPrefix ?: './'; ?>" title="Explore RT Chocos">
            Explore Overview
          </a>
          <a class="dropdown-item <?php echo $activeNav === 'chocopedia' ? 'active' : ''; ?>" data-page="chocopedia" href="<?php echo $pathPrefix; ?>chocopedia" title="Chocolate Encyclopedia">
            Chocopedia
          </a>
          <a class="dropdown-item <?php echo $activeNav === 'about' ? 'active' : ''; ?>" data-page="about" href="<?php echo $pathPrefix; ?>about" title="About RT Chocos & Aarti Saluja">
            About RT Chocos
          </a>
        </div>
      </div>

      <!-- 2. PRODUCTS -->
      <a class="nav-item-link <?php echo ($activeNav === 'shop') ? 'active' : ''; ?>" data-page="shop" href="<?php echo $pathPrefix; ?>shop" title="Artisanal Chocolate Products & Shop">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2.5"/>
          <line x1="3" y1="9" x2="21" y2="9"/>
          <line x1="3" y1="15" x2="21" y2="15"/>
          <line x1="9" y1="3" x2="9" y2="21"/>
          <line x1="15" y1="3" x2="15" y2="21"/>
        </svg>
        <span class="nav-label">PRODUCTS</span>
      </a>

      <!-- 3. ACADEMY -->
      <div class="nav-item-dropdown">
        <a class="nav-item-link dropdown-toggle <?php echo ($activeNav === 'workshops' || $activeNav === 'gallery') ? 'active' : ''; ?>" data-page="academy" href="<?php echo $pathPrefix; ?>workshops" title="RT Chocos Academy">
          <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/>
          </svg>
          <span class="nav-label">ACADEMY</span>
        </a>
        <div class="nav-dropdown-menu">
          <a class="dropdown-item <?php echo $activeNav === 'workshops' ? 'active' : ''; ?>" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops" title="Chocolate Academy & Masterclasses">
            Workshops &amp; Masterclasses
          </a>
          <a class="dropdown-item <?php echo $activeNav === 'gallery' ? 'active' : ''; ?>" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery" title="Tested Recipes & Formulations">
            Recipes &amp; Formulations
          </a>
        </div>
      </div>

      <!-- 4. THE CACAO JOURNAL -->
      <a class="nav-item-link <?php echo $activeNav === 'blog' ? 'active' : ''; ?>" data-page="blog" href="<?php echo $pathPrefix; ?>blog" title="The Cacao Journal & Blog">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
          <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
        </svg>
        <span class="nav-label">THE CACAO JOURNAL</span>
      </a>

      <!-- 5. INNOVATION LAB -->
      <a class="nav-item-link <?php echo $activeNav === 'innovation-lab' ? 'active' : ''; ?>" data-page="innovation-lab" href="<?php echo $pathPrefix; ?>innovation-lab" title="Chocolate Innovation Lab">
        <svg class="nav-icon-svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
          <line x1="8.5" y1="2" x2="15.5" y2="2"/>
          <line x1="7" y1="14.5" x2="17" y2="14.5"/>
        </svg>
        <span class="nav-label">INNOVATION LAB<?php if (get_site_setting('innovation_lab_mode', 'coming_soon') === 'coming_soon'): ?><span style="display:inline-block; font-size: 8px; font-weight: 800; background: rgba(229,179,88,0.22); color: var(--gold-light, #F7D592); border: 1px solid rgba(229,179,88,0.4); padding: 1px 5px; border-radius: 4px; margin-left: 5px; vertical-align: middle; letter-spacing: 0.5px;">SOON</span><?php endif; ?></span>
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

<!-- Cart option completely removed from website -->

      <!-- Work with Us CTA (Desktop Only) -->
      <a href="<?php echo $pathPrefix; ?>work-with-us" class="signin-join-btn work-with-us-btn header-desktop-cta" title="Work With RT Chocos — Consulting, R&D & Workshops">
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
</header>

<nav id="mobile-menu" aria-label="Mobile navigation">
    <?php $navPrefix = (isset($pathPrefix) && $pathPrefix !== '') ? $pathPrefix : '/'; ?>
    <!-- Mobile Drawer Top Bar with Close Button -->
    <div class="mobile-menu-header">
      <div class="mobile-menu-title-wrap">
        <span class="mobile-menu-tag">ARTISANAL CACAO</span>
        <span class="mobile-menu-title">RT CHOCOS</span>
      </div>
      <button type="button" class="mobile-menu-close-btn" onclick="toggleMobileMenu(true)" aria-label="Close navigation menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <!-- Quick Search Row in Drawer -->
    <div class="mobile-drawer-search-wrap">
      <form action="<?php echo $navPrefix; ?>shop.php" method="GET" class="mobile-drawer-search-form">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="search" name="search" placeholder="Search chocolates, spreads, ingredients..." autocomplete="off">
      </form>
    </div>

    <!-- Mobile Products Group -->
    <div class="mobile-nav-group">
      <div class="mobile-nav-group-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2.5"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
        <span>PRODUCTS &amp; CREATIONS</span>
      </div>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'shop' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>shop">All Products</a>
      <a class="mobile-nav-sublink" href="<?php echo $navPrefix; ?>shop?category=chocolates">Bean-to-Bar Chocolates</a>
      <a class="mobile-nav-sublink" href="<?php echo $navPrefix; ?>shop?category=spreads">Spreads &amp; Nut Butters</a>
      <a class="mobile-nav-sublink" href="<?php echo $navPrefix; ?>shop?category=ingredients">Natural Ingredients</a>
      <a class="mobile-nav-sublink" href="<?php echo $navPrefix; ?>shop?category=gifting">Corporate &amp; Festive Gifting</a>
    </div>

    <!-- Mobile Academy Group -->
    <div class="mobile-nav-group">
      <div class="mobile-nav-group-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 6 2 6 2s6 0 6-2v-5"/></svg>
        <span>ACADEMY &amp; MASTERCLASSES</span>
      </div>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'workshops' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>workshops">Workshops &amp; Masterclasses</a>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'gallery' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>gallery">Tested Recipes &amp; Formulations</a>
    </div>

    <!-- Mobile Explore Group -->
    <div class="mobile-nav-group">
      <div class="mobile-nav-group-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
        <span>EXPLORE &amp; KNOWLEDGE</span>
      </div>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'home' ? 'active' : ''; ?>" href="<?php echo $navPrefix ?: './'; ?>">Home Overview</a>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'about' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>about">About Aarti &amp; RT Chocos</a>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'chocopedia' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>chocopedia">Chocopedia &mdash; Cocoa Encyclopedia</a>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'blog' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>blog">The Cacao Journal</a>
      <a class="mobile-nav-sublink <?php echo $activeNav === 'innovation-lab' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>innovation-lab">Innovation &amp; R&amp;D Lab<?php if (get_site_setting('innovation_lab_mode', 'coming_soon') === 'coming_soon'): ?><span style="display:inline-block; font-size: 8.5px; font-weight: 800; background: rgba(229,179,88,0.22); color: var(--gold-light, #F7D592); border: 1px solid rgba(229,179,88,0.4); padding: 1px 6px; border-radius: 4px; margin-left: 8px; vertical-align: middle; letter-spacing: 0.5px;">SOON</span><?php endif; ?></a>
    </div>

    <!-- Mobile Direct Actions & Support -->
    <div class="mobile-drawer-direct-links">
      <a class="mobile-nav-link <?php echo $activeNav === 'work-with-us' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>work-with-us">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
        <span>WORK WITH US &amp; B2B</span>
      </a>
      <a class="mobile-nav-link <?php echo $activeNav === 'contact' ? 'active' : ''; ?>" href="<?php echo $navPrefix; ?>contact">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.6"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <span>CONTACT US</span>
      </a>
    </div>

    <!-- Downside Concierge & AI Actions -->
    <div class="mobile-menu-ai-dock">
      <a href="https://wa.me/919140238741?text=Hi%20RT%20Chocos,%20I%20have%20an%20inquiry%20regarding%20artisanal%20chocolates" target="_blank" rel="noopener noreferrer" class="mobile-menu-whatsapp-pill">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#25D366" stroke-width="2">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
        </svg>
        <span>WhatsApp Chocolatier Concierge</span>
      </a>
      <button class="mobile-menu-ai-pill" onclick="toggleAiDrawer(); toggleMobileMenu();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="1.8">
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
        <span>CHOCOLATE AI &mdash; COCOAGENIUS</span>
        <span class="ai-sparkle-pill">✦</span>
      </button>
    </div>
  </nav>
  <div id="mobile-menu-backdrop" class="mobile-menu-backdrop" onclick="toggleMobileMenu(true)"></div>

<?php require_once __DIR__ . '/components/ai_drawer.php'; ?>
