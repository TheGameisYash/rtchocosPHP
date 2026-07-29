<?php
  session_start();
  $pageTitle = "Shopping Cart — RT Chocos India | Bean-to-Bar Chocolate Shop";
  $pageDescription = "Review your shopping cart at RT Chocos. Artisan bean-to-bar chocolate, cacao nibs, and professional chocolate tools. Free shipping on orders above ₹999.";
  $pageKeywords = "RT Chocos cart, chocolate shop cart, buy chocolate India, bean to bar chocolate order";
  $pathPrefix = "";

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/cart.php";

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Shop', 'item' => 'https://www.rtchocos.com/shop.php'],
      ['name' => 'Cart', 'item' => $canonicalUrl]
  ];

  include $pathPrefix . 'includes/header.php';
  require_once __DIR__ . '/includes/db.php';

  $cartItems = [];
  $subtotal = 0;

  if (!empty($_SESSION['cart'])) {
      try {
          $pdo = get_db();
          $ids = array_keys($_SESSION['cart']);
          $placeholders = implode(',', array_fill(0, count($ids), '?'));
          $stmt = $pdo->prepare("SELECT id, slug, name, price, sale_price, image_main, stock_quantity FROM products WHERE id IN ($placeholders) AND is_active = 1");
          $stmt->execute($ids);
          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($products as $p) {
              $qty = $_SESSION['cart'][$p['id']];
              $unitPrice = ($p['sale_price'] && $p['sale_price'] > 0) ? $p['sale_price'] : $p['price'];
              $lineTotal = $unitPrice * $qty;
              $subtotal += $lineTotal;
              $cartItems[] = array_merge($p, [
                  'quantity' => $qty,
                  'unit_price' => $unitPrice,
                  'line_total' => $lineTotal
              ]);
          }
      } catch (Exception $e) {}
  }

  $shipping = $subtotal >= 999 ? 0 : ($subtotal > 0 ? 99 : 0);
  $total = $subtotal + $shipping;
?>

