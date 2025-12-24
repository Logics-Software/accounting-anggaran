<?php
namespace Core\Helpers;

/**
 * VIEW OPTIMIZATION: Asset Helper for versioning and cache headers
 * 
 * Provides asset versioning based on file modification time
 * and helps with cache busting
 */
class AssetHelper {
    /**
     * Get asset URL with version for cache busting
     * 
     * @param string $path Relative path from assets directory (e.g., 'css/style.css')
     * @param string $baseUrl Base URL of the application
     * @return string Asset URL with version query parameter
     */
    public static function url($path, $baseUrl = '/') {
        // Normalize baseUrl
        $baseUrl = rtrim($baseUrl, '/');
        if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
            $baseUrl = '';
        }
        
        $fullPath = __DIR__ . '/../../assets/' . ltrim($path, '/');
        
        // VIEW OPTIMIZATION: Use file modification time as version for cache busting
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        
        // Build asset path - always start with /assets/
        $assetsPath = '/assets/' . ltrim($path, '/');
        
        // If baseUrl is empty (root), return path directly
        // Otherwise, concatenate baseUrl + assetsPath
        if ($baseUrl === '') {
            return $assetsPath . '?v=' . $version;
        }
        
        // Ensure no double slashes
        return rtrim($baseUrl, '/') . $assetsPath . '?v=' . $version;
    }
    
    /**
     * Get CSS asset URL
     * 
     * @param string $path Relative path from assets/css (e.g., 'style.css')
     * @param string $baseUrl Base URL of the application
     * @return string CSS asset URL with version
     */
    public static function css($path, $baseUrl = '/') {
        return self::url('css/' . ltrim($path, '/'), $baseUrl);
    }
    
    /**
     * Get JS asset URL
     * 
     * @param string $path Relative path from assets/js (e.g., 'app.js')
     * @param string $baseUrl Base URL of the application
     * @return string JS asset URL with version
     */
    public static function js($path, $baseUrl = '/') {
        return self::url('js/' . ltrim($path, '/'), $baseUrl);
    }
    
    /**
     * Get image asset URL
     * 
     * @param string $path Relative path from assets/images (e.g., 'logo.png')
     * @param string $baseUrl Base URL of the application
     * @return string Image asset URL with version
     */
    public static function image($path, $baseUrl = '/') {
        return self::url('images/' . ltrim($path, '/'), $baseUrl);
    }
    
    /**
     * Get icon asset URL
     * 
     * @param string $path Relative path from assets/icons (e.g., 'user.svg')
     * @param string $baseUrl Base URL of the application
     * @return string Icon asset URL with version
     */
    public static function icon($path, $baseUrl = '/') {
        return self::url('icons/' . ltrim($path, '/'), $baseUrl);
    }
}

