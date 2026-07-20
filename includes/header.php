<?php
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
?>
<!DOCTYPE html>
<html lang="en-IN">
<head>
<script>
  (function() {
    const savedTheme = localStorage.getItem('rtchocos-color-theme');
    if (savedTheme) {
      document.documentElement.classList.add(savedTheme);
    }
  })();
</script>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>" />
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />
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
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css">

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
        "url" => "https://www.rtchocos.com/about.php",
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
?>
<script type="application/ld+json">
<?php echo json_encode(["@context" => "https://schema.org", "@graph" => $graph], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . $bodyClass . '"' : ''; ?>>
<!-- --- HEADER --- -->
<header id="site-header" class="<?php echo ($isHome ?? false) ? '' : 'not-home'; ?>">
  <div class="header-inner">
    <a href="<?php echo $pathPrefix; ?>index.php" class="logo">
      <span class="logo-rt">RT</span><span class="logo-chocos"> Chocos</span>
    </a>
    <div class="header-nav-left">
      <a class="nav-link" data-page="home" href="<?php echo $pathPrefix; ?>index.php">Home</a>
      <a class="nav-link" data-page="about" href="<?php echo $pathPrefix; ?>about.php">About</a>
      <a class="nav-link" data-page="workshops" href="<?php echo $pathPrefix; ?>workshops.php">Workshops</a>
    </div>
    <div class="header-nav-right">
      <a class="nav-link" data-page="blog" href="<?php echo $pathPrefix; ?>blog.php">Blog</a>
      <a class="nav-link" data-page="chocopedia" href="<?php echo $pathPrefix; ?>chocopedia.php">Chocopedia</a>
      <a class="nav-link" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php">Recipes</a>
      <a class="nav-link" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
      <button class="nav-ai-btn" aria-label="Ask AI Chatbot" onclick="toggleAiDrawer()">
        ✨ Ask AI
      </button>
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
    <a class="mobile-nav-link" data-page="chocopedia" href="<?php echo $pathPrefix; ?>chocopedia.php">Chocopedia</a>
    <a class="mobile-nav-link" data-page="gallery" href="<?php echo $pathPrefix; ?>gallery.php">Recipes</a>
    <a class="mobile-nav-link" data-page="contact" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
    <button class="mobile-nav-ai-btn" onclick="toggleAiDrawer(); toggleMobileMenu();">
      ✨ Ask CocoaGenius AI
    </button>
  </div>
</header>

<!-- AI Chat Drawer -->
<div id="ai-chat-drawer" class="ai-drawer-container">
  <div class="ai-drawer-header">
    <div class="ai-drawer-title">
      <span class="ai-glowing-dot"></span>
      <h3>CocoaGenius AI</h3>
    </div>
    <button class="ai-drawer-close" onclick="toggleAiDrawer()">&times;</button>
  </div>
  <div class="ai-drawer-body" id="ai-chat-messages">
    <div class="ai-message system">
      <div class="ai-msg-bubble">
        Hello! I am <strong>CocoaGenius AI</strong>, your expert guide to the science, craft, and chemistry of chocolate making. How can I help you today?
      </div>
    </div>
  </div>
  
  <div class="ai-prompt-chips">
    <button class="ai-chip" onclick="sendQuickPrompt('Explain chocolate tempering science')">🔬 Tempering Science</button>
    <button class="ai-chip" onclick="sendQuickPrompt('Why is my chocolate blooming?')">🫘 Bloom Diagnosis</button>
    <button class="ai-chip" onclick="sendQuickPrompt('What is the difference between Criollo and Forastero?')">🍫 Cacao Varieties</button>
    <button class="ai-chip" onclick="sendQuickPrompt('Who runs RT Chocos and who developed this website?')">💻 Founder & Developer</button>
  </div>

  <div class="ai-drawer-footer">
    <form id="ai-chat-form" onsubmit="handleAiChatSubmit(event)">
      <input type="text" id="ai-chat-input" placeholder="Ask about tempering, roasting, recipes..." required autocomplete="off">
      <button type="submit" aria-label="Send message">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </form>
  </div>
</div>
<div id="ai-drawer-overlay" onclick="toggleAiDrawer()"></div>
