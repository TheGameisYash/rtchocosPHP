<?php
// admin/product-editor.php - Create / Edit E-Commerce Products
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

$productId = (int)($_GET['id'] ?? 0);
$isEdit = $productId > 0;

// Default empty values
$product = [
    'id' => 0,
    'name' => '',
    'slug' => '',
    'category' => 'Chocolates',
    'price' => '',
    'sale_price' => '',
    'stock_quantity' => 50,
    'short_description' => '',
    'long_description' => '',
    'image_main' => '',
    'image_gallery' => '[]',
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'is_active' => 1,
    'is_featured' => 0
];

// If edit, fetch from database
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fetched) {
            header('Location: products.php?error=' . urlencode('Product not found.'));
            exit;
        }
        $product = $fetched;
    } catch (Exception $e) {
        $error = 'Error fetching product: ' . $e->getMessage();
    }
}

// Fetch all existing categories for suggestions
$categories = [];
try {
    $categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

// Fetch recent media items for media picker modal
$mediaItems = [];
try {
    $mediaItems = $pdo->query("SELECT id, filename, path FROM media ORDER BY id DESC LIMIT 40")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? 'Chocolates');
    $price = trim($_POST['price'] ?? '');
    $salePrice = trim($_POST['sale_price'] ?? '');
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $shortDesc = trim($_POST['short_description'] ?? '');
    $longDesc = trim($_POST['long_description'] ?? '');
    $metaTitle = trim($_POST['meta_title'] ?? '');
    $metaDesc = trim($_POST['meta_description'] ?? '');
    $metaKeywords = trim($_POST['meta_keywords'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $imageMainExisting = trim($_POST['image_main_existing'] ?? '');
    $galleryJson = trim($_POST['image_gallery_json'] ?? '[]');

    // Retain state for form redisplay if error occurs
    $product['name'] = $name;
    $product['slug'] = $slug;
    $product['category'] = $category;
    $product['price'] = $price;
    $product['sale_price'] = $salePrice;
    $product['stock_quantity'] = $stockQuantity;
    $product['short_description'] = $shortDesc;
    $product['long_description'] = $longDesc;
    $product['meta_title'] = $metaTitle;
    $product['meta_description'] = $metaDesc;
    $product['meta_keywords'] = $metaKeywords;
    $product['is_active'] = $isActive;
    $product['is_featured'] = $isFeatured;
    $product['image_main'] = $imageMainExisting;
    $product['image_gallery'] = $galleryJson;

    if (!verify_csrf($csrfToken)) {
        $error = 'Invalid security token. Please try again.';
    } elseif (empty($name) || empty($slug)) {
        $error = 'Product name and URL slug are required.';
    } elseif (!is_numeric($price) || (float)$price <= 0) {
        $error = 'Please enter a valid regular price greater than 0.';
    } elseif (!empty($salePrice) && (!is_numeric($salePrice) || (float)$salePrice < 0)) {
        $error = 'Sale price must be a valid positive number.';
    } else {
        // Sanitize slug
        $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($slug)));
        $slug = preg_replace('/-+/', '-', trim($slug, '-'));
        $product['slug'] = $slug;

        // Check slug uniqueness
        try {
            $slugCheckSql = $isEdit ? "SELECT id FROM products WHERE slug = ? AND id != ?" : "SELECT id FROM products WHERE slug = ?";
            $slugCheckParams = $isEdit ? [$slug, $productId] : [$slug];
            $stmt = $pdo->prepare($slugCheckSql);
            $stmt->execute($slugCheckParams);

            if ($stmt->fetch()) {
                $error = "The URL slug '{$slug}' is already in use by another product. Slugs must be unique.";
            } else {
                $uploadDir = __DIR__ . '/../assets/products';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $imagePath = $imageMainExisting;
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB

                // 1. Process Main Image Upload (if uploaded)
                if (isset($_FILES['main_image_file']) && $_FILES['main_image_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['main_image_file']['tmp_name'];
                    $fileName = $_FILES['main_image_file']['name'];
                    $fileSize = $_FILES['main_image_file']['size'];
                    $fileType = mime_content_type($fileTmp);
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($fileType, $allowedMimeTypes)) {
                        $error = 'Main Image must be a JPG, PNG, or WEBP image.';
                    } elseif ($fileSize > $maxFileSize) {
                        $error = 'Main Image file size must be less than 5MB.';
                    } else {
                        $newFileName = 'prod-' . $slug . '-' . time() . '.' . $fileExt;
                        $destPath = $uploadDir . '/' . $newFileName;
                        if (move_uploaded_file($fileTmp, $destPath)) {
                            $imagePath = 'assets/products/' . $newFileName;

                            // Register in media table
                            try {
                                $mediaStmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
                                $mediaStmt->execute([$fileName, $imagePath, $fileType, $fileSize]);
                            } catch (Exception $e) {}
                        } else {
                            $error = 'Failed to save uploaded image.';
                        }
                    }
                }

                // 2. Process Additional Gallery Uploads
                $galleryArray = json_decode($galleryJson, true);
                if (!is_array($galleryArray)) {
                    $galleryArray = [];
                }

                if (empty($error) && isset($_FILES['gallery_files']) && is_array($_FILES['gallery_files']['name'])) {
                    $numFiles = count($_FILES['gallery_files']['name']);
                    for ($i = 0; $i < $numFiles; $i++) {
                        if ($_FILES['gallery_files']['error'][$i] === UPLOAD_ERR_OK) {
                            $gTmp = $_FILES['gallery_files']['tmp_name'][$i];
                            $gName = $_FILES['gallery_files']['name'][$i];
                            $gSize = $_FILES['gallery_files']['size'][$i];
                            $gType = mime_content_type($gTmp);
                            $gExt = strtolower(pathinfo($gName, PATHINFO_EXTENSION));

                            if (in_array($gType, $allowedMimeTypes) && $gSize <= $maxFileSize) {
                                $newGalleryName = 'gallery-' . $slug . '-' . time() . '-' . rand(100, 999) . '.' . $gExt;
                                $gDest = $uploadDir . '/' . $newGalleryName;
                                if (move_uploaded_file($gTmp, $gDest)) {
                                    $relGPath = 'assets/products/' . $newGalleryName;
                                    $galleryArray[] = $relGPath;

                                    try {
                                        $mediaStmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
                                        $mediaStmt->execute([$gName, $relGPath, $gType, $gSize]);
                                    } catch (Exception $e) {}
                                }
                            }
                        }
                    }
                }

                $galleryJsonFinal = json_encode(array_values(array_unique($galleryArray)));
                $finalSalePrice = (!empty($salePrice) && (float)$salePrice > 0) ? (float)$salePrice : null;

                if (empty($error)) {
                    if ($isEdit) {
                        $updateSql = "UPDATE products SET
                            slug = ?, name = ?, category = ?, price = ?, sale_price = ?, stock_quantity = ?,
                            short_description = ?, long_description = ?, image_main = ?, image_gallery = ?,
                            meta_title = ?, meta_description = ?, meta_keywords = ?, is_active = ?, is_featured = ?,
                            updated_at = NOW()
                            WHERE id = ?";
                        $stmt = $pdo->prepare($updateSql);
                        $stmt->execute([
                            $slug, $name, $category, (float)$price, $finalSalePrice, $stockQuantity,
                            $shortDesc, $longDesc, $imagePath, $galleryJsonFinal,
                            $metaTitle, $metaDesc, $metaKeywords, $isActive, $isFeatured,
                            $productId
                        ]);

                        $success = 'Product updated successfully!';
                        $product['image_main'] = $imagePath;
                        $product['image_gallery'] = $galleryJsonFinal;
                    } else {
                        $insertSql = "INSERT INTO products (
                            slug, name, category, price, sale_price, stock_quantity,
                            short_description, long_description, image_main, image_gallery,
                            meta_title, meta_description, meta_keywords, is_active, is_featured,
                            created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                        $stmt = $pdo->prepare($insertSql);
                        $stmt->execute([
                            $slug, $name, $category, (float)$price, $finalSalePrice, $stockQuantity,
                            $shortDesc, $longDesc, $imagePath, $galleryJsonFinal,
                            $metaTitle, $metaDesc, $metaKeywords, $isActive, $isFeatured
                        ]);

                        $newProductId = $pdo->lastInsertId();
                        header('Location: product-editor.php?id=' . $newProductId . '&success=' . urlencode('Product created successfully!'));
                        exit;
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$galleryImages = json_decode($product['image_gallery'] ?: '[]', true) ?: [];
$csrfToken = generate_csrf();
render_admin_header($isEdit ? "Edit Product: " . htmlspecialchars($product['name']) : "Create New Product", "products");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<form action="product-editor.php<?php echo $isEdit ? '?id=' . $productId : ''; ?>" method="POST" enctype="multipart/form-data" id="productForm">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="image_main_existing" id="imageMainExisting" value="<?php echo htmlspecialchars($product['image_main']); ?>">
    <input type="hidden" name="image_gallery_json" id="imageGalleryJson" value="<?php echo htmlspecialchars(json_encode($galleryImages)); ?>">

    <!-- Top Bar Action Row -->
    <div class="editor-header" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="products.php" class="btn btn-outline btn-sm" title="Back to Catalog">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Products
            </a>
            <div>
                <h2 style="font-family:'Cormorant Garamond',serif; font-size: 26px; margin: 0;"><?php echo $isEdit ? 'Edit Product' : 'Add New Product'; ?></h2>
                <div style="font-size: 13px; color: var(--text-light);">
                    <?php echo $isEdit ? 'ID #' . $productId . ' · /shop/' . htmlspecialchars($product['slug']) : 'Create a new handcrafted chocolate offering'; ?>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <?php if ($isEdit): ?>
                <a href="../shop/<?php echo urlencode($product['slug']); ?>" target="_blank" class="btn btn-outline btn-sm" title="View on Live Store">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Preview in Store
                </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" id="saveProductBtn">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                <?php echo $isEdit ? 'Update Product' : 'Publish Product'; ?>
            </button>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="editor-layout" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- LEFT COLUMN: Primary Details & Media -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Basic Information Card -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="editor-title">Basic Information</div>

                <div class="form-group">
                    <label class="form-label" for="productName">Product Name *</label>
                    <input type="text" id="productName" name="name" class="form-control" placeholder="e.g. Signature Single-Origin 72% Dark Chocolate Bar" value="<?php echo htmlspecialchars($product['name']); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" for="productSlug" style="margin-bottom:0;">URL Slug *</label>
                        <button type="button" class="btn-text" id="toggleSlugLock" style="font-size: 11.5px; color: var(--gold-light); cursor: pointer; background: none; border: none;">Auto-Generated</button>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 13px; color: var(--text-light); background: var(--bg-subtle); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); white-space: nowrap;">
                            /shop/
                        </span>
                        <input type="text" id="productSlug" name="slug" class="form-control" placeholder="signature-dark-chocolate-72" value="<?php echo htmlspecialchars($product['slug']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="shortDescription">Short Description (Excerpt)</label>
                    <textarea id="shortDescription" name="short_description" class="form-control" rows="2" placeholder="Brief 1-2 sentence overview shown in store catalog and product listing cards..."><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="longDescription">Detailed Product Description</label>
                    <textarea id="longDescription" name="long_description" class="form-control" rows="8" placeholder="Full product details, tasting notes (e.g. red fruit, roasted cacao), ingredients, allergens, origin story, and pairing suggestions..."><?php echo htmlspecialchars($product['long_description']); ?></textarea>
                </div>
            </div>

            <!-- Media & Images Card -->
            <div class="form-card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div class="editor-title" style="margin-bottom: 0;">Product Images</div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="openMediaModal('main')" style="gap: 6px;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Choose from Media Library
                    </button>
                </div>

                <!-- Main Featured Image -->
                <div class="form-group">
                    <label class="form-label">Primary Product Image</label>
                    <div class="image-preview-box" id="mainImagePreviewBox" onclick="triggerFileInput('mainImageFileInput')">
                        <?php if (!empty($product['image_main'])): ?>
                            <img src="../<?php echo htmlspecialchars($product['image_main']); ?>" id="mainImageImg" alt="Main Product Preview">
                            <div style="position: absolute; bottom: 10px; right: 10px; display: flex; gap: 8px;">
                                <button type="button" class="btn btn-sm btn-outline" style="background: rgba(0,0,0,0.7); color: #fff; border: none;" onclick="event.stopPropagation(); triggerFileInput('mainImageFileInput')">Replace</button>
                                <button type="button" class="btn btn-sm btn-danger" style="border: none;" onclick="event.stopPropagation(); removeMainImage()">Remove</button>
                            </div>
                        <?php else: ?>
                            <div id="mainImagePlaceholder" style="text-align: center; color: var(--text-light);">
                                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom: 8px;"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div>Click to upload main image or drag & drop</div>
                                <div style="font-size: 11px; margin-top: 4px; color: var(--text-muted);">PNG, JPG, WEBP up to 5MB</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="main_image_file" id="mainImageFileInput" accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="previewMainImage(this)">
                </div>

                <!-- Additional Gallery Images Strip -->
                <div class="form-group" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label" style="margin-bottom: 0;">Product Gallery (Additional Angles & Shots)</label>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="openMediaModal('gallery')" style="padding: 3px 8px; font-size: 11.5px;">+ Pick from Library</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="triggerFileInput('galleryFileInput')" style="padding: 3px 8px; font-size: 11.5px;">+ Upload Files</button>
                        </div>
                    </div>
                    <input type="file" name="gallery_files[]" id="galleryFileInput" multiple accept="image/jpeg,image/png,image/webp" style="display: none;" onchange="previewGalleryUploads(this)">

                    <div class="gallery-grid" id="galleryContainer">
                        <?php foreach ($galleryImages as $idx => $gPath): ?>
                            <div class="gallery-item" id="gallery-item-<?php echo $idx; ?>">
                                <img src="../<?php echo htmlspecialchars($gPath); ?>" alt="Gallery Image">
                                <button type="button" class="gallery-remove-btn" onclick="removeGalleryItem(<?php echo $idx; ?>)" title="Remove Photo">✕</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="galleryEmptyNotice" style="<?php echo empty($galleryImages) ? 'display:block;' : 'display:none;'; ?> font-size: 12px; color: var(--text-light); margin-top: 10px; font-style: italic;">
                        No gallery images added yet. Click "+ Upload Files" or "+ Pick from Library" to add extra product views.
                    </div>
                </div>
            </div>

            <!-- SEO & Metadata Card with Live Google Snippet -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="editor-title">Search Engine Optimization (SEO)</div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between;">
                        <label class="form-label" for="metaTitle">SEO Meta Title</label>
                        <span id="titleCounter" style="font-size: 11px; color: var(--text-light);">0 / 60</span>
                    </div>
                    <input type="text" id="metaTitle" name="meta_title" class="form-control" placeholder="Signature 72% Dark Chocolate Bar | RT Chocos India" value="<?php echo htmlspecialchars($product['meta_title']); ?>">
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between;">
                        <label class="form-label" for="metaDesc">SEO Meta Description</label>
                        <span id="descCounter" style="font-size: 11px; color: var(--text-light);">0 / 160</span>
                    </div>
                    <textarea id="metaDesc" name="meta_description" class="form-control" rows="2" placeholder="Experience authentic bean-to-bar 72% dark chocolate crafted in India with single-origin Idukki cacao..."><?php echo htmlspecialchars($product['meta_description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="metaKeywords">SEO Keywords (Comma Separated)</label>
                    <input type="text" id="metaKeywords" name="meta_keywords" class="form-control" placeholder="bean to bar chocolate, dark chocolate India, single origin cacao, artisan chocolate" value="<?php echo htmlspecialchars($product['meta_keywords']); ?>">
                </div>

                <!-- Live Google Search Result Mockup -->
                <div style="margin-top: 16px;">
                    <label class="form-label" style="font-size: 12px; color: var(--text-light);">Live Google Search Preview</label>
                    <div class="google-preview-card">
                        <div class="google-preview-url">
                            <span style="font-weight: bold;">rtchocos.com</span>
                            <span>› shop › <span id="googlePreviewSlug"><?php echo htmlspecialchars($product['slug'] ?: 'product-slug'); ?></span></span>
                        </div>
                        <div class="google-preview-title" id="googlePreviewTitle">
                            <?php echo htmlspecialchars($product['meta_title'] ?: ($product['name'] ?: 'Product Title') . ' — RT Chocos'); ?>
                        </div>
                        <div class="google-preview-snippet" id="googlePreviewSnippet">
                            <?php echo htmlspecialchars($product['meta_description'] ?: ($product['short_description'] ?: 'Discover handcrafted artisan chocolate confections made with premium Indian cacao beans.')); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT SIDEBAR: Pricing, Inventory & Status -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Publish & Visibility Card -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="editor-title">Visibility & Storefront</div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 12px;">
                        <input type="checkbox" name="is_active" id="isActiveInput" value="1" <?php echo $product['is_active'] ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: var(--gold-light);">
                        <div>
                            <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">Active on Store</div>
                            <div style="font-size: 11.5px; color: var(--text-light);">Visible in catalog & available for purchase</div>
                        </div>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_featured" id="isFeaturedInput" value="1" <?php echo $product['is_featured'] ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: var(--gold-light);">
                        <div>
                            <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">Featured Product</div>
                            <div style="font-size: 11.5px; color: var(--text-light);">Pinned to top of shop & highlights</div>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 42px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                    <?php echo $isEdit ? 'Save Changes' : 'Publish to Shop'; ?>
                </button>
            </div>

            <!-- Pricing & Discounts Card -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="editor-title">Pricing & Discounts</div>

                <div class="form-group">
                    <label class="form-label" for="regularPrice">Regular Price (₹) *</label>
                    <input type="number" step="0.01" min="0" id="regularPrice" name="price" class="form-control" placeholder="350.00" value="<?php echo htmlspecialchars($product['price']); ?>" required oninput="calculateDiscount()">
                </div>

                <div class="form-group">
                    <label class="form-label" for="salePrice">Sale / Promotional Price (₹)</label>
                    <input type="number" step="0.01" min="0" id="salePrice" name="sale_price" class="form-control" placeholder="299.00" value="<?php echo htmlspecialchars($product['sale_price']); ?>" oninput="calculateDiscount()">
                    <div style="font-size: 11px; color: var(--text-light); margin-top: 4px;">Leave blank if not on special promotion.</div>
                </div>

                <!-- Dynamic Discount Badge Card -->
                <div id="discountPreviewBox" style="display: none; background: var(--bg-subtle); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12px; color: var(--text-light);">Shopper Discount:</span>
                        <span class="discount-badge" id="discountPercentText" style="font-size: 13px;">0% OFF</span>
                    </div>
                    <div style="font-size: 12px; color: var(--text-main); margin-top: 4px;" id="discountSavingsText">
                        Customer saves ₹0.00
                    </div>
                </div>
            </div>

            <!-- Inventory & Stock Management -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="editor-title">Inventory & Stock</div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="stockQuantity">Stock Quantity (Units Available) *</label>
                    <input type="number" min="0" id="stockQuantity" name="stock_quantity" class="form-control" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required oninput="checkStockAlert(this.value)">
                    
                    <div id="stockAlertNotice" style="margin-top: 8px; font-size: 12px;"></div>
                </div>
            </div>

            <!-- Category Assignment -->
            <div class="form-card" style="margin-bottom: 0;">
                <div class="editor-title">Product Category</div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="categoryInput">Category Name</label>
                    <input type="text" id="categoryInput" name="category" list="categoryDatalist" class="form-control" placeholder="Select or type new..." value="<?php echo htmlspecialchars($product['category']); ?>">
                    <datalist id="categoryDatalist">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"></option>
                        <?php endforeach; ?>
                        <option value="Chocolates"></option>
                        <option value="Cacao & Nibs"></option>
                        <option value="Bonbons & Truffles"></option>
                        <option value="Chocolate Kits"></option>
                        <option value="Gift Hampers"></option>
                    </datalist>
                </div>
            </div>

        </div>

    </div>
</form>

<!-- Media Library Quick Picker Modal -->
<div class="order-modal-backdrop" id="mediaPickerModal">
    <div class="order-modal-content" style="max-width: 760px;">
        <div class="order-modal-header">
            <h3 style="font-family:'Cormorant Garamond',serif; font-size: 20px; margin: 0;">Choose from Media Library</h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="closeMediaModal()" style="padding: 4px 8px;">✕</button>
        </div>
        <div class="order-modal-body" style="max-height: 480px;">
            <div style="font-size: 13px; color: var(--text-light); margin-bottom: 12px;">Click any image to attach to this product:</div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px;" id="mediaGrid">
                <?php foreach ($mediaItems as $m): ?>
                    <div class="media-pick-item" onclick="selectMediaItem(<?php echo htmlspecialchars(json_encode($m['path'])); ?>)" style="aspect-ratio: 1; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.2s ease; position: relative;">
                        <img src="../<?php echo htmlspecialchars($m['path']); ?>" alt="<?php echo htmlspecialchars($m['filename']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (empty($mediaItems)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-light);">No images found in Media Library.</div>
            <?php endif; ?>
        </div>
        <div class="order-modal-footer">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeMediaModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
    let currentPickerTarget = 'main'; // 'main' or 'gallery'
    let autoSlug = <?php echo $isEdit ? 'false' : 'true'; ?>;
    let galleryList = <?php echo json_encode($galleryImages); ?>;

    const nameInput = document.getElementById('productName');
    const slugInput = document.getElementById('productSlug');
    const toggleSlugBtn = document.getElementById('toggleSlugLock');

    // Slug auto generator
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            if (autoSlug) {
                const slug = nameInput.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');
                slugInput.value = slug;
                updateGoogleSnippet();
            }
        });
    }

    if (slugInput) {
        slugInput.addEventListener('input', () => {
            autoSlug = false;
            if (toggleSlugBtn) toggleSlugBtn.textContent = 'Manual Override';
            updateGoogleSnippet();
        });
    }

    if (toggleSlugBtn) {
        toggleSlugBtn.addEventListener('click', () => {
            autoSlug = !autoSlug;
            toggleSlugBtn.textContent = autoSlug ? 'Auto-Generated' : 'Manual Override';
            if (autoSlug && nameInput.value) {
                slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
                updateGoogleSnippet();
            }
        });
    }

    // SEO Counter & Preview Updates
    const metaTitle = document.getElementById('metaTitle');
    const metaDesc = document.getElementById('metaDesc');
    const titleCounter = document.getElementById('titleCounter');
    const descCounter = document.getElementById('descCounter');

    function updateGoogleSnippet() {
        const title = metaTitle.value.trim() || (nameInput.value.trim() ? nameInput.value.trim() + ' — RT Chocos' : 'Product Title — RT Chocos');
        const slug = slugInput.value.trim() || 'product-slug';
        const desc = metaDesc.value.trim() || (document.getElementById('shortDescription').value.trim() || 'Discover handcrafted artisan chocolate confections made with premium Indian cacao beans.');

        document.getElementById('googlePreviewTitle').textContent = title;
        document.getElementById('googlePreviewSlug').textContent = slug;
        document.getElementById('googlePreviewSnippet').textContent = desc;

        titleCounter.textContent = metaTitle.value.length + ' / 60';
        titleCounter.style.color = metaTitle.value.length > 60 ? '#d32f2f' : 'var(--text-light)';

        descCounter.textContent = metaDesc.value.length + ' / 160';
        descCounter.style.color = metaDesc.value.length > 160 ? '#d32f2f' : 'var(--text-light)';
    }

    metaTitle.addEventListener('input', updateGoogleSnippet);
    metaDesc.addEventListener('input', updateGoogleSnippet);
    nameInput.addEventListener('input', updateGoogleSnippet);
    document.getElementById('shortDescription').addEventListener('input', updateGoogleSnippet);

    // Pricing & Discount calculation
    function calculateDiscount() {
        const reg = parseFloat(document.getElementById('regularPrice').value) || 0;
        const sale = parseFloat(document.getElementById('salePrice').value) || 0;
        const box = document.getElementById('discountPreviewBox');

        if (reg > 0 && sale > 0 && sale < reg) {
            const savings = reg - sale;
            const percent = Math.round((savings / reg) * 100);

            document.getElementById('discountPercentText').textContent = percent + '% OFF';
            document.getElementById('discountSavingsText').textContent = 'Customer saves ₹' + savings.toFixed(2);
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    // Stock alert checker
    function checkStockAlert(qty) {
        const val = parseInt(qty) || 0;
        const notice = document.getElementById('stockAlertNotice');
        if (val === 0) {
            notice.innerHTML = '<span style="color: #d32f2f; font-weight: 600;">⚠️ Out of Stock</span> (Product will show Out of Stock on store)';
        } else if (val <= 10) {
            notice.innerHTML = '<span style="color: #ef6c00; font-weight: 600;">⚡ Low Stock Warning</span> (' + val + ' units remaining)';
        } else {
            notice.innerHTML = '<span style="color: #2e7d32;">✓ Sufficient Inventory</span>';
        }
    }

    // Trigger Hidden File Inputs
    function triggerFileInput(id) {
        document.getElementById(id).click();
    }

    // Preview Main Image Upload
    function previewMainImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const box = document.getElementById('mainImagePreviewBox');
                box.innerHTML = `
                    <img src="${e.target.result}" id="mainImageImg" alt="Main Product Preview">
                    <div style="position: absolute; bottom: 10px; right: 10px; display: flex; gap: 8px;">
                        <button type="button" class="btn btn-sm btn-outline" style="background: rgba(0,0,0,0.7); color: #fff; border: none;" onclick="event.stopPropagation(); triggerFileInput('mainImageFileInput')">Replace</button>
                        <button type="button" class="btn btn-sm btn-danger" style="border: none;" onclick="event.stopPropagation(); removeMainImage()">Remove</button>
                    </div>
                `;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeMainImage() {
        document.getElementById('mainImageExisting').value = '';
        document.getElementById('mainImageFileInput').value = '';
        const box = document.getElementById('mainImagePreviewBox');
        box.innerHTML = `
            <div id="mainImagePlaceholder" style="text-align: center; color: var(--text-light);">
                <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom: 8px;"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <div>Click to upload main image or drag & drop</div>
                <div style="font-size: 11px; margin-top: 4px; color: var(--text-muted);">PNG, JPG, WEBP up to 5MB</div>
            </div>
        `;
    }

    // Gallery Management
    function renderGallery() {
        const container = document.getElementById('galleryContainer');
        const notice = document.getElementById('galleryEmptyNotice');
        container.innerHTML = '';

        if (galleryList.length === 0) {
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
            galleryList.forEach((path, idx) => {
                const item = document.createElement('div');
                item.className = 'gallery-item';
                item.innerHTML = `
                    <img src="../${path}" alt="Gallery Image">
                    <button type="button" class="gallery-remove-btn" onclick="removeGalleryItem(${idx})" title="Remove Photo">✕</button>
                `;
                container.appendChild(item);
            });
        }
        document.getElementById('imageGalleryJson').value = JSON.stringify(galleryList);
    }

    function removeGalleryItem(idx) {
        galleryList.splice(idx, 1);
        renderGallery();
    }

    function previewGalleryUploads(input) {
        if (input.files) {
            const count = input.files.length;
            showToast(count + ' gallery photo(s) queued for upload upon saving.', 'info');
        }
    }

    // Media Picker Modal
    function openMediaModal(target) {
        currentPickerTarget = target;
        document.getElementById('mediaPickerModal').classList.add('open');
    }

    function closeMediaModal() {
        document.getElementById('mediaPickerModal').classList.remove('open');
    }

    function selectMediaItem(path) {
        if (currentPickerTarget === 'main') {
            document.getElementById('imageMainExisting').value = path;
            const box = document.getElementById('mainImagePreviewBox');
            box.innerHTML = `
                <img src="../${path}" id="mainImageImg" alt="Main Product Preview">
                <div style="position: absolute; bottom: 10px; right: 10px; display: flex; gap: 8px;">
                    <button type="button" class="btn btn-sm btn-outline" style="background: rgba(0,0,0,0.7); color: #fff; border: none;" onclick="event.stopPropagation(); triggerFileInput('mainImageFileInput')">Replace</button>
                    <button type="button" class="btn btn-sm btn-danger" style="border: none;" onclick="event.stopPropagation(); removeMainImage()">Remove</button>
                </div>
            `;
            showToast('Primary product image updated.', 'success');
        } else if (currentPickerTarget === 'gallery') {
            if (!galleryList.includes(path)) {
                galleryList.push(path);
                renderGallery();
                showToast('Image added to product gallery.', 'success');
            } else {
                showToast('Image already in gallery.', 'info');
            }
        }
        closeMediaModal();
    }

    // Initial setups on load
    window.addEventListener('DOMContentLoaded', () => {
        calculateDiscount();
        checkStockAlert(document.getElementById('stockQuantity').value);
        updateGoogleSnippet();
    });
</script>

<?php
render_admin_footer();
?>
