<?php
  session_start();
  require_once __DIR__ . '/includes/db.php';

  // Get product slug from URL
  $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
  if (empty($slug)) {
      header('Location: shop.php');
      exit;
  }

  try {
      $pdo = get_db();
      $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1");
      $stmt->execute([$slug]);
      $product = $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
      $product = null;
  }

  if (!$product) {
      header('HTTP/1.0 404 Not Found');
      include __DIR__ . '/error.php';
      exit;
  }

  // Auto-generate meta if blank
  $autoTitle = $product['name'] . " | Buy Bean-to-Bar Chocolate Online | RT Chocos India";
  $autoDesc = substr("Buy " . $product['name'] . " — premium " . strtolower($product['category']) . " from RT Chocos, India's first bean-to-bar chocolate academy. Artisan quality, ethically sourced Indian cacao.", 0, 155);

  $pageTitle = !empty($product['meta_title']) ? $product['meta_title'] : $autoTitle;
  $pageDescription = !empty($product['meta_description']) ? $product['meta_description'] : $autoDesc;
  $pageKeywords = !empty($product['meta_keywords']) ? $product['meta_keywords'] : "buy " . strtolower($product['name']) . ", " . strtolower($product['category']) . " India, bean to bar chocolate online, RT Chocos shop, craft chocolate India";
  $pathPrefix = "";
  $pageImage = $product['image_main'] ?: '';

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/shop/" . $product['slug'];

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Shop', 'item' => 'https://www.rtchocos.com/shop.php'],
      ['name' => $product['category'], 'item' => 'https://www.rtchocos.com/shop.php?category=' . urlencode($product['category'])],
      ['name' => $product['name'], 'item' => $canonicalUrl]
  ];

  include $pathPrefix . 'includes/header.php';

  // Parse image gallery
  $gallery = [];
  if (!empty($product['image_gallery'])) {
      $gallery = json_decode($product['image_gallery'], true) ?: [];
  }
  $allImages = [];
  if ($product['image_main']) $allImages[] = $product['image_main'];
  $allImages = array_merge($allImages, $gallery);

  // Display price
  $displayPrice = ($product['sale_price'] && $product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];
  $hasDiscount = ($product['sale_price'] && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']);

  // Related products (same category, exclude current)
  try {
      $relStmt = $pdo->prepare("SELECT id, slug, name, short_description, price, sale_price, category, image_main FROM products WHERE category = ? AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 3");
      $relStmt->execute([$product['category'], $product['id']]);
      $relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
      $relatedProducts = [];
  }

  // Find a related blog article (match category keyword in title/excerpt)
  $relatedBlog = null;
  try {
      $categoryKeyword = '%' . strtolower(explode(' ', $product['category'])[0]) . '%';
      $blogStmt = $pdo->prepare("SELECT slug, title FROM blogs WHERE is_published = 1 AND (LOWER(title) LIKE ? OR LOWER(excerpt) LIKE ?) ORDER BY RAND() LIMIT 1");
      $blogStmt->execute([$categoryKeyword, $categoryKeyword]);
      $relatedBlog = $blogStmt->fetch(PDO::FETCH_ASSOC);
  } catch (Exception $e) {}
?>

<main>
<div id="page-product" class="page active" style="padding-top:100px;">
  <div class="section">
    <!-- Breadcrumb text -->
    <div style="font-family:'Jost',sans-serif; font-size:13px; color:var(--brown-light); margin-bottom:24px; font-weight:300;">
      <a href="index.php" style="color:var(--brown-light); text-decoration:none;">Home</a> › 
      <a href="shop.php" style="color:var(--brown-light); text-decoration:none;">Shop</a> › 
      <a href="shop.php?category=<?php echo urlencode($product['category']); ?>" style="color:var(--brown-light); text-decoration:none;"><?php echo htmlspecialchars($product['category']); ?></a> › 
      <span style="color:var(--brown);"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="contact-grid">
      <!-- Image Gallery Column -->
      <div>
        <?php if (!empty($allImages)): ?>
        <div id="product-main-image" style="border-radius:20px; overflow:hidden; margin-bottom:16px; box-shadow:0 8px 32px rgba(59,42,34,0.10);">
          <img id="main-img" src="<?php echo htmlspecialchars($allImages[0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?> — bean-to-bar chocolate, RT Chocos India" style="width:100%; height:auto; display:block; object-fit:cover;" loading="lazy">
        </div>
        <?php if (count($allImages) > 1): ?>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php foreach ($allImages as $idx => $img): ?>
          <img src="<?php echo htmlspecialchars($img); ?>" 
               alt="<?php echo htmlspecialchars($product['name']); ?> view <?php echo $idx + 1; ?>" 
               loading="lazy"
               onclick="document.getElementById('main-img').src=this.src"
               style="width:72px; height:72px; object-fit:cover; border-radius:10px; cursor:pointer; border:2px solid <?php echo $idx === 0 ? 'var(--brown)' : 'transparent'; ?>; opacity:<?php echo $idx === 0 ? '1' : '0.7'; ?>; transition:all 0.2s ease;"
               onmouseover="this.style.opacity='1'; this.style.borderColor='var(--brown)'"
               onmouseout="this.style.opacity='<?php echo $idx === 0 ? '1' : '0.7'; ?>'; this.style.borderColor='<?php echo $idx === 0 ? 'var(--brown)' : 'transparent'; ?>'">
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Product Details Column -->
      <div>
        <div class="section-label" style="margin-bottom:8px;"><?php echo htmlspecialchars($product['category']); ?></div>
        <h1 style="font-family:'Cormorant Garamond',serif; font-size:32px; font-weight:700; color:var(--brown); margin-bottom:12px; line-height:1.2;">
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

        <?php if ($product['short_description']): ?>
        <p style="font-family:'Jost',sans-serif; font-size:15px; line-height:1.7; color:var(--brown-light); font-weight:300; margin-bottom:20px;">
          <?php echo htmlspecialchars($product['short_description']); ?>
        </p>
        <?php endif; ?>

        <!-- Add to Cart -->
        <?php if ($product['stock_quantity'] != 0): ?>
        <div style="display:flex; gap:12px; align-items:center; margin-bottom:24px; flex-wrap:wrap;">
          <div style="display:flex; align-items:center; gap:0; border:1px solid rgba(59,42,34,0.15); border-radius:8px; overflow:hidden;">
            <button onclick="updateQty(-1)" style="width:38px; height:38px; border:none; background:var(--cream); cursor:pointer; font-size:18px; color:var(--brown);">−</button>
            <input id="qty-input" type="number" value="1" min="1" max="<?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 99; ?>" style="width:48px; height:38px; border:none; text-align:center; font-family:'Jost',sans-serif; font-size:15px; color:var(--brown); outline:none;">
            <button onclick="updateQty(1)" style="width:38px; height:38px; border:none; background:var(--cream); cursor:pointer; font-size:18px; color:var(--brown);">+</button>
          </div>
          <button class="btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)" style="padding:10px 28px;">
            Add to Cart
          </button>
          <a href="cart.php" class="btn-outline" style="text-decoration:none; padding:10px 20px;">View Cart</a>
        </div>
        <div id="cart-feedback" style="display:none; font-size:13px; color:#27ae60; font-weight:500; margin-bottom:16px;"></div>
        <?php else: ?>
        <p style="color:#c0392b; font-weight:600; font-size:15px; margin-bottom:24px;">Currently Out of Stock</p>
        <?php endif; ?>

        <!-- Long Description -->
        <?php if ($product['long_description']): ?>
        <div style="margin-top:20px; padding-top:20px; border-top:1px solid rgba(59,42,34,0.08);">
          <h3 style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:600; color:var(--brown); margin-bottom:12px;">About This Product</h3>
          <div style="font-family:'Jost',sans-serif; font-size:14.5px; line-height:1.8; color:var(--brown-light); font-weight:300;">
            <?php echo nl2br(htmlspecialchars($product['long_description'])); ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Internal Links -->
        <div style="margin-top:24px; padding-top:20px; border-top:1px solid rgba(59,42,34,0.08);">
          <?php if ($relatedBlog): ?>
          <p style="font-size:13px; color:var(--brown-light); margin-bottom:6px;">
            📖 Related Article: <a href="blog/<?php echo htmlspecialchars($relatedBlog['slug']); ?>" style="color:var(--brown); font-weight:500;"><?php echo htmlspecialchars($relatedBlog['title']); ?></a>
          </p>
          <?php endif; ?>
          <p style="font-size:13px; color:var(--brown-light);">
            ❓ Have questions? See our <a href="faq.php" style="color:var(--brown); font-weight:500;">Shipping & Delivery FAQ</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($relatedProducts)): ?>
  <section style="background:var(--green-50);">
    <div class="section" style="text-align:center;">
      <div class="section-label">You May Also Like</div>
      <h2 class="section-title">Related Products</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      <div class="grid-3">
        <?php foreach ($relatedProducts as $rp): 
          $rpPrice = ($rp['sale_price'] && $rp['sale_price'] > 0) ? $rp['sale_price'] : $rp['price'];
        ?>
        <a href="shop/<?php echo htmlspecialchars($rp['slug']); ?>" style="text-decoration:none;">
          <div class="why-card" style="cursor:pointer; height:100%;">
            <div class="why-card-img-wrapper">
              <?php if ($rp['image_main']): ?>
              <img src="<?php echo htmlspecialchars($rp['image_main']); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?> — bean-to-bar chocolate, RT Chocos India" loading="lazy" style="width:100%; height:200px; object-fit:cover;">
              <?php endif; ?>
            </div>
            <div class="why-card-text">
              <h4><?php echo htmlspecialchars($rp['name']); ?></h4>
              <p style="font-weight:700; font-size:17px; color:var(--brown); margin-top:8px;">₹<?php echo number_format($rpPrice, 0); ?></p>
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

