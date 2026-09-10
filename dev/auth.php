<?php
// dev/auth.php - Developer Master Portal Session, CSRF and Security Engine

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Restrict direct access
if (basename($_SERVER['PHP_SELF']) === 'auth.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Forbidden');
}

// Check if developer is logged in
function require_dev_auth() {
    if (!isset($_SESSION['dev_logged_in']) || $_SESSION['dev_logged_in'] !== true) {
        header('Location: /dev/login.php');
        exit;
    }
    
    // Automatically grant master admin session so dev has single sign-on over /admin
    $_SESSION['admin_logged_in'] = true;
    if (empty($_SESSION['admin_user'])) {
        $_SESSION['admin_user'] = $_SESSION['dev_user'] ?? 'Developer';
    }
}

function require_dev_login() {
    require_dev_auth();
}

function is_dev_logged_in() {
    return isset($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true;
}

function get_current_dev_user() {
    return $_SESSION['dev_user'] ?? 'Developer';
}

function is_root_dev() {
    if (!is_dev_logged_in()) {
        return false;
    }
    $user = $_SESSION['dev_user'] ?? '';
    if ($user === 'dev') {
        return true;
    }
    $role = strtolower($_SESSION['dev_role'] ?? '');
    if (in_array($role, ['root_dev', 'super_dev', 'root'])) {
        return true;
    }
    // Live DB verification
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT role FROM developers WHERE username = ?");
        $stmt->execute([$user]);
        $dbRole = strtolower($stmt->fetchColumn() ?: '');
        if (in_array($dbRole, ['root_dev', 'super_dev', 'root'])) {
            $_SESSION['dev_role'] = $dbRole;
            return true;
        }
    } catch (Exception $e) {}
    return false;
}

function require_root_dev() {
    require_dev_auth();
    if (!is_root_dev()) {
        header('HTTP/1.0 403 Forbidden');
        exit('Access Denied: Root Developer privileges required.');
    }
}

// CSRF token generation
function get_dev_csrf_token() {
    if (empty($_SESSION['dev_csrf_token'])) {
        $_SESSION['dev_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['dev_csrf_token'];
}

function generate_csrf() {
    return get_dev_csrf_token();
}

// CSRF token verification
function verify_dev_csrf_token($token) {
    if (!isset($_SESSION['dev_csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['dev_csrf_token'], $token);
}

function verify_csrf($token) {
    return verify_dev_csrf_token($token);
}

// Dev login rate limiting (5 failed attempts -> 15 min lockout)
function is_dev_login_locked() {
    if (isset($_SESSION['dev_lockout_until']) && time() < $_SESSION['dev_lockout_until']) {
        return true;
    }
    if (isset($_SESSION['dev_lockout_until']) && time() >= $_SESSION['dev_lockout_until']) {
        unset($_SESSION['dev_lockout_until']);
        unset($_SESSION['dev_failed_attempts']);
    }
    return false;
}

function record_failed_dev_login() {
    if (!isset($_SESSION['dev_failed_attempts'])) {
        $_SESSION['dev_failed_attempts'] = 0;
    }
    $_SESSION['dev_failed_attempts']++;
    if ($_SESSION['dev_failed_attempts'] >= 5) {
        $_SESSION['dev_lockout_until'] = time() + (15 * 60);
    }
}

function reset_failed_dev_logins() {
    unset($_SESSION['dev_failed_attempts']);
    unset($_SESSION['dev_lockout_until']);
}
?>
