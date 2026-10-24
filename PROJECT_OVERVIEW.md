# RT Chocos - Project Overview

> **Last Updated:** 2026-10-23  
> **Purpose:** Quick reference for AI to understand the entire RT Chocos codebase

---

## 📋 Project Summary

**RT Chocos** is a premium chocolate education platform combining:
- **Chocolate Blog** - Educational content about bean-to-bar chocolate making
- **Academy** - Professional workshops and masterclasses
- **Shop** - Premium chocolate products and kits
- **Innovation Lab** - Experimental chocolate research and development
- **Interactive Tools** - Flavor wheel, chocolate table, AI assistant

**Tech Stack:**
- **Backend:** PHP 8.x, MySQL
- **Frontend:** Vanilla JavaScript, Custom CSS (no frameworks)
- **Architecture:** Traditional MVC-style with API endpoints
- **Hosting:** Apache/Nginx with PHP-FPM

---

## 🏗️ Core Architecture

### **Entry Points**
```
index.php          → Landing page (home)
blog.php           → Blog listing
innovation-lab.php → Innovation Lab section
workshops.php      → Workshops listing
shop.php          → E-commerce shop
chocopedia.php    → Chocolate encyclopedia
about.php         → About page
contact.php       → Contact form
```

### **Key Directories**

```
📁 Root
├── 📁 admin/              # Admin dashboard & CMS
├── 📁 api/                # REST API endpoints
├── 📁 includes/           # Shared PHP components
├── 📁 css/                # All stylesheets
├── 📁 js/                 # All JavaScript
├── 📁 assets/             # Images, videos, media
├── 📁 data/cache/         # Cache files
├── 📁 scripts/            # Database setup/migrations
├── 📁 dev/                # Developer tools
└── 📁 backups/            # Database backups
```

---

## 🗄️ Database Schema

### **Core Tables**
```sql
-- Content Management
blogs              # Blog articles
blog_categories    # Blog categories
comments           # Article comments
ai_insights        # AI-generated insights for ticker

-- E-commerce
products           # Shop products
orders             # Customer orders
order_items        # Order line items
cart_sessions      # Shopping cart data

-- Innovation Lab
lab_experiments              # Active experiments
lab_failed_batches           # Failed experiment case studies
lab_what_if_questions        # Exploratory questions
lab_ingredient_categories    # Ingredient innovation
lab_future_pillars           # Future of chocolate pillars

-- System
users              # Admin users
contacts           # Contact form submissions
subscribers        # Email subscribers
site_settings      # Dynamic site configuration
ingredient_spotlights  # AI-generated ingredient features
```

---

## 🎨 Frontend Architecture

### **CSS Organization**
```
variables.css          # Design tokens, color schemes
base.css              # Reset, typography, animations
components.css        # Reusable UI components
header-footer.css     # Navigation & footer
landing-luxury.css    # Homepage specific styles
innovation-lab.css    # Lab section styles
pages.css            # Other page styles
chocopedia.css       # Encyclopedia styles
shop-luxury.css      # E-commerce styles
```

### **JavaScript Modules**
```
core.js                      # Global utilities, navigation
ai-assistant.js              # AI chatbot drawer
landing-luxury.js            # Homepage interactions
bean-to-bar.js               # Bean-to-bar timeline
innovation-lab-enhancements.js  # Lab interactions
components.js                # Reusable UI components
```

---

## 🔄 Data Flow

### **Page Load Flow**
```
1. User requests page (e.g., index.php)
2. router.php intercepts and routes
3. includes/header.php loads
4. Page-specific content renders
5. includes/footer.php closes
6. JavaScript initializes
```

### **API Request Flow**
```
Frontend → api/*.php → includes/db.php → MySQL → JSON Response
```

### **Admin Flow**
```
admin/login.php → admin/dashboard.php → admin/*.php (CRUD operations)
```

---

## 🔑 Key Files Explained

### **Configuration**
- `includes/db.php` - Database connection & helper functions
- `includes/env_loader.php` - Environment variables
- `css/variables.css` - Design system tokens

### **Routing**
- `router.php` - URL routing logic
- `.htaccess` - Apache rewrite rules

### **Components**
- `includes/header.php` - Global header & navigation
- `includes/footer.php` - Global footer
- `includes/components/preloader.php` - Page loader
- `includes/components/ai_drawer.php` - AI assistant

### **API Endpoints**
- `api/blogs.php` - Blog CRUD
- `api/cart.php` - Cart operations
- `api/comments.php` - Comment management
- `api_ai.php` - AI chatbot backend
- `api_generate_spotlight.php` - Ingredient spotlight generation

