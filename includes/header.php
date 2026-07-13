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
<html lang="en-IN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo $pageTitle; ?></title>
<meta name="description" content="<?php echo $pageDescription; ?>" />
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

<?php
$defaultKeywords = "chocolate academy India, Indian chocolate blog, India first chocolate blog, bean to bar India, bean to bar learning academy, chocolate course India, chocolate workshops India, learn chocolate making India, cocoa science blog India, craft chocolate India, chocolate education India, chocolate blogging India, tempering chocolate course, chocolate consultant Mumbai, artisan chocolate making India, cacao farming blog India, chocolate recipes India, chocolate maker India, RT Chocos, Aarti Saluja Sahni chocolate";
$keywordsVal = !empty($pageKeywords) ? htmlspecialchars($pageKeywords) : $defaultKeywords;
?>
<meta name="keywords" content="<?php echo $keywordsVal; ?>" />
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
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&family=Dancing+Script:wght@700&family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css">

<!-- JSON-LD Structured Data -->
<?php
$graph = [
    [
        "@type" => "EducationalOrganization",
        "@id" => "https://www.rtchocos.com/#organization",
        "name" => "RT Chocos",
        "alternateName" => "RT Chocos Chocolate Academy",
        "url" => "https://www.rtchocos.com/",
        "description" => "India's first chocolate blogging website and bean-to-bar learning academy. Professional chocolate education, cocoa science articles, craft chocolate workshops, and recipe development by Aarti Saluja Sahni.",
        "logo" => [
            "@type" => "ImageObject",
            "url" => "https://www.rtchocos.com/assets/logo.png"
        ],
        "founder" => [
            "@id" => "https://www.rtchocos.com/#founder"
        ],
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "Mumbai Craft Kitchen Studio",
            "addressLocality" => "Mumbai",
            "addressRegion" => "MH",
            "postalCode" => "400001",
            "addressCountry" => "IN"
        ],
        "areaServed" => [
            "@type" => "Country",
            "name" => "India"
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
        "@type" => "WebSite",
        "@id" => "https://www.rtchocos.com/#website",
        "url" => "https://www.rtchocos.com/",
        "name" => "RT Chocos — India's First Chocolate Blog & Academy",
        "description" => "India's first chocolate blogging website and bean-to-bar learning academy",
        "publisher" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ],
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => "https://www.rtchocos.com/blog.php?q={search_term_string}",
            "query-input" => "required name=search_term_string"
        ]
    ],
    [
        "@type" => "LocalBusiness",
        "@id" => "https://www.rtchocos.com/#localbusiness",
        "name" => "RT Chocos — Chocolate Academy India",
        "description" => "India's first chocolate blog and bean-to-bar learning academy offering professional chocolate workshops, cocoa science education, and craft chocolate courses in Mumbai and online.",
        "image" => "https://www.rtchocos.com/assets/logo.png",
        "url" => "https://www.rtchocos.com/",
        "telephone" => "+919140238741",
        "priceRange" => "$$",
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "Mumbai Craft Kitchen Studio",
            "addressLocality" => "Mumbai",
            "addressRegion" => "MH",
            "postalCode" => "400001",
            "addressCountry" => "IN"
        ],
        "geo" => [
            "@type" => "GeoCoordinates",
            "latitude" => "19.0760",
            "longitude" => "72.8777"
        ],
        "areaServed" => [
            "@type" => "Country",
            "name" => "India"
        ],
        "founder" => [
            "@id" => "https://www.rtchocos.com/#founder"
        ]
    ],
    [
        "@type" => "Service",
        "@id" => "https://www.rtchocos.com/#chocolate-consulting",
        "name" => "Professional Bean-to-Bar Chocolate Consulting & Brand Business Setup",
        "description" => "Comprehensive consulting services for craft and bean-to-bar chocolate businesses in India. Includes micro-batch production scaling, cacao bean sourcing, stone grinder selection, chocolate tempering machinery consulting, packaging guidance, and legal compliance.",
        "provider" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ],
        "areaServed" => [
            "@type" => "Country",
            "name" => "India"
        ],
        "category" => "Chocolate Food Industry Consulting",
        "offers" => [
            "@type" => "Offer",
            "priceCurrency" => "INR",
            "price" => "0",
            "description" => "Contact hello@rtchocos.com for custom quote pricing on consulting packages"
        ]
    ],
    [
        "@type" => "Service",
        "@id" => "https://www.rtchocos.com/#recipe-development",
        "name" => "Artisan Chocolate Recipe Development & Formulation Services",
        "description" => "Professional chocolate recipe formulation, dark chocolate profiles, organic inclusions, dairy-free chocolate alternatives, sugar-free keto chocolate recipes, and shelf-life optimization by food developer Aarti Saluja Sahni.",
        "provider" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ],
        "areaServed" => [
            "@type" => "Country",
            "name" => "India"
        ],
        "category" => "Food Recipe Development",
        "offers" => [
            "@type" => "Offer",
            "priceCurrency" => "INR",
            "price" => "0",
            "description" => "Contact hello@rtchocos.com for custom chocolate recipe formulation packages"
        ]
    ]
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
        "datePublished" => date('c', strtotime($dbPost['created_at'] ?? 'now')),
        "dateModified" => date('c', strtotime($dbPost['updated_at'] ?? 'now')),
        "author" => [
            "@type" => "Person",
            "name" => "Aarti Saluja Sahni",
            "url" => "https://www.rtchocos.com/about.php"
        ],
        "publisher" => [
            "@id" => "https://www.rtchocos.com/#organization"
        ]
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
?>
<script type="application/ld+json">
<?php echo json_encode(["@context" => "https://schema.org", "@graph" => $graph], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>
<!-- --- HEADER --- -->
<header id="site-header" class="<?php echo ($isHome ?? false) ? '' : 'not-home'; ?>">
  <div class="header-inner">
    <a href="<?php echo $pathPrefix; ?>index.php" class="logo" title="RT Chocos — India's First Chocolate Blog & Academy">
      <img src="<?php echo $pathPrefix; ?>assets/logo.png" class="logo-img logo-img-header" alt="RT Chocos — India's First Chocolate Blog & Bean-to-Bar Academy" />
    </a>
    <nav class="header-nav-left" aria-label="Primary navigation">
      <a class="nav-link" data-page="home" href="<?php echo $pathPrefix; ?>index.php">Home</a>
      <a class="nav-link" data-page="about" href="<?php echo $pathPrefix; ?>about.php">About</a>
      <a class="nav-link" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php" title="Chocolate Academy & Workshops India">Workshops</a>
    </nav>
    <nav class="header-nav-right" aria-label="Secondary navigation">
      <a class="nav-link" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php" title="Indian Chocolate Blog — Cocoa Science & Articles">Blog</a>
      <a class="nav-link" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php" title="Chocolate Recipes India">Recipes</a>
      <a class="nav-link" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
      <button class="search-btn" aria-label="Search RT Chocos chocolate articles" onclick="openSearch()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="search-icon-svg">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </button>
    </nav>
    <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Open navigation menu">
      <span></span><span></span><span></span>
    </button>
  </div>
  <nav id="mobile-menu" aria-label="Mobile navigation">
    <a class="mobile-nav-link" data-page="home" href="<?php echo $pathPrefix; ?>index.php">Home</a>
    <a class="mobile-nav-link" data-page="about" href="<?php echo $pathPrefix; ?>about.php">About</a>
    <a class="mobile-nav-link" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php">Workshops</a>
    <a class="mobile-nav-link" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php">Blog</a>
    <a class="mobile-nav-link" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php">Recipes</a>
    <a class="mobile-nav-link" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
  </nav>
</header>
