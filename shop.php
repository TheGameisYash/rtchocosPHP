<?php
  session_start();
  $pageTitle = "Artisanal Chocolates, Gourmet Spreads & Corporate Gifting | RT Chocos India";
  $pageDescription = "Explore handcrafted bean-to-bar chocolates, roasted nut spreads, dates powder, and bespoke corporate gifting solutions by RT Chocos — India's premier chocolate academy.";
  $pageKeywords = "gourmet chocolate India, bean to bar chocolate online, almond halwa spread, cashew coconut spread, corporate chocolate gifting, private label chocolates India, RT Chocos";
  $pathPrefix = "";

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . ($_SERVER['HTTP_HOST'] ?? 'www.rtchocos.com') . "/shop";

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Products', 'item' => $canonicalUrl]
  ];

  include $pathPrefix . 'includes/header.php';
  require_once __DIR__ . '/includes/db.php';

  // Fetch Store Business Focus Mode & E-Commerce parameters
  $dbStoreMode = get_site_setting('store_mode', 'retail');
  // Allow URL override for instant testing: ?mode=retail, ?mode=bulk, ?mode=hybrid
  $storeMode = isset($_GET['mode']) && in_array($_GET['mode'], ['retail', 'bulk', 'hybrid'], true) ? $_GET['mode'] : $dbStoreMode;
  $freeShippingMin = (int)get_site_setting('retail_free_shipping_min', 999);
  $bulkMOQ = get_site_setting('bulk_min_order_qty', '50 Units');
  $bulkPhone = get_site_setting('bulk_enquiry_phone', '+919140238741');

  // Category filter from URL if present
  $categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';

  // Preset catalog products matching the reference design
  $curatedCatalog = [
      [
          'id' => 6,
          'name' => 'Almond Halwa Spread',
          'slug' => 'almond-halwa-spread',
          'category' => 'Spreads & Nut Butters',
          'category_key' => 'spreads',
          'short_description' => 'The richness of traditional almond halwa, reimagined in a smooth, indulgent spread.',
          'image' => 'assets/products/almond_halwa_spread.jpg',
          'price' => 380,
          'sale_price' => 349,
          'customizable' => true
      ],
      [
          'id' => 7,
          'name' => 'Cashew Coconut Spread',
          'slug' => 'cashew-coconut-spread',
          'category' => 'Spreads & Nut Butters',
          'category_key' => 'spreads',
          'short_description' => 'A delicate blend of roasted cashews and coconut with a naturally indulgent character.',
          'image' => 'assets/products/cashew_coconut_spread.jpg',
          'price' => 390,
          'sale_price' => 360,
          'customizable' => true
      ],
      [
          'id' => 8,
          'name' => 'Crunchy Almond Spread',
          'slug' => 'crunchy-almond-spread',
          'category' => 'Spreads & Nut Butters',
          'category_key' => 'spreads',
          'short_description' => 'Roasted almonds combined with a delightful crunch for a memorable bite.',
          'image' => 'assets/products/crunchy_almond_spread.jpg',
          'price' => 370,
          'sale_price' => 340,
          'customizable' => true
      ],
      [
          'id' => 9,
          'name' => 'Almond Spread',
          'slug' => 'almond-spread',
          'category' => 'Spreads & Nut Butters',
          'category_key' => 'spreads',
          'short_description' => 'Classic, creamy and crafted around the richness of premium almonds.',
          'image' => 'assets/products/almond_spread.jpg',
          'price' => 350,
          'sale_price' => 325,
          'customizable' => true
      ],
      [
          'id' => 10,
          'name' => 'Almond Bark',
          'slug' => 'almond-bark',
          'category' => 'Chocolates',
          'category_key' => 'chocolates',
          'short_description' => 'Premium chocolate layered with roasted almonds for an indulgent, textured bite.',
          'image' => 'assets/products/almond_bark.jpg',
          'price' => 420,
          'sale_price' => 390,
          'customizable' => true
      ],
      [
          'id' => 11,
          'name' => 'Bean to Bar Dark Chocolate',
          'slug' => 'bean-to-bar-dark-chocolate',
          'category' => 'Chocolates',
          'category_key' => 'chocolates',
          'short_description' => 'Crafted from carefully sourced cocoa for a rich and refined chocolate experience.',
          'image' => 'assets/products/bean_to_bar_dark.jpg',
          'price' => 290,
          'sale_price' => 265,
          'customizable' => true
      ],
      [
          'id' => 12,
          'name' => 'Bean to Bar Milk Chocolate',
          'slug' => 'bean-to-bar-milk-chocolate',
          'category' => 'Chocolates',
          'category_key' => 'chocolates',
          'short_description' => 'Smooth, creamy and beautifully balanced for a luxurious chocolate experience.',
          'image' => 'assets/products/bean_to_bar_milk.jpg',
          'price' => 280,
          'sale_price' => 255,
          'customizable' => true
      ],
      [
          'id' => 13,
          'name' => 'Bean to Bar White Chocolate',
          'slug' => 'bean-to-bar-white-chocolate',
          'category' => 'Chocolates',
          'category_key' => 'chocolates',
          'short_description' => 'Rich, velvety and delicately crafted for a refined indulgence.',
          'image' => 'assets/products/bean_to_bar_white.jpg',
          'price' => 295,
          'sale_price' => 270,
          'customizable' => true
      ],
      [
          'id' => 14,
          'name' => 'Dates Powder',
          'slug' => 'dates-powder',
          'category' => 'Ingredients',
          'category_key' => 'ingredients',
          'short_description' => 'Natural dates transformed into a versatile powder with rich, caramel-like notes.',
          'image' => 'assets/products/dates_powder.jpg',
          'price' => 260,
          'sale_price' => 230,
          'customizable' => true
      ],
      [
          'id' => 15,
          'name' => 'Handcrafted Chocolates',
          'slug' => 'handcrafted-chocolates',
          'category' => 'Corporate & Gifting',
          'category_key' => 'gifting',
          'short_description' => 'Artisan chocolates designed for celebrations, gifting and special occasions.',
          'image' => 'assets/products/handcrafted_chocolates.jpg',
          'price' => 650,
          'sale_price' => 599,
          'customizable' => true
      ]
  ];

  // Fetch active products directly from MySQL database (fully integrated with Admin Panel)
  try {
      $pdo = get_db();
      $dbProducts = $pdo->query("SELECT id, slug, name, category, short_description, image_main, price, sale_price FROM products WHERE is_active = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
      if (!empty($dbProducts)) {
          $displayCatalog = [];
          foreach ($dbProducts as $dp) {
              $cat = $dp['category'] ?? '';
              $cLower = strtolower(trim($cat));
              $catKey = 'chocolates';
              if (strpos($cLower, 'spread') !== false || strpos($cLower, 'butter') !== false) {
                  $catKey = 'spreads';
              } elseif (strpos($cLower, 'ingredient') !== false || strpos($cLower, 'powder') !== false) {
                  $catKey = 'ingredients';
              } elseif (strpos($cLower, 'gift') !== false || strpos($cLower, 'corporate') !== false || strpos($cLower, 'box') !== false) {
                  $catKey = 'gifting';
              }

              $displayCatalog[] = [
                  'id' => (int)$dp['id'],
                  'name' => $dp['name'],
                  'slug' => $dp['slug'],
                  'category' => $dp['category'],
                  'category_key' => $catKey,
                  'short_description' => $dp['short_description'],
                  'image' => !empty($dp['image_main']) ? $dp['image_main'] : 'assets/products/' . str_replace('-', '_', $dp['slug']) . '.jpg',
                  'customizable' => true,
                  'price' => (float)$dp['price'],
                  'sale_price' => (float)$dp['sale_price']
              ];
          }
          $curatedCatalog = $displayCatalog;
      }
  } catch (Exception $e) {
      // Fallback cleanly to curatedCatalog array
  }
?>

<!-- Link Dedicated Luxury Shop Stylesheet -->
<link rel="stylesheet" href="css/shop-luxury.css?v=<?php echo time(); ?>">

<main class="shop-page-wrapper">

  <!-- =========================================================================
       SECTION 1: HERO SECTION (Dynamic: Retail vs Bulk vs Hybrid)
       ========================================================================= -->
  <section class="shop-hero-lux">
    <div class="shop-hero-container">
      
      <!-- RETAIL HERO CONTENT -->
      <div class="shop-hero-left mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>" id="heroContentRetail">
        <span class="shop-hero-tag">Gourmet Artisanal Treats</span>
        <h1 class="shop-hero-title">Pure Indulgence in Every Jar.<br>Delivered Fresh to Your Door.</h1>
        <p class="shop-hero-desc">
          Handcrafted in small batches with 100% pure cocoa butter, single-origin cacao, and California almonds. Zero palm oil, no artificial preservatives—pure artisanal taste crafted for your breakfast table, desserts, and spoon indulgence.
        </p>

        <!-- 4 Retail Value Badges -->
        <div class="shop-hero-badges">
          <!-- 1: 100% Pure Cocoa Butter -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 3a9 9 0 0 1 9 9c0 3-1.5 5.5-4 7"></path>
                <path d="M3.5 12A8.5 8.5 0 0 1 12 3.5"></path>
                <path d="M7 20h10"></path>
                <path d="M10 20c0-4.4 2-7 4-10"></path>
              </svg>
            </div>
            <span class="hero-badge-label">100% Pure<br>Cocoa Butter</span>
          </div>

          <!-- 2: Fresh Small Batches -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
              </svg>
            </div>
            <span class="hero-badge-label">Fresh Small<br>Batches</span>
          </div>

          <!-- 3: Chilled Pan-India Delivery -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
              </svg>
            </div>
            <span class="hero-badge-label">Chilled Express<br>Delivery</span>
          </div>

          <!-- 4: Zero Palm Oil -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <path d="M9 12l2 2 4-4"></path>
              </svg>
            </div>
            <span class="hero-badge-label">Zero Palm Oil<br>&amp; Additives</span>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="shop-hero-actions">
          <a href="#explore-range" class="btn-gold-lux">
            Order Online Now &rarr;
          </a>
          <a href="#explore-range" class="btn-outline-lux">
            Explore All Flavours &darr;
          </a>
        </div>
      </div>

      <!-- BULK / B2B HERO CONTENT -->
      <div class="shop-hero-left mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>" id="heroContentBulk">
        <span class="shop-hero-tag">B2B &amp; Corporate Gifting</span>
        <h1 class="shop-hero-title">Crafted with Passion,<br>Made for Every Occasion.</h1>
        <p class="shop-hero-desc">
          From indulgent spreads to bean-to-bar chocolates and handcrafted delights, our products are made with the finest ingredients and can be customized for your brand, events, or gifting requirements.
        </p>

        <!-- 4 B2B Value Badges -->
        <div class="shop-hero-badges">
          <!-- 1: Premium Ingredients -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M7 20h10"></path>
                <path d="M10 20c0-4.4 2-7 4-10"></path>
                <path d="M20 7c-4 0-7 2-10 4"></path>
                <path d="M12 3a9 9 0 0 1 9 9c0 3-1.5 5.5-4 7"></path>
                <path d="M3.5 12A8.5 8.5 0 0 1 12 3.5"></path>
              </svg>
            </div>
            <span class="hero-badge-label">Premium<br>Ingredients</span>
          </div>

          <!-- 2: Customizable for Your Brand -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
            </div>
            <span class="hero-badge-label">Customizable<br>for Your Brand</span>
          </div>

          <!-- 3: B2B & Bulk Orders -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
              </svg>
            </div>
            <span class="hero-badge-label">B2B &amp;<br>Bulk Orders</span>
          </div>

          <!-- 4: Corporate Gifting & Events -->
          <div class="hero-badge-item">
            <div class="hero-badge-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 12 20 22 4 22 4 12"></polyline>
                <rect x="2" y="7" width="20" height="5"></rect>
                <line x1="12" y1="22" x2="12" y2="7"></line>
                <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
              </svg>
            </div>
            <span class="hero-badge-label">Corporate Gifting<br>&amp; Events</span>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="shop-hero-actions">
          <button type="button" class="btn-gold-lux" onclick="openEnquiryModal('Hero Requirement Enquiry')">
            Enquire for Your Requirement &rarr;
          </button>
          <a href="#explore-range" class="btn-outline-lux">
            Explore Products &darr;
          </a>
        </div>
      </div>

      <!-- Right Visual Showcase (Desktop shows background seamlessly; Mobile shows crisp image card) -->
      <div class="shop-hero-showcase-wrap">
        <img src="assets/products/shop_hero_luxury.jpg?v=20" alt="RT Chocos Luxury Gift Box & Artisanal Treats" class="shop-hero-mobile-photo" loading="eager" width="1024" height="576">
      </div>

    </div>
  </section>

  <?php if ($storeMode === 'hybrid'): ?>
  <!-- =========================================================================
       HYBRID MODE SWITCHER PILL (Allows visitors to toggle Retail vs Bulk)
       ========================================================================= -->
  <div class="hybrid-mode-toggle-wrap">
    <div class="hybrid-mode-toggle-bar">
      <button type="button" class="hybrid-toggle-btn active" id="hybridBtnRetail" onclick="setHybridView('retail')">
        <span>🛒</span>
        <span>Retail / Home Delivery</span>
      </button>
      <button type="button" class="hybrid-toggle-btn" id="hybridBtnBulk" onclick="setHybridView('bulk')">
        <span>📦</span>
        <span>Bulk &amp; Corporate Gifting</span>
      </button>
    </div>
  </div>
  <?php endif; ?>


  <!-- =========================================================================
       SECTION 2: CATALOG (Explore Our Range - Premium Products, Thoughtfully Crafted)
       ========================================================================= -->
  <section id="explore-range" class="shop-catalog-lux">
    <div class="catalog-header-lux">
      <div class="catalog-eyebrow-lux" id="catalogEyebrow"><?php echo $storeMode === 'bulk' ? 'Explore Our Range' : 'Our Fresh Batch Collection'; ?></div>
      <h2 class="catalog-title-lux" id="catalogTitle"><?php echo $storeMode === 'bulk' ? 'Premium Products, Thoughtfully Crafted' : 'Artisanal Treats — Handcrafted for You'; ?></h2>
    </div>

    <!-- Category Filter Tabs -->
    <div class="catalog-tabs-lux" id="catalog-filter-tabs">
      <button type="button" class="cat-tab-lux active" data-cat="all">All Products</button>
      <button type="button" class="cat-tab-lux" data-cat="spreads">Spreads &amp; Nut Butters</button>
      <button type="button" class="cat-tab-lux" data-cat="chocolates">Chocolates</button>
      <button type="button" class="cat-tab-lux" data-cat="ingredients">Ingredients</button>
      <button type="button" class="cat-tab-lux" data-cat="gifting">Corporate &amp; Gifting</button>
    </div>

    <!-- Product Grid: 10 Curated Products (5 columns) -->
    <div class="catalog-grid-lux" id="catalog-products-grid">
      <?php foreach ($curatedCatalog as $prod): 
        $prodPrice = (!empty($prod['sale_price']) && $prod['sale_price'] > 0) ? $prod['sale_price'] : ($prod['price'] ?? 0);
        $origPrice = $prod['price'] ?? 0;
        $hasSale = (!empty($prod['sale_price']) && $prod['sale_price'] > 0 && $prod['sale_price'] < $origPrice);
      ?>
      <div class="lux-product-card" data-category="<?php echo htmlspecialchars($prod['category_key']); ?>">
        <a href="shop/<?php echo htmlspecialchars($prod['slug']); ?>" class="lux-prod-img-wrap" title="View <?php echo htmlspecialchars($prod['name']); ?> details">
          <img src="<?php echo htmlspecialchars($prod['image']); ?>" 
               alt="<?php echo htmlspecialchars($prod['name']); ?> — RT Chocos" 
               loading="lazy">
          
          <!-- Mode-Aware Badges -->
          <span class="lux-card-badge-retail mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>">
            ✨ 100% Pure Cocoa
          </span>
          <?php if (!empty($prod['customizable'])): ?>
          <span class="lux-card-badge mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Customizable (MOQ: <?php echo htmlspecialchars($bulkMOQ); ?>)
          </span>
          <?php endif; ?>
        </a>
        <div class="lux-prod-content">
          <a href="shop/<?php echo htmlspecialchars($prod['slug']); ?>" class="lux-prod-title-link">
            <h3 class="lux-prod-title"><?php echo htmlspecialchars($prod['name']); ?></h3>
          </a>
          <p class="lux-prod-desc"><?php echo htmlspecialchars($prod['short_description']); ?></p>
          
          <!-- Mode-Aware Price / Wholesale Display -->
          <div class="lux-prod-price-row mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>">
            <span class="lux-prod-price">₹<?php echo number_format($prodPrice, 0); ?></span>
            <?php if ($hasSale): ?>
              <span class="lux-prod-orig-price">₹<?php echo number_format($origPrice, 0); ?></span>
              <span class="lux-prod-discount"><?php echo round((1 - $prod['sale_price'] / $origPrice) * 100); ?>% OFF</span>
            <?php endif; ?>
          </div>
          <div class="lux-prod-price-row mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>" style="font-size: 13px; font-weight: 700; color: var(--lux-gold-400); display: flex; align-items: center; justify-content: space-between;">
            <span>Wholesale Supply</span>
            <span style="font-size: 11.5px; font-weight: 500; color: var(--lux-text-muted);">From ₹<?php echo number_format($prodPrice, 0); ?>/unit</span>
          </div>

          <!-- Actions Retail: Add to Cart + Details Link -->
          <div class="lux-card-actions mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>">
            <button type="button" class="lux-btn-add-cart" onclick="quickAddToCart(<?php echo (int)($prod['id'] ?? 0); ?>, '<?php echo addslashes($prod['name']); ?>', this, event)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
              <span>Add to Cart</span>
            </button>
            <a href="shop/<?php echo htmlspecialchars($prod['slug']); ?>" class="lux-view-details-link">
              Details &rarr;
            </a>
          </div>

          <!-- Actions Bulk: Enquire Bulk + WhatsApp Quote + Details Link -->
          <div class="lux-card-actions mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>">
            <button type="button" class="lux-btn-bulk-enquiry" onclick="openEnquiryModal('<?php echo addslashes($prod['name']); ?>', '<?php echo addslashes($prod['category_key'] ?? ''); ?>')">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
              </svg>
              <span>Enquire Bulk</span>
            </button>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $bulkPhone); ?>?text=Hello%20RT%20Chocos!%20I%20have%20a%20bulk%2Fcorporate%20order%20enquiry%20for%20<?php echo urlencode($prod['name']); ?>%20(MOQ%20<?php echo urlencode($bulkMOQ); ?>)." target="_blank" class="lux-btn-bulk-wa" title="Instant WhatsApp Quote">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
              </svg>
            </a>
            <a href="shop/<?php echo htmlspecialchars($prod['slug']); ?>" class="lux-view-details-link">
              Details &rarr;
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>


  <!-- =========================================================================
       SECTION 3: PROCESS & STORY (Retail: Bean to Spoon | Bulk: Your Idea, Our Craft)
       ========================================================================= -->
  
  <!-- RETAIL PROCESS: FROM BEAN TO SPOON -->
  <section class="shop-custom-lux mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>" id="middleSectionRetail">
    <div class="shop-custom-container">
      <div class="shop-custom-header-row">
        <div class="shop-custom-lead">
          <span class="shop-custom-tag">How We Make It Pure</span>
          <h2 class="shop-custom-title">From Cocoa Farm to Your Spoon.<br>The Artisanal Craft.</h2>
          <p class="shop-custom-desc">
            We believe real chocolate should be uncompromised, clean, and extraordinary. We slow-grind and stone-conche in small batches to unleash the deepest natural cacao notes with zero artificial fillers or palm oil.
          </p>
          <a href="#explore-range" class="btn-custom-outline">
            Shop Fresh Batch &rarr;
          </a>
        </div>

        <div class="shop-custom-branding-img">
          <img src="assets/products/custom_branding_showcase.jpg" alt="RT Chocos Artisanal Chocolate Craftsmanship" loading="lazy">
        </div>
      </div>

      <!-- 5 Craft Steps Timeline -->
      <div class="shop-steps-row">
        <!-- Step 01 -->
        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 3a9 9 0 0 1 9 9c0 3-1.5 5.5-4 7"></path>
              <path d="M3.5 12A8.5 8.5 0 0 1 12 3.5"></path>
            </svg>
          </div>
          <span class="shop-step-number">01</span>
          <span class="shop-step-title">Single-Estate Cacao</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <!-- Step 02 -->
        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <span class="shop-step-number">02</span>
          <span class="shop-step-title">72h Stone Conching</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <!-- Step 03 -->
        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <path d="M9 12l2 2 4-4"></path>
            </svg>
          </div>
          <span class="shop-step-number">03</span>
          <span class="shop-step-title">100% Clean Ingredients</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <!-- Step 04 -->
        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="1" y="3" width="15" height="13"></rect>
              <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
              <circle cx="5.5" cy="18.5" r="2.5"></circle>
              <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
          </div>
          <span class="shop-step-number">04</span>
          <span class="shop-step-title">Chilled Packaging</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <!-- Step 05 -->
        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
              <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
              <line x1="6" y1="1" x2="6" y2="4"></line>
              <line x1="10" y1="1" x2="10" y2="4"></line>
              <line x1="14" y1="1" x2="14" y2="4"></line>
            </svg>
          </div>
          <span class="shop-step-number">05</span>
          <span class="shop-step-title">Savor at Your Table</span>
        </div>
      </div>
    </div>
  </section>

  <!-- BULK B2B PROCESS: YOUR IDEA, OUR CRAFT -->
  <section class="shop-custom-lux mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>" id="middleSectionBulk">
    <div class="shop-custom-container">
      <div class="shop-custom-header-row">
        <div class="shop-custom-lead">
          <span class="shop-custom-tag">Your Idea, Our Craft.</span>
          <h2 class="shop-custom-title">Endless Possibilities,<br>Created for You</h2>
          <p class="shop-custom-desc">
            From flavour to packaging, we work with you to create products and gifting solutions that reflect your brand and delight your audience.
          </p>
          <button type="button" class="btn-custom-outline" onclick="openEnquiryModal('Custom Branding Solution')">
            Explore Customization &rarr;
          </button>
        </div>

        <div class="shop-custom-branding-img">
          <img src="assets/products/custom_branding_showcase.jpg" alt="RT Chocos Bespoke Packaging and Corporate Branding Solution" loading="lazy">
        </div>
      </div>

      <!-- 5 B2B Steps Timeline -->
      <div class="shop-steps-row">
        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12h18"></path><path d="M4 12c0 5 3.5 9 8 9s8-4 8-9"></path><line x1="10" y1="12" x2="16" y2="4"></line><path d="M14.5 3.5l3 1.5"></path>
            </svg>
          </div>
          <span class="shop-step-number">01</span>
          <span class="shop-step-title">Choose Your Product</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 21a7 7 0 0 1-7-7c0-5 7-11 7-11s7 6 7 11a7 7 0 0 1-7 7z"></path><path d="M12 21v-9"></path><path d="M12 15c2-1.5 4-1 5 0"></path>
            </svg>
          </div>
          <span class="shop-step-number">02</span>
          <span class="shop-step-title">Customize Your Requirement</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
          </div>
          <span class="shop-step-number">03</span>
          <span class="shop-step-title">Select Format &amp; Pack Size</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
          </div>
          <span class="shop-step-number">04</span>
          <span class="shop-step-title">Add Your Branding</span>
        </div>

        <div class="step-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </div>

        <div class="step-node">
          <div class="step-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
            </svg>
          </div>
          <span class="shop-step-number">05</span>
          <span class="shop-step-title">Create Your Solution</span>
        </div>
      </div>
    </div>
  </section>


  <!-- =========================================================================
       SECTION 4: PERKS & VALUES (Retail: RT Chocos Promise | Bulk: B2B Partner)
       ========================================================================= -->

  <!-- RETAIL CONSUMER VALUE PILLARS -->
  <section class="shop-partner-lux mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>" id="lowerSectionRetail">
    <div class="shop-partner-container">
      <div class="partner-left-lead">
        <h2>Delight in Every Bite.<br>The RT Chocos<br>Promise.</h2>
        <p>
          We are obsessively committed to taste purity, ethical sourcing, and delivering the freshest possible artisanal chocolates straight to your doorstep across India.
        </p>
      </div>

      <div class="partner-cards-grid">
        <!-- Perk 1 -->
        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
          </div>
          <h3 class="partner-card-title">Chilled Express Delivery</h3>
          <p class="partner-card-text">Insulated thermal cold packs guarantee zero melting anywhere in India.</p>
        </div>

        <!-- Perk 2 -->
        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path>
            </svg>
          </div>
          <h3 class="partner-card-title">Zero Palm Oil Guarantee</h3>
          <p class="partner-card-text">Made exclusively with pure cocoa butter and California almonds/cashews.</p>
        </div>

        <!-- Perk 3 -->
        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 3a9 9 0 0 1 9 9c0 3-1.5 5.5-4 7"></path><path d="M3.5 12A8.5 8.5 0 0 1 12 3.5"></path>
            </svg>
          </div>
          <h3 class="partner-card-title">Wholesome Sweetness</h3>
          <p class="partner-card-text">Natural options sweetened with organic dates powder &amp; low-sugar profiles.</p>
        </div>

        <!-- Perk 4 -->
        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
            </svg>
          </div>
          <h3 class="partner-card-title">Gift-Ready Hampers</h3>
          <p class="partner-card-text">Artisanal presentation boxes ready for birthdays, anniversaries &amp; festivals.</p>
        </div>

        <!-- Perk 5 -->
        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line>
            </svg>
          </div>
          <h3 class="partner-card-title">100% Satisfaction Promise</h3>
          <p class="partner-card-text">Freshly batched by expert chocolatiers to give you true taste delight.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- BULK B2B PARTNER CARDS -->
  <section class="shop-partner-lux mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>" id="lowerSectionBulk">
    <div class="shop-partner-container">
      <div class="partner-left-lead">
        <h2>More Than a Product.<br>A Partner for Your<br>Requirement.</h2>
        <p>
          We provide customized food solutions for businesses of all sizes. From bulk orders to private label products, we help you delight your clients and make every occasion memorable.
        </p>
      </div>

      <div class="partner-cards-grid">
        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
          </div>
          <h3 class="partner-card-title">Bulk B2B Orders</h3>
          <p class="partner-card-text">Scalable solutions for businesses of all sizes.</p>
        </div>

        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
            </svg>
          </div>
          <h3 class="partner-card-title">Corporate Gifting</h3>
          <p class="partner-card-text">Premium gifting options for every occasion.</p>
        </div>

        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
          </div>
          <h3 class="partner-card-title">Customized Products</h3>
          <p class="partner-card-text">Tailored flavours, formats and packaging.</p>
        </div>

        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
            </svg>
          </div>
          <h3 class="partner-card-title">Private Label Solutions</h3>
          <p class="partner-card-text">Your brand, our expertise.</p>
        </div>

        <div class="partner-card-item">
          <div class="partner-card-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
              <path d="m14 10 3 3m-9-3 3 3"></path><path d="m2 22 1-1.5a14.5 14.5 0 0 0 11.5-3.5l1.5-1.5L8 7.5l-1.5 1.5A14.5 14.5 0 0 0 3 20.5L2 22Z"></path><path d="m17 7 1-1a3 3 0 0 1 4 4l-1 1"></path><circle cx="18" cy="3" r="1"></circle><circle cx="22" cy="8" r="1"></circle>
            </svg>
          </div>
          <h3 class="partner-card-title">Events &amp; Celebrations</h3>
          <p class="partner-card-text">Perfect for events, weddings and festive moments.</p>
        </div>
      </div>
    </div>
  </section>


  <!-- =========================================================================
       SECTION 5: BOTTOM CALL-TO-ACTION BANNER
       ========================================================================= -->

  <!-- RETAIL BOTTOM BANNER -->
  <section style="padding: 0 24px;" class="mode-section-retail <?php echo ($storeMode === 'bulk') ? 'mode-hidden' : ''; ?>" id="ctaBannerRetail">
    <div class="shop-cta-banner-lux">
      <div class="cta-banner-content">
        <div class="cta-banner-left">
          <div class="cta-bubble-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
          </div>
          <div>
            <h3>Free Pan-India Delivery on Orders Above ₹<?php echo $freeShippingMin; ?>!</h3>
            <p>Freshly handcrafted, carefully packed in insulated chilled containers, and shipped directly to your door.</p>
          </div>
        </div>

        <div class="cta-banner-actions">
          <a href="#explore-range" class="btn-gold-lux">
            Shop All Flavours &rarr;
          </a>
          <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $bulkPhone); ?>?text=Hi%20RT%20Chocos!%20I%20would%20like%20to%20order%20chocolates%20and%20spreads." 
             target="_blank" 
             rel="noopener" 
             class="btn-whatsapp-lux">
            <svg viewBox="0 0 24 24">
              <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            Order on WhatsApp
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- BULK B2B BOTTOM BANNER -->
  <section style="padding: 0 24px;" class="mode-section-bulk <?php echo ($storeMode === 'retail') ? 'mode-hidden' : ''; ?>" id="ctaBannerBulk">
    <div class="shop-cta-banner-lux">
      <div class="cta-banner-content">
        <div class="cta-banner-left">
          <div class="cta-bubble-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
              <circle cx="9" cy="11.5" r="1" fill="currentColor"></circle>
              <circle cx="12" cy="11.5" r="1" fill="currentColor"></circle>
              <circle cx="15" cy="11.5" r="1" fill="currentColor"></circle>
            </svg>
          </div>
          <div>
            <h3>Have a Bulk or Custom Requirement?</h3>
            <p>Tell us what you're looking for. We'll help you explore tailored product formulations, custom branding, and wholesale pricing.</p>
          </div>
        </div>

        <div class="cta-banner-actions">
          <button type="button" class="btn-gold-lux" onclick="openEnquiryModal('Requirement Banner Enquiry')">
            Enquire Now &rarr;
          </button>
          <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $bulkPhone); ?>?text=Hi%20Real%20Taste%2C%20I%20have%20an%20enquiry%20regarding%20your%20products%20and%20customization%20services." 
             target="_blank" 
             rel="noopener" 
             class="btn-whatsapp-lux">
            <svg viewBox="0 0 24 24">
              <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            WhatsApp Us
          </a>
        </div>
      </div>
    </div>
  </section>

  <?php
  // Embed shop FAQs if available
  $faqCategory = 'shop';
  $faqLimit = 4;
  if (file_exists(__DIR__ . '/includes/faq-block.php')) {
      include __DIR__ . '/includes/faq-block.php';
  }
  ?>

