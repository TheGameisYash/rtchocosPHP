<?php
// admin/dashboard.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();

// Set default timezone for relative calculations
date_default_timezone_set('Asia/Kolkata');

// Fetch metrics
try {
    // 1. E-Commerce Metrics
    $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();

    // Recent Orders (latest 4)
    $recentOrders = $pdo->query("SELECT id, order_number, customer_name, total, order_status, payment_status, created_at FROM orders ORDER BY created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

    // 2. Blog counts
    $totalBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    $publishedBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 1")->fetchColumn();
    $draftBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 0")->fetchColumn();
    
    // 3. Subscriber counts
    $totalSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    $newSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
    
    // 4. Message count
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    // 5. Analytics views count
    $totalViews = (int)$pdo->query("SELECT SUM(views) FROM blogs")->fetchColumn();

    // 6. Recent Blogs (latest 3)
    $stmt = $pdo->query("SELECT id, title, slug, excerpt, category, thumbnail_path, is_published, views, created_at FROM blogs ORDER BY created_at DESC LIMIT 3");
    $recentBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Unified Activity Timeline (fetch from orders, subscribers, contacts, and blogs, then merge)
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
    // Limit to latest 6 events
    $activities = array_slice($activities, 0, 6);

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

render_admin_header("Dashboard", "dashboard");
?>

<!-- Welcome Banner -->
<div class="form-card" style="background: linear-gradient(135deg, var(--green-900) 0%, var(--green-800) 100%); color: var(--white); border: none; padding: 24px 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; margin-bottom: 32px; box-shadow: var(--shadow-md);">
    <div style="flex: 1; min-width: 250px;">
        <h3 style="font-family: var(--font-heading); font-size: 26px; font-weight: 700; color: var(--gold); margin-bottom: 6px;">
            <?php echo $greeting; ?>, <?php echo htmlspecialchars($adminUser); ?>!
        </h3>
        <p style="font-size: 14px; color: rgba(246, 242, 234, 0.85); line-height: 1.5; max-width: 600px;">
            Store operations are active. You have <strong style="color: var(--gold-light);"><?php echo $pendingOrders; ?> pending order<?php echo $pendingOrders !== 1 ? 's' : ''; ?></strong> requiring packaging & dispatch, and <strong><?php echo $unreadMessages; ?></strong> unread customer messages.
        </p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="product-editor.php" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            Add Product
        </a>
        <a href="orders.php" class="btn btn-outline" style="border-color: rgba(246, 242, 234, 0.4); color: var(--white);">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            Orders (<?php echo $pendingOrders; ?>)
        </a>
        <a href="blog-editor.php" class="btn btn-outline" style="border-color: rgba(246, 242, 234, 0.3); color: var(--white);">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            Write Blog
        </a>
    </div>
</div>

<!-- Metrics Dashboard Cards -->
<div class="metrics-grid" style="margin-bottom: 28px;">
    <!-- Store Revenue -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Store Revenue</div>
            <div class="metric-value" style="color: var(--gold-light);">₹<?php echo number_format($totalRevenue, 2); ?></div>
            <div style="font-size: 11px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                From paid customer checkouts
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <!-- Store Orders -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Store Orders</div>
            <div class="metric-value count-up" data-target="<?php echo $totalOrders; ?>">0</div>
            <div class="metric-trend <?php echo $pendingOrders > 0 ? 'up' : ''; ?>" style="<?php echo $pendingOrders > 0 ? 'color: #ef6c00;' : ''; ?>">
                <span><?php echo $pendingOrders; ?> pending fulfillment</span>
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        </div>
    </div>

    <!-- Shop Products -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Shop Products</div>
            <div class="metric-value count-up" data-target="<?php echo $totalProducts; ?>">0</div>
            <div style="font-size: 11px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                <span style="color: #2e7d32;"><?php echo $activeProducts; ?> active</span> &bull; <?php echo ($totalProducts - $activeProducts); ?> drafts
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
    </div>

    <!-- Total Blogs -->
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Total Blogs</div>
            <div class="metric-value count-up" data-target="<?php echo $totalBlogs; ?>">0</div>
            <div style="font-size: 11px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                <?php echo $publishedBlogs; ?> Published &bull; <?php echo $draftBlogs; ?> Drafts
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="editor-layout" style="margin-top: 8px; align-items: stretch;">
    
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
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-app);">
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
                <span>Recently Added Blogs</span>
                <a href="blogs.php" class="btn btn-outline btn-sm">All Blogs</a>
            </div>
            
            <?php if (empty($recentBlogs)): ?>
                <div style="text-align: center; padding: 30px; color: var(--text-light);">
                    No blog posts found.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($recentBlogs as $blog): ?>
                        <div style="display: flex; gap: 14px; border: 1px solid var(--border-color); padding: 10px; border-radius: 8px; background: var(--bg-app); position: relative; align-items: center;">
                            <div style="width: 56px; height: 56px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: var(--cream);">
                                <img src="../<?php echo htmlspecialchars($blog['thumbnail_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: center; min-width: 0; flex-grow: 1; padding-right: 36px;">
                                <span style="font-size: 10.5px; text-transform: uppercase; color: var(--gold); font-weight: 600;"><?php echo htmlspecialchars($blog['category']); ?></span>
                                <h4 style="font-size: 13.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 2px 0 4px 0; color: var(--text-main);"><?php echo htmlspecialchars($blog['title']); ?></h4>
                                <div style="font-size: 11px; color: var(--text-light);">
                                    <span><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span> &bull; <span><?php echo $blog['views']; ?> views</span>
                                </div>
                            </div>
                            <a href="blog-editor.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline btn-sm" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); padding: 5px 7px;" title="Edit">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: Recent Activity Timeline -->
    <div class="editor-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
        <div class="editor-title" style="margin-bottom: 20px;">
            <span>System Activity Timeline</span>
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
                            $desc = '<strong>' . htmlspecialchars($act['title']) . '</strong> joined the newsletter.';
                        } elseif ($act['type'] === 'message') {
                            $iconClass = 'message';
                            $title = 'New Message';
                            $desc = 'Message from <strong>' . htmlspecialchars($act['title']) . '</strong>: "' . htmlspecialchars($act['subtitle']) . '"';
                        } elseif ($act['type'] === 'blog') {
                            $iconClass = 'blog';
                            $title = 'Blog Post Updated';
                            $desc = 'Blog article <strong>' . htmlspecialchars($act['title']) . '</strong> updated.';
                        }
                    ?>
                    <div class="timeline-item <?php echo $iconClass; ?>">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <span class="timeline-time"><?php echo $timeStr; ?> &bull; <?php echo date('h:i A', strtotime($act['created_at'])); ?></span>
                            <span class="timeline-title" style="color: var(--text-main); font-size:13.5px;"><?php echo $title; ?></span>
                            <span class="timeline-desc" style="font-size:12.5px;"><?php echo $desc; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Count-up script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.count-up');
        const speed = 200; // The lower the slower

        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;

                // Lower inc to slow and higher to slow
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
            };
            
            // Trigger delay slightly to allow page fade transition to start
            setTimeout(updateCount, 150);
        });
    });
</script>

<?php
render_admin_footer();
?>
