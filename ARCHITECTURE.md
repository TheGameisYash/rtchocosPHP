# RT Chocos - Architecture Documentation

> **Last Updated:** 2026-10-23  
> **Complete system architecture and data flow**

---

## 🏗️ System Architecture Overview

### **Architecture Pattern**
- **Type:** Monolithic MVC-style (no framework)
- **Backend:** Pure PHP with PDO
- **Frontend:** Vanilla JavaScript (no frameworks)
- **Database:** MySQL with prepared statements
- **APIs:** RESTful JSON endpoints

### **Request Flow**

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       ↓
┌─────────────────────────────────────┐
│  Apache/Nginx + .htaccess/router.php │
└──────┬──────────────────────────────┘
       │
       ↓
┌─────────────────────────────────────┐
│        Page Entry Point              │
│  (index.php, blog.php, shop.php)    │
└──────┬──────────────────────────────┘
       │
       ├─→ includes/header.php (navigation)
       │
       ├─→ includes/db.php (database)
       │
       ├─→ Page-specific content
       │
       └─→ includes/footer.php
       │
       ↓
   ┌─────────────┐
   │  JavaScript  │
   │  Initialize  │
   └─────────────┘
```

---

## 📂 Directory Structure (Detailed)

```
RT Chocos/
│
├── 📄 index.php               # Homepage (landing page)
├── 📄 router.php              # Dev server routing
├── 📄 db_setup.php           # Initial database setup
├── 📄 .htaccess              # Apache rewrite rules
│
├── 📁 includes/              # Shared PHP components
│   ├── db.php               # Database singleton
│   ├── env_loader.php       # Environment config
│   ├── header.php           # Global navigation
│   ├── footer.php           # Global footer
│   ├── blog-cache.php       # Blog caching layer
│   ├── blog-data.php        # Blog data access
│   ├── blog-uploads.php     # Image upload handling
│   ├── mailer.php           # Email sending
│   ├── payment.php          # Payment processing
│   ├── ingredient_spotlight.php  # AI spotlight generation
│   ├── workshops_data.php   # Workshop data
│   ├── maintenance_check.php # Maintenance mode
│   └── components/
│       ├── preloader.php    # Page loader
│       └── ai_drawer.php    # AI assistant UI
│
├── 📁 admin/                 # Admin dashboard
│   ├── index.php            # Admin home redirect
│   ├── login.php            # Admin authentication
│   ├── dashboard.php        # Admin overview
│   ├── blogs.php            # Blog management
│   ├── blog-editor.php      # WYSIWYG blog editor
│   ├── products.php         # Product management
│   ├── orders.php           # Order management
│   ├── innovation-lab.php   # Lab content management
│   ├── settings.php         # Site settings
│   ├── media.php            # Media library
│   └── layout.php           # Admin layout wrapper
│
├── 📁 api/                   # REST API endpoints
│   ├── blogs.php            # Blog CRUD operations
│   ├── cart.php             # Cart operations
│   ├── comments.php         # Comment management
│   ├── contact.php          # Contact form handler
│   └── subscribe.php        # Newsletter subscription
│
├── 📁 css/                   # All stylesheets
│   ├── variables.css        # Design tokens (8 themes)
│   ├── base.css            # Reset, typography, animations
│   ├── components.css       # Reusable components
│   ├── header-footer.css    # Navigation & footer
│   ├── landing-luxury.css   # Homepage styles
│   ├── pages.css           # General page styles
│   ├── shop-luxury.css     # E-commerce styles
│   ├── innovation-lab.css   # Lab section styles
│   ├── chocopedia.css      # Encyclopedia styles
│   └── landing-sections-upgrade.css # Section enhancements
│
├── 📁 js/                    # All JavaScript
│   ├── core.js             # Global utilities
│   ├── components.js        # Reusable UI components
│   ├── ai-assistant.js      # AI chatbot
│   ├── landing-luxury.js    # Homepage interactions
│   ├── bean-to-bar.js       # Timeline component
│   ├── innovation-lab-enhancements.js # Lab features
│   └── landing-sections-upgrade.js # Section animations
│
├── 📁 assets/               # Media files
│   ├── images/             # General images
│   ├── blogs/              # Blog article images
│   │   └── thumbnails/     # Auto-generated thumbnails
│   ├── products/           # Product images
│   └── cocoa-pod/          # 3D model assets
│
├── 📁 data/cache/           # File-based cache
│   ├── site_settings.json   # Cached settings
│   ├── ticker_insights.json # Cached insights
│   └── ingredient_spotlight_*.json # Spotlight cache
│
├── 📁 scripts/              # Setup & migrations
│   ├── setup_innovation_lab.php # Lab table creation
│   ├── setup_categories.php     # Category setup
│   └── add_performance_indexes.php # DB optimization
│
├── 📁 dev/                  # Developer tools
│   ├── index.php           # Dev dashboard
│   ├── database.php        # DB management
│   ├── design.php          # Design system
│   └── system.php          # System info
│
└── 📁 backups/              # Database backups
    └── backup_all_content.php # Backup script
