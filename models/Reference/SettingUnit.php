<?php
namespace Models\Reference;

use Database;
use Cache;

class SettingUnit {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT su.*, sb.namabagian, u.namalengkap as nama_pimpinan, u.username as username_pimpinan 
                FROM setting_unit su 
                LEFT JOIN setting_bagian sb ON su.id_bagian = sb.id 
                LEFT JOIN users u ON su.id_pimpinan = u.id 
                WHERE su.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC', $filterBagian = '') {
        // Generate cache key
        $cacheKey = "setting_unit_all_{$page}_{$perPage}_" . md5($search) . "_" . md5($filterBagian) . "_{$sortBy}_{$sortOrder}";
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (su.namaunit LIKE ? OR su.jabatan_pimpinan LIKE ? OR sb.namabagian LIKE ? OR u.namalengkap LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        if (!empty($filterBagian)) {
            $where .= " AND su.id_bagian = ?";
            $params[] = (int)$filterBagian;
        }
        
        $validSortColumns = ['id', 'namaunit', 'jabatan_pimpinan', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT su.*, sb.namabagian, u.namalengkap as nama_pimpinan, u.username as username_pimpinan 
                FROM setting_unit su 
                LEFT JOIN setting_bagian sb ON su.id_bagian = sb.id 
                LEFT JOIN users u ON su.id_pimpinan = u.id 
                WHERE {$where} ORDER BY su.{$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $result = $this->db->fetchAll($sql, $params);
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $result, 300);
        
        return $result;
    }
    
    public function count($search = '', $filterBagian = '') {
        // Generate cache key
        $cacheKey = "setting_unit_count_" . md5($search) . "_" . md5($filterBagian);
        
        // Try cache first (5 minutes TTL)
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (su.namaunit LIKE ? OR su.jabatan_pimpinan LIKE ? OR sb.namabagian LIKE ? OR u.namalengkap LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        if (!empty($filterBagian)) {
            $where .= " AND su.id_bagian = ?";
            $params[] = (int)$filterBagian;
        }
        
        $sql = "SELECT COUNT(*) as total FROM setting_unit su 
                LEFT JOIN setting_bagian sb ON su.id_bagian = sb.id 
                LEFT JOIN users u ON su.id_pimpinan = u.id 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        $total = $result['total'] ?? 0;
        
        // Cache for 5 minutes (300 seconds)
        Cache::set($cacheKey, $total, 300);
        
        return $total;
    }
    
    public function create($data) {
        $sql = "INSERT INTO setting_unit (namaunit, id_bagian, jabatan_pimpinan, id_pimpinan, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['namaunit'],
            !empty($data['id_bagian']) ? (int)$data['id_bagian'] : null,
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
        
        $allowedFields = ['namaunit', 'id_bagian', 'jabatan_pimpinan', 'id_pimpinan', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['id_bagian', 'id_pimpinan'])) {
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
        $sql = "UPDATE setting_unit SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $result = $this->db->query($sql, $params);
        
        // Invalidate cache
        $this->invalidateCache();
        Cache::delete("setting_unit_{$id}");
        
        return $result;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM setting_unit WHERE id = ?";
        $result = $this->db->query($sql, [$id]);
        
        // Invalidate cache
        $this->invalidateCache();
        Cache::delete("setting_unit_{$id}");
        
        return $result;
    }
    
    public function getByBagian($idBagian) {
        $sql = "SELECT * FROM setting_unit WHERE id_bagian = ? AND status = 'aktif' ORDER BY namaunit";
        return $this->db->fetchAll($sql, [$idBagian]);
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
            if (strpos($key, 'setting_unit_') === 0) {
                @unlink($file);
            }
        }
    }
}

