<?php
// dev/store-ops.php - Master Store & Business Operations Studio
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed. Please refresh and retry.';
    } else {
        try {
            $settingsToSave = [
                'store_mode' => $_POST['store_mode'] ?? 'retail',
                'bulk_min_order_qty' => trim($_POST['bulk_min_order_qty'] ?? '50 Units'),
                'bulk_enquiry_phone' => trim($_POST['bulk_enquiry_phone'] ?? ''),
                'retail_free_shipping_min' => (int)($_POST['retail_free_shipping_min'] ?? 999),
                'show_announcement_banner' => isset($_POST['show_announcement_banner']) ? '1' : '0',
                'store_announcement_text' => trim($_POST['store_announcement_text'] ?? ''),
                'store_announcement_link_text' => trim($_POST['store_announcement_link_text'] ?? ''),
                'store_announcement_link_url' => trim($_POST['store_announcement_link_url'] ?? ''),
                'contact_email' => trim($_POST['contact_email'] ?? ''),
                'contact_phone' => trim($_POST['contact_phone'] ?? ''),
                'whatsapp_phone' => trim($_POST['whatsapp_phone'] ?? ''),
                'social_instagram' => trim($_POST['social_instagram'] ?? ''),
                'social_facebook' => trim($_POST['social_facebook'] ?? ''),
                'social_youtube' => trim($_POST['social_youtube'] ?? ''),
                'social_linkedin' => trim($_POST['social_linkedin'] ?? '')
            ];

            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            foreach ($settingsToSave as $k => $v) {
                $stmt->execute([$k, (string)$v, (string)$v]);
            }

            $successMsg = 'Store operations, business details, and announcement banner saved successfully!';
        } catch (Exception $e) {
            $errorMsg = 'Failed to save store settings: ' . $e->getMessage();
        }
    }
}

// Fetch all current store settings
$settings = [];
try {
    $settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Exception $e) {
    // non-fatal
}

$storeMode = $settings['store_mode'] ?? 'retail';
$bulkMinQty = $settings['bulk_min_order_qty'] ?? '50 Units';
$bulkPhone = $settings['bulk_enquiry_phone'] ?? '+919140238741';
$retailFreeShipping = $settings['retail_free_shipping_min'] ?? '999';
$showBanner = ($settings['show_announcement_banner'] ?? '1') === '1';
$bannerText = $settings['store_announcement_text'] ?? 'Custom Festival Gifting Open for Corporate Orders!';
$bannerLinkText = $settings['store_announcement_link_text'] ?? 'Get Instant Quote →';
$bannerLinkUrl = $settings['store_announcement_link_url'] ?? '/shop.php#bulk-enquiry';
$contactEmail = $settings['contact_email'] ?? 'hello@rtchocos.com';
$contactPhone = $settings['contact_phone'] ?? '+919140238741';
$whatsappPhone = $settings['whatsapp_phone'] ?? '+919140238741';
$socialIg = $settings['social_instagram'] ?? 'https://instagram.com/rtchocos';
$socialFb = $settings['social_facebook'] ?? 'https://facebook.com/rtchocos';
$socialYt = $settings['social_youtube'] ?? 'https://youtube.com/rtchocos';
$socialLi = $settings['social_linkedin'] ?? 'https://linkedin.com/company/rtchocos';

