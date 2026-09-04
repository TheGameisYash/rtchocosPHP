<?php
// admin/products.php - E-Commerce Product Management
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// Check for AJAX request
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || (isset($_POST['is_ajax']) && $_POST['is_ajax'] === '1');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
            exit;
        }
        $error = 'Invalid security token.';
    } else {
        try {
            // 1. Single Toggle Active
            if ($action === 'toggle_active') {
                $productId = (int)($_POST['product_id'] ?? 0);
                if ($productId > 0) {
                    $stmt = $pdo->prepare("UPDATE products SET is_active = (1 - is_active), updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$productId]);
                    
                    // Fetch new status
                    $stmtStatus = $pdo->prepare("SELECT is_active FROM products WHERE id = ?");
                    $stmtStatus->execute([$productId]);
                    $newStatus = (int)$stmtStatus->fetchColumn();

                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'is_active' => $newStatus,
                            'message' => $newStatus ? 'Product is now active on the store.' : 'Product hidden from store.'
                        ]);
                        exit;
                    }
                    $success = $newStatus ? 'Product activated successfully!' : 'Product deactivated.';
                }
            }
            // 2. Single Toggle Featured
            elseif ($action === 'toggle_featured') {
                $productId = (int)($_POST['product_id'] ?? 0);
                if ($productId > 0) {
                    $stmt = $pdo->prepare("UPDATE products SET is_featured = (1 - is_featured), updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$productId]);

                    $stmtStatus = $pdo->prepare("SELECT is_featured FROM products WHERE id = ?");
                    $stmtStatus->execute([$productId]);
                    $newStatus = (int)$stmtStatus->fetchColumn();

                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'is_featured' => $newStatus,
                            'message' => $newStatus ? 'Product featured on store!' : 'Product unfeatured.'
                        ]);
                        exit;
                    }
                    $success = $newStatus ? 'Product featured on store.' : 'Product removed from featured.';
                }
            }
            // 3. Duplicate Product
            elseif ($action === 'duplicate_product') {
                $productId = (int)($_POST['product_id'] ?? 0);
                if ($productId > 0) {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$productId]);
                    $orig = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($orig) {
                        $newName = $orig['name'] . ' (Copy)';
                        $baseSlug = $orig['slug'] . '-copy';
                        $newSlug = $baseSlug;
                        $counter = 1;

                        // Ensure unique slug
                        while (true) {
                            $check = $pdo->prepare("SELECT id FROM products WHERE slug = ?");
                            $check->execute([$newSlug]);
                            if (!$check->fetch()) {
                                break;
                            }
                            $counter++;
                            $newSlug = $baseSlug . '-' . $counter;
                        }

                        $insertStmt = $pdo->prepare("INSERT INTO products (
                            slug, name, short_description, long_description, price, sale_price,
                            stock_quantity, is_active, is_featured, image_main, image_gallery,
                            category, meta_title, meta_description, meta_keywords, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

                        $insertStmt->execute([
                            $newSlug,
                            $newName,
                            $orig['short_description'],
                            $orig['long_description'],
                            $orig['price'],
                            $orig['sale_price'],
                            $orig['stock_quantity'],
                            $orig['image_main'],
                            $orig['image_gallery'],
                            $orig['category'],
                            $orig['meta_title'],
                            $orig['meta_description'],
                            $orig['meta_keywords']
                        ]);

                        $newId = $pdo->lastInsertId();

                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode([
                                'success' => true,
                                'new_id' => $newId,
                                'message' => 'Product duplicated as draft successfully!'
                            ]);
                            exit;
                        }
                        header('Location: products.php?success=' . urlencode('Product duplicated as draft!'));
                        exit;
                    }
                }
            }
            // 4. Delete Product
            elseif ($action === 'delete_product') {
                $productId = (int)($_POST['product_id'] ?? 0);
                if ($productId > 0) {
                    $stmt = $pdo->prepare("SELECT image_main, image_gallery FROM products WHERE id = ?");
                    $stmt->execute([$productId]);
                    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($prod) {
                        // Clean up main image if stored in assets/products/
                        if (!empty($prod['image_main']) && strpos($prod['image_main'], 'assets/products/') === 0) {
                            $filePath = __DIR__ . '/../' . $prod['image_main'];
                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }

                        // Delete record
                        $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                        $delStmt->execute([$productId]);

                        if ($isAjax) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
                            exit;
                        }
                        $success = 'Product deleted successfully.';
                    }
                }
            }
            // 5. Bulk Actions
            elseif ($action === 'bulk_action') {
                $bulkType = $_POST['bulk_type'] ?? '';
                $selectedIds = $_POST['selected_ids'] ?? [];

                if (is_array($selectedIds) && !empty($selectedIds)) {
                    $sanitizedIds = array_map('intval', $selectedIds);
                    $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));

                    if ($bulkType === 'bulk_activate') {
                        $stmt = $pdo->prepare("UPDATE products SET is_active = 1, updated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($sanitizedIds);
                        $success = count($sanitizedIds) . ' products activated.';
                    } elseif ($bulkType === 'bulk_deactivate') {
                        $stmt = $pdo->prepare("UPDATE products SET is_active = 0, updated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($sanitizedIds);
                        $success = count($sanitizedIds) . ' products deactivated.';
                    } elseif ($bulkType === 'bulk_delete') {
                        // Delete products
                        $stmt = $pdo->prepare("DELETE FROM products WHERE id IN ($placeholders)");
                        $stmt->execute($sanitizedIds);
                        $success = count($sanitizedIds) . ' products deleted.';
                    }
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
$category = trim($_GET['category'] ?? '');
$stockStatus = trim($_GET['stock_status'] ?? '');
$activeStatus = trim($_GET['active_status'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$viewMode = $_COOKIE['products_view_pref'] ?? 'grid';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = ($viewMode === 'table') ? 15 : 12;
$offset = ($page - 1) * $limit;

// Build Query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE ? OR category LIKE ? OR short_description LIKE ? OR slug LIKE ?)";
    $searchWild = "%$search%";
    $params[] = $searchWild;
    $params[] = $searchWild;
    $params[] = $searchWild;
    $params[] = $searchWild;
}

if ($category !== '') {
    $where[] = "category = ?";
    $params[] = $category;
}

if ($stockStatus === 'in_stock') {
    $where[] = "stock_quantity > 10";
} elseif ($stockStatus === 'low_stock') {
    $where[] = "stock_quantity > 0 AND stock_quantity <= 10";
} elseif ($stockStatus === 'out_of_stock') {
    $where[] = "stock_quantity = 0";
}

if ($activeStatus === 'active') {
    $where[] = "is_active = 1";
} elseif ($activeStatus === 'inactive') {
    $where[] = "is_active = 0";
}

$whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sort Order
$orderClause = "is_featured DESC, created_at DESC";
switch ($sort) {
    case 'oldest':
        $orderClause = "created_at ASC";
        break;
    case 'price_asc':
        $orderClause = "price ASC";
        break;
    case 'price_desc':
        $orderClause = "price DESC";
        break;
    case 'name_asc':
        $orderClause = "name ASC";
        break;
    case 'stock_asc':
        $orderClause = "stock_quantity ASC";
        break;
    case 'newest':
    default:
        $orderClause = "created_at DESC";
        break;
}

// Consolidated Single Aggregate Query for Metrics & Counts (1 Network Call to Hostinger)
try {
    $metricsRow = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN is_active = 1 THEN 1 END) as active,
            COUNT(CASE WHEN stock_quantity <= 10 THEN 1 END) as low_or_out,
            COUNT(DISTINCT category) as categories
        FROM products
    ")->fetch(PDO::FETCH_ASSOC);

    $metrics = [
        'total' => (int)($metricsRow['total'] ?? 0),
        'active' => (int)($metricsRow['active'] ?? 0),
        'low_or_out' => (int)($metricsRow['low_or_out'] ?? 0),
        'categories' => (int)($metricsRow['categories'] ?? 0)
    ];

    // Total filtered results count
    $countSql = "SELECT COUNT(*) FROM products $whereSql";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalFiltered = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalFiltered / $limit));

    // Fetch Products
    $sql = "SELECT * FROM products $whereSql ORDER BY $orderClause LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all distinct categories with counts for the category pills bar
    $catCountsSql = "SELECT category, COUNT(*) as cat_count FROM products WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY category ASC";
    $catCountRows = $pdo->query($catCountsSql)->fetchAll(PDO::FETCH_ASSOC);
    $categoryStats = [];
    foreach ($catCountRows as $cr) {
        $categoryStats[$cr['category']] = (int)$cr['cat_count'];
    }
    $allCategories = array_keys($categoryStats);

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$csrfToken = generate_csrf();
render_admin_header("Product Catalog", "products");
?>

<style>
    /* Local dropdowns & action buttons - Fixed to open downward */
    .more-actions-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 6px) !important;
        bottom: auto !important;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: var(--shadow-lg);
        z-index: 1000;
        min-width: 155px;
        overflow: hidden;
    }
    
    .more-actions-dropdown button,
    .more-actions-dropdown a {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 9px 16px;
        background: none;
        border: none;
        text-align: left;
        font-size: 13px;
        color: var(--text-muted);
        text-decoration: none;
        cursor: pointer;
        font-family: var(--font-sans);
        transition: background-color var(--transition), color var(--transition);
        gap: 8px;
    }

    .more-actions-dropdown button:hover,
    .more-actions-dropdown a:hover {
        background-color: var(--bg-subtle);
        color: var(--text-main);
    }
    
    .more-actions-dropdown button.delete-btn:hover {
        background-color: rgba(211, 47, 47, 0.08);
        color: #d32f2f;
    }
