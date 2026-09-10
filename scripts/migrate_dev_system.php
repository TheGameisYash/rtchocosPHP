<?php
// scripts/migrate_dev_system.php - Migration to create developers table, update admins, and add dev site settings
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = get_db();
    echo "Starting Developer System & Maintenance Mode Migration...\n";

    // 1. Create developers table
    echo "Creating 'developers' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS developers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(150) NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'lead_developer',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "OK\n";

    // 2. Seed default developer account if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM developers");
    if ($stmt->fetchColumn() == 0) {
        $devUser = 'dev';
        $devPass = 'Dev@rtchocosMaster1';
        $hash = password_hash($devPass, PASSWORD_BCRYPT);
        
        $ins = $pdo->prepare("INSERT INTO developers (username, password, email, role, is_active) VALUES (?, ?, ?, 'super_dev', 1)");
        $ins->execute([$devUser, $hash, 'dev@rtchocos.com']);
        echo "Created default developer account: '{$devUser}'\n";
    } else {
        echo "Developer account already exists.\n";
    }

    // 3. Add is_active column to admins table if not exists
    $adminCols = $pdo->query("SHOW COLUMNS FROM admins")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_active', $adminCols)) {
        echo "Adding 'is_active' column to 'admins'... ";
        $pdo->exec("ALTER TABLE admins ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;");
        echo "OK\n";
    } else {
        echo "'is_active' column already exists in 'admins'.\n";
    }

    if (!in_array('last_login', $adminCols)) {
        echo "Adding 'last_login' column to 'admins'... ";
        $pdo->exec("ALTER TABLE admins ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;");
        echo "OK\n";
    } else {
        echo "'last_login' column already exists in 'admins'.\n";
    }

    if (!in_array('password_vault', $adminCols)) {
        echo "Adding 'password_vault' column to 'admins'... ";
        $pdo->exec("ALTER TABLE admins ADD COLUMN password_vault VARCHAR(255) NULL;");
        echo "OK\n";
    }

    $devCols = $pdo->query("SHOW COLUMNS FROM developers")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('password_vault', $devCols)) {
        echo "Adding 'password_vault' column to 'developers'... ";
        $pdo->exec("ALTER TABLE developers ADD COLUMN password_vault VARCHAR(255) NULL;");
        echo "OK\n";
    }

    // 4. Populate default site_settings for maintenance mode and URLs
    $newSettings = [
        'site_url' => 'https://www.rtchocos.com',
        'beta_url' => 'https://www.rtchocos.com/beta',
        'maintenance_mode' => '0',
        'maintenance_title' => "We're Perfecting Something Delicious",
        'maintenance_subtitle' => 'Our chocolate laboratory is currently undergoing planned improvements.',
        'maintenance_message' => "We are fine-tuning our handcrafted batches and platform to bring you an even more delightful bean-to-bar experience. We will be back online shortly!",
        'maintenance_eta' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'maintenance_media_type' => 'both',
        'maintenance_image' => 'assets/ph.png',
        'maintenance_video' => 'https://www.youtube.com/watch?v=kY3Pvdq0YpY',
        'maintenance_bypass_key' => 'rtdev2026',
        'maintenance_notify_enabled' => '1'
    ];

    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
    $insertStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");

    foreach ($newSettings as $key => $val) {
        $checkStmt->execute([$key]);
        if ($checkStmt->fetchColumn() == 0) {
            $insertStmt->execute([$key, $val]);
            echo "Added setting: {$key}\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
