<?php
// includes/media-storage.php
// Shared persistent account-level media storage resolver and dual-save helpers.
// Stores uploaded assets outside public_html (uploads_blogs / uploads_products)
// alongside a web-accessible copy in assets/* so deployments never erase live uploads.

/**
 * Resolves the persistent filesystem directory outside public_html when running on Hostinger,
 * or falls back to the local assets subdirectory in local development.
 */
function resolve_persistent_uploads_dir($externalFolderName, $localFallbackSubdir) {
    static $resolved = [];
    $cacheKey = $externalFolderName . '|' . $localFallbackSubdir;
    if (isset($resolved[$cacheKey])) {
        return $resolved[$cacheKey];
    }

    $candidates = [];
    $searchPaths = [
        __DIR__,
        $_SERVER['DOCUMENT_ROOT'] ?? '',
        $_SERVER['SCRIPT_FILENAME'] ?? '',
        dirname(__DIR__)
    ];

    // Candidate 1: Parent of public_html on Hostinger / shared hosts
    foreach ($searchPaths as $sp) {
        if (!empty($sp) && preg_match('#^(.*?[/\\\\]public_html)([/\\\\].*)?$#i', $sp, $m)) {
            $candidates[] = dirname($m[1]) . '/' . $externalFolderName;
        }
    }

    // Candidate 2: Walk upwards from __DIR__ (up to 6 levels)
    $curr = __DIR__;
    for ($i = 0; $i < 6; $i++) {
        $candidates[] = $curr . '/' . $externalFolderName;
        $parent = dirname($curr);
        if ($parent === $curr) break;
        $curr = $parent;
    }

    // Candidate 3: Walk upwards from DOCUMENT_ROOT (up to 5 levels)
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $curr = $_SERVER['DOCUMENT_ROOT'];
        for ($i = 0; $i < 5; $i++) {
            $candidates[] = $curr . '/' . $externalFolderName;
            $parent = dirname($curr);
            if ($parent === $curr) break;
            $curr = $parent;
        }
    }

    foreach ($candidates as $cand) {
        if (!empty($cand) && is_dir($cand)) {
            return $resolved[$cacheKey] = str_replace('\\', '/', realpath($cand) ?: $cand);
        }
    }

    // Create persistent folder outside public_html when detected
    foreach ($searchPaths as $sp) {
        if (!empty($sp) && preg_match('#^(.*?[/\\\\]public_html)([/\\\\].*)?$#i', $sp, $m)) {
            $target = dirname($m[1]) . '/' . $externalFolderName;
            if (!file_exists($target)) {
                @mkdir($target, 0755, true);
            }
            if (is_dir($target)) {
                return $resolved[$cacheKey] = str_replace('\\', '/', realpath($target) ?: $target);
            }
        }
    }

    // Local development fallback
    $localAssets = dirname(__DIR__) . '/' . trim($localFallbackSubdir, '/\\');
    if (!file_exists($localAssets)) {
        @mkdir($localAssets, 0755, true);
    }
    return $resolved[$cacheKey] = str_replace('\\', '/', realpath($localAssets) ?: $localAssets);
}

/**
 * Dual-saves an uploaded file into both the persistent external uploads folder
 * and the site's local web assets directory.
 */
function save_dual_media_upload($tmpPath, $fileName, $persistentBaseDir, $webAssetsBaseDir, $subDir = '') {
    $uploadsDir = $persistentBaseDir;
    $webAssetsDir = $webAssetsBaseDir;

    if (!empty($subDir)) {
        $sub = trim($subDir, '/\\');
        $uploadsDir .= '/' . $sub;
        $webAssetsDir .= '/' . $sub;
    }

    if (!file_exists($uploadsDir)) @mkdir($uploadsDir, 0755, true);
    if (!file_exists($webAssetsDir)) @mkdir($webAssetsDir, 0755, true);

    $destPrimary = $uploadsDir . '/' . $fileName;
    $destWeb = $webAssetsDir . '/' . $fileName;
    $sameTarget = (str_replace('\\', '/', $uploadsDir) === str_replace('\\', '/', $webAssetsDir));

    $success = false;
    if (is_uploaded_file($tmpPath)) {
        if (@copy($tmpPath, $destPrimary) || @move_uploaded_file($tmpPath, $destPrimary)) {
            $success = true;
            if (!$sameTarget) {
                @copy($destPrimary, $destWeb);
            }
        } elseif (@move_uploaded_file($tmpPath, $destWeb)) {
            $success = true;
            if (!$sameTarget) {
                @copy($destWeb, $destPrimary);
            }
        }
    } else {
        if (@copy($tmpPath, $destPrimary)) {
            $success = true;
            if (!$sameTarget) {
                @copy($destPrimary, $destWeb);
            }
        } elseif (@copy($tmpPath, $destWeb)) {
            $success = true;
            if (!$sameTarget) {
                @copy($destWeb, $destPrimary);
            }
        }
    }

    return $success;
}

/**
 * Deletes an uploaded media file from both persistent storage and local web assets.
 */
function delete_dual_media_file($relativePath, $persistentDir, $rootDir) {
    if (empty($relativePath)) return false;
    $filename = basename($relativePath);
    $liveFile = $persistentDir . '/' . $filename;
    $localFile = rtrim($rootDir, '/\\') . '/' . ltrim($relativePath, '/\\');

    $deleted = false;
    if (file_exists($liveFile) && is_file($liveFile)) {
        if (@unlink($liveFile)) $deleted = true;
    }
    if (file_exists($localFile) && is_file($localFile) && realpath($localFile) !== realpath($liveFile)) {
        if (@unlink($localFile)) $deleted = true;
    }
    return $deleted;
}
