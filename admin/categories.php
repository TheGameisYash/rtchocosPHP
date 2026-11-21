<?php
// admin/categories.php - Full Category Management Hub
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$csrfToken = generate_csrf();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// Handle Direct POST submissions (fallback for non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $error = 'Invalid security token. Please refresh.';
    } else {
        $action = $_POST['action'] ?? '';

        // Create
        if ($action === 'create_category') {
            $name = trim($_POST['name'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            if (!empty($name)) {
                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
                $check = $pdo->prepare("SELECT id FROM product_categories WHERE LOWER(name) = LOWER(?) OR slug = ?");
                $check->execute([$name, $slug]);
                if ($check->fetch()) {
                    $error = "Category '{$name}' already exists.";
                } else {
                    $ins = $pdo->prepare("INSERT INTO product_categories (name, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $ins->execute([$name, $slug, $desc]);
                    $success = "Category '{$name}' created successfully!";
                }
            } else {
                $error = "Category name cannot be empty.";
            }
        }
        // Rename
        elseif ($action === 'rename_category') {
            $id = (int)($_POST['category_id'] ?? 0);
            $newName = trim($_POST['name'] ?? '');
            $newDesc = trim($_POST['description'] ?? '');

            if ($id > 0 && !empty($newName)) {
                $getOld = $pdo->prepare("SELECT name FROM product_categories WHERE id = ?");
                $getOld->execute([$id]);
                $oldName = $getOld->fetchColumn();

                if ($oldName) {
                    $newSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $newName), '-'));
                    $upd = $pdo->prepare("UPDATE product_categories SET name = ?, slug = ?, description = ?, updated_at = NOW() WHERE id = ?");
                    $upd->execute([$newName, $newSlug, $newDesc, $id]);

                    // Sync products
                    $updProd = $pdo->prepare("UPDATE products SET category = ? WHERE category = ?");
                    $updProd->execute([$newName, $oldName]);

                    $success = "Category updated to '{$newName}'.";
                }
            }
        }
        // Delete
        elseif ($action === 'delete_category') {
            $id = (int)($_POST['category_id'] ?? 0);
            $reassign = trim($_POST['reassign_to'] ?? 'Uncategorized');

            if ($id > 0) {
                $getOld = $pdo->prepare("SELECT name FROM product_categories WHERE id = ?");
                $getOld->execute([$id]);
                $oldName = $getOld->fetchColumn();

                if ($oldName) {
                    $del = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
                    $del->execute([$id]);

                    // Reassign products
                    $reassignStmt = $pdo->prepare("UPDATE products SET category = ? WHERE category = ?");
                    $reassignStmt->execute([$reassign, $oldName]);

                    $success = "Category '{$oldName}' removed. Products moved to '{$reassign}'.";
                }
            }
        }
    }
}

