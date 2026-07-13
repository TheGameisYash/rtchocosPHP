<?php
// api_cart.php — AJAX Cart Operations
// Matches existing api_blogs.php / api_comments.php pattern

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Also accept form-encoded data
if (empty($input)) {
    $input = $_POST;
}

$action = $input['action'] ?? $_GET['action'] ?? '';

try {
    $pdo = get_db();

    switch ($action) {
        case 'add':
            $productId = (int)($input['product_id'] ?? 0);
            $qty = max(1, (int)($input['quantity'] ?? 1));
            
            if ($productId <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
                exit;
            }
            
            // Verify product exists and is active
            $stmt = $pdo->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }
            
            $currentQty = $_SESSION['cart'][$productId] ?? 0;
            $newQty = $currentQty + $qty;
            
            if ($product['stock_quantity'] > 0 && $newQty > $product['stock_quantity']) {
                echo json_encode(['success' => false, 'error' => 'Not enough stock available']);
                exit;
            }
            
            $_SESSION['cart'][$productId] = $newQty;
            echo json_encode([
                'success' => true, 
                'message' => htmlspecialchars($product['name']) . ' added to cart',
                'cart_count' => array_sum($_SESSION['cart'])
            ]);
            break;

        case 'update':
            $productId = (int)($input['product_id'] ?? 0);
            $qty = (int)($input['quantity'] ?? 0);
            
            if ($qty <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = $qty;
            }
            
            echo json_encode([
                'success' => true, 
                'cart_count' => array_sum($_SESSION['cart'])
            ]);
            break;

        case 'remove':
            $productId = (int)($input['product_id'] ?? 0);
            unset($_SESSION['cart'][$productId]);
            
            echo json_encode([
                'success' => true, 
                'cart_count' => array_sum($_SESSION['cart'])
            ]);
            break;

        case 'get':
            $cartItems = [];
            $subtotal = 0;
            
            if (!empty($_SESSION['cart'])) {
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
                    
                    $cartItems[] = [
                        'id' => $p['id'],
                        'slug' => $p['slug'],
                        'name' => $p['name'],
                        'price' => (float)$p['price'],
                        'sale_price' => $p['sale_price'] ? (float)$p['sale_price'] : null,
                        'unit_price' => (float)$unitPrice,
                        'image' => $p['image_main'],
                        'quantity' => $qty,
                        'line_total' => $lineTotal,
                        'in_stock' => $p['stock_quantity'] > 0 || $p['stock_quantity'] == -1
                    ];
                }
            }
            
            $shipping = $subtotal >= 999 ? 0 : 99;
            
            echo json_encode([
                'success' => true,
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $subtotal + $shipping,
                'cart_count' => array_sum($_SESSION['cart'])
            ]);
            break;

        case 'clear':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true, 'cart_count' => 0]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Cart API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>
