<?php
// admin/orders.php - Order Fulfillment and Customer Order Management
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// AJAX requests handling
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1');

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $search = trim($_GET['search'] ?? '');
    $orderStatus = trim($_GET['order_status'] ?? '');
    $paymentStatus = trim($_GET['payment_status'] ?? '');

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ? OR city LIKE ?)";
        $wild = "%$search%";
        $params = array_merge($params, [$wild, $wild, $wild, $wild, $wild]);
    }
    if ($orderStatus !== '') {
        $where[] = "order_status = ?";
        $params[] = $orderStatus;
    }
    if ($paymentStatus !== '') {
        $where[] = "payment_status = ?";
        $params[] = $paymentStatus;
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    $exportStmt = $pdo->prepare("SELECT order_number, created_at, customer_name, customer_email, customer_phone, shipping_address, city, state, pincode, subtotal, shipping_cost, total, payment_status, payment_id, order_status, notes FROM orders $whereSql ORDER BY created_at DESC");
    $exportStmt->execute($params);
    $ordersData = $exportStmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=orders_export_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order Number', 'Date', 'Customer Name', 'Email', 'Phone', 'Address', 'City', 'State', 'Pincode', 'Subtotal', 'Shipping', 'Total', 'Payment Status', 'Payment ID', 'Order Status', 'Notes']);

    foreach ($ordersData as $row) {
        fputcsv($output, [
            $row['order_number'],
            $row['created_at'],
            $row['customer_name'],
            $row['customer_email'],
            $row['customer_phone'],
            $row['shipping_address'],
            $row['city'],
            $row['state'],
            $row['pincode'],
            $row['subtotal'],
            $row['shipping_cost'],
            $row['total'],
            $row['payment_status'],
            $row['payment_id'],
            $row['order_status'],
            $row['notes']
        ]);
    }
    fclose($output);
    exit;
}

