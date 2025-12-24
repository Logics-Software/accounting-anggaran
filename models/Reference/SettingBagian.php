<?php
namespace Models\Reference;

use Database;
use Cache;

class SettingBagian {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT sb.*, u.namalengkap as nama_pimpinan, u.username as username_pimpinan 
                FROM setting_bagian sb 
                LEFT JOIN users u ON sb.id_pimpinan = u.id 
                WHERE sb.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        // Generate cache key
        $cacheKey = "setting_bagian_all_{$page}_{$perPage}_" . md5($search) . "_{$sortBy}_{$sortOrder}";
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (sb.namabagian LIKE ? OR sb.jabatan_pimpinan LIKE ? OR u.namalengkap LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'namabagian', 'pengelola_akun', 'jabatan_pimpinan', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT sb.*, u.namalengkap as nama_pimpinan, u.username as username_pimpinan 
                FROM setting_bagian sb 
                LEFT JOIN users u ON sb.id_pimpinan = u.id 
                WHERE {$where} ORDER BY sb.{$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $result = $this->db->fetchAll($sql, $params);
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $result, 300);
        
        return $result;
    }
    
    public function count($search = '') {
        // Generate cache key
        $cacheKey = "setting_bagian_count_" . md5($search);
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (sb.namabagian LIKE ? OR sb.jabatan_pimpinan LIKE ? OR u.namalengkap LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM setting_bagian sb 
                LEFT JOIN users u ON sb.id_pimpinan = u.id 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        $total = $result['total'] ?? 0;
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $total, 300);
        
        return $total;
    }
    
    public function create($data) {
        $sql = "INSERT INTO setting_bagian (namabagian, pengelola_akun, jabatan_pimpinan, id_pimpinan, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['namabagian'],
            isset($data['pengelola_akun']) ? ($data['pengelola_akun'] ? 1 : 0) : 0,
            $data['jabatan_pimpinan'] ?? null,
            !empty($data['id_pimpinan']) ? (int)$data['id_pimpinan'] : null,
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
        
        $allowedFields = ['namabagian', 'pengelola_akun', 'jabatan_pimpinan', 'id_pimpinan', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'pengelola_akun') {
                    $fields[] = "{$field} = ?";
                    // Convert boolean to int (true = 1, false = 0) for MySQL BOOLEAN
                    $params[] = $data[$field] ? 1 : 0;
                } elseif ($field === 'id_pimpinan') {
                    $fields[] = "{$field} = ?";
                    $params[] = !empty($data[$field]) ? (int)$data[$field] : null;
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
        $sql = "UPDATE setting_bagian SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $result = $this->db->query($sql, $params);
        
        // Invalidate cache
        $this->invalidateCache();
        Cache::delete("setting_bagian_{$id}");
        
        return $result;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM setting_bagian WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        
        // Invalidate cache
        $this->invalidateCache();
        Cache::delete("setting_bagian_{$id}");
        
        return $result;
    }
    
    public function getAllActive() {
        $cacheKey = "setting_bagian_all_active";
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $sql = "SELECT id, namabagian FROM setting_bagian WHERE status = 'aktif' ORDER BY namabagian ASC";
        $result = $this->db->fetchAll($sql, []);
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $result, 300);
        
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
            if (strpos($key, 'setting_bagian_') === 0) {
                @unlink($file);
            }
        }
    }
}

