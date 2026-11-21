<?php
// backups/create_backup.php — Comprehensive Backup Generator for RT Chocos
require_once __DIR__ . '/../includes/db.php';

$pdo = get_db();
$rootDir = dirname(__DIR__);
$backupDir = $rootDir . '/backups';

if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$imagesBackupDir = $backupDir . '/product_images';
if (!file_exists($imagesBackupDir)) {
    mkdir($imagesBackupDir, 0777, true);
}

$timestamp = date('Y-m-d_H-i-s');
echo "=== RT CHOCOS PRODUCT & CONTENT BACKUP GENERATOR ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// ----------------------------------------------------
// 1. Fetch all products and categories
// ----------------------------------------------------
$products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM product_categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

echo "Fetched " . count($products) . " products and " . count($categories) . " categories.\n";

// ----------------------------------------------------
// 2. Generate Human-Readable JSON Backup
// ----------------------------------------------------
$jsonFile = $backupDir . '/products_catalog_backup.json';
$jsonData = [
    'backup_created_at' => date('c'),
    'site' => 'RT Chocos (https://www.rtchocos.com)',
    'total_products' => count($products),
    'categories' => $categories,
    'products' => array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'],
            'category' => $p['category'],
            'price' => (float)$p['price'],
            'sale_price' => !empty($p['sale_price']) ? (float)$p['sale_price'] : null,
            'stock_quantity' => (int)$p['stock_quantity'],
            'is_active' => (bool)$p['is_active'],
            'is_featured' => (bool)$p['is_featured'],
            'short_description' => $p['short_description'],
            'long_description' => $p['long_description'],
            'image_main' => $p['image_main'],
            'image_gallery' => json_decode($p['image_gallery'] ?? '[]', true) ?: [],
            'meta_title' => $p['meta_title'],
            'meta_description' => $p['meta_description'],
            'meta_keywords' => $p['meta_keywords'],
            'created_at' => $p['created_at'],
            'updated_at' => $p['updated_at']
        ];
    }, $products)
];

file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo "[1/5] Created JSON backup: {$jsonFile} (" . round(filesize($jsonFile)/1024, 1) . " KB)\n";

// ----------------------------------------------------
// 3. Generate Human-Readable Markdown Document
// ----------------------------------------------------
$mdFile = $backupDir . '/PRODUCT_CATALOG_CONTENT_BACKUP.md';
$md = "# RT Chocos — Product Catalog & Written Content Backup\n\n";
$md .= "**Generated on:** " . date('F j, Y, g:i a') . "\n";
$md .= "**Total Products:** " . count($products) . "\n";
$md .= "**Website:** [rtchocos.com](https://www.rtchocos.com/shop)\n\n";
$md .= "---\n\n";

foreach ($products as $p) {
    $saleStr = !empty($p['sale_price']) ? " ~~₹{$p['price']}~~ **₹{$p['sale_price']}**" : " **₹{$p['price']}**";
    $md .= "## {$p['id']}. {$p['name']}\n\n";
    $md .= "- **Slug:** `{$p['slug']}`\n";
    $md .= "- **Category:** {$p['category']}\n";
    $md .= "- **Price:**{$saleStr}\n";
    $md .= "- **Stock:** {$p['stock_quantity']} units\n";
    $md .= "- **Status:** " . ($p['is_active'] ? "Active (In Shop)" : "Draft / Inactive") . "\n";
    $md .= "- **Main Image:** `{$p['image_main']}`\n\n";
    
    $md .= "### Short Description\n\n";
    $md .= ($p['short_description'] ?: "_No short description provided._") . "\n\n";
    
    $md .= "### Long Description & Story\n\n";
    $md .= ($p['long_description'] ?: "_No long description provided._") . "\n\n";

    if (!empty($p['meta_title']) || !empty($p['meta_description'])) {
        $md .= "### SEO Metadata\n\n";
        $md .= "- **Meta Title:** " . ($p['meta_title'] ?: "Default") . "\n";
        $md .= "- **Meta Description:** " . ($p['meta_description'] ?: "Default") . "\n\n";
    }

    $md .= "---\n\n";
}

file_put_contents($mdFile, $md);
echo "[2/5] Created Markdown backup: {$mdFile} (" . round(filesize($mdFile)/1024, 1) . " KB)\n";

