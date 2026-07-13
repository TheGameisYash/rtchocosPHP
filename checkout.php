<?php
  session_start();
  $pageTitle = "Checkout — RT Chocos India | Secure Order";
  $pageDescription = "Complete your order at RT Chocos. Secure checkout for artisan bean-to-bar chocolate and professional tools.";
  $pageKeywords = "RT Chocos checkout, buy chocolate India, order chocolate online, bean to bar checkout";
  $pathPrefix = "";

  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/checkout.php";

  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Shop', 'item' => 'https://www.rtchocos.com/shop.php'],
      ['name' => 'Cart', 'item' => 'https://www.rtchocos.com/cart.php'],
      ['name' => 'Checkout', 'item' => $canonicalUrl]
  ];

  require_once __DIR__ . '/includes/db.php';
  require_once __DIR__ . '/includes/payment.php';

  $error = '';
  $success = false;
  $orderNumber = '';

  // Process order submission
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim($_POST['name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $phone = trim($_POST['phone'] ?? '');
      $address = trim($_POST['address'] ?? '');
      $city = trim($_POST['city'] ?? '');
      $state = trim($_POST['state'] ?? '');
      $pincode = trim($_POST['pincode'] ?? '');

      if (empty($name) || empty($email) || empty($address) || empty($city) || empty($state) || empty($pincode)) {
          $error = 'Please fill in all required fields.';
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $error = 'Please enter a valid email address.';
      } elseif (empty($_SESSION['cart'])) {
          $error = 'Your cart is empty.';
      } else {
          try {
              $pdo = get_db();
              $pdo->beginTransaction();

              // Fetch cart products
              $ids = array_keys($_SESSION['cart']);
              $placeholders = implode(',', array_fill(0, count($ids), '?'));
              $stmt = $pdo->prepare("SELECT id, name, price, sale_price, stock_quantity FROM products WHERE id IN ($placeholders) AND is_active = 1");
              $stmt->execute($ids);
              $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

              $subtotal = 0;
              $orderItems = [];

              foreach ($products as $p) {
                  $qty = $_SESSION['cart'][$p['id']];
                  $unitPrice = ($p['sale_price'] && $p['sale_price'] > 0) ? $p['sale_price'] : $p['price'];
                  $lineTotal = $unitPrice * $qty;
                  $subtotal += $lineTotal;

                  $orderItems[] = [
                      'product_id' => $p['id'],
                      'product_name' => $p['name'],
                      'quantity' => $qty,
                      'unit_price' => $unitPrice,
                      'total_price' => $lineTotal
                  ];
              }

              $shipping = $subtotal >= 999 ? 0 : 99;
              $total = $subtotal + $shipping;
              $orderNumber = 'RTC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

              // Insert order
              $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, shipping_address, city, state, pincode, subtotal, shipping_cost, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
              $stmt->execute([$orderNumber, $name, $email, $phone, $address, $city, $state, $pincode, $subtotal, $shipping, $total]);
              $orderId = $pdo->lastInsertId();

              // Insert order items
              $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
              foreach ($orderItems as $item) {
                  $itemStmt->execute([$orderId, $item['product_id'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['total_price']]);
              }

              $pdo->commit();

              // Process payment
              $paymentResult = processPayment($orderId, $total, $email);

              if ($paymentResult['success']) {
                  $_SESSION['cart'] = [];
                  $success = true;
              } else {
                  $error = $paymentResult['error'] ?? 'Payment processing failed.';
              }
          } catch (Exception $e) {
              if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
              error_log("Checkout error: " . $e->getMessage());
              $error = 'An error occurred while processing your order. Please try again.';
          }
      }
  }

  // Get cart for display
  $cartItems = [];
  $subtotal = 0;
  if (!$success && !empty($_SESSION['cart'])) {
      try {
          $pdo = get_db();
          $ids = array_keys($_SESSION['cart']);
          $placeholders = implode(',', array_fill(0, count($ids), '?'));
          $stmt = $pdo->prepare("SELECT id, name, price, sale_price FROM products WHERE id IN ($placeholders) AND is_active = 1");
          $stmt->execute($ids);
          foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
              $qty = $_SESSION['cart'][$p['id']];
              $unitPrice = ($p['sale_price'] && $p['sale_price'] > 0) ? $p['sale_price'] : $p['price'];
              $lineTotal = $unitPrice * $qty;
              $subtotal += $lineTotal;
              $cartItems[] = ['name' => $p['name'], 'qty' => $qty, 'unit_price' => $unitPrice, 'line_total' => $lineTotal];
          }
      } catch (Exception $e) {}
  }
  $shipping = $subtotal >= 999 ? 0 : ($subtotal > 0 ? 99 : 0);
  $total = $subtotal + $shipping;

  include $pathPrefix . 'includes/header.php';
?>

<main>
<div id="page-checkout" class="page active" style="padding-top:100px;">
  <div class="section">
    <div style="text-align:center; margin-bottom:32px;">
      <div class="section-label">Secure Checkout</div>
      <h1 class="section-title">Checkout</h1>
      <div class="divider" style="margin:16px auto;"></div>
    </div>

    <?php if ($success): ?>
    <!-- Order Confirmation -->
    <div style="max-width:600px; margin:0 auto; text-align:center; padding:40px 20px;">
      <div style="font-size:48px; margin-bottom:16px;">✓</div>
      <h2 style="font-family:'Cormorant Garamond',serif; font-size:28px; color:var(--brown); margin-bottom:12px;">Order Confirmed!</h2>
      <p style="color:var(--brown-light); font-size:15px; margin-bottom:8px;">Your order <strong><?php echo htmlspecialchars($orderNumber); ?></strong> has been placed successfully.</p>
      <p style="color:var(--brown-light); font-size:14px; margin-bottom:24px;">A confirmation email will be sent to your registered email address.</p>
      <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
        <a href="shop.php" class="btn-primary" style="text-decoration:none; padding:12px 28px;">Continue Shopping</a>
        <a href="index.php" class="btn-outline" style="text-decoration:none; padding:12px 28px;">Go Home</a>
      </div>
    </div>

    <?php elseif (empty($_SESSION['cart'])): ?>
    <div style="text-align:center; padding:60px 20px;">
      <p style="font-family:'Cormorant Garamond',serif; font-size:24px; color:var(--brown); margin-bottom:16px;">Your cart is empty</p>
      <a href="shop.php" class="btn-primary" style="text-decoration:none; padding:14px 32px;">Browse Shop</a>
    </div>

    <?php else: ?>
    <!-- Checkout Form -->
    <div class="contact-grid" style="max-width:1000px; margin:0 auto;">
      <form class="contact-form" method="POST" action="checkout.php" novalidate>
        <h3>Shipping Details</h3>
        <?php if ($error): ?>
        <div class="form-feedback" style="display:block; background:#fde8e8; color:#c0392b; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        <div class="form-fields-wrapper">
          <div class="form-group"><label class="form-label">Full Name *</label><input class="form-input" name="name" type="text" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Your full name"></div>
          <div class="form-group"><label class="form-label">Email *</label><input class="form-input" name="email" type="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="your@email.com"></div>
          <div class="form-group"><label class="form-label">Phone</label><input class="form-input" name="phone" type="tel" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+91 XXXXXXXXXX"></div>
          <div class="form-group"><label class="form-label">Shipping Address *</label><textarea class="form-input form-textarea" name="address" rows="3" required placeholder="House/Flat No., Street, Landmark"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea></div>
          <div class="form-group"><label class="form-label">City *</label><input class="form-input" name="city" type="text" required value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" placeholder="Mumbai"></div>
          <div class="form-group"><label class="form-label">State *</label><input class="form-input" name="state" type="text" required value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>" placeholder="Maharashtra"></div>
          <div class="form-group"><label class="form-label">Pincode *</label><input class="form-input" name="pincode" type="text" required value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>" placeholder="400001" maxlength="6"></div>
          <button class="btn-primary" type="submit" style="width:100%; justify-content:center; padding:14px;">Place Order — ₹<?php echo number_format($total, 0); ?></button>
        </div>
      </form>

      <!-- Order Summary -->
      <div>
        <div class="section-label" style="margin-bottom:8px;">Order Summary</div>
        <h2 style="font-size:24px; margin-bottom:12px;">Your Items</h2>
        <div class="divider" style="margin-bottom:20px;"></div>
        <?php foreach ($cartItems as $item): ?>
        <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:14px; color:var(--brown);">
          <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['qty']; ?></span>
          <span style="font-weight:600;">₹<?php echo number_format($item['line_total'], 0); ?></span>
        </div>
        <?php endforeach; ?>
        <div style="border-top:1px solid rgba(59,42,34,0.1); margin-top:12px; padding-top:12px;">
          <div style="display:flex; justify-content:space-between; font-size:14px; color:var(--brown-light); margin-bottom:6px;">
            <span>Subtotal</span><span>₹<?php echo number_format($subtotal, 0); ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:14px; color:var(--brown-light); margin-bottom:6px;">
            <span>Shipping</span><span><?php echo $shipping == 0 ? 'FREE' : '₹' . $shipping; ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:700; color:var(--brown); padding-top:8px; border-top:1px solid rgba(59,42,34,0.1);">
            <span>Total</span><span>₹<?php echo number_format($total, 0); ?></span>
          </div>
        </div>
        <a href="cart.php" style="display:block; text-align:center; margin-top:16px; font-size:13px; color:var(--brown-light);">← Edit Cart</a>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>
</main>

<?php include $pathPrefix . 'includes/footer.php'; ?>
