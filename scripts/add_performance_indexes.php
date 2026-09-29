<?php
// scripts/add_performance_indexes.php - Add performance indexes to database tables
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = get_db();
    echo "Connected to database successfully.\n";

    $indexes = [
        ['blogs', 'idx_blogs_pub_date', 'CREATE INDEX idx_blogs_pub_date ON blogs (is_published, created_at)'],
        ['blogs', 'idx_blogs_cat', 'CREATE INDEX idx_blogs_cat ON blogs (category)'],
        ['contacts', 'idx_contacts_is_read', 'CREATE INDEX idx_contacts_is_read ON contacts (is_read)'],
        ['orders', 'idx_orders_status', 'CREATE INDEX idx_orders_status ON orders (order_status)'],
        ['orders', 'idx_orders_created', 'CREATE INDEX idx_orders_created ON orders (created_at)']
    ];

    foreach ($indexes as $item) {
        list($table, $indexName, $sql) = $item;
        
        // Check if index already exists
        $checkStmt = $pdo->prepare("SHOW INDEX FROM `$table` WHERE Key_name = ?");
        $checkStmt->execute([$indexName]);
        if ($checkStmt->fetch()) {
            echo "Index '$indexName' on '$table' already exists. Skipping.\n";
            continue;
        }

        try {
            $pdo->exec($sql);
            echo "Successfully created index '$indexName' on '$table'.\n";
        } catch (Exception $ex) {
            echo "Could not create index '$indexName' on '$table': " . $ex->getMessage() . "\n";
        }
    }

    echo "Performance indexing completed.\n";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
