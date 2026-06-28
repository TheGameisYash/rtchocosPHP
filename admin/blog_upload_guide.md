# RT Chocos — Author Blog Publishing Guide

This guide explains how to write, format, and publish new articles on the RT Chocos blog platform systematically using the Admin Panel.

---

## 🖼️ Image Guidelines & Sizing

To maintain a professional grid design and ensure fast page loads, follow these exact image specifications:

| Image Type | Recommended Resolution | Aspect Ratio | Maximum File Size | Recommended Format |
| :--- | :--- | :--- | :--- | :--- |
| **Header / Hero Image** | `1200 x 675 pixels` | **16:9** | `250 KB` | WebP or JPEG |
| **Card Thumbnail** | `600 x 338 pixels` | **16:9** | `100 KB` | WebP or JPEG |

### Key Image Considerations:
* **Aspect Ratio Integrity**: Always crop your images to **16:9** before uploading. Since the listing grid uses `object-fit: fill` for strict visual alignment, uploading non-16:9 images will cause minor stretching.
* **Text in Banners**: If you put text on the header banner (e.g. title text), keep it centered with at least a 10% safety margin on the sides so it remains fully visible on mobile viewports.
* **Optimization**: Run images through tools like TinyPNG or squoosh.app to compress them. Heavy images hurt SEO and page load speeds.

---

## 🔍 SEO & Metadata Settings

Before writing, plan the metadata fields in the editor carefully:

1. **Title**: Under **60 characters**. Include primary keywords near the start (e.g. *How to Temper Chocolate: The Temperature Curve Guide*).
2. **Slug**: The URL handle (e.g. `tempering-chocolate-guide`). Use lowercase letters, numbers, and hyphens (`-`). No spaces or special characters.
3. **Excerpt**: A **120-160 character** description of the article. This appears as the preview snippet on the blog listing page and is used as the HTML `meta description` for search results.
4. **Category**: Match one of the core categories (e.g., `Science`, `Beginner Guide`, `Business Tips`) so the header filter buttons route users correctly.

---

## ✍️ Content Formatting (Markdown Cheatsheet)

The editor supports a custom Markdown parser that dynamically styles your text and generates a Table of Contents (TOC) sidebar automatically.

### 1. Document Headings
Always organize content using clean hierarchies. Do not use `#` (H1) as this is reserved for the article title.
```markdown
## Section Title (H2 - appears in TOC sidebar)
### Subsection (H3 - appears nested in TOC sidebar)
```

### 2. Special Layout Elements

#### YouTube Video Embeds
To insert a responsive, centered video player with rounded corners, use the YouTube shortcode tag anywhere in the body:
```text
{{youtube:VIDEO_ID}}
```
*Example: `{{youtube:dQw4w9WgXcQ}}`*

#### Dynamic Tables
Create custom structured data tables using standard pipe notation:
```markdown
| Parameter | Dark Chocolate | Milk Chocolate | White Chocolate |
| :--- | :--- | :--- | :--- |
| **Cocoa %** | 70% - 85% | 35% - 45% | 0% (Butter only) |
| **Tempering Temp** | 31°C - 32°C | 29°C - 30°C | 28°C - 29°C |
```

#### Image Alignment & Positioning
Align images dynamically within the text flow using formatting modifiers:
```markdown
![Left Floating Image](path/to/image.jpg){align=left}
![Centered Main Image](path/to/image.jpg){align=center}
![Right Floating Image](path/to/image.jpg){align=right}
```

#### Monospace Code Blocks
Ideal for temperature curves, formulas, or technical lists:
```text
```text
Phase 1: Melt completely at 45°C - 50°C
Phase 2: Cool to seed crystal formation at 27°C
Phase 3: Reheat to working temperature at 31°C - 32°C
```
```

---

## 🚀 Systematic Publishing Checklist (Step-by-Step)

Follow these steps when uploading an article through the Admin Panel:

1. **Log In**: Go to `/admin/` and authenticate using your credentials.
2. **Create New**: Click the **New Article** button on the dashboard.
3. **Fill Fields**: Enter the Title, Category, Read Time, and write the URL slug manually.
4. **Upload Graphics**: Select your compressed **16:9 header image** and **16:9 thumbnail image**.
5. **Write Excerpt**: Provide the meta description text.
6. **Compose Body**: Use the Markdown editor to draft the content. Organize your thoughts using H2 headings (`##`) to automatically generate the sidebar navigation menu.
7. **Preview**: Click the **Preview** button to confirm all headings, lists, tables, and images are aligned correctly.
8. **Publish**: Toggle status to **Published** and click **Save**. The dynamic sitemap will immediately index the new post for Google crawlers.
