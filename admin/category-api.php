<?php
// admin/category-api.php - AJAX API for Category Management
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_auth();

header('Content-Type: application/json');

$pdo = get_db();
$action = $_REQUEST['action'] ?? '';

try {
    // 1. GET ALL CATEGORIES
    if ($action === 'get_all') {
        $sql = "SELECT pc.id, pc.name, pc.slug, pc.description, pc.created_at,
                (SELECT COUNT(*) FROM products p WHERE p.category = pc.name) as product_count
                FROM product_categories pc
                ORDER BY pc.name ASC";
        $categories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Also check if there are any products with categories not in product_categories
        $extraSql = "SELECT DISTINCT category FROM products 
                     WHERE category IS NOT NULL AND category != '' 
                     AND category NOT IN (SELECT name FROM product_categories)";
        $extras = $pdo->query($extraSql)->fetchAll(PDO::FETCH_COLUMN);

        // Auto-seed any missing ones into product_categories
        if (!empty($extras)) {
            $seedStmt = $pdo->prepare("INSERT IGNORE INTO product_categories (name, slug) VALUES (?, ?)");
            foreach ($extras as $extra) {
                $extra = trim($extra);
                if ($extra !== '') {
                    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $extra), '-'));
                    $seedStmt->execute([$extra, $slug]);
                }
            }
            // Re-fetch
            $categories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        exit;
    }

    // CSRF verification for state-changing operations
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!verify_csrf($csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
            exit;
        }

        // 2. CREATE CATEGORY
        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            if (empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Category name is required.']);
                exit;
            }

            // Generate clean slug
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
            if (empty($slug)) {
                $slug = 'category-' . time();
            }

            // Check if name already exists
            $checkStmt = $pdo->prepare("SELECT id FROM product_categories WHERE LOWER(name) = LOWER(?) OR slug = ?");
            $checkStmt->execute([$name, $slug]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => "Category '{$name}' already exists."]);
                exit;
            }

            $insertStmt = $pdo->prepare("INSERT INTO product_categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $insertStmt->execute([$name, $slug, $desc]);
            $newId = (int)$pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => "Category '{$name}' created successfully!",
                'category' => [
                    'id' => $newId,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $desc,
                    'product_count' => 0
                ]
            ]);
            exit;
        }

        // 3. RENAME / EDIT CATEGORY
        elseif ($action === 'rename') {
            $id = (int)($_POST['id'] ?? 0);
            $newName = trim($_POST['name'] ?? '');
            $newDesc = trim($_POST['description'] ?? '');

            if ($id <= 0 || empty($newName)) {
                echo json_encode(['success' => false, 'message' => 'Valid category ID and name are required.']);
                exit;
            }

            // Fetch old name
            $getStmt = $pdo->prepare("SELECT name FROM product_categories WHERE id = ?");
            $getStmt->execute([$id]);
            $oldName = $getStmt->fetchColumn();

            if (!$oldName) {
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
                exit;
            }

            // Check unique name
            $checkStmt = $pdo->prepare("SELECT id FROM product_categories WHERE LOWER(name) = LOWER(?) AND id != ?");
            $checkStmt->execute([$newName, $id]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => "Another category with the name '{$newName}' already exists."]);
                exit;
            }

            $newSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $newName), '-'));

            // Update category record
            $updateCat = $pdo->prepare("UPDATE product_categories SET name = ?, slug = ?, description = ?, updated_at = NOW() WHERE id = ?");
            $updateCat->execute([$newName, $newSlug, $newDesc, $id]);

            // Synchronize products table
            $updateProds = $pdo->prepare("UPDATE products SET category = ? WHERE category = ?");
            $updateProds->execute([$newName, $oldName]);
            $affected = $updateProds->rowCount();

            echo json_encode([
                'success' => true,
                'message' => "Category renamed to '{$newName}'. ($affected product(s) updated).",
                'category' => [
                    'id' => $id,
                    'name' => $newName,
                    'slug' => $newSlug,
                    'description' => $newDesc
                ]
            ]);
            exit;
        }

        // 4. DELETE CATEGORY
        elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $reassignTo = trim($_POST['reassign_to'] ?? 'Uncategorized');

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid category ID.']);
                exit;
            }

            // Fetch name
            $getStmt = $pdo->prepare("SELECT name FROM product_categories WHERE id = ?");
            $getStmt->execute([$id]);
            $catName = $getStmt->fetchColumn();

            if (!$catName) {
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
                exit;
            }

            // Delete from product_categories
            $delStmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
            $delStmt->execute([$id]);

            // Reassign affected products safely
            $reassignStmt = $pdo->prepare("UPDATE products SET category = ? WHERE category = ?");
            $reassignStmt->execute([$reassignTo, $catName]);
            $reassignedCount = $reassignStmt->rowCount();

            echo json_encode([
                'success' => true,
                'message' => "Category '{$catName}' deleted successfully. ({$reassignedCount} product(s) reassigned to '{$reassignTo}')."
            ]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
