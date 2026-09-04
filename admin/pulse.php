<?php
// admin/pulse.php - Real-Time AJAX Status & Notification Polling Endpoint
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Strict Admin Authentication Check
require_auth();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $pdo = get_db();

    // 1. Pending & Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
    $pendingOrders = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = (int)$stmt->fetchColumn();

    // 2. Latest Order Details
    $stmt = $pdo->query("SELECT id, order_number, customer_name, total, order_status, payment_status, created_at FROM orders ORDER BY id DESC LIMIT 1");
    $latestOrder = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // 3. Unread Messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
    $unreadMessages = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT id, name, subject, created_at FROM contacts WHERE is_read = 0 ORDER BY id DESC LIMIT 1");
    $latestMessage = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // 4. Products & Subscribers Counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM subscribers");
    $totalSubscribers = (int)$stmt->fetchColumn();

    // 5. System Health
    $uploadsPath = __DIR__ . '/../assets/products';
    $uploadsWritable = is_dir($uploadsPath) && is_writable($uploadsPath);

    $mysqlVersion = 'Unknown';
    try {
        $mysqlVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    } catch (Exception $e) {}

    $response = [
        'success' => true,
        'timestamp' => time(),
        'counts' => [
            'pending_orders' => $pendingOrders,
            'total_orders' => $totalOrders,
            'unread_messages' => $unreadMessages,
            'total_products' => $totalProducts,
            'total_subscribers' => $totalSubscribers
        ],
        'latest_order' => $latestOrder ? [
            'id' => (int)$latestOrder['id'],
            'order_number' => $latestOrder['order_number'],
            'customer_name' => $latestOrder['customer_name'],
            'total' => (float)$latestOrder['total'],
            'order_status' => $latestOrder['order_status'],
            'created_at' => $latestOrder['created_at']
        ] : null,
        'latest_message' => $latestMessage ? [
            'id' => (int)$latestMessage['id'],
            'name' => $latestMessage['name'],
            'subject' => $latestMessage['subject'],
            'created_at' => $latestMessage['created_at']
        ] : null,
        'system' => [
            'php_version' => PHP_VERSION,
            'mysql_version' => $mysqlVersion,
            'server_time' => date('Y-m-d H:i:s'),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'uploads_writable' => $uploadsWritable
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