</style>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Bulk Actions Slider Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div class="bulk-info"><span id="bulkSelectedCount">0</span> products selected</div>
    <div class="bulk-actions-btns">
        <form action="products.php" method="POST" id="bulkForm" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="bulk_action">
            <input type="hidden" name="bulk_type" id="bulkActionType" value="">
            <div id="bulkIdsContainer"></div>
            
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulk('bulk_activate')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                Set Active
            </button>
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulk('bulk_deactivate')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                Set Inactive
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="submitBulk('bulk_delete')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Delete Selected
            </button>
            <button type="button" class="btn btn-outline btn-sm" onclick="clearBulkSelection()">Cancel</button>
        </form>
    </div>
</div>

<!-- Primary Action Bar (Clean Single Header) -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <p style="color: var(--text-light); margin: 0; font-size: 14px;">Manage chocolates, cacao, gifts, pricing, and live inventory</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="../shop.php" target="_blank" class="btn btn-outline btn-sm" title="View Customer Storefront">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            Storefront
        </a>
        <button type="button" class="btn btn-outline btn-sm" onclick="openCategoryManagerModal()" title="Manage chocolate categories">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Categories
        </button>
        <a href="product-editor.php" class="btn btn-primary btn-sm">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            Add Product
        </a>
    </div>
