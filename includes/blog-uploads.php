<?php
// includes/blog-uploads.php
// Centralized path resolver for blog image uploads & assets
// Handles Hostinger live environment (main site, beta subfolder, beta subdomain) and local dev.

/**
 * Returns the absolute filesystem directory where blog uploads are stored.
 * On live server (main or beta under public_html), this resolves to the persistent
 * uploads folder outside public_html: /home/<user>/uploads_blogs
 * On local dev, falls back to <project_root>/assets/blogs
 */
function get_blog_uploads_dir() {
    static $resolvedDir = null;
    if ($resolvedDir !== null) {
        return $resolvedDir;
    }

    $candidates = [];

    // Candidate 1: Detect public_html in __DIR__, DOCUMENT_ROOT, or SCRIPT_FILENAME and get parent of public_html
    $searchPaths = [
        __DIR__,
        $_SERVER['DOCUMENT_ROOT'] ?? '',
        $_SERVER['SCRIPT_FILENAME'] ?? '',
        dirname(__DIR__)
    ];

    foreach ($searchPaths as $sp) {
        if (!empty($sp) && preg_match('#^(.*?[/\\\\]public_html)([/\\\\].*)?$#i', $sp, $m)) {
            $publicHtml = $m[1];
            $accountRoot = dirname($publicHtml);
            $candidates[] = $accountRoot . '/uploads_blogs';
        }
    }

    // Candidate 2: Walk upwards from __DIR__ (up to 6 levels)
    $curr = __DIR__;
    for ($i = 0; $i < 6; $i++) {
        $candidates[] = $curr . '/uploads_blogs';
        $parent = dirname($curr);
        if ($parent === $curr) break;
        $curr = $parent;
    }

    // Candidate 3: Walk upwards from DOCUMENT_ROOT (up to 5 levels)
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $curr = $_SERVER['DOCUMENT_ROOT'];
        for ($i = 0; $i < 5; $i++) {
            $candidates[] = $curr . '/uploads_blogs';
            $parent = dirname($curr);
            if ($parent === $curr) break;
            $curr = $parent;
        }
    }

    // Check if any candidate already exists
    foreach ($candidates as $cand) {
        if (!empty($cand) && is_dir($cand)) {
            $resolvedDir = str_replace('\\', '/', realpath($cand) ?: $cand);
            return $resolvedDir;
        }
    }

    // If on live server where public_html was detected, create and use the account-level uploads_blogs
    foreach ($searchPaths as $sp) {
        if (!empty($sp) && preg_match('#^(.*?[/\\\\]public_html)([/\\\\].*)?$#i', $sp, $m)) {
            $target = dirname($m[1]) . '/uploads_blogs';
            if (!file_exists($target)) {
                @mkdir($target, 0755, true);
            }
            if (is_dir($target)) {
                $resolvedDir = str_replace('\\', '/', realpath($target) ?: $target);
                return $resolvedDir;
            }
        }
    }

    // Fallback: local development project root assets/blogs
    $localAssets = dirname(__DIR__) . '/assets/blogs';
    if (!file_exists($localAssets)) {
        @mkdir($localAssets, 0755, true);
    }
    $resolvedDir = str_replace('\\', '/', realpath($localAssets) ?: $localAssets);
    return $resolvedDir;
}

/**
 * Returns the absolute filesystem directory for blog thumbnails.
 */
function get_blog_thumbnails_dir() {
    $base = get_blog_uploads_dir();
    $thumb = $base . '/thumbnails';
    if (!file_exists($thumb)) {
        @mkdir($thumb, 0755, true);
    }
    return $thumb;
}

/**
 * Saves an uploaded blog image into both the persistent safe uploads directory
 * and the local web-accessible assets/blogs directory.
 * Ensures the image is immediately visible without relying on symlinks or rewrites,
 * while keeping the permanent copy in uploads_blogs.
 */
function save_blog_upload($tmpPath, $fileName, $subDir = '') {
    $uploadsDir = get_blog_uploads_dir();
    $webAssetsDir = dirname(__DIR__) . '/assets/blogs';

    if (!empty($subDir)) {
        $sub = trim($subDir, '/\\');
        $uploadsDir .= '/' . $sub;
        $webAssetsDir .= '/' . $sub;
    }

    if (!file_exists($uploadsDir)) @mkdir($uploadsDir, 0755, true);
    if (!file_exists($webAssetsDir)) @mkdir($webAssetsDir, 0755, true);

    $destPrimary = $uploadsDir . '/' . $fileName;
    $destWeb = $webAssetsDir . '/' . $fileName;

    $success = false;

    if (is_uploaded_file($tmpPath)) {
        if (@copy($tmpPath, $destPrimary) || @move_uploaded_file($tmpPath, $destPrimary)) {
            $success = true;
            @copy($destPrimary, $destWeb);
        } elseif (@move_uploaded_file($tmpPath, $destWeb)) {
            $success = true;
            @copy($destWeb, $destPrimary);
        }
    } else {
        if (@copy($tmpPath, $destPrimary)) {
            $success = true;
            @copy($destPrimary, $destWeb);
        } elseif (@copy($tmpPath, $destWeb)) {
            $success = true;
            @copy($destWeb, $destPrimary);
        }
    }

    return $success;
}

/**
 * Attempts to delete a blog image from both live uploads_blogs and local assets/blogs.
 */
function delete_blog_image_file($relativePath) {
    if (empty($relativePath)) return false;
    $filename = basename($relativePath);
    
    // Check if it's in thumbnails/
    $isThumb = (strpos($relativePath, 'thumbnails/') !== false);
    $uploadsDir = $isThumb ? get_blog_thumbnails_dir() : get_blog_uploads_dir();
    $liveFile = $uploadsDir . '/' . $filename;
    
    // Local path
    $localFile = dirname(__DIR__) . '/' . ltrim($relativePath, '/\\');
    
    $deleted = false;
    if (file_exists($liveFile)) {
        if (@unlink($liveFile)) $deleted = true;
    }
    if (file_exists($localFile) && realpath($localFile) !== realpath($liveFile)) {
        if (@unlink($localFile)) $deleted = true;
    }
    return $deleted;
}
