<?php
// includes/db.php - Centralized PDO Database Connection

require_once __DIR__ . '/env_loader.php';

function get_db() {
    static $pdo = null;
    if ($pdo === null) {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $db   = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            error_log("Database Connection Failed: " . $e->getMessage());
            throw $e;
        }
    }
    return $pdo;
}

function get_site_setting($key, $default = '') {
    static $settingsCache = null;
    if ($settingsCache === null) {
        try {
            $pdo = get_db();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $settingsCache = [];
            foreach ($rows as $row) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }
    return $settingsCache[$key] ?? $default;
}
?>