</div>

<!-- KPI Metrics Ribbon (Proper Vertical Stacking) -->
<div class="metrics-grid" style="margin-bottom: 24px;">
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">TOTAL PRODUCTS</div>
            <div class="metric-value"><?php echo number_format($metrics['total']); ?></div>
            <div class="metric-subtitle">Catalog items</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">ACTIVE ON STORE</div>
            <div class="metric-value" style="color: #2e7d32;"><?php echo number_format($metrics['active']); ?></div>
            <div class="metric-subtitle">Visible to shoppers</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">STOCK ALERTS</div>
            <div class="metric-value" style="color: <?php echo $metrics['low_or_out'] > 0 ? '#ef6c00' : 'var(--text-main)'; ?>;">
                <?php echo number_format($metrics['low_or_out']); ?>
            </div>
            <div class="metric-subtitle">Low stock (&le;10) or out</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">CATEGORIES</div>
            <div class="metric-value" style="color: var(--gold-light);"><?php echo number_format($metrics['categories']); ?></div>
            <div class="metric-subtitle">Active product lines</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
        </div>
    </div>
</div>

<!-- Interactive Category Pills Bar -->
<div class="category-pills-bar" id="categoryPillsBar">
    <a href="products.php<?php echo $search ? '?search='.urlencode($search) : ''; ?>" class="cat-pill <?php echo empty($category) ? 'active' : ''; ?>">
        <span>All Products</span>
        <span class="cat-pill-count"><?php echo $metrics['total']; ?></span>
    </a>
    <?php foreach ($categoryStats as $catName => $catCount): ?>
        <a href="products.php?category=<?php echo urlencode($catName); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="cat-pill <?php echo $category === $catName ? 'active' : ''; ?>">
            <span><?php echo htmlspecialchars($catName); ?></span>
            <span class="cat-pill-count"><?php echo $catCount; ?></span>
        </a>
    <?php endforeach; ?>
    <button type="button" class="cat-pill" onclick="openCategoryManagerModal()" style="border-style: dashed; cursor: pointer;" title="Manage Categories">
        <span>⚙️ Manage</span>
    </button>
</div>

