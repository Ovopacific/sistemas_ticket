-- Help Desk MariaDB Database Schema
-- Strict Relations, Foreign Keys, Indexes and Default Seed Data

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `technicians`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `priorities`;
DROP TABLE IF EXISTS `statuses`;
DROP TABLE IF EXISTS `settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Settings Table
CREATE TABLE `settings` (
  `setting_key` VARCHAR(50) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Departments Table
CREATE TABLE `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Priorities Table
CREATE TABLE `priorities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `color_hex` VARCHAR(7) NOT NULL DEFAULT '#6c757d',
  `level` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Statuses Table
CREATE TABLE `statuses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `color_hex` VARCHAR(7) NOT NULL DEFAULT '#6c757d',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `role` ENUM('admin', 'technician', 'user') NOT NULL DEFAULT 'user',
  `department_id` INT DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `avatar_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) 
    REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Technicians Detail Table (Extended profile for techs)
CREATE TABLE `technicians` (
  `user_id` INT PRIMARY KEY,
  `specialty` VARCHAR(150) DEFAULT NULL,
  `status` ENUM('available', 'busy', 'away') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_technicians_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tickets Table
CREATE TABLE `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_number` VARCHAR(20) NOT NULL UNIQUE,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `department_id` INT DEFAULT NULL,
  `category_id` INT DEFAULT NULL,
  `priority_id` INT DEFAULT NULL,
  `status_id` INT DEFAULT NULL,
  `requester_id` INT DEFAULT NULL,
  `assigned_technician_id` INT DEFAULT NULL,
  `time_spent` INT NOT NULL DEFAULT 0, -- cumulative minutes worked
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `closed_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_tickets_department` FOREIGN KEY (`department_id`) 
    REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) 
    REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_priority` FOREIGN KEY (`priority_id`) 
    REFERENCES `priorities` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_status` FOREIGN KEY (`status_id`) 
    REFERENCES `statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_requester` FOREIGN KEY (`requester_id`) 
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tickets_technician` FOREIGN KEY (`assigned_technician_id`) 
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_tickets_number` (`ticket_number`),
  INDEX `idx_tickets_status` (`status_id`),
  INDEX `idx_tickets_priority` (`priority_id`),
  INDEX `idx_tickets_requester` (`requester_id`),
  INDEX `idx_tickets_tech` (`assigned_technician_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Comments Table
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `comment_text` TEXT NOT NULL,
  `attachment_path` VARCHAR(255) DEFAULT NULL,
  `attachment_filename` VARCHAR(150) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_comments_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Audit Logs Table
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Notifications Table
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ticket_id` INT DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_ticket` FOREIGN KEY (`ticket_id`) 
    REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_notifications_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- SEED DATA
-- --------------------------------------------------------

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'Mesa de Ayuda Corp'),
('company_logo', ''),
('theme_color', '#0d6efd'),
('timezone', 'America/Bogota'),
('language', 'es'),
('max_upload_size', '10485760'), -- 10MB default
('allowed_extensions', 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip,rar,txt,log'),
('ldap_enabled', '0'),
('ldap_host', ''),
('ldap_port', '389'),
('ldap_dn', ''),
('ldap_search_base', '');

-- Insert Priorities
INSERT INTO `priorities` (`id`, `name`, `color_hex`, `level`) VALUES
(1, 'Baja', '#28a745', 1),
(2, 'Media', '#fd7e14', 2),
(3, 'Alta', '#dc3545', 3),
(4, 'Crítica', '#6f42c1', 4),
(5, 'Urgente', '#e83e8c', 5);

-- Insert Statuses
INSERT INTO `statuses` (`id`, `name`, `color_hex`) VALUES
(1, 'Nuevo', '#0dcaf0'),
(2, 'Abierto', '#0d6efd'),
(3, 'Asignado', '#6610f2'),
(4, 'En proceso', '#fd7e14'),
(5, 'Esperando Usuario', '#ffc107'),
(6, 'Escalado', '#6f42c1'),
(7, 'Resuelto', '#198754'),
(8, 'Cerrado', '#212529'),
(9, 'Cancelado', '#dc3545');

-- Insert Sample Department
INSERT INTO `departments` (`id`, `name`, `description`) VALUES
(1, 'Sistemas', 'Departamento de Tecnología y Comunicaciones'),
(2, 'Recursos Humanos', 'Gestión de personal y nómina'),
(3, 'Contabilidad', 'Gestión financiera y facturación');

-- Insert Sample Categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Hardware', 'Problemas con equipos físicos, mouse, teclado, pantallas, discos duros.'),
(2, 'Software', 'Errores en programas instalados y herramientas de oficina.'),
(3, 'Correo', 'Configuración de cuentas de correo, problemas de envío o recepción.'),
(4, 'VPN', 'Acceso remoto y conexión segura.'),
(5, 'Internet', 'Problemas de navegación o velocidad.'),
(6, 'Windows', 'Sistema operativo Windows, actualizaciones y fallos.'),
(7, 'Linux', 'Instalación y problemas de servidores o equipos Linux.'),
(8, 'Impresoras', 'Configuración, atascos de papel o cambio de tóner.'),
(9, 'Red', 'Conexión a internet cableada o Wi-Fi, switches, routers.'),
(10, 'Telefonía', 'Extensiones analógicas, IP o fallos de audio.'),
(11, 'Servidores', 'Soporte y fallos en servidores locales o virtuales.'),
(12, 'Otros', 'Cualquier solicitud no contemplada en las categorías anteriores.');
