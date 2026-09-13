<?php
// admin/dashboard.php - Modernized Executive Dashboard with Chart.js Analytics
require_once __DIR__ . '/layout.php';

$pdo = get_db();

// Set default timezone for relative calculations
date_default_timezone_set('Asia/Kolkata');

require_once dirname(__DIR__) . '/includes/ingredient_spotlight.php';
try {
    $currentSpotlight = get_current_ingredient_spotlight(false);
} catch (Exception $e) {
    $currentSpotlight = get_curated_spotlight_fallback();
}

// Fetch core metrics
try {
    // 1. E-Commerce Metrics
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $paidOrdersCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    $avgOrderValue = $paidOrdersCount > 0 ? ($totalRevenue / $paidOrdersCount) : 0;

    // Recent Orders (latest 4)
    $recentOrders = $pdo->query("SELECT id, order_number, customer_name, total, order_status, payment_status, created_at FROM orders ORDER BY created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

    // 2. 14-Day Timeline Analytics for Chart.js
    $chartLabels = [];
    $revMap = [];
    $ordersMap = [];

    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $displayLabel = date('M d', strtotime("-$i days"));
        $chartLabels[] = $displayLabel;
        $revMap[$d] = 0;
        $ordersMap[$d] = 0;
    }

    $chartSql = "SELECT DATE(created_at) as o_date, COUNT(*) as o_count, SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as o_rev FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY DATE(created_at)";
    $chartRows = $pdo->query($chartSql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($chartRows as $cr) {
        if (isset($revMap[$cr['o_date']])) {
            $revMap[$cr['o_date']] = (float)$cr['o_rev'];
            $ordersMap[$cr['o_date']] = (int)$cr['o_count'];
        }
    }
    $revenueSeries = array_values($revMap);
    $ordersSeries = array_values($ordersMap);

    // 3. Product Catalog Breakdown by Category
    $catRows = $pdo->query("SELECT category, COUNT(*) as count FROM products WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);
    $catLabels = [];
    $catCounts = [];
    foreach ($catRows as $crow) {
        $catLabels[] = $crow['category'];
        $catCounts[] = (int)$crow['count'];
    }
    if (empty($catLabels)) {
        $catLabels = ['Chocolates', 'Bonbons', 'Kits', 'Cacao'];
        $catCounts = [1, 1, 1, 1];
    }

    // 4. Blog counts
    $totalBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    $publishedBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 1")->fetchColumn();
    $draftBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 0")->fetchColumn();
    
    // 5. Subscriber counts
    $totalSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    
    // 6. Message count
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    // 7. Recent Blogs (latest 3)
    $stmt = $pdo->query("SELECT id, title, slug, excerpt, category, thumbnail_path, is_published, views, created_at FROM blogs ORDER BY created_at DESC LIMIT 3");
    $recentBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Unified Activity Timeline (fetch from orders, subscribers, contacts, and blogs, then merge)
    $activities = [];
    
    // Recent orders
    $stmt = $pdo->query("SELECT 'order' as type, order_number as title, CONCAT(customer_name, ' - ₹', total) as subtitle, created_at FROM orders ORDER BY created_at DESC LIMIT 4");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = $row;
    }

    // Recent subscribers
    $stmt = $pdo->query("SELECT 'subscriber' as type, email as title, '' as subtitle, created_at FROM subscribers ORDER BY created_at DESC LIMIT 4");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = $row;
    }
    
    // Recent messages
    $stmt = $pdo->query("SELECT 'message' as type, name as title, subject as subtitle, created_at FROM contacts ORDER BY created_at DESC LIMIT 4");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = $row;
    }
    
    // Recent blogs
    $stmt = $pdo->query("SELECT 'blog' as type, title as title, slug as subtitle, created_at FROM blogs ORDER BY created_at DESC LIMIT 4");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = $row;
    }

    // Sort chronologically (newest first)
    usort($activities, function($a, $b) {
        return strcmp($b['created_at'], $a['created_at']);
    });
    // Limit to latest 5 events
    $activities = array_slice($activities, 0, 5);

    // 9. System Diagnostics
    $uploadsPath = __DIR__ . '/../assets/products';
    $uploadsWritable = is_dir($uploadsPath) && is_writable($uploadsPath);
    $mysqlVersion = 'MySQL Database';
    try {
        $mysqlVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    } catch (Exception $e) {}

} catch (Exception $e) {
    die("Error loading dashboard metrics: " . $e->getMessage());
}

