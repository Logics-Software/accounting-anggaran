<?php
/**
 * Environment Configuration Helper
 * 
 * Loads environment variables from .env file
 * 
 * @package Core\Helpers
 */
class Env {
    private static $loaded = false;
    
    /**
     * Load environment variables from .env file
     * 
     * @param string $envPath Path to .env file
     * @return void
     */
    public static function load($envPath = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($envPath === null) {
            $envPath = __DIR__ . '/../../.env';
        }
        
        if (!file_exists($envPath)) {
            return;
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Set environment variable if not already set
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value if not found
     * @return mixed Environment variable value or default
     */
    public static function get($key, $default = null) {
        self::load();
        
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convert string booleans
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }
        
        // Don't convert to numeric if:
        // 1. Value starts with 0 (like passwords: 051199)
        // 2. Key contains PASS, PASSWORD, SECRET, KEY, TOKEN (sensitive data)
        $sensitiveKeys = ['PASS', 'PASSWORD', 'SECRET', 'KEY', 'TOKEN'];
        $isSensitive = false;
        foreach ($sensitiveKeys as $sensitive) {
            if (stripos($key, $sensitive) !== false) {
                $isSensitive = true;
                break;
            }
        }
        
        // Convert numeric strings only if not sensitive and doesn't start with 0
        if (!$isSensitive && is_numeric($value) && substr($value, 0, 1) !== '0') {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        return $value;
    }
    
    /**
     * Check if environment is production
     * 
     * @return bool True if production
     */
    public static function isProduction() {
        return self::get('APP_ENV', 'development') === 'production';
    }
    
    /**
     * Check if debug mode is enabled
     * 
     * @return bool True if debug enabled
     */
    public static function isDebug() {
        return self::get('APP_DEBUG', false) === true || self::get('APP_DEBUG', 'false') === 'true';
    }
}

