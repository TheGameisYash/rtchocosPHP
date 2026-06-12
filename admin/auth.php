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
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

// Alias for require_login used in layout.php
function require_auth() {
    require_login();
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
?>
