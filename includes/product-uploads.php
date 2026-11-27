<?php
// includes/product-uploads.php
// Centralized persistent storage resolver for uploaded product images & galleries.
// Mirrors blog-uploads.php so product uploads persist outside public_html in uploads_products/
// and stay shared safely between rtchocos.com (main) and beta.rtchocos.com (beta).

require_once __DIR__ . '/media-storage.php';

/**
 * Returns the persistent filesystem directory where uploaded product images are stored.
 * On Hostinger live server, resolves to uploads_products/ outside public_html.
 * On local dev, falls back to <project_root>/assets/products.
 */
function get_product_uploads_dir() {
    return resolve_persistent_uploads_dir('uploads_products', 'assets/products');
}

/**
 * Saves an uploaded product image into both persistent uploads_products/
 * and the local web-accessible assets/products/ folder.
 */
function save_product_upload($tmpPath, $fileName, $subDir = '') {
    return save_dual_media_upload(
        $tmpPath,
        $fileName,
        get_product_uploads_dir(),
        dirname(__DIR__) . '/assets/products',
        $subDir
    );
}

/**
 * Deletes an uploaded product image from both persistent uploads_products/ and local assets/products/.
 */
function delete_product_image_file($relativePath) {
    return delete_dual_media_file($relativePath, get_product_uploads_dir(), dirname(__DIR__));
}
