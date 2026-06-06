<?php
// env_loader.php - Lightweight native PHP .env loader

function load_env_file($path = null) {
    if ($path === null) {
        // Check local folder first, then search up
        if (file_exists(__DIR__ . '/../.env')) {
            $path = __DIR__ . '/../.env';
        } elseif (file_exists(__DIR__ . '/../../.env')) {
            // Support call from sub-directories like blog/
            $path = __DIR__ . '/../../.env';
        } else {
            return false;
        }
    }

    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and lines without '='
        if (empty($line) || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        // Split by the first '=' to allow value containing '='
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove surrounding quotes if present
        $value = trim($value, '"\'');

        // Populate environment variables
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    return true;
}

// Auto-execute on include
load_env_file();
