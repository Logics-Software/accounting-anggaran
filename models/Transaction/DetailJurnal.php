<?php
namespace Models\Transaction;

use Database;

class DetailJurnal {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all detail jurnal by id_jurnal
     */
    public function getByJurnal($idJurnal, $sortBy = 'nourut', $sortOrder = 'ASC') {
        $validSortColumns = ['id', 'nourut', 'debet', 'kredit'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'nourut';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT dj.*, ma.nomor_akun, ma.nama_akun, ma.kelompok_akun, ma.detail_akun
                FROM detail_jurnal dj
                INNER JOIN master_akun ma ON dj.id_perkiraan = ma.id
                WHERE dj.id_jurnal = ?
                ORDER BY dj.{$sortBy} {$sortOrder}";
        return $this->db->fetchAll($sql, [$idJurnal]);
    }
    
    /**
     * Find detail jurnal by ID
     */
    public function findById($id) {
        $sql = "SELECT dj.*, ma.nomor_akun, ma.nama_akun, ma.kelompok_akun, ma.detail_akun
                FROM detail_jurnal dj
                INNER JOIN master_akun ma ON dj.id_perkiraan = ma.id
                WHERE dj.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Create new detail jurnal
     */
    public function create($data) {
        $sql = "INSERT INTO detail_jurnal (
                    id_jurnal, periode, nojurnal, id_perkiraan, uraian, 
                    debet, kredit, nourut
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            (int)$data['id_jurnal'],
            $data['periode'],
            $data['nojurnal'],
            (int)$data['id_perkiraan'],
            $data['uraian'] ?? null,
            isset($data['debet']) ? (float)$data['debet'] : 0.00,
            isset($data['kredit']) ? (float)$data['kredit'] : 0.00,
            (int)$data['nourut']
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update detail jurnal
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['id_perkiraan'])) {
            $fields[] = "id_perkiraan = ?";
            $params[] = (int)$data['id_perkiraan'];
        }
        if (isset($data['uraian'])) {
            $fields[] = "uraian = ?";
            $params[] = $data['uraian'];
        }
        if (isset($data['debet'])) {
            $fields[] = "debet = ?";
            $params[] = (float)$data['debet'];
        }
        if (isset($data['kredit'])) {
            $fields[] = "kredit = ?";
            $params[] = (float)$data['kredit'];
        }
        if (isset($data['nourut'])) {
            $fields[] = "nourut = ?";
            $params[] = (int)$data['nourut'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE detail_jurnal SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    /**
     * Delete detail jurnal
     */
    public function delete($id) {
        $sql = "DELETE FROM detail_jurnal WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Delete all detail jurnal by id_jurnal
     */
    public function deleteByJurnal($idJurnal) {
        $sql = "DELETE FROM detail_jurnal WHERE id_jurnal = ?";
        $this->db->query($sql, [$idJurnal]);
        return true;
    }
    
    /**
     * Get max nourut for a jurnal
     */
    public function getMaxNourut($idJurnal) {
        $sql = "SELECT MAX(nourut) as max_nourut FROM detail_jurnal WHERE id_jurnal = ?";
        $result = $this->db->fetchOne($sql, [$idJurnal]);
        return $result['max_nourut'] ?? 0;
    }
    
    /**
     * Get total debet and kredit for a jurnal
     */
    public function getTotal($idJurnal) {
        $sql = "SELECT 
                    COALESCE(SUM(debet), 0) as total_debet,
                    COALESCE(SUM(kredit), 0) as total_kredit
                FROM detail_jurnal 
                WHERE id_jurnal = ?";
        return $this->db->fetchOne($sql, [$idJurnal]);
    }
}