---

## 🎯 Feature Modules

### **1. Innovation Lab**
```
innovation-lab.php           # Main page
admin/innovation-lab.php     # Admin management
css/innovation-lab.css       # Styles
js/innovation-lab-enhancements.js  # Interactions
```

**Database Tables:**
- lab_experiments
- lab_failed_batches
- lab_what_if_questions
- lab_ingredient_categories
- lab_future_pillars

### **2. Blog System**
```
blog.php                # Listing
blog-article.php        # Single article
admin/blog-editor.php   # Content editor
includes/blog-data.php  # Data layer
```

### **3. E-commerce**
```
shop.php               # Product listing
product.php            # Single product
cart.php              # Shopping cart
checkout.php          # Checkout flow
admin/products.php    # Product management
```

### **4. Workshops**
```
workshops.php              # Listing
includes/workshops_data.php  # Workshop data
```

---

## 🔐 Authentication & Security

### **Admin Access**
- Login: `admin/login.php`
- Session-based authentication
- CSRF protection on forms
- Password hashing (PHP password_hash)

### **API Security**
- CORS headers configured
- Input sanitization
- SQL injection prevention (prepared statements)

---

## 🎨 Design System

### **Color Schemes** (8 themes available)
1. **Forest Green & Warm Ivory** (Default)
2. Deep Teal & Seafoam
3. Velvet Plum & Champagne
4. Roasted Cacao & Cinnamon
5. Sage & Eucalyptus
6. Midnight & Liquid Gold (Dark)
7. Vintage Ruby & Bordeaux
8. Royal Navy & Silver

**Switching:** CSS variables in `:root` with theme classes

### **Typography**
- Headings: Playfair Display (serif)
- Body: Inter (sans-serif)
- Accent: Cormorant Garamond (italic serif)

---

## 🚀 Deployment

### **Requirements**
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite OR Nginx
- 512MB+ RAM
- SSL certificate recommended

### **Setup Commands**
```bash
# 1. Database setup
php db_setup.php

# 2. Run migrations
php scripts/setup_innovation_lab.php
php scripts/setup_categories.php

# 3. Set permissions
chmod 755 data/cache
chmod 755 assets/blogs
chmod 755 backups
```

---

## 📝 Common Tasks

### **Add New Page**
1. Create `new-page.php` in root
2. Add CSS in `css/pages.css` or create `css/new-page.css`
3. Add JS if needed in `js/new-page.js`
4. Update navigation in `includes/header.php`

### **Add API Endpoint**
1. Create `api/endpoint-name.php`
2. Use `includes/db.php` for database
3. Return JSON with proper headers

### **Add Innovation Lab Experiment**
1. Use admin panel: `admin/innovation-lab.php`
2. Or direct SQL to `lab_experiments` table

---

## 🐛 Debugging

### **Enable Debug Mode**
```php
// In includes/db.php or relevant file
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### **Check Logs**
- PHP errors: Check server error logs
- Database: Check `data/cache/` for cache issues
- JavaScript: Browser console

---

## 📚 External Dependencies

### **CDN Resources**
- Google Fonts (Playfair, Inter, Cormorant)
- Anime.js (animation library) - `js/anime.min.js`

### **APIs Used**
- OpenRouter AI (for chatbot & spotlight generation)
- Email service (configured in `includes/mailer.php`)

---

## 🎓 Learning the Codebase

### **Start Here (Priority Order):**
1. Read this file completely
2. `includes/db.php` - Database patterns
3. `index.php` - Homepage structure
4. `includes/header.php` - Global navigation
5. `css/variables.css` - Design system
6. `innovation-lab.php` - Complex feature example
7. `api/blogs.php` - API pattern example

### **Architecture Patterns:**
- **No Framework** - Pure PHP, vanilla JS
- **Component-based** - Reusable includes
- **API-first** - AJAX for dynamic features
- **Progressive Enhancement** - Works without JS

---

## 📄 Related Documentation

See also:
- `ARCHITECTURE.md` - Detailed architecture
- `API_REFERENCE.md` - API documentation
- `DATABASE_SCHEMA.md` - Complete schema
- `REDUNDANCY_REPORT.md` - Code cleanup recommendations
- `DEPLOYMENT_GUIDE.md` - Production setup

---

**Quick Questions?**
- Where is X feature? → Check this file's Feature Modules section
- How do I add Y? → Check Common Tasks section
- Code not working? → Check Debugging section
- Need database info? → Check Database Schema section