<!-- Search, Filter & View Controls -->
<div class="filter-toolbar">
    <form action="products.php" method="GET" id="filterForm" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; width: 100%;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1 1 400px;">
            <!-- Keyword Search (Full Flexible Width) -->
            <div class="filter-search-box">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" placeholder="Search by product name, SKU, keywords..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <!-- Category Filter Dropdown -->
            <select name="category" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Categories</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Stock Status Filter -->
            <select name="stock_status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Inventory</option>
                <option value="in_stock" <?php echo $stockStatus === 'in_stock' ? 'selected' : ''; ?>>In Stock (&gt;10)</option>
                <option value="low_stock" <?php echo $stockStatus === 'low_stock' ? 'selected' : ''; ?>>Low Stock (1-10)</option>
                <option value="out_of_stock" <?php echo $stockStatus === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock (0)</option>
            </select>

            <!-- Active Status Filter -->
            <select name="active_status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Statuses</option>
                <option value="active" <?php echo $activeStatus === 'active' ? 'selected' : ''; ?>>Active (Visible)</option>
                <option value="inactive" <?php echo $activeStatus === 'inactive' ? 'selected' : ''; ?>>Draft / Hidden</option>
            </select>

            <!-- Sort By -->
            <select name="sort" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                <option value="stock_asc" <?php echo $sort === 'stock_asc' ? 'selected' : ''; ?>>Stock: Low to High</option>
            </select>

            <?php if (!empty($search) || !empty($category) || !empty($stockStatus) || !empty($activeStatus)): ?>
                <a href="products.php" class="btn btn-outline btn-sm" style="height: 38px; padding: 0 12px;" title="Reset All Filters">
                    Reset
                </a>
            <?php endif; ?>
        </div>

        <!-- View Mode Switch -->
        <div style="display: flex; gap: 4px; background: var(--bg-subtle); padding: 3px; border-radius: 8px; border: 1px solid var(--border-color); flex-shrink: 0;">
            <button type="button" class="btn btn-sm <?php echo $viewMode === 'grid' ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 5px 10px; border: none;" onclick="setViewMode('grid')" title="Grid View">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </button>
            <button type="button" class="btn btn-sm <?php echo $viewMode === 'table' ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 5px 10px; border: none;" onclick="setViewMode('table')" title="Table View">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </form>
</div>


<!-- Products Content Area -->
<?php if (empty($products)): ?>
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 40px; margin-bottom: 12px; color: var(--gold-light);">🍫</div>
        <h3 style="font-family:'Cormorant Garamond',serif; font-size: 22px; margin-bottom: 8px;">No Products Found</h3>
        <p style="color: var(--text-light); max-width: 400px; margin: 0 auto 20px;">No chocolate or shop products match your filter criteria. Try adjusting your search or add a new handcrafted confection.</p>
        <a href="product-editor.php" class="btn btn-primary">Add New Product</a>
    </div>
