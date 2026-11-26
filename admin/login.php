<?php
// admin/login.php - Admin Login Interface

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if (isset($_GET['error']) && $_GET['error'] === 'lockdown') {
    $error = 'Emergency Security Notice: All administrator sessions were terminated by Developer Master Control.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Invalid session request. Please refresh and try again.';
    } elseif (is_login_locked()) {
        $lockout_left = ceil(($_SESSION['login_locked_until'] - time()) / 60);
        $error = "Too many failed login attempts. Locked out. Please try again in {$lockout_left} minutes.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            try {
                $pdo = get_db();
                $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
                $stmt->execute([$username]);
                $admin = $stmt->fetch();

                if ($admin && password_verify($password, $admin['password'])) {
                    if (isset($admin['is_active']) && (int)$admin['is_active'] === 0) {
                        $error = 'Access Revoked: This administrator account has been suspended by Developer Master Control.';
                    } else {
                        // Success!
                        reset_failed_logins();
                        session_regenerate_id(true);
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_user'] = $admin['username'];
                        $_SESSION['admin_session_salt'] = get_site_setting('admin_session_salt', '');
                        
                        // Handle remember me cookie if checked (1 week duration)
                        if (isset($_POST['remember_me'])) {
                            setcookie('admin_remember', $admin['username'], time() + (86400 * 7), '/');
                        }
                        
                        header('Location: /admin/dashboard.php');
                        exit;
                    }
                } else {
                    record_failed_login();
                    $error = 'Invalid username or password.';
                    if (is_login_locked()) {
                        $error = 'Too many failed login attempts. You are now locked out for 15 minutes.';
                    }
                }
            } catch (Exception $e) {
                $error = 'An error occurred during verification. Please try again.';
            }
        }
    }
}

$csrf_token = get_csrf_token();
$savedUsername = $_COOKIE['admin_remember'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | RT Chocos</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="login-body">
    <!-- Randomized background particles -->
    <div class="login-particles">
        <?php for ($i = 0; $i < 15; $i++): 
            $size = rand(8, 20);
            $left = rand(5, 95);
            $delay = rand(0, 15);
            $duration = rand(12, 22);
        ?>
            <div class="login-particle" style="width: <?php echo $size; ?>px; height: <?php echo $size; ?>px; left: <?php echo $left; ?>%; animation-delay: <?php echo $delay; ?>s; animation-duration: <?php echo $duration; ?>s;"></div>
        <?php endfor; ?>
    </div>

    <!-- Login Box -->
    <div class="login-card <?php echo !empty($error) ? 'shake' : ''; ?>" id="loginCard">
        <div class="login-logo">
            <!-- Inline Premium Chocolate SVG Logo -->
            <svg width="24" height="50" viewBox="0 0 147 307" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:12px;">
                <path d="M59 41L42 53L32 63L20 80L11 98L5 115L2 133L1 134L1 140L0 141L0 176L1 177L1 184L2 185L6 207L13 227L24 249L32 262L45 279L67 302L42 268L28 243L28 241L24 234L21 224L19 221L15 208L14 200L11 190L11 185L10 184L10 176L9 175L9 146L10 145L10 137L11 136L11 131L13 126L14 118L19 102L32 75L45 57L60 42ZM72 40L71 64L70 65L70 89L69 90L69 208L70 209L70 243L71 244L71 277L72 278L72 299L71 300L60 273L50 240L50 236L48 231L45 211L44 210L44 202L43 201L43 193L42 192L42 177L41 176L41 150L42 149L42 135L43 134L45 113L53 81L68 41L65 44L57 57L49 73L44 86L44 89L42 92L36 118L35 132L34 133L33 174L34 175L34 186L35 187L37 209L45 241L61 283L72 304L72 306L74 306L77 301L79 302L95 286L111 266L121 251L133 227L135 219L139 210L144 189L144 184L145 183L145 176L146 175L146 142L145 141L143 124L139 112L139 109L127 82L119 70L109 58L96 47L86 41L101 57L110 69L120 86L120 88L125 97L133 122L135 138L136 139L137 173L136 174L135 190L134 191L131 208L120 239L106 265L90 288L78 302L77 300L95 259L103 235L104 227L107 219L109 202L111 195L111 187L112 186L113 149L112 148L111 126L110 125L109 114L104 93L94 67L86 52L78 41L81 51L89 69L99 104L101 119L102 120L102 127L103 128L103 137L104 138L105 173L104 174L103 202L102 203L102 210L101 211L98 231L94 244L94 248L89 262L89 265L87 268L85 277L76 299L75 298L75 259L76 258L76 211L77 210L76 209L77 208L77 101L76 100L75 54L74 53L74 43L73 40ZM71 0L67 2L68 15L69 16L68 37L78 37L77 35L78 2L75 0Z" fill="var(--gold)" fill-rule="evenodd" />
            </svg>
            <h1>RT Chocos</h1>
            <p>Admin Portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 13.5px; padding: 12px 16px; text-align: left;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="/admin/login.php" method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($savedUsername); ?>" <?php echo empty($savedUsername) ? 'autofocus' : ''; ?>>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required autocomplete="current-password" <?php echo !empty($savedUsername) ? 'autofocus' : ''; ?>>
                    <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Toggle password visibility">
                        <svg class="eye-open" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <svg class="eye-closed" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88L3 3m12 12l6 6M21 12a9 9 0 01-3.35 5.646m-2.298-1.424A9 9 0 0021 12c-1.274-4.057-5.064-7-9.542-7-1.298 0-2.522.25-3.645.703"></path></svg>
                    </button>
                </div>
            </div>

            <div class="login-actions-row">
                <label class="login-remember">
                    <input type="checkbox" name="remember_me" id="remember_me" <?php echo !empty($savedUsername) ? 'checked' : ''; ?>>
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="btn-text">Sign In</span>
                <span class="btn-loader" style="display:none; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; width: 16px; height: 16px; animation: spin 0.8s linear infinite;"></span>
            </button>
        </form>

        <a href="../index.php" class="footer-link" style="margin-top:28px;">&larr; Return to main website</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle logic
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const eyeOpen = passwordToggle.querySelector('.eye-open');
            const eyeClosed = passwordToggle.querySelector('.eye-closed');

            passwordToggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });

            // Error Shake Timeout reset
            const card = document.getElementById('loginCard');
            if (card.classList.contains('shake')) {
                setTimeout(() => {
                    card.classList.remove('shake');
                }, 400);
            }

            // Submit Loader logic
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');

            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoader.style.display = 'inline-block';
            });
        });
    </script>
    
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