<main>
<div id="page-cart" class="page active" style="padding-top:100px;">
  <div class="section">
    <div style="text-align:center; margin-bottom:32px;">
      <div class="section-label">Your Selection</div>
      <h1 class="section-title">Shopping Cart</h1>
      <div class="divider" style="margin:16px auto;"></div>
    </div>

    <?php if (!empty($cartItems)): ?>
    <div style="max-width:900px; margin:0 auto;">
      <!-- Cart Items -->
      <?php foreach ($cartItems as $item): ?>
      <div id="cart-item-<?php echo $item['id']; ?>" style="display:flex; gap:20px; padding:20px; margin-bottom:12px; background:var(--cream); border-radius:16px; box-shadow:0 2px 8px rgba(59,42,34,0.06); align-items:center; flex-wrap:wrap;">
        <!-- Image -->
        <?php if ($item['image_main']): ?>
        <a href="shop/<?php echo htmlspecialchars($item['slug']); ?>">
          <img src="<?php echo htmlspecialchars($item['image_main']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy" style="width:80px; height:80px; object-fit:cover; border-radius:12px;">
        </a>
        <?php endif; ?>
        
        <!-- Details -->
        <div style="flex:1; min-width:160px;">
          <a href="shop/<?php echo htmlspecialchars($item['slug']); ?>" style="text-decoration:none;">
            <h4 style="font-family:'Jost',sans-serif; font-size:15px; font-weight:600; color:var(--brown); margin:0 0 4px;"><?php echo htmlspecialchars($item['name']); ?></h4>
          </a>
          <p style="font-size:13px; color:var(--brown-light); margin:0;">₹<?php echo number_format($item['unit_price'], 0); ?> each</p>
        </div>

        <!-- Quantity -->
        <div style="display:flex; align-items:center; gap:0; border:1px solid rgba(59,42,34,0.15); border-radius:8px; overflow:hidden;">
          <button onclick="updateCartQty(<?php echo $item['id']; ?>, -1)" style="width:32px; height:32px; border:none; background:white; cursor:pointer; font-size:16px; color:var(--brown);">−</button>
          <span id="qty-<?php echo $item['id']; ?>" style="width:36px; text-align:center; font-family:'Jost',sans-serif; font-size:14px; color:var(--brown);"><?php echo $item['quantity']; ?></span>
          <button onclick="updateCartQty(<?php echo $item['id']; ?>, 1)" style="width:32px; height:32px; border:none; background:white; cursor:pointer; font-size:16px; color:var(--brown);">+</button>
        </div>

        <!-- Line Total -->
        <div style="min-width:80px; text-align:right;">
          <span id="total-<?php echo $item['id']; ?>" style="font-family:'Jost',sans-serif; font-weight:700; font-size:16px; color:var(--brown);">₹<?php echo number_format($item['line_total'], 0); ?></span>
        </div>

        <!-- Remove -->
        <button onclick="removeFromCart(<?php echo $item['id']; ?>)" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--brown-light); padding:4px;" title="Remove">✕</button>
      </div>
      <?php endforeach; ?>

      <!-- Summary -->
      <div style="background:white; border-radius:16px; padding:24px; margin-top:24px; box-shadow:0 4px 16px rgba(59,42,34,0.08);">
        <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-family:'Jost',sans-serif; font-size:15px; color:var(--brown-light);">
          <span>Subtotal</span>
          <span id="cart-subtotal">₹<?php echo number_format($subtotal, 0); ?></span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-family:'Jost',sans-serif; font-size:15px; color:var(--brown-light);">
          <span>Shipping</span>
          <span id="cart-shipping"><?php echo $shipping == 0 ? 'FREE' : '₹' . $shipping; ?></span>
        </div>
        <?php if ($subtotal < 999 && $subtotal > 0): ?>
        <p style="font-size:12px; color:#27ae60; margin-bottom:12px;">Add ₹<?php echo number_format(999 - $subtotal, 0); ?> more for free shipping!</p>
        <?php endif; ?>
        <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:1px solid rgba(59,42,34,0.08); font-family:'Jost',sans-serif; font-size:20px; font-weight:700; color:var(--brown);">
          <span>Total</span>
          <span id="cart-total">₹<?php echo number_format($total, 0); ?></span>
        </div>
        <div style="display:flex; gap:12px; margin-top:24px; flex-wrap:wrap;">
          <a href="checkout.php" class="btn-primary" style="text-decoration:none; flex:1; text-align:center; padding:14px;">Proceed to Checkout</a>
          <a href="shop.php" class="btn-outline" style="text-decoration:none; padding:14px 24px;">Continue Shopping</a>
        </div>
      </div>
    </div>

    <?php else: ?>
    <div style="text-align:center; padding:60px 20px;">
      <p style="font-family:'Cormorant Garamond',serif; font-size:24px; color:var(--brown); margin-bottom:16px;">Your cart is empty</p>
      <p style="color:var(--brown-light); margin-bottom:24px;">Discover our artisan chocolate collection and add something delightful.</p>
      <a href="shop.php" class="btn-primary" style="text-decoration:none; padding:14px 32px;">Browse Shop</a>
    </div>
    <?php endif; ?>

  </div>
</div>
</main>

<script>
function updateCartQty(productId, delta) {
  var qtyEl = document.getElementById('qty-' + productId);
  var newQty = parseInt(qtyEl.textContent) + delta;
  if (newQty < 1) { removeFromCart(productId); return; }
  
  fetch('api_cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'update', product_id: productId, quantity: newQty})
  })
  .then(function(r) { return r.json(); })
  .then(function() { location.reload(); });
}

function removeFromCart(productId) {
  fetch('api_cart.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'remove', product_id: productId})
  })
  .then(function(r) { return r.json(); })
  .then(function() { location.reload(); });
}
</script>

<?php include $pathPrefix . 'includes/footer.php'; ?>
