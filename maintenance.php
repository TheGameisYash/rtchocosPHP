<?php
// maintenance.php - Public Luxury Maintenance Mode Screen for RT Chocos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

// Handle email subscription during maintenance
$subscribeSuccess = false;
$subscribeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maint_email'])) {
    $email = filter_var(trim($_POST['maint_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if ($email) {
        try {
            $pdo = get_db();
            $check = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetchColumn() == 0) {
                $ins = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
                $ins->execute([$email]);
            }
            $subscribeSuccess = true;
        } catch (Exception $e) {
            $subscribeError = 'Unable to save notification preference.';
        }
    } else {
        $subscribeError = 'Please enter a valid email address.';
    }
}

// Fetch maintenance configurations
$title = get_site_setting('maintenance_title', "We're Perfecting Something Delicious");
$subtitle = get_site_setting('maintenance_subtitle', 'Our chocolate laboratory is currently undergoing planned upgrades.');
$message = get_site_setting('maintenance_message', "We are fine-tuning our handcrafted recipes and upgrading our platform to bring you an even more delightful bean-to-bar experience. We will be back online shortly!");
$eta = get_site_setting('maintenance_eta', '');
$mediaType = get_site_setting('maintenance_media_type', 'both');
$imageUrl = get_site_setting('maintenance_image', 'assets/ph.png');
$videoUrl = get_site_setting('maintenance_video', 'https://www.youtube.com/watch?v=kY3Pvdq0YpY');
$notifyEnabled = (get_site_setting('maintenance_notify_enabled', '1') === '1');

// Helper to extract YouTube embed URL
function parse_video_embed($url) {
    if (empty($url)) return '';
    
    // YouTube
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_\-]+)/', $url, $m)) {
        $id = $m[1];
        return "https://www.youtube.com/embed/{$id}?autoplay=1&mute=1&loop=1&playlist={$id}&controls=1&rel=0";
    }
    // Vimeo
    if (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/', $url, $m)) {
        return "https://player.vimeo.com/video/{$m[1]}?autoplay=1&muted=1&loop=1";
    }
    // Direct MP4
    if (preg_match('/\.mp4(\?.*)?$/i', $url)) {
        return $url;
    }
    return $url;
}

