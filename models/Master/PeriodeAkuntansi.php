<?php
namespace Models\Master;

use Database;

class PeriodeAkuntansi {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all periode akuntansi
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'periode', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (pa.periode LIKE ? OR pa.tahun LIKE ? OR u.username LIKE ? OR u.namalengkap LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        $validSortColumns = ['id', 'periode', 'bulan', 'tahun', 'tanggalproses', 'aktif'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'periode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT pa.*, u.username, u.namalengkap 
                FROM periode_akuntansi pa 
                LEFT JOIN users u ON pa.id_user = u.id 
                WHERE {$where} 
                ORDER BY pa.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total periode akuntansi
     */
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (pa.periode LIKE ? OR pa.tahun LIKE ? OR u.username LIKE ? OR u.namalengkap LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM periode_akuntansi pa 
                LEFT JOIN users u ON pa.id_user = u.id 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Find periode akuntansi by ID
     */
    public function findById($id) {
        $sql = "SELECT pa.*, u.username, u.namalengkap 
                FROM periode_akuntansi pa 
                LEFT JOIN users u ON pa.id_user = u.id 
                WHERE pa.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find periode akuntansi by periode (YYYYMM)
     */
    public function findByPeriode($periode) {
        $sql = "SELECT * FROM periode_akuntansi WHERE periode = ?";
        return $this->db->fetchOne($sql, [$periode]);
    }
    
    /**
     * Get active periode (only 1 should be active)
     */
    public function getActive() {
        $sql = "SELECT * FROM periode_akuntansi WHERE aktif = 1 LIMIT 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Check if periode akuntansi exists (has any data)
     */
    public function exists() {
        $sql = "SELECT COUNT(*) as count FROM periode_akuntansi";
        $result = $this->db->fetchOne($sql);
        return ($result && $result['count'] > 0);
    }
    
    /**
     * Create new periode akuntansi
     */
    public function create($data) {
        // If setting as active, deactivate all others first
        if (!empty($data['aktif']) && $data['aktif']) {
            $this->deactivateAll();
        }
        
        $sql = "INSERT INTO periode_akuntansi (
                    periode, bulan, tahun, tanggalproses, aktif, id_user
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['periode'],
            $data['bulan'],
            $data['tahun'],
            $data['tanggalproses'],
            !empty($data['aktif']) ? 1 : 0,
            !empty($data['id_user']) ? (int)$data['id_user'] : null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update periode akuntansi
     */
    public function update($id, $data) {
        // If setting as active, deactivate all others first
        if (!empty($data['aktif']) && $data['aktif']) {
            $this->deactivateAll($id); // Exclude current ID
        }
        
        $fields = [];
        $params = [];
        
        if (isset($data['periode'])) {
            $fields[] = "periode = ?";
            $params[] = $data['periode'];
        }
        if (isset($data['bulan'])) {
            $fields[] = "bulan = ?";
            $params[] = (int)$data['bulan'];
        }
        if (isset($data['tahun'])) {
            $fields[] = "tahun = ?";
            $params[] = (int)$data['tahun'];
        }
        if (isset($data['tanggalproses'])) {
            $fields[] = "tanggalproses = ?";
            $params[] = $data['tanggalproses'];
        }
        if (isset($data['aktif'])) {
            $fields[] = "aktif = ?";
            $params[] = !empty($data['aktif']) ? 1 : 0;
        }
        if (isset($data['id_user'])) {
            $fields[] = "id_user = ?";
            $params[] = !empty($data['id_user']) ? (int)$data['id_user'] : null;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE periode_akuntansi SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    /**
     * Delete periode akuntansi
     */
    public function delete($id) {
        $sql = "DELETE FROM periode_akuntansi WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Deactivate all periode (except optional exclude ID)
     */
    private function deactivateAll($excludeId = null) {
        if ($excludeId) {
            $sql = "UPDATE periode_akuntansi SET aktif = 0 WHERE id != ?";
            $this->db->query($sql, [$excludeId]);
        } else {
            $sql = "UPDATE periode_akuntansi SET aktif = 0";
            $this->db->query($sql);
        }
    }
    
    /**
     * Get all for dropdown
     */
    public function getAllForDropdown() {
        $sql = "SELECT id, periode, bulan, tahun, aktif 
                FROM periode_akuntansi 
                ORDER BY periode DESC";
        return $this->db->fetchAll($sql);
    }
}