```

---

## 🔄 Core Data Flows

### **1. Page Rendering Flow**

```php
// Every page follows this pattern:

1. Entry Point (e.g., index.php)
   ↓
2. Load Configuration
   require_once 'includes/db.php';
   require_once 'includes/env_loader.php';
   ↓
3. Set Page Metadata
   $pageTitle = "...";
   $pageDescription = "...";
   ↓
4. Fetch Dynamic Data
   $pdo = get_db();
   $data = $pdo->query("SELECT ...");
   ↓
5. Include Header
   include 'includes/header.php';
   ↓
6. Render Page Content
   <section>...</section>
   ↓
7. Include Footer
   include 'includes/footer.php';
```

### **2. API Request Flow**

```
Frontend JavaScript
   ↓
fetch('/api/endpoint.php', {
   method: 'POST',
   body: JSON.stringify(data)
})
   ↓
api/endpoint.php
   ↓
includes/db.php (get_db())
   ↓
MySQL Database
   ↓
JSON Response
   ↓
Frontend Update
```

**Example API Call:**
```javascript
// Frontend
const response = await fetch('/api/blogs.php', {
    method: 'GET'
});
const blogs = await response.json();

// Backend (api/blogs.php)
<?php
require_once '../includes/db.php';
$pdo = get_db();
$stmt = $pdo->query("SELECT * FROM blogs");
$blogs = $stmt->fetchAll();
header('Content-Type: application/json');
echo json_encode($blogs);
?>
```

### **3. Authentication Flow**

```
User → admin/login.php
   ↓
Submit credentials
   ↓
Verify against users table
   ↓
Create session ($_SESSION['admin_logged_in'])
   ↓
Redirect to admin/dashboard.php
   ↓
All admin pages check session
   ↓
Logout clears session
```

### **4. Blog System Flow**

```
Admin Creates Blog
   ↓
admin/blog-editor.php
   ↓
Save to blogs table + Upload images
   ↓
Cache cleared (includes/blog-cache.php)
   ↓
Public views blog
   ↓
blog.php (listing) or blog-article.php (single)
   ↓
Check cache first (get_cached_blog_article)
   ↓
If cache miss, query database
   ↓
Cache result for 1 hour
```

### **5. Innovation Lab Data Flow**

```
Admin Manages Lab Content
   ↓
admin/innovation-lab.php
   ↓
CRUD operations on:
  - lab_experiments
  - lab_failed_batches
  - lab_what_if_questions
  - lab_ingredient_categories
  - lab_future_pillars
   ↓
Public views Lab
   ↓
innovation-lab.php
   ↓
Query all tables
   ↓
JavaScript enhances interactions
   (js/innovation-lab-enhancements.js)
   ↓
Modals show detailed dossiers
```

---

## 🗄️ Database Architecture

### **Connection Pattern**
```php
// Singleton pattern in includes/db.php
function get_db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO($dsn, $user, $pass, $options);
    }
    return $pdo;
}

// Usage everywhere:
$pdo = get_db();
$stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
```

### **Caching Strategy**

**File-based caching:**
```php
// Pattern used throughout
function get_cached_data($key, $ttl = 3600) {
    $cacheFile = __DIR__ . "/../data/cache/{$key}.json";
    
    // Check if cache exists and is fresh
    if (file_exists($cacheFile) && 
        (time() - filemtime($cacheFile) < $ttl)) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Cache miss: query database
    $data = query_database();
    
    // Save to cache
    file_put_contents($cacheFile, json_encode($data));
    
    return $data;
}
```

**Cached items:**
- `site_settings.json` (30 min TTL)
- `ticker_insights.json` (30 min TTL)
- `ingredient_spotlight_*.json` (6 hour TTL)
- Blog articles (1 hour TTL)

---

## 🎨 Frontend Architecture

### **CSS Architecture (BEM-inspired)**

```css
/* Block */
.workshop-card { }