// Handle GET AJAX details request
if (isset($_GET['action']) && $_GET['action'] === 'get_details') {
    $orderId = (int)($_GET['order_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $ord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ord) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    // Fetch order items joined with product thumbnail
    $itemsStmt = $pdo->prepare("SELECT oi.*, p.image_main, p.slug 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order' => $ord,
        'items' => $items
    ]);
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please reload.']);
            exit;
        }
        $error = 'Invalid security token.';
    } else {
        try {
            // 1. Update Order Status
            if ($action === 'update_order_status') {
                $orderId = (int)($_POST['order_id'] ?? 0);
                $newStatus = trim($_POST['order_status'] ?? '');
                $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

                if ($orderId > 0 && in_array($newStatus, $validStatuses)) {
                    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $orderId]);

                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'new_status' => $newStatus,
                            'message' => 'Order status updated to ' . ucfirst($newStatus) . '.'
                        ]);
                        exit;
                    }
                    $success = 'Order status updated to ' . ucfirst($newStatus) . '.';
                }
            }
            // 2. Update Payment Status
            elseif ($action === 'update_payment_status') {
                $orderId = (int)($_POST['order_id'] ?? 0);
                $newPaymentStatus = trim($_POST['payment_status'] ?? '');
                $validPayment = ['pending', 'paid', 'failed', 'refunded'];

                if ($orderId > 0 && in_array($newPaymentStatus, $validPayment)) {
                    $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newPaymentStatus, $orderId]);

                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'new_status' => $newPaymentStatus,
                            'message' => 'Payment status updated to ' . ucfirst($newPaymentStatus) . '.'
                        ]);
                        exit;
                    }
                    $success = 'Payment status updated to ' . ucfirst($newPaymentStatus) . '.';
                }
            }
            // 3. Bulk Status Update
            elseif ($action === 'bulk_update_status') {
                $bulkStatus = trim($_POST['bulk_order_status'] ?? '');
                $selectedIds = $_POST['selected_ids'] ?? [];
                $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

                if (in_array($bulkStatus, $validStatuses) && is_array($selectedIds) && !empty($selectedIds)) {
                    $sanitizedIds = array_map('intval', $selectedIds);
                    $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));
                    $params = array_merge([$bulkStatus], $sanitizedIds);

                    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
                    $stmt->execute($params);

                    $success = count($sanitizedIds) . ' orders updated to ' . ucfirst($bulkStatus) . '.';
                }
            }
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            }
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Search and Filter Params
$search = trim($_GET['search'] ?? '');
$orderStatus = trim($_GET['order_status'] ?? '');
$paymentStatus = trim($_GET['payment_status'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

// Build Query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ? OR city LIKE ?)";
    $wild = "%$search%";
    $params = array_merge($params, [$wild, $wild, $wild, $wild, $wild]);
}

if ($orderStatus !== '') {
    $where[] = "order_status = ?";
    $params[] = $orderStatus;
}

if ($paymentStatus !== '') {
    $where[] = "payment_status = ?";
    $params[] = $paymentStatus;
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sort Clause
$orderClause = "created_at DESC";
switch ($sort) {
    case 'oldest':
        $orderClause = "created_at ASC";
        break;
    case 'total_high':
        $orderClause = "total DESC";
        break;
    case 'total_low':
        $orderClause = "total ASC";
        break;
    case 'newest':
    default:
        $orderClause = "created_at DESC";
        break;
}

// KPI Metrics - Consolidated single query for high remote DB speed
$kpis = [
    'total_orders' => 0,
    'total_revenue' => 0.00,
    'pending_orders' => 0,
    'delivered_orders' => 0
];

try {
    $kpiRow = $pdo->query("SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END), 0) as total_revenue,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
    FROM orders")->fetch(PDO::FETCH_ASSOC);

    if ($kpiRow) {
        $kpis['total_orders'] = (int)($kpiRow['total_orders'] ?? 0);
        $kpis['total_revenue'] = (float)($kpiRow['total_revenue'] ?? 0);
        $kpis['pending_orders'] = (int)($kpiRow['pending_orders'] ?? 0);
        $kpis['delivered_orders'] = (int)($kpiRow['delivered_orders'] ?? 0);
    }

    // Total Filtered Count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $whereSql");
    $countStmt->execute($params);
    $totalFiltered = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalFiltered / $limit));

    // Fetch Orders with items count
    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count 
            FROM orders o 
            $whereSql 
            ORDER BY $orderClause 
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$csrfToken = generate_csrf();
render_admin_header("Store Orders", "orders");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Bulk Actions Slider Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div class="bulk-info"><span id="bulkSelectedCount">0</span> orders selected</div>
    <div class="bulk-actions-btns">
        <form action="orders.php" method="POST" id="bulkForm" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="bulk_update_status">
            <input type="hidden" name="bulk_order_status" id="bulkStatusField" value="">
            <div id="bulkIdsContainer"></div>

            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulkOrder('confirmed')">Mark Confirmed</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulkOrder('shipped')">Mark Shipped</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulkOrder('delivered')">Mark Delivered</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="submitBulkOrder('cancelled')">Cancel Orders</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="clearBulkSelection()">Deselect</button>
        </form>
    </div>
</div>

<!-- Primary Action Bar (Clean Single Header) -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <p style="color: var(--text-light); margin: 0; font-size: 14px;">Monitor store checkout orders, update delivery milestones, and issue invoices</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="orders.php?action=export_csv&<?php echo http_build_query($_GET); ?>" class="btn btn-outline btn-sm" title="Download CSV Report">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export CSV
        </a>
    </div>
</div>

<!-- KPI Metrics Ribbon (Proper Vertical Stacking & Modern Icons) -->
<div class="metrics-grid" style="margin-bottom: 24px;">
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">TOTAL ORDERS</div>
            <div class="metric-value"><?php echo number_format($kpis['total_orders']); ?></div>
            <div class="metric-subtitle">All-time customer checkouts</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">TOTAL REVENUE</div>
            <div class="metric-value" style="color: var(--gold-light);">₹<?php echo number_format($kpis['total_revenue'], 2); ?></div>
            <div class="metric-subtitle">Paid transactions</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">PENDING FULFILLMENT</div>
            <div class="metric-value" style="color: <?php echo $kpis['pending_orders'] > 0 ? '#ef6c00' : 'var(--text-main)'; ?>;">
                <?php echo number_format($kpis['pending_orders']); ?>
            </div>
            <div class="metric-subtitle">Needs packaging & dispatch</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">DELIVERED ORDERS</div>
            <div class="metric-value" style="color: #2e7d32;"><?php echo number_format($kpis['delivered_orders']); ?></div>
            <div class="metric-subtitle">Successfully received</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<!-- Search, Filter & Controls Toolbar -->
<div class="filter-toolbar">
    <form action="orders.php" method="GET" id="orderFilterForm" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; width: 100%;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1 1 400px;">
            <!-- Keyword Search -->
            <div class="filter-search-box">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" placeholder="Search order #, customer, email, city..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <!-- Order Status Filter -->
            <select name="order_status" class="filter-select" onchange="document.getElementById('orderFilterForm').submit()">
                <option value="">All Order Statuses</option>
                <option value="pending" <?php echo $orderStatus === 'pending' ? 'selected' : ''; ?>>Pending (Needs Action)</option>
                <option value="confirmed" <?php echo $orderStatus === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                <option value="shipped" <?php echo $orderStatus === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                <option value="delivered" <?php echo $orderStatus === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                <option value="cancelled" <?php echo $orderStatus === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>

            <!-- Payment Status Filter -->
            <select name="payment_status" class="filter-select" onchange="document.getElementById('orderFilterForm').submit()">
                <option value="">All Payment Statuses</option>
                <option value="paid" <?php echo $paymentStatus === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="pending" <?php echo $paymentStatus === 'pending' ? 'selected' : ''; ?>>Payment Pending</option>
                <option value="refunded" <?php echo $paymentStatus === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                <option value="failed" <?php echo $paymentStatus === 'failed' ? 'selected' : ''; ?>>Failed</option>
            </select>

            <!-- Sort By -->
            <select name="sort" class="filter-select" onchange="document.getElementById('orderFilterForm').submit()">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Date: Newest First</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Date: Oldest First</option>
                <option value="total_high" <?php echo $sort === 'total_high' ? 'selected' : ''; ?>>Amount: High to Low</option>
                <option value="total_low" <?php echo $sort === 'total_low' ? 'selected' : ''; ?>>Amount: Low to High</option>
            </select>

            <?php if (!empty($search) || !empty($orderStatus) || !empty($paymentStatus)): ?>
                <a href="orders.php" class="btn btn-outline btn-sm" style="height: 38px; padding: 0 12px; display: inline-flex; align-items: center;" title="Reset Filters">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Orders Table -->
<?php if (empty($orders)): ?>
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 40px; margin-bottom: 12px; color: var(--gold-light);">📦</div>
        <h3 style="font-family:'Cormorant Garamond',serif; font-size: 22px; margin-bottom: 8px;">No Orders Found</h3>
        <p style="color: var(--text-light); max-width: 420px; margin: 0 auto 20px;">No store orders match your current filter settings or search query. Customer checkout purchases will appear here in real time.</p>
        <a href="orders.php" class="btn btn-outline btn-sm">Reset Filters</a>
    </div>
<?php else: ?>
    <div class="admin-table-card">
        <div class="table-responsive">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">
                            <input type="checkbox" id="selectAllOrders" onchange="toggleSelectAllOrders()" style="cursor: pointer; accent-color: var(--gold-light);">
                        </th>
                        <th>Order # & Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Order Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                        <?php
                            // Generate customer initials
                            $nameParts = explode(' ', trim($o['customer_name'] ?? 'Guest'));
                            $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                        ?>
                        <tr id="order-row-<?php echo $o['id']; ?>">
                            <td style="text-align: center;">
                                <input type="checkbox" class="order-select-checkbox" value="<?php echo $o['id']; ?>" onchange="updateBulkOrderBar()" style="cursor: pointer; accent-color: var(--gold-light);">
                            </td>
                            <td>
                                <a href="javascript:void(0)" onclick="openOrderModal(<?php echo $o['id']; ?>)" style="font-weight: 700; color: var(--gold-light); font-size: 13.5px; text-decoration: none;">
                                    <?php echo htmlspecialchars($o['order_number']); ?>
                                </a>
                                <div style="font-size: 11.5px; color: var(--text-light); margin-top: 3px;">
                                    <?php echo date('M d, Y · h:i A', strtotime($o['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="customer-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 13px;">
                                            <?php echo htmlspecialchars($o['customer_name']); ?>
                                        </div>
                                        <div style="font-size: 11.5px; color: var(--text-light);">
                                            <?php echo htmlspecialchars($o['customer_email']); ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--text-light);">
                                            <?php echo htmlspecialchars($o['city'] . ($o['state'] ? ', ' . $o['state'] : '')); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge" style="background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); font-size: 11px;">
                                    <?php echo (int)$o['items_count']; ?> item<?php echo (int)$o['items_count'] !== 1 ? 's' : ''; ?>
                                </span>
                            </td>
                            <td>
                                <strong style="font-size: 14px; font-family: var(--font-sans);">₹<?php echo number_format($o['total'], 2); ?></strong>
                                <?php if ($o['shipping_cost'] > 0): ?>
                                    <div style="font-size: 10.5px; color: var(--text-light);">incl. ₹<?php echo number_format($o['shipping_cost'], 0); ?> shipping</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $o['payment_status'] === 'paid' ? 'active' : ($o['payment_status'] === 'pending' ? 'pending' : 'cancelled'); ?>">
                                    <?php echo ucfirst($o['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <!-- Quick Status Selector -->
                                <select class="status-badge <?php echo $o['order_status']; ?>" id="status-select-<?php echo $o['id']; ?>" onchange="quickUpdateOrderStatus(<?php echo $o['id']; ?>, this.value)" style="cursor: pointer; border: 1px solid var(--border-color); font-weight: 600; padding: 4px 10px; border-radius: 20px; outline: none; background: var(--bg-card);">
                                    <option value="pending" <?php echo $o['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $o['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="shipped" <?php echo $o['order_status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $o['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $o['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openOrderModal(<?php echo $o['id']; ?>)" title="View Order Details">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Details
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="printOrderInvoice(<?php echo $o['id']; ?>)" title="Print Invoice">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 13px; color: var(--text-light);">
            Showing <?php echo number_format($offset + 1); ?>–<?php echo number_format(min($totalFiltered, $offset + $limit)); ?> of <?php echo number_format($totalFiltered); ?> orders
        </div>
        <div style="display: flex; gap: 6px;">
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-outline btn-sm">Previous</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>" style="min-width: 32px; justify-content: center;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-outline btn-sm">Next</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Order Details Modal -->
<div class="order-modal-backdrop" id="orderDetailsModal">
    <div class="order-modal-content" style="max-width: 780px;">
        <div class="order-modal-header">
            <div>
                <h3 style="font-family:'Cormorant Garamond',serif; font-size: 24px; margin: 0;" id="modalOrderNumber">Order Details</h3>
                <div style="font-size: 12px; color: var(--text-light);" id="modalOrderDate">Placed on ...</div>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" class="btn btn-outline btn-sm" id="modalPrintBtn" onclick="printCurrentOrderInvoice()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeOrderModal()" style="padding: 4px 8px;">✕</button>
            </div>
        </div>

        <div class="order-modal-body" id="modalBodyContent">
            <!-- Loaded dynamically via AJAX -->
            <div style="text-align: center; padding: 40px; color: var(--text-light);">Loading order details...</div>
        </div>

        <div class="order-modal-footer" style="justify-content: space-between;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <span style="font-size: 13px; font-weight: 600;">Update Status:</span>
                <select id="modalStatusSelect" class="form-control" style="width: auto; height: 34px; font-size: 12.5px;">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveModalOrderStatus()">Save Status</button>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeOrderModal()">Close</button>
        </div>
    </div>
</div>

<!-- Hidden printable invoice container -->
<div id="printableInvoiceArea" style="display: none;"></div>

<script>
    const csrfToken = <?php echo json_encode($csrfToken); ?>;
    let currentLoadedOrder = null;

    // Quick inline status update
    function quickUpdateOrderStatus(id, newStatus) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'update_order_status');
        formData.append('order_id', id);
        formData.append('order_status', newStatus);
        formData.append('is_ajax', '1');

        fetch('orders.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                const sel = document.getElementById('status-select-' + id);
                if (sel) {
                    sel.className = 'status-badge ' + newStatus;
                }
            } else {
                showToast(data.message || 'Failed to update status', 'danger');
            }
        })
        .catch(() => showToast('Network error updating order status.', 'danger'));
    }

    // Open Modal & Fetch Details
    function openOrderModal(id) {
        const modal = document.getElementById('orderDetailsModal');
        const body = document.getElementById('modalBodyContent');
        modal.classList.add('open');
        body.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-light);">Loading order information...</div>';

        fetch('orders.php?action=get_details&order_id=' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentLoadedOrder = data;
                renderModalData(data.order, data.items);
            } else {
                body.innerHTML = '<div style="color: #d32f2f; text-align: center; padding: 20px;">' + (data.message || 'Error loading details.') + '</div>';
            }
        })
        .catch(() => {
            body.innerHTML = '<div style="color: #d32f2f; text-align: center; padding: 20px;">Network error loading details.</div>';
        });
    }

    function closeOrderModal() {
        document.getElementById('orderDetailsModal').classList.remove('open');
        currentLoadedOrder = null;
    }

    function renderModalData(order, items) {
        document.getElementById('modalOrderNumber').textContent = 'Order #' + order.order_number;
        document.getElementById('modalOrderDate').textContent = 'Placed on ' + order.created_at;
        document.getElementById('modalStatusSelect').value = order.order_status;

        let itemsHtml = '';
        items.forEach(item => {
            const thumb = item.image_main ? '../' + item.image_main : '../assets/premium_chocolate.png';
            itemsHtml += `
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 10px; display: flex; align-items: center; gap: 12px;">
                        <img src="${thumb}" alt="${item.product_name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;" onerror="this.onerror=null;this.src='../assets/premium_chocolate.png';">
                        <div>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 13px;">${item.product_name}</div>
                            <div style="font-size: 11px; color: var(--text-light);">Product ID: #${item.product_id}</div>
                        </div>
                    </td>
                    <td style="padding: 10px; text-align: center;">${item.quantity}</td>
                    <td style="padding: 10px; text-align: right;">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 600;">₹${parseFloat(item.total_price).toFixed(2)}</td>
                </tr>
            `;
        });

        const html = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Customer Details Box -->
                <div style="background: var(--bg-subtle); padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gold-light); margin-bottom: 8px;">Customer & Shipping</div>
                    <div style="font-weight: 600; font-size: 14px; color: var(--text-main); margin-bottom: 4px;">${order.customer_name}</div>
                    <div style="font-size: 12.5px; color: var(--text-light); margin-bottom: 2px;">✉️ ${order.customer_email}</div>
                    <div style="font-size: 12.5px; color: var(--text-light); margin-bottom: 8px;">📞 ${order.customer_phone || 'Not provided'}</div>
                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4; border-top: 1px solid var(--border-color); padding-top: 6px;">
                        📍 ${order.shipping_address}<br>
                        ${order.city ? order.city + ', ' : ''}${order.state ? order.state + ' - ' : ''}${order.pincode || ''}
                    </div>
                </div>

                <!-- Payment & Order Meta Box -->
                <div style="background: var(--bg-subtle); padding: 14px 16px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gold-light); margin-bottom: 8px;">Payment & Status</div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span style="color: var(--text-light);">Payment Status:</span>
                        <span class="status-badge ${order.payment_status === 'paid' ? 'active' : 'pending'}">${order.payment_status.toUpperCase()}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span style="color: var(--text-light);">Transaction ID:</span>
                        <span style="font-family: monospace; font-size: 12px;">${order.payment_id || 'N/A'}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                        <span style="color: var(--text-light);">Order Status:</span>
                        <span class="status-badge ${order.order_status}">${order.order_status.toUpperCase()}</span>
                    </div>
                    ${order.notes ? `<div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid var(--border-color); font-size: 12px; color: var(--text-muted);"><em>"Notes: ${order.notes}"</em></div>` : ''}
                </div>
            </div>

            <!-- Items Table -->
            <div style="margin-bottom: 20px;">
                <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--text-main);">Purchased Chocolates & Confections</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); text-align: left; font-size: 11.5px; color: var(--text-light); text-transform: uppercase;">
                            <th style="padding: 8px 10px;">Item</th>
                            <th style="padding: 8px 10px; text-align: center;">Qty</th>
                            <th style="padding: 8px 10px; text-align: right;">Unit Price</th>
                            <th style="padding: 8px 10px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                </table>
            </div>

            <!-- Financial Totals Breakdown -->
            <div style="display: flex; justify-content: flex-end;">
                <div style="width: 260px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--text-light);">
                        <span>Subtotal:</span>
                        <span>₹${parseFloat(order.subtotal).toFixed(2)}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; color: var(--text-light);">
                        <span>Shipping Cost:</span>
                        <span>₹${parseFloat(order.shipping_cost).toFixed(2)}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-top: 1px solid var(--border-color); margin-top: 4px; font-size: 16px; font-weight: 700; color: var(--text-main);">
                        <span>Grand Total:</span>
                        <span style="color: var(--gold-light);">₹${parseFloat(order.total).toFixed(2)}</span>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('modalBodyContent').innerHTML = html;
    }

    // Save Status from inside modal
    function saveModalOrderStatus() {
        if (!currentLoadedOrder) return;
        const newStatus = document.getElementById('modalStatusSelect').value;
        const orderId = currentLoadedOrder.order.id;

        quickUpdateOrderStatus(orderId, newStatus);
        currentLoadedOrder.order.order_status = newStatus;
        setTimeout(() => {
            renderModalData(currentLoadedOrder.order, currentLoadedOrder.items);
            showToast('Order status updated.', 'success');
        }, 300);
    }

    // Invoice Printing
    function printCurrentOrderInvoice() {
        if (!currentLoadedOrder) return;
        printOrder(currentLoadedOrder.order, currentLoadedOrder.items);
    }

    function printOrderInvoice(id) {
        fetch('orders.php?action=get_details&order_id=' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                printOrder(data.order, data.items);
            } else {
                showToast('Unable to prepare invoice', 'danger');
            }
        });
    }

    function printOrder(order, items) {
        let itemsRows = '';
        items.forEach((item, i) => {
            itemsRows += `
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">${i + 1}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>${item.product_name}</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">${item.quantity}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">₹${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">₹${parseFloat(item.total_price).toFixed(2)}</td>
                </tr>
            `;
        });

        const printHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Invoice - ${order.order_number}</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #222; margin: 30px; font-size: 13px; line-height: 1.5; }
                    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2c1a0e; padding-bottom: 16px; margin-bottom: 20px; }
                    .logo { font-size: 24px; font-weight: bold; color: #2c1a0e; font-family: serif; letter-spacing: 1px; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th { background: #f7f4ef; padding: 10px 8px; text-align: left; font-size: 12px; border-bottom: 2px solid #ccc; }
                    .summary { width: 300px; margin-left: auto; }
                    .summary td { padding: 4px 8px; }
                    .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 11px; color: #666; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div>
                        <div class="logo">RT CHOCOS</div>
                        <div>Artisan Bean-to-Bar Chocolates</div>
                        <div>www.rtchocos.com · hello@rtchocos.com</div>
                    </div>
                    <div style="text-align: right;">
                        <h2 style="margin: 0; color: #2c1a0e;">TAX INVOICE</h2>
                        <div style="font-weight: bold; margin-top: 4px;">#${order.order_number}</div>
                        <div>Date: ${order.created_at}</div>
                        <div>Payment: ${order.payment_status.toUpperCase()}</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <strong>Billed & Shipped To:</strong>
                        <div>${order.customer_name}</div>
                        <div>${order.shipping_address}</div>
                        <div>${order.city ? order.city + ', ' : ''}${order.state ? order.state + ' ' : ''}${order.pincode}</div>
                        <div>Phone: ${order.customer_phone || 'N/A'}</div>
                        <div>Email: ${order.customer_email}</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Description</th>
                            <th style="text-align: center; width: 60px;">Qty</th>
                            <th style="text-align: right; width: 100px;">Unit Price</th>
                            <th style="text-align: right; width: 100px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsRows}
                    </tbody>
                </table>

                <div class="summary">
                    <table>
                        <tr>
                            <td>Subtotal:</td>
                            <td style="text-align: right;">₹${parseFloat(order.subtotal).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td>Shipping:</td>
                            <td style="text-align: right;">₹${parseFloat(order.shipping_cost).toFixed(2)}</td>
                        </tr>
                        <tr style="font-size: 15px; font-weight: bold; border-top: 1px solid #222;">
                            <td style="padding-top: 8px;">Total Paid:</td>
                            <td style="text-align: right; padding-top: 8px;">₹${parseFloat(order.total).toFixed(2)}</td>
                        </tr>
                    </table>
                </div>

                <div class="footer">
                    Thank you for choosing RT Chocos Handcrafted Chocolates! Hand-tempered with love in India.
                </div>
                <script>
                    window.onload = function() { window.print(); }
                <\/script>
            </body>
            </html>
        `;

        const printWindow = window.open('', '_blank', 'width=800,height=700');
        printWindow.document.open();
        printWindow.document.write(printHtml);
        printWindow.document.close();
    }

    // Bulk selection management
    function updateBulkOrderBar() {
        const checkboxes = document.querySelectorAll('.order-select-checkbox:checked');
        const count = checkboxes.length;
        const bulkBar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('bulkSelectedCount');

        countSpan.textContent = count;
        if (count > 0) {
            bulkBar.classList.add('active');
        } else {
            bulkBar.classList.remove('active');
        }
    }

    function toggleSelectAllOrders() {
        const selectAll = document.getElementById('selectAllOrders');
        const checkboxes = document.querySelectorAll('.order-select-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkOrderBar();
    }

    function clearBulkSelection() {
        document.querySelectorAll('.order-select-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllOrders');
        if (selectAll) selectAll.checked = false;
        updateBulkOrderBar();
    }

    function submitBulkOrder(status) {
        const checked = document.querySelectorAll('.order-select-checkbox:checked');
        if (checked.length === 0) return;

        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkStatusField').value = status;
        document.getElementById('bulkForm').submit();
    }
</script>

<?php
render_admin_footer();
?>
