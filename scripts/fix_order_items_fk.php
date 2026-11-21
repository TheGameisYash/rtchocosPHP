<?php
// scripts/fix_order_items_fk.php - Migration to allow safe deletion of products with order history
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = get_db();
    echo "Connecting to database...\n";

    // 1. Make order_items.product_id nullable
    echo "Modifying order_items.product_id to INT(11) NULL...\n";
    $pdo->exec("ALTER TABLE `order_items` MODIFY `product_id` INT(11) NULL;");
    echo "Column modified successfully.\n";

    // 2. Drop existing foreign key constraint
    echo "Dropping old foreign key order_items_ibfk_2...\n";
    try {
        $pdo->exec("ALTER TABLE `order_items` DROP FOREIGN KEY `order_items_ibfk_2`;");
        echo "Old foreign key dropped.\n";
    } catch (Exception $e) {
        echo "Notice: Could not drop constraint (may have different name or already dropped): " . $e->getMessage() . "\n";
    }

    // 3. Add new foreign key with ON DELETE SET NULL
    echo "Adding new foreign key with ON DELETE SET NULL...\n";
    $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;");
    echo "New foreign key constraint added successfully!\n";

    // 4. Verify updated table definition
    $res = $pdo->query("SHOW CREATE TABLE `order_items`")->fetch(PDO::FETCH_ASSOC);
    echo "\nUpdated Schema for order_items:\n";
    echo $res['Create Table'] . "\n";

    echo "\nMigration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
