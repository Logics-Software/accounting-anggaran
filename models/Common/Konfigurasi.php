<?php
namespace Models\Common;

use Database;

class Konfigurasi {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get configuration data (only one record)
     * @return array|null
     */
    public function get() {
        $sql = "SELECT * FROM konfigurasi LIMIT 1";
        return $this->db->fetchOne($sql);
    }

    /**
     * Check if configuration exists
     * @return bool
     */
    public function exists() {
        $sql = "SELECT COUNT(*) as count FROM konfigurasi";
        $result = $this->db->fetchOne($sql);
        return ($result && $result['count'] > 0);
    }

    /**
     * Create configuration
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $sql = "INSERT INTO konfigurasi (namaorganisasi, alamatorganisasi, notelporganisasi, namaapp, logo, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['namaorganisasi'] ?? null,
            $data['alamatorganisasi'] ?? null,
            $data['notelporganisasi'] ?? null,
            $data['namaapp'] ?? null,
            $data['logo'] ?? null
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Update configuration (only one record exists)
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

        $sql = "UPDATE konfigurasi SET 
                namaorganisasi = ?,
                alamatorganisasi = ?,
                notelporganisasi = ?,
                namaapp = ?,
                logo = ?,
                updated_at = NOW()
                WHERE id = ?";

        $params = [
            $data['namaorganisasi'] ?? $existing['namaorganisasi'],
            $data['alamatorganisasi'] ?? $existing['alamatorganisasi'],
            $data['notelporganisasi'] ?? $existing['notelporganisasi'],
            $data['namaapp'] ?? $existing['namaapp'],
            $data['logo'] ?? $existing['logo'],
            $existing['id']
        ];

        $this->db->query($sql, $params);
        return true;
    }
}