<!-- Cart JS -->
<script>
function updateQty(delta) {
  var input = document.getElementById('qty-input');
  var val = parseInt(input.value) + delta;
  var max = parseInt(input.max) || 99;
  if (val < 1) val = 1;
  if (val > max) val = max;
  input.value = val;
}

function addToCart(productId) {
  var qty = parseInt(document.getElementById('qty-input').value) || 1;
  fetch('api_cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add', product_id: productId, quantity: qty})
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    var fb = document.getElementById('cart-feedback');
    if (data.success) {
      fb.style.color = '#27ae60';
      fb.textContent = '✓ ' + (data.message || 'Added to cart');
    } else {
      fb.style.color = '#c0392b';
      fb.textContent = '✗ ' + (data.error || 'Could not add to cart');
    }
    fb.style.display = 'block';
    setTimeout(function() { fb.style.display = 'none'; }, 3000);
  })
  .catch(function() {
    var fb = document.getElementById('cart-feedback');
    fb.style.color = '#c0392b';
    fb.textContent = 'Network error. Please try again.';
    fb.style.display = 'block';
  });
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
    "description" => $product['short_description'] ?: $product['long_description'] ?: $product['name'],
    "sku" => "RTCHOCOS-" . $product['id'],
    "brand" => [
        "@type" => "Brand",
        "name" => "RT Chocos"
    ],
    "offers" => [
        "@type" => "Offer",
        "url" => $canonicalUrl,
        "priceCurrency" => "INR",
        "price" => number_format($displayPrice, 2, '.', ''),
        "availability" => $product['stock_quantity'] != 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
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

include $pathPrefix . 'includes/footer.php';
?>
