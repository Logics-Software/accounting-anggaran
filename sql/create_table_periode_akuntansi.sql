CREATE TABLE IF NOT EXISTS periode_akuntansi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periode VARCHAR(6) NOT NULL UNIQUE COMMENT 'Format: YYYYMM (tahun dan bulan)',
    bulan INT NOT NULL COMMENT 'Bulan (1-12)',
    tahun INT NOT NULL COMMENT 'Tahun',
    tanggalproses DATE NOT NULL COMMENT 'Tanggal proses periode',
    aktif BOOLEAN NOT NULL DEFAULT 0 COMMENT '0=non aktif, 1=aktif (hanya 1 yang aktif)',
    id_user INT NULL COMMENT 'User yang melakukan proses',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_periode (periode),
    INDEX idx_aktif (aktif),
    INDEX idx_tahun_bulan (tahun, bulan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

