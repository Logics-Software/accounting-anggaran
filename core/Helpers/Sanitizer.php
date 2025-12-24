<?php
/**
 * Sanitizer Helper Class
 * 
 * Centralized input sanitization for security
 * 
 * @package Core\Helpers
 */
class Sanitizer {
    /**
     * Sanitize string input
     * Remove HTML tags and trim whitespace
     * 
     * @param mixed $value Value to sanitize
     * @return string Sanitized string
     */
    public static function string($value) {
        if ($value === null) {
            return '';
        }
        
        // Convert to string
        $value = (string)$value;
        
        // Trim whitespace
        $value = trim($value);
        
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        return $value;
    }
    
    /**
     * Sanitize string but allow HTML (with escaping)
     * Use for display purposes only
     * 
     * @param mixed $value Value to sanitize
     * @return string Sanitized string with HTML escaped
     */
    public static function html($value) {
        if ($value === null) {
            return '';
        }
        
        $value = self::string($value);
        
        // Escape HTML special characters
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize email address
     * 
     * @param mixed $value Email to sanitize
     * @return string Sanitized email or empty string
     */
    public static function email($value) {
        if (empty($value)) {
            return '';
        }
        
        $value = self::string($value);
        
        // Filter and validate email
        $email = filter_var($value, FILTER_SANITIZE_EMAIL);
        
        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return '';
    }
    
    /**
     * Sanitize integer value
     * 
     * @param mixed $value Value to sanitize
     * @return int Sanitized integer or 0
     */
    public static function int($value) {
        if ($value === null || $value === '') {
            return 0;
        }
        
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitize float value
     * 
     * @param mixed $value Value to sanitize
     * @return float Sanitized float or 0.0
     */
    public static function float($value) {
        if ($value === null || $value === '') {
            return 0.0;
        }
        
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Sanitize URL
     * 
     * @param mixed $value URL to sanitize
     * @return string Sanitized URL or empty string
     */
    public static function url($value) {
        if (empty($value)) {
            return '';
        }
        
        $value = self::string($value);
        
        // Filter and validate URL
        $url = filter_var($value, FILTER_SANITIZE_URL);
        
        // Validate URL format
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        return '';
    }
    
    /**
     * Sanitize array of values
     * 
     * @param array $array Array to sanitize
     * @param string $type Sanitization type (string, html, email, int, float)
     * @return array Sanitized array
     */
    public static function array($array, $type = 'string') {
        if (!is_array($array)) {
            return [];
        }
        
        $sanitized = [];
        foreach ($array as $key => $value) {
            $sanitizedKey = self::string($key);
            
            if (is_array($value)) {
                $sanitized[$sanitizedKey] = self::array($value, $type);
            } else {
                switch ($type) {
                    case 'html':
                        $sanitized[$sanitizedKey] = self::html($value);
                        break;
                    case 'email':
                        $sanitized[$sanitizedKey] = self::email($value);
                        break;
                    case 'int':
                        $sanitized[$sanitizedKey] = self::int($value);
                        break;
                    case 'float':
                        $sanitized[$sanitizedKey] = self::float($value);
                        break;
                    default:
                        $sanitized[$sanitizedKey] = self::string($value);
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize POST data
     * 
     * @param array $data POST data array
     * @return array Sanitized data
     */
    public static function post($data) {
        if (!is_array($data)) {
            return [];
        }
        
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitizedKey = self::string($key);
            
            if (is_array($value)) {
                $sanitized[$sanitizedKey] = self::array($value);
            } else {
                $sanitized[$sanitizedKey] = self::string($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize GET data
     * 
     * @param array $data GET data array
     * @return array Sanitized data
     */
    public static function get($data) {
        return self::post($data); // Same logic
    }
    
    /**
     * Clean input for database (prevent SQL injection)
     * Note: This is a secondary layer. Always use prepared statements!
     * 
     * @param mixed $value Value to clean
     * @return string Cleaned value
     */
    public static function db($value) {
        if ($value === null) {
            return '';
        }
        
        $value = self::string($value);
        
        // Remove SQL injection patterns (secondary protection)
        $dangerous = [
            '/(\bUNION\b)/i',
            '/(\bSELECT\b)/i',
            '/(\bINSERT\b)/i',
            '/(\bUPDATE\b)/i',
            '/(\bDELETE\b)/i',
            '/(\bDROP\b)/i',
            '/(\bCREATE\b)/i',
            '/(\bALTER\b)/i',
            '/(\bEXEC\b)/i',
            '/(\bEXECUTE\b)/i',
            '/(\bSCRIPT\b)/i',
        ];
        
        // Note: This is NOT a replacement for prepared statements!
        // This is just an additional layer of protection
        foreach ($dangerous as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }
        
        return $value;
    }
    
    /**
     * Sanitize filename
     * Remove dangerous characters from filename
     * 
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    public static function filename($filename) {
        if (empty($filename)) {
            return '';
        }
        
        // Remove path components
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Remove leading dots
        $filename = ltrim($filename, '.');
        
        return $filename;
    }
}

