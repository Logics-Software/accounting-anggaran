CREATE TABLE IF NOT EXISTS `setting_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namaunit` varchar(255) NOT NULL,
  `id_bagian` int(11) DEFAULT NULL,
  `jabatan_pimpinan` varchar(255) DEFAULT NULL,
  `id_pimpinan` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_id_bagian` (`id_bagian`),
  KEY `idx_id_pimpinan` (`id_pimpinan`),
  CONSTRAINT `fk_setting_unit_bagian` FOREIGN KEY (`id_bagian`) REFERENCES `setting_bagian` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_setting_unit_pimpinan` FOREIGN KEY (`id_pimpinan`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

