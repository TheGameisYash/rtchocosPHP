<?php
// includes/blog-uploads.php
// Centralized path resolver for blog image uploads & assets
// Handles Hostinger live environment (main site, beta subdomain) and local dev.

require_once __DIR__ . '/media-storage.php';

/**
 * Returns the absolute filesystem directory where blog uploads are stored.
 * On live server (under public_html), resolves to persistent storage outside public_html (uploads_blogs).
 * On local dev, falls back to <project_root>/assets/blogs.
 */
function get_blog_uploads_dir() {
    return resolve_persistent_uploads_dir('uploads_blogs', 'assets/blogs');
}

/**
 * Returns the absolute filesystem directory for blog thumbnails.
 */
function get_blog_thumbnails_dir() {
    $thumb = get_blog_uploads_dir() . '/thumbnails';
    if (!file_exists($thumb)) {
        @mkdir($thumb, 0755, true);
    }
    return $thumb;
}

/**
 * Saves an uploaded blog image into both the persistent uploads_blogs directory
 * and the local web-accessible assets/blogs directory.
 */
function save_blog_upload($tmpPath, $fileName, $subDir = '') {
    return save_dual_media_upload(
        $tmpPath,
        $fileName,
        get_blog_uploads_dir(),
        dirname(__DIR__) . '/assets/blogs',
        $subDir
    );
}

/**
 * Attempts to delete a blog image from both live uploads_blogs and local assets/blogs.
 */
function delete_blog_image_file($relativePath) {
    if (empty($relativePath)) return false;
    $isThumb = (strpos($relativePath, 'thumbnails/') !== false);
    $persistentDir = $isThumb ? get_blog_thumbnails_dir() : get_blog_uploads_dir();
    return delete_dual_media_file($relativePath, $persistentDir, dirname(__DIR__));
}