// Fetch categories with product counts
try {
    $catSql = "SELECT pc.id, pc.name, pc.slug, pc.description, pc.created_at,
               COUNT(p.id) as product_count,
               SUM(CASE WHEN p.is_active = 1 THEN 1 ELSE 0 END) as active_count
               FROM product_categories pc
               LEFT JOIN products p ON p.category = pc.name
               GROUP BY pc.id, pc.name, pc.slug, pc.description, pc.created_at
               ORDER BY pc.name ASC";
    $categories = $pdo->query($catSql)->fetchAll(PDO::FETCH_ASSOC);

    // KPI Metrics
    $totalCategories = count($categories);
    $totalAssignedProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE category IS NOT NULL AND category != '' AND category != 'Uncategorized'")->fetchColumn();
    $uncategorizedCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE category IS NULL OR category = '' OR category = 'Uncategorized'")->fetchColumn();
    $activeLinesCount = 0;
    foreach ($categories as $cat) {
        if (!empty($cat['active_count']) && $cat['active_count'] > 0) {
            $activeLinesCount++;
        }
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

render_admin_header("Product Categories", "categories");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Primary Action Bar (Clean Single Header) -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <p style="color: var(--text-light); margin: 0; font-size: 14px;">Organize chocolates into collections, manage gift lines, and edit storefront filters</p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="products.php" class="btn btn-outline btn-sm" title="View Catalog">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            Products Catalog
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="focusCategoryInput()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            New Category
        </button>
    </div>
</div>

<!-- KPI Metrics Ribbon -->
<div class="metrics-grid" style="margin-bottom: 24px;">
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">TOTAL CATEGORIES</div>
            <div class="metric-value"><?php echo number_format($totalCategories); ?></div>
            <div class="metric-subtitle">Active collections</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">CLASSIFIED ITEMS</div>
            <div class="metric-value" style="color: #2e7d32;"><?php echo number_format($totalAssignedProducts); ?></div>
            <div class="metric-subtitle">Assigned products</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">ACTIVE LINES</div>
            <div class="metric-value" style="color: var(--gold-light);"><?php echo number_format($activeLinesCount); ?></div>
            <div class="metric-subtitle">Visible on storefront</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">UNCATEGORIZED</div>
            <div class="metric-value" style="color: <?php echo $uncategorizedCount > 0 ? '#ef6c00' : 'var(--text-main)'; ?>;">
                <?php echo number_format($uncategorizedCount); ?>
            </div>
            <div class="metric-subtitle">Unassigned products</div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>
</div>

<!-- Two Column Layout: Quick Create + Category Table -->
<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">

    <!-- Quick Create Card -->
    <div class="form-card" style="margin-bottom: 0;">
        <div class="editor-title" style="margin-bottom: 4px;">Add New Category</div>
        <div style="font-size: 12px; color: var(--text-light); margin-bottom: 16px;">Create a product line or seasonal chocolate collection</div>

        <form action="categories.php" method="POST" id="createCategoryForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="create_category">

            <div class="form-group">
                <label class="form-label" for="newCategoryName">Category Name *</label>
                <input type="text" id="newCategoryName" name="name" class="form-control" placeholder="e.g. Vegan Truffles, Holiday Hampers" required oninput="previewSlug(this.value)">
            </div>

            <div class="form-group">
                <label class="form-label">Auto-Generated URL Slug</label>
                <div style="font-size: 12px; color: var(--text-light); background: var(--bg-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-family: monospace;">
                    /shop?category=<span id="slugPreviewText" style="color: var(--gold-light);">category-slug</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="newCategoryDesc">Description (Optional)</label>
                <textarea id="newCategoryDesc" name="description" class="form-control" rows="3" placeholder="Brief summary of what confections belong in this collection..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 42px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                Create Category
            </button>
        </form>
    </div>

    <!-- Category List & Search Area -->
    <div>
        <!-- Search Filter Toolbar -->
        <div class="filter-toolbar" style="margin-bottom: 16px;">
            <div class="filter-search-box" style="flex: 1;">
                <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); width: 16px; height: 16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" id="categorySearchInput" placeholder="Filter categories by name, slug, description..." oninput="filterCategoryTable(this.value)">
            </div>
            <div style="font-size: 12.5px; color: var(--text-light); white-space: nowrap;">
                <span id="displayedCategoryCount"><?php echo count($categories); ?></span> categories listed
            </div>
        </div>

        <!-- Categories Table Card -->
        <div class="admin-table-card">
            <div class="table-responsive">
                <table class="admin-data-table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th>Category Name & Slug</th>
                            <th>Description</th>
                            <th style="text-align: center;">Products</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    No categories found. Create your first chocolate collection using the form on the left!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr id="category-row-<?php echo $cat['id']; ?>" class="category-table-row" data-name="<?php echo strtolower(htmlspecialchars($cat['name'])); ?>" data-desc="<?php echo strtolower(htmlspecialchars($cat['description'] ?? '')); ?>">
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </div>
                                        <div style="font-size: 11.5px; color: var(--text-light); font-family: monospace; margin-top: 2px;">
                                            /<?php echo htmlspecialchars($cat['slug']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 12.5px; color: var(--text-light); max-width: 280px; line-height: 1.4;">
                                            <?php echo htmlspecialchars($cat['description'] ?: '—'); ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="products.php?category=<?php echo urlencode($cat['name']); ?>" class="status-badge" style="background: var(--bg-subtle); color: var(--gold-light); text-decoration: none; border: 1px solid var(--border-color); font-weight: 600;" title="View all products in <?php echo htmlspecialchars($cat['name']); ?>">
                                            <?php echo (int)$cat['product_count']; ?> item<?php echo (int)$cat['product_count'] !== 1 ? 's' : ''; ?>
                                        </a>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ((int)$cat['active_count'] > 0): ?>
                                            <span class="status-badge active" style="font-size: 11px;">Active on Store</span>
                                        <?php else: ?>
                                            <span class="status-badge" style="background: var(--bg-subtle); color: var(--text-light); border: 1px solid var(--border-color); font-size: 11px;">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                            <button type="button" class="btn btn-outline btn-sm" onclick="openRenameModal(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat['description'] ?? '', ENT_QUOTES); ?>')" title="Rename / Edit Category">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>', <?php echo (int)$cat['product_count']; ?>)" title="Delete Category">
                                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Rename / Edit Category Modal -->
<div class="order-modal-backdrop" id="renameCategoryModal">
    <div class="order-modal-content" style="max-width: 480px;">
        <div class="order-modal-header">
            <div>
                <h3 style="font-family:'Cormorant Garamond',serif; font-size: 22px; margin: 0;">Edit Category</h3>
                <div style="font-size: 12px; color: var(--text-light);">Updates the category and all associated product tags</div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeRenameModal()">✕</button>
        </div>
        <form action="categories.php" method="POST" id="renameForm" style="padding: 20px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="rename_category">
            <input type="hidden" name="category_id" id="editCategoryId" value="">

            <div class="form-group">
                <label class="form-label" for="editCategoryName">Category Name *</label>
                <input type="text" id="editCategoryName" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="editCategoryDesc">Description (Optional)</label>
                <textarea id="editCategoryDesc" name="description" class="form-control" rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeRenameModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="order-modal-backdrop" id="deleteCategoryModal">
    <div class="order-modal-content" style="max-width: 480px;">
        <div class="order-modal-header">
            <div>
                <h3 style="font-family:'Cormorant Garamond',serif; font-size: 22px; margin: 0; color: #d32f2f;">Delete Category</h3>
                <div style="font-size: 12px; color: var(--text-light);">Safe product reassignment</div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDeleteModal()">✕</button>
        </div>
        <form action="categories.php" method="POST" id="deleteForm" style="padding: 20px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="category_id" id="deleteCategoryId" value="">

            <p style="font-size: 14px; color: var(--text-main); margin-bottom: 16px;" id="deleteCategoryPromptText">
                Are you sure you want to delete this category?
            </p>

            <div class="form-group">
                <label class="form-label" for="reassignCategorySelect">Reassign Existing Products To:</label>
                <select name="reassign_to" id="reassignCategorySelect" class="form-control">
                    <option value="Uncategorized">Uncategorized</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div style="font-size: 11px; color: var(--text-light); margin-top: 4px;">Existing products won't be deleted, only reassigned.</div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    function focusCategoryInput() {
        const input = document.getElementById('newCategoryName');
        input.focus();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function previewSlug(val) {
        const clean = val.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
        document.getElementById('slugPreviewText').textContent = clean || 'category-slug';
    }

    function filterCategoryTable(query) {
        const q = query.trim().toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('.category-table-row').forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const desc = row.getAttribute('data-desc') || '';
            if (!q || name.includes(q) || desc.includes(q)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        document.getElementById('displayedCategoryCount').textContent = visibleCount;
    }

    // Rename Modal
    function openRenameModal(id, name, desc) {
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        document.getElementById('editCategoryDesc').value = desc;
        document.getElementById('renameCategoryModal').classList.add('open');
    }

    function closeRenameModal() {
        document.getElementById('renameCategoryModal').classList.remove('open');
    }

    // Delete Modal
    function confirmDeleteCategory(id, name, count) {
        document.getElementById('deleteCategoryId').value = id;
        const prompt = count > 0 
            ? `Category "<strong>${name}</strong>" currently has <strong>${count}</strong> product(s) assigned. Deleting it will reassign them.`
            : `Are you sure you want to delete the category "<strong>${name}</strong>"?`;
        document.getElementById('deleteCategoryPromptText').innerHTML = prompt;

        // Hide current category from reassign select
        const sel = document.getElementById('reassignCategorySelect');
        Array.from(sel.options).forEach(opt => {
            opt.style.display = opt.value === name ? 'none' : '';
        });
        sel.value = 'Uncategorized';

        document.getElementById('deleteCategoryModal').classList.add('open');
    }

    function closeDeleteModal() {
        document.getElementById('deleteCategoryModal').classList.remove('open');
    }

    // Close on backdrop click
    document.querySelectorAll('.order-modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('open');
            }
        });
    });
</script>
