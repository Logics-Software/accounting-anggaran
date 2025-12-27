<?php
namespace Models\Master;

use Database;

class SettingJurnal {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get setting jurnal data (only one record)
     * @return array|null
     */
    public function get() {
        $sql = "SELECT * FROM setting_jurnal LIMIT 1";
        return $this->db->fetchOne($sql);
    }

    /**
     * Check if setting jurnal exists
     * @return bool
     */
    public function exists() {
        $sql = "SELECT COUNT(*) as count FROM setting_jurnal";
        $result = $this->db->fetchOne($sql);
        return ($result && $result['count'] > 0);
    }

    /**
     * Create setting jurnal
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $sql = "INSERT INTO setting_jurnal (
                    inisial_jurnal_umum, 
                    inisial_jurnal_penyesuaian, 
                    inisial_jurnal_neraca,
                    bulan_jurnal_umum,
                    bulan_jurnal_penyesuaian,
                    bulan_jurnal_neraca,
                    tahun_jurnal_umum,
                    tahun_jurnal_penyesuaian,
                    tahun_jurnal_neraca,
                    jurnal_approval,
                    created_at, 
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['inisial_jurnal_umum'] ?? null,
            $data['inisial_jurnal_penyesuaian'] ?? null,
            $data['inisial_jurnal_neraca'] ?? null,
            !empty($data['bulan_jurnal_umum']) ? 1 : 0,
            !empty($data['bulan_jurnal_penyesuaian']) ? 1 : 0,
            !empty($data['bulan_jurnal_neraca']) ? 1 : 0,
            !empty($data['tahun_jurnal_umum']) ? 1 : 0,
            !empty($data['tahun_jurnal_penyesuaian']) ? 1 : 0,
            !empty($data['tahun_jurnal_neraca']) ? 1 : 0,
            !empty($data['jurnal_approval']) ? 1 : 0
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Update setting jurnal (only one record exists)
     * @param array $data
     * @return bool
     */
    public function update($data) {
        // Get existing record first
        $existing = $this->get();
        if (!$existing) {
            // If no record exists, create new one
            return $this->create($data);
        }

        $sql = "UPDATE setting_jurnal SET 
                inisial_jurnal_umum = ?,
                inisial_jurnal_penyesuaian = ?,
                inisial_jurnal_neraca = ?,
                bulan_jurnal_umum = ?,
                bulan_jurnal_penyesuaian = ?,
                bulan_jurnal_neraca = ?,
                tahun_jurnal_umum = ?,
                tahun_jurnal_penyesuaian = ?,
                tahun_jurnal_neraca = ?,
                jurnal_approval = ?,
                updated_at = NOW()
                WHERE id = ?";

        $params = [
            $data['inisial_jurnal_umum'] ?? $existing['inisial_jurnal_umum'],
            $data['inisial_jurnal_penyesuaian'] ?? $existing['inisial_jurnal_penyesuaian'],
            $data['inisial_jurnal_neraca'] ?? $existing['inisial_jurnal_neraca'],
            !empty($data['bulan_jurnal_umum']) ? 1 : 0,
            !empty($data['bulan_jurnal_penyesuaian']) ? 1 : 0,
            !empty($data['bulan_jurnal_neraca']) ? 1 : 0,
            !empty($data['tahun_jurnal_umum']) ? 1 : 0,
            !empty($data['tahun_jurnal_penyesuaian']) ? 1 : 0,
            !empty($data['tahun_jurnal_neraca']) ? 1 : 0,
            !empty($data['jurnal_approval']) ? 1 : 0,
            $existing['id']
        ];

        $this->db->query($sql, $params);
        return true;
    }
}