// ----------------------------------------------------
// 4. Generate SQL Restore Script (Products + Categories)
// ----------------------------------------------------
$sqlFile = $backupDir . '/products_and_categories_backup.sql';
$sql = "-- ========================================================\n";
$sql .= "-- RT Chocos: Products & Categories SQL Backup\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Host: srv1875.hstgr.io | Database: u219698334_RTchocos\n";
$sql .= "-- ========================================================\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// Categories Table
$sql .= "-- Table structure for product_categories\n";
$sql .= "CREATE TABLE IF NOT EXISTS `product_categories` (\n";
$sql .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `name` varchar(100) NOT NULL,\n";
$sql .= "  `slug` varchar(100) NOT NULL,\n";
$sql .= "  `description` text DEFAULT NULL,\n";
$sql .= "  `created_at` timestamp NULL DEFAULT current_timestamp(),\n";
$sql .= "  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  UNIQUE KEY `slug` (`slug`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "-- Dumping data for product_categories\n";
foreach ($categories as $c) {
    $cDesc = $c['description'] !== null ? "'" . addslashes($c['description']) . "'" : "NULL";
    $sql .= "INSERT INTO `product_categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) ";
    $sql .= "VALUES ({$c['id']}, '" . addslashes($c['name']) . "', '" . addslashes($c['slug']) . "', {$cDesc}, '{$c['created_at']}', '{$c['updated_at']}') ";
    $sql .= "ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`);\n";
}
$sql .= "\n";

// Products Table
$sql .= "-- Table structure for products\n";
$sql .= "CREATE TABLE IF NOT EXISTS `products` (\n";
$sql .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `slug` varchar(150) NOT NULL,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `short_description` text DEFAULT NULL,\n";
$sql .= "  `long_description` longtext DEFAULT NULL,\n";
$sql .= "  `price` decimal(10,2) NOT NULL DEFAULT 0.00,\n";
$sql .= "  `sale_price` decimal(10,2) DEFAULT NULL,\n";
$sql .= "  `category` varchar(100) DEFAULT NULL,\n";
$sql .= "  `stock_quantity` int(11) NOT NULL DEFAULT 0,\n";
$sql .= "  `image_main` varchar(255) DEFAULT NULL,\n";
$sql .= "  `image_gallery` longtext DEFAULT NULL,\n";
$sql .= "  `meta_title` varchar(255) DEFAULT NULL,\n";
$sql .= "  `meta_description` text DEFAULT NULL,\n";
$sql .= "  `meta_keywords` varchar(500) DEFAULT NULL,\n";
$sql .= "  `is_featured` tinyint(1) NOT NULL DEFAULT 0,\n";
$sql .= "  `is_active` tinyint(1) NOT NULL DEFAULT 1,\n";
$sql .= "  `created_at` timestamp NULL DEFAULT current_timestamp(),\n";
$sql .= "  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  UNIQUE KEY `slug` (`slug`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "-- Dumping data for products\n";
foreach ($products as $p) {
    $sDesc = $p['short_description'] !== null ? "'" . addslashes($p['short_description']) . "'" : "NULL";
    $lDesc = $p['long_description'] !== null ? "'" . addslashes($p['long_description']) . "'" : "NULL";
    $saleP = $p['sale_price'] !== null ? $p['sale_price'] : "NULL";
    $cat = $p['category'] !== null ? "'" . addslashes($p['category']) . "'" : "NULL";
    $imgM = $p['image_main'] !== null ? "'" . addslashes($p['image_main']) . "'" : "NULL";
    $imgG = $p['image_gallery'] !== null ? "'" . addslashes($p['image_gallery']) . "'" : "NULL";
    $mTitle = $p['meta_title'] !== null ? "'" . addslashes($p['meta_title']) . "'" : "NULL";
    $mDesc = $p['meta_description'] !== null ? "'" . addslashes($p['meta_description']) . "'" : "NULL";
    $mKeys = $p['meta_keywords'] !== null ? "'" . addslashes($p['meta_keywords']) . "'" : "NULL";

    $sql .= "INSERT INTO `products` (`id`, `slug`, `name`, `short_description`, `long_description`, `price`, `sale_price`, `category`, `stock_quantity`, `image_main`, `image_gallery`, `meta_title`, `meta_description`, `meta_keywords`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES (";
    $sql .= "{$p['id']}, '" . addslashes($p['slug']) . "', '" . addslashes($p['name']) . "', ";
    $sql .= "{$sDesc}, {$lDesc}, {$p['price']}, {$saleP}, {$cat}, {$p['stock_quantity']}, ";
    $sql .= "{$imgM}, {$imgG}, {$mTitle}, {$mDesc}, {$mKeys}, {$p['is_featured']}, {$p['is_active']}, '{$p['created_at']}', '{$p['updated_at']}') ";
    $sql .= "ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `short_description`=VALUES(`short_description`), `long_description`=VALUES(`long_description`), `price`=VALUES(`price`), `sale_price`=VALUES(`sale_price`), `stock_quantity`=VALUES(`stock_quantity`), `image_main`=VALUES(`image_main`), `meta_title`=VALUES(`meta_title`), `meta_description`=VALUES(`meta_description`);\n";
}

$sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
file_put_contents($sqlFile, $sql);
echo "[3/5] Created SQL backup: {$sqlFile} (" . round(filesize($sqlFile)/1024, 1) . " KB)\n";

// ----------------------------------------------------
// 5. Backup Product Images & Media
// ----------------------------------------------------
echo "[4/5] Backing up Product Images into backups/product_images/ ...\n";
$productImagesSource = $rootDir . '/assets/products';
$copiedCount = 0;

if (is_dir($productImagesSource)) {
    $dirFiles = scandir($productImagesSource);
    foreach ($dirFiles as $f) {
        if ($f !== '.' && $f !== '..' && is_file($productImagesSource . '/' . $f)) {
            copy($productImagesSource . '/' . $f, $imagesBackupDir . '/' . $f);
            $copiedCount++;
        }
    }
}

// Also ensure the exact filenames referenced in products DB are present in assets/products
$productImageAliases = [
    'prod-almond-halwa-spread-1790077418.png' => 'almond_halwa_spread.jpg',
    'prod-cashew-coconut-spread-1790015793.png' => 'cashew_coconut_spread.jpg',
    'prod-almond-spread-1790015745.png' => 'crunchy_almond_spread.jpg',
    'prod-hazelnut-spread-clean-label-product-healthy-gifting-1790015567.png' => 'hazelnut_spread.jpg',
    'prod-gourmet-stuffed-dates-gift-box-1790015134.png' => 'gourmet_stuffed_dates.jpg',
    'prod-the-rocher-collection-1790015004.png' => 'the_rocher_collection.jpg',
    'prod-almond-bark-1790015356.png' => 'almond_bark.jpg',
    'prod-classic-almond-spread-1790078880.png' => 'almond_spread.jpg'
];

foreach ($productImageAliases as $aliasPng => $srcJpg) {
    $srcPath = $productImagesSource . '/' . $srcJpg;
    $aliasPath = $productImagesSource . '/' . $aliasPng;
    $backupAliasPath = $imagesBackupDir . '/' . $aliasPng;
    
    if (file_exists($srcPath)) {
        if (!file_exists($aliasPath)) {
            copy($srcPath, $aliasPath);
        }
        copy($srcPath, $backupAliasPath);
        $copiedCount++;
    }
}

echo "Backed up {$copiedCount} product image assets into backups/product_images/.\n";

// ----------------------------------------------------
// 6. Create Full ZIP Archive of Backup
// ----------------------------------------------------
echo "[5/5] Creating complete ZIP Archive ...\n";
$zipFile = $backupDir . '/rtchocos_products_and_content_backup_' . date('Ymd_His') . '.zip';
$latestZipFile = $backupDir . '/rtchocos_products_and_content_backup_latest.zip';

if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        $zip->addFile($sqlFile, 'products_and_categories_backup.sql');
        $zip->addFile($jsonFile, 'products_catalog_backup.json');
        $zip->addFile($mdFile, 'PRODUCT_CATALOG_CONTENT_BACKUP.md');

        // Add all images
        $imgFiles = scandir($imagesBackupDir);
        foreach ($imgFiles as $f) {
            if ($f !== '.' && $f !== '..' && is_file($imagesBackupDir . '/' . $f)) {
                $zip->addFile($imagesBackupDir . '/' . $f, 'product_images/' . $f);
            }
        }
        $zip->close();
        copy($zipFile, $latestZipFile);
        echo "Successfully created ZIP Archive: {$zipFile} (" . round(filesize($zipFile)/(1024*1024), 2) . " MB)\n";
    }
}

echo "\n=======================================================\n";
echo "BACKUP COMPLETED SUCCESSFULLY!\n";
echo "Location: d:\\Coding Projects\\AI LLM websites\\RTchocosReal\\backups\\\n";
echo "=======================================================\n";
