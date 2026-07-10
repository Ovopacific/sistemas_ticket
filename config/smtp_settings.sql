CREATE TABLE IF NOT EXISTS `smtp_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mail_host` VARCHAR(255) NOT NULL,
  `mail_port` INT NOT NULL DEFAULT 587,
  `mail_username` VARCHAR(255) NOT NULL,
  `mail_password` TEXT NOT NULL,
  `mail_encryption` ENUM('none', 'ssl', 'tls') NOT NULL DEFAULT 'tls',
  `mail_from` VARCHAR(255) NOT NULL,
  `mail_from_name` VARCHAR(255) NOT NULL,
  `reply_to` VARCHAR(255) DEFAULT NULL,
  `bcc` VARCHAR(255) DEFAULT NULL,
  `timeout` INT NOT NULL DEFAULT 30,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'inactive',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `smtp_settings` (id, mail_host, mail_port, mail_username, mail_password, mail_encryption, mail_from, mail_from_name, status)
VALUES (1, 'smtp.example.com', 587, 'user@example.com', '', 'tls', 'noreply@example.com', 'Mesa de Soporte', 'inactive')
ON DUPLICATE KEY UPDATE id=id;
