# RT Chocos — Complete Technical Architecture & AI Reference Guide

> **Note for AI LLMs & Agents**: This document provides an exhaustive, single-file map of the entire codebase, routing model, database schema, and design patterns. Reading this file gives you complete contextual understanding of the repository without requiring full codebase scans.

---

## 1. System Overview & Technology Stack

**RT Chocos** (`rtchocos.com`) is built as a lightweight, high-performance web platform combining a content-managed blog, bean-to-bar chocolate academy, e-commerce shop, and admin CMS.

- **Backend**: Native PHP 8.x (No heavy framework dependencies for maximum performance & portability).
- **Database**: MySQL 8.x (PDO with prepared statements for security).
- **Frontend**: Standard HTML5, custom responsive CSS variables (`style.css`), vanilla JavaScript (`script.js`).
- **Routing Engine**:
  - *Production (Apache)*: `.htaccess` mod_rewrite.
  - *Local Dev (PHP Built-in Server)*: `router.php`.
- **Environment Config**: Native `.env` loader via `includes/env_loader.php`.

---

## 2. Directory Structure & File Map

### Core Public Pages (Root Level)
| File | Role & Description |
| :--- | :--- |
| `index.php` | Homepage — Hero, Academy highlights, featured blog grid, workshop callouts, recipes showcase. |
| `about.php` | Founder profile (Aarti Saluja Sahni), bean-to-bar education background, consulting services. |
| `blog.php` | Blog Hub page — Displays published articles, category filter tabs, search bar. |
| `blog-article.php` | Full article renderer — Parses Markdown content, renders breadcrumbs, author info, comments. |
| `gallery.php` | Recipe & Formulations Hub — Displays recipe cards, category filtering (Bars, Truffles, Bonbons). |
| `workshops.php` | Academy workshops & masterclass registration details. |
| `shop.php` | E-commerce shop catalog — Products, prices, stock filters. |
| `product.php` | Single product detail page — Image gallery, description, add to cart. |
| `cart.php` | Shopping cart drawer / view page. |
| `checkout.php` | Checkout flow & order processing. |
| `contact.php` | Contact form & consulting inquiry page. |
| `faq.php` | Categorized FAQ page (General, Workshops, Shop, Shipping, Courses). |
| `brand-listicle.php` | SEO Listicle page ("Top Indian Craft Chocolate Brands"). |
| `error.php` | Custom 404 / 500 error page. |
| `sitemap.php` | Dynamic XML sitemap generator for SEO search crawlers. |
| `robots.txt` | Search engine crawl directives. |
| `llms.txt` | LLM web summary file for AI crawlers. |

### `api/` Directory (Backend JSON API Modules)
| File | Role & Description |
| :--- | :--- |
| `api/blogs.php` | JSON API endpoint returning published blog articles list. |
| `api/cart.php` | JSON API endpoint for cart operations (add, remove, update quantity). |
| `api/comments.php` | JSON API endpoint for fetching & submitting article comments. |
| `api/contact.php` | Contact form AJAX backend & email/CSV storer. |
| `api/subscribe.php` | Newsletter subscription AJAX backend & email storer. |

### `tools/` Directory (Maintenance & Utilities)
| File | Role & Description |
| :--- | :--- |
| `tools/db_setup.php` | Database setup & migration script — Creates tables, seeds initial data, adds indexes. |
| `tools/create_symlink.php` | Live host utility script — Symlinks `uploads_blogs` outside `public_html` to `assets/blogs`. |
| `tools/test_blog.php` | CLI automated test suite — Verifies backend blog routing, rendering, and API structures. |

### System & Infrastructure Scripts
| File | Role & Description |
| :--- | :--- |
| `router.php` | Local dev server router — Maps clean extensionless URLs (e.g. `/blog`, `/about`) to `.php` files. |
| `start.bat` | Windows batch launcher script running `php -S localhost:8000 router.php`. |
| `.htaccess` | Production Apache configuration — Compression, caching, security headers, clean URLs. |
| `blog_image.php` | Image router — Serves uploaded images from `uploads_blogs/` or streams live fallback images. |

