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
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = $admin['username'];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | RT Chocos</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-900: #0D3B12;
            --green-800: #14501A;
            --gold: #C7A66A;
            --gold-light: #D4BA8A;
            --cream: #F6F2EA;
            --white: #FEFDFB;
            --black: #1A1410;
            --brown: #3B2A22;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Jost', sans-serif;
            background: var(--black);
            color: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }
        /* Deco Circles */
        .deco-circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(199,166,106,0.1) 0%, rgba(13,59,18,0) 70%);
            z-index: 1;
        }
        .deco-1 { width: 500px; height: 500px; top: -10%; left: -10%; }
        .deco-2 { width: 600px; height: 600px; bottom: -20%; right: -10%; }
        
        .login-card {
            background: rgba(25, 20, 16, 0.95);
            border: 1px solid rgba(199, 166, 106, 0.2);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            z-index: 2;
            text-align: center;
        }
        .logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--gold);
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .subtitle {
            font-size: 14px;
            color: rgba(246, 242, 234, 0.6);
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            color: var(--gold-light);
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(199, 166, 106, 0.15);
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 15px;
            color: var(--white);
            font-family: 'Jost', sans-serif;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: var(--gold);
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 10px rgba(199, 166, 106, 0.2);
        }
        .btn-login {
            width: 100%;
            background: var(--gold);
            color: var(--black);
            border: none;
            border-radius: 6px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: var(--gold-light);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(199, 166, 106, 0.3);
        }
        .error-message {
            background: rgba(239, 83, 80, 0.1);
            border: 1px solid rgba(239, 83, 80, 0.3);
            color: #ef5350;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13.5px;
            line-height: 1.4;
            text-align: left;
        }
        .footer-link {
            display: block;
            margin-top: 24px;
            font-size: 13px;
            color: rgba(246, 242, 234, 0.4);
            text-decoration: none;
            transition: color 0.3s;
        }
        .footer-link:hover {
            color: var(--gold);
        }
    </style>
</head>
<body>
    <div class="deco-circle deco-1"></div>
    <div class="deco-circle deco-2"></div>

    <div class="login-card">
        <div class="logo">RT Chocos</div>
        <div class="subtitle">Admin Portal</div>

        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <a href="../index.php" class="footer-link">&larr; Return to main site</a>
    </div>
</body>
</html>
