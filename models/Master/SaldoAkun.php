<?php
namespace Models\Master;

use Database;

class SaldoAkun {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all saldo akun
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $filterPeriode = null, $filterAkun = null, $sortBy = 'periode', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ma.nomor_akun LIKE ? OR ma.nama_akun LIKE ? OR sa.periode LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterPeriode)) {
            $where .= " AND sa.periode = ?";
            $params[] = $filterPeriode;
        }
        
        if (!empty($filterAkun)) {
            $where .= " AND sa.id_akun = ?";
            $params[] = (int)$filterAkun;
        }
        
        $validSortColumns = ['id', 'periode', 'saldoawal', 'debet', 'kredit', 'saldoakhir'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'periode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT sa.*, ma.nomor_akun, ma.nama_akun, ma.kelompok_akun, ma.level_akun
                FROM saldo_akun sa 
                INNER JOIN master_akun ma ON sa.id_akun = ma.id 
                WHERE {$where} 
                ORDER BY sa.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total saldo akun
     */
    public function count($search = '', $filterPeriode = null, $filterAkun = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ma.nomor_akun LIKE ? OR ma.nama_akun LIKE ? OR sa.periode LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterPeriode)) {
            $where .= " AND sa.periode = ?";
            $params[] = $filterPeriode;
        }
        
        if (!empty($filterAkun)) {
            $where .= " AND sa.id_akun = ?";
            $params[] = (int)$filterAkun;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM saldo_akun sa 
                INNER JOIN master_akun ma ON sa.id_akun = ma.id 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Find saldo akun by ID
     */
    public function findById($id) {
        $sql = "SELECT sa.*, ma.nomor_akun, ma.nama_akun, ma.kelompok_akun, ma.level_akun
                FROM saldo_akun sa 
                INNER JOIN master_akun ma ON sa.id_akun = ma.id 
                WHERE sa.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find saldo akun by id_akun and periode
     */
    public function findByAkunPeriode($idAkun, $periode) {
        $sql = "SELECT * FROM saldo_akun WHERE id_akun = ? AND periode = ?";
        return $this->db->fetchOne($sql, [$idAkun, $periode]);
    }
    
    /**
     * Get saldo akun by periode
     */
    public function getByPeriode($periode) {
        $sql = "SELECT sa.*, ma.nomor_akun, ma.nama_akun, ma.kelompok_akun, ma.level_akun
                FROM saldo_akun sa 
                INNER JOIN master_akun ma ON sa.id_akun = ma.id 
                WHERE sa.periode = ? 
                ORDER BY ma.nomor_akun ASC";
        return $this->db->fetchAll($sql, [$periode]);
    }
    
    /**
     * Get saldo akun by id_akun
     */
    public function getByAkun($idAkun, $sortBy = 'periode', $sortOrder = 'DESC') {
        $validSortColumns = ['periode', 'saldoawal', 'debet', 'kredit', 'saldoakhir'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'periode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT sa.*, ma.nomor_akun, ma.nama_akun, ma.kelompok_akun, ma.level_akun
                FROM saldo_akun sa 
                INNER JOIN master_akun ma ON sa.id_akun = ma.id 
                WHERE sa.id_akun = ? 
                ORDER BY sa.{$sortBy} {$sortOrder}";
        return $this->db->fetchAll($sql, [$idAkun]);
    }
    
    /**
     * Create new saldo akun
     */
    public function create($data) {
        $sql = "INSERT INTO saldo_akun (
                    id_akun, periode, saldoawal, debet, kredit, saldoakhir
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            (int)$data['id_akun'],
            $data['periode'],
            isset($data['saldoawal']) ? (float)$data['saldoawal'] : 0.00,
            isset($data['debet']) ? (float)$data['debet'] : 0.00,
            isset($data['kredit']) ? (float)$data['kredit'] : 0.00,
            isset($data['saldoakhir']) ? (float)$data['saldoakhir'] : 0.00
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update saldo akun
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['id_akun'])) {
            $fields[] = "id_akun = ?";
            $params[] = (int)$data['id_akun'];
        }
        if (isset($data['periode'])) {
            $fields[] = "periode = ?";
            $params[] = $data['periode'];
        }
        if (isset($data['saldoawal'])) {
            $fields[] = "saldoawal = ?";
            $params[] = (float)$data['saldoawal'];
        }
        if (isset($data['debet'])) {
            $fields[] = "debet = ?";
            $params[] = (float)$data['debet'];
        }
        if (isset($data['kredit'])) {
            $fields[] = "kredit = ?";
            $params[] = (float)$data['kredit'];
        }
        if (isset($data['saldoakhir'])) {
            $fields[] = "saldoakhir = ?";
            $params[] = (float)$data['saldoakhir'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE saldo_akun SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    /**
     * Update or create saldo akun (upsert)
     */
    public function updateOrCreate($idAkun, $periode, $data) {
        $existing = $this->findByAkunPeriode($idAkun, $periode);
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['id_akun'] = $idAkun;
            $data['periode'] = $periode;
            return $this->create($data);
        }
    }
    
    /**
     * Delete saldo akun
     */
    public function delete($id) {
        $sql = "DELETE FROM saldo_akun WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Calculate saldo akhir
     */
    public function calculateSaldoAkhir($saldoAwal, $debet, $kredit, $saldoNormal) {
        // Saldo akhir = saldo awal + (debet - kredit) untuk DEBET normal
        // Saldo akhir = saldo awal + (kredit - debet) untuk KREDIT normal
        if ($saldoNormal === 'DEBET') {
            return $saldoAwal + $debet - $kredit;
        } else {
            return $saldoAwal + $kredit - $debet;
        }
    }
}

