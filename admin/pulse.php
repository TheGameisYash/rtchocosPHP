<?php
// admin/pulse.php - Real-Time AJAX Status & Notification Polling Endpoint
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Strict Admin Authentication Check
require_auth();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Fast short-term cache for pulse polling across multiple tabs / rapid checks
$pulseCacheFile = __DIR__ . '/../data/cache/pulse_cache.json';
if (file_exists($pulseCacheFile) && (time() - filemtime($pulseCacheFile) < 15)) {
    $cached = @file_get_contents($pulseCacheFile);
    if ($cached !== false) {
        echo $cached;
        exit;
    }
}

try {
    $pdo = get_db();

    // 1. Consolidated Counts Query (1 single ultra-fast query instead of 5 separate queries)
    $stmt = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COUNT(*) FROM contacts WHERE is_read = 0) as unread_messages,
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM subscribers) as total_subscribers
    ");
    $countsRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $pendingOrders = (int)($countsRow['pending_orders'] ?? 0);
    $totalOrders = (int)($countsRow['total_orders'] ?? 0);
    $unreadMessages = (int)($countsRow['unread_messages'] ?? 0);
    $totalProducts = (int)($countsRow['total_products'] ?? 0);
    $totalSubscribers = (int)($countsRow['total_subscribers'] ?? 0);

    // 2. Latest Order Details
    $stmt = $pdo->query("SELECT id, order_number, customer_name, total, order_status, payment_status, created_at FROM orders ORDER BY id DESC LIMIT 1");
    $latestOrder = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // 3. Unread Message Details (Only query if unread count > 0)
    $latestMessage = null;
    if ($unreadMessages > 0) {
        $stmt = $pdo->query("SELECT id, name, subject, created_at FROM contacts WHERE is_read = 0 ORDER BY id DESC LIMIT 1");
        $latestMessage = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

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

    $jsonOutput = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $cacheDir = dirname($pulseCacheFile);
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
    @file_put_contents($pulseCacheFile, $jsonOutput);

    echo $jsonOutput;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
