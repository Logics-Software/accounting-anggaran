<?php
namespace Models\Reference;

use Database;
use Cache;

class SettingBidang {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM setting_bidang WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        // Generate cache key
        $cacheKey = "setting_bidang_all_{$page}_{$perPage}_" . md5($search) . "_{$sortBy}_{$sortOrder}";
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (namabidang LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam];
        }
        
        $validSortColumns = ['id', 'namabidang', 'pengelola_akun', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM setting_bidang WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $result = $this->db->fetchAll($sql, $params);
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $result, 300);
        
        return $result;
    }
    
    public function count($search = '') {
        // Generate cache key
        $cacheKey = "setting_bidang_count_" . md5($search);
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (namabidang LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM setting_bidang WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        $total = $result['total'] ?? 0;
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $total, 300);
        
        return $total;
    }
    
    public function create($data) {
        $sql = "INSERT INTO setting_bidang (namabidang, pengelola_akun, status) 
                VALUES (?, ?, ?)";
        
        $params = [
            $data['namabidang'],
            isset($data['pengelola_akun']) ? ($data['pengelola_akun'] ? 1 : 0) : 0,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        $id = $this->db->lastInsertId();
        
        // Invalidate cache
        $this->invalidateCache();
        
        return $id;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['namabidang', 'pengelola_akun', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'pengelola_akun') {
                    $fields[] = "{$field} = ?";
                    $params[] = (int)$data[$field];
                } else {
                    $fields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE setting_bidang SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $result = $this->db->query($sql, $params);
        
        // Invalidate cache
        $this->invalidateCache();
        Cache::delete("setting_bidang_{$id}");
        
        return $result;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM setting_bidang WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        
        // Invalidate cache
        $this->invalidateCache();
        Cache::delete("setting_bidang_{$id}");
        
        return $result;
    }
    
    private function invalidateCache() {
        $cacheDir = __DIR__ . '/../../cache/';
        if (!is_dir($cacheDir)) {
            return;
        }
        
        $files = glob($cacheDir . '*.cache');
        if (empty($files)) {
            return;
        }
        
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            
            $data = @unserialize(file_get_contents($file));
            if (!is_array($data) || !isset($data['key'])) {
                continue;
            }
            
            $key = $data['key'];
            if (strpos($key, 'setting_bidang_') === 0) {
                @unlink($file);
            }
        }
    }
}