<?php elseif ($viewMode === 'grid'): ?>
    <!-- GRID VIEW -->
    <div class="products-grid-view">
        <?php foreach ($products as $p): ?>
            <?php
                $hasDiscount = !empty($p['sale_price']) && $p['sale_price'] > 0 && $p['sale_price'] < $p['price'];
                $discountPercent = $hasDiscount ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
                $currentPrice = $hasDiscount ? $p['sale_price'] : $p['price'];
                $isOutOfStock = (int)$p['stock_quantity'] === 0;
                $isLowStock = (int)$p['stock_quantity'] > 0 && (int)$p['stock_quantity'] <= 10;
            ?>
            <div class="product-card" id="product-card-<?php echo $p['id']; ?>">
                <!-- Thumbnail with Badges -->
                <div class="product-card-thumb-wrap">
                    <?php if (!empty($p['image_main'])): ?>
                        <img src="../<?php echo htmlspecialchars($p['image_main']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="product-card-thumb" onerror="this.onerror=null;this.src='../assets/premium_chocolate.png';">
                    <?php else: ?>
                        <div class="product-card-thumb-placeholder">
                            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                    <?php endif; ?>

                    <!-- Top Left Badges -->
                    <div class="product-card-badges">
                        <span class="status-badge <?php echo $p['is_active'] ? 'active' : 'draft'; ?>" id="status-badge-<?php echo $p['id']; ?>">
                            <?php echo $p['is_active'] ? 'Active' : 'Draft'; ?>
                        </span>
                        <?php if ($p['is_featured']): ?>
                            <span class="status-badge featured" id="featured-badge-<?php echo $p['id']; ?>">★ Featured</span>
                        <?php endif; ?>
                    </div>

                    <!-- Bottom Right Stock Indicator -->
                    <div class="product-card-stock" style="<?php echo $isOutOfStock ? 'background:rgba(211,47,47,0.85);' : ($isLowStock ? 'background:rgba(239,108,0,0.85);' : ''); ?>">
                        <?php if ($isOutOfStock): ?>
                            Out of Stock
                        <?php elseif ($isLowStock): ?>
                            Low: <?php echo $p['stock_quantity']; ?> left
                        <?php else: ?>
                            <?php echo $p['stock_quantity']; ?> in stock
                        <?php endif; ?>
                    </div>

                    <!-- Bulk select checkbox in card -->
                    <div style="position: absolute; top: 12px; right: 12px; z-index: 2;">
                        <input type="checkbox" class="product-select-checkbox" value="<?php echo $p['id']; ?>" onchange="updateBulkBar()" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--gold-light);">
                    </div>
                </div>

                <!-- Body -->
                <div class="product-card-body">
                    <div class="product-card-category"><?php echo htmlspecialchars($p['category'] ?: 'Uncategorized'); ?></div>
                    <h3 class="product-card-title" title="<?php echo htmlspecialchars($p['name']); ?>">
                        <a href="product-editor.php?id=<?php echo $p['id']; ?>" style="color:inherit; text-decoration:none;">
                            <?php echo htmlspecialchars($p['name']); ?>
                        </a>
                    </h3>

                    <!-- Pricing -->
                    <div class="product-card-pricing">
                        <span class="product-price-current">₹<?php echo number_format($currentPrice, 2); ?></span>
                        <?php if ($hasDiscount): ?>
                            <span class="product-price-original">₹<?php echo number_format($p['price'], 2); ?></span>
                            <span class="discount-badge"><?php echo $discountPercent; ?>% OFF</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-card-desc">
                        <?php echo htmlspecialchars($p['short_description'] ?: 'No summary provided.'); ?>
                    </div>

                    <!-- Footer Actions -->
                    <div class="product-card-footer">
                        <div style="font-size: 11px; color: var(--text-light);">
                            ID: #<?php echo $p['id']; ?>
                        </div>
                        <div style="display: flex; gap: 8px; position: relative;">
                            <a href="../shop/<?php echo urlencode($p['slug']); ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Preview in Store">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            <a href="product-editor.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Edit Product">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                            <div style="position: relative;">
                                <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px;" onclick="toggleCardMenu(<?php echo $p['id']; ?>)">
                                    •••
                                </button>
                                <div class="more-actions-dropdown" id="menu-<?php echo $p['id']; ?>">
                                    <button type="button" onclick="toggleActive(<?php echo $p['id']; ?>)">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                                        <span id="toggle-active-text-<?php echo $p['id']; ?>"><?php echo $p['is_active'] ? 'Deactivate' : 'Activate'; ?></span>
                                    </button>
                                    <button type="button" onclick="toggleFeatured(<?php echo $p['id']; ?>)">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                        <span id="toggle-featured-text-<?php echo $p['id']; ?>"><?php echo $p['is_featured'] ? 'Unfeature' : 'Feature'; ?></span>
                                    </button>
                                    <button type="button" onclick="duplicateProduct(<?php echo $p['id']; ?>)">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        Duplicate
                                    </button>
                                    <button type="button" class="delete-btn" onclick="confirmDelete(<?php echo $p['id']; ?>, <?php echo htmlspecialchars(json_encode($p['name'])); ?>)">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- TABLE VIEW -->
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 32px;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()" style="cursor: pointer; accent-color: var(--gold-light);">
                        </th>
                        <th style="width: 60px;">Image</th>
                        <th>Product Name & Slug</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Status</th>
                        <th>Active</th>
                        <th>Featured</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <?php
                            $hasDiscount = !empty($p['sale_price']) && $p['sale_price'] > 0 && $p['sale_price'] < $p['price'];
                            $currentPrice = $hasDiscount ? $p['sale_price'] : $p['price'];
                            $isOutOfStock = (int)$p['stock_quantity'] === 0;
                            $isLowStock = (int)$p['stock_quantity'] > 0 && (int)$p['stock_quantity'] <= 10;
                        ?>
                        <tr id="product-row-<?php echo $p['id']; ?>">
                            <td style="text-align: center;">
                                <input type="checkbox" class="product-select-checkbox" value="<?php echo $p['id']; ?>" onchange="updateBulkBar()" style="cursor: pointer; accent-color: var(--gold-light);">
                            </td>
                            <td>
                                <div style="width: 46px; height: 46px; border-radius: 8px; overflow: hidden; background: var(--bg-subtle);">
                                    <?php if (!empty($p['image_main'])): ?>
                                        <img src="../<?php echo htmlspecialchars($p['image_main']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='../assets/premium_chocolate.png';">
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-light);">🍫</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="product-editor.php?id=<?php echo $p['id']; ?>" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </a>
                                <div style="font-size: 11px; color: var(--text-light); margin-top: 2px;">
                                    /shop/<?php echo htmlspecialchars($p['slug']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge" style="background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color); font-size: 11px;">
                                    <?php echo htmlspecialchars($p['category'] ?: 'Uncategorized'); ?>
                                </span>
                            </td>
                            <td>
                                <strong>₹<?php echo number_format($currentPrice, 2); ?></strong>
                                <?php if ($hasDiscount): ?>
                                    <div style="font-size: 11px; color: var(--text-light); text-decoration: line-through;">₹<?php echo number_format($p['price'], 2); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isOutOfStock): ?>
                                    <span class="status-badge out-of-stock">Out of Stock (0)</span>
                                <?php elseif ($isLowStock): ?>
                                    <span class="status-badge low-stock">Low: <?php echo $p['stock_quantity']; ?> left</span>
                                <?php else: ?>
                                    <span class="status-badge active"><?php echo $p['stock_quantity']; ?> in stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="status-badge <?php echo $p['is_active'] ? 'active' : 'draft'; ?>" id="table-status-<?php echo $p['id']; ?>" onclick="toggleActive(<?php echo $p['id']; ?>)" style="cursor: pointer; border: none;">
                                    <?php echo $p['is_active'] ? 'Active' : 'Draft'; ?>
                                </button>
                            </td>
                            <td>
                                <button type="button" class="status-badge <?php echo $p['is_featured'] ? 'featured' : 'read'; ?>" id="table-featured-<?php echo $p['id']; ?>" onclick="toggleFeatured(<?php echo $p['id']; ?>)" style="cursor: pointer; border: none;">
                                    <?php echo $p['is_featured'] ? '★ Featured' : 'Standard'; ?>
                                </button>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    <a href="../shop/<?php echo urlencode($p['slug']); ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Storefront Preview">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    <a href="product-editor.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Edit Product">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button type="button" class="btn btn-outline btn-sm" style="padding: 4px 8px;" onclick="duplicateProduct(<?php echo $p['id']; ?>)" title="Duplicate">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" style="padding: 4px 8px;" onclick="confirmDelete(<?php echo $p['id']; ?>, <?php echo htmlspecialchars(json_encode($p['name'])); ?>)" title="Delete">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

