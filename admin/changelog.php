<?php
// admin/changelog.php - Website Changelog & Version History
require_once __DIR__ . '/layout.php';

// Changelog data - add new entries at the top
$changelog = [
    [
        'version' => '2.0.0-beta',
        'date' => '27 June 2026',
        'tag' => 'Major Release',
        'tag_color' => 'gold',
        'summary' => 'Massive website overhaul — new blog engine, SEO infrastructure, comments system, security hardening, and performance optimization.',
        'sections' => [
            [
                'title' => '✍️ Enhanced Blog Editor',
                'items' => [
                    'Upgraded Markdown parser to support fenced code blocks with language syntax highlighting',
                    'Added pipe-based table rendering inside blog articles',
                    'YouTube video embedding via <code>{{youtube:VIDEO_ID}}</code> template tags',
                    'Image positioning system — authors can now place images <strong>left</strong>, <strong>center</strong>, <strong>right</strong>, or <strong>end of blog</strong>',
                    'New alignment dropdown selector in the block editor for image blocks',
                    'Inline code styling with backtick notation',
                    'Editor preview now matches live article rendering 1:1',
                ]
            ],
            [
                'title' => '🔍 SEO & Discoverability',
                'items' => [
                    'Dynamic XML sitemap generation (<code>/sitemap.xml</code>) pulling live blog slugs from database',
                    'Added <code>image:image</code> XML sitemap tagging with metadata to index blog header pictures in Google Image search',
                    'Clean blog URLs: <code>/blog/your-article-slug</code> instead of query parameters',
                    'Open Graph and Twitter Card meta tags for every blog article',
                    'Rich JSON-LD Schema graphs supporting <code>Organization</code>, <code>WebSite</code>, <code>BlogPosting</code>, <code>LocalBusiness</code>, <code>BreadcrumbList</code>, <code>Recipe</code>, and <code>Course</code>',
                    'Dynamic keywords and descriptive page titles matching search queries',
                    'Server-side heading ID anchor generator (<code>h2/h3</code>) enabling Google sitelinks',
                    'Visible breadcrumbs trail showing site hierarchy on blog article pages',
                    'Related articles recommendation carousel showing category-matched insights',
                    'Canonical URL tags to prevent duplicate content indexing',
                    'Updated <code>robots.txt</code> to block admin, cache, and data directories',
                    'Resource preconnect hints for Google Fonts and external CDNs',
                ]
            ],
            [
                'title' => '🛠️ Admin Panel & Toast UI',
                'items' => [
                    'Added a Changelog timeline view detailing all project changes',
                    'Fixed Toast Notifications timer so notifications dismiss cleanly after 5 seconds',
                    'Fixed close cross button handler in layout script to support immediate toast removal',
                    'Added theme switch toggle button checks to prevent layout crashes',
                ]
            ],
            [
                'title' => '🛡️ Database Resilience',
                'items' => [
                    'Introduced file-based JSON caching layer (<code>data/cache/</code>)',
                    '3-tier fallback chain: <strong>Database → File Cache → Static Array</strong>',
                    'Blog listing and individual articles survive complete database outages',
                    'Fixed broken blog image paths for <code>lecithin-chocolate</code> and <code>freeze-dried-fruits-chocolate</code> in the database',
                    'Added safe path resolution checks in <code>blog-article.php</code> to support absolute, root-relative, and relative (<code>../</code>) image sources without breaking page formatting',
                    'Custom styled 404 error page for missing articles',
                    'Sitemap gracefully degrades to cached/static blog list when DB is down',
                ]
            ],
            [
                'title' => '💬 Comments System',
                'items' => [
                    'Server-side comment persistence via MySQL <code>comments</code> table',
                    'REST API endpoint (<code>api_comments.php</code>) for fetching and posting comments',
                    'Session-based rate limiting — max 3 comments per minute per user',
                    'Automatic localStorage fallback when API is unavailable (offline mode)',
                    'Admin moderation dashboard with approve/unapprove and delete actions',
                    'Search and pagination on the comments management page',
                ]
            ],
            [
                'title' => '⚡ Performance',
                'items' => [
                    'Blog listing page now server-side rendered (no JS flash of empty content)',
                    'Native lazy loading (<code>loading="lazy"</code>) on below-the-fold images',
                    'Eliminated unnecessary JS-dependent blog card rendering for crawlers',
                ]
            ],
            [
                'title' => '🔒 Security Hardening',
                'items' => [
                    'Session fixation protection via <code>session_regenerate_id(true)</code> on admin login',
                    'Honeypot spam fields on newsletter subscribe and contact forms',
                    'Rate limiting on subscribe (5/hour), contact (5/hour), and comment (3/min) submissions',
                    'Security headers: X-Content-Type-Options, X-Frame-Options, Referrer-Policy via .htaccess',
                    'Direct access to <code>/includes/</code> and <code>/data/</code> directories blocked (403)',
                    'CSRF token validation on all admin form submissions',
                ]
            ],
            [
                'title' => '🎨 Blog Content Styling',
                'items' => [
                    'Float-based image alignment classes (<code>.blog-img-left</code>, <code>.blog-img-right</code>, etc.)',
                    'Responsive 16:9 YouTube embed container with rounded corners',
                    'Styled code blocks with dark theme background and monospace fonts',
                    'Blog content tables with striped rows and gold-accented headers',
                ]
            ],
        ]
    ],
    [
        'version' => '1.0.0',
        'date' => '14 June 2026',
        'tag' => 'Initial Launch',
        'tag_color' => 'green',
        'summary' => 'Initial website launch with core pages, admin panel, blog system, media library, and contact functionality.',
        'sections' => [
            [
                'title' => '🚀 Core Features',
                'items' => [
                    'Homepage with hero section, workshop cards, testimonials, gallery preview, and newsletter signup',
                    'Dynamic blog system with category filtering, search, and article pages',
                    'Admin panel with dashboard, blog editor, media library, subscribers, contacts, and settings',
                    'Block-based Gutenberg-style blog editor with drag-and-drop reordering',
                    'Contact form with database storage and admin notification badges',
                    'Newsletter subscription system with CSV import/export',
                    'Responsive design across all device sizes',
                ]
            ],
        ]
    ],
];