### `includes/` Directory (Shared Components)
| File | Role & Description |
| :--- | :--- |
| `db.php` | Centralized PDO database connection singleton function (`get_db()`). |
| `env_loader.php` | Native PHP `.env` file loader function (`load_env_file()`). |
| `blog-data.php` | Static array `$BLOGS` containing fallback blog metadata & markdown paths. |
| `blog-cache.php` | JSON file-caching utility for blog lists & articles to ensure offline resilience. |
| `header.php` | Master HTML head, navigation header, meta tags, schema.org JSON-LD structured data. |
| `footer.php` | Master site footer, footer navigation, copyright, script includes. |
| `comments.php` | Article comments UI rendering component. |
| `faq-block.php` | Reusable FAQ UI block component. |
| `payment.php` | Payment gateway integration helper functions. |

### `data/` Directory (File Storage & Cache)
| Path / File | Role & Description |
| :--- | :--- |
| `data/cache/` | Offline JSON cache store — Stores `blogs.json` and `article-{slug}.json` files generated by `includes/blog-cache.php`. |
| `data/subscribers.csv` | Legacy subscriber data CSV used during database setup migrations. |

### `admin/` Directory (CMS Management Panel)
| File | Role & Description |
| :--- | :--- |
| `admin/login.php` | Admin authentication login form. |
| `admin/auth.php` | Session-based authentication check & password verification helper. |
| `admin/dashboard.php` | Admin overview dashboard — Analytics, view counts, recent contacts, quick actions. |
| `admin/blogs.php` | Blog list management table — Publish, unpublish, edit, delete, views counter. |
| `admin/blog-editor.php` | Rich blog article editor — Title, category, excerpt, Markdown body, image upload. |
| `admin/comments.php` | Comment moderation interface — Approve, reject, delete user comments. |
| `admin/contacts.php` | Contact inbox — View form submissions, phone numbers, mark as read. |
| `admin/subscribers.php` | Newsletter subscriber management & CSV export. |
| `admin/media.php` | Media library manager — Upload, view, and organize uploaded blog images. |
| `admin/settings.php` | Site settings key-value editor (stored in `site_settings` table). |
| `admin/changelog.php` | Version history & feature release log dashboard page. |
| `admin/editor.js` | Custom WYSIWYG editor logic for blog content formatting & media embed. |
| `admin/admin-style.css` | Stylesheet for admin dashboard layout & UI. |
| `admin/editor-style.css` | Stylesheet for rich article editor. |
| `admin/logout.php` | Admin session logout endpoint. |

---

## 3. Clean URL Routing Architecture

The site uses extensionless, clean URLs (e.g. `/blog` instead of `/blog.php`, `/about` instead of `/about.php`, `/blog/cocoa-ph` for articles).

### How Routing Works in Production (`.htaccess`)
```apache
# 1. Map /blog and /blog/ to root blog.php
RewriteRule ^blog/?$ blog.php [L]

# 2. Redirect explicit .php requests to clean extensionless URLs (SEO 301)
RewriteCond %{THE_REQUEST} ^[A-Z]{3,}\s/([^/.]+)\.php [NC]
RewriteRule ^ %1 [R=301,L]

# 3. Internally map extensionless requests (e.g. /about -> about.php)
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^([^/.]+)/?$ $1.php [NC,L]

# 4. Route article slugs: /blog/{slug} -> blog/article.php?slug={slug}
RewriteRule ^blog/([^/]+)$ blog/article.php?slug=$1 [L,QSA]

# 5. Route missing blog images to blog_image.php
RewriteRule ^assets/blogs/(.+)$ blog_image.php?file=$1 [L,QSA]
```

### How Routing Works in Local Dev (`router.php`)
Because PHP's built-in server treats physical directories specially, `/blog` would return 404 without `router.php` because `blog` exists as a folder. `router.php` intercepts incoming requests:
```php
// If request is /blog, serve blog.php
if ($uri === '/blog' || $uri === '/blog/') {
    include __DIR__ . '/blog.php';
    return true;
}
// If request is /blog/{slug}, serve blog-article.php with $articleKey
if (preg_match('#^/blog/([^/]+)$#', $uri, $m)) {
    $articleKey = $m[1];
    include __DIR__ . '/blog-article.php';
    return true;
}
```

---

## 4. Complete Database Schema (10 Tables)

### 1. `admins`
Stores backend admin user credentials.
- `id` (INT, PK, Auto-increment)
- `username` (VARCHAR(50), Unique)
- `password` (VARCHAR(255), BCRYPT Hash)
- `created_at` (TIMESTAMP)

