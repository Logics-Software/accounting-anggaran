<?php
/**
 * Cache Class - Simple file-based cache implementation
 * 
 * Provides caching functionality for query results and other data.
 * Can be extended to support Redis/Memcached for production.
 * 
 * Usage:
 *   Cache::set('key', $value, 3600);  // Cache for 1 hour
 *   $value = Cache::get('key');       // Get from cache
 *   Cache::delete('key');              // Delete from cache
 *   Cache::clear();                    // Clear all cache
 */
class Cache {
    private static $cacheDir = null;
    private static $stats = [
        'hits' => 0,
        'misses' => 0
    ];
    
    /**
     * Get cache directory path
     * 
     * @return string Cache directory path
     */
    private static function getCacheDir() {
        if (self::$cacheDir === null) {
            self::$cacheDir = __DIR__ . '/../cache/';
            if (!is_dir(self::$cacheDir)) {
                mkdir(self::$cacheDir, 0755, true);
                // Create .gitignore to exclude cache files
                file_put_contents(self::$cacheDir . '.gitignore', "*\n!.gitignore\n");
            }
        }
        return self::$cacheDir;
    }
    
    /**
     * Get cached value
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed Cached value or default
     */
    public static function get($key, $default = null) {
        $file = self::getCacheDir() . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            self::$stats['misses']++;
            return $default;
        }
        
        $data = @unserialize(file_get_contents($file));
        
        if ($data === false || !is_array($data)) {
            // Corrupted cache file, delete it
            @unlink($file);
            self::$stats['misses']++;
            return $default;
        }
        
        // Check expiration
        if (isset($data['expires']) && $data['expires'] < time()) {
            @unlink($file);
            self::$stats['misses']++;
            return $default;
        }
        
        self::$stats['hits']++;
        return $data['value'] ?? $default;
    }
    
    /**
     * Set cached value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds (default: 3600 = 1 hour)
     * @return bool Success
     */
    public static function set($key, $value, $ttl = 3600) {
        $file = self::getCacheDir() . md5($key) . '.cache';
        
        $data = [
            'value' => $value,
            'expires' => time() + (int)$ttl,
            'created' => time(),
            'key' => $key // Store original key for debugging
        ];
        
        $result = @file_put_contents($file, serialize($data), LOCK_EX);
        
        if ($result === false) {
            error_log("Cache: Failed to write cache file: {$file}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete cached value
     * 
     * @param string $key Cache key
     * @return bool Success
     */
    public static function delete($key) {
        $file = self::getCacheDir() . md5($key) . '.cache';
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }
    
    /**
     * Check if key exists and is valid
     * 
     * @param string $key Cache key
     * @return bool
     */
    public static function has($key) {
        $file = self::getCacheDir() . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = @unserialize(file_get_contents($file));
        if ($data === false || !is_array($data)) {
            @unlink($file);
            return false;
        }
        
        return isset($data['expires']) && $data['expires'] > time();
    }
    
    /**
     * Clear all cache
     * 
     * @return int Number of files deleted
     */
    public static function clear() {
        $dir = self::getCacheDir();
        $files = glob($dir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && @unlink($file)) {
                $count++;
            }
        }
        
        // Reset stats
        self::$stats = ['hits' => 0, 'misses' => 0];
        
        return $count;
    }
    
    /**
     * Clear expired cache files
     * 
     * @return int Number of files deleted
     */
    public static function clearExpired() {
        $dir = self::getCacheDir();
        $files = glob($dir . '*.cache');
        $count = 0;
        $now = time();
        
        foreach ($files as $file) {
            $data = @unserialize(file_get_contents($file));
            if ($data === false || !is_array($data)) {
                // Corrupted file, delete it
                @unlink($file);
                $count++;
                continue;
            }
            
            if (isset($data['expires']) && $data['expires'] < $now) {
                @unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Statistics (hits, misses, hit_rate)
     */
    public static function getStats() {
        $total = self::$stats['hits'] + self::$stats['misses'];
        $hitRate = $total > 0 ? (self::$stats['hits'] / $total) * 100 : 0;
        
        return [
            'hits' => self::$stats['hits'],
            'misses' => self::$stats['misses'],
            'total' => $total,
            'hit_rate' => round($hitRate, 2)
        ];
    }
    
    /**
     * Get cache size (number of cached items)
     * 
     * @return int Number of cache files
     */
    public static function getSize() {
        $dir = self::getCacheDir();
        $files = glob($dir . '*.cache');
        return count($files);
    }
    
    /**
     * Get cache directory size in bytes
     * 
     * @return int Total size in bytes
     */
    public static function getDirectorySize() {
        $dir = self::getCacheDir();
        $files = glob($dir . '*.cache');
        $size = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }
}