render_admin_header("Changelog", "changelog");
?>

<style>
    .changelog-container { max-width: 860px; }

    .changelog-version {
        position: relative;
        padding: 32px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 32px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow var(--transition), border-color var(--transition);
    }
    .changelog-version:hover {
        box-shadow: var(--shadow-md);
        border-color: var(--gold);
    }

    .changelog-version-header {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }

    .changelog-version-number {
        font-family: var(--font-heading);
        font-size: 28px;
        font-weight: 700;
        color: var(--text-main);
        letter-spacing: -0.5px;
    }

    .changelog-tag {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-family: var(--font-sans);
    }
    .changelog-tag.gold {
        background: rgba(199, 166, 106, 0.15);
        color: var(--gold);
        border: 1px solid rgba(199, 166, 106, 0.25);
    }
    .changelog-tag.green {
        background: rgba(13, 59, 18, 0.1);
        color: var(--green-600);
        border: 1px solid rgba(13, 59, 18, 0.2);
    }

    .changelog-date {
        font-size: 13px;
        color: var(--text-light);
        font-family: var(--font-sans);
        margin-left: auto;
    }

    .changelog-summary {
        font-size: 15px;
        line-height: 1.7;
        color: var(--text-muted);
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .changelog-section {
        margin-bottom: 24px;
    }
    .changelog-section:last-child {
        margin-bottom: 0;
    }

    .changelog-section-title {
        font-family: var(--font-sans);
        font-size: 15px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 12px;
        letter-spacing: 0.2px;
    }

    .changelog-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .changelog-list li {
        position: relative;
        padding: 6px 0 6px 24px;
        font-size: 14px;
        line-height: 1.7;
        color: var(--text-muted);
        font-family: var(--font-sans);
    }
    .changelog-list li::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 14px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--gold);
        opacity: 0.6;
    }
    .changelog-list li code {
        background: rgba(199, 166, 106, 0.1);
        padding: 2px 7px;
        border-radius: 4px;
        font-size: 12.5px;
        font-family: 'Courier New', monospace;
        color: var(--gold);
    }
    .changelog-list li strong {
        color: var(--text-main);
        font-weight: 600;
    }

    /* Timeline connector line */
    .changelog-timeline {
        position: relative;
        padding-left: 28px;
    }
    .changelog-timeline::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--gold), var(--border-color));
        border-radius: 2px;
    }
    .changelog-timeline .changelog-version {
        position: relative;
    }
    .changelog-timeline .changelog-version::before {
        content: '';
        position: absolute;
        left: -28px;
        top: 40px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--bg-card);
        border: 3px solid var(--gold);
        z-index: 1;
    }

    .changelog-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-light);
        font-style: italic;
    }

    @media (max-width: 768px) {
        .changelog-version { padding: 20px; }
        .changelog-version-number { font-size: 22px; }
        .changelog-date { margin-left: 0; width: 100%; margin-top: 4px; }
        .changelog-timeline { padding-left: 20px; }
        .changelog-timeline .changelog-version::before { left: -20px; width: 10px; height: 10px; }
    }
</style>

<div class="changelog-container">
    <p style="color: var(--text-light); font-size: 14px; margin-bottom: 28px; line-height: 1.7;">
        A record of all notable changes, improvements, and fixes made to the RT Chocos website. Newest changes appear first.
    </p>

    <?php if (empty($changelog)): ?>
        <div class="changelog-empty">No changelog entries yet.</div>
    <?php else: ?>
        <div class="changelog-timeline">
            <?php foreach ($changelog as $entry): ?>
                <div class="changelog-version">
                    <div class="changelog-version-header">
                        <span class="changelog-version-number">v<?php echo htmlspecialchars($entry['version']); ?></span>
                        <span class="changelog-tag <?php echo htmlspecialchars($entry['tag_color']); ?>"><?php echo htmlspecialchars($entry['tag']); ?></span>
                        <span class="changelog-date"><?php echo htmlspecialchars($entry['date']); ?></span>
                    </div>
                    <p class="changelog-summary"><?php echo $entry['summary']; ?></p>

                    <?php foreach ($entry['sections'] as $section): ?>
                        <div class="changelog-section">
                            <div class="changelog-section-title"><?php echo $section['title']; ?></div>
                            <ul class="changelog-list">
                                <?php foreach ($section['items'] as $item): ?>
                                    <li><?php echo $item; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
render_admin_footer();
?>
