# RT Chocos - Complete Database Schema

> **Last Updated:** 2026-10-23  
> **Purpose:** Complete MySQL database structure with all tables, columns, relationships, and indexes

---

## 📊 Database Overview

- **Database Type:** MySQL 5.7+ / MariaDB 10.3+
- **Character Set:** utf8mb4
- **Engine:** InnoDB
- **Total Tables:** 23
- **Connection Pattern:** Singleton PDO (see `includes/db.php`)

---

## 🗄️ Complete Table Reference

### **1. admins**
Admin user authentication

```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,              -- bcrypt hashed
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Default Credentials:**
- Username: `admin`
- Password: `Admin@rtchocos1`

---

### **2. developers**
Master control developer accounts

```sql
CREATE TABLE developers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,              -- bcrypt hashed
    email VARCHAR(150) NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'super_dev',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Default Credentials:**
- Username: `dev`
- Password: `Dev@rtchocosMaster1`

---

### **3. blogs**
Blog articles and content

```sql
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,           -- URL-friendly identifier
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Science',
    excerpt TEXT NULL,                           -- Short description
    content LONGTEXT NULL,                       -- Full article markdown/HTML
    image_path VARCHAR(255) NULL,                -- Header image
    thumbnail_path VARCHAR(255) NULL,            -- List/card thumbnail
    body_class VARCHAR(100) NULL,                -- CSS theme class
    youtube_url VARCHAR(255) NULL,               -- Optional video embed
    read_time VARCHAR(50) NULL,                  -- e.g., "8 min read"
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    views INT NOT NULL DEFAULT 0,                -- View counter
    scheduled_at TIMESTAMP NULL DEFAULT NULL,    -- Future publishing
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_blogs_pub_date (is_published, created_at),
    INDEX idx_blogs_cat (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Categories:** Science, Process, Origin Stories, Recipes, Sustainability

---

### **4. blog_tags**
Tag taxonomy for blogs

```sql
CREATE TABLE blog_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,           -- Display name
    slug VARCHAR(100) UNIQUE NOT NULL            -- URL-friendly
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **5. blog_tag_map**
Many-to-many relationship between blogs and tags

