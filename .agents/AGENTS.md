# RT Chocos — AI Agent Guidelines & Architecture Rules

This file defines critical repository knowledge, architectural constraints, and guidance for AI assistants working in this repository.

---

## 1. Local Development & Server Routing

- **Dev Server Command**: Always use `php -S localhost:8000 router.php` (or run `start.bat`).
  - *Reason*: `blog` is a physical folder. Running `php -S localhost:8000` without `router.php` will fail with a `404` when requesting clean URLs like `/blog` or `/about`.
- **Do Not Modify `.htaccess` for Dev Server Fixes**: `.htaccess` rules apply only to Apache in production (Hostinger). Router logic for local dev must go into `router.php`.

---

## 2. Blog Image Pipeline & Asset Paths

- **Directory Ignored**: `assets/blogs/` is listed in `.gitignore`. Uploaded blog images are stored on the live host (`/uploads_blogs/` or `assets/blogs/`).
- **Database Image Paths**: In the `blogs` database table, `image_path` and `thumbnail_path` store paths like `assets/blogs/filename.jpeg`.
- **Image Fallback Strategy**:
  - `blog_image.php` is invoked via rewrite rules when an image requested under `assets/blogs/` is missing on disk.
  - Static blog post fallbacks are defined in `includes/blog-data.php` (e.g. `assets/ph.png`, `assets/milkfat.png`).

---

## 3. Sub-directory & Navigation Convention (`$pathPrefix`)

- Pages rendered inside subdirectories (like `blog/article.php`) set `$pathPrefix = "../"`.
- Root-level pages set `$pathPrefix = ""`.
- `includes/header.php` and `includes/footer.php` rely on `$pathPrefix` for assets, CSS, and navigation links. Always use `$pathPrefix` when referencing assets in shared templates.

---

## 4. Git Branch Strategy & Deployment

- **`main`**: Production branch deployed to `rtchocos.com`.
- **`beta`**: Staging branch deployed to `rtchocos.com/beta` via GitHub Actions (`.github/workflows/deploy-beta.yml`).
- Never push untested code directly to `main`. Test on `beta` first.

---

## 5. Database & Offline Cache System

- **Database Connection**: Managed via `includes/db.php` using environment variables (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
- **Database Migrations**: Initialized/updated via `db_setup.php`.
- **Offline Resiliency**: `includes/blog-cache.php` automatically writes JSON backups to `data/cache/` whenever blogs are read or updated. If database queries fail, APIs and blog renderers fall back to reading JSON cache files seamlessly.

---

## 6. Admin Panel & Security

- Admin CMS lives in `admin/`. All protected pages require `require_once __DIR__ . '/auth.php'`.
- Passwords are hashed using `PASSWORD_BCRYPT`. Default credentials are env-configurable.

