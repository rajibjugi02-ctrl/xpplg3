-- Run this file in phpMyAdmin SQL tab
-- Import ke database bernama: webkelas_pplg3

CREATE DATABASE IF NOT EXISTS `webkelas_pplg3` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webkelas_pplg3`;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
);
-- Password = admin123 (bcrypt hashed)
INSERT IGNORE INTO `users` (`username`, `password`)
VALUES ('adminPPLG3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================
-- TABLE: structure
-- ============================================
CREATE TABLE IF NOT EXISTS `structure` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `photo` VARCHAR(255) DEFAULT '',
    `order_num` INT DEFAULT 0
);
TRUNCATE TABLE `structure`;
INSERT INTO `structure` (`role`, `name`, `order_num`) VALUES
('Wali Kelas',   'Pa Firman Sidik', 1),
('Ketua Kelas',  'Bagus Pambudi',   2),
('Wakil Ketua',  'Nadine',          3),
('Sekretaris 1', 'Salsabilla',      4),
('Sekretaris 2', 'Rafli',           5),
('Bendahara 1',  'Oktavia',         6),
('Bendahara 2',  'Faneezza',        7),
('PDD',          'Rajib Zahir',     8);

-- ============================================
-- TABLE: students
-- ============================================
CREATE TABLE IF NOT EXISTS `students` (
    `id` VARCHAR(10) PRIMARY KEY,
    `nisn` VARCHAR(20) DEFAULT NULL,
    `kelas` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `portfolio_link` VARCHAR(255) DEFAULT NULL,
    `github_link` VARCHAR(255) DEFAULT NULL,
    `reset_token` VARCHAR(255) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `photo` VARCHAR(255) DEFAULT NULL
);

-- ============================================
-- TABLE: visitors
-- ============================================
CREATE TABLE IF NOT EXISTS `visitors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `kelas` VARCHAR(50) NOT NULL,
    `last_login` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE: activity_logs
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_type` ENUM('admin', 'student', 'visitor') NOT NULL,
    `user_identifier` VARCHAR(100) NOT NULL,
    `action` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
TRUNCATE TABLE `students`;
INSERT INTO `students` (`id`, `name`) VALUES
('001','Abyan Alfarizi'),('002','Aisyah Chyntia Devantara'),('003','Alivia Cahaya Lukmana'),
('004','Andini Novriani'),('005','Asyifa Nurmaulidya'),('006','Bagus Pambudi Priyambodo'),
('007','Bramantyo Arsya Wijaya'),('008','Crisna Juliana'),('009','Davin Alfarrel Nasrullah'),
('010','Dema Arya Ramadhan'),('011','Don Matteu Abie Wewengkang'),('012','Faneza Putri'),
('013','Faris Ahmad Ghaisan'),('014','Habib Ramadhan'),('015','Ilham Muhamad Fahri'),
('016','Intan Nuraeni'),('017','Khaira Putri Madani'),('018','Lilu Maulida'),
('019','Maisie Anzala Maramis'),('020','Muhamad Aditya Saputra'),('021','Muhamad Anzas Adzahri'),
('022',"Muhammad Alif Fatir Sya'bani"),('023','Muhammad Candra Kusuma'),
('024','Muhammad Hafiyz Nurhidayah'),('025','Muhammad Noval Adil Adha'),
('026','Muhammad Rajib Zahir'),('027','Muhammad Refan Abiena Wafa'),('028','Mutia Khamelia'),
('029','Nadine Shahmina'),('030','Nazhril Rizky Alfiansyah'),('031','Niko Keandre Adinata'),
('032','Nur Syifa Fauziah'),('033','Oktavia Indriani'),('034','Rafi Udin'),('035','Rafli'),
('036','Ranty Dwi Oktavia'),('037','Reisya Auliaul Jannah'),('038','Restu Alfarizhi'),
('039','Revand Aqila Al Hafiz'),('040','Rizky Maulana'),('041','Salsabila Azzahra'),
('042','Siti Ainun'),('043','Siti Salwa Aulia'),('044','Sulthan Azzam Rizqullah'),
('045','Taruna Jayalaksana Suwarman');

-- ============================================
-- TABLE: gallery
-- ============================================
CREATE TABLE IF NOT EXISTS `gallery` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `image` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT ''
);

-- ============================================
-- TABLE: projects
-- ============================================
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(255) DEFAULT '',
    `link` VARCHAR(255) DEFAULT ''
);

-- ============================================
-- TABLE: contact
-- ============================================
CREATE TABLE IF NOT EXISTS `contact` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `instagram` VARCHAR(255) DEFAULT '',
    `whatsapp` VARCHAR(255) DEFAULT '',
    `email` VARCHAR(255) DEFAULT '',
    `logo` VARCHAR(255) DEFAULT ''
);
INSERT IGNORE INTO `contact` (`id`, `instagram`, `whatsapp`, `email`, `logo`)
VALUES (1, '@pplg3_engineering', '6281234567890', 'hello@pplg3.com', '');