```sql
CREATE TABLE blog_tag_map (
    blog_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (blog_id, tag_id),
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **6. comments**
User comments on blog articles

```sql
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_slug VARCHAR(100) NOT NULL,             -- References blogs.slug
    name VARCHAR(100) NOT NULL,                  -- Commenter name
    comment TEXT NOT NULL,                       -- Comment text
    is_approved TINYINT(1) DEFAULT 1,            -- Moderation flag
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_blog_slug (blog_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **7. subscribers**
Email newsletter subscribers

```sql
CREATE TABLE subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **8. contacts**
Contact form submissions

```sql
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NULL,
    subject VARCHAR(150) NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,       -- Admin inbox flag
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_contacts_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **9. site_settings**
Dynamic site configuration

```sql
CREATE TABLE site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Key Settings:**
- `site_name`, `site_tagline`, `meta_description`
- `contact_email`, `contact_phone`
- `social_instagram`, `social_facebook`, `social_youtube`, `social_linkedin`
- `maintenance_mode`, `maintenance_title`, `maintenance_message`
- `show_theme_tester`, `site_url`, `beta_url`
- All Innovation Lab content settings (see Innovation Lab section)

**Caching:** 30-minute TTL in `data/cache/site_settings.json`

---

### **10. media**
Media library assets

```sql
CREATE TABLE media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,                  -- Relative to root
    mime_type VARCHAR(100) NOT NULL,
    size INT NOT NULL,                           -- Bytes
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **11. ai_insights**
Rotating cacao science facts (ticker)

```sql
CREATE TABLE ai_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insight_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Usage:** Displayed in rolling ticker on landing page  
**Cache:** `data/cache/ticker_insights.json` (30 min TTL)

---

### **12. ai_class_facts**
Workshop/masterclass educational facts

```sql
CREATE TABLE ai_class_facts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fact_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Usage:** Displayed on workshops page

---

### **13. ai_ingredient_spotlights**
AI-generated ingredient features (landing page)

```sql
CREATE TABLE ai_ingredient_spotlights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_name VARCHAR(150) NOT NULL,
    tag VARCHAR(100) NOT NULL DEFAULT '🌱 INGREDIENT SPOTLIGHT',
    short_desc VARCHAR(255) NOT NULL,            -- Card description
    detailed_notes TEXT NULL,                    -- Modal content
    flavor_notes VARCHAR(150) NULL,
    origin_region VARCHAR(100) NULL,
    modal_key VARCHAR(50) NOT NULL DEFAULT 'bean-to-bar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Cache:** `data/cache/ingredient_spotlight_*.json` (6 hour TTL)

---

## 🧪 Innovation Lab Tables

### **14. lab_experiments**
Active chocolate experiments

```sql
CREATE TABLE lab_experiments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'formulation-lab',
    status_badge VARCHAR(100) NOT NULL DEFAULT 'IN PROGRESS',
    status_color VARCHAR(50) NOT NULL DEFAULT 'green',
    image_path VARCHAR(255) NULL,
    description TEXT NOT NULL,                   -- Card excerpt
    tags VARCHAR(255) NULL,                      -- e.g., "FAT SYSTEMS | FORMULATION"
    hypothesis TEXT NULL,                        -- Dossier: The Hypothesis
    methodology TEXT NULL,                       -- Dossier: The Method
    findings TEXT NULL,                          -- Dossier: Early Findings
    next_steps TEXT NULL,                        -- Dossier: Next Steps
    full_content LONGTEXT NULL,                  -- Full experiment HTML
    is_featured TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_lab_cat (category),
    INDEX idx_lab_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Categories:**
- `formulation-lab` - Recipe formulation experiments
- `ingredient-innovation` - New ingredient testing
- `stability-shelf-life` - Shelf life studies
- `sensory-research` - Taste testing research

---

### **15. lab_failed_batches**
Failed experiment case studies

```sql
CREATE TABLE lab_failed_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    badge_label VARCHAR(100) NOT NULL DEFAULT 'BECAUSE FAILURE IS DATA.',
    image_path VARCHAR(255) NULL,
    hypothesis TEXT NOT NULL,                    -- 1. The Hypothesis
    what_happened TEXT NOT NULL,                 -- 2. What Happened?
    why_it_happened TEXT NOT NULL,               -- 3. Understanding The Science
    what_next TEXT NOT NULL,                     -- 4. The Learning
    full_story LONGTEXT NULL,                    -- Complete case study
    is_featured TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fail_active (is_active, is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **16. lab_what_if_questions**
Exploratory research questions

```sql
CREATE TABLE lab_what_if_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    num_label VARCHAR(20) NOT NULL,              -- e.g., "01", "02"
    question TEXT NOT NULL,
    subtext TEXT NULL,                           -- Optional subtitle
    link_url VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_whatif_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **17. lab_ingredient_categories**
Ingredient innovation categories with full dossiers

```sql
CREATE TABLE lab_ingredient_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,                 -- e.g., "INDIAN FRUITS"
    slug VARCHAR(150) UNIQUE NOT NULL,
    image_path VARCHAR(255) NULL,
    description TEXT NULL,                       -- Card description
    link_url VARCHAR(255) NULL,
    key_varietals TEXT NULL,                     -- Dossier pillar 1
    science_behavior TEXT NULL,                  -- Dossier pillar 2
    applications TEXT NULL,                      -- Dossier pillar 3
    lab_notes TEXT NULL,                         -- Dossier pillar 4
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ing_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Dossier Structure:** 4 aligned pillars (Key Varietals, Science & Behavior, Applications, Lab Notes)

---

### **18. lab_future_pillars**
Future of chocolate research pillars

```sql
CREATE TABLE lab_future_pillars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    link_url VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pillar_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🛒 E-commerce Tables

### **19. products**
Shop products

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) NULL,
    description TEXT NULL,
    ingredients TEXT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    stock_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_prod_cat (category),
    INDEX idx_prod_active (is_active, is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **20. orders**
Customer orders

```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(50) NULL,
    shipping_address TEXT NULL,
    billing_address TEXT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    order_status VARCHAR(50) NOT NULL DEFAULT 'processing',
    payment_method VARCHAR(50) NULL,
    transaction_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_order_status (order_status),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Payment Status:** pending, completed, failed, refunded  
**Order Status:** processing, shipped, delivered, cancelled

---

### **21. order_items**
Line items in orders

```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,                         -- NULL if product deleted
    product_name VARCHAR(255) NOT NULL,          -- Preserved for history
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    
    INDEX idx_order_items (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Note:** `product_id` can be NULL to preserve order history when products are deleted

---

### **22. cart_sessions**
Shopping cart state

```sql
CREATE TABLE cart_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    cart_data LONGTEXT NULL,                     -- JSON serialized cart
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_cart_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### **23. blog_categories**
Blog category metadata (if used)

```sql
CREATE TABLE blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 🔗 Relationships Diagram

```
users (admins) ─┐
                │
developers ─────┤
                │
                ├──> Authentication Layer
                
blogs ──┬──> blog_tags (via blog_tag_map)
        ├──> blog_categories
        └──> comments

products ──┬──> orders ──> order_items
           └──> cart_sessions

site_settings ──> Global Configuration

lab_experiments ─┐
lab_failed_batches ─┤
lab_what_if_questions ─┤
lab_ingredient_categories ─┤──> Innovation Lab Module
lab_future_pillars ─┘

ai_insights ──┬──> AI-Generated Content
ai_class_facts ──┤
ai_ingredient_spotlights ─┘

media ──> Shared Media Library

subscribers ──> Newsletter
contacts ──> Contact Form
```

---

## 🔍 Key Indexes

### **Performance Indexes**

```sql
-- Blog queries (most frequent)
ALTER TABLE blogs ADD INDEX idx_blogs_pub_date (is_published, created_at);
ALTER TABLE blogs ADD INDEX idx_blogs_cat (category);

-- Comment lookups
ALTER TABLE comments ADD INDEX idx_blog_slug (blog_slug);

-- Contact inbox
ALTER TABLE contacts ADD INDEX idx_contacts_is_read (is_read);

-- Lab experiments
ALTER TABLE lab_experiments ADD INDEX idx_lab_cat (category);
ALTER TABLE lab_experiments ADD INDEX idx_lab_active (is_active, sort_order);

-- Orders
ALTER TABLE orders ADD INDEX idx_order_status (order_status);
ALTER TABLE orders ADD INDEX idx_payment_status (payment_status);

-- Cart lookups
ALTER TABLE cart_sessions ADD INDEX idx_cart_session (session_id);
```

---

## 🗂️ Data Access Patterns

### **1. Get Published Blogs (with caching)**
```php
// Check cache first (includes/blog-cache.php)
$cacheKey = 'all_blogs';
$blogs = get_cached_data($cacheKey, 3600); // 1 hour TTL

if (!$blogs) {
    $stmt = $pdo->query("
        SELECT * FROM blogs 
        WHERE is_published = 1 
        ORDER BY created_at DESC
    ");
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    cache_data($cacheKey, $blogs);
}
```

### **2. Get Single Blog Article**
```php
$stmt = $pdo->prepare("
    SELECT * FROM blogs 
    WHERE slug = ? AND is_published = 1
");
$stmt->execute([$slug]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
```

### **3. Get Active Lab Experiments**
```php
$stmt = $pdo->query("
    SELECT * FROM lab_experiments 
    WHERE is_active = 1 
    ORDER BY sort_order ASC, created_at DESC
");
$experiments = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### **4. Get Site Settings (cached)**
```php
// From includes/db.php
$setting = get_site_setting('site_name', 'RT Chocos');
// Cache: data/cache/site_settings.json (30 min TTL)
```

### **5. Create Order with Items**
```php
$pdo->beginTransaction();
try {
    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (...) VALUES (...)");
    $stmt->execute([...]);
    $orderId = $pdo->lastInsertId();
    
    // Insert order items
    foreach ($items as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (...) VALUES (...)");
        $stmt->execute([$orderId, ...]);
    }
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## 🧹 Maintenance Queries

### **Clear Cache After Content Update**
```php
// In PHP
clear_site_settings_cache();
clear_blog_cache();

// Manually
rm data/cache/site_settings.json
rm data/cache/ticker_insights.json
```

### **Backup Database**
```bash
# Command line
mysqldump -u username -p rtchocos > backup.sql

# Using PHP script
php backups/backup_all_content.php
```

### **Reset to Fresh State**
```sql
-- WARNING: Deletes all data
TRUNCATE TABLE blogs;
TRUNCATE TABLE comments;
TRUNCATE TABLE subscribers;
TRUNCATE TABLE contacts;
-- etc.
```

---

## 📈 Growth Considerations

### **Current Scale**
- Blogs: ~50-100 articles expected
- Products: ~20-50 products
- Orders: Growing over time
- Lab Experiments: ~10-20 active

### **When to Optimize**
- **> 1000 blogs:** Add full-text search index
- **> 10k orders:** Partition orders table by year
- **> 100k page views/day:** Consider Redis for caching
- **High comment volume:** Add comment pagination

### **Scaling Recommendations**
1. Move from file-based cache to Redis/Memcached
2. Add read replicas for high traffic
3. Implement proper full-text search (MySQL FTS or Elasticsearch)
4. Archive old orders to separate table
5. Add CDN for media assets

---

## 🔐 Security Notes

### **Always Use Prepared Statements**
```php
// ✅ Good
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ Bad (SQL injection risk)
$result = $pdo->query("SELECT * FROM users WHERE email = '$email'");
```

### **Password Hashing**
```php
// Hashing
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verification
if (password_verify($inputPassword, $storedHash)) {
    // Valid
}
```

### **Foreign Key Constraints**
All relationships use `ON DELETE CASCADE` or `ON DELETE SET NULL` to maintain referential integrity.

---

## 📝 Migration History

### **Initial Setup**
- `db_setup.php` - Core tables (admins, blogs, subscribers, contacts, etc.)

### **Innovation Lab**
- `scripts/setup_innovation_lab.php` - All lab_* tables

### **E-commerce**
- Products, orders, order_items, cart_sessions

### **Future Migrations**
- Create in `scripts/migration_YYYYMMDD_description.php`
- Document changes here

---

## 🎯 Quick Reference

**Need to find:**
- Blog content? → `blogs` table
- Lab experiments? → `lab_experiments` table
- Site settings? → `site_settings` table (cached)
- Orders? → `orders` + `order_items` tables
- User comments? → `comments` table

**Common Tasks:**
- Add blog: Admin panel → Blogs → Create
- Add experiment: Admin panel → Innovation Lab → Experiments
- Update settings: Admin panel → Settings
- View orders: Admin panel → Orders

---

**Related Documentation:**
- `PROJECT_OVERVIEW.md` - Quick reference
- `ARCHITECTURE.md` - System architecture
- `API_REFERENCE.md` - API endpoints
- `REDUNDANCY_REPORT.md` - Code cleanup guide
