CREATE TABLE IF NOT EXISTS `setting_bagian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namabagian` varchar(255) NOT NULL,
  `pengelola_akun` BOOLEAN NOT NULL DEFAULT FALSE,
  `jabatan_pimpinan` varchar(255) DEFAULT NULL,
  `id_pimpinan` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_id_pimpinan` (`id_pimpinan`),
  CONSTRAINT `fk_setting_bagian_pimpinan` FOREIGN KEY (`id_pimpinan`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

