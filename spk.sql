-- Database: `spk_sekolah`
CREATE DATABASE IF NOT EXISTS `spk_sekolah` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `spk_sekolah`;

-- Tabel Pengguna (Users)
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user'
);

-- Tabel Sekolah (Schools)
CREATE TABLE `sekolah` (
  `id_sekolah` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_sekolah` VARCHAR(255) NOT NULL,
  `alamat` TEXT NOT NULL,
  `akreditasi` CHAR(1) NOT NULL,
  `total_guru` INT NOT NULL,
  `rata_un` DECIMAL(5,2) NOT NULL,
  `biaya_spp` DECIMAL(10,2) NOT NULL
);

-- Tabel Kriteria (Criteria)
CREATE TABLE `kriteria` (
  `id_kriteria` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kriteria` VARCHAR(100) NOT NULL,
  `bobot` DECIMAL(5,2) NOT NULL,
  `tipe` ENUM('benefit', 'cost') NOT NULL
);

-- Tabel Penilaian/Ranking
CREATE TABLE `penilaian` (
  `id_sekolah` INT NOT NULL,
  `id_kriteria` INT NOT NULL,
  `nilai` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id_sekolah`, `id_kriteria`),
  FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah`(`id_sekolah`),
  FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria`(`id_kriteria`)
);

-- Tabel Hasil Akhir
CREATE TABLE `hasil_akhir` (
  `id_sekolah` INT NOT NULL,
  `total_skor` DECIMAL(10,2) NOT NULL,
  `peringkat` INT NOT NULL,
  PRIMARY KEY (`id_sekolah`),
  FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah`(`id_sekolah`)
);

-- Insert Sample Users
INSERT INTO `users` (`nama_lengkap`, `username`, `password`, `role`) VALUES
('Administrator', 'admin', MD5('admin123'), 'admin'),
('User Biasa', 'user', MD5('user123'), 'user');

-- Insert Sample Kriteria
INSERT INTO `kriteria` (`nama_kriteria`, `bobot`, `tipe`) VALUES 
('Akreditasi', 0.25, 'benefit'),
('Jumlah Guru', 0.20, 'benefit'),
('Rata-rata UN', 0.25, 'benefit'),
('Biaya SPP', 0.30, 'cost');