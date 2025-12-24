<?php
namespace Models\Common;

use Database;
use Cache;

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'aktif'";
        return $this->db->fetchOne($sql, [$username]);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        // Generate cache key
        $cacheKey = "users_all_{$page}_{$perPage}_" . md5($search) . "_{$sortBy}_{$sortOrder}";
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR namalengkap LIKE ? OR email LIKE ? OR nomorhp LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'username', 'namalengkap', 'email', 'role', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM users WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $result = $this->db->fetchAll($sql, $params);
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $result, 300);
        
        return $result;
    }
    
    public function count($search = '') {
        // Generate cache key
        $cacheKey = "user_count_" . md5($search);
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR namalengkap LIKE ? OR email LIKE ? OR nomorhp LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        $total = $result['total'] ?? 0;
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $total, 300);
        
        return $total;
    }
    
    /**
     * Count users by status
     * 
     * @param string $status Status to count ('aktif' or 'nonaktif')
     * @return int Total count
     */
    public function countByStatus($status = 'aktif') {
        // Generate cache key
        $cacheKey = "user_count_status_{$status}";
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE status = ?";
        $result = $this->db->fetchOne($sql, [$status]);
        $total = $result['total'] ?? 0;
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $total, 300);
        
        return $total;
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, namalengkap, email, password, role, nomorhp, picture, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['username'],
            $data['namalengkap'],
            $data['email'],
            $this->hashPassword($data['password']),
            $data['role'],
            $data['nomorhp'] ?? null,
            $data['picture'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        $userId = $this->db->lastInsertId();
        
        // Invalidate user-related cache
        $this->invalidateCache();
        
        return $userId;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['username', 'namalengkap', 'email', 'role', 'nomorhp', 'picture', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = $this->hashPassword($data['password']);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $result = $this->db->query($sql, $params);
        
        // Invalidate user-related cache
        $this->invalidateCache();
        Cache::delete("user_{$id}"); // Invalidate specific user cache if exists
        
        return $result;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        
        // Invalidate user-related cache
        $this->invalidateCache();
        Cache::delete("user_{$id}"); // Invalidate specific user cache if exists
        
        return $result;
    }
    
    /**
     * Invalidate all user-related cache
     * Called after create, update, or delete operations
     */
    private function invalidateCache() {
        // Clear all user-related cache
        // Get cache directory - same path as Cache class uses
        // Cache class is in core/, so it uses __DIR__ . '/../cache/'
        // User model is in models/Common/, so we use __DIR__ . '/../../cache/'
        $cacheDir = __DIR__ . '/../../cache/';
        if (!is_dir($cacheDir)) {
            return; // Cache directory doesn't exist yet
        }
        
        $files = glob($cacheDir . '*.cache');
        if (empty($files)) {
            return; // No cache files to process
        }
        
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            
            $data = @unserialize(file_get_contents($file));
            if (!is_array($data) || !isset($data['key'])) {
                continue; // Skip invalid cache files
            }
            
            // Check if key contains user-related prefixes
            // Matches: user_, users_, users_all_, user_count_, user_count_status_
            $key = $data['key'];
            if (strpos($key, 'user_') === 0 || 
                strpos($key, 'users_') === 0 ||
                strpos($key, 'users_all_') === 0 ||
                strpos($key, 'user_count') === 0) {
                @unlink($file);
            }
        }
    }
}

