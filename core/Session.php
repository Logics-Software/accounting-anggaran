<?php
class Session {
    private static $started = false;
    private static $closed = false; // Track if session was explicitly closed
    
    public static function start() {
        // FIX: Don't start session if headers already sent or if explicitly closed
        if (headers_sent()) {
            return false; // Can't start session after headers sent
        }
        
        if (session_status() === PHP_SESSION_NONE && !self::$closed) {
            $config = require __DIR__ . '/../config/app.php';
            
            // OPTIMIZATION: Optimize cookie settings for security and performance
            $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            session_set_cookie_params([
                'lifetime' => 0, // Session cookie (expires when browser closes)
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure, // HTTPS only if available
                'httponly' => true, // Prevent XSS attacks
                'samesite' => 'Lax' // CSRF protection (Lax for better compatibility)
            ]);
            
            session_name($config['session_name']);
            session_start();
            self::$started = true;
            self::$closed = false;
            
            // IDLE TIMEOUT: Track last activity time
            self::updateLastActivity();
            
            return true;
        }
        
        // IDLE TIMEOUT: Update last activity on each request
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::updateLastActivity();
        }
        
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    /**
     * IDLE TIMEOUT: Update last activity timestamp
     */
    private static function updateLastActivity() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['last_activity'] = time();
            // Also track session creation time if not set
            if (!isset($_SESSION['created_at'])) {
                $_SESSION['created_at'] = time();
            }
        }
    }
    
    /**
     * IDLE TIMEOUT: Check if session has expired due to idle timeout
     * Only checks idle timeout, not max lifetime (session can be active indefinitely if user is active)
     * 
     * @return bool True if session is expired, false otherwise
     */
    public static function isExpired() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false; // No session to check
        }
        
        $config = require __DIR__ . '/../config/app.php';
        $idleTimeout = $config['session_idle_timeout'] ?? 1800; // Default 30 minutes
        
        $now = time();
        
        // Only check idle timeout (no activity for X seconds)
        // If user is active, session can continue indefinitely
        if (isset($_SESSION['last_activity'])) {
            $idleTime = $now - $_SESSION['last_activity'];
            if ($idleTime > $idleTimeout) {
                return true; // Session expired due to idle timeout
            }
        }
        
        return false; // Session is still valid (user is active)
    }
    
    /**
     * IDLE TIMEOUT: Get remaining idle time in seconds
     * 
     * @return int Remaining idle time in seconds, or 0 if expired
     */
    public static function getRemainingIdleTime() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return 0;
        }
        
        $config = require __DIR__ . '/../config/app.php';
        $idleTimeout = $config['session_idle_timeout'] ?? 1800;
        
        if (!isset($_SESSION['last_activity'])) {
            return $idleTimeout; // No activity tracked yet, return full timeout
        }
        
        $now = time();
        $idleTime = $now - $_SESSION['last_activity'];
        $remaining = $idleTimeout - $idleTime;
        
        return max(0, $remaining); // Return 0 if expired
    }
    
    /**
     * OPTIMIZATION: Close session early to release lock
     * Use after reading session data to allow concurrent requests
     * NOTE: After closing, session cannot be restarted (headers already sent)
     */
    public static function writeClose() {
        if (self::$started && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            self::$started = false;
            self::$closed = true; // Mark as closed to prevent restart
        }
    }
    
    /**
     * OPTIMIZATION: Regenerate session ID periodically for security
     * Call this after login or every 30 minutes
     */
    public static function regenerateId($deleteOldSession = true) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOldSession);
        }
    }
    
    public static function set($key, $value) {
        // FIX: Don't try to start if already closed or headers sent
        if (session_status() === PHP_SESSION_NONE && !self::$closed && !headers_sent()) {
            self::start();
        }
        // Only set if session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$key] = $value;
        }
    }
    
    public static function get($key, $default = null) {
        // FIX: Don't try to start if already closed or headers sent
        if (session_status() === PHP_SESSION_NONE && !self::$closed && !headers_sent()) {
            self::start();
        }
        // Only get if session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            return $_SESSION[$key] ?? $default;
        }
        return $default;
    }
    
    public static function has($key) {
        // FIX: Don't try to start if already closed or headers sent
        if (session_status() === PHP_SESSION_NONE && !self::$closed && !headers_sent()) {
            self::start();
        }
        // Only check if session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            return isset($_SESSION[$key]);
        }
        return false;
    }
    
    public static function remove($key) {
        // FIX: Don't try to start if already closed or headers sent
        if (session_status() === PHP_SESSION_NONE && !self::$closed && !headers_sent()) {
            self::start();
        }
        // Only remove if session is active
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            self::$started = false;
        }
    }
    
    public static function flash($key, $value = null) {
        // FIX: Don't try to start if already closed or headers sent
        if (session_status() === PHP_SESSION_NONE && !self::$closed && !headers_sent()) {
            self::start();
        }
        
        // Only access if session is active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $value === null ? null : false;
        }
        
        if ($value === null) {
            $message = $_SESSION['flash'][$key] ?? null;
            if (isset($_SESSION['flash'][$key])) {
                unset($_SESSION['flash'][$key]);
            }
            return $message;
        }
        $_SESSION['flash'][$key] = $value;
        return true;
    }
    
    /**
     * OPTIMIZATION: Get all session data (for debugging/monitoring)
     */
    public static function all() {
        // FIX: Don't try to start if already closed or headers sent
        if (session_status() === PHP_SESSION_NONE && !self::$closed && !headers_sent()) {
            self::start();
        }
        // Only return if session is active
        if (session_status() === PHP_SESSION_ACTIVE) {
            return $_SESSION ?? [];
        }
        return [];
    }
    
    /**
     * OPTIMIZATION: Check if session is active
     */
    public static function isActive() {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}

