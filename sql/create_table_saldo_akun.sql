CREATE TABLE IF NOT EXISTS saldo_akun (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_akun INT NOT NULL COMMENT 'ID dari master_akun',
    periode VARCHAR(6) NOT NULL COMMENT 'Format: YYYYMM (tahun dan bulan)',
    saldoawal DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo awal periode',
    debet DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Total mutasi debet',
    kredit DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Total mutasi kredit',
    saldoakhir DECIMAL(18,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo akhir periode',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_akun) REFERENCES master_akun(id) ON DELETE CASCADE,
    UNIQUE KEY uk_akun_periode (id_akun, periode),
    INDEX idx_periode (periode),
    INDEX idx_id_akun (id_akun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

