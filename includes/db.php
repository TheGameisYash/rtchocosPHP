<?php
// includes/db.php - Centralized PDO Database Connection

require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/blog-uploads.php';

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

function get_site_setting($key, $default = '', $forceReload = false) {
    static $settingsCache = null;
    if ($settingsCache !== null && !$forceReload) {
        return $settingsCache[$key] ?? $default;
    }

    $cacheFile = __DIR__ . '/../data/cache/site_settings.json';
    $cacheTtl = 1800; // 30 minutes

    // 1. Try reading from disk cache if fresh and not force-reloading
    if (!$forceReload && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
        $content = @file_get_contents($cacheFile);
        if ($content !== false) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $settingsCache = $decoded;
                return $settingsCache[$key] ?? $default;
            }
        }
    }

    // 2. Query database when cache is missing, expired, or force-reloaded
    try {
        $pdo = get_db();
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settingsCache = [];
        foreach ($rows as $row) {
            $settingsCache[$row['setting_key']] = $row['setting_value'];
        }

        // Persist to cache file
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($cacheFile, json_encode($settingsCache, JSON_UNESCAPED_SLASHES));
    } catch (Exception $e) {
        if ($settingsCache === null) {
            $settingsCache = [];
        }
    }

    return $settingsCache[$key] ?? $default;
}

function clear_site_settings_cache() {
    $cacheFile = __DIR__ . '/../data/cache/site_settings.json';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
    }
    get_site_setting('', '', true);
}
?>
