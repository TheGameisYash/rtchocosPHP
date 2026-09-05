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
  $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
  $pathPrefix = (strpos($requestPath, '/shop/') !== false) ? '../' : '';
  $pageImage = $product['image_main'] ?: '';

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/shop/" . $product['slug'];

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Shop', 'item' => 'https://www.rtchocos.com/shop.php'],
      ['name' => $product['category'], 'item' => 'https://www.rtchocos.com/shop.php?category=' . urlencode($product['category'])],
      ['name' => $product['name'], 'item' => $canonicalUrl]
  ];

  include __DIR__ . '/includes/header.php';

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
      <a href="<?php echo $pathPrefix; ?>index.php" style="color:var(--brown-light); text-decoration:none;">Home</a> › 
      <a href="<?php echo $pathPrefix; ?>shop.php" style="color:var(--brown-light); text-decoration:none;">Shop</a> › 
      <a href="<?php echo $pathPrefix; ?>shop.php?category=<?php echo urlencode($product['category']); ?>" style="color:var(--brown-light); text-decoration:none;"><?php echo htmlspecialchars($product['category']); ?></a> › 
      <span style="color:var(--brown);"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="contact-grid">
      <!-- Image Gallery Column -->
      <div>
        <?php if (!empty($allImages)): 
          $mainImgSrc = $allImages[0];
          if ($mainImgSrc && strpos($mainImgSrc, 'http') !== 0 && strpos($mainImgSrc, '/') !== 0 && strpos($mainImgSrc, '../') !== 0) {
            $mainImgSrc = $pathPrefix . $mainImgSrc;
          }
        ?>
        <div id="product-main-image" style="border-radius:20px; overflow:hidden; margin-bottom:16px; box-shadow:0 8px 32px rgba(59,42,34,0.10);">
          <img id="main-img" src="<?php echo htmlspecialchars($mainImgSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?> — bean-to-bar chocolate, RT Chocos India" style="width:100%; height:auto; display:block; object-fit:cover;" loading="lazy">
        </div>
        <?php if (count($allImages) > 1): ?>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <?php foreach ($allImages as $idx => $img): 
            $thumbSrc = $img;
            if ($thumbSrc && strpos($thumbSrc, 'http') !== 0 && strpos($thumbSrc, '/') !== 0 && strpos($thumbSrc, '../') !== 0) {
              $thumbSrc = $pathPrefix . $thumbSrc;
            }
          ?>
          <img src="<?php echo htmlspecialchars($thumbSrc); ?>" 
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

        <?php if ($product['stock_quantity'] != 0): ?>
        <div style="display:flex; gap:12px; align-items:center; margin-bottom:18px; flex-wrap:wrap;">
          <div style="display:flex; align-items:center; gap:0; border:1px solid rgba(59,42,34,0.15); border-radius:8px; overflow:hidden;">
            <button onclick="updateQty(-1)" style="width:38px; height:38px; border:none; background:var(--cream); cursor:pointer; font-size:18px; color:var(--brown);">−</button>
            <input id="qty-input" type="number" value="<?php echo $defaultQty; ?>" min="<?php echo ($prodStoreMode === 'bulk') ? $numericMOQ : 1; ?>" max="<?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 9999; ?>" style="width:56px; height:38px; border:none; text-align:center; font-family:'Jost',sans-serif; font-size:15px; color:var(--brown); outline:none;">
            <button onclick="updateQty(1)" style="width:38px; height:38px; border:none; background:var(--cream); cursor:pointer; font-size:18px; color:var(--brown);">+</button>
          </div>

          <?php if ($prodStoreMode === 'bulk'): ?>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $prodBulkPhone); ?>?text=Hello%20RT%20Chocos!%20I%20have%20a%20bulk%20enquiry%20for%20<?php echo urlencode($product['name']); ?>%20(Requested%20Qty:%20<?php echo $defaultQty; ?>%20Units)." target="_blank" class="btn-primary" style="text-decoration:none; padding:11px 22px; display:inline-flex; align-items:center; gap:6px; background:#25D366; border-color:#25D366; color:#FFFFFF; font-weight:700;">
              💬 Instant WhatsApp Quote &rarr;
            </a>
            <button class="btn-outline" onclick="addToCart(<?php echo $product['id']; ?>)" style="padding:11px 20px;">
              Add Wholesale Qty to Cart
            </button>
            <a href="<?php echo $pathPrefix; ?>shop.php#bulk-enquiry" class="btn-outline" style="text-decoration:none; padding:11px 18px;">
              Request Custom RFP Form &rarr;
            </a>
          <?php else: ?>
            <button class="btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)" style="padding:10px 28px;">
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
  <section style="background:var(--green-50);">
    <div class="section" style="text-align:center;">
      <div class="section-label">You May Also Like</div>
      <h2 class="section-title">Related Products</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      <div class="grid-3">
        <?php foreach ($relatedProducts as $rp): 
          $rpPrice = ($rp['sale_price'] && $rp['sale_price'] > 0) ? $rp['sale_price'] : $rp['price'];
          $rpImg = $rp['image_main'];
          if ($rpImg && strpos($rpImg, 'http') !== 0 && strpos($rpImg, '/') !== 0 && strpos($rpImg, '../') !== 0) {
            $rpImg = $pathPrefix . $rpImg;
          }
        ?>
        <a href="<?php echo $pathPrefix; ?>shop/<?php echo htmlspecialchars($rp['slug']); ?>" style="text-decoration:none;">
          <div class="why-card" style="cursor:pointer; height:100%;">
            <div class="why-card-img-wrapper">
              <?php if ($rp['image_main']): ?>
              <img src="<?php echo htmlspecialchars($rpImg); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?> — bean-to-bar chocolate, RT Chocos India" loading="lazy" style="width:100%; height:200px; object-fit:cover;">
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
  var qtyInput = document.getElementById('qty-input');
  var qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
  var btn = event ? (event.currentTarget || event.target) : null;
  var originalText = btn ? btn.innerHTML : '';
  if (btn && btn.tagName === 'BUTTON') {
    btn.disabled = true;
    btn.innerHTML = '<span>Adding...</span>';
  }

  fetch('/api_cart.php', {
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
        fb.innerHTML = '✓ ' + (data.message || 'Added to cart') + ' &nbsp; <a href="/cart.php" style="color:var(--brown);font-weight:700;text-decoration:underline;">View Cart &rarr;</a>';
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
    '<div style="display:flex;gap:8px;"><a href="/cart.php" style="background:transparent;color:#E8EDE9;border:1px solid rgba(232,184,92,0.35);padding:6px 11px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;">View Cart (' + count + ')</a>' +
    '<a href="/checkout.php" style="background:linear-gradient(135deg,#E8B85C 0%,#D19E3E 100%);color:#0B1C12;padding:6px 12px;border-radius:4px;font-size:11px;font-weight:700;text-decoration:none;">Checkout &rarr;</a></div>' +
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

include __DIR__ . '/includes/footer.php';
?>