### 2. `blogs`
Core content repository for blog articles.
- `id` (INT, PK, Auto-increment)
- `slug` (VARCHAR(100), Unique)
- `title` (VARCHAR(255))
- `category` (VARCHAR(50))
- `excerpt` (TEXT)
- `content` (LONGTEXT, Markdown format)
- `image_path` (VARCHAR(255), Header image path)
- `thumbnail_path` (VARCHAR(255), Card thumbnail path)
- `body_class` (VARCHAR(100))
- `youtube_url` (VARCHAR(255))
- `read_time` (VARCHAR(50))
- `views` (INT, Default 0)
- `is_published` (TINYINT(1), Default 1)
- `scheduled_at` (TIMESTAMP, Nullable)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 3. `subscribers`
Newsletter email signups.
- `id` (INT, PK, Auto-increment)
- `email` (VARCHAR(150), Unique)
- `created_at` (TIMESTAMP)

### 4. `contacts`
Inquiries submitted via contact form.
- `id` (INT, PK, Auto-increment)
- `name` (VARCHAR(100))
- `email` (VARCHAR(150))
- `phone` (VARCHAR(50))
- `subject` (VARCHAR(150))
- `message` (TEXT)
- `is_read` (TINYINT(1), Default 0)
- `created_at` (TIMESTAMP)

### 5. `comments`
User comments on blog articles.
- `id` (INT, PK, Auto-increment)
- `blog_slug` (VARCHAR(100), Index)
- `name` (VARCHAR(100))
- `comment` (TEXT)
- `is_approved` (TINYINT(1), Default 1)
- `created_at` (TIMESTAMP)

### 6. `site_settings`
Key-value store for site configuration.
- `setting_key` (VARCHAR(100), PK)
- `setting_value` (TEXT)

### 7. `products`
E-commerce catalog items.
- `id` (INT, PK, Auto-increment)
- `slug` (VARCHAR(150), Unique)
- `name` (VARCHAR(255))
- `short_description` (TEXT)
- `long_description` (LONGTEXT)
- `price` (DECIMAL(10,2))
- `sale_price` (DECIMAL(10,2), Nullable)
- `category` (VARCHAR(100))
- `stock_quantity` (INT)
- `image_main` (VARCHAR(255))
- `image_gallery` (JSON)
- `meta_title`, `meta_description`, `meta_keywords`
- `is_featured`, `is_active`
- `created_at`, `updated_at`

### 8. `faqs`
Categorized Q&A items.
- `id` (INT, PK, Auto-increment)
- `question` (VARCHAR(255))
- `answer` (TEXT)
- `category` (ENUM: 'general', 'workshops', 'shop', 'shipping', 'courses')
- `display_order` (INT)
- `is_active` (TINYINT(1))

### 9. `media`
Admin media library file registry.
- `id` (INT, PK, Auto-increment)
- `filename` (VARCHAR(255))
- `path` (VARCHAR(255))
- `mime_type` (VARCHAR(100))
- `size` (INT)
- `uploaded_at` (TIMESTAMP)

### 10. `blog_tags` & `blog_tag_map`
Tagging system for articles.
- `blog_tags`: `id`, `name`, `slug`
- `blog_tag_map`: `blog_id`, `tag_id` (Composite PK)

---

## 5. Sub-directory Path Resolution Pattern (`$pathPrefix`)

Shared template files (`includes/header.php`, `includes/footer.php`) are included from both root-level files and sub-directory scripts (such as `blog/article.php`).

To ensure CSS, JS, images, and links resolve correctly regardless of depth, every page defines a `$pathPrefix` string before including header/footer:

```php
// In root-level pages (e.g. blog.php):
$pathPrefix = "";

// In sub-directory pages (e.g. blog/article.php):
$pathPrefix = "../";

// In includes/header.php:
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>style.css">
<img src="<?php echo $pathPrefix; ?>assets/logo.png">
```

---

## 6. Development Workflow Guidelines

1. **Starting Local Server**: Run `start.bat` or `php -S localhost:8000 router.php`.
2. **Database Migrations**: Run `php tools/db_setup.php` to initialize or update tables locally.
3. **Automated Testing**: Run `php tools/test_blog.php` to execute the CLI test suite.
4. **Adding New Pages**: Create the root `.php` file and add clean URL rewrite rules to both `.htaccess` and `router.php`.
5. **Modifying Shared Layouts**: Always check `$pathPrefix` usage when editing `includes/header.php` or `includes/footer.php`.
