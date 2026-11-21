<?php
// admin/auth.php - Session management, CSRF validation, and Login rate limiting

if (session_status() === PHP_SESSION_NONE) {
    // Session cookie security options
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Only use secure cookies if HTTPS is enabled
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Restrict direct access to this file
if (basename($_SERVER['PHP_SELF']) == 'auth.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

// Check if user is logged in
function require_login() {
    // Apex Hierarchy: Developers automatically possess master access to admin portal
    if (isset($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true) {
        $_SESSION['admin_logged_in'] = true;
        if (empty($_SESSION['admin_user'])) {
            $_SESSION['admin_user'] = 'Dev Master (' . ($_SESSION['dev_user'] ?? 'dev') . ')';
        }
        return;
    }

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: /admin/login.php');
        exit;
    }

    // Check emergency session lockdown salt triggered by Developer
    require_once __DIR__ . '/../includes/db.php';
    $currentSalt = get_site_setting('admin_session_salt', '');
    if (!empty($currentSalt)) {
        if (!isset($_SESSION['admin_session_salt']) || $_SESSION['admin_session_salt'] !== $currentSalt) {
            unset($_SESSION['admin_logged_in']);
            unset($_SESSION['admin_user']);
            unset($_SESSION['admin_session_salt']);
            header('Location: /admin/login.php?error=lockdown');
            exit;
        }
    }
}

// Alias for require_login used in layout.php
function require_auth() {
    require_login();
}

// Check if admin or developer is currently logged in without redirecting
function is_admin_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    return (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
        || (!empty($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true);
}

// CSRF token generation
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Alias for get_csrf_token used in various admin files
function generate_csrf() {
    return get_csrf_token();
}

// CSRF token verification
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Alias for verify_csrf_token used in various admin files
function verify_csrf($token) {
    return verify_csrf_token($token);
}

// Check login lockout status (5 failed attempts -> 15 min lock)
function is_login_locked() {
    if (isset($_SESSION['login_locked_until']) && time() < $_SESSION['login_locked_until']) {
        return true;
    }
    // Lock expired, reset
    if (isset($_SESSION['login_locked_until']) && time() >= $_SESSION['login_locked_until']) {
        unset($_SESSION['login_locked_until']);
        unset($_SESSION['failed_logins']);
    }
    return false;
}

// Record a failed login attempt
function record_failed_login() {
    if (!isset($_SESSION['failed_logins'])) {
        $_SESSION['failed_logins'] = 0;
    }
    $_SESSION['failed_logins']++;
    if ($_SESSION['failed_logins'] >= 5) {
        $_SESSION['login_locked_until'] = time() + (15 * 60);
    }
}

// Reset login attempts after successful login
function reset_failed_logins() {
    unset($_SESSION['failed_logins']);
    unset($_SESSION['login_locked_until']);
}

// Robust MIME type detection helper with multiple fallbacks (no recursion)
function get_file_mime_type($filePath, $originalFileName = '') {
    // 1. Try finfo if available (standard PHP Fileinfo extension)
    if (class_exists('finfo')) {
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($filePath);
            if (!empty($mime) && $mime !== 'application/octet-stream') {
                return $mime;
            }
        } catch (\Exception $e) { }
    }

    // 2. Try getimagesize for images (reads file header magic bytes directly from disk)
    if (function_exists('getimagesize')) {
        try {
            $imgInfo = @getimagesize($filePath);
            if (!empty($imgInfo['mime'])) {
                return $imgInfo['mime'];
            }
        } catch (\Exception $e) { }
    }

    // 3. Try exif_imagetype
    if (function_exists('exif_imagetype')) {
        try {
            $type = @exif_imagetype($filePath);
            if ($type === IMAGETYPE_JPEG) return 'image/jpeg';
            if ($type === IMAGETYPE_PNG) return 'image/png';
            if ($type === IMAGETYPE_WEBP) return 'image/webp';
            if ($type === IMAGETYPE_GIF) return 'image/gif';
        } catch (\Exception $e) { }
    }

    // 4. Fallback based on extension
    $fileName = $originalFileName ?: $filePath;
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'pdf' => 'application/pdf',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm'
    ];

    return $mimeTypes[$ext] ?? 'application/octet-stream';
}

// Global polyfill for mime_content_type when fileinfo extension is disabled in PHP
if (!function_exists('mime_content_type')) {
    function mime_content_type($filename) {
        return get_file_mime_type($filename);
    }
}
?>
