CREATE TABLE IF NOT EXISTS `detail_jurnal` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_jurnal` bigint(20) NOT NULL COMMENT 'ID dari header_jurnal',
  `periode` varchar(6) NOT NULL COMMENT 'YYYYMM (Tahun Bulan)',
  `nojurnal` varchar(20) NOT NULL,
  `id_perkiraan` int(11) NOT NULL COMMENT 'ID perkiraan di master_akun',
  `uraian` varchar(254) DEFAULT NULL,
  `debet` decimal(18,2) NOT NULL DEFAULT 0.00,
  `kredit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `nourut` int(11) NOT NULL COMMENT 'Nomor urutan detail perkiraan jurnal saat input',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_id_jurnal` (`id_jurnal`),
  KEY `idx_periode` (`periode`),
  KEY `idx_nojurnal` (`nojurnal`),
  KEY `idx_id_perkiraan` (`id_perkiraan`),
  KEY `idx_nourut` (`id_jurnal`, `nourut`),
  FOREIGN KEY (`id_jurnal`) REFERENCES `header_jurnal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_perkiraan`) REFERENCES `master_akun` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

