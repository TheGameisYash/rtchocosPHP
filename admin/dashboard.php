<?php
// admin/dashboard.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();

// Set default timezone for relative calculations
date_default_timezone_set('Asia/Kolkata');

// Fetch metrics
try {
    // 1. Blog counts
    $totalBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    $publishedBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 1")->fetchColumn();
    $draftBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 0")->fetchColumn();
    
    // 2. Subscriber counts
    $totalSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    $newSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
    
    // 3. Message count
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    // 4. Analytics views count
    $totalViews = (int)$pdo->query("SELECT SUM(views) FROM blogs")->fetchColumn();

    // 5. Recent Blogs (latest 3)
    $stmt = $pdo->query("SELECT id, title, slug, excerpt, category, thumbnail_path, is_published, views, created_at FROM blogs ORDER BY created_at DESC LIMIT 3");
    $recentBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Unified Activity Timeline (fetch from subscribers, contacts, and blogs, then merge)
    $activities = [];
    
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
            Welcome back to your workspace. The site is running smoothly. You have <strong><?php echo $unreadMessages; ?></strong> unread messages requiring attention.
        </p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="blog-editor.php" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            Write Blog
        </a>
        <a href="settings.php" class="btn btn-outline" style="border-color: rgba(246, 242, 234, 0.3); color: var(--white);">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Settings
        </a>
    </div>
</div>

<!-- Metrics Dashboard Cards -->
<div class="metrics-grid">
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
    
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Subscribers</div>
            <div class="metric-value count-up" data-target="<?php echo $totalSubscribers; ?>">0</div>
            <div class="metric-trend up">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:2px;"><path d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                <span>+<?php echo $newSubscribers; ?> this week</span>
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Unread Inbox</div>
            <div class="metric-value count-up" style="color: <?php echo $unreadMessages > 0 ? '#d32f2f' : 'inherit'; ?>;" data-target="<?php echo $unreadMessages; ?>">0</div>
            <div style="font-size: 11px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                <?php echo $unreadMessages > 0 ? 'Requires attention' : 'Inbox is clean'; ?>
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        </div>
    </div>
    
    <div class="metric-card">
        <div class="metric-info">
            <div class="metric-title">Total Views</div>
            <div class="metric-value count-up" data-target="<?php echo $totalViews; ?>">0</div>
            <div style="font-size: 11px; margin-top: 8px; color: var(--text-light); font-weight: 500;">
                Accumulated views
            </div>
        </div>
        <div class="metric-icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
    </div>
</div>

<div class="editor-layout" style="margin-top: 8px; align-items: stretch;">
    <!-- Recent Blogs Row -->
    <div style="grid-column: span 1; display: flex; flex-direction: column; gap: 24px;">
        <div class="form-card" style="margin-bottom: 0; flex-grow: 1; display: flex; flex-direction: column;">
            <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <span>Recently Added Blogs</span>
                <a href="blogs.php" class="btn btn-outline btn-sm">All Blogs</a>
            </div>
            
            <?php if (empty($recentBlogs)): ?>
                <div style="flex-grow:1; display:flex; align-items:center; justify-content:center; padding: 40px; color: var(--text-light);">
                    No blog posts found.
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px; flex-grow: 1;">
                    <?php foreach ($recentBlogs as $blog): ?>
                        <div style="display: flex; gap: 16px; border: 1px solid var(--border-color); padding: 12px; border-radius: 8px; background: var(--bg-app); position: relative;">
                            <div style="width: 80px; height: 80px; border-radius: 6px; overflow: hidden; flex-shrink: 0; background: var(--cream);">
                                <img src="../<?php echo htmlspecialchars($blog['thumbnail_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width:100%; height:100%; object-fit:cover;">
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: center; min-width: 0; flex-grow: 1; padding-right: 48px;">
                                <span style="font-size: 11px; text-transform: uppercase; color: var(--gold); font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($blog['category']); ?></span>
                                <h4 style="font-size: 14.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 6px; color: var(--text-main);"><?php echo htmlspecialchars($blog['title']); ?></h4>
                                <div style="display: flex; align-items: center; gap: 12px; font-size: 11.5px; color: var(--text-light);">
                                    <span><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                                    <span>&bull;</span>
                                    <span><?php echo $blog['views']; ?> views</span>
                                    <span>&bull;</span>
                                    <span class="status-badge <?php echo $blog['is_published'] ? 'published' : 'draft'; ?>" style="font-size: 9px; padding: 2px 6px;"><?php echo $blog['is_published'] ? 'Published' : 'Draft'; ?></span>
                                </div>
                            </div>
                            <a href="blog-editor.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline btn-sm" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); padding: 6px 8px;" title="Edit">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin:0;"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
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

                        if ($act['type'] === 'subscriber') {
                            $iconClass = 'subscriber';
                            $title = 'New Subscriber';
                            $desc = '<strong>' . htmlspecialchars($act['title']) . '</strong> joined the mailing list.';
                        } elseif ($act['type'] === 'message') {
                            $iconClass = 'message';
                            $title = 'New Message';
                            $desc = 'Message from <strong>' . htmlspecialchars($act['title']) . '</strong>: "' . htmlspecialchars($act['subtitle']) . '"';
                        } elseif ($act['type'] === 'blog') {
                            $iconClass = 'blog';
                            $title = 'Blog Post Updated';
                            $desc = 'Blog article <strong>' . htmlspecialchars($act['title']) . '</strong> was configured/published.';
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