$parsedVideo = parse_video_embed($videoUrl);
$isDirectMp4 = (bool)preg_match('/\.mp4(\?.*)?$/i', $parsedVideo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | RT Chocos</title>
    <!-- Luxury Brand Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #090604;
            --cocoa-rich: #160f0a;
            --cocoa-card: rgba(26, 17, 12, 0.75);
            --gold: #d4af37;
            --gold-light: #f5d77f;
            --gold-glow: rgba(212, 175, 55, 0.25);
            --text-main: #fcf8f2;
            --text-muted: #b8a698;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 20% 15%, rgba(212, 175, 55, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 80% 85%, rgba(139, 69, 19, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, #110b07 0%, var(--bg-dark) 100%);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient floating particles */
        .ambient-particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .ambient-particle {
            position: absolute;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            animation: floatSlow linear infinite;
        }

        @keyframes floatSlow {
            0% { transform: translateY(100vh) scale(0.8); opacity: 0; }
            20% { opacity: 0.6; }
            80% { opacity: 0.6; }
            100% { transform: translateY(-10vh) scale(1.2); opacity: 0; }
        }

        .maint-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 1080px;
            padding: 40px 24px 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        /* Brand Logo Badge */
        .maint-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
        }

        .maint-logo-icon {
            width: 58px;
            height: 58px;
            color: var(--gold);
            filter: drop-shadow(0 0 16px var(--gold-glow));
            margin-bottom: 12px;
        }

        .maint-logo-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin: 0;
            background: linear-gradient(135deg, #fff 20%, var(--gold-light) 60%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .maint-logo-sub {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--gold);
            margin-top: 4px;
            font-weight: 600;
        }

        /* Status Pill */
        .maint-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(212, 175, 55, 0.12);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
            color: var(--gold-light);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 24px;
        }

        .maint-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 8px var(--gold);
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* Headline & Messaging */
        .maint-headline {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(34px, 5.5vw, 56px);
            font-weight: 700;
            line-height: 1.15;
            margin: 0 0 14px 0;
            color: #ffffff;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            max-width: 850px;
        }

        .maint-subtitle {
            font-size: clamp(16px, 2.5vw, 20px);
            color: var(--gold-light);
            margin: 0 0 16px 0;
            font-weight: 500;
            max-width: 700px;
        }

        .maint-message {
            font-size: 15px;
            line-height: 1.7;
            color: var(--text-muted);
            max-width: 680px;
            margin: 0 auto 36px;
        }

        /* Countdown Timer */
        .countdown-wrap {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-bottom: 44px;
            flex-wrap: wrap;
        }

        .countdown-box {
            background: var(--cocoa-card);
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 14px;
            padding: 16px 20px;
            min-width: 85px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        .countdown-num {
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            font-weight: 700;
            color: var(--gold-light);
            line-height: 1;
        }

        .countdown-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--text-muted);
            margin-top: 6px;
            font-weight: 600;
        }

        /* Media Section (Video & Pictures) */
        .maint-media-section {
            width: 100%;
            margin-bottom: 44px;
        }

        .media-grid-both {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            align-items: stretch;
        }

        .media-single {
            max-width: 760px;
            margin: 0 auto;
        }

        .media-card {
            background: var(--cocoa-card);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 30px rgba(212, 175, 55, 0.08);
            backdrop-filter: blur(12px);
            position: relative;
        }

        .media-image-frame {
            width: 100%;
            height: 100%;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }

        .media-image-frame img {
            width: 100%;
            height: 100%;
            max-height: 420px;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }

        .media-image-frame:hover img {
            transform: scale(1.03);
        }

        .media-video-frame {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
            background: #000;
        }

        .media-video-frame iframe,
        .media-video-frame video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Email Notification Box */
        .maint-subscribe-card {
            background: var(--cocoa-card);
            border: 1px solid rgba(212, 175, 55, 0.25);
            border-radius: 16px;
            padding: 30px;
            max-width: 540px;
            width: 100%;
            margin: 0 auto 36px;
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .maint-subscribe-card h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .maint-subscribe-card p {
            margin: 0 0 20px 0;
            font-size: 13.5px;
            color: var(--text-muted);
        }

        .maint-form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .maint-input {
            flex: 1 1 240px;
            background: rgba(10, 6, 4, 0.7);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            padding: 13px 18px;
            font-size: 14px;
            color: #fff;
            outline: none;
            transition: all 0.2s;
        }

        .maint-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px var(--gold-glow);
        }

        .maint-btn {
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            color: #140d07;
            font-weight: 700;
            font-size: 14px;
            border: none;
            border-radius: 10px;
            padding: 13px 24px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px var(--gold-glow);
        }

        .maint-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
            filter: brightness(1.05);
        }

        .maint-alert-success {
            background: rgba(0, 245, 160, 0.12);
            border: 1px solid rgba(0, 245, 160, 0.35);
            color: #6ee7b7;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 20px;
        }

        /* Social & Footer */
        .maint-footer {
            position: relative;
            z-index: 10;
            width: 100%;
            border-top: 1px solid rgba(212, 175, 55, 0.12);
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 12.5px;
            color: var(--text-muted);
            max-width: 1080px;
        }

        .maint-social-links {
            display: flex;
            gap: 16px;
        }

        .maint-social-links a {
            color: var(--gold);
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .maint-social-links a:hover {
            color: var(--gold-light);
            text-decoration: underline;
        }

        .dev-secret-link {
            color: rgba(255, 255, 255, 0.25);
            text-decoration: none;
            font-family: monospace;
            font-size: 11px;
            transition: color 0.2s;
        }

        .dev-secret-link:hover {
            color: var(--gold);
        }

        @media (max-width: 768px) {
            .media-grid-both {
                grid-template-columns: 1fr;
            }
            .maint-footer {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Floating ambient particles -->
    <div class="ambient-particles">
        <?php for ($i = 0; $i < 12; $i++): 
            $left = rand(5, 95);
            $size = rand(6, 18);
            $dur = rand(15, 30);
            $delay = rand(0, 15);
        ?>
            <div class="ambient-particle" style="left: <?php echo $left; ?>%; width: <?php echo $size; ?>px; height: <?php echo $size; ?>px; animation-duration: <?php echo $dur; ?>s; animation-delay: <?php echo $delay; ?>s;"></div>
        <?php endfor; ?>
    </div>

    <!-- Main Content Container -->
    <div class="maint-container">
        <!-- Brand Logo Header -->
        <div class="maint-logo">
            <svg class="maint-logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <h1 class="maint-logo-title">RT Chocos</h1>
            <div class="maint-logo-sub">Artisan Bean-to-Bar &bull; Cocoa Science</div>
        </div>

        <!-- Status Badge -->
        <div class="maint-badge">
            <span class="maint-badge-dot"></span>
            <span>Platform Upgrade in Progress</span>
        </div>

        <!-- Headline & Notice -->
        <h2 class="maint-headline"><?php echo htmlspecialchars($title); ?></h2>
        <?php if (!empty($subtitle)): ?>
            <div class="maint-subtitle"><?php echo htmlspecialchars($subtitle); ?></div>
        <?php endif; ?>
        <p class="maint-message"><?php echo nl2br(htmlspecialchars($message)); ?></p>

        <!-- Live Countdown Timer (if ETA is set) -->
        <?php if (!empty($eta) && strtotime($eta) > time()): ?>
            <div class="countdown-wrap" id="countdownWrap" data-target="<?php echo htmlspecialchars(date('c', strtotime($eta))); ?>">
                <div class="countdown-box">
                    <div class="countdown-num" id="cdDays">00</div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-box">
                    <div class="countdown-num" id="cdHours">00</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-box">
                    <div class="countdown-num" id="cdMinutes">00</div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-box">
                    <div class="countdown-num" id="cdSeconds">00</div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>
            <script>
                (function() {
                    const wrap = document.getElementById('countdownWrap');
                    const targetTime = new Date(wrap.getAttribute('data-target')).getTime();
                    function updateCountdown() {
                        const now = new Date().getTime();
                        const diff = targetTime - now;
                        if (diff <= 0) {
                            wrap.style.display = 'none';
                            return;
                        }
                        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        const secs = Math.floor((diff % (1000 * 60)) / 1000);
                        document.getElementById('cdDays').textContent = String(days).padStart(2, '0');
                        document.getElementById('cdHours').textContent = String(hours).padStart(2, '0');
                        document.getElementById('cdMinutes').textContent = String(mins).padStart(2, '0');
                        document.getElementById('cdSeconds').textContent = String(secs).padStart(2, '0');
                    }
                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                })();
            </script>
        <?php endif; ?>

        <!-- Media Showcase Section (Video & Pictures) -->
        <?php if ($mediaType !== 'none'): ?>
            <div class="maint-media-section">
                <?php if ($mediaType === 'both' && !empty($imageUrl) && !empty($parsedVideo)): ?>
                    <!-- Both Picture & Video Side-by-Side -->
                    <div class="media-grid-both">
                        <div class="media-card">
                            <div class="media-image-frame">
                                <img src="/<?php echo ltrim($imageUrl, '/'); ?>" alt="RT Chocos Artisan Chocolate">
                            </div>
                        </div>
                        <div class="media-card">
                            <div class="media-video-frame">
                                <?php if ($isDirectMp4): ?>
                                    <video src="<?php echo htmlspecialchars($parsedVideo); ?>" autoplay muted loop playsinline controls></video>
                                <?php else: ?>
                                    <iframe src="<?php echo htmlspecialchars($parsedVideo); ?>" title="RT Chocos Video Showcase" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif (($mediaType === 'image' || $mediaType === 'both') && !empty($imageUrl)): ?>
                    <!-- Picture Only -->
                    <div class="media-single">
                        <div class="media-card">
                            <div class="media-image-frame">
                                <img src="/<?php echo ltrim($imageUrl, '/'); ?>" alt="RT Chocos Artisan Chocolate">
                            </div>
                        </div>
                    </div>
                <?php elseif (($mediaType === 'video' || $mediaType === 'both') && !empty($parsedVideo)): ?>
                    <!-- Video Only -->
                    <div class="media-single">
                        <div class="media-card">
                            <div class="media-video-frame">
                                <?php if ($isDirectMp4): ?>
                                    <video src="<?php echo htmlspecialchars($parsedVideo); ?>" autoplay muted loop playsinline controls></video>
                                <?php else: ?>
                                    <iframe src="<?php echo htmlspecialchars($parsedVideo); ?>" title="RT Chocos Video Showcase" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Visitor Notification Box -->
        <?php if ($notifyEnabled): ?>
            <div class="maint-subscribe-card">
                <h3>Be The First To Taste</h3>
                <p>Enter your email below to be immediately notified the moment our store and tasting workshops go live.</p>

                <?php if ($subscribeSuccess): ?>
                    <div class="maint-alert-success">
                        ✨ Thank you! We've reserved your priority invitation for launch day.
                    </div>
                <?php else: ?>
                    <?php if (!empty($subscribeError)): ?>
                        <div style="color: #f87171; font-size: 13px; margin-bottom: 14px;">
                            <?php echo htmlspecialchars($subscribeError); ?>
                        </div>
                    <?php endif; ?>
                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
                        <div class="maint-form-row">
                            <input type="email" name="maint_email" class="maint-input" required placeholder="your.name@example.com">
                            <button type="submit" class="maint-btn">Notify Me</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="maint-footer">
        <div>&copy; <?php echo date('Y'); ?> RT Chocos Private Limited. All rights reserved.</div>

        <div class="maint-social-links">
            <a href="https://instagram.com/rtchocos" target="_blank" rel="noopener">Instagram</a>
            <a href="https://facebook.com/rtchocos" target="_blank" rel="noopener">Facebook</a>
            <a href="https://youtube.com/rtchocos" target="_blank" rel="noopener">YouTube</a>
        </div>

        <a href="/dev" class="dev-secret-link" title="Authorized Staff Access">Staff &bull; Dev Root &rarr;</a>
    </footer>
</body>
</html>