</main>


<!-- =========================================================================
     INTERACTIVE REQUIREMENT ENQUIRY MODAL
     ========================================================================= -->
<div id="enquiryModalBackdrop" class="enquiry-modal-backdrop" onclick="closeEnquiryModal(event)">
  <div class="enquiry-modal-card" onclick="event.stopPropagation()">
    <button type="button" class="modal-close-btn" onclick="closeEnquiryModal()">&times;</button>
    
    <h3 class="modal-heading">Custom Requirement Enquiry</h3>
    <p class="modal-sub">Tell us about your custom chocolate, spread, or gifting requirements. Our team will prepare a tailored proposal for you.</p>

    <form id="shopEnquiryForm" onsubmit="submitShopEnquiry(event)">
      <input type="hidden" name="subject" id="modalSubject" value="Shop Product Requirement">
      <input type="text" name="nickname" style="display:none;" tabindex="-1" autocomplete="off">

      <div class="modal-form-grid">
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--lux-gold-300); display:block; margin-bottom:6px;">Your Name *</label>
          <input type="text" name="name" class="modal-input" placeholder="e.g. Priya Sharma" required>
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:var(--lux-gold-300); display:block; margin-bottom:6px;">Phone / WhatsApp *</label>
          <input type="tel" name="phone" class="modal-input" placeholder="e.g. +91 98765 43210" required>
        </div>
        <div class="modal-form-full">
          <label style="font-size:12px; font-weight:600; color:var(--lux-gold-300); display:block; margin-bottom:6px;">Email Address *</label>
          <input type="email" name="email" class="modal-input" placeholder="e.g. priya@company.com" required>
        </div>
        <div class="modal-form-full">
          <label style="font-size:12px; font-weight:600; color:var(--lux-gold-300); display:block; margin-bottom:6px;">Product / Service Interest</label>
          <select name="product_interest" class="modal-input" style="background:#0b1c12; color:#FAF6EF;" id="modalProductSelect">
            <option value="Spreads & Nut Butters">Gourmet Spreads &amp; Nut Butters</option>
            <option value="Bean to Bar Chocolates">Artisanal Bean-to-Bar Chocolates</option>
            <option value="Corporate Gifting">Corporate &amp; Event Gifting</option>
            <option value="Private Label">Private Label &amp; Co-Branding</option>
            <option value="Bulk B2B Ingredients">Bulk B2B &amp; Ingredients</option>
          </select>
        </div>
        <div class="modal-form-full">
          <label style="font-size:12px; font-weight:600; color:var(--lux-gold-300); display:block; margin-bottom:6px;">Requirement Details (Quantity, customization, timeline) *</label>
          <textarea name="message" id="modalMessageField" class="modal-input" placeholder="Describe your required quantities, packaging preferences, custom branding, or preferred delivery dates..." required></textarea>
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:20px; flex-wrap:wrap;">
        <a id="modalWhatsAppBtn" href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $bulkPhone); ?>?text=Hi%20RT%20Chocos,%20I%20have%20a%20bulk%20enquiry." target="_blank" class="btn-outline-lux" style="display:inline-flex; align-items:center; gap:6px; color:#25D366; border-color:rgba(37,211,102,0.5); text-decoration:none; font-size:11.5px; padding:10px 16px;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
          </svg>
          <span>Chat on WhatsApp &rarr;</span>
        </a>
        <div style="display:flex; gap:10px;">
          <button type="button" class="btn-outline-lux" onclick="closeEnquiryModal()" style="font-size:11.5px; padding:10px 18px;">Cancel</button>
          <button type="submit" class="btn-gold-lux" id="modalSubmitBtn" style="font-size:11.5px; padding:10px 22px;">Submit Enquiry &rarr;</button>
        </div>
      </div>
      <div id="modalStatusMsg" style="margin-top:14px; font-size:13px; text-align:center; display:none;"></div>
    </form>
  </div>
