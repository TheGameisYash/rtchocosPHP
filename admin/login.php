<?php
// admin/login.php - Admin Login Interface

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

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
                    // Success!
                    reset_failed_logins();
                    session_regenerate_id(true);
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = $admin['username'];
                    
                    // Handle remember me cookie if checked (1 week duration)
                    if (isset($_POST['remember_me'])) {
                        setcookie('admin_remember', $admin['username'], time() + (86400 * 7), '/');
                    }
                    
                    header('Location: dashboard.php');
                    exit;
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
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <h1>RT Chocos</h1>
            <p>Admin Portal</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 13.5px; padding: 12px 16px; text-align: left;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="loginForm">
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
