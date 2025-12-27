CREATE TABLE IF NOT EXISTS `setting_jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inisial_jurnal_umum` varchar(5) DEFAULT NULL,
  `inisial_jurnal_penyesuaian` varchar(5) DEFAULT NULL,
  `inisial_jurnal_neraca` varchar(5) DEFAULT NULL,
  `bulan_jurnal_umum` boolean NOT NULL DEFAULT 0,
  `bulan_jurnal_penyesuaian` boolean NOT NULL DEFAULT 0,
  `bulan_jurnal_neraca` boolean NOT NULL DEFAULT 0,
  `tahun_jurnal_umum` boolean NOT NULL DEFAULT 0,
  `tahun_jurnal_penyesuaian` boolean NOT NULL DEFAULT 0,
  `tahun_jurnal_neraca` boolean NOT NULL DEFAULT 0,
  `jurnal_approval` boolean NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

