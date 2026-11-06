<?php
// includes/beta_lock.php - RT Chocos Beta Website Security Lock & Access Gatekeeper

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Detects whether the current execution is running in the Beta environment.
 * Checks HTTP host, request path, filesystem directory (Hostinger public_html/beta),
 * environment variables, and git branch (local dev).
 */
function is_beta_environment() {
    static $isBeta = null;
    if ($isBeta !== null) {
        return $isBeta;
    }

    // 1. Explicit environment variable check (.env)
    if (isset($_ENV['IS_BETA'])) {
        $val = filter_var($_ENV['IS_BETA'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($val !== null) {
            return $isBeta = $val;
        }
    }
    if (isset($_ENV['APP_ENV'])) {
        $appEnv = strtolower(trim($_ENV['APP_ENV']));
        if ($appEnv === 'production' || $appEnv === 'prod') {
            return $isBeta = false;
        }
        if ($appEnv === 'beta' || $appEnv === 'staging') {
            return $isBeta = true;
        }
    }
    if (isset($_ENV['ENVIRONMENT'])) {
        $env = strtolower(trim($_ENV['ENVIRONMENT']));
        if ($env === 'production' || $env === 'prod') {
            return $isBeta = false;
        }
        if ($env === 'beta' || $env === 'staging') {
            return $isBeta = true;
        }
    }

    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $host = strtolower($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
    $hostOnly = explode(':', $host)[0];

    // 2. Production domain whitelist:
    // If request is explicitly on rtchocos.com or www.rtchocos.com AND NOT under the /beta subpath, it is production.
    $isPrimaryProdDomain = ($hostOnly === 'rtchocos.com' || $hostOnly === 'www.rtchocos.com');
    if ($isPrimaryProdDomain && !preg_match('#^/beta(/|\?|$)#i', $uri)) {
        return $isBeta = false;
    }

    // 3. Request URI path detection (e.g. /beta/ or /beta on any host)
    if (preg_match('#^/beta(/|\?|$)#i', $uri)) {
        return $isBeta = true;
    }

    // 4. HTTP Host / Subdomain detection (e.g. beta.rtchocos.com, beta-*, *.beta.*)
    if (!empty($hostOnly)) {
        if (strpos($hostOnly, 'beta.') === 0 || strpos($hostOnly, 'beta-') === 0 || preg_match('/(^|\.)beta(\.|$)/i', $hostOnly)) {
            return $isBeta = true;
        }
    }

    // 5. Filesystem path detection (Hostinger remote_path: domains/rtchocos.com/public_html/beta)
    $root = str_replace('\\', '/', __DIR__);
    if (preg_match('#/public_html/beta(/|$)#i', $root) || preg_match('#/domains/[^/]+/public_html/beta#i', $root)) {
        return $isBeta = true;
    }
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    if (preg_match('#/public_html/beta(/|$)#i', $docRoot)) {
        return $isBeta = true;
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    if (preg_match('#/public_html/beta(/|$)#i', $script)) {
        return $isBeta = true;
    }

    // 6. Git branch detection for local development (branch: beta)
    // Only applied if not explicitly accessing the production hostname
    if (!$isPrimaryProdDomain) {
        $gitHead = dirname(__DIR__) . '/.git/HEAD';
        if (file_exists($gitHead)) {
            $headContent = @file_get_contents($gitHead);
            if ($headContent !== false && strpos($headContent, 'refs/heads/beta') !== false) {
                return $isBeta = true;
            }
        }
    }

    return $isBeta = false;
}

/**
 * Checks whether Beta Locking is enabled in site settings / configuration.
 * Defaults to TRUE (locked) whenever running in the Beta environment.
 */
function is_beta_lock_enabled() {
    // If not on beta environment, never lock
    if (!is_beta_environment()) {
        return false;
    }

    // Check .env override
    if (isset($_ENV['LOCK_BETA'])) {
        return filter_var($_ENV['LOCK_BETA'], FILTER_VALIDATE_BOOLEAN);
    }

    // Check database setting if available; defaults to '1' (enabled)
    if (function_exists('get_site_setting')) {
        $val = get_site_setting('beta_locked', '1');
        return ($val === '1' || $val === 'true' || $val === true || $val === 1);
    }

    return true;
}

/**
 * Computes a secure signature for persistent browser unlock cookies.
 */
function get_beta_cookie_signature($secretKey) {
    return hash_hmac('sha256', 'rtchocos_beta_unlocked_payload', $secretKey);
}

/**
 * Checks whether the current visitor is authorized to access the Beta site.
 */
function is_beta_authorized() {
    // 1. Logged in Developers (Root Dev Portal)
    if (!empty($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true) {
        return true;
    }

    // 2. Logged in Administrators (Admin CMS)
    if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }

    // 3. Active session unlock
    if (!empty($_SESSION['beta_authorized']) && $_SESSION['beta_authorized'] === true) {
        return true;
    }

    // Retrieve accepted passcodes
    $secretCode = function_exists('get_site_setting') ? get_site_setting('beta_access_code', 'rtbeta2026') : 'rtbeta2026';
    $bypassKey = function_exists('get_site_setting') ? get_site_setting('maintenance_bypass_key', 'rtdev2026') : 'rtdev2026';
    $validKeys = array_filter([
        $secretCode,
        $bypassKey,
        $_ENV['BETA_PASSCODE'] ?? null,
        $_ENV['ADMIN_PASS'] ?? null
    ]);

    // 4. URL query bypass parameter (?unlock=KEY, ?beta_pass=KEY, or ?bypass=KEY)
    $suppliedKey = $_GET['unlock'] ?? $_GET['beta_pass'] ?? $_GET['bypass'] ?? null;
    if ($suppliedKey !== null) {
        foreach ($validKeys as $vk) {
            if (hash_equals((string)$vk, (string)$suppliedKey)) {
                $_SESSION['beta_authorized'] = true;
                set_beta_access_cookie($secretCode);
                return true;
            }
        }
    }

    // 5. Persistent signed cookie check
    if (!empty($_COOKIE['rt_beta_access'])) {
        $expectedSig = get_beta_cookie_signature($secretCode);
        if (hash_equals($expectedSig, (string)$_COOKIE['rt_beta_access'])) {
            $_SESSION['beta_authorized'] = true;
            return true;
        }
        // Also check against dev bypass key
        $devSig = get_beta_cookie_signature($bypassKey);
        if (hash_equals($devSig, (string)$_COOKIE['rt_beta_access'])) {
            $_SESSION['beta_authorized'] = true;
            return true;
        }
    }

    return false;
}

/**
 * Sets a persistent browser cookie for authorized access.
 */
function set_beta_access_cookie($secretCode, $days = 30) {
    $sig = get_beta_cookie_signature($secretCode);
    $expires = time() + (86400 * $days);
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    setcookie('rt_beta_access', $sig, [
        'expires' => $expires,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

/**
 * Clears authorized access session and cookie (re-locks the website for this browser).
 */
function clear_beta_authorization() {
    unset($_SESSION['beta_authorized']);
    if (isset($_COOKIE['rt_beta_access'])) {
        setcookie('rt_beta_access', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['rt_beta_access']);
    }
}

/**
 * Master Beta Lockdown Gatekeeper.
 * Call this before serving any page or API response.
 */
function check_beta_lock() {
    static $alreadyChecked = false;
    if ($alreadyChecked) {
        return;
    }
    $alreadyChecked = true;

    // 1. Skip CLI scripts (unless explicitly testing HTTP simulation)
    if (php_sapi_name() === 'cli' && empty($_SERVER['TEST_HTTP_SIMULATION'])) {
        return;
    }

    // 2. Only enforce if running in Beta environment and lock is enabled
    if (!is_beta_environment() || !is_beta_lock_enabled()) {
        return;
    }

    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    // 3. Static assets bypass (CSS, JS, images, fonts, icons, videos)
    if (preg_match('/\.(?:css|js|png|jpe?g|gif|webp|svg|woff2?|ttf|eot|ico|mp4|webm|map|json)$/i', $requestUri)) {
        return;
    }

    // 4. Admin and Dev management portals are NEVER locked out
    if (preg_match('#^/(?:beta/)?(admin|dev)(/|\?|$)#i', $requestUri)) {
        return;
    }

    // 5. Handle manual re-lock parameter (?lock_beta=1 or ?relock=1)
    if (isset($_GET['lock_beta']) || isset($_GET['relock'])) {
        clear_beta_authorization();
        $cleanUrl = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        header('Location: ' . ($cleanUrl ?: '/'));
        exit;
    }

    $unlockError = '';

    // 6. Handle unlock form POST submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unlock_beta') {
        $enteredPasscode = trim($_POST['beta_passcode'] ?? '');
        $rememberMe = isset($_POST['remember_unlock']);

        $secretCode = function_exists('get_site_setting') ? get_site_setting('beta_access_code', 'rtbeta2026') : 'rtbeta2026';
        $bypassKey = function_exists('get_site_setting') ? get_site_setting('maintenance_bypass_key', 'rtdev2026') : 'rtdev2026';

        $validKeys = array_filter([
            $secretCode,
            $bypassKey,
            $_ENV['BETA_PASSCODE'] ?? null,
            $_ENV['ADMIN_PASS'] ?? null
        ]);

        $matched = false;
        foreach ($validKeys as $vk) {
            if (!empty($enteredPasscode) && hash_equals((string)$vk, (string)$enteredPasscode)) {
                $matched = true;
                break;
            }
        }

        if ($matched) {
            $_SESSION['beta_authorized'] = true;
            if ($rememberMe) {
                set_beta_access_cookie($secretCode, 30);
            }
            // Redirect to clean requested URL to avoid form resubmission
            $redirectTarget = $_POST['redirect_target'] ?? $_SERVER['REQUEST_URI'] ?? '/';
            // Strip any action query from redirect
            $cleanRedirect = preg_replace('/(\?|&)(?:lock_beta|relock|action)=[^&]*/', '', $redirectTarget);
            header('Location: ' . ($cleanRedirect ?: '/'));
            exit;
        } else {
            $unlockError = 'Incorrect access passcode. Please check with Aarti or development team.';
        }
    }

    // 7. Check authorization
    if (is_beta_authorized()) {
        return;
    }

    // 8. If unauthorized: check if this is an API / AJAX request
    $isJson = (
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        preg_match('#^/(?:beta/)?(api_|api/)#i', $requestUri)
    );

    if ($isJson) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        header('X-Robots-Tag: noindex, nofollow, noarchive');
        echo json_encode([
            'success' => false,
            'locked' => true,
            'environment' => 'beta',
            'error' => 'Beta staging environment is locked. Public access is restricted.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // 9. Unauthorized web visitor: render Luxury Beta Lock Screen with 403 Forbidden
    http_response_code(403);
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    render_beta_lock_screen($unlockError);
    exit;
}

/**
 * Renders the ultra-luxury RT Chocos Beta Lock Screen.
 */
function render_beta_lock_screen($error = '') {
    $mainSiteUrl = function_exists('get_site_setting') ? get_site_setting('site_url', 'https://www.rtchocos.com') : 'https://www.rtchocos.com';
    $currentUri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Beta Staging | RT Chocos Luxury Artisan Chocolate</title>
    <meta name="robots" content="noindex, nofollow, noarchive">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #080503;
            --cocoa-deep: #120b07;
            --cocoa-card: rgba(22, 14, 9, 0.82);
            --gold: #d4af37;
            --gold-bright: #f5d77f;
            --gold-glow: rgba(212, 175, 55, 0.28);
            --border-gold: rgba(212, 175, 55, 0.22);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --text-primary: #fcf8f2;
            --text-secondary: #c9b9ac;
            --text-muted: #8d7a6e;
            --crimson: #ef4444;
            --crimson-bg: rgba(239, 68, 68, 0.12);
            --crimson-border: rgba(239, 68, 68, 0.35);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-dark);
            background-image:
                radial-gradient(circle at 15% 20%, rgba(212, 175, 55, 0.10) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(139, 69, 19, 0.18) 0%, transparent 55%),
                radial-gradient(circle at 50% 50%, #150d08 0%, var(--bg-dark) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle animated background grid line */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(rgba(212, 175, 55, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(212, 175, 55, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
            z-index: 1;
        }

        .lock-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 520px;
            background: var(--cocoa-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-gold);
            border-radius: 24px;
            padding: 44px 36px 36px;
            box-shadow: 
                0 25px 60px rgba(0, 0, 0, 0.75),
                0 0 80px rgba(212, 175, 55, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.12);
            text-align: center;
        }

        /* Brand Crest Header */
        .brand-crest {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: radial-gradient(circle, #24160e 0%, #110906 100%);
            border: 1.5px solid var(--gold);
            box-shadow: 
                0 0 24px var(--gold-glow),
                inset 0 0 12px rgba(212, 175, 55, 0.2);
            margin-bottom: 22px;
            position: relative;
        }

        .brand-crest svg {
            width: 38px;
            height: 38px;
            color: var(--gold);
            filter: drop-shadow(0 2px 8px rgba(212, 175, 55, 0.5));
        }

        .brand-crest .badge-pulse {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ef4444;
            border: 2px solid var(--bg-dark);
            box-shadow: 0 0 8px #ef4444;
            animation: pulseDot 2s infinite ease-in-out;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.25); opacity: 0.7; }
        }

        /* Pill status tag */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 14px;
            border-radius: 999px;
            background: rgba(212, 175, 55, 0.08);
            border: 1px solid rgba(212, 175, 55, 0.28);
            color: var(--gold-bright);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .status-pill::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 6px var(--gold);
        }

        /* Typography */
        h1.lock-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            line-height: 1.25;
            margin-bottom: 10px;
        }

        h1.lock-title span {
            color: var(--gold);
            font-style: italic;
        }

        p.lock-desc {
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--text-secondary);
            margin-bottom: 28px;
            padding: 0 8px;
        }

        /* Error notification */
        .error-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--crimson-bg);
            border: 1px solid var(--crimson-border);
            color: #fca5a5;
            padding: 11px 14px;
            border-radius: 12px;
            font-size: 12.5px;
            line-height: 1.4;
            margin-bottom: 20px;
            text-align: left;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        .error-alert svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            color: var(--crimson);
        }

        /* Passcode Form */
        .passcode-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 24px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: -6px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s;
        }

        .passcode-input {
            width: 100%;
            background: rgba(10, 6, 4, 0.7);
            border: 1px solid var(--border-gold);
            border-radius: 12px;
            padding: 13px 44px 13px 42px;
            color: #ffffff;
            font-size: 15px;
            font-family: inherit;
            letter-spacing: 0.04em;
            transition: all 0.25s ease;
            outline: none;
        }

        .passcode-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.18), 0 0 20px rgba(212, 175, 55, 0.12);
            background: rgba(15, 9, 6, 0.9);
        }

        .passcode-input::placeholder {
            color: rgba(201, 185, 172, 0.35);
            font-size: 13.5px;
        }

        .toggle-pw-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.2s;
        }

        .toggle-pw-btn:hover {
            color: var(--gold);
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-secondary);
            padding: 0 2px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            accent-color: var(--gold);
            width: 14px;
            height: 14px;
            cursor: pointer;
        }

        /* Submit Button */
        .btn-unlock {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            padding: 13px 24px;
            background: linear-gradient(135deg, #d4af37 0%, #b89127 100%);
            border: none;
            border-radius: 12px;
            color: #0b0704;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(212, 175, 55, 0.3);
            text-decoration: none;
        }

        .btn-unlock:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.45);
            filter: brightness(1.08);
        }

        .btn-unlock:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 24px 0 20px;
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-subtle);
        }

        /* Secondary actions */
        .official-site-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            text-align: left;
            margin-bottom: 20px;
            transition: border-color 0.2s, background 0.2s;
        }

        .official-site-card:hover {
            border-color: rgba(212, 175, 55, 0.35);
            background: rgba(212, 175, 55, 0.03);
        }

        .official-text .title {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-primary);
            display: block;
        }

        .official-text .sub {
            font-size: 11px;
            color: var(--text-muted);
        }

        .btn-live {
            padding: 7px 13px;
            background: transparent;
            border: 1px solid var(--gold);
            color: var(--gold-bright);
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .btn-live:hover {
            background: var(--gold);
            color: #090503;
        }

        /* Bottom Footer */
        .lock-footer {
            font-size: 11.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .lock-footer a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .lock-footer a:hover {
            color: var(--gold);
            text-decoration: underline;
        }

        .security-badge {
            margin-top: 14px;
            font-size: 10px;
            color: rgba(141, 122, 110, 0.6);
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="lock-container">
        <!-- Brand Shield Crest -->
        <div class="brand-crest">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                <circle cx="12" cy="16" r="1.5"></circle>
            </svg>
            <span class="badge-pulse" title="Beta Lockdown Protocol Active"></span>
        </div>

        <!-- Staging Indicator Pill -->
        <div>
            <span class="status-pill">Private Staging Environment</span>
        </div>

        <h1 class="lock-title">RT Chocos <span>Beta Access Locked</span></h1>
        
        <p class="lock-desc">
            This staging platform is restricted for chocolate recipe R&amp;D, sensory formulation trials, and internal engineering. Public access is currently locked.
        </p>

        <?php if (!empty($error)): ?>
            <div class="error-alert" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Passcode Unlock Form -->
        <form class="passcode-form" method="POST" action="">
            <input type="hidden" name="action" value="unlock_beta">
            <input type="hidden" name="redirect_target" value="<?php echo $currentUri; ?>">
            
            <label class="form-label" for="beta_passcode">Team Access Key</label>
            <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2l-2 2m-2-2l2 2m0 0l-3.5 3.5M16 5l3 3M4 21l7-7m0 0a5 5 0 10-7-7 5 5 0 007 7z"></path>
                </svg>
                <input 
                    type="password" 
                    id="beta_passcode" 
                    name="beta_passcode" 
                    class="passcode-input" 
                    placeholder="Enter beta access key..." 
                    required 
                    autofocus
                    autocomplete="current-password"
                >
                <button type="button" class="toggle-pw-btn" onclick="togglePasscodeVisibility()" aria-label="Toggle password visibility">
                    <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember_unlock" value="1" checked>
                    <span>Remember on this browser (30 days)</span>
                </label>
            </div>

            <button type="submit" class="btn-unlock">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                </svg>
                <span>Unlock Staging Platform</span>
            </button>
        </form>

        <div class="divider">Or visit live brand</div>

        <!-- Official Store Card -->
        <div class="official-site-card">
            <div class="official-text">
                <span class="title">Looking for RT Chocos?</span>
                <span class="sub">Our official bean-to-bar store is live &amp; public</span>
            </div>
            <a href="<?php echo htmlspecialchars($mainSiteUrl); ?>" class="btn-live">
                <span>Visit Store &rarr;</span>
            </a>
        </div>

        <!-- Footer / Staff Gateway -->
        <div class="lock-footer">
            <a href="/admin/login.php">Staff CMS Login</a>
            <span>&bull;</span>
            <a href="/dev/login.php">Developer Portal</a>
        </div>

        <div class="security-badge">
            Security Isolation Active &bull; HTTP 403 Restricted &bull; RT Chocos R&amp;D
        </div>
    </div>

    <script>
        function togglePasscodeVisibility() {
            var input = document.getElementById('beta_passcode');
            var icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>
</html>
    <?php
}
