<?php
  session_start();
  $pageTitle = "Chocolate Shop India — Buy Bean-to-Bar Chocolate, Cacao Nibs & Starter Kits Online | RT Chocos";
  $pageDescription = "Shop artisan bean-to-bar chocolate, single-origin cacao nibs, chocolate making starter kits, and professional tools from RT Chocos — India's first chocolate academy. Free shipping on orders above ₹999.";
  $pageKeywords = "buy chocolate online India, bean to bar chocolate buy, artisan chocolate India, craft chocolate shop, cacao nibs India, chocolate making kit India, buy dark chocolate online, RT Chocos shop, chocolate tools India, single origin chocolate India";
  $pathPrefix = "";

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/shop.php";

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Shop', 'item' => $canonicalUrl]
  ];

  include $pathPrefix . 'includes/header.php';

  require_once __DIR__ . '/includes/db.php';

  // Filters
  $categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
  $searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';
  $page = max(1, (int)($_GET['page'] ?? 1));
  $perPage = 12;
  $offset = ($page - 1) * $perPage;

  try {
      $pdo = get_db();

      // Get categories for filter bar
      $catStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = 1 ORDER BY category ASC");
      $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

      // Build product query
      $where = ["is_active = 1"];
      $params = [];

      if ($categoryFilter) {
          $where[] = "category = ?";
          $params[] = $categoryFilter;
      }
      if ($searchFilter) {
          $where[] = "(name LIKE ? OR short_description LIKE ? OR category LIKE ?)";
          $searchLike = '%' . $searchFilter . '%';
          $params[] = $searchLike;
          $params[] = $searchLike;
          $params[] = $searchLike;
      }

      $whereClause = implode(' AND ', $where);

      // Total count
      $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $whereClause");
      $countStmt->execute($params);
      $totalProducts = $countStmt->fetchColumn();
      $totalPages = max(1, ceil($totalProducts / $perPage));

      // Fetch products
      $productStmt = $pdo->prepare("SELECT id, slug, name, short_description, price, sale_price, category, image_main, is_featured, stock_quantity FROM products WHERE $whereClause ORDER BY is_featured DESC, created_at DESC LIMIT $perPage OFFSET $offset");
      $productStmt->execute($params);
      $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

  } catch (Exception $e) {
      $categories = [];
      $products = [];
      $totalProducts = 0;
      $totalPages = 1;
  }
?>