<!-- Pagination Controls -->
<?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 13px; color: var(--text-light);">
            Showing <?php echo number_format($offset + 1); ?>–<?php echo number_format(min($totalFiltered, $offset + $limit)); ?> of <?php echo number_format($totalFiltered); ?> products
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

<!-- Delete Confirmation Modal -->
<div class="order-modal-backdrop" id="deleteModal">
    <div class="order-modal-content" style="max-width: 440px;">
        <div class="order-modal-header">
            <h3 style="font-family:'Cormorant Garamond',serif; font-size: 20px; margin: 0; color: #d32f2f;">Confirm Delete</h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeDeleteModal()" style="padding: 4px 8px;">✕</button>
        </div>
        <div class="order-modal-body">
            <p style="margin-bottom: 8px;">Are you sure you want to permanently delete the following product?</p>
            <div id="deleteProductName" style="font-weight: 600; margin-bottom: 14px; padding: 10px; background: var(--bg-subtle); border-radius: 8px; color: var(--text-main);"></div>
            <p style="font-size: 12px; color: #d32f2f; margin: 0;">⚠️ This action cannot be undone. Its inventory records and image will be removed.</p>
        </div>
        <div class="order-modal-footer">
            <form action="products.php" method="POST" id="deleteForm" style="display: flex; gap: 10px;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" id="deleteProductId" value="">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm">Yes, Delete Product</button>
            </form>
        </div>
    </div>
</div>