// Personalized time-aware greeting
$hour = (int)date('H');
$greeting = "Good evening";
if ($hour < 12) {
    $greeting = "Good morning";
} elseif ($hour < 17) {
    $greeting = "Good afternoon";
}

$adminUser = $_SESSION['admin_user'] ?? 'Admin';

// Relative time formatting function
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

render_admin_header("Store Dashboard", "dashboard");
?>

<!-- Load Chart.js 4.4 via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Welcome Hero Banner -->
<div class="dashboard-hero-banner">
    <div style="flex: 1; min-width: 260px; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <span class="store-status-pill" style="background: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.3); color: #10B981;">
                <span class="pulse-dot" style="background: #10B981;"></span>
                <span>System Operational</span>
            </span>
            <span style="font-size: 12px; color: var(--text-muted);"><?php echo date('l, F j, Y'); ?></span>
        </div>
        <h2 style="font-family: var(--font-heading); font-size: 32px; font-weight: 700; color: var(--gold); margin-bottom: 8px; letter-spacing: 0.5px;">
            <?php echo $greeting; ?>, <?php echo htmlspecialchars($adminUser); ?>!
        </h2>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; max-width: 620px;">
            You have <strong style="color: var(--gold-light); font-weight: 700;"><?php echo $pendingOrders; ?> pending order<?php echo $pendingOrders !== 1 ? 's' : ''; ?></strong> requiring packaging & dispatch, and <strong style="color: var(--text-main);"><?php echo $unreadMessages; ?></strong> unread customer inquiry. Press <kbd class="kbd-chip">Ctrl + K</kbd> to search or trigger quick actions.
        </p>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap; z-index: 1;">
        <a href="product-editor.php" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            Add Product
        </a>
        <a href="orders.php" class="btn btn-outline">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            Orders (<?php echo $pendingOrders; ?>)
        </a>
        <a href="blog-editor.php" class="btn btn-outline">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            Write Blog
        </a>
    </div>
</div>

<!-- Metrics KPI Cards -->
<div class="metrics-grid" style="margin-bottom: 28px;">
    <!-- Store Revenue -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Store Revenue</div>
            <div class="metric-value" style="color: var(--gold-light);">₹<?php echo number_format($totalRevenue, 2); ?></div>
            <div style="font-size: 11.5px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                Avg. Order: <strong style="color: var(--text-main);">₹<?php echo number_format($avgOrderValue, 2); ?></strong>
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <!-- Store Orders -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Customer Orders</div>
            <div class="metric-value count-up" data-target="<?php echo $totalOrders; ?>">0</div>
            <div class="metric-trend <?php echo $pendingOrders > 0 ? 'up' : ''; ?>" style="<?php echo $pendingOrders > 0 ? 'color: #ef6c00;' : ''; ?>">
                <span><?php echo $pendingOrders; ?> awaiting fulfillment</span>
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
    </div>

    <!-- Shop Products -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Product Catalog</div>
            <div class="metric-value count-up" data-target="<?php echo $totalProducts; ?>">0</div>
            <div style="font-size: 11.5px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                <span style="color: #2e7d32;"><?php echo $activeProducts; ?> published</span> &bull; <?php echo ($totalProducts - $activeProducts); ?> drafts
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
    </div>

    <!-- Total Subscribers -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Subscribers & Reach</div>
            <div class="metric-value count-up" data-target="<?php echo $totalSubscribers; ?>">0</div>
            <div style="font-size: 11.5px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                <?php echo $totalBlogs; ?> Stories &bull; <?php echo $totalViews ?? 0; ?> Views
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
    </div>
</div>

