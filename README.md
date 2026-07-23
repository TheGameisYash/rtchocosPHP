# RT Chocos — India's First Chocolate Blog & Academy

Welcome to **RT Chocos**, founded by certified chocolate educator and food consultant **Aarti Saluja Sahni**. This platform serves as India's premier bean-to-bar chocolate education portal, cocoa science journal, and artisan product showcase.

---

## 🚀 Quick Start (Local Development)

### Prerequisites
- PHP 8.0+
- MySQL / MariaDB (or Hostinger Remote DB configured in `.env`)

### Running the Application Locally
To start the local development server with clean URL routing enabled, simply run:

```bash
# Option 1: Double-click or run the batch script (Windows)
start.bat

# Option 2: Run directly via PHP command
php -S localhost:8000 router.php
```

Then open your browser and navigate to:
👉 **[http://localhost:8000/](http://localhost:8000/)**

> ⚠️ **Important**: Always pass `router.php` to `php -S`. Running without `router.php` will cause 404 errors on clean URLs like `/blog` or `/about` because `blog` is a physical directory in the repository.

---

## 🛠️ Project Stack & Architecture

- **Backend**: Native PHP (Modular procedural architecture, PDO prepared statements)
- **Database**: MySQL (`u219698334_RTchocos` on Hostinger / Local MySQL)
- **Frontend**: Vanilla HTML5, Modern CSS variables (`style.css`), Vanilla JavaScript (`script.js`)
- **Web Server Routing**:
  - Apache / Hostinger: `.htaccess` (mod_rewrite)
  - Local PHP Dev Server: `router.php`
- **CI/CD Deployment**: GitHub Actions (`.github/workflows/deploy-beta.yml` -> SFTP sync to Hostinger)

---

## 📁 Key Directory Structure

```
RTchocosReal/
├── .agents/              # AI Agent workspace instructions & guidelines
│   └── AGENTS.md
├── admin/                # Admin Panel (CMS for blogs, comments, products, media)
├── api/                  # Backend JSON API Handlers (blogs, cart, comments, contact)
│   ├── blogs.php
│   ├── cart.php
│   ├── comments.php
│   ├── contact.php
│   └── subscribe.php
├── tools/                # Maintenance & Setup Utilities
│   ├── db_setup.php
│   ├── create_symlink.php
│   └── test_blog.php
├── docs/                 # Documentation Center
│   ├── ARCHITECTURE.md
│   ├── README_SHOP_FAQ.md
│   └── blog_upload_guide.md
├── assets/               # Static images, icons, videos, and recipe guides
├── blog/                 # Legacy/routing directory & static markdown files
├── data/                 # Data storage & JSON cache files
├── includes/             # Shared PHP components & configuration
│   ├── db.php            # Database connection handler (PDO)
│   ├── blog-data.php     # Static blog data array (resilience fallback)
│   ├── env_loader.php    # Native .env file loader
│   ├── header.php        # Site header & navigation bar
│   └── footer.php        # Site footer & analytics scripts
├── blog.php              # Main blog list page
├── blog-article.php      # Full blog article renderer
├── index.php             # Homepage
├── router.php            # Dev server URL router
├── start.bat             # One-click dev server launcher script
└── style.css             # Main stylesheet
```

---

## 📚 Documentation & Reference Files

- [docs/ARCHITECTURE.md](file:///d:/Coding%20Projects/AI%20LLM%20websites/RTchocosReal/docs/ARCHITECTURE.md) — Full technical architecture, routing specs, database schemas, and AI reading guide.
- [docs/README_SHOP_FAQ.md](file:///d:/Coding%20Projects/AI%20LLM%20websites/RTchocosReal/docs/README_SHOP_FAQ.md) — Guide for managing products & FAQs via SQL.
- [docs/blog_upload_guide.md](file:///d:/Coding%20Projects/AI%20LLM%20websites/RTchocosReal/docs/blog_upload_guide.md) — Author blog upload & markdown formatting guide.
- [llms.txt](file:///d:/Coding%20Projects/AI%20LLM%20websites/RTchocosReal/llms.txt) — LLM/AI high-level web overview.
- [.agents/AGENTS.md](file:///d:/Coding%20Projects/AI%20LLM%20websites/RTchocosReal/.agents/AGENTS.md) — Coding rules and conventions for AI assistants.

---

## 🌿 Git Branch Strategy

- **`main`**: Production codebase (Live on `https://www.rtchocos.com/`).
- **`beta`**: Staging/testing branch (Auto-deployed to `https://www.rtchocos.com/beta` via GitHub Actions).