<script>
    const csrfToken = <?php echo json_encode($csrfToken); ?>;

    // View mode switch with Cookie persistence
    function setViewMode(mode) {
        document.cookie = "products_view_pref=" + mode + ";path=/;max-age=31536000";
        window.location.reload();
    }

    // Toggle card dropdown menu
    function toggleCardMenu(id) {
        const menu = document.getElementById('menu-' + id);
        const isOpen = menu.style.display === 'block';
        document.querySelectorAll('.more-actions-dropdown').forEach(d => d.style.display = 'none');
        if (!isOpen) {
            menu.style.display = 'block';
        }
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.more-actions-dropdown') && !e.target.closest('button[onclick^="toggleCardMenu"]')) {
            document.querySelectorAll('.more-actions-dropdown').forEach(d => d.style.display = 'none');
        }
    });

    // AJAX Toggle Active
    function toggleActive(id) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'toggle_active');
        formData.append('product_id', id);
        formData.append('is_ajax', '1');

        fetch('products.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Update Card Badge
                const cardBadge = document.getElementById('status-badge-' + id);
                if (cardBadge) {
                    cardBadge.className = 'status-badge ' + (data.is_active ? 'active' : 'draft');
                    cardBadge.textContent = data.is_active ? 'Active' : 'Draft';
                }
                // Update Table Badge
                const tableBadge = document.getElementById('table-status-' + id);
                if (tableBadge) {
                    tableBadge.className = 'status-badge ' + (data.is_active ? 'active' : 'draft');
                    tableBadge.textContent = data.is_active ? 'Active' : 'Draft';
                }
                // Update Dropdown Text
                const dropText = document.getElementById('toggle-active-text-' + id);
                if (dropText) {
                    dropText.textContent = data.is_active ? 'Deactivate' : 'Activate';
                }
            } else {
                showToast(data.message || 'Error updating status', 'danger');
            }
        })
        .catch(err => {
            showToast('Network error updating status.', 'danger');
        });
    }

    // AJAX Toggle Featured
    function toggleFeatured(id) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'toggle_featured');
        formData.append('product_id', id);
        formData.append('is_ajax', '1');

        fetch('products.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Card Badge
                let cardFeatured = document.getElementById('featured-badge-' + id);
                if (data.is_featured) {
                    if (!cardFeatured) {
                        const badgesWrap = document.querySelector('#product-card-' + id + ' .product-card-badges');
                        if (badgesWrap) {
                            cardFeatured = document.createElement('span');
                            cardFeatured.id = 'featured-badge-' + id;
                            cardFeatured.className = 'status-badge featured';
                            cardFeatured.textContent = '★ Featured';
                            badgesWrap.appendChild(cardFeatured);
                        }
                    }
                } else if (cardFeatured) {
                    cardFeatured.remove();
                }

                // Table Badge
                const tableFeatured = document.getElementById('table-featured-' + id);
                if (tableFeatured) {
                    tableFeatured.className = 'status-badge ' + (data.is_featured ? 'featured' : 'read');
                    tableFeatured.textContent = data.is_featured ? '★ Featured' : 'Standard';
                }

                // Dropdown Text
                const dropText = document.getElementById('toggle-featured-text-' + id);
                if (dropText) {
                    dropText.textContent = data.is_featured ? 'Unfeature' : 'Feature';
                }
            } else {
                showToast(data.message || 'Error updating featured state', 'danger');
            }
        })
        .catch(err => {
            showToast('Network error updating product.', 'danger');
        });
    }

    // Duplicate Product
    function duplicateProduct(id) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'duplicate_product');
        formData.append('product_id', id);
        formData.append('is_ajax', '1');

        fetch('products.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Duplication failed', 'danger');
            }
        })
        .catch(() => showToast('Error duplicating product', 'danger'));
    }

    // Delete Modal Handling
    function confirmDelete(id, name) {
        document.getElementById('deleteProductId').value = id;
        document.getElementById('deleteProductName').textContent = name;
        document.getElementById('deleteModal').classList.add('open');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('open');
    }

    // Bulk selection management
    function updateBulkBar() {
        const checkboxes = document.querySelectorAll('.product-select-checkbox:checked');
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

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.product-select-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkBar();
    }

    function clearBulkSelection() {
        document.querySelectorAll('.product-select-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
        updateBulkBar();
    }

    function submitBulk(type) {
        const checked = document.querySelectorAll('.product-select-checkbox:checked');
        if (checked.length === 0) return;

        if (type === 'bulk_delete' && !confirm('Are you sure you want to delete these ' + checked.length + ' products? This cannot be undone.')) {
            return;
        }

        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulkActionType').value = type;
        document.getElementById('bulkForm').submit();
    }

    // ==========================================
    // Category Manager Modal & AJAX Handler
    // ==========================================
    let cachedModalCategories = [];

    function openCategoryManagerModal() {
        document.getElementById('categoryManagerModal').classList.add('open');
        loadModalCategories();
    }

    function closeCategoryManagerModal() {
        document.getElementById('categoryManagerModal').classList.remove('open');
    }

    function loadModalCategories() {
        const list = document.getElementById('modalCategoriesList');
        list.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--text-light);">Loading collections...</div>';

        fetch('category-api.php?action=get_all')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    cachedModalCategories = data.categories;
                    renderModalCategoriesList(cachedModalCategories);
                } else {
                    list.innerHTML = '<div style="padding: 20px; text-align: center; color: #d32f2f;">Failed to load categories.</div>';
                }
            })
            .catch(() => {
                list.innerHTML = '<div style="padding: 20px; text-align: center; color: #d32f2f;">Connection error loading categories.</div>';
            });
    }

    function renderModalCategoriesList(cats) {
        const list = document.getElementById('modalCategoriesList');
        if (!cats || cats.length === 0) {
            list.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-light);">No categories found. Add one above!</div>';
            return;
        }

        let html = '<div style="display: flex; flex-direction: column; divide-y: 1px solid var(--border-color);">';
        cats.forEach(c => {
            html += `
                <div class="cat-modal-item" data-name="${(c.name || '').toLowerCase()}" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid var(--border-color); background: var(--bg-card);">
                    <div>
                        <span style="font-weight: 600; font-size: 13.5px; color: var(--text-main);">${escapeHtml(c.name)}</span>
                        <span style="font-size: 11px; color: var(--text-light); margin-left: 6px; font-family: monospace;">/${escapeHtml(c.slug)}</span>
                        <span class="status-badge" style="background: var(--bg-subtle); color: var(--gold-light); font-size: 10.5px; margin-left: 8px; padding: 2px 7px;">${c.product_count} items</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-outline btn-sm" style="padding: 3px 8px; font-size: 11.5px;" onclick="promptRenameCategory(${c.id}, '${escapeHtml(c.name)}')">Rename</button>
                        <button type="button" class="btn btn-danger btn-sm" style="padding: 3px 8px; font-size: 11.5px;" onclick="promptDeleteCategory(${c.id}, '${escapeHtml(c.name)}', ${c.product_count})">Delete</button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        list.innerHTML = html;
    }

    function filterModalCategories(q) {
        const query = q.trim().toLowerCase();
        document.querySelectorAll('.cat-modal-item').forEach(item => {
            const name = item.getAttribute('data-name') || '';
            item.style.display = (!query || name.includes(query)) ? 'flex' : 'none';
        });
    }

    function submitCreateCategory() {
        const input = document.getElementById('quickCatName');
        const name = input.value.trim();
        if (!name) {
            showToast('Please enter a category name.', 'danger');
            return;
        }

        const fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('action', 'create');
        fd.append('name', name);

        fetch('category-api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    input.value = '';
                    loadModalCategories();
                    refreshPagePills();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(() => showToast('Error creating category.', 'danger'));
    }

    function promptRenameCategory(id, currentName) {
        const newName = prompt('Enter new category name for "' + currentName + '":', currentName);
        if (!newName || newName.trim() === '' || newName.trim() === currentName) return;

        const fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('action', 'rename');
        fd.append('id', id);
        fd.append('name', newName.trim());

        fetch('category-api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadModalCategories();
                    refreshPagePills();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(() => showToast('Error renaming category.', 'danger'));
    }

    function promptDeleteCategory(id, name, count) {
        const warning = count > 0 
            ? `Category "${name}" has ${count} product(s). Deleting will reassign them to "Uncategorized". Proceed?`
            : `Are you sure you want to delete category "${name}"?`;

        if (!confirm(warning)) return;

        const fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('action', 'delete');
        fd.append('id', id);

        fetch('category-api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadModalCategories();
                    refreshPagePills();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(() => showToast('Error deleting category.', 'danger'));
    }

    function refreshPagePills() {
        // Refresh categories on page when closed or updated
        fetch('category-api.php?action=get_all')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const pillsBar = document.getElementById('categoryPillsBar');
                    if (pillsBar) {
                        let html = `
                            <a href="products.php" class="cat-pill active">
                                <span>All Products</span>
                                <span class="cat-pill-count">${document.getElementById('selectAllCheckbox') ? '<?php echo $metrics['total']; ?>' : ''}</span>
                            </a>
                        `;
                        data.categories.forEach(c => {
                            if (c.product_count > 0) {
                                html += `
                                    <a href="products.php?category=${encodeURIComponent(c.name)}" class="cat-pill">
                                        <span>${escapeHtml(c.name)}</span>
                                        <span class="cat-pill-count">${c.product_count}</span>
                                    </a>
                                `;
                            }
                        });
                        html += `
                            <button type="button" class="cat-pill" onclick="openCategoryManagerModal()" style="border-style: dashed; cursor: pointer;" title="Manage Categories">
                                <span>⚙️ Manage</span>
                            </button>
                        `;
                        pillsBar.innerHTML = html;
                    }
                }
            });
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
    }
</script>

<!-- Category Manager Modal Backdrop -->
<div class="order-modal-backdrop" id="categoryManagerModal">
    <div class="order-modal-content" style="max-width: 620px;">
        <div class="order-modal-header">
            <div>
                <h3 style="font-family:'Cormorant Garamond',serif; font-size: 24px; margin: 0;">Manage Product Categories</h3>
                <div style="font-size: 12px; color: var(--text-light);">Create new collections, rename tags, or remove obsolete categories</div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeCategoryManagerModal()">✕</button>
        </div>

        <div style="padding: 20px;">
            <!-- Quick Create Category Input -->
            <div style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: var(--text-main);">+ Quick Create Category</div>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="quickCatName" class="form-control" placeholder="e.g. Artisanal Truffles, Vegan Bars..." style="flex: 1; height: 38px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="submitCreateCategory()" style="white-space: nowrap;">
                        Add Category
                    </button>
                </div>
            </div>

            <!-- Category Search Filter for when there are many categories -->
            <div style="position: relative; margin-bottom: 12px;">
                <svg style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-light); width: 14px; height: 14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="catModalSearch" placeholder="Search categories..." oninput="filterModalCategories(this.value)" class="form-control" style="padding-left: 32px; height: 36px; font-size: 12.5px;">
            </div>

            <!-- Categories List Container -->
            <div style="max-height: 280px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: 8px;" id="modalCategoriesList">
                <div style="padding: 20px; text-align: center; color: var(--text-light);">Loading categories...</div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                <a href="categories.php" class="btn btn-outline btn-sm" style="font-size: 12px;">Open Full Categories Hub ↗</a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCategoryManagerModal()">Done</button>
            </div>
        </div>
    </div>
</div>


<?php
render_admin_footer();
?>
