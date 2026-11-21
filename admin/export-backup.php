<?php
// admin/export-backup.php — Product Catalog & Content Backup Center
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();
$rootDir = dirname(__DIR__);
$backupDir = $rootDir . '/backups';

// Ensure backup directory exists
if (!file_exists($backupDir)) {
    @mkdir($backupDir, 0777, true);
}

// -----------------------------------------------------------
// Direct File Download Handler (Streams before HTML output)
// -----------------------------------------------------------
$action = $_GET['action'] ?? '';
if (!empty($action)) {
    if ($action === 'download_zip') {
        $zipFile = $backupDir . '/rtchocos_products_and_content_backup.zip';
        if (file_exists($zipFile)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="rtchocos_backup_' . date('Y-m-d') . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($zipFile);
            exit;
        } else {
            $error = "Backup ZIP not found. Please click 'Generate Fresh Backup' below.";
        }
    } elseif ($action === 'download_json') {
        $jsonFile = $backupDir . '/products_catalog_backup.json';
        if (file_exists($jsonFile)) {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="rtchocos_products_backup_' . date('Y-m-d') . '.json"');
            header('Content-Length: ' . filesize($jsonFile));
            readfile($jsonFile);
            exit;
        }
    } elseif ($action === 'download_sql') {
        $sqlFile = $backupDir . '/products_and_categories_backup.sql';
        if (file_exists($sqlFile)) {
            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="rtchocos_products_and_categories_' . date('Y-m-d') . '.sql"');
            header('Content-Length: ' . filesize($sqlFile));
            readfile($sqlFile);
            exit;
        }
    } elseif ($action === 'download_all_sql') {
        $sqlFile = $backupDir . '/rtchocos_all_content_database_backup.sql';
        if (file_exists($sqlFile)) {
            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="rtchocos_all_content_database_' . date('Y-m-d') . '.sql"');
            header('Content-Length: ' . filesize($sqlFile));
            readfile($sqlFile);
            exit;
        }
    } elseif ($action === 'download_md') {
        $mdFile = $backupDir . '/PRODUCT_CATALOG_CONTENT_BACKUP.md';
        if (file_exists($mdFile)) {
            header('Content-Type: text/markdown; charset=utf-8');
            header('Content-Disposition: attachment; filename="rtchocos_catalog_written_content_' . date('Y-m-d') . '.md"');
            header('Content-Length: ' . filesize($mdFile));
            readfile($mdFile);
            exit;
        }
    }
}

// -----------------------------------------------------------
// Handle Fresh Backup Generation
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_fresh') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        try {
            // Run create_backup.php logic
            include_once $backupDir . '/create_backup.php';
            include_once $backupDir . '/backup_all_content.php';
            
            // Re-compress if command line tool or powershell available
            @exec('powershell -Command "Compress-Archive -Path backups\\products_and_categories_backup.sql, backups\\rtchocos_all_content_database_backup.sql, backups\\products_catalog_backup.json, backups\\PRODUCT_CATALOG_CONTENT_BACKUP.md, backups\\product_images -DestinationPath backups\\rtchocos_products_and_content_backup.zip -Force"');
            
            $success = 'Fresh backup generated successfully with all written descriptions and images!';
        } catch (Exception $e) {
            $error = 'Backup failed: ' . $e->getMessage();
        }
    }
}

// Fetch stats for display
$products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM product_categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$totalProducts = count($products);
$totalCategories = count($categories);

// Check backup files
$zipFile = $backupDir . '/rtchocos_products_and_content_backup.zip';
$zipExists = file_exists($zipFile);
$zipSize = $zipExists ? round(filesize($zipFile) / (1024 * 1024), 1) . ' MB' : 'Not generated';
$zipTime = $zipExists ? date('M j, Y - g:i a', filemtime($zipFile)) : 'N/A';

render_admin_header("Data & Product Backup Center", "products");
?>

