<?php
// dev/logout.php - Developer Portal Logout
require_once __DIR__ . '/auth.php';

unset($_SESSION['dev_logged_in']);
unset($_SESSION['dev_user']);
unset($_SESSION['dev_role']);
unset($_SESSION['dev_csrf_token']);

// Also remove remember cookie if set
if (isset($_COOKIE['dev_remember'])) {
    setcookie('dev_remember', '', time() - 3600, '/');
}

header('Location: /dev/login.php');
exit;
?>
