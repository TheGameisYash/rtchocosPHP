<?php
// includes/maintenance_check.php - Global Maintenance Interceptor & Gatekeeper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Check if maintenance mode is enabled
$maintenanceActive = (get_site_setting('maintenance_mode', '0') === '1');

if ($maintenanceActive) {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

    // 1. Never block /dev portal or /admin portal
    if (strpos($requestUri, '/dev') === 0 || strpos($requestUri, '/admin') === 0) {
        return;
    }

    // 2. Never block logged-in Developers (Apex Tier) or Admins
    if (!empty($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true) {
        return;
    }
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return;
    }

    // 3. Bypass secret key in URL (?bypass=SECRET_KEY)
    $bypassKey = get_site_setting('maintenance_bypass_key', 'rtdev2026');
    if (isset($_GET['bypass']) && $_GET['bypass'] === $bypassKey) {
        $_SESSION['maintenance_bypass'] = true;
        return;
    }

    // 4. Existing bypass session
    if (!empty($_SESSION['maintenance_bypass']) && $_SESSION['maintenance_bypass'] === true) {
        return;
    }

    // 5. Allow maintenance preview URL
    if ($requestUri === '/maintenance.php' || $requestUri === '/maintenance') {
        return;
    }

    // Site is in maintenance mode and visitor is not exempt:
    http_response_code(503);
    header('Retry-After: 3600');
    include __DIR__ . '/../maintenance.php';
    exit;
}
?>