<main>
<div id="page-shop" class="page active" style="padding-top:80px;">
  <div class="page-hero contact-page-hero">
    <div class="page-hero-content">
      <h1 class="fade-up">Chocolate Shop</h1>
      <p class="fade-up-d1">Artisan bean-to-bar chocolate, cacao nibs, starter kits, and professional tools — crafted and curated by India's first chocolate academy.</p>
    </div>
  </div>

  <section style="background:var(--green-50);">
    <div class="section" style="text-align:center;">
      <div class="section-label">Browse Our Collection</div>
      <h2 class="section-title">Products</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>

      <!-- Search & Category Filters -->
      <div style="max-width:700px; margin:0 auto 32px; display:flex; flex-wrap:wrap; gap:12px; justify-content:center; align-items:center;">
        <form method="GET" action="shop.php" style="display:flex; gap:8px; flex:1; min-width:240px;">
          <?php if ($categoryFilter): ?>
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>">
          <?php endif; ?>
          <input class="form-input" type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchFilter); ?>" style="flex:1; padding:10px 16px; font-size:14px;">
          <button class="btn-primary" type="submit" style="padding:10px 20px; white-space:nowrap;">Search</button>
        </form>
      </div>

      <?php if (!empty($categories)): ?>
      <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-bottom:36px;">
        <a href="shop.php<?php echo $searchFilter ? '?search=' . urlencode($searchFilter) : ''; ?>" 
           class="btn-outline" 
           style="text-decoration:none; padding:8px 18px; font-size:13px; <?php echo !$categoryFilter ? 'background:var(--brown); color:white;' : ''; ?>">
          All
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="shop.php?category=<?php echo urlencode($cat); ?><?php echo $searchFilter ? '&search=' . urlencode($searchFilter) : ''; ?>" 
           class="btn-outline" 
           style="text-decoration:none; padding:8px 18px; font-size:13px; <?php echo $categoryFilter === $cat ? 'background:var(--brown); color:white;' : ''; ?>">
          <?php echo htmlspecialchars($cat); ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Product Grid -->
      <?php if (!empty($products)): ?>
      <div class="grid-3">
        <?php foreach ($products as $product): 
          $displayPrice = ($product['sale_price'] && $product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];
          $productUrl = 'shop/' . htmlspecialchars($product['slug']);
          $imgAlt = htmlspecialchars($product['name']) . ' — bean-to-bar chocolate, RT Chocos India';
        ?>
        <a href="<?php echo $productUrl; ?>" style="text-decoration:none; display:block;">
          <div class="why-card" style="cursor:pointer; height:100%;">
            <div class="why-card-img-wrapper">
              <?php if ($product['image_main']): ?>
              <img src="<?php echo htmlspecialchars($product['image_main']); ?>" alt="<?php echo $imgAlt; ?>" loading="lazy" style="width:100%; height:220px; object-fit:cover;">
              <?php else: ?>
              <div style="width:100%; height:220px; background:var(--green-50); display:flex; align-items:center; justify-content:center; color:var(--brown-light); font-size:14px;">No Image</div>
              <?php endif; ?>
            </div>
            <div class="why-card-text">
              <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--brown-light); font-weight:600; margin-bottom:6px;">
                <?php echo htmlspecialchars($product['category']); ?>
              </div>
              <h4 style="margin-bottom:8px;"><?php echo htmlspecialchars($product['name']); ?></h4>
              <p style="font-size:13px; margin-bottom:12px;"><?php echo htmlspecialchars($product['short_description'] ?? ''); ?></p>
              <div style="font-family:'Jost',sans-serif; font-weight:700; font-size:18px; color:var(--brown);">
                ₹<?php echo number_format($displayPrice, 0); ?>
                <?php if ($product['sale_price'] && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']): ?>
                <span style="font-size:13px; font-weight:400; color:var(--brown-light); text-decoration:line-through; margin-left:6px;">₹<?php echo number_format($product['price'], 0); ?></span>
                <?php endif; ?>
              </div>
              <?php if ($product['stock_quantity'] == 0): ?>
              <span style="font-size:12px; color:#c0392b; font-weight:600; margin-top:6px; display:block;">Out of Stock</span>
              <?php endif; ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div style="display:flex; justify-content:center; gap:8px; margin-top:40px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): 
          $paginationUrl = 'shop.php?page=' . $i;
          if ($categoryFilter) $paginationUrl .= '&category=' . urlencode($categoryFilter);
          if ($searchFilter) $paginationUrl .= '&search=' . urlencode($searchFilter);
        ?>
        <a href="<?php echo $paginationUrl; ?>" 
           class="btn-outline" 
           style="text-decoration:none; padding:8px 14px; font-size:13px; min-width:36px; text-align:center; <?php echo $i === $page ? 'background:var(--brown); color:white;' : ''; ?>">
          <?php echo $i; ?>
        </a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <p style="color:var(--brown-light); font-size:16px; padding:40px 0;">
        <?php echo $searchFilter || $categoryFilter ? 'No products found matching your criteria.' : 'Products are coming soon! Subscribe to be notified when our shop launches.'; ?>
      </p>
      <?php endif; ?>
    </div>
  </section>

  <?php
  // Embed shop FAQs
  $faqCategory = 'shop';
  $faqLimit = 4;
  include __DIR__ . '/includes/faq-block.php';
  ?>

</div>
</main>

<?php
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

// ItemList schema for products
if (!empty($products)) {
    $listItems = [];
    foreach ($products as $idx => $p) {
        $listItems[] = [
            "@type" => "ListItem",
            "position" => $idx + 1,
            "url" => "https://www.rtchocos.com/shop/" . $p['slug']
        ];
    }
    echo '<script type="application/ld+json">' . "\n";
    echo json_encode(["@context" => "https://schema.org", "@type" => "ItemList", "itemListElement" => $listItems], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}

include $pathPrefix . 'includes/footer.php';
?>