</div>


<!-- =========================================================================
     CLIENT-SIDE FILTERING & MODAL SCRIPTS
     ========================================================================= -->
<script>
  // Tab Filter Logic
  document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.cat-tab-lux');
    const cards = document.querySelectorAll('.lux-product-card');

    tabs.forEach(tab => {
      tab.addEventListener('click', function() {
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const cat = this.getAttribute('data-cat');
        cards.forEach(card => {
          const cardCat = card.getAttribute('data-category');
          if (cat === 'all' || cardCat === cat) {
            card.style.display = 'flex';
            card.style.opacity = '0';
            setTimeout(() => {
              card.style.opacity = '1';
              card.style.transition = 'opacity 0.3s ease';
            }, 10);
          } else {
            card.style.display = 'none';
          }
        });
      });
    });

    // Check if category is preset in query params
    const urlParams = new URLSearchParams(window.location.search);
    const initialCategory = urlParams.get('category');
    if (initialCategory) {
      const lower = initialCategory.toLowerCase();
      let matchedTab = null;
      if (lower.includes('spread')) matchedTab = document.querySelector('[data-cat="spreads"]');
      else if (lower.includes('choco')) matchedTab = document.querySelector('[data-cat="chocolates"]');
      else if (lower.includes('ingredient')) matchedTab = document.querySelector('[data-cat="ingredients"]');
      else if (lower.includes('gift') || lower.includes('corporate')) matchedTab = document.querySelector('[data-cat="gifting"]');
      
      if (matchedTab) {
        matchedTab.click();
      }
    }
  });

  // Hybrid Mode Visitor Switcher (Retail vs Bulk)
  function setHybridView(view) {
    const btnRetail = document.getElementById('hybridBtnRetail');
    const btnBulk = document.getElementById('hybridBtnBulk');
    const retailEls = document.querySelectorAll('.mode-section-retail');
    const bulkEls = document.querySelectorAll('.mode-section-bulk');
    const eyebrow = document.getElementById('catalogEyebrow');
    const title = document.getElementById('catalogTitle');

    if (view === 'retail') {
      if (btnRetail) btnRetail.classList.add('active');
      if (btnBulk) btnBulk.classList.remove('active');
      retailEls.forEach(el => el.classList.remove('mode-hidden'));
      bulkEls.forEach(el => el.classList.add('mode-hidden'));
      if (eyebrow) eyebrow.innerText = 'Our Fresh Batch Collection';
      if (title) title.innerText = 'Artisanal Treats — Handcrafted for You';
      sessionStorage.setItem('shop_hybrid_mode', 'retail');
    } else {
      if (btnRetail) btnRetail.classList.remove('active');
      if (btnBulk) btnBulk.classList.add('active');
      retailEls.forEach(el => el.classList.add('mode-hidden'));
      bulkEls.forEach(el => el.classList.remove('mode-hidden'));
      if (eyebrow) eyebrow.innerText = 'Explore Our Range';
      if (title) title.innerText = 'Premium Products, Thoughtfully Crafted';
      sessionStorage.setItem('shop_hybrid_mode', 'bulk');
    }
  }

  // Restore saved hybrid view on load if in hybrid mode
  document.addEventListener('DOMContentLoaded', function() {
    const savedView = sessionStorage.getItem('shop_hybrid_mode');
    if (savedView && document.getElementById('hybridBtnRetail')) {
      setHybridView(savedView);
    }
  });

  // Modal Controls
  function openEnquiryModal(contextTitle, categoryKey) {
    const modal = document.getElementById('enquiryModalBackdrop');
    if (!modal) return;
    
    if (contextTitle) {
      document.getElementById('modalSubject').value = 'Bulk Enquiry: ' + contextTitle;
      const msgField = document.getElementById('modalMessageField');
      if (msgField) {
        msgField.value = 'Hello, I would like to enquire about a bulk / corporate order for ' + contextTitle + '. Please provide tiered volume pricing, customization options, and delivery timelines.';
      }
      const waBtn = document.getElementById('modalWhatsAppBtn');
      if (waBtn) {
        const phone = '<?php echo preg_replace('/[^0-9]/', '', $bulkPhone); ?>';
        waBtn.href = 'https://wa.me/' + phone + '?text=' + encodeURIComponent('Hi RT Chocos! I would like to inquire about bulk ordering for ' + contextTitle + '.');
      }
    }
    
    if (categoryKey) {
      const select = document.getElementById('modalProductSelect');
      if (select) {
        for (let i = 0; i < select.options.length; i++) {
          if (select.options[i].value.toLowerCase().includes(categoryKey.toLowerCase()) || 
              categoryKey.toLowerCase().includes(select.options[i].value.toLowerCase())) {
            select.selectedIndex = i;
            break;
          }
        }
      }
    }
    
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeEnquiryModal(event) {
    if (event && event.target !== document.getElementById('enquiryModalBackdrop')) {
      return;
    }
    const modal = document.getElementById('enquiryModalBackdrop');
    if (modal) modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  // Auto-open modal if URL has #bulk-enquiry or #enquiry
  document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#bulk-enquiry' || window.location.hash === '#enquiry') {
      setTimeout(() => openEnquiryModal('Announcement Banner Link'), 350);
    }
  });

  // Form Submission
  async function submitShopEnquiry(event) {
    event.preventDefault();
    const form = document.getElementById('shopEnquiryForm');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const statusMsg = document.getElementById('modalStatusMsg');
    
    submitBtn.disabled = true;
    submitBtn.innerText = 'Sending...';
    statusMsg.style.display = 'none';

    const formData = new FormData(form);
    const data = {
      name: formData.get('name'),
      phone: formData.get('phone'),
      email: formData.get('email'),
      subject: formData.get('subject') + ' [' + formData.get('product_interest') + ']',
      message: 'Product Interest: ' + formData.get('product_interest') + '\n\n' + formData.get('message'),
      nickname: formData.get('nickname')
    };

    try {
      const targetUrl = (typeof pathPrefix !== 'undefined' && pathPrefix ? pathPrefix : '/') + 'send_contact.php';
      const resp = await fetch(targetUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const res = await resp.json();
      if (res.status === 'success') {
        statusMsg.style.color = '#74E291';
        statusMsg.innerText = '✓ Thank you! Your bulk requirement has been submitted. Our team will contact you shortly.';
        statusMsg.style.display = 'block';
        form.reset();
        setTimeout(() => {
          closeEnquiryModal();
          statusMsg.style.display = 'none';
          submitBtn.disabled = false;
          submitBtn.innerText = 'Submit Enquiry \u2192';
        }, 2500);
      } else {
        statusMsg.style.color = '#ff6b6b';
        statusMsg.innerText = res.message || 'Submission error. Please check your inputs.';
        statusMsg.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.innerText = 'Submit Enquiry \u2192';
      }
    } catch (err) {
      console.error('Enquiry error:', err);
      statusMsg.style.color = '#ff6b6b';
      statusMsg.innerText = 'Notice: We received your click. If submission took long, please message us on WhatsApp directly.';
      statusMsg.style.display = 'block';
      submitBtn.disabled = false;
      submitBtn.innerText = 'Submit Enquiry \u2192';
    }
  }

  // Quick Add to Cart from Shop Catalog
  function quickAddToCart(productId, productName, btn, event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    if (!productId) return;
    
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin-icon"><circle cx="12" cy="12" r="10" stroke-opacity="0.3"></circle><path d="M12 2a10 10 0 0 1 10 10"></path></svg> <span>Adding...</span>';

    fetch('/api_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.success) {
        btn.innerHTML = '<span style="color:#74E291;">✓ Added!</span>';
        btn.classList.add('btn-added');

        // Update Header Badge
        const badge = document.getElementById('header-cart-count');
        if (badge && typeof data.cart_count !== 'undefined') {
          badge.textContent = data.cart_count;
          badge.classList.remove('hidden');
          badge.style.transform = 'scale(1.4)';
          setTimeout(() => { badge.style.transform = ''; }, 300);
        }

        // Show floating toast
        showCartToast(productName, data.cart_count);

        setTimeout(function() {
          btn.innerHTML = originalContent;
          btn.classList.remove('btn-added');
          btn.disabled = false;
        }, 2200);
      } else {
        btn.innerHTML = '<span style="color:#FF8080;">✗ ' + (data.error || 'Error') + '</span>';
        setTimeout(function() {
          btn.innerHTML = originalContent;
          btn.disabled = false;
        }, 2500);
      }
    })
    .catch(function(err) {
      btn.innerHTML = '<span style="color:#FF8080;">✗ Error</span>';
      setTimeout(function() {
        btn.innerHTML = originalContent;
        btn.disabled = false;
      }, 2500);
    });
  }

  // Floating Toast Notification
  function showCartToast(productName, count) {
    let existingToast = document.getElementById('luxCartToast');
    if (existingToast) existingToast.remove();

    const toast = document.createElement('div');
    toast.id = 'luxCartToast';
    toast.className = 'lux-cart-toast';
    toast.innerHTML = `
      <div class="toast-icon-wrap">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#74E291" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>
      <div class="toast-content">
        <div class="toast-title">Added to Shopping Cart</div>
        <div class="toast-prod-name">${escapeHtml(productName)}</div>
      </div>
      <div class="toast-btns">
        <a href="/cart.php" class="toast-link-cart">View Cart (${count})</a>
        <a href="/checkout.php" class="toast-link-checkout">Checkout &rarr;</a>
      </div>
      <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
    `;

    document.body.appendChild(toast);
    setTimeout(() => { toast.classList.add('show'); }, 10);

    setTimeout(() => {
      if (toast && toast.parentElement) {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
      }
    }, 5500);
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
</script>

<?php
// ItemList schema for SEO
$itemList = [];
foreach ($curatedCatalog as $idx => $item) {
    $itemList[] = [
        "@type" => "ListItem",
        "position" => $idx + 1,
        "name" => $item['name'],
        "url" => "https://www.rtchocos.com/shop/" . $item['slug']
    ];
}
echo '<script type="application/ld+json">' . "\n";
echo json_encode(["@context" => "https://schema.org", "@type" => "ItemList", "itemListElement" => $itemList], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo "\n" . '</script>' . "\n";

include $pathPrefix . 'includes/footer.php';
?>
