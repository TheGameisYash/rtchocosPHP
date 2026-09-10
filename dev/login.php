<?php
// dev/login.php - Developer Master Control Portal Login
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// If already logged in as developer, redirect to dev dashboard
if (isset($_SESSION['dev_logged_in']) && $_SESSION['dev_logged_in'] === true) {
    header('Location: /dev/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    
    if (!verify_dev_csrf_token($token)) {
        $error = 'Security session expired or invalid. Please reload.';
    } elseif (is_dev_login_locked()) {
        $minsLeft = ceil(($_SESSION['dev_lockout_until'] - time()) / 60);
        $error = "Dev console locked due to excessive failed attempts. Please retry in {$minsLeft} minute(s).";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Developer credentials required.';
        } else {
            try {
                $pdo = get_db();
                $stmt = $pdo->prepare("SELECT * FROM developers WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $dev = $stmt->fetch();

                $authenticated = false;

                if ($dev) {
                    if (isset($dev['is_active']) && (int)$dev['is_active'] === 0) {
                        $error = 'This developer account has been deactivated.';
                    } elseif (password_verify($password, $dev['password'])) {
                        $authenticated = true;
                    }
                }

                if ($authenticated) {
                    reset_failed_dev_logins();
                    session_regenerate_id(true);

                    $_SESSION['dev_logged_in'] = true;
                    $_SESSION['dev_user'] = $dev['username'] ?? $username;
                    $_SESSION['dev_role'] = $dev['role'] ?? 'root_dev';

                    // Apex hierarchy: Dev automatically has master access to Admin portal
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = 'Dev Master (' . ($_SESSION['dev_user']) . ')';

                    // Update last login
                    try {
                        $upd = $pdo->prepare("UPDATE developers SET last_login = NOW() WHERE username = ?");
                        $upd->execute([$username]);
                    } catch (Exception $e) {
                        // non-fatal
                    }

                    if (isset($_POST['remember_me'])) {
                        setcookie('dev_remember', $username, time() + (86400 * 14), '/dev/');
                    }

                    header('Location: /dev/index.php');
                    exit;
                } else {
                    if (empty($error)) {
                        record_failed_dev_login();
                        $error = 'Access Denied: Invalid developer credentials.';
                        if (is_dev_login_locked()) {
                            $error = 'Dev console locked for 15 minutes due to repeated failed logins.';
                        }
                    }
                }

            } catch (Exception $e) {
                $error = 'System error: ' . $e->getMessage();
            }
        }
    }
}

$csrfToken = get_dev_csrf_token();
$savedDevUser = $_COOKIE['dev_remember'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Master Portal | RT Chocos</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dev-style.css">
    <style>
        body.dev-login-body {
            background: #07090e;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(0, 240, 255, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 80%, rgba(0, 245, 160, 0.07) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(13, 17, 26, 0.9) 0%, #06080d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #e2e8f0;
            overflow-x: hidden;
            position: relative;
        }

        /* Cyber grid matrix lines */
        .dev-grid-bg {
            position: fixed;
            inset: 0;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            pointer-events: none;
            z-index: 0;
        }

        .dev-login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            background: rgba(13, 18, 28, 0.85);
            border: 1px solid rgba(0, 240, 255, 0.2);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7), 0 0 40px rgba(0, 240, 255, 0.1);
            border-radius: 16px;
            padding: 40px 36px;
            backdrop-filter: blur(16px);
            animation: devCardFade 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes devCardFade {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .dev-header-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #00f5a0;
            background: rgba(0, 245, 160, 0.1);
            border: 1px solid rgba(0, 245, 160, 0.25);
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 16px;
        }

        .dev-badge-pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #00f5a0;
            box-shadow: 0 0 10px #00f5a0;
            animation: pulseGlow 1.8s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .dev-login-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 6px 0;
            background: linear-gradient(135deg, #ffffff 30%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dev-login-subtitle {
            font-size: 13.5px;
            color: #64748b;
            margin: 0 0 28px 0;
        }

        .dev-input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .dev-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
            font-family: 'Fira Code', monospace;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dev-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .dev-input {
            width: 100%;
            background: rgba(7, 10, 16, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 14.5px;
            color: #f1f5f9;
            transition: all 0.25s ease;
            box-sizing: border-box;
            outline: none;
        }

        .dev-input:focus {
            border-color: #00f0ff;
            background: rgba(10, 15, 25, 0.95);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.15), 0 0 15px rgba(0, 240, 255, 0.1);
        }

        .dev-input-wrap .toggle-eye {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        .dev-input-wrap .toggle-eye:hover {
            color: #00f0ff;
        }

        .dev-btn-submit {
            width: 100%;
            margin-top: 10px;
            padding: 13px 20px;
            font-size: 14.5px;
            font-weight: 700;
            color: #05070a;
            background: linear-gradient(135deg, #00f0ff 0%, #00f5a0 100%);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(0, 240, 255, 0.3);
        }

        .dev-btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 25px rgba(0, 245, 160, 0.4);
            filter: brightness(1.05);
        }

        .dev-btn-submit:active {
            transform: translateY(1px);
        }

        .dev-alert {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .dev-footer-links {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
            padding-top: 18px;
        }

        .dev-footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .dev-footer-links a:hover {
            color: #00f0ff;
        }
    </style>
</head>
<body class="dev-login-body">
    <div class="dev-grid-bg"></div>

    <div class="dev-login-card">
        <div class="dev-header-badge">
            <span class="dev-badge-pulse"></span>
            Apex Tier &bull; Master Dev Portal
        </div>

        <h1 class="dev-login-title">Developer Console</h1>
        <p class="dev-login-subtitle">Master level authentication required for root access</p>

        <?php if (!empty($error)): ?>
            <div class="dev-alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="/dev/login.php" method="POST" id="devLoginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="dev-input-group">
                <label class="dev-label" for="username">Developer Key / User</label>
                <div class="dev-input-wrap">
                    <input type="text" id="username" name="username" class="dev-input" required autocomplete="username" value="<?php echo htmlspecialchars($savedDevUser); ?>" placeholder="dev" <?php echo empty($savedDevUser) ? 'autofocus' : ''; ?>>
                </div>
            </div>

            <div class="dev-input-group">
                <label class="dev-label" for="password">Master Password</label>
                <div class="dev-input-wrap">
                    <input type="password" id="password" name="password" class="dev-input" required autocomplete="current-password" placeholder="••••••••••••" <?php echo !empty($savedDevUser) ? 'autofocus' : ''; ?>>
                    <button type="button" class="toggle-eye" id="togglePasswordBtn" aria-label="Toggle password view">
                        <svg class="eye-on" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg class="eye-off" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L3 3m12 12l6 6M21 12a9 9 0 01-3.35 5.646m-2.298-1.424A9 9 0 0021 12c-1.274-4.057-5.064-7-9.542-7-1.298 0-2.522.25-3.645.703"/></svg>
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #94a3b8; cursor: pointer;">
                    <input type="checkbox" name="remember_me" <?php echo !empty($savedDevUser) ? 'checked' : ''; ?> style="accent-color: #00f0ff;">
                    Remember terminal
                </label>
            </div>

            <button type="submit" class="dev-btn-submit" id="devSubmitBtn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Authorize & Unlock</span>
            </button>
        </form>

        <div class="dev-footer-links">
            <a href="/admin/login.php">&larr; Admin Portal</a>
            <a href="/">Main Website &rarr;</a>
        </div>
    </div>

    <script>
        const pwd = document.getElementById('password');
        const eyeBtn = document.getElementById('togglePasswordBtn');
        const eyeOn = eyeBtn.querySelector('.eye-on');
        const eyeOff = eyeBtn.querySelector('.eye-off');

        eyeBtn.addEventListener('click', () => {
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeOn.style.display = 'none';
                eyeOff.style.display = 'block';
            } else {
                pwd.type = 'password';
                eyeOn.style.display = 'block';
                eyeOff.style.display = 'none';
            }
        });
    </script>
</body>
</html>
