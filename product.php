<?php
  session_start();
  require_once __DIR__ . '/includes/db.php';

  // Fallback curated catalog products matching shop definitions
  $curatedCatalogFallback = [
      'almond-halwa-spread' => [
          'id' => 6,
          'name' => 'Almond Halwa Spread',
          'slug' => 'almond-halwa-spread',
          'category' => 'Spreads & Nut Butters',
          'short_description' => 'The richness of traditional almond halwa, reimagined in a smooth, indulgent spread.',
          'long_description' => "Our signature Almond Halwa Spread is an artisanal reinterpretation of India's beloved royal dessert. Slow-roasted California almonds are stone-ground with fragrant green cardamom, organic raw sugar, and pure cacao butter to produce a velvety, melt-in-the-mouth texture. Completely palm-oil free and 100% natural.",
          'image_main' => 'assets/products/almond_halwa_spread.jpg',
          'image_gallery' => '[]',
          'price' => 380,
          'sale_price' => 349,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'cashew-coconut-spread' => [
          'id' => 7,
          'name' => 'Cashew Coconut Spread',
          'slug' => 'cashew-coconut-spread',
          'category' => 'Spreads & Nut Butters',
          'short_description' => 'A delicate blend of roasted cashews and coconut with a naturally indulgent character.',
          'long_description' => 'Slow-roasted Goan cashews paired with desiccated coastal coconut and cold-pressed cacao butter. Smooth, rich, and naturally creamy with a mild tropical sweetness.',
          'image_main' => 'assets/products/cashew_coconut_spread.jpg',
          'image_gallery' => '[]',
          'price' => 390,
          'sale_price' => 360,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'crunchy-almond-spread' => [
          'id' => 8,
          'name' => 'Crunchy Almond Spread',
          'slug' => 'crunchy-almond-spread',
          'category' => 'Spreads & Nut Butters',
          'short_description' => 'Roasted almonds combined with a delightful crunch for a memorable bite.',
          'long_description' => 'Stone-ground almond butter enriched with crushed roasted almond nibs for an exquisite textural crunch. Clean label, rich in protein, and free from hydrogenated fats.',
          'image_main' => 'assets/products/crunchy_almond_spread.jpg',
          'image_gallery' => '[]',
          'price' => 370,
          'sale_price' => 340,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'almond-spread' => [
          'id' => 9,
          'name' => 'Crunchy Almond Spread',
          'slug' => 'almond-spread',
          'category' => 'Spreads & Nut Butters',
          'short_description' => 'Roasted almonds combined with a delightful crunch for a memorable bite.',
          'long_description' => 'Stone-ground almond butter enriched with crushed roasted almond nibs for an exquisite textural crunch. Clean label, rich in protein, and free from hydrogenated fats.',
          'image_main' => 'assets/products/crunchy_almond_spread.jpg',
          'image_gallery' => '[]',
          'price' => 370,
          'sale_price' => 340,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'classic-almond-spread' => [
          'id' => 8,
          'name' => 'Classic Almond Butter Spread',
          'slug' => 'classic-almond-spread',
          'category' => 'Spreads & Nut Butters',
          'short_description' => 'Classic, creamy and crafted around the richness of premium slow-roasted almonds.',
          'long_description' => 'Stone-ground for 24 hours to achieve an ultra-smooth, creamy texture. Made solely with high-grade California almonds and a touch of sea salt. Free from hydrogenated oils and artificial preservatives.',
          'image_main' => 'assets/products/almond_spread.jpg',
          'image_gallery' => '[]',
          'price' => 350,
          'sale_price' => 325,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'almond-bark' => [
          'id' => 10,
          'name' => 'Almond Bark',
          'slug' => 'almond-bark',
          'category' => 'Chocolates',
          'short_description' => 'Premium chocolate layered with roasted almonds for an indulgent, textured bite.',
          'long_description' => 'Our Artisan Almond Bark combines single-origin dark and milk chocolate slabs with freshly roasted, lightly salted Californian almonds. Hand-cracked into rustic pieces, each bite delivers a rich snap and nutty crunch.',
          'image_main' => 'assets/products/almond_bark.jpg',
          'image_gallery' => '[]',
          'price' => 420,
          'sale_price' => 390,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'bean-to-bar-dark-chocolate' => [
          'id' => 11,
          'name' => 'Bean to Bar Dark Chocolate',
          'slug' => 'bean-to-bar-dark-chocolate',
          'category' => 'Chocolates',
          'short_description' => 'Crafted from carefully sourced cocoa for a rich and refined chocolate experience.',
          'long_description' => 'Handcrafted using 72% single-estate Indian cacao beans from Idukki, Kerala. Roasted to highlight deep notes of red berry, toasted oak, and malted cocoa with a silky, slow melt.',
          'image_main' => 'assets/products/bean_to_bar_dark.jpg',
          'image_gallery' => '[]',
          'price' => 290,
          'sale_price' => 265,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'bean-to-bar-milk-chocolate' => [
          'id' => 12,
          'name' => 'Bean to Bar Milk Chocolate',
          'slug' => 'bean-to-bar-milk-chocolate',
          'category' => 'Chocolates',
          'short_description' => 'Smooth, creamy and beautifully balanced for a luxurious chocolate experience.',
          'long_description' => 'A sophisticated 55% dark-milk chocolate crafted from Indian cacao and whole milk solids, caramelized gently during conching for warm caramel undertones and honey finish.',
          'image_main' => 'assets/products/bean_to_bar_milk.jpg',
          'image_gallery' => '[]',
          'price' => 280,
          'sale_price' => 255,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'bean-to-bar-white-chocolate' => [
          'id' => 13,
          'name' => 'Bean to Bar White Chocolate',
          'slug' => 'bean-to-bar-white-chocolate',
          'category' => 'Chocolates',
          'short_description' => 'Rich, velvety and delicately crafted for a refined indulgence.',
          'long_description' => 'Made from cold-pressed, unrefined cocoa butter, whole milk, and real Bourbon vanilla bean specks. Wonderfully balanced sweetness with creamy floral complexity.',
          'image_main' => 'assets/products/bean_to_bar_white.jpg',
          'image_gallery' => '[]',
          'price' => 295,
          'sale_price' => 270,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'dates-powder' => [
          'id' => 14,
          'name' => 'Dates Powder',
          'slug' => 'dates-powder',
          'category' => 'Ingredients',
          'short_description' => 'Natural dates transformed into a versatile powder with rich, caramel-like notes.',
          'long_description' => '100% pure dehydrated Arabian dates ground into a superfine, golden-brown culinary powder. A wholesome, nutrient-dense natural sweetener packed with fiber, potassium, and warm caramel notes.',
          'image_main' => 'assets/products/dates_powder.jpg',
          'image_gallery' => '[]',
          'price' => 260,
          'sale_price' => 230,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'handcrafted-chocolates' => [
          'id' => 15,
          'name' => 'Handcrafted Chocolates',
          'slug' => 'handcrafted-chocolates',
          'category' => 'Corporate & Gifting',
          'short_description' => 'Artisan chocolates designed for celebrations, gifting and special occasions.',
          'long_description' => 'An opulent assortment of handcrafted bonbons, single-origin pralines, and gianduja truffles. Hand-decorated with natural cocoa butter pigments and packaged in our signature keepsake gift box.',
          'image_main' => 'assets/products/handcrafted_chocolates.jpg',
          'image_gallery' => '[]',
          'price' => 650,
          'sale_price' => 599,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'customized-chocolate-inclusion-bar' => [
          'id' => 17,
          'name' => 'Customized Chocolate Inclusion Bar',
          'slug' => 'customized-chocolate-inclusion-bar',
          'category' => 'Chocolates',
          'short_description' => 'Bespoke single-origin craft chocolate bars tailored with premium nuts, spices, and custom corporate sleeves.',
          'long_description' => 'Handcrafted bean-to-bar inclusion slabs customized with roasted nuts, Himalayan pink salt, candied peel, or botanical infusions. Complete with custom embossed wrappers for brands and corporate clients.',
          'image_main' => 'assets/products/custom_branding_showcase.jpg',
          'image_gallery' => '[]',
          'price' => 450,
          'sale_price' => 410,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'hazelnut-spread-clean-label-product-healthy-gifting' => [
          'id' => 18,
          'name' => 'Hazelnut Spread | Clean Label Product | Healthy Gifting',
          'slug' => 'hazelnut-spread-clean-label-product-healthy-gifting',
          'category' => 'Spreads & Nut Butters',
          'short_description' => 'A clean-label, slow-roasted hazelnut and dark chocolate spread crafted without palm oil.',
          'long_description' => 'Made with 60% slow-roasted hazelnuts, single-origin cacao, and unrefined coconut sugar. Silky, deeply nutty, and free from palm oil or chemical stabilizers.',
          'image_main' => 'assets/products/hazelnut_spread.jpg',
          'image_gallery' => '[]',
          'price' => 420,
          'sale_price' => 385,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'gourmet-stuffed-dates-gift-box' => [
          'id' => 19,
          'name' => 'Gourmet Stuffed Dates Gift Box',
          'slug' => 'gourmet-stuffed-dates-gift-box',
          'category' => 'Corporate & Gifting',
          'short_description' => 'Hand-selected jumbo Medjool dates stuffed with roasted nuts and dipped in artisan dark chocolate.',
          'long_description' => 'Premium imported Medjool dates filled with roasted pistachio, almond slivers, and candied citrus peel, hand-dipped in 70% single-origin dark chocolate. Elegantly packaged in a keepsake gift chest.',
          'image_main' => 'assets/products/gourmet_stuffed_dates.jpg',
          'image_gallery' => '[]',
          'price' => 750,
          'sale_price' => 699,
          'stock_quantity' => 100,
          'is_active' => 1
      ],
      'the-rocher-collection' => [
          'id' => 20,
          'name' => 'The Rocher Collection',
          'slug' => 'the-rocher-collection',
          'category' => 'Corporate & Gifting',
          'short_description' => 'Spherical hazelnut praline truffles coated in crushed roasted nuts and crisp chocolate glaze.',
          'long_description' => 'An artisan homage to the classic Rocher: a whole roasted hazelnut centered in velvety hazelnut gianduja, wrapped in a wafer shell and enrobed in milk chocolate with roasted hazelnut nibs.',
          'image_main' => 'assets/products/the_rocher_collection.jpg',
          'image_gallery' => '[]',
          'price' => 680,
          'sale_price' => 620,
          'stock_quantity' => 100,
          'is_active' => 1
      ]
  ];

  // Get product slug or id from URL
  $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

  $product = null;
  $pdo = null;

  try {
      $pdo = get_db();
      if (!empty($slug)) {
          $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1 LIMIT 1");
          $stmt->execute([$slug]);
          $product = $stmt->fetch(PDO::FETCH_ASSOC);
      } elseif ($id > 0) {
          $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
          $stmt->execute([$id]);
          $product = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($product) {
              $slug = $product['slug'];
          }
      }
  } catch (Exception $e) {
      $product = null;
  }

  // Graceful fallback to curated catalog if DB lookup returned nothing
  if (!$product) {
      if (!empty($slug) && isset($curatedCatalogFallback[$slug])) {
          $product = $curatedCatalogFallback[$slug];
      } elseif ($id > 0) {
          foreach ($curatedCatalogFallback as $cp) {
              if ($cp['id'] === $id) {
                  $product = $cp;
                  $slug = $cp['slug'];
                  break;
              }
          }
      } elseif (empty($slug) && empty($id)) {
          // If completely empty, redirect to shop
          header('Location: shop.php');
          exit;
      }
  }

  if (!$product) {
      header('HTTP/1.0 404 Not Found');
      include __DIR__ . '/error.php';
      exit;
  }

  // Path prefix detection
  $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
  $pathPrefix = (strpos($requestPath, '/shop/') !== false) ? '../' : '';

  // Smart high-res image resolver
  if (!function_exists('resolveProductImageSafe')) {
      function resolveProductImageSafe($img, $slug = '', $pathPrefix = '') {
          if (!empty($img)) {
              if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0 || strpos($img, 'data:') === 0) {
                  return $img;
              }
              $clean = ltrim($img, '/');
              if (file_exists(__DIR__ . '/' . $clean)) {
                  return $pathPrefix . $clean;
              }
          }
          // Check slug-derived filenames on disk
          if (!empty($slug)) {
              $slugJpg = 'assets/products/' . str_replace('-', '_', $slug) . '.jpg';
              if (file_exists(__DIR__ . '/' . $slugJpg)) {
                  return $pathPrefix . $slugJpg;
              }
              $slugPng = 'assets/products/' . str_replace('-', '_', $slug) . '.png';
              if (file_exists(__DIR__ . '/' . $slugPng)) {
                  return $pathPrefix . $slugPng;
              }
          }
          return $pathPrefix . 'assets/premium_chocolate.png';
      }
  }

  // Auto-generate SEO metadata if blank
  $autoTitle = $product['name'] . " | Buy Bean-to-Bar Chocolate Online | RT Chocos India";
  $autoDesc = substr("Buy " . $product['name'] . " — premium " . strtolower($product['category'] ?? 'Chocolate') . " from RT Chocos, India's first bean-to-bar chocolate academy. Artisan quality, ethically sourced Indian cacao.", 0, 155);

  $pageTitle = !empty($product['meta_title']) ? $product['meta_title'] : $autoTitle;
  $pageDescription = !empty($product['meta_description']) ? $product['meta_description'] : $autoDesc;
  $pageKeywords = !empty($product['meta_keywords']) ? $product['meta_keywords'] : "buy " . strtolower($product['name']) . ", " . strtolower($product['category'] ?? 'craft chocolate') . " India, bean to bar chocolate online, RT Chocos shop, craft chocolate India";

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $host = $_SERVER['HTTP_HOST'] ?? 'www.rtchocos.com';
  $canonicalUrl = $protocol . "://" . $host . "/shop/" . $product['slug'];

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Shop', 'item' => 'https://www.rtchocos.com/shop.php'],
      ['name' => $product['category'] ?? 'Chocolates', 'item' => 'https://www.rtchocos.com/shop.php?category=' . urlencode($product['category'] ?? 'Chocolates')],
      ['name' => $product['name'], 'item' => $canonicalUrl]
  ];

  include __DIR__ . '/includes/header.php';

  // Parse image gallery with validation
  $gallery = [];
  if (!empty($product['image_gallery'])) {
      $gallery = json_decode($product['image_gallery'], true) ?: [];
  }
  
  $allImages = [];
  $mainResolved = resolveProductImageSafe($product['image_main'] ?? '', $product['slug'], $pathPrefix);
  $allImages[] = $mainResolved;

  foreach ($gallery as $gImg) {
      $res = resolveProductImageSafe($gImg, '', $pathPrefix);
      if ($res && !in_array($res, $allImages)) {
          $allImages[] = $res;
      }
  }

  // Display price
  $displayPrice = (!empty($product['sale_price']) && $product['sale_price'] > 0) ? $product['sale_price'] : ($product['price'] ?? 0);
  $hasDiscount = (!empty($product['sale_price']) && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']);

  // Related products (same category, exclude current)
  $relatedProducts = [];
  if ($pdo) {
      try {
          $relStmt = $pdo->prepare("SELECT id, slug, name, short_description, price, sale_price, category, image_main FROM products WHERE category = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 3");
          $relStmt->execute([$product['category'] ?? 'Chocolates', $product['id'] ?? 0]);
          $relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (Exception $e) {
          $relatedProducts = [];
      }
  }
  if (empty($relatedProducts)) {
      // Fallback from curated catalog
      foreach ($curatedCatalogFallback as $fallbackProd) {
          if (($fallbackProd['slug'] ?? '') !== $product['slug'] && ($fallbackProd['category'] ?? '') === ($product['category'] ?? '')) {
              $relatedProducts[] = $fallbackProd;
              if (count($relatedProducts) >= 3) break;
          }
      }
  }

  // Find a related blog article (match category keyword in title/excerpt)
  $relatedBlog = null;
  if ($pdo) {
      try {
          $categoryKeyword = '%' . strtolower(explode(' ', $product['category'] ?? 'chocolate')[0]) . '%';
          $blogStmt = $pdo->prepare("SELECT slug, title FROM blogs WHERE is_published = 1 AND (LOWER(title) LIKE ? OR LOWER(excerpt) LIKE ?) ORDER BY RAND() LIMIT 1");
          $blogStmt->execute([$categoryKeyword, $categoryKeyword]);
          $relatedBlog = $blogStmt->fetch(PDO::FETCH_ASSOC);
      } catch (Exception $e) {}
  }
?>

<main>
<div id="page-product" class="page active" style="padding-top:100px;">
  <div class="section">
    <!-- Breadcrumb text -->
    <div style="font-family:'Jost',sans-serif; font-size:13px; color:var(--brown-light); margin-bottom:24px; font-weight:300;">
      <a href="<?php echo $pathPrefix; ?>index.php" style="color:var(--brown-light); text-decoration:none;">Home</a> › 
      <a href="<?php echo $pathPrefix; ?>shop.php" style="color:var(--brown-light); text-decoration:none;">Shop</a> › 
      <a href="<?php echo $pathPrefix; ?>shop.php?category=<?php echo urlencode($product['category'] ?? 'Chocolates'); ?>" style="color:var(--brown-light); text-decoration:none;"><?php echo htmlspecialchars($product['category'] ?? 'Chocolates'); ?></a> › 
      <span style="color:var(--brown); font-weight:600;"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

<style>
  .product-layout-grid {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 48px;
    align-items: start;
  }
  .product-gallery-sticky {
    position: sticky;
    top: 90px;
  }
  .product-main-image-wrap {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 12px 36px rgba(59,42,34,0.12), 0 2px 8px rgba(0,0,0,0.04);
    background: #FAF6F0;
    aspect-ratio: 4 / 3;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(197, 160, 89, 0.25);
    cursor: crosshair;
  }
  .product-main-img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    transform-origin: center center;
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
  }
  .product-zoom-badge {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(12, 30, 20, 0.8);
    backdrop-filter: blur(8px);
    color: #F8E7BE;
    font-family: 'Jost', sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 5px 11px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    pointer-events: none;
    opacity: 0.9;
    transition: opacity 0.2s ease;
    border: 1px solid rgba(232, 184, 92, 0.3);
  }
  .product-main-image-wrap:hover .product-zoom-badge {
    opacity: 0;
  }
  .product-thumb-strip {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    flex-wrap: wrap;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
  }
  .product-thumb-item {
    width: 74px;
    height: 74px;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.75;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
    background: #FAF6F0;
  }
  .product-thumb-item:hover,
  .product-thumb-item.active {
    opacity: 1;
    border-color: #C5A059;
    box-shadow: 0 4px 14px rgba(197, 160, 89, 0.35);
    transform: translateY(-2px);
  }
  .product-action-row {
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 18px;
    flex-wrap: wrap;
  }
  @media (max-width: 900px) {
    .product-layout-grid {
      grid-template-columns: 1fr !important;
      gap: 32px !important;
    }
    .product-gallery-sticky {
      position: static !important;
    }
    .product-action-row {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 10px !important;
    }
    .product-action-row .btn-primary,
    .product-action-row .btn-outline {
      width: 100% !important;
      justify-content: center !important;
      text-align: center !important;
      padding: 13px 20px !important;
    }
    .product-qty-stepper {
      width: 100% !important;
      justify-content: center !important;
    }
    .product-thumb-strip {
      flex-wrap: nowrap !important;
    }
  }
</style>

    <div class="product-layout-grid">
      <!-- Image Gallery Column -->
      <div class="product-gallery-sticky">
        <?php if (!empty($allImages)): 
          $mainImgSrc = $allImages[0];
        ?>
        <div id="product-main-image" class="product-main-image-wrap">
          <img id="main-img" 
               src="<?php echo htmlspecialchars($mainImgSrc); ?>" 
               alt="<?php echo htmlspecialchars($product['name']); ?> — Bean-to-Bar Chocolate, RT Chocos India" 
               class="product-main-img"
               loading="eager"
               fetchpriority="high"
               decoding="async"
               onerror="this.onerror=null;this.src='<?php echo $pathPrefix; ?>assets/premium_chocolate.png';">
          <div class="product-zoom-badge">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
            <span>Hover to Inspect</span>
          </div>
        </div>
        <?php if (count($allImages) > 1): ?>
        <div class="product-thumb-strip">
          <?php foreach ($allImages as $idx => $img): ?>
          <img src="<?php echo htmlspecialchars($img); ?>" 
               alt="<?php echo htmlspecialchars($product['name']); ?> view <?php echo $idx + 1; ?>" 
               loading="lazy"
               class="product-thumb-item <?php echo $idx === 0 ? 'active' : ''; ?>"
               onclick="switchProductMainImage(this, '<?php echo htmlspecialchars(addslashes($img)); ?>')"
               onerror="this.style.display='none';">
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Product Details Column -->
      <div>
        <div class="section-label" style="margin-bottom:8px;"><?php echo htmlspecialchars($product['category'] ?? 'Artisanal Selection'); ?></div>
        <h1 style="font-family:'Cormorant Garamond',serif; font-size:clamp(28px, 4vw, 38px); font-weight:700; color:var(--brown); margin-bottom:12px; line-height:1.2;">
          <?php echo htmlspecialchars($product['name']); ?>
        </h1>
        
        <div style="font-family:'Jost',sans-serif; font-size:28px; font-weight:700; color:var(--brown); margin-bottom:16px;">
          ₹<?php echo number_format($displayPrice, 0); ?>
          <?php if ($hasDiscount): ?>
          <span style="font-size:16px; font-weight:400; color:var(--brown-light); text-decoration:line-through; margin-left:8px;">₹<?php echo number_format($product['price'], 0); ?></span>
          <span style="font-size:13px; font-weight:600; color:#27ae60; margin-left:8px;">
            <?php echo round((1 - $product['sale_price'] / $product['price']) * 100); ?>% OFF
          </span>
          <?php endif; ?>
        </div>

        <?php if (!empty($product['short_description'])): ?>
        <p style="font-family:'Jost',sans-serif; font-size:15px; line-height:1.7; color:var(--brown-light); font-weight:300; margin-bottom:20px;">
          <?php echo htmlspecialchars($product['short_description']); ?>
        </p>
        <?php endif; ?>

        <!-- Mode-Aware Order / Add to Cart Actions -->
        <?php 
          $prodStoreMode = get_site_setting('store_mode', 'retail');
          $prodBulkPhone = get_site_setting('bulk_enquiry_phone', '+919140238741');
          $prodBulkMOQ = get_site_setting('bulk_min_order_qty', '50 Units');
          $numericMOQ = (int)filter_var($prodBulkMOQ, FILTER_SANITIZE_NUMBER_INT) ?: 50;
          $defaultQty = ($prodStoreMode === 'bulk') ? $numericMOQ : 1;
        ?>

        <?php if ($prodStoreMode === 'bulk'): ?>
        <div style="background: rgba(229,179,88,0.1); border: 1px solid rgba(229,179,88,0.35); border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
          <div style="font-weight: 700; color: #8C6D23; font-size: 14px; margin-bottom: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span>📦 B2B Wholesale &amp; Corporate Gifting</span>
            <span style="font-size: 11px; background: #E5B358; color: #07150E; padding: 2px 8px; border-radius: 12px; font-weight: 800;">MOQ: <?php echo htmlspecialchars($prodBulkMOQ); ?></span>
          </div>
          <p style="font-size: 13px; color: var(--brown-light); margin: 0; line-height: 1.5;">
            Custom branding, festive gift packaging, and tiered volume pricing available. Minimum order quantity: <strong><?php echo htmlspecialchars($prodBulkMOQ); ?></strong>.
          </p>
        </div>
        <?php endif; ?>

        <?php if (!isset($product['stock_quantity']) || (int)$product['stock_quantity'] != 0): ?>
        <div class="product-action-row">
          <div class="product-qty-stepper" style="display:flex; align-items:center; gap:0; border:1px solid rgba(59,42,34,0.15); border-radius:8px; overflow:hidden;">
            <button onclick="updateQty(-1)" style="width:38px; height:38px; border:none; background:var(--cream); cursor:pointer; font-size:18px; color:var(--brown);">−</button>
            <input id="qty-input" type="number" value="<?php echo $defaultQty; ?>" min="<?php echo ($prodStoreMode === 'bulk') ? $numericMOQ : 1; ?>" max="<?php echo (!empty($product['stock_quantity']) && $product['stock_quantity'] > 0) ? $product['stock_quantity'] : 9999; ?>" style="width:56px; height:38px; border:none; text-align:center; font-family:'Jost',sans-serif; font-size:15px; color:var(--brown); outline:none;">
            <button onclick="updateQty(1)" style="width:38px; height:38px; border:none; background:var(--cream); cursor:pointer; font-size:18px; color:var(--brown);">+</button>
          </div>

          <?php if ($prodStoreMode === 'bulk'): ?>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $prodBulkPhone); ?>?text=Hello%20RT%20Chocos!%20I%20have%20a%20bulk%20enquiry%20for%20<?php echo urlencode($product['name']); ?>%20(Requested%20Qty:%20<?php echo $defaultQty; ?>%20Units)." target="_blank" class="btn-primary" style="text-decoration:none; padding:11px 22px; display:inline-flex; align-items:center; gap:6px; background:#25D366; border-color:#25D366; color:#FFFFFF; font-weight:700;">
              💬 Instant WhatsApp Quote &rarr;
            </a>
            <button class="btn-outline" onclick="addToCart(<?php echo (int)($product['id'] ?? 0); ?>)" style="padding:11px 20px;">
              Add Wholesale Qty to Cart
            </button>
            <a href="<?php echo $pathPrefix; ?>shop.php#bulk-enquiry" class="btn-outline" style="text-decoration:none; padding:11px 18px;">
              Request Custom RFP Form &rarr;
            </a>
          <?php else: ?>
            <button class="btn-primary" onclick="addToCart(<?php echo (int)($product['id'] ?? 0); ?>)" style="padding:10px 28px;">
              Add to Cart
            </button>
            <a href="<?php echo $pathPrefix; ?>cart.php" class="btn-outline" style="text-decoration:none; padding:10px 20px;">View Cart</a>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $prodBulkPhone); ?>?text=Hello%20RT%20Chocos!%20I%20have%20a%20bulk%20enquiry%20for%20<?php echo urlencode($product['name']); ?>%20(MOQ%20<?php echo urlencode($prodBulkMOQ); ?>)." target="_blank" class="btn-outline" style="text-decoration:none; padding:10px 18px; border-color:var(--gold-light); color:var(--text-main); font-weight:600;">
              📦 Bulk Inquiry (MOQ: <?php echo htmlspecialchars($prodBulkMOQ); ?>)
            </a>
          <?php endif; ?>
        </div>

        <!-- Value Guarantee Strip -->
        <div style="display:flex; flex-wrap:wrap; gap:16px; margin: 0 0 20px; font-size:12px; color:var(--brown-light); font-weight:500;">
          <span>❄️ Chilled Insulated Packaging</span>
          <span>🌿 100% Pure Cocoa Butter</span>
          <span>🚀 Express Pan-India Dispatch</span>
        </div>
        <div id="cart-feedback" style="display:none; font-size:13px; color:#27ae60; font-weight:500; margin-bottom:16px;"></div>
        <?php else: ?>
        <p style="color:#c0392b; font-weight:600; font-size:15px; margin-bottom:24px;">Currently Out of Stock</p>
        <?php endif; ?>

        <!-- Long Description -->
        <?php if (!empty($product['long_description'])): ?>
        <div style="margin-top:20px; padding-top:20px; border-top:1px solid rgba(59,42,34,0.08);">
          <h3 style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:600; color:var(--brown); margin-bottom:12px;">About This Creation</h3>
          <div style="font-family:'Jost',sans-serif; font-size:14.5px; line-height:1.8; color:var(--brown-light); font-weight:300;">
            <?php echo nl2br(htmlspecialchars($product['long_description'])); ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Internal Links -->
        <div style="margin-top:24px; padding-top:20px; border-top:1px solid rgba(59,42,34,0.08);">
          <?php if ($relatedBlog): ?>
          <p style="font-size:13px; color:var(--brown-light); margin-bottom:6px;">
            📖 Related Article: <a href="<?php echo $pathPrefix; ?>blog/<?php echo htmlspecialchars($relatedBlog['slug']); ?>" style="color:var(--brown); font-weight:500;"><?php echo htmlspecialchars($relatedBlog['title']); ?></a>
          </p>
          <?php endif; ?>
          <p style="font-size:13px; color:var(--brown-light);">
            ❓ Have questions? See our <a href="<?php echo $pathPrefix; ?>faq.php" style="color:var(--brown); font-weight:500;">Shipping & Delivery FAQ</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($relatedProducts)): ?>
  <section style="background:var(--green-50); padding: 48px 0; margin-top: 48px;">
    <div class="section" style="text-align:center;">
      <div class="section-label">You May Also Like</div>
      <h2 class="section-title">Related Creations</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      <div class="grid-3" style="text-align:left;">
        <?php foreach ($relatedProducts as $rp): 
          $rpPrice = (!empty($rp['sale_price']) && $rp['sale_price'] > 0) ? $rp['sale_price'] : ($rp['price'] ?? 0);
          $rpImg = resolveProductImageSafe($rp['image_main'] ?? '', $rp['slug'] ?? '', $pathPrefix);
          $rpLink = $pathPrefix . 'product.php?slug=' . urlencode($rp['slug']);
        ?>
        <a href="<?php echo $rpLink; ?>" style="text-decoration:none; color:inherit; display:block;">
          <div class="why-card" style="cursor:pointer; height:100%; border-radius:16px; overflow:hidden; transition:transform 0.3s ease, box-shadow 0.3s ease; box-shadow:0 4px 16px rgba(0,0,0,0.06);">
            <div class="why-card-img-wrapper" style="height:210px; overflow:hidden; background:#FAF6F0;">
              <img src="<?php echo htmlspecialchars($rpImg); ?>" 
                   alt="<?php echo htmlspecialchars($rp['name']); ?> — RT Chocos" 
                   loading="lazy" 
                   style="width:100%; height:100%; object-fit:cover; transition:transform 0.4s ease;"
                   onerror="this.onerror=null;this.src='<?php echo $pathPrefix; ?>assets/premium_chocolate.png';">
            </div>
            <div class="why-card-text" style="padding:18px;">
              <h4 style="font-family:'Cormorant Garamond',serif; font-size:18px; font-weight:700; color:var(--brown); margin-bottom:6px;"><?php echo htmlspecialchars($rp['name']); ?></h4>
              <p style="font-family:'Jost',sans-serif; font-weight:700; font-size:16px; color:var(--brown);">₹<?php echo number_format($rpPrice, 0); ?></p>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php
  // Embed shop FAQs
  $faqCategory = 'shop';
  $faqLimit = 3;
  include __DIR__ . '/includes/faq-block.php';
  ?>

</div>
</main>

<!-- Interactive Image Gallery & Cart Scripts -->
<script>
// Thumbnail image switcher
function switchProductMainImage(thumbEl, newSrc) {
  var mainImg = document.getElementById('main-img');
  if (!mainImg) return;
  
  mainImg.style.opacity = '0.35';
  setTimeout(function() {
    mainImg.src = newSrc;
    mainImg.style.opacity = '1';
  }, 100);

  document.querySelectorAll('.product-thumb-item').forEach(function(el) {
    el.classList.remove('active');
  });
  if (thumbEl) thumbEl.classList.add('active');
}

// Interactive hover-zoom on main product image
document.addEventListener('DOMContentLoaded', function() {
  var wrap = document.getElementById('product-main-image');
  var img = document.getElementById('main-img');
  if (!wrap || !img) return;

  wrap.addEventListener('mousemove', function(e) {
    var rect = wrap.getBoundingClientRect();
    var x = ((e.clientX - rect.left) / rect.width) * 100;
    var y = ((e.clientY - rect.top) / rect.height) * 100;
    img.style.transformOrigin = x + '% ' + y + '%';
    img.style.transform = 'scale(1.75)';
  });

  wrap.addEventListener('mouseleave', function() {
    img.style.transformOrigin = 'center center';
    img.style.transform = 'scale(1)';
  });
});

function updateQty(delta) {
  var input = document.getElementById('qty-input');
  if (!input) return;
  var val = parseInt(input.value) + delta;
  var min = parseInt(input.min) || 1;
  var max = parseInt(input.max) || 9999;
  if (val < min) val = min;
  if (val > max) val = max;
  input.value = val;
}

function addToCart(productId) {
  var qtyInput = document.getElementById('qty-input');
  var qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
  var btn = event ? (event.currentTarget || event.target) : null;
  var originalText = btn ? btn.innerHTML : '';
  if (btn && btn.tagName === 'BUTTON') {
    btn.disabled = true;
    btn.innerHTML = '<span>Adding...</span>';
  }

  var apiPath = (typeof pathPrefix !== 'undefined' && pathPrefix) ? pathPrefix + 'api_cart.php' : 'api_cart.php';

  fetch(apiPath, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add', product_id: productId, quantity: qty})
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    var fb = document.getElementById('cart-feedback');
    if (data.success) {
      if (fb) {
        fb.style.color = '#27ae60';
        fb.innerHTML = '✓ ' + (data.message || 'Added to cart') + ' &nbsp; <a href="<?php echo $pathPrefix; ?>cart.php" style="color:var(--brown);font-weight:700;text-decoration:underline;">View Cart &rarr;</a>';
        fb.style.display = 'block';
      }
      if (btn && btn.tagName === 'BUTTON') {
        btn.innerHTML = '<span>✓ Added!</span>';
      }
      var badge = document.getElementById('header-cart-count');
      if (badge && typeof data.cart_count !== 'undefined') {
        badge.textContent = data.cart_count;
        badge.classList.remove('hidden');
        badge.style.transform = 'scale(1.35)';
        setTimeout(function() { badge.style.transform = ''; }, 300);
      }
      showProductToast(<?php echo json_encode($product['name']); ?>, data.cart_count);
      setTimeout(function() {
        if (btn && btn.tagName === 'BUTTON') {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      }, 2200);
    } else {
      if (fb) {
        fb.style.color = '#c0392b';
        fb.textContent = '✗ ' + (data.error || 'Could not add to cart');
        fb.style.display = 'block';
      }
      if (btn && btn.tagName === 'BUTTON') {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    }
  })
  .catch(function() {
    var fb = document.getElementById('cart-feedback');
    if (fb) {
      fb.style.color = '#c0392b';
      fb.textContent = 'Network error. Please try again.';
      fb.style.display = 'block';
    }
    if (btn && btn.tagName === 'BUTTON') {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  });
}

function showProductToast(productName, count) {
  var existing = document.getElementById('prodCartToast');
  if (existing) existing.remove();
  var toast = document.createElement('div');
  toast.id = 'prodCartToast';
  toast.style.cssText = 'position:fixed;bottom:28px;right:28px;background:#0C1E14;color:#FFFFFF;border:1px solid rgba(232,184,92,0.35);border-radius:12px;padding:14px 18px;box-shadow:0 16px 40px rgba(0,0,0,0.35);display:flex;align-items:center;gap:14px;z-index:99999;font-family:sans-serif;animation:fadeIn 0.3s ease;';
  toast.innerHTML = '<div style="width:32px;height:32px;border-radius:50%;background:rgba(116,226,145,0.15);display:flex;align-items:center;justify-content:center;color:#74E291;font-weight:bold;">✓</div>' +
    '<div style="min-width:130px;"><div style="font-size:10.5px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#E8B85C;">Added to Cart</div><div style="font-size:13px;font-weight:600;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (productName || '') + '</div></div>' +
    '<div style="display:flex;gap:8px;"><a href="<?php echo $pathPrefix; ?>cart.php" style="background:transparent;color:#E8EDE9;border:1px solid rgba(232,184,92,0.35);padding:6px 11px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;">View Cart (' + count + ')</a>' +
    '<a href="<?php echo $pathPrefix; ?>checkout.php" style="background:linear-gradient(135deg,#E8B85C 0%,#D19E3E 100%);color:#0B1C12;padding:6px 12px;border-radius:4px;font-size:11px;font-weight:700;text-decoration:none;">Checkout &rarr;</a></div>' +
    '<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#8C9990;font-size:18px;cursor:pointer;padding:2px 4px;margin-left:2px;">&times;</button>';
  document.body.appendChild(toast);
  setTimeout(function() { if (toast && toast.parentElement) toast.remove(); }, 5000);
}
</script>

<?php
// Product JSON-LD Schema
$productImages = array_map(function($img) {
    if (strpos($img, 'http') === 0) return $img;
    return 'https://www.rtchocos.com/' . ltrim($img, '/.');
}, $allImages);

$productSchemaData = [
    "@context" => "https://schema.org",
    "@type" => "Product",
    "name" => $product['name'],
    "image" => $productImages,
    "description" => $product['short_description'] ?: ($product['long_description'] ?? $product['name']),
    "sku" => "RTCHOCOS-" . ($product['id'] ?? 'PROD'),
    "brand" => [
        "@type" => "Brand",
        "name" => "RT Chocos"
    ],
    "offers" => [
        "@type" => "Offer",
        "url" => $canonicalUrl,
        "priceCurrency" => "INR",
        "price" => number_format($displayPrice, 2, '.', ''),
        "availability" => (!isset($product['stock_quantity']) || $product['stock_quantity'] != 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
        "seller" => [
            "@type" => "Organization",
            "name" => "RT Chocos"
        ]
    ]
];

echo '<script type="application/ld+json">' . "\n";
echo json_encode($productSchemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo "\n" . '</script>' . "\n";

// FAQPage schema for embedded FAQs
if (!empty($GLOBALS['faqSchemaItems'])) {
    $faqEntities = [];
    foreach ($GLOBALS['faqSchemaItems'] as $item) {
        $faqEntities[] = [
            "@type" => "Question",
            "name" => $item['question'],
            "acceptedAnswer" => [
                "@type" => "Answer",
                "text" => $item['answer']
            ]
        ];
    }
    echo '<script type="application/ld+json">' . "\n";
    echo json_encode(["@context" => "https://schema.org", "@type" => "FAQPage", "mainEntity" => $faqEntities], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

include __DIR__ . '/includes/footer.php';
?>
