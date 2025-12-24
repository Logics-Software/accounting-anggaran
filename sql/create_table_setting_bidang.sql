CREATE TABLE IF NOT EXISTS `setting_bidang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namabidang` varchar(255) NOT NULL,
  `pengelola_akun` BOOLEAN NOT NULL DEFAULT FALSE,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

