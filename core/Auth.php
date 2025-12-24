<?php
use Models\Common\User;

class Auth {
    public static function check() {
        Session::start();
        
        // IDLE TIMEOUT: Check if session has expired
        if (Session::isExpired()) {
            // Session expired, destroy it
            Session::destroy();
            return false;
        }
        
        $hasUser = Session::has('user_id');
        // OPTIMIZATION: Close session early after read (if not needed for write)
        // Note: We keep session open here because check() is often followed by user() or login()
        return $hasUser;
    }
    
    public static function user() {
        Session::start();
        if (Session::has('user_id')) {
            // OPTIMIZATION: Store only user_id in session, load full user from database
            // This minimizes session data size
            $userId = Session::get('user_id');
            
            // FIX: Don't close session here - it might be needed for flash messages
            // Session will be closed automatically by PHP at end of script
            // Or explicitly closed in redirect() if needed
            
            $userModel = new User();
            return $userModel->findById($userId);
        }
        return null;
    }
    
    public static function login($userId, $userData) {
        Session::start();
        
        // IDLE TIMEOUT: Reset last activity timestamp on login
        // Note: We don't track created_at anymore since we only check idle timeout
        $_SESSION['last_activity'] = time();
        
        // OPTIMIZATION: Store only essential data in session
        // Full user data will be loaded from database when needed
        Session::set('user_id', $userId);
        Session::set('user_role', $userData['role'] ?? 'user');
        // OPTIMIZATION: Don't store full user data, only essential fields
        // Removed: Session::set('user_username', $userData['username']);
        
        // OPTIMIZATION: Regenerate session ID after login for security
        Session::regenerateId(true);
        
        // FIX: Don't close session here - it might be needed for flash messages
        // Session will be closed automatically by PHP at end of script
        // Or explicitly closed in redirect() if needed
    }
    
    public static function logout() {
        Session::start();
        Session::destroy();
    }
    
    public static function isAdmin() {
        return self::user() && self::user()['role'] === 'admin';
    }
    
    public static function isManajemen() {
        $user = self::user();
        return $user && ($user['role'] === 'admin' || $user['role'] === 'manajemen');
    }
    
    public static function isUser() {
        $user = self::user();
        return $user && $user['role'] === 'user';
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            // IDLE TIMEOUT: Check if session expired due to idle timeout
            Session::start();
            if (Session::isExpired()) {
                // Session expired, set flash message and redirect to login
                Session::flash('error', 'Session Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
            } else {
                // No session, just redirect to login
                Session::flash('info', 'Silakan login untuk melanjutkan.');
            }
            header('Location: /login');
            exit;
        }
    }
    
    public static function requireRole($roles) {
        self::requireAuth();
        $user = self::user();
        if (!in_array($user['role'], (array)$roles)) {
            header('Location: /dashboard');
            exit;
        }
    }
}

