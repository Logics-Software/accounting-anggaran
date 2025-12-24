-- Create table konfigurasi
CREATE TABLE IF NOT EXISTS `konfigurasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namaorganisasi` varchar(255) NOT NULL,
  `alamatorganisasi` text NOT NULL,
  `notelporganisasi` varchar(20) NOT NULL,
  `namaapp` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

