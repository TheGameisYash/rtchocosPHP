<?php
// scripts/setup_categories.php
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = get_db();
    
    // Create product_categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "Table product_categories created or verified.\n";
    
    // Seed existing categories from products
    $existing = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);
    $defaultPresets = ['Chocolates', 'Bonbons', 'Cacao', 'Kits', 'Gifting', 'Spreads'];
    $allToSeed = array_unique(array_merge($defaultPresets, $existing));
    
    $insert = $pdo->prepare("INSERT IGNORE INTO product_categories (name, slug) VALUES (?, ?)");
    $count = 0;
    foreach ($allToSeed as $cat) {
        $cat = trim($cat);
        if ($cat === '') continue;
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cat), '-'));
        $insert->execute([$cat, $slug]);
        $count++;
    }
    
    echo "Seeded $count product categories successfully.\n";
    
    $rows = $pdo->query("SELECT id, name, slug FROM product_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
