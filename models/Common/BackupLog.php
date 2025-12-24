<?php
namespace Models\Common;

use Database;

class BackupLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create backup log entry
     */
    public function create($data) {
        $sql = "INSERT INTO backup_log (tanggal_backup, ip_address, user_agent, user_id, folder_backup) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['tanggal_backup'] ?? date('Y-m-d H:i:s'),
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
            !empty($data['user_id']) ? (int)$data['user_id'] : null,
            $data['folder_backup'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get all backup logs
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'tanggal_backup', $sortOrder = 'DESC', $dateFrom = '', $dateTo = '') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (bl.ip_address LIKE ? OR bl.folder_backup LIKE ? OR u.namalengkap LIKE ? OR u.username LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        if (!empty($dateFrom)) {
            $where .= " AND DATE(bl.tanggal_backup) >= ?";
            $params[] = $dateFrom;
        }
        
        if (!empty($dateTo)) {
            $where .= " AND DATE(bl.tanggal_backup) <= ?";
            $params[] = $dateTo;
        }
        
        $validSortColumns = ['id', 'tanggal_backup', 'ip_address', 'user_id', 'folder_backup'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'tanggal_backup';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT bl.*, u.username, u.namalengkap 
                FROM backup_log bl 
                LEFT JOIN users u ON bl.user_id = u.id 
                WHERE {$where} 
                ORDER BY bl.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count backup logs
     */
    public function count($search = '', $dateFrom = '', $dateTo = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (bl.ip_address LIKE ? OR bl.folder_backup LIKE ? OR u.namalengkap LIKE ? OR u.username LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        if (!empty($dateFrom)) {
            $where .= " AND DATE(bl.tanggal_backup) >= ?";
            $params[] = $dateFrom;
        }
        
        if (!empty($dateTo)) {
            $where .= " AND DATE(bl.tanggal_backup) <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM backup_log bl 
                LEFT JOIN users u ON bl.user_id = u.id 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get backup log by ID
     */
    public function findById($id) {
        $sql = "SELECT bl.*, u.username, u.namalengkap 
                FROM backup_log bl 
                LEFT JOIN users u ON bl.user_id = u.id 
                WHERE bl.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Delete backup log
     */
    public function delete($id) {
        $sql = "DELETE FROM backup_log WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

