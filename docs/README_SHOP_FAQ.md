# RT Chocos — Shop & FAQ Database Management Guide

This document explains how to add, edit, or manage products and FAQs directly in the database without making any modifications to the code.

---

## 1. Managing the FAQ System

The FAQ system is powered entirely by the `faqs` database table. Adding a row here instantly displays the FAQ on the site.

### SQL Table Schema: `faqs`
- `id` (INT, Primary Key, Auto-increment)
- `question` (VARCHAR) — The FAQ question.
- `answer` (TEXT) — The FAQ answer. HTML tags (like `<a>`, `<strong>`, `<em>`) are fully supported.
- `category` (ENUM) — Must be one of:
  - `'general'` (appears on General FAQ section)
  - `'workshops'` (appears on Workshops section / Workshops page)
  - `'shop'` (appears on Shop page / Product details pages)
  - `'shipping'` (appears on Shipping section / FAQ page)
  - `'courses'` (appears on Courses section / FAQ page)
- `display_order` (INT) — Order of appearance (lower numbers appear first).
- `is_active` (TINYINT) — Set to `1` to show it on the site, `0` to hide it.

### How to Add a New FAQ:
Run this SQL query to add a new FAQ:
```sql
INSERT INTO faqs (question, answer, category, display_order, is_active) 
VALUES (
    'Do you offer vegan options?', 
    'Yes! All of our dark chocolate bars are 100% vegan, dairy-free, and gluten-free. Check out our <a href="shop/signature-dark-chocolate-72">Signature Dark Chocolate Bar</a>.', 
    'shop', 
    4, 
    1
);
```

---

## 2. Managing Products & Shop

The product listing, detail pages, and checkout validation are driven by the `products` database table.

### SQL Table Schema: `products`
- `id` (INT, Primary Key, Auto-increment)
- `slug` (VARCHAR, Unique) — SEO-friendly slug. Must match the format `lowercase-with-hyphens` (e.g. `signature-dark-chocolate-72`). Do not use spaces or special characters.
- `name` (VARCHAR) — Product title.
- `short_description` (TEXT) — Summary shown on product card and detail page (under price).
- `long_description` (LONGTEXT) — Detailed product description, ingredients, taste notes.
- `price` (DECIMAL) — Regular product price (INR).
- `sale_price` (DECIMAL, Nullable) — Sale price. If set, the site will show a discount badge and cross out the regular price.
- `category` (VARCHAR) — Product category (e.g., `'Chocolates'`, `'Cacao'`, `'Kits'`).
- `stock_quantity` (INT) — Inventory limit. Set to `-1` for unlimited stock, or a positive integer (e.g. `50`). If stock is `0`, it shows as "Out of stock" and disables the add-to-cart button.
- `image_main` (VARCHAR) — Path to product image (e.g. `assets/signature-dark-chocolate.png`).
- `image_gallery` (JSON, Nullable) — Array of additional images (e.g. `["assets/product-back.png", "assets/product-packaging.png"]`). If empty, use `[]`.
- `meta_title` (VARCHAR, Nullable) — Custom SEO browser title. If blank, auto-generates: `"{Product Name} | Buy Bean-to-Bar Chocolate Online | RT Chocos India"`.
- `meta_description` (TEXT, Nullable) — Custom SEO meta description. If blank, auto-generates a 155-character description.
- `meta_keywords` (VARCHAR, Nullable) — Custom keywords. If blank, auto-generates category keywords.
- `is_featured` (TINYINT) — Set to `1` to highlight the product at the top of the shop.
- `is_active` (TINYINT) — Set to `1` to list in the shop, `0` to hide/archive it.

### How to Add a New Product:
Run this SQL query to add a new product:
```sql
INSERT INTO products (slug, name, short_description, long_description, price, sale_price, category, stock_quantity, image_main, image_gallery, is_featured, is_active) 
VALUES (
    'craft-cacao-nibs-250g',
    'Premium Kerala Cacao Nibs (250g)',
    '100% natural, roasted cacao nibs sourced directly from organic Kerala estates.',
    'Carefully roasted and cracked single-origin cacao nibs. Rich in minerals and antioxidants. Perfect as a baking ingredient, cereal topping, or healthy snack.',
    250.00,
    null,
    'Cacao',
    80,
    'assets/cacao-nibs.png',
    '[]',
    0,
    1
);
```

---

## 3. How Sitemap Updates Work
The sitemap (`sitemap.php`) queries both `blogs` and `products` tables directly. Any new product or blog article inserted into the database will instantly appear in `https://www.rtchocos.com/sitemap.xml` automatically without needing to modify or rebuild the sitemap file.