<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 60px;">
    <!-- Top Return / Navigation Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="products.php" style="color: var(--gold); text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                Back to Product Catalog
            </a>
            <h1 style="font-family: var(--font-heading); font-size: 26px; color: var(--text-main); margin: 6px 0 2px 0;">Safe Data &amp; Content Backup</h1>
            <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">Preserve and download all your handcrafted product descriptions, written stories, pricing, and images.</p>
        </div>
        <form method="POST" action="export-backup.php" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="generate_fresh">
            <button type="submit" class="btn btn-outline" style="border-color: var(--gold); color: var(--gold); display: inline-flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Generate Fresh Backup
            </button>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #4ade80; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Master Backup Card -->
    <div style="background: var(--bg-card); border: 1px solid var(--gold); border-radius: 14px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 30px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 140px; height: 140px; background: radial-gradient(circle, rgba(212,175,55,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
        
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px;">
            <div>
                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: var(--gold); font-weight: 700;">Complete Archive</span>
                <h2 style="font-family: var(--font-heading); font-size: 24px; color: var(--text-main); margin: 6px 0 10px 0;">All-in-One Backup Package (ZIP)</h2>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 600px; line-height: 1.6; margin: 0 0 12px 0;">
                    Contains everything in a single archive: MySQL SQL dumps, JSON catalog, Markdown document with all written descriptions, and the entire high-resolution product photography library.
                </p>
                <div style="display: flex; gap: 18px; font-size: 13px; color: var(--text-light);">
                    <span>📦 Size: <strong><?php echo $zipSize; ?></strong></span>
                    <span>🕒 Last Updated: <strong><?php echo $zipTime; ?></strong></span>
                    <span>🏷️ Items: <strong><?php echo $totalProducts; ?> Products, <?php echo $totalCategories; ?> Categories</strong></span>
                </div>
            </div>
            
            <a href="export-backup.php?action=download_zip" class="btn btn-primary" style="padding: 14px 26px; font-size: 15px; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 20px rgba(212,175,55,0.35);">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download Full ZIP Archive
            </a>
        </div>
    </div>

    <!-- Quick Download Options Grid -->
    <h3 style="font-family: var(--font-heading); font-size: 19px; color: var(--text-main); margin-bottom: 16px;">Individual Format Exports</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 36px;">
        
        <!-- JSON Card -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">JSON</div>
                    <h4 style="margin: 0; font-size: 16px; color: var(--text-main);">Human-Readable JSON</h4>
                </div>
                <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; margin: 0 0 16px 0;">
                    Formatted JSON containing every product name, slug, pricing, and exact short &amp; long written descriptions. Openable in any text editor.
                </p>
            </div>
            <a href="export-backup.php?action=download_json" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                Download JSON File
            </a>
        </div>

        <!-- SQL Products Card -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(34, 197, 94, 0.15); color: #4ade80; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">SQL</div>
                    <h4 style="margin: 0; font-size: 16px; color: var(--text-main);">Products &amp; Categories SQL</h4>
                </div>
                <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; margin: 0 0 16px 0;">
                    Ready-to-run MySQL script with CREATE TABLE and INSERT statements with ON DUPLICATE KEY UPDATE.
                </p>
            </div>
            <a href="export-backup.php?action=download_sql" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                Download Products SQL
            </a>
        </div>

        <!-- Full Database SQL Card -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(168, 85, 247, 0.15); color: #c084fc; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">ALL</div>
                    <h4 style="margin: 0; font-size: 16px; color: var(--text-main);">Full Site Database SQL</h4>
                </div>
                <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; margin: 0 0 16px 0;">
                    Complete dump of all content tables: Products, Categories, Blogs, Media library, Site Settings, and FAQs.
                </p>
            </div>
            <a href="export-backup.php?action=download_all_sql" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                Download Full Database SQL
            </a>
        </div>

        <!-- Markdown Document Card -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(234, 179, 8, 0.15); color: #facc15; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">MD</div>
                    <h4 style="margin: 0; font-size: 16px; color: var(--text-main);">Markdown Catalog Doc</h4>
                </div>
                <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; margin: 0 0 16px 0;">
                    Formatted document with all product descriptions and stories. Ideal for documentation, PDF generation, or reading copy.
                </p>
            </div>
            <a href="export-backup.php?action=download_md" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                Download Markdown (.md)
            </a>
        </div>
    </div>

    <!-- Backed Up Products Catalog Preview -->
    <h3 style="font-family: var(--font-heading); font-size: 19px; color: var(--text-main); margin-bottom: 14px;">Products Backed Up (<?php echo $totalProducts; ?> items)</h3>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.2);">
                    <th style="padding: 12px 16px; width: 60px;">Image</th>
                    <th style="padding: 12px 16px;">Product Name &amp; Slug</th>
                    <th style="padding: 12px 16px;">Category</th>
                    <th style="padding: 12px 16px;">Price</th>
                    <th style="padding: 12px 16px;">Description Preview</th>
                    <th style="padding: 12px 16px; text-align: right;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px 16px;">
                            <div style="width: 44px; height: 44px; border-radius: 6px; overflow: hidden; background: var(--cream); border: 1px solid var(--border-color);">
                                <img src="../<?php echo htmlspecialchars($p['image_main']); ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.onerror=null;this.src='../assets/premium_chocolate.png';">
                            </div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <strong style="color: var(--text-main); display: block;"><?php echo htmlspecialchars($p['name']); ?></strong>
                            <span style="font-family: monospace; font-size: 11px; color: var(--text-light);"><?php echo htmlspecialchars($p['slug']); ?></span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="background: rgba(212,175,55,0.12); color: var(--gold); padding: 3px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 500;">
                                <?php echo htmlspecialchars($p['category']); ?>
                            </span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 600; color: var(--text-main);">
                            ₹<?php echo number_format($p['price'], 2); ?>
                        </td>
                        <td style="padding: 12px 16px; color: var(--text-muted); max-width: 360px;">
                            <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                <?php echo htmlspecialchars($p['short_description'] ?: $p['long_description']); ?>
                            </div>
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <span style="color: <?php echo $p['is_active'] ? '#4ade80' : '#f87171'; ?>; font-weight: 600; font-size: 12px;">
                                <?php echo $p['is_active'] ? '● Active' : '○ Draft'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php render_admin_footer(); ?>
