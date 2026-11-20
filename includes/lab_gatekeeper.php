<?php
// includes/lab_gatekeeper.php - Innovation Lab Access Control & Coming Soon Gatekeeper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Checks whether the current visitor has access to the live Innovation Lab,
 * or if they should be presented with the Coming Soon experience.
 *
 * @return array Gatekeeper status and permissions
 */
function get_innovation_lab_access() {
    $mode = get_site_setting('innovation_lab_mode', 'coming_soon');

    // 1. Check logged-in Admin or Developer session
    $isAdmin = (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true)
            || (!empty($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true)
            || !empty($_SESSION['admin_user']);

    // 2. Check secret bypass or preview parameter
    $bypassKey = get_site_setting('maintenance_bypass_key', 'rtdev2026');
    $hasPreviewParam = (isset($_GET['preview']) && in_array(strtolower((string)$_GET['preview']), ['1', 'true', 'admin', 'rtdev2026', 'preview', 'yes']))
                    || (isset($_GET['bypass']) && $_GET['bypass'] === $bypassKey);

    if ($hasPreviewParam) {
        $_SESSION['lab_preview_bypass'] = true;
    }

    $hasSessionBypass = !empty($_SESSION['lab_preview_bypass']) && $_SESSION['lab_preview_bypass'] === true;

    // 3. Allow authorized users to simulate the public Coming Soon view using ?view_as=public or ?public=1
    $forcePublicView = (isset($_GET['view_as']) && strtolower((string)$_GET['view_as']) === 'public')
                    || (isset($_GET['public']) && $_GET['public'] === '1');

    $isAuthorized = ($isAdmin || $hasPreviewParam || $hasSessionBypass);
    $canAccessLive = $isAuthorized && !$forcePublicView;
    $isComingSoonForVisitor = ($mode === 'coming_soon') && !$canAccessLive;
    $isPreviewActive = ($mode === 'coming_soon') && $canAccessLive;

    return [
        'mode'                     => $mode,                    // 'coming_soon' | 'live'
        'can_access_live'          => $canAccessLive,           // true if authorized to see live page
        'is_coming_soon'           => $isComingSoonForVisitor,  // true if visitor should see coming soon screen
        'is_preview_active'        => $isPreviewActive,         // true if viewing live page while in coming soon mode
        'is_authorized'            => $isAuthorized,            // true if admin/dev/preview
        'simulating_public'        => $forcePublicView && $isAuthorized
    ];
}