/* Element */
.workshop-card__title { }
.workshop-card__image { }

/* Modifier */
.workshop-card--featured { }
```

**CSS Loading Order:**
```html
1. variables.css    (design tokens)
2. base.css        (reset, typography)
3. components.css  (reusable components)
4. header-footer.css (navigation)
5. page-specific.css (landing-luxury.css, etc.)
```

### **JavaScript Architecture**

**Module Pattern:**
```javascript
(function() {
  'use strict';
  
  const MODULE_NAME = {
    init() {
      this.bindEvents();
      this.setupObservers();
    },
    
    bindEvents() {
      // Event listeners
    },
    
    setupObservers() {
      // Intersection observers
    }
  };
  
  // Auto-initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => MODULE_NAME.init());
  } else {
    MODULE_NAME.init();
  }
})();
```

**JavaScript Loading:**
```html
<!-- Core first -->
<script src="js/core.js"></script>

<!-- Components -->
<script src="js/components.js"></script>

<!-- Page-specific last -->
<script src="js/landing-luxury.js"></script>
```

---

## 🔐 Security Architecture

### **Input Sanitization**
```php
// Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_POST['email']]);

// Escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### **Authentication**
```php
// Session-based
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
```

### **CSRF Protection**
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

---

## 🚀 Performance Optimizations

### **Database**
- Connection pooling (singleton pattern)
- Prepared statement caching
- Index optimization
- Query result caching

### **Frontend**
- CSS/JS minification (manual)
- Image lazy loading
- Intersection Observer for animations
- GPU-accelerated transforms
- Debounced scroll handlers

### **Caching**
- File-based cache (30min - 6hr TTL)
- Browser caching via headers
- Static asset versioning (`?v=timestamp`)

---

## 🔧 Extension Points

### **Add New Feature Module**

1. **Create database tables** (scripts/setup_*.php)
2. **Add API endpoints** (api/*.php)
3. **Create admin interface** (admin/*.php)
4. **Build public pages** (*.php)
5. **Add styles** (css/*.css)
6. **Add JavaScript** (js/*.js)

### **Add New API Endpoint**

```php
<?php
// api/new-endpoint.php
require_once '../includes/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $pdo = get_db();
    
    // Handle request
    $result = ['success' => true, 'data' => []];
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

---

## 📊 Performance Metrics

### **Typical Page Load**
- **Time to First Byte:** ~200ms
- **DOM Content Loaded:** ~500ms
- **Full Page Load:** ~1.5s
- **Database Queries:** 3-8 per page
- **Cache Hit Rate:** ~85%

### **Database Performance**
- **Connection Time:** ~10ms
- **Avg Query Time:** ~5-15ms
- **Total DB Size:** ~50MB
- **Largest Table:** blogs (~1000 rows)

---

## 🔄 Data Relationships

```
users (admins)
  ↓
blogs ←→ blog_categories
  ↓
comments

products
  ↓
orders
  ↓
order_items

lab_experiments
lab_failed_batches
lab_what_if_questions
lab_ingredient_categories
lab_future_pillars
(all independent)

site_settings
  ↓ (cached)
All pages read settings
```

---

## 📝 Code Conventions

### **PHP**
- PSR-12 style (mostly)
- Snake_case for functions
- camelCase for variables
- Database functions in `includes/db.php`

### **JavaScript**
- ES6+ features
- camelCase naming
- IIFE module pattern
- Event delegation

### **CSS**
- BEM-inspired naming
- CSS variables for theming
- Mobile-first approach
- Semantic class names

---

## 🎯 Next Steps for AI Understanding

After reading this document, AI should understand:
1. ✅ How pages are structured and rendered
2. ✅ How data flows through the system
3. ✅ Where to find specific functionality
4. ✅ How to add new features
5. ✅ Security and performance patterns

**Related Docs:**
- `PROJECT_OVERVIEW.md` - Quick reference
- `DATABASE_SCHEMA.md` - Complete schema
- `API_REFERENCE.md` - API documentation
- `REDUNDANCY_REPORT.md` - Code cleanup guide
