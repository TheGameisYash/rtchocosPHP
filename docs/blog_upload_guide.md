# RT Chocos — Author & Editor Blog Publishing Guide

This guide explains how to upload blog images, format Markdown content, and publish blog articles via the Admin CMS.

---

## 1. Accessing the Admin CMS
1. Navigate to `/admin/login.php`.
2. Log in using your admin credentials.

---

## 2. Creating / Editing Blog Articles
1. In the admin navigation, click **Blogs** -> **New Article**.
2. Fill out the core fields:
   - **Title**: Article headline.
   - **Slug**: URL-friendly identifier (e.g. `cocoa-ph`).
   - **Category**: Select category (`Science`, `Beginner Guide`, `Business Tips`, `Industry Insights`).
   - **Read Time**: e.g. `6 min`.
   - **Excerpt**: 2-3 sentence summary displayed on card grids and search previews.
   - **Main / Header Image**: Upload high-res header banner.
   - **Thumbnail Image**: Upload card thumbnail (optional, falls back to header image).
   - **Content Body**: Write or paste your article in Markdown.

---

## 3. Image Dimensions & Formatting
- **Header Images**: Recommended ratio `16:9` (min width `1200px`).
- **Thumbnails**: Recommended ratio `1:1` or `4:3` (min width `600px`).
- **Uploaded File Path**: Saved to `/uploads_blogs/` outside `public_html` on Hostinger.

---

## 4. Markdown Formatting Support
The article renderer supports standard Markdown formatting:
- `# Heading 1`, `## Heading 2`, `### Heading 3`
- `**Bold text**`, `*Italic text*`
- Lists (`- item`, `1. item`)
- Blockquotes (`> quote text`)
- Tables (`| Header | Header |`)
