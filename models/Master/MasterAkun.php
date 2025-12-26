<?php
namespace Models\Master;

use Database;

class MasterAkun {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all master akun with pagination, search, and filters
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC', $filterKelompok = null, $filterLevel = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ma.nomor_akun LIKE ? OR ma.nama_akun LIKE ? OR ma.namalevel1 LIKE ? OR ma.namalevel2 LIKE ? OR ma.namalevel3 LIKE ? OR ma.namalevel4 LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterKelompok)) {
            $where .= " AND ma.kelompok_akun = ?";
            $params[] = $filterKelompok;
        }
        
        if (!empty($filterLevel)) {
            $where .= " AND ma.level_akun = ?";
            $params[] = (int)$filterLevel;
        }
        
        $validSortColumns = ['id', 'nomor_akun', 'nama_akun', 'kelompok_akun', 'level_akun', 'saldo_normal'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT ma.*, u.username, u.namalengkap 
                FROM master_akun ma 
                LEFT JOIN users u ON ma.id_user = u.id 
                WHERE {$where} 
                ORDER BY ma.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total master akun
     */
    public function count($search = '', $filterKelompok = null, $filterLevel = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ma.nomor_akun LIKE ? OR ma.nama_akun LIKE ? OR ma.namalevel1 LIKE ? OR ma.namalevel2 LIKE ? OR ma.namalevel3 LIKE ? OR ma.namalevel4 LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterKelompok)) {
            $where .= " AND ma.kelompok_akun = ?";
            $params[] = $filterKelompok;
        }
        
        if (!empty($filterLevel)) {
            $where .= " AND ma.level_akun = ?";
            $params[] = (int)$filterLevel;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM master_akun ma 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get all master akun for export (no pagination, with filters)
     */
    public function getAllForExport($search = '', $filterKelompok = null, $filterLevel = null, $sortBy = 'nomor_akun', $sortOrder = 'ASC') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ma.nomor_akun LIKE ? OR ma.nama_akun LIKE ? OR ma.namalevel1 LIKE ? OR ma.namalevel2 LIKE ? OR ma.namalevel3 LIKE ? OR ma.namalevel4 LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterKelompok)) {
            $where .= " AND ma.kelompok_akun = ?";
            $params[] = $filterKelompok;
        }
        
        if (!empty($filterLevel)) {
            $where .= " AND ma.level_akun = ?";
            $params[] = (int)$filterLevel;
        }
        
        $validSortColumns = ['id', 'nomor_akun', 'nama_akun', 'kelompok_akun', 'level_akun', 'saldo_normal'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'nomor_akun';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT ma.*, u.username, u.namalengkap 
                FROM master_akun ma 
                LEFT JOIN users u ON ma.id_user = u.id 
                WHERE {$where} 
                ORDER BY ma.{$sortBy} {$sortOrder}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Find master akun by ID
     */
    public function findById($id) {
        $sql = "SELECT ma.*, u.username, u.namalengkap 
                FROM master_akun ma 
                LEFT JOIN users u ON ma.id_user = u.id 
                WHERE ma.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find master akun by nomor_akun
     */
    public function findByNomorAkun($nomorAkun) {
        $sql = "SELECT * FROM master_akun WHERE nomor_akun = ?";
        return $this->db->fetchOne($sql, [$nomorAkun]);
    }
    
    /**
     * Get all master akun by level
     */
    public function getByLevel($level) {
        $sql = "SELECT * FROM master_akun WHERE level_akun = ? ORDER BY nomor_akun ASC";
        return $this->db->fetchAll($sql, [$level]);
    }
    
    /**
     * Get all master akun by parent (id_induk)
     */
    public function getByParent($idInduk) {
        $sql = "SELECT * FROM master_akun WHERE id_induk = ? ORDER BY nomor_akun ASC";
        return $this->db->fetchAll($sql, [$idInduk]);
    }
    
    /**
     * Get all master akun for dropdown (parent selection)
     */
    public function getAllForDropdown($excludeId = null, $maxLevel = null) {
        $where = "1=1";
        $params = [];
        
        if ($excludeId) {
            $where .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        // Filter by max level (for parent selection: only show accounts with level < current level)
        if ($maxLevel !== null) {
            $where .= " AND level_akun < ?";
            $params[] = (int)$maxLevel;
        }
        
        $sql = "SELECT id, nomor_akun, nama_akun, level_akun, 
                       level1, level2, level3, level4,
                       namalevel1, namalevel2, namalevel3, namalevel4,
                       id_induk
                FROM master_akun 
                WHERE {$where} 
                ORDER BY nomor_akun ASC";
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Calculate saldo_normal based on kelompok_akun
     */
    public function calculateSaldoNormal($kelompokAkun) {
        if (in_array($kelompokAkun, ['AKTIVA', 'BEBAN'])) {
            return 'DEBET';
        }
        return 'KREDIT';
    }
    
    /**
     * Create new master akun
     */
    public function create($data) {
        // Calculate saldo_normal if not provided
        if (!isset($data['saldo_normal']) || empty($data['saldo_normal'])) {
            $data['saldo_normal'] = $this->calculateSaldoNormal($data['kelompok_akun']);
        }
        
        $sql = "INSERT INTO master_akun (
                    nomor_akun, nama_akun, kelompok_akun, detail_akun, level_akun, 
                    saldo_normal, level1, level2, level3, level4, 
                    namalevel1, namalevel2, namalevel3, namalevel4, 
                    id_induk, id_user
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nomor_akun'],
            $data['nama_akun'],
            $data['kelompok_akun'],
            $data['detail_akun'] ?? null,
            $data['level_akun'] ?? 1,
            $data['saldo_normal'],
            !empty($data['level1']) ? (int)$data['level1'] : null,
            !empty($data['level2']) ? (int)$data['level2'] : null,
            !empty($data['level3']) ? (int)$data['level3'] : null,
            !empty($data['level4']) ? (int)$data['level4'] : null,
            $data['namalevel1'] ?? null,
            $data['namalevel2'] ?? null,
            $data['namalevel3'] ?? null,
            $data['namalevel4'] ?? null,
            !empty($data['id_induk']) ? (int)$data['id_induk'] : null,
            !empty($data['id_user']) ? (int)$data['id_user'] : null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update master akun
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = [
            'nomor_akun', 'nama_akun', 'kelompok_akun', 'detail_akun', 'level_akun',
            'saldo_normal', 'level1', 'level2', 'level3', 'level4',
            'namalevel1', 'namalevel2', 'namalevel3', 'namalevel4',
            'id_induk', 'id_user'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'saldo_normal' && empty($data[$field])) {
                    // Recalculate saldo_normal if kelompok_akun is being updated
                    if (isset($data['kelompok_akun'])) {
                        $data[$field] = $this->calculateSaldoNormal($data['kelompok_akun']);
                    }
                }
                
                if (in_array($field, ['level1', 'level2', 'level3', 'level4', 'id_induk', 'id_user', 'level_akun'])) {
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
        $sql = "UPDATE master_akun SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete master akun
     */
    public function delete($id) {
        // Check if has children
        $children = $this->getByParent($id);
        if (!empty($children)) {
            throw new \Exception('Tidak dapat menghapus akun yang memiliki sub-akun');
        }
        
        $sql = "DELETE FROM master_akun WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

