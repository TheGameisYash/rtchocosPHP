# RT Chocos — Author Blog Publishing Guide

This guide explains how to write, format, and publish new articles on the RT Chocos blog platform systematically using the custom Admin Block Editor.

---

## 🖥️ Editor Interface Overview

The RT Chocos Admin Panel features a distraction-free split layout designed for systematic content creation:

1. **Top Actions Bar**:
   * **Back to List**: Safely return to the dashboard.
   * **Autosave Indicator**: Shows green when your drafts are safely saved to browser storage.
   * **Undo/Redo (Ctrl+Z / Ctrl+Y)**: Roll back text or block additions instantly.
   * **Immersive Preview**: Opens a 1:1 view matching exactly how the article will render on the public website.
   * **Drawer Toggle (Gear Icon)**: Collapses or expands the right-side Metadata Drawer to maximize writing space.

2. **Main Block Canvas (Left)**:
   * The writing area is a modular block editor. You can insert paragraph, heading, list, or image blocks directly.
   * **Inline Image Uploader**: Drag and drop images directly into image blocks inside the canvas. The system automatically performs an AJAX upload, saves the file to `/assets/blogs/inline-[timestamp].png` (max size 5MB), and registers it in the media library.

3. **Metadata Side-Drawer (Right)**:
   * Contains SEO fields, classification categories, scheduler, tags, and cover images.

---

## 🖼️ Graphic Specifications & Image Management

To maintain a professional grid design and ensure fast page loads, follow these exact image specifications:

| Image Type | Recommended Resolution | Aspect Ratio | Maximum File Size | Recommended Format | Purpose |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Header / Hero Image** | `1200 x 675 pixels` | **16:9** | `250 KB` | WebP or JPEG | Appears at the top of the detail article page |
| **Card Thumbnail** | `600 x 338 pixels` | **16:9** | `100 KB` | WebP or JPEG | Appears on the main grid listing page |
| **Inline Content Images** | `800 x auto pixels` | *Variable* | `150 KB` | WebP or JPEG | Inline illustrations within the article |

### Key Image Considerations:
* **Strict Aspect Ratio**: Crop your header and thumbnail images to exactly **16:9** before uploading. Since the grid listing page uses `object-fit: fill` to ensure perfect size consistency, uploading images of other aspect ratios will result in visual distortion.
* **Banner Typography Safety**: If you put text on the header banner (e.g. title text), keep it centered with at least a 10% safety margin on the sides so it remains fully visible on mobile viewports.
* **Compression**: Run all graphics through tools like TinyPNG or squoosh.app to compress them. Heavy images hurt SEO ranking and load times.

---

## 🔍 SEO & Metadata Optimization

The Settings Drawer contains crucial fields that determine search rankings. Fill them out as follows:

1. **Title**: Under **60 characters**. Include primary target keywords near the start (e.g., *How to Temper Chocolate: The Temperature Curve Guide*).
2. **URL Slug**: The clean web handle (e.g., `tempering-chocolate-guide`). Use lowercase letters, numbers, and hyphens (`-`). No spaces or special characters. The system automatically formats this on save.
3. **Excerpt**: A **120-160 character** description of the article. This appears as the card description on the listing page and is used as the HTML `meta description` for search results.
4. **Google Search Listing Preview**: View this live mockup card at the bottom of the drawer to see exactly how your article will look on a Google Search results page.
5. **YouTube URL**: Paste the video link (e.g., `https://youtube.com/watch?v=...`) to highlight that the blog contains video content. This automatically flags a `🎥 Video` badge on the grid cards.
6. **Body CSS Class**: Optional. Add a custom class (e.g., `cocoa-article`) to apply specific layout rules or custom styles defined in `style.css`.

---

## 🏷️ Categorization & Tags

* **Category Selection**: Choose one of the core categories (`Science`, `Beginner Guide`, `Recipe`, `Artisan`, `Business Tips`, `Industry Insights`) from the dropdown. This determines which page sections and filters display your post.
* **Tag Manager**:
  * Check the checkboxes for existing tags (e.g. `Lecithin`, `Cocoa Powder`, `Sugar Bloom`).
  * Add new tags in the **Add new tags** input field as a comma-separated list (e.g. `ganache, tempering, dark-chocolate`). The system automatically inserts them into the database and maps them.

---

## ✍️ Content Formatting (Markdown Cheatsheet)

The custom Markdown parser dynamically compiles your content and builds a Table of Contents (TOC) sidebar automatically.

### 1. Structure Headings
Always organize content using clean hierarchies. Do not use `#` (H1) as this is reserved for the article title.
```markdown
## Section Title (H2 - generates a main category in the TOC sidebar)
### Subsection (H3 - generates a nested category in the TOC sidebar)
```

### 2. Layout Modules

#### YouTube Video Embeds
To insert a responsive, centered video player with rounded corners, use the shortcode tag anywhere in the body:
```text
{{youtube:VIDEO_ID}}
```
*Example: `{{youtube:dQw4w9WgXcQ}}`*

#### Tables
Use pipe notation to render elegant tables (which automatically feature striped rows and gold accents):
```markdown
| Parameter | Dark Chocolate | Milk Chocolate | White Chocolate |
| :--- | :--- | :--- | :--- |
| **Cocoa %** | 70% - 85% | 35% - 45% | 0% (Butter only) |
| **Tempering Temp** | 31°C - 32°C | 29°C - 30°C | 28°C - 29°C |
```

#### Image Alignment Modifiers
Place inline images dynamically using formatting tags:
```markdown
![Left Floating Wrap](path/to/image.jpg){align=left}
![Centered Main Graphic](path/to/image.jpg){align=center}
![Right Floating Wrap](path/to/image.jpg){align=right}
```

#### Monospace Code Blocks
Ideal for technical data, ingredient ratios, or temperature steps:
```text
```text
Phase 1: Melt completely at 45°C - 50°C
Phase 2: Cool to seed crystal formation at 27°C
Phase 3: Reheat to working temperature at 31°C - 32°C
```
```

---

## 🚀 Systematic Publishing Steps

1. **Access Editor**: Log in to `/admin/` and click **New Article** (or edit an existing post).
2. **Setup Settings**: Click the gear icon to open the Settings Drawer. Enter Title, Excerpt, and category.
3. **Upload Media**: Drop your compressed **16:9 header image** and **16:9 thumbnail image** into the settings dropzones.
4. **Draft Content**: Write your article in the Block Canvas. Structure sections using H2 (`##`) headings.
5. **Map Tags**: Select existing tags or create new ones using the comma-separated tag input.
6. **Schedule / Publish**:
   * **Immediate**: Leave "Schedule Publish Date" blank, toggle **Publish Post** to ON, and save.
   * **Scheduled**: Select a future date/time in the **Schedule Publish Date** selector, toggle **Publish Post** to ON, and save. The post will remain hidden until that timestamp is reached.
7. **Verify**: Open **Immersive Preview** to review the layout, and verify that the dynamic XML sitemap (at `/sitemap.xml`) has indexed the new clean URL.
