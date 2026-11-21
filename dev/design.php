<?php
// dev/design.php - Theme & Custom Script Injection Studio
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed.';
    } else {
        try {
            $defaultTheme = trim($_POST['default_site_theme'] ?? 'theme-teal-sage');
            $showTester = isset($_POST['show_theme_tester']) ? '1' : '0';
            $customHeadScripts = trim($_POST['custom_head_scripts'] ?? '');
            $customCss = trim($_POST['custom_css'] ?? '');

            $settings = [
                'default_site_theme' => $defaultTheme,
                'show_theme_tester' => $showTester,
                'custom_head_scripts' => $customHeadScripts,
                'custom_css' => $customCss
            ];

            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            foreach ($settings as $k => $v) {
                $stmt->execute([$k, $v, $v]);
            }

            $successMsg = 'Theme configurations and custom code injections saved!';
        } catch (Exception $e) {
            $errorMsg = 'Failed saving design settings: ' . $e->getMessage();
        }
    }
}

$defaultTheme = get_site_setting('default_site_theme', 'theme-teal-sage');
$showTester = (get_site_setting('show_theme_tester', '0') === '1');
$customHeadScripts = get_site_setting('custom_head_scripts', '');
$customCss = get_site_setting('custom_css', '');

$themes = [
    'theme-teal-sage' => ['name' => 'Royal Teal & Sage', 'desc' => 'Rich emerald-teal backdrop with warm sage luxury cocoa accents', 'accent' => '#14b8a6'],
    'theme-cream-forest' => ['name' => 'Luxury Forest Cream', 'desc' => 'Classic ivory parchment tones with deep artisan forest green', 'accent' => '#22c55e'],
    'theme-rose-blush' => ['name' => 'Velvet Rose & Cocoa', 'desc' => 'Warm rose gold palette tailored for romantic gifting lines', 'accent' => '#f43f5e'],
    'theme-dark-cacao' => ['name' => 'Midnight Dark Cacao', 'desc' => 'Deep 85% single-origin dark chocolate espresso theme', 'accent' => '#e6a147'],
    'theme-royal-gold' => ['name' => 'Imperial Royal Gold', 'desc' => 'Refined metallic gold framing for festival hampers and luxury gift boxes', 'accent' => '#eab308']
];

$csrfToken = generate_csrf();
render_dev_header("Theme & Script Injection", "design");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<form action="design.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

    <div class="dev-page-header">
        <div class="dev-page-title">
            <h2>Design System, Color Palettes & Script Injections</h2>
            <p>Master control over the website's default color identity, public theme preview switcher, and live third-party script tags</p>
        </div>

        <button type="submit" class="dev-btn dev-btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            <span>Save Design Settings</span>
        </button>
    </div>

    <!-- Theme Selection Grid -->
    <div class="dev-card" style="margin-bottom: 24px;">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                <span>Default Public Theme Palette</span>
            </h3>

            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="show_theme_tester" value="1" <?php echo $showTester ? 'checked' : ''; ?> style="accent-color: var(--dev-cyan);">
                <span style="font-size: 13px; font-weight: 700; color: #fff;">Show Interactive Theme Widget to Public</span>
            </label>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <?php foreach ($themes as $key => $theme): 
                $isSelected = ($defaultTheme === $key);
            ?>
                <label style="display: block; cursor: pointer; border: 2px solid <?php echo $isSelected ? 'var(--dev-cyan)' : 'var(--dev-border-subtle)'; ?>; background: rgba(7, 10, 16, 0.6); border-radius: 12px; padding: 16px; transition: all 0.2s;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="width: 20px; height: 20px; border-radius: 50%; background: <?php echo $theme['accent']; ?>; display: inline-block; box-shadow: 0 0 10px <?php echo $theme['accent']; ?>;"></span>
                        <input type="radio" name="default_site_theme" value="<?php echo $key; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="accent-color: var(--dev-cyan);">
                    </div>
                    <div style="font-weight: 700; color: #fff; font-size: 14px; margin-bottom: 4px;"><?php echo htmlspecialchars($theme['name']); ?></div>
                    <div style="font-size: 11.5px; color: var(--dev-text-muted); line-height: 1.4;"><?php echo htmlspecialchars($theme['desc']); ?></div>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dev-grid-2">
        <!-- Custom Head Scripts Injection -->
        <div class="dev-card">
            <div class="dev-card-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <span>Custom &lt;head&gt; Code & Tag Manager</span>
                </h3>
            </div>

            <div class="dev-form-group">
                <label for="custom_head_scripts">Header HTML / JS Script Tags</label>
                <textarea id="custom_head_scripts" name="custom_head_scripts" class="dev-textarea" rows="8" style="font-family: 'Fira Code', monospace; font-size: 12.5px;" placeholder="<!-- Google Analytics, Meta Pixel, or custom scripts -->&#10;<script>&#10;  // Custom JS tracking code here&#10;</script>"><?php echo htmlspecialchars($customHeadScripts); ?></textarea>
                <div class="help-text">Automatically injected into the <code>&lt;head&gt;</code> of all public customer-facing pages.</div>
            </div>
        </div>

        <!-- Custom CSS Injection -->
        <div class="dev-card">
            <div class="dev-card-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    <span>Global Custom CSS Overrides</span>
                </h3>
            </div>

            <div class="dev-form-group">
                <label for="custom_css">CSS Stylesheet Rules</label>
                <textarea id="custom_css" name="custom_css" class="dev-textarea" rows="8" style="font-family: 'Fira Code', monospace; font-size: 12.5px;" placeholder="/* Custom CSS overrides */&#10;.product-card { border-radius: 16px; }&#10;#site-header { backdrop-filter: blur(10px); }"><?php echo htmlspecialchars($customCss); ?></textarea>
                <div class="help-text">Injected into a prioritized <code>&lt;style&gt;</code> block across all public pages for real-time visual hotfixes.</div>
            </div>
        </div>
    </div>
</form>

<?php
render_dev_footer();
?>