<!-- Interactive Charts Grid -->
<div class="charts-grid">
    <!-- Chart 1: Revenue Velocity & Order Volume -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    Revenue & Order Velocity (Past 14 Days)
                </div>
                <div style="font-size: 11.5px; color: var(--text-light); margin-top: 2px;">
                    Daily store checkout volume and receipts
                </div>
            </div>
            <div class="chart-metric-badge">
                ₹<?php echo number_format($totalRevenue, 0); ?> Total
            </div>
        </div>
        <div class="chart-body">
            <canvas id="revenueVelocityChart"></canvas>
        </div>
    </div>

    <!-- Chart 2: Category Distribution Donut -->
    <div class="chart-card">
        <div class="chart-header">
            <div>
                <div class="chart-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    Catalog by Category
                </div>
                <div style="font-size: 11.5px; color: var(--text-light); margin-top: 2px;">
                    Distribution across chocolate varieties
                </div>
            </div>
            <div class="chart-metric-badge" style="background: rgba(199, 166, 106, 0.15); color: var(--gold);">
                <?php echo $totalProducts; ?> Items
            </div>
        </div>
        <div class="chart-body" style="display: flex; align-items: center; justify-content: center;">
            <canvas id="categoryDonutChart" style="max-height: 230px;"></canvas>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="editor-layout" style="align-items: stretch;">
    
    <!-- LEFT: Recent Orders & Recent Blogs -->
    <div style="grid-column: span 1; display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Recent Store Orders Card -->
        <div class="form-card" style="margin-bottom: 0;">
            <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span>Recent Customer Orders</span>
                <a href="orders.php" class="btn btn-outline btn-sm">All Orders</a>
            </div>

            <?php if (empty($recentOrders)): ?>
                <div style="text-align: center; padding: 30px; color: var(--text-light);">
                    No orders placed yet.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($recentOrders as $ro): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-app); transition: transform var(--transition);">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <a href="orders.php?search=<?php echo urlencode($ro['order_number']); ?>" style="font-weight: 700; color: var(--text-main); text-decoration: none; font-size: 13.5px;">
                                        #<?php echo htmlspecialchars($ro['order_number']); ?>
                                    </a>
                                    <span class="status-badge <?php echo $ro['order_status']; ?>" style="font-size: 10px; padding: 2px 6px;">
                                        <?php echo ucfirst($ro['order_status']); ?>
                                    </span>
                                </div>
                                <div style="font-size: 12px; color: var(--text-light); margin-top: 2px;">
                                    <?php echo htmlspecialchars($ro['customer_name']); ?> &bull; <?php echo date('M d, h:i A', strtotime($ro['created_at'])); ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; font-size: 14px; color: var(--text-main);">₹<?php echo number_format($ro['total'], 2); ?></div>
                                <span class="status-badge <?php echo $ro['payment_status'] === 'paid' ? 'active' : 'pending'; ?>" style="font-size: 9.5px; padding: 1px 5px;">
                                    <?php echo ucfirst($ro['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Blogs Card -->
        <div class="form-card" style="margin-bottom: 0;">
            <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span>Recently Added Stories</span>
                <a href="blogs.php" class="btn btn-outline btn-sm">All Stories</a>
            </div>
            
            <?php if (empty($recentBlogs)): ?>
                <div style="text-align: center; padding: 30px; color: var(--text-light);">
                    No blog posts found.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($recentBlogs as $blog): ?>
                        <div style="display: flex; gap: 14px; border: 1px solid var(--border-color); padding: 10px; border-radius: 10px; background: var(--bg-app); position: relative; align-items: center;">
                            <div style="width: 54px; height: 54px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: var(--cream);">
                                <img src="../<?php echo htmlspecialchars($blog['thumbnail_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: center; min-width: 0; flex-grow: 1; padding-right: 36px;">
                                <span style="font-size: 10px; text-transform: uppercase; color: var(--gold); font-weight: 700; letter-spacing: 0.5px;"><?php echo htmlspecialchars($blog['category']); ?></span>
                                <h4 style="font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 2px 0 3px 0; color: var(--text-main);"><?php echo htmlspecialchars($blog['title']); ?></h4>
                                <div style="font-size: 11px; color: var(--text-light);">
                                    <span><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span> &bull; <span><?php echo $blog['views']; ?> views</span>
                                </div>
                            </div>
                            <a href="blog-editor.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline btn-sm" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); padding: 5px 7px;" title="Edit Post">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: Diagnostics & Activity Timeline -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- AI Ingredient Spotlight Card -->
        <div class="form-card" id="spotlightAdminCard" style="margin-bottom: 0; background: var(--bg-card); border: 1px solid var(--border-color); position: relative; overflow: hidden;">
            <div style="position: absolute; top: -30px; right: -30px; width: 110px; height: 110px; background: radial-gradient(circle, rgba(199, 166, 106, 0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
            
            <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 8px; background: rgba(199, 166, 106, 0.15); color: var(--gold); font-size: 14px;">✨</span>
                    <span>AI Ingredient Spotlight</span>
                </div>
                <span style="font-size: 10.5px; padding: 3px 8px; border-radius: 12px; background: rgba(46, 125, 50, 0.12); color: #2e7d32; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #2e7d32;"></span> OpenRouter AI
                </span>
            </div>

            <p style="font-size: 12px; color: var(--text-light); margin: 0 0 14px 0; line-height: 1.45;">
                Featured live on the home &amp; explore hero section. Rotates dynamically every 6 hours via OpenRouter AI.
            </p>

            <div id="spotlightContentBox" style="background: var(--bg-app); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                    <span id="spotlightTag" style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--gold);"><?= htmlspecialchars($currentSpotlight['tag'] ?? '🌱 INGREDIENT SPOTLIGHT') ?></span>
                    <span id="spotlightOrigin" style="font-size: 11px; color: var(--text-light);"><?= htmlspecialchars($currentSpotlight['origin_region'] ?? 'Artisan Single Origin') ?></span>
                </div>
                <h4 id="spotlightName" style="font-family: 'Playfair Display', serif; font-size: 16px; margin: 0 0 6px 0; color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($currentSpotlight['ingredient_name'] ?? 'Cocoa Butter') ?></h4>
                <p id="spotlightDesc" style="font-size: 12px; color: var(--text-light); margin: 0 0 10px 0; line-height: 1.45;"><?= htmlspecialchars($currentSpotlight['short_desc'] ?? '') ?></p>
                
                <div id="spotlightChips" style="display: flex; flex-wrap: wrap; gap: 5px;">
                    <?php foreach ($currentSpotlight['flavor_notes_list'] ?? [] as $chip): ?>
                        <span style="font-size: 10.5px; padding: 2px 7px; border-radius: 5px; background: rgba(199, 166, 106, 0.12); color: var(--gold); font-weight: 500;"><?= htmlspecialchars($chip) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <div style="font-size: 11.5px; color: var(--text-light);">
                    Rotates: <strong style="color: var(--text-main);" id="spotlightTimer">Every 6 Hours</strong>
                </div>
                <button type="button" id="btnRefreshSpotlight" class="btn btn-outline btn-sm" onclick="triggerSpotlightRegeneration()" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                    <svg id="refreshIcon" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>⚡ Regenerate via AI</span>
                </button>
            </div>
        </div>

        <!-- System Health & Diagnostics Card -->
        <div class="form-card" style="margin-bottom: 0;">
            <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>System Health & Diagnostics</span>
                <span class="pulse-dot" title="Server Alive"></span>
            </div>
            <div class="diag-grid">
                <div class="diag-item">
                    <div class="diag-label">PHP Engine</div>
                    <div class="diag-value">v<?php echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION; ?></div>
                </div>
                <div class="diag-item">
                    <div class="diag-label">Database</div>
                    <div class="diag-value"><span class="diag-badge-ok">&bull;</span> Connected</div>
                </div>
                <div class="diag-item">
                    <div class="diag-label">Uploads Disk</div>
                    <div class="diag-value">
                        <?php if ($uploadsWritable): ?>
                            <span class="diag-badge-ok">&check; Writable</span>
                        <?php else: ?>
                            <span style="color:#d32f2f; font-size:11px;">Read-only</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="diag-item">
                    <div class="diag-label">Memory Usage</div>
                    <div class="diag-value"><?php echo round(memory_get_usage(true) / 1024 / 1024, 1); ?> MB</div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="editor-card" style="margin-bottom: 0; display: flex; flex-direction: column; flex-grow: 1;">
            <div class="editor-title" style="margin-bottom: 18px;">
                <span>System Activity Stream</span>
            </div>
            
            <?php if (empty($activities)): ?>
                <div style="flex-grow:1; display:flex; align-items:center; justify-content:center; padding: 40px; color: var(--text-light);">
                    No recent activity recorded.
                </div>
            <?php else: ?>
                <div class="timeline" style="flex-grow: 1;">
                    <?php foreach ($activities as $act): ?>
                        <?php 
                            $timeStr = time_elapsed_string($act['created_at']);
                            $iconClass = 'blog';
                            $title = '';
                            $desc = '';

                            if ($act['type'] === 'order') {
                                $iconClass = 'subscriber';
                                $title = 'Store Order Placed';
                                $desc = 'Order <strong>#' . htmlspecialchars($act['title']) . '</strong> for ' . htmlspecialchars($act['subtitle']);
                            } elseif ($act['type'] === 'subscriber') {
                                $iconClass = 'subscriber';
                                $title = 'New Subscriber';
                                $desc = '<strong>' . htmlspecialchars($act['title']) . '</strong> joined newsletter.';
                            } elseif ($act['type'] === 'message') {
                                $iconClass = 'message';
                                $title = 'New Inquiry';
                                $desc = 'From <strong>' . htmlspecialchars($act['title']) . '</strong>: "' . htmlspecialchars($act['subtitle']) . '"';
                            } elseif ($act['type'] === 'blog') {
                                $iconClass = 'blog';
                                $title = 'Story Published/Updated';
                                $desc = 'Blog article <strong>' . htmlspecialchars($act['title']) . '</strong> updated.';
                            }
                        ?>
                        <div class="timeline-item <?php echo $iconClass; ?>">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <span class="timeline-time"><?php echo $timeStr; ?> &bull; <?php echo date('h:i A', strtotime($act['created_at'])); ?></span>
                                <span class="timeline-title" style="color: var(--text-main); font-size:13px;"><?php echo $title; ?></span>
                                <span class="timeline-desc" style="font-size:12px;"><?php echo $desc; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js Render Scripts & Counter Animation -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Counter Animation
        const counters = document.querySelectorAll('.count-up');
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            const step = Math.max(1, Math.ceil(target / 40));
            const timer = setInterval(() => {
                count += step;
                if (count >= target) {
                    counter.innerText = target;
                    clearInterval(timer);
                } else {
                    counter.innerText = count;
                }
            }, 25);
        });

        // Chart.js Theme Helpers
        function isDarkTheme() {
            return document.documentElement.getAttribute('data-theme') === 'dark';
        }

        function getChartColors() {
            const dark = isDarkTheme();
            return {
                text: dark ? '#D1C2B4' : '#5A473C',
                grid: dark ? 'rgba(212, 175, 55, 0.08)' : 'rgba(29, 21, 16, 0.06)',
                gold: '#D4AF37',
                goldLight: '#E8D293',
                green: '#10B981',
                greenLight: '#34D399',
                bgCard: dark ? '#140F0C' : '#FFFFFF'
            };
        }

        const chartLabels = <?php echo json_encode($chartLabels); ?>;
        const revenueSeries = <?php echo json_encode($revenueSeries); ?>;
        const ordersSeries = <?php echo json_encode($ordersSeries); ?>;
        const catLabels = <?php echo json_encode($catLabels); ?>;
        const catCounts = <?php echo json_encode($catCounts); ?>;

        let velocityChart = null;
        let donutChart = null;

        function initCharts() {
            const colors = getChartColors();

            // 1. Revenue & Order Velocity Chart
            const ctxVelocity = document.getElementById('revenueVelocityChart');
            if (ctxVelocity) {
                if (velocityChart) velocityChart.destroy();

                const gradRev = ctxVelocity.getContext('2d').createLinearGradient(0, 0, 0, 260);
                gradRev.addColorStop(0, 'rgba(199, 166, 106, 0.35)');
                gradRev.addColorStop(1, 'rgba(199, 166, 106, 0.0)');

                velocityChart = new Chart(ctxVelocity, {
                    type: 'line',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: 'Revenue (₹)',
                                data: revenueSeries,
                                borderColor: colors.gold,
                                backgroundColor: gradRev,
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointBackgroundColor: colors.gold,
                                pointRadius: 3.5,
                                pointHoverRadius: 6,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Orders Count',
                                data: ordersSeries,
                                borderColor: colors.greenLight,
                                backgroundColor: 'transparent',
                                borderDash: [4, 4],
                                tension: 0.3,
                                borderWidth: 2,
                                pointBackgroundColor: colors.greenLight,
                                pointRadius: 3,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'end',
                                labels: {
                                    color: colors.text,
                                    font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' },
                                    boxWidth: 12,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: isDarkTheme() ? '#16120F' : '#FEFDFB',
                                titleColor: isDarkTheme() ? '#F6F2EA' : '#3B2A22',
                                bodyColor: isDarkTheme() ? '#D4BA8A' : '#5C4033',
                                borderColor: colors.gold,
                                borderWidth: 1,
                                padding: 10,
                                boxPadding: 4,
                                usePointStyle: true,
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.yAxisID === 'y') {
                                            return ' Revenue: ₹' + Number(context.raw).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                                        }
                                        return ' Orders: ' + context.raw;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: colors.grid },
                                ticks: { color: colors.text, font: { family: 'Plus Jakarta Sans', size: 10.5 } }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: colors.grid },
                                ticks: {
                                    color: colors.text,
                                    font: { family: 'Plus Jakarta Sans', size: 10.5 },
                                    callback: function(value) { return '₹' + value; }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    color: colors.text,
                                    font: { family: 'Plus Jakarta Sans', size: 10.5 },
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // 2. Category Donut Chart
            const ctxDonut = document.getElementById('categoryDonutChart');
            if (ctxDonut) {
                if (donutChart) donutChart.destroy();

                const donutPalette = [
                    '#D4AF37', // Champagne Gold
                    '#E8D293', // Light Gold
                    '#A87948', // Warm Caramel
                    '#5C4033', // Deep Truffle
                    '#10B981', // Pistachio Mint
                    '#F59E0B'  // Honey Amber
                ];

                donutChart = new Chart(ctxDonut, {
                    type: 'doughnut',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            data: catCounts,
                            backgroundColor: donutPalette.slice(0, catLabels.length),
                            borderWidth: 2,
                            borderColor: colors.bgCard,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: colors.text,
                                    font: { family: 'Plus Jakarta Sans', size: 11, weight: '500' },
                                    boxWidth: 10,
                                    usePointStyle: true,
                                    padding: 14
                                }
                            },
                            tooltip: {
                                backgroundColor: isDarkTheme() ? '#16120F' : '#FEFDFB',
                                titleColor: isDarkTheme() ? '#F6F2EA' : '#3B2A22',
                                bodyColor: isDarkTheme() ? '#D4BA8A' : '#5C4033',
                                borderColor: colors.gold,
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.raw + ' items';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        // Initialize charts
        initCharts();

        // Listen for theme changes to automatically refresh charts with proper colors
        window.addEventListener('themechanged', function() {
            setTimeout(initCharts, 50);
        });
    });

    function triggerSpotlightRegeneration() {
        const btn = document.getElementById('btnRefreshSpotlight');
        const icon = document.getElementById('refreshIcon');
        if (!btn) return;

        btn.disabled = true;
        btn.style.opacity = '0.6';
        if (icon) {
            icon.style.transition = 'transform 0.5s';
            icon.style.animation = 'spinSpotlight 1s linear infinite';
        }

        fetch('../api_generate_spotlight.php?action=regenerate', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.spotlight) {
                    const s = data.spotlight;
                    if (document.getElementById('spotlightTag')) document.getElementById('spotlightTag').textContent = s.tag || '🌱 INGREDIENT SPOTLIGHT';
                    if (document.getElementById('spotlightName')) document.getElementById('spotlightName').textContent = s.ingredient_name;
                    if (document.getElementById('spotlightDesc')) document.getElementById('spotlightDesc').textContent = s.short_desc;
                    if (document.getElementById('spotlightOrigin')) document.getElementById('spotlightOrigin').textContent = s.origin_region || '';
                    
                    const chipsEl = document.getElementById('spotlightChips');
                    if (chipsEl && s.flavor_notes_list) {
                        chipsEl.innerHTML = s.flavor_notes_list.map(c => `<span style="font-size: 10.5px; padding: 2px 7px; border-radius: 5px; background: rgba(199, 166, 106, 0.12); color: var(--gold); font-weight: 500;">${c}</span>`).join('');
                    }

                    // Temporary visual feedback
                    btn.classList.remove('btn-outline');
                    btn.classList.add('btn-primary');
                    btn.innerHTML = '<span>✓ AI Refreshed!</span>';
                    setTimeout(() => {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline');
                        btn.innerHTML = `<svg id="refreshIcon" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> <span>⚡ Regenerate via AI</span>`;
                    }, 2500);
                } else {
                    alert(data.message || 'Could not regenerate spotlight.');
                }
            })
            .catch(err => {
                console.error('Spotlight generation error:', err);
                alert('Connection to AI service failed. Please check your OpenRouter API key.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
                if (icon) icon.style.animation = 'none';
            });
    }
</script>
<style>
@keyframes spinSpotlight {
    100% { transform: rotate(360deg); }
}
</style>

<?php
render_admin_footer();
?>
