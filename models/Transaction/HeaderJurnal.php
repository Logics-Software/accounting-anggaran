<?php
namespace Models\Transaction;

use Database;

class HeaderJurnal {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all header jurnal with pagination, search, and filters
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $filterTipeJurnal = null, $filterPeriode = null, $sortBy = 'id', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (
                hj.nojurnal LIKE ? OR 
                hj.noreferensi LIKE ? OR 
                hj.keterangan LIKE ?
            )";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterTipeJurnal)) {
            $where .= " AND hj.tipejurnal = ?";
            $params[] = $filterTipeJurnal;
        }
        
        if (!empty($filterPeriode)) {
            $where .= " AND hj.periode = ?";
            $params[] = $filterPeriode;
        }
        
        $validSortColumns = ['id', 'periode', 'tipejurnal', 'nojurnal', 'tanggaljurnal', 'totaldebet', 'totalkredit', 'approvement'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT hj.*, u.username, u.namalengkap 
                FROM header_jurnal hj
                LEFT JOIN users u ON hj.id_user = u.id
                WHERE {$where}
                ORDER BY hj.{$sortBy} {$sortOrder}
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total header jurnal
     */
    public function count($search = '', $filterTipeJurnal = null, $filterPeriode = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (
                hj.nojurnal LIKE ? OR 
                hj.noreferensi LIKE ? OR 
                hj.keterangan LIKE ?
            )";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filterTipeJurnal)) {
            $where .= " AND hj.tipejurnal = ?";
            $params[] = $filterTipeJurnal;
        }
        
        if (!empty($filterPeriode)) {
            $where .= " AND hj.periode = ?";
            $params[] = $filterPeriode;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM header_jurnal hj
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Find header jurnal by ID
     */
    public function findById($id) {
        $sql = "SELECT hj.*, u.username, u.namalengkap 
                FROM header_jurnal hj
                LEFT JOIN users u ON hj.id_user = u.id
                WHERE hj.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find header jurnal by nojurnal and periode
     */
    public function findByNojurnalPeriode($nojurnal, $periode) {
        $sql = "SELECT * FROM header_jurnal WHERE nojurnal = ? AND periode = ?";
        return $this->db->fetchOne($sql, [$nojurnal, $periode]);
    }
    
    /**
     * Get last counter number for a given prefix pattern
     * Pattern format: {inisial}{bulan?}{tahun?}
     */
    public function getLastCounter($pattern, $periode) {
        // Escape special regex characters in pattern
        $patternEscaped = preg_quote($pattern, '/');
        // Match pattern followed by digits (counter)
        $patternRegex = '^' . $patternEscaped . '(\d+)$';
        
        $sql = "SELECT nojurnal FROM header_jurnal 
                WHERE periode = ? AND nojurnal REGEXP ? 
                ORDER BY CAST(SUBSTRING(nojurnal, LENGTH(?) + 1) AS UNSIGNED) DESC 
                LIMIT 1";
        
        $result = $this->db->fetchOne($sql, [$periode, $patternRegex, $pattern]);
        
        if ($result && isset($result['nojurnal'])) {
            // Extract counter number from the end
            $counterStr = substr($result['nojurnal'], strlen($pattern));
            return (int)$counterStr;
        }
        
        return 0;
    }
    
    /**
     * Create new header jurnal
     */
    public function create($data) {
        $sql = "INSERT INTO header_jurnal (
                    periode, tipejurnal, nojurnal, tanggaljurnal, noreferensi, 
                    keterangan, posting, nomor_posting, totaldebet, totalkredit, 
                    approvement, id_user
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['periode'],
            $data['tipejurnal'] ?? 'UMUM',
            $data['nojurnal'],
            $data['tanggaljurnal'],
            $data['noreferensi'] ?? null,
            $data['keterangan'] ?? null,
            $data['posting'] ?? 'NON POSTING',
            $data['nomor_posting'] ?? null,
            isset($data['totaldebet']) ? (float)$data['totaldebet'] : 0.00,
            isset($data['totalkredit']) ? (float)$data['totalkredit'] : 0.00,
            $data['approvement'] ?? 'APPROVAL',
            $data['id_user'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update header jurnal
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['periode'])) {
            $fields[] = "periode = ?";
            $params[] = $data['periode'];
        }
        if (isset($data['tipejurnal'])) {
            $fields[] = "tipejurnal = ?";
            $params[] = $data['tipejurnal'];
        }
        if (isset($data['nojurnal'])) {
            $fields[] = "nojurnal = ?";
            $params[] = $data['nojurnal'];
        }
        if (isset($data['tanggaljurnal'])) {
            $fields[] = "tanggaljurnal = ?";
            $params[] = $data['tanggaljurnal'];
        }
        if (isset($data['noreferensi'])) {
            $fields[] = "noreferensi = ?";
            $params[] = $data['noreferensi'];
        }
        if (isset($data['keterangan'])) {
            $fields[] = "keterangan = ?";
            $params[] = $data['keterangan'];
        }
        if (isset($data['posting'])) {
            $fields[] = "posting = ?";
            $params[] = $data['posting'];
        }
        if (isset($data['nomor_posting'])) {
            $fields[] = "nomor_posting = ?";
            $params[] = $data['nomor_posting'];
        }
        if (isset($data['totaldebet'])) {
            $fields[] = "totaldebet = ?";
            $params[] = (float)$data['totaldebet'];
        }
        if (isset($data['totalkredit'])) {
            $fields[] = "totalkredit = ?";
            $params[] = (float)$data['totalkredit'];
        }
        if (isset($data['approvement'])) {
            $fields[] = "approvement = ?";
            $params[] = $data['approvement'];
        }
        if (isset($data['id_user'])) {
            $fields[] = "id_user = ?";
            $params[] = $data['id_user'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE header_jurnal SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        return true;
    }
    
    /**
     * Delete header jurnal (cascade to detail_jurnal)
     */
    public function delete($id) {
        $sql = "DELETE FROM header_jurnal WHERE id = ?";
        $this->db->query($sql, [$id]);
        return true;
    }
    
    /**
     * Update total debet and kredit from detail
     */
    public function updateTotal($id) {
        $sql = "UPDATE header_jurnal hj
                SET hj.totaldebet = (
                    SELECT COALESCE(SUM(debet), 0) 
                    FROM detail_jurnal 
                    WHERE id_jurnal = ?
                ),
                hj.totalkredit = (
                    SELECT COALESCE(SUM(kredit), 0) 
                    FROM detail_jurnal 
                    WHERE id_jurnal = ?
                )
                WHERE hj.id = ?";
        $this->db->query($sql, [$id, $id, $id]);
        return true;
    }
}