$csrfToken = generate_csrf();
render_dev_header("Store & Business Operations", "store-ops");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<form action="store-ops.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

    <div class="dev-page-header">
        <div class="dev-page-title">
            <h2>Store Operations & Business Control</h2>
            <p>Master control over E-Commerce mode, B2B wholesale parameters, announcement ribbons, and corporate communication channels</p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="/shop" target="_blank" class="dev-btn dev-btn-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Preview Storefront</span>
            </a>
            <button type="submit" class="dev-btn dev-btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                <span>Save Store Operations</span>
            </button>
        </div>
    </div>

    <!-- Store Mode Switcher Card -->
    <div class="dev-card" style="margin-bottom: 24px; border-color: <?php echo ($storeMode === 'bulk') ? 'var(--dev-cyan)' : 'var(--dev-gold)'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 11px; font-family: 'Fira Code', monospace; text-transform: uppercase; color: var(--dev-text-sub); letter-spacing: 0.08em; margin-bottom: 4px;">
                    Active Catalog Mode
                </div>
                <div style="font-size: 18px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 10px;">
                    <span><?php echo ($storeMode === 'bulk') ? '📦 B2B Wholesale & Corporate Gifting Mode' : '🍫 Direct-to-Consumer Retail Mode'; ?></span>
                    <span class="dev-pill <?php echo ($storeMode === 'bulk') ? 'dev-pill-cyan' : 'dev-pill-success'; ?>">
                        <?php echo strtoupper($storeMode); ?>
                    </span>
                </div>
                <div style="font-size: 12.5px; color: var(--dev-text-muted); margin-top: 4px;">
                    <?php echo ($storeMode === 'bulk') 
                        ? 'Website prioritizes wholesale quote requests, minimum order quantities, and corporate gifting forms.' 
                        : 'Website functions as a standard direct retail checkout and consumer chocolate cart.'; ?>
                </div>
            </div>

            <div style="display: flex; gap: 12px; background: rgba(7, 10, 16, 0.7); padding: 6px; border-radius: 10px; border: 1px solid var(--dev-border-subtle);">
                <label style="display: flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 8px; cursor: pointer; <?php echo ($storeMode === 'retail') ? 'background: rgba(0, 245, 160, 0.15); color: #fff; font-weight: 700;' : 'color: var(--dev-text-muted);'; ?>">
                    <input type="radio" name="store_mode" value="retail" <?php echo ($storeMode === 'retail') ? 'checked' : ''; ?> style="accent-color: var(--dev-emerald);">
                    <span>Retail Mode</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 8px; cursor: pointer; <?php echo ($storeMode === 'bulk') ? 'background: rgba(0, 240, 255, 0.15); color: #fff; font-weight: 700;' : 'color: var(--dev-text-muted);'; ?>">
                    <input type="radio" name="store_mode" value="bulk" <?php echo ($storeMode === 'bulk') ? 'checked' : ''; ?> style="accent-color: var(--dev-cyan);">
                    <span>Bulk / B2B Mode</span>
                </label>
            </div>
        </div>
    </div>

    <div class="dev-grid-2">
        <!-- Wholesale & Pricing Parameters -->
        <div class="dev-card">
            <div class="dev-card-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Commerce Parameters</span>
                </h3>
            </div>

            <div class="dev-form-group">
                <label for="bulk_min_order_qty">Bulk Minimum Order Quantity (MOQ)</label>
                <input type="text" id="bulk_min_order_qty" name="bulk_min_order_qty" class="dev-input" value="<?php echo htmlspecialchars($bulkMinQty); ?>" placeholder="e.g. 50 Units">
                <div class="help-text">Displayed on wholesale product detail pages and catalog inquiry popups.</div>
            </div>

            <div class="dev-form-group">
                <label for="retail_free_shipping_min">Retail Free Shipping Threshold (INR ₹)</label>
                <input type="number" id="retail_free_shipping_min" name="retail_free_shipping_min" class="dev-input" value="<?php echo htmlspecialchars($retailFreeShipping); ?>" placeholder="999">
                <div class="help-text">Cart subtotal at which automatic free delivery is applied.</div>
            </div>

            <div class="dev-form-group">
                <label for="bulk_enquiry_phone">Wholesale Direct Hotline</label>
                <input type="text" id="bulk_enquiry_phone" name="bulk_enquiry_phone" class="dev-input" value="<?php echo htmlspecialchars($bulkPhone); ?>" placeholder="+919140238741">
            </div>
        </div>

        <!-- Announcement Ribbon Controls -->
        <div class="dev-card">
            <div class="dev-card-header">
                <h3>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <span>Store Announcement Banner</span>
                </h3>

                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="show_announcement_banner" value="1" <?php echo $showBanner ? 'checked' : ''; ?> style="accent-color: var(--dev-cyan);">
                    <span style="font-size: 13px; font-weight: 700; color: #fff;">Show Banner</span>
                </label>
            </div>

            <div class="dev-form-group">
                <label for="store_announcement_text">Banner Announcement Message</label>
                <input type="text" id="store_announcement_text" name="store_announcement_text" class="dev-input" value="<?php echo htmlspecialchars($bannerText); ?>" placeholder="e.g. Festival Corporate Gifting Open!">
                <div class="help-text">Displayed at the top of the header across all public website pages.</div>
            </div>

            <div class="dev-form-group">
                <label for="store_announcement_link_text">Call-to-Action Text</label>
                <input type="text" id="store_announcement_link_text" name="store_announcement_link_text" class="dev-input" value="<?php echo htmlspecialchars($bannerLinkText); ?>" placeholder="e.g. Get Instant Quote →">
            </div>

            <div class="dev-form-group">
                <label for="store_announcement_link_url">Call-to-Action Target Link</label>
                <input type="text" id="store_announcement_link_url" name="store_announcement_link_url" class="dev-input" value="<?php echo htmlspecialchars($bannerLinkUrl); ?>" placeholder="/shop#bulk-enquiry">
            </div>
        </div>
    </div>

    <!-- Communication & Social Channels -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Official Contact & Social Media Channels</span>
            </h3>
        </div>

        <div class="dev-grid-4" style="margin-bottom: 0;">
            <div class="dev-form-group">
                <label for="contact_email">Public Inquiries Email</label>
                <input type="email" id="contact_email" name="contact_email" class="dev-input" value="<?php echo htmlspecialchars($contactEmail); ?>">
            </div>
            <div class="dev-form-group">
                <label for="contact_phone">Customer Service Phone</label>
                <input type="text" id="contact_phone" name="contact_phone" class="dev-input" value="<?php echo htmlspecialchars($contactPhone); ?>">
            </div>
            <div class="dev-form-group">
                <label for="whatsapp_phone">WhatsApp Business Number</label>
                <input type="text" id="whatsapp_phone" name="whatsapp_phone" class="dev-input" value="<?php echo htmlspecialchars($whatsappPhone); ?>">
            </div>
            <div class="dev-form-group">
                <label for="social_instagram">Instagram Profile</label>
                <input type="url" id="social_instagram" name="social_instagram" class="dev-input" value="<?php echo htmlspecialchars($socialIg); ?>">
            </div>
            <div class="dev-form-group">
                <label for="social_facebook">Facebook Page</label>
                <input type="url" id="social_facebook" name="social_facebook" class="dev-input" value="<?php echo htmlspecialchars($socialFb); ?>">
            </div>
            <div class="dev-form-group">
                <label for="social_youtube">YouTube Channel</label>
                <input type="url" id="social_youtube" name="social_youtube" class="dev-input" value="<?php echo htmlspecialchars($socialYt); ?>">
            </div>
            <div class="dev-form-group">
                <label for="social_linkedin">LinkedIn Company</label>
                <input type="url" id="social_linkedin" name="social_linkedin" class="dev-input" value="<?php echo htmlspecialchars($socialLi); ?>">
            </div>
        </div>
    </div>
</form>

<?php
render_dev_footer();
?>
