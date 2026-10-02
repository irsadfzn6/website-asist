-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2026 at 07:03 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_asist`
--

-- --------------------------------------------------------

--
-- Table structure for table `kpi_evaluations`
--

CREATE TABLE `kpi_evaluations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `indicator_id` int(11) NOT NULL,
  `target_role` varchar(30) NOT NULL,
  `periode` varchar(50) NOT NULL,
  `kpi_range` decimal(5,2) DEFAULT 0.00,
  `rating` decimal(3,2) DEFAULT 0.00,
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kpi_evaluations`
--

INSERT INTO `kpi_evaluations` (`id`, `user_id`, `indicator_id`, `target_role`, `periode`, `kpi_range`, `rating`, `unit_sekolah`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'guru', '2026/2027-Ganjil', '100.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(2, 2, 2, 'guru', '2026/2027-Ganjil', '91.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(3, 2, 3, 'guru', '2026/2027-Ganjil', '80.00', '4.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(4, 2, 4, 'guru', '2026/2027-Ganjil', '70.00', '4.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(5, 2, 5, 'guru', '2026/2027-Ganjil', '60.00', '3.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(6, 2, 6, 'guru', '2026/2027-Ganjil', '50.00', '3.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(7, 2, 7, 'guru', '2026/2027-Ganjil', '40.00', '2.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(8, 2, 8, 'guru', '2026/2027-Ganjil', '30.00', '2.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(9, 2, 9, 'guru', '2026/2027-Ganjil', '30.00', '2.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(10, 2, 10, 'guru', '2026/2027-Ganjil', '20.00', '1.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(11, 2, 11, 'guru', '2026/2027-Ganjil', '10.00', '1.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(12, 2, 12, 'guru', '2026/2027-Ganjil', '100.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(13, 2, 13, 'guru', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(14, 2, 14, 'guru', '2026/2027-Ganjil', '80.00', '4.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(15, 2, 15, 'guru', '2026/2027-Ganjil', '80.00', '4.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(16, 2, 16, 'guru', '2026/2027-Ganjil', '20.00', '1.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:28:06'),
(17, 7, 17, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(18, 7, 18, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(19, 7, 19, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(20, 7, 20, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(21, 7, 21, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(22, 7, 22, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(23, 7, 23, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(24, 7, 24, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(25, 7, 25, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(26, 7, 26, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(27, 7, 27, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(28, 7, 28, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(29, 7, 29, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(30, 7, 30, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(31, 7, 31, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(32, 7, 32, 'kaprog', '2026/2027-Ganjil', '90.00', '5.00', 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(49, 2, 1, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(50, 2, 2, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(51, 2, 3, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(52, 2, 4, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(53, 2, 5, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(54, 2, 6, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(55, 2, 7, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(56, 2, 8, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(57, 2, 9, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(58, 2, 10, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(59, 2, 11, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(60, 2, 12, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(61, 2, 13, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(62, 2, 14, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(63, 2, 15, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42'),
(64, 2, 16, 'guru', '2027/2028-Genap', '90.00', '5.00', 'wb_1', '2026-08-19 23:28:42', '2026-08-19 23:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `kpi_indicators`
--

CREATE TABLE `kpi_indicators` (
  `id` int(11) NOT NULL,
  `perspective_id` int(11) NOT NULL,
  `target_role` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kpi_indicators`
--

INSERT INTO `kpi_indicators` (`id`, `perspective_id`, `target_role`, `nama`, `deskripsi`, `urutan`, `unit_sekolah`, `created_at`, `updated_at`) VALUES
(1, 1, 'guru', 'Mengenal Karakteristik Peserta Didik', 'm', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(2, 1, 'guru', 'Menguasai Teori Belajar dan Prinsip-Prinsip', 'a', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(3, 1, 'guru', 'Mengembangkan Kurikulum', 'b', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(4, 2, 'guru', 'Bertindak Sesuai Norma Agama, Hukum, Sosial, dan Kebudayaan Nasional', 'c', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(5, 2, 'guru', 'Menunjukkan Pribadi Yang Dewasa dan Teladan', 'd', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(6, 2, 'guru', 'Etos Kerja, Tanggung Jawab, Yang Tinggi, Rasa Bangga Menjadi Guru', 'e', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(7, 3, 'guru', 'Bersifat Eksklusif, Objective, Serta Tidak Diskriminatif', 'f', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(8, 3, 'guru', 'Komunikasi Sesama Guru, Tenaga Kependidikkan, Orang Tua, Peserta Didik, dan Masyarakat', 'g', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(9, 4, 'guru', 'Menguasai Materi, Struktur, Pola Pikir Keilmuan yang Mendukung Mata Pelajaran', 'h', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(10, 4, 'guru', 'Mengembangkan Keprofesionalan Melalui Tindakan Reflektif', 'i', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(11, 5, 'guru', 'Laporan Vidio Pembelajaran', 'j', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(12, 5, 'guru', 'Laporan Perangkat Ajar', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(13, 6, 'guru', 'Ketepatan Waktu Mengajar', 'l', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(14, 6, 'guru', 'Disiplin Kehadiran', 'm', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(15, 7, 'guru', 'Pelayanan / layanan publik dan masyarakat', 'n', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(16, 7, 'guru', 'Berpenampilan Sopan dan Ramah', 'o', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(17, 8, 'kaprog', 'Mengenal Karakteristik Peserta Didik', 'm', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(18, 8, 'kaprog', 'Menguasai Teori Belajar dan Prinsip-Prinsip', 'a', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(19, 8, 'kaprog', 'Mengembangkan Kurikulum', 'b', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(20, 9, 'kaprog', 'Bertindak Sesuai Norma Agama, Hukum, Sosial, dan Kebudayaan Nasional', 'c', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(21, 9, 'kaprog', 'Menunjukkan Pribadi Yang Dewasa dan Teladan', 'd', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(22, 9, 'kaprog', 'Etos Kerja, Tanggung Jawab, Yang Tinggi, Rasa Bangga Menjadi Guru', 'e', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(23, 10, 'kaprog', 'Bersifat Eksklusif, Objective, Serta Tidak Diskriminatif', 'f', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(24, 10, 'kaprog', 'Komunikasi Sesama Guru, Tenaga Kependidikkan, Orang Tua, Peserta Didik, dan Masyarakat', 'g', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(25, 11, 'kaprog', 'Menguasai Materi, Struktur, Pola Pikir Keilmuan yang Mendukung Mata Pelajaran', 'h', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(26, 11, 'kaprog', 'Mengembangkan Keprofesionalan Melalui Tindakan Reflektif', 'i', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(27, 12, 'kaprog', 'Laporan Vidio Pembelajaran', 'j', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(28, 12, 'kaprog', 'Laporan Perangkat Ajar', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(29, 13, 'kaprog', 'Ketepatan Waktu Mengajar', 'l', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(30, 13, 'kaprog', 'Disiplin Kehadiran', 'm', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(31, 14, 'kaprog', 'Pelayanan / layanan publik dan masyarakat', 'n', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(32, 14, 'kaprog', 'Berpenampilan Sopan dan Ramah', 'o', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(33, 15, 'kepsek', 'Mengenal Karakteristik Peserta Didik', 'm', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(34, 15, 'kepsek', 'Menguasai Teori Belajar dan Prinsip-Prinsip', 'a', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(35, 15, 'kepsek', 'Mengembangkan Kurikulum', 'b', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(36, 16, 'kepsek', 'Bertindak Sesuai Norma Agama, Hukum, Sosial, dan Kebudayaan Nasional', 'c', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(37, 16, 'kepsek', 'Menunjukkan Pribadi Yang Dewasa dan Teladan', 'd', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(38, 16, 'kepsek', 'Etos Kerja, Tanggung Jawab, Yang Tinggi, Rasa Bangga Menjadi Guru', 'e', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(39, 17, 'kepsek', 'Bersifat Eksklusif, Objective, Serta Tidak Diskriminatif', 'f', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(40, 17, 'kepsek', 'Komunikasi Sesama Guru, Tenaga Kependidikkan, Orang Tua, Peserta Didik, dan Masyarakat', 'g', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(41, 18, 'kepsek', 'Menguasai Materi, Struktur, Pola Pikir Keilmuan yang Mendukung Mata Pelajaran', 'h', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(42, 18, 'kepsek', 'Mengembangkan Keprofesionalan Melalui Tindakan Reflektif', 'i', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(43, 19, 'kepsek', 'Laporan Vidio Pembelajaran', 'j', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(44, 19, 'kepsek', 'Laporan Perangkat Ajar', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(45, 20, 'kepsek', 'Ketepatan Waktu Mengajar', 'l', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(46, 20, 'kepsek', 'Disiplin Kehadiran', 'm', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(47, 21, 'kepsek', 'Pelayanan / layanan publik dan masyarakat', 'n', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(48, 21, 'kepsek', 'Berpenampilan Sopan dan Ramah', 'o', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(49, 22, 'wakakur', 'Mengenal Karakteristik Peserta Didik', 'm', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(50, 22, 'wakakur', 'Menguasai Teori Belajar dan Prinsip-Prinsip', 'a', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(51, 22, 'wakakur', 'Mengembangkan Kurikulum', 'b', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(52, 23, 'wakakur', 'Bertindak Sesuai Norma Agama, Hukum, Sosial, dan Kebudayaan Nasional', 'c', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(53, 23, 'wakakur', 'Menunjukkan Pribadi Yang Dewasa dan Teladan', 'd', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(54, 23, 'wakakur', 'Etos Kerja, Tanggung Jawab, Yang Tinggi, Rasa Bangga Menjadi Guru', 'e', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(55, 24, 'wakakur', 'Bersifat Eksklusif, Objective, Serta Tidak Diskriminatif', 'f', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(56, 24, 'wakakur', 'Komunikasi Sesama Guru, Tenaga Kependidikkan, Orang Tua, Peserta Didik, dan Masyarakat', 'g', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(57, 25, 'wakakur', 'Menguasai Materi, Struktur, Pola Pikir Keilmuan yang Mendukung Mata Pelajaran', 'h', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(58, 25, 'wakakur', 'Mengembangkan Keprofesionalan Melalui Tindakan Reflektif', 'i', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(59, 26, 'wakakur', 'Laporan Vidio Pembelajaran', 'j', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(60, 26, 'wakakur', 'Laporan Perangkat Ajar', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(61, 27, 'wakakur', 'Ketepatan Waktu Mengajar', 'l', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(62, 27, 'wakakur', 'Disiplin Kehadiran', 'm', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(63, 28, 'wakakur', 'Pelayanan / layanan publik dan masyarakat', 'n', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(64, 28, 'wakakur', 'Berpenampilan Sopan dan Ramah', 'o', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(65, 29, 'wakasis', 'Mengenal Karakteristik Peserta Didik', 'm', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(66, 29, 'wakasis', 'Menguasai Teori Belajar dan Prinsip-Prinsip', 'a', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(67, 29, 'wakasis', 'Mengembangkan Kurikulum', 'b', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(68, 30, 'wakasis', 'Bertindak Sesuai Norma Agama, Hukum, Sosial, dan Kebudayaan Nasional', 'c', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(69, 30, 'wakasis', 'Menunjukkan Pribadi Yang Dewasa dan Teladan', 'd', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(70, 30, 'wakasis', 'Etos Kerja, Tanggung Jawab, Yang Tinggi, Rasa Bangga Menjadi Guru', 'e', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(71, 31, 'wakasis', 'Bersifat Eksklusif, Objective, Serta Tidak Diskriminatif', 'f', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(72, 31, 'wakasis', 'Komunikasi Sesama Guru, Tenaga Kependidikkan, Orang Tua, Peserta Didik, dan Masyarakat', 'g', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(73, 32, 'wakasis', 'Menguasai Materi, Struktur, Pola Pikir Keilmuan yang Mendukung Mata Pelajaran', 'h', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(74, 32, 'wakasis', 'Mengembangkan Keprofesionalan Melalui Tindakan Reflektif', 'i', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(75, 33, 'wakasis', 'Laporan Vidio Pembelajaran', 'j', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(76, 33, 'wakasis', 'Laporan Perangkat Ajar', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(77, 34, 'wakasis', 'Ketepatan Waktu Mengajar', 'l', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(78, 34, 'wakasis', 'Disiplin Kehadiran', 'm', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(79, 35, 'wakasis', 'Pelayanan / layanan publik dan masyarakat', 'n', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(80, 35, 'wakasis', 'Berpenampilan Sopan dan Ramah', 'o', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `kpi_perspectives`
--

CREATE TABLE `kpi_perspectives` (
  `id` int(11) NOT NULL,
  `target_role` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kpi_perspectives`
--

INSERT INTO `kpi_perspectives` (`id`, `target_role`, `nama`, `deskripsi`, `urutan`, `unit_sekolah`, `created_at`, `updated_at`) VALUES
(1, 'guru', 'Pedagogik', 'p', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(2, 'guru', 'Kepribadian', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(3, 'guru', 'Sosial', 's', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(4, 'guru', 'Profesional', 'p', 4, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(5, 'guru', 'Out Put Laporan', 'o', 5, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(6, 'guru', 'Kehadiran', 'k', 6, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(7, 'guru', 'Kepatuhan', 'k', 7, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(8, 'kaprog', 'Pedagogik', 'p', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(9, 'kaprog', 'Kepribadian', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(10, 'kaprog', 'Sosial', 's', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(11, 'kaprog', 'Profesional', 'p', 4, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(12, 'kaprog', 'Out Put Laporan', 'o', 5, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(13, 'kaprog', 'Kehadiran', 'k', 6, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(14, 'kaprog', 'Kepatuhan', 'k', 7, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(15, 'kepsek', 'Pedagogik', 'p', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(16, 'kepsek', 'Kepribadian', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(17, 'kepsek', 'Sosial', 's', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(18, 'kepsek', 'Profesional', 'p', 4, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(19, 'kepsek', 'Out Put Laporan', 'o', 5, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(20, 'kepsek', 'Kehadiran', 'k', 6, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(21, 'kepsek', 'Kepatuhan', 'k', 7, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(22, 'wakakur', 'Pedagogik', 'p', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(23, 'wakakur', 'Kepribadian', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(24, 'wakakur', 'Sosial', 's', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(25, 'wakakur', 'Profesional', 'p', 4, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(26, 'wakakur', 'Out Put Laporan', 'o', 5, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(27, 'wakakur', 'Kehadiran', 'k', 6, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(28, 'wakakur', 'Kepatuhan', 'k', 7, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(29, 'wakasis', 'Pedagogik', 'p', 1, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(30, 'wakasis', 'Kepribadian', 'k', 2, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(31, 'wakasis', 'Sosial', 's', 3, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(32, 'wakasis', 'Profesional', 'p', 4, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(33, 'wakasis', 'Out Put Laporan', 'o', 5, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(34, 'wakasis', 'Kehadiran', 'k', 6, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41'),
(35, 'wakasis', 'Kepatuhan', 'k', 7, 'wb_1', '2026-08-19 23:18:41', '2026-08-19 23:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_kegiatan`
--

CREATE TABLE `proposal_kegiatan` (
  `id` int(11) NOT NULL,
  `pengaju_id` int(11) NOT NULL,
  `nama_proposal` varchar(150) NOT NULL,
  `diajukan_oleh` varchar(80) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `file_proposal` varchar(150) DEFAULT NULL,
  `dokumentasi_link` varchar(255) DEFAULT NULL,
  `file_lpj` varchar(150) DEFAULT NULL,
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `revisi_status` enum('none','pending','diperbaiki') DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `validasi_kepsek` tinyint(1) DEFAULT 0,
  `validasi_kepsek_by` int(11) DEFAULT NULL,
  `validasi_kepsek_at` datetime DEFAULT NULL,
  `validasi_ketua_yayasan` tinyint(1) DEFAULT 0,
  `validasi_ketua_yayasan_by` int(11) DEFAULT NULL,
  `validasi_ketua_yayasan_at` datetime DEFAULT NULL,
  `validasi_keuangan` tinyint(1) DEFAULT 0,
  `validasi_keuangan_by` int(11) DEFAULT NULL,
  `validasi_keuangan_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `proposal_kegiatan`
--

INSERT INTO `proposal_kegiatan` (`id`, `pengaju_id`, `nama_proposal`, `diajukan_oleh`, `tanggal`, `file_proposal`, `dokumentasi_link`, `file_lpj`, `unit_sekolah`, `revisi_status`, `created_at`, `updated_at`, `validasi_kepsek`, `validasi_kepsek_by`, `validasi_kepsek_at`, `validasi_ketua_yayasan`, `validasi_ketua_yayasan_by`, `validasi_ketua_yayasan_at`, `validasi_keuangan`, `validasi_keuangan_by`, `validasi_keuangan_at`) VALUES
(20, 6, 'Proposal Kegiatan Kemah Bhakti Penggalang Pramuka', 'Wakil Kesiswaan', '2026-08-02', 'prop_wb1_val_2.pdf', 'https://www.youtube.com/watch?v=2v8HAf99O74', 'lpj_wb1_val_2.pdf', 'wb_1', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(21, 5, 'Proposal Penyelarasan Kurikulum Vokasi Dengan DUDI', 'Wakil Kurikulum', '2026-08-03', 'prop_wb1_val_3.pdf', 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ', 'lpj_wb1_val_3.pdf', 'wb_1', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(23, 4, 'Proposal Pengadaan Perangkat Laboratorium Jaringan Komputer', 'Kepala Sekolah', '2026-08-05', 'prop_wb1_val_5.pdf', 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', 'lpj_wb1_val_5.pdf', 'wb_1', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(24, 6, 'Proposal Pelaksanaan Turnamen Futsal Antar Sekolah WB 1', 'Wakil Kesiswaan', '2026-08-06', 'prop_wb1_unval_1.pdf', NULL, NULL, 'wb_1', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(25, 7, 'Proposal Pembekalan Praktek Kerja Lapangan (PKL) Kelas XII', 'Kepala Program', '2026-08-07', 'prop_wb1_unval_2.pdf', NULL, NULL, 'wb_1', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(26, 2, 'Proposal Inovasi Media Pembelajaran Interaktif Guru', 'Guru WB 1', '2026-08-08', 'prop_wb1_unval_3.pdf', NULL, NULL, 'wb_1', 'pending', '2026-08-09 13:29:20', '2026-08-19 10:03:35', 0, NULL, NULL, 0, NULL, NULL, 1, 26, '2026-08-19 17:03:35'),
(27, 5, 'Proposal Bimbingan Belajar Persiapan Asesmen Nasional (ANBK)', 'Wakil Kurikulum', '2026-08-08', 'prop_wb1_unval_4.pdf', NULL, NULL, 'wb_1', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(28, 4, 'Proposal Program Perawatan & Maintenance Ruang Server', 'Kepala Sekolah', '2026-08-09', 'prop_wb1_unval_5.pdf', NULL, NULL, 'wb_1', 'none', '2026-08-09 13:29:20', '2026-08-09 19:00:56', 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(29, 11, 'Proposal Pelatihan Digital Marketing Siswa Bisnis Daring', 'Guru WB 2', '2026-08-01', 'prop_wb2_val_1.pdf', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'lpj_wb2_val_1.pdf', 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(30, 18, 'Proposal Pentas Seni dan Pameran Karya Kewirausahaan Siswa', 'Wakil Kesiswaan WB 2', '2026-08-02', 'prop_wb2_val_2.pdf', 'https://www.youtube.com/watch?v=2v8HAf99O74', 'lpj_wb2_val_2.pdf', 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(31, 17, 'Proposal Peningkatan Kompetensi Guru Dalam Modul Ajar P5', 'Wakil Kurikulum WB 2', '2026-08-03', 'prop_wb2_val_3.pdf', 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ', 'lpj_wb2_val_3.pdf', 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(32, 19, 'Proposal Kerjasama Industri dan Job Fair Bursa Kerja Khusus', 'Kepala Program WB 2', '2026-08-04', 'prop_wb2_val_4.pdf', 'https://www.youtube.com/watch?v=L_LUpnjgPso', 'lpj_wb2_val_4.pdf', 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(33, 16, 'Proposal Renovasi Sarana Ruang Praktik Siswa WB 2', 'Kepala Sekolah WB 2', '2026-08-05', 'prop_wb2_val_5.pdf', 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', 'lpj_wb2_val_5.pdf', 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(34, 18, 'Proposal Pelaksanaan Outbound & Latihan Dasar Kepemimpinan', 'Wakil Kesiswaan WB 2', '2026-08-06', 'prop_wb2_unval_1.pdf', NULL, NULL, 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(35, 19, 'Proposal Program Pemagangan Kerja Luar Negeri', 'Kepala Program WB 2', '2026-08-07', 'prop_wb2_unval_2.pdf', NULL, NULL, 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(36, 11, 'Proposal Workshop Publikasi Karya Ilmiah Guru', 'Guru WB 2', '2026-08-08', 'prop_wb2_unval_3.pdf', NULL, NULL, 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(37, 17, 'Proposal Penerapan E-Learning & Bank Soal Ujian Online', 'Wakil Kurikulum WB 2', '2026-08-08', 'prop_wb2_unval_4.pdf', NULL, NULL, 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(38, 16, 'Proposal Pengadaan Peralatan Multimedia & Studio Foto', 'Kepala Sekolah WB 2', '2026-08-09', 'prop_wb2_unval_5.pdf', NULL, NULL, 'wb_2', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(39, 20, 'Proposal Pelatihan Cybersecurity & Coding Literacy Siswa', 'Guru WB 3', '2026-08-01', 'prop_wb3_val_1.pdf', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'lpj_wb3_val_1.pdf', 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(40, 24, 'Proposal Gerakan Sekolah Adiwiyata dan Bank Sampah Siswa', 'Wakil Kesiswaan WB 3', '2026-08-02', 'prop_wb3_val_2.pdf', 'https://www.youtube.com/watch?v=2v8HAf99O74', 'lpj_wb3_val_2.pdf', 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(41, 23, 'Proposal Preparasi Akreditasi Satuan Pendidikan BAN-PDM', 'Wakil Kurikulum WB 3', '2026-08-03', 'prop_wb3_val_3.pdf', 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ', 'lpj_wb3_val_3.pdf', 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(42, 25, 'Proposal Pengadaan Trainer Kit Otomasi Industri Siemens', 'Kepala Program WB 3', '2026-08-04', 'prop_wb3_val_4.pdf', 'https://www.youtube.com/watch?v=L_LUpnjgPso', 'lpj_wb3_val_4.pdf', 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(43, 22, 'Proposal Program Peningkatan Keamanan & CCTV Lingkungan Sekolah', 'Kepala Sekolah WB 3', '2026-08-05', 'prop_wb3_val_5.pdf', 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ', 'lpj_wb3_val_5.pdf', 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(44, 24, 'Proposal Peringatan HUT Kemerdekaan RI & Lomba antar Siswa', 'Wakil Kesiswaan WB 3', '2026-08-06', 'prop_wb3_unval_1.pdf', NULL, NULL, 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(45, 25, 'Proposal Pelatihan Robotika dan IoT Bagi Kelompok Bakat Siswa', 'Kepala Program WB 3', '2026-08-07', 'prop_wb3_unval_2.pdf', NULL, NULL, 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(46, 20, 'Proposal Workshop Pembuatan Modul Ajar Digital', 'Guru WB 3', '2026-08-08', 'prop_wb3_unval_3.pdf', NULL, NULL, 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(47, 23, 'Proposal Program Matrikulasi Siswa Baru T.P 2026/2027', 'Wakil Kurikulum WB 3', '2026-08-08', 'prop_wb3_unval_4.pdf', NULL, NULL, 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(48, 22, 'Proposal Pembangunan Smart Classroom & Interactive Board', 'Kepala Sekolah WB 3', '2026-08-09', 'prop_wb3_unval_5.pdf', NULL, NULL, 'wb_3', 'none', '2026-08-09 13:29:20', NULL, 0, NULL, NULL, 0, NULL, NULL, 0, NULL, NULL),
(49, 2, 'pengajuan lomba olahraga', 'Guru WB 1', '2026-08-10', 'prop_u1_20260810011300_prop_u1_20260810004137_surat_20260810003233_tpl_wb_1_s_iz_template.docx', 'https://youtu.be/_4nvQjDYUCg?si=UmAEyul4qftT2w53', 'lpj_u1_20260810012457_surat_20260810003233_tpl_wb_1_s_iz_template.docx', 'wb_1', 'diperbaiki', '2026-08-09 17:41:37', '2026-08-19 10:03:20', 0, NULL, NULL, 0, NULL, NULL, 1, 26, '2026-08-19 17:03:20'),
(50, 2, 'pengajuan lomba 17 an', 'Guru WB 1', '2026-08-10', 'prop_u1_20260810094544_Surat_Undangan_Pembukaan_UAPS_Mahasiswa_Prodi_Sistem_Informasi_-_08_Agustus_2026.pdf', 'https://www.youtube.com/watch?v=SN6yluJHg3M', NULL, 'wb_1', 'none', '2026-08-10 02:45:44', '2026-08-18 18:22:21', 0, NULL, NULL, 0, NULL, NULL, 1, 26, '2026-08-19 01:22:21'),
(51, 2, 'seminar apaan', 'Guru WB 1', '2026-08-16', 'prop_u1_20260816212158_surat_20260809201604_asissss.pdf', NULL, NULL, 'wb_1', 'diperbaiki', '2026-08-15 19:58:50', '2026-08-18 16:35:42', 0, NULL, NULL, 0, NULL, NULL, 1, 26, '2026-08-18 23:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_revisi`
--

CREATE TABLE `proposal_revisi` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `revisi_oleh` int(11) NOT NULL,
  `revisi_role` enum('kepsek','ketua_yayasan') NOT NULL,
  `keterangan_revisi` text NOT NULL,
  `status` enum('pending','diperbaiki') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `proposal_revisi`
--

INSERT INTO `proposal_revisi` (`id`, `proposal_id`, `revisi_oleh`, `revisi_role`, `keterangan_revisi`, `status`, `created_at`) VALUES
(11, 49, 9, 'ketua_yayasan', 'tolong ganti hari', 'diperbaiki', '2026-08-09 17:46:44'),
(12, 51, 9, 'ketua_yayasan', 'jhg', 'diperbaiki', '2026-08-16 14:21:22'),
(13, 26, 4, 'kepsek', 'tidak jelas', 'pending', '2026-08-18 15:22:44');

-- --------------------------------------------------------

--
-- Table structure for table `surat_keluar`
--

CREATE TABLE `surat_keluar` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `nomor_surat` varchar(60) DEFAULT NULL,
  `perihal` varchar(100) DEFAULT NULL,
  `tujuan_surat` varchar(150) DEFAULT NULL,
  `lampiran` varchar(30) DEFAULT '-',
  `isi_surat` text DEFAULT NULL,
  `penandatangan_nama` varchar(100) DEFAULT NULL,
  `penandatangan_jabatan` varchar(100) DEFAULT NULL,
  `penandatangan_nip` varchar(30) DEFAULT NULL,
  `tembusan` text DEFAULT NULL,
  `tanggal_pengajuan` date DEFAULT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `status` enum('diajukan','diproses','selesai','ditolak') DEFAULT 'diajukan',
  `file_ttd` varchar(150) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `kota_tanggal` varchar(50) DEFAULT 'Kab. Contoh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `surat_keluar`
--

INSERT INTO `surat_keluar` (`id`, `user_id`, `kategori_id`, `nomor_surat`, `perihal`, `tujuan_surat`, `lampiran`, `isi_surat`, `penandatangan_nama`, `penandatangan_jabatan`, `penandatangan_nip`, `tembusan`, `tanggal_pengajuan`, `tanggal_keluar`, `status`, `file_ttd`, `catatan`, `created_at`, `updated_at`, `unit_sekolah`, `kota_tanggal`) VALUES
(4, 2, 1, '001/S-ED/I/1970', 'pengumuman libur semester', 'Bapak/Ibu / Saudara/i', '-', '<p class=\"MsoNormal\" style=\"margin-bottom:0cm;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;\r\nmso-ansi-language:EN-US\">Assalamualaikum Warahmatullahi Wabarakatuh<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;\r\nmso-ansi-language:EN-US\">&nbsp;</span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph;text-indent:36.0pt;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;mso-ansi-language:\r\nEN-US\">Teriring salam dan do’a semoga Bapak/Ibu selalu dalam lindungan Allah\r\nSWT, sehat wal’afiat serta selalu sukses dalam menjalankan tugas sehari-hari.\r\nAamiin.<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">Sesuai kalender pendidikan Tahun\r\nPelajaran 2025/2026 dan selesainya kegiatan Sumatif Tengah Semester Genap dan\r\nPSAJ (Penilaian Sumatif Akhir Jenjang di SMA Negara Contoh 2, maka dengan ini Kami\r\nmemberitahukan perihal Pengambilan Rapor STS Genap dan Libur Lebaran:<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><div align=\"center\">\r\n\r\n<table class=\"MsoTableGrid\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"652\" style=\"width: 488.8pt; border-width: medium; border-style: none; border-color: currentcolor; border-image: none;\">\r\n <tbody><tr style=\"mso-yfti-irow:0;mso-yfti-firstrow:yes;height:20.15pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: 1pt; border-color: windowtext; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">No<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width: 149.25pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Tanggal<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width: 148.85pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Kegiatan<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width: 163pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Kelas<o:p></o:p></span></b></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:1;height:20.45pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 20.45pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">1<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">04 April 26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Ambil Raport\r\n  STS Genap<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X &amp; XI\r\n  Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:2;height:21.45pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 21.45pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">2<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">16 s.d 28 Maret\r\n  26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Libur Idul\r\n  Fitri (Lebaran)<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X,XI &amp;\r\n  XII Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:3;mso-yfti-lastrow:yes;height:20.35pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 20.35pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">3<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">30 Maret 26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Masuk Sekolah<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X,XI &amp;\r\n  XII Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n</tbody></table>\r\n\r\n</div><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<span lang=\"EN-US\" style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-fareast-font-family:&quot;Times New Roman&quot;;mso-fareast-theme-font:minor-fareast;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US;mso-fareast-language:IN;\r\nmso-bidi-language:AR-SA\">Demikian surat pemberitahuan ini. Atas perhatian dan\r\nkerja sama yang baik dari bapak/ibu orang tua/wali, kami sampaikan terima kasih.</span></p>', 'Nama Kepala Sekolah', 'Kepala Sekolah', '', '- Pengurus Yayasan\r\n- Kantin & Security\r\n- Arsip', '2026-08-19', '2026-08-19', 'selesai', 'surat_ttd_4_20260819155246.pdf', 'tanggal sekian ya sampai sekian ya liburnya', '2026-08-19 08:51:48', '2026-08-19 08:52:46', 'wb_1', 'Kab. Contoh'),
(5, 2, 1, '001/S-ED/I/1970', 'Pengambilan Raport', 'Bapak/Ibu Orang Tua  Siswa Kelas X, XI & XII', '-', '<p class=\"MsoNormal\" style=\"margin-bottom:0cm;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;\r\nmso-ansi-language:EN-US\">Assalamualaikum Warahmatullahi Wabarakatuh<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;\r\nmso-ansi-language:EN-US\">&nbsp;</span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph;text-indent:36.0pt;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;mso-ansi-language:\r\nEN-US\">Teriring salam dan do’a semoga Bapak/Ibu selalu dalam lindungan Allah\r\nSWT, sehat wal’afiat serta selalu sukses dalam menjalankan tugas sehari-hari.\r\nAamiin.<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">Sesuai kalender pendidikan Tahun\r\nPelajaran 2025/2026 dan selesainya kegiatan Sumatif Tengah Semester Genap dan\r\nPSAJ (Penilaian Sumatif Akhir Jenjang di SMA Negara Contoh 2, maka dengan ini Kami\r\nmemberitahukan perihal Pengambilan Rapor STS Genap dan Libur Lebaran:<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><div align=\"center\">\r\n\r\n<table class=\"MsoTableGrid\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"652\" style=\"width: 488.8pt; border-width: medium; border-style: none; border-color: currentcolor; border-image: none;\">\r\n <tbody><tr style=\"mso-yfti-irow:0;mso-yfti-firstrow:yes;height:20.15pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: 1pt; border-color: windowtext; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">No<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width: 149.25pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Tanggal<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width: 148.85pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Kegiatan<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width: 163pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Kelas<o:p></o:p></span></b></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:1;height:20.45pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 20.45pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">1<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">04 April 26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Ambil Raport\r\n  STS Genap<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X &amp; XI\r\n  Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:2;height:21.45pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 21.45pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">2<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">16 s.d 28 Maret\r\n  26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Libur Idul\r\n  Fitri (Lebaran)<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X,XI &amp;\r\n  XII Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:3;mso-yfti-lastrow:yes;height:20.35pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 20.35pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">3<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">30 Maret 26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Masuk Sekolah<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X,XI &amp;\r\n  XII Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n</tbody></table>\r\n\r\n</div><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<span lang=\"EN-US\" style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-fareast-font-family:&quot;Times New Roman&quot;;mso-fareast-theme-font:minor-fareast;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US;mso-fareast-language:IN;\r\nmso-bidi-language:AR-SA\">Demikian surat pemberitahuan ini. Atas perhatian dan\r\nkerja sama yang baik dari bapak/ibu orang tua/wali, kami sampaikan terima kasih.</span></p>', 'Nama Kepala Sekolah', 'Kepala Sekolah', '', '- Pengurus Yayasan\r\n- Kantin & Security\r\n- Arsip', '2026-08-19', '2026-08-19', 'selesai', 'surat_ttd_5_20260819162738.pdf', 'dsfsafsa', '2026-08-19 09:11:11', '2026-08-19 09:31:46', 'wb_1', 'Kab. Contoh'),
(6, 2, 1, NULL, 'seminar wisuda', 'Wali Siswa', '-', NULL, NULL, NULL, NULL, NULL, '2026-08-19', NULL, 'ditolak', NULL, 'sfaf', '2026-08-19 09:11:54', '2026-08-19 09:14:43', 'wb_1', 'Kab. Contoh');

-- --------------------------------------------------------

--
-- Table structure for table `surat_keluar_kategori`
--

CREATE TABLE `surat_keluar_kategori` (
  `id` int(11) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `template_isi` text DEFAULT NULL,
  `gambar_kop` varchar(150) DEFAULT NULL,
  `unit_sekolah` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `surat_keluar_kategori`
--

INSERT INTO `surat_keluar_kategori` (`id`, `kode`, `nama`, `deskripsi`, `template_isi`, `gambar_kop`, `unit_sekolah`) VALUES
(1, 'S-ED', 'Surat Edaran', 'Surat edaran resmi untuk pengumuman sekolah, kegiatan, dan tata tertib.', '<p class=\"MsoNormal\" style=\"margin-bottom:0cm;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;\r\nmso-ansi-language:EN-US\">Assalamualaikum Warahmatullahi Wabarakatuh<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;\r\nmso-ansi-language:EN-US\">&nbsp;</span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph;text-indent:36.0pt;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;mso-bidi-font-family:Arial;mso-ansi-language:\r\nEN-US\">Teriring salam dan do’a semoga Bapak/Ibu selalu dalam lindungan Allah\r\nSWT, sehat wal’afiat serta selalu sukses dalam menjalankan tugas sehari-hari.\r\nAamiin.<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">Sesuai kalender pendidikan Tahun\r\nPelajaran 2025/2026 dan selesainya kegiatan Sumatif Tengah Semester Genap dan\r\nPSAJ (Penilaian Sumatif Akhir Jenjang di SMA Negara Contoh 2, maka dengan ini Kami\r\nmemberitahukan perihal Pengambilan Rapor STS Genap dan Libur Lebaran:<o:p></o:p></span></p><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><div align=\"center\">\r\n\r\n<table class=\"MsoTableGrid\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"652\" style=\"width: 488.8pt; border-width: medium; border-style: none; border-color: currentcolor; border-image: none;\">\r\n <tbody><tr style=\"mso-yfti-irow:0;mso-yfti-firstrow:yes;height:20.15pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: 1pt; border-color: windowtext; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">No<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width: 149.25pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Tanggal<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width: 148.85pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Kegiatan<o:p></o:p></span></b></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width: 163pt; border-width: 1pt 1pt 1pt medium; border-style: solid solid solid none; border-color: windowtext windowtext windowtext currentcolor; padding: 0cm 5.4pt; height: 20.15pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><b><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Kelas<o:p></o:p></span></b></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:1;height:20.45pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 20.45pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">1<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">04 April 26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Ambil Raport\r\n  STS Genap<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X &amp; XI\r\n  Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:2;height:21.45pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 21.45pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">2<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">16 s.d 28 Maret\r\n  26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Libur Idul\r\n  Fitri (Lebaran)<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:21.45pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X,XI &amp;\r\n  XII Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n <tr style=\"mso-yfti-irow:3;mso-yfti-lastrow:yes;height:20.35pt\">\r\n  <td width=\"37\" valign=\"top\" style=\"width: 27.7pt; border-width: medium 1pt 1pt; border-style: none solid solid; border-color: currentcolor windowtext windowtext; padding: 0cm 5.4pt; height: 20.35pt;\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">3<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"199\" valign=\"top\" style=\"width:149.25pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" align=\"center\" style=\"margin-bottom:0cm;text-align:center;\r\n  line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">30 Maret 26<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"198\" valign=\"top\" style=\"width:148.85pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">Masuk Sekolah<o:p></o:p></span></p>\r\n  </td>\r\n  <td width=\"217\" valign=\"top\" style=\"width:163.0pt;border-top:none;border-left:\r\n  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;\r\n  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;\r\n  mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt;height:20.35pt\">\r\n  <p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\n  inter-ideograph;line-height:normal\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\n  mso-bidi-font-family:&quot;Times New Roman&quot;;mso-ansi-language:EN-US\">X,XI &amp;\r\n  XII Semua Jurusan<o:p></o:p></span></p>\r\n  </td>\r\n </tr>\r\n</tbody></table>\r\n\r\n</div><p class=\"MsoNormal\" style=\"margin-bottom:0cm;text-align:justify;text-justify:\r\ninter-ideograph\"><span lang=\"EN-US\" style=\"font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US\">&nbsp;</span></p><p>\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n<span lang=\"EN-US\" style=\"font-size:11.0pt;line-height:115%;font-family:&quot;Verdana&quot;,sans-serif;\r\nmso-fareast-font-family:&quot;Times New Roman&quot;;mso-fareast-theme-font:minor-fareast;\r\nmso-bidi-font-family:Arial;mso-ansi-language:EN-US;mso-fareast-language:IN;\r\nmso-bidi-language:AR-SA\">Demikian surat pemberitahuan ini. Atas perhatian dan\r\nkerja sama yang baik dari bapak/ibu orang tua/wali, kami sampaikan terima kasih.</span></p>', 'master_kop_20260819140906_796.png', 'wb_1');

-- --------------------------------------------------------

--
-- Table structure for table `surat_masuk`
--

CREATE TABLE `surat_masuk` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `perihal` varchar(150) NOT NULL,
  `nama_instansi` varchar(100) NOT NULL,
  `disposisi` text NOT NULL,
  `aktor_tujuan` varchar(50) DEFAULT NULL,
  `file_surat` varchar(150) DEFAULT NULL,
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `surat_masuk`
--

INSERT INTO `surat_masuk` (`id`, `nomor_surat`, `tanggal_masuk`, `perihal`, `nama_instansi`, `disposisi`, `aktor_tujuan`, `file_surat`, `unit_sekolah`, `created_at`, `updated_at`) VALUES
(22, '118/SMK-MITRA/VII/2026', '2026-08-05', 'Undangan Sinkronisasi Kurikulum dan Penyelarasan Dunia Industri', 'PT Maju Teknologi Indonesia', 'Mohon Kepala Program menindaklanjuti undangan, menghadiri kegiatan, dan', 'keprog_wb1', 'surat_masuk_22.pdf', 'wb_1', '2026-08-04 20:25:59', '2026-08-09 12:59:04'),
(27, '088/SMK-CENTER/VIII/2026', '2026-08-04', 'Undangan Pelatihan Pembuatan Media Pembelajaran Interaktif Berbasis AI', 'Balai Pengembangan Sumber Daya Manusia', 'Ditugaskan kepada Guru untuk mengikuti pelatihan online/offline hingga selesai dan menyusun laporan.', 'guru1', 'surat_masuk_27.pdf', 'wb_1', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(34, '002/DINAS-PDK/VIII/2026', '2026-08-01', 'Verifikasi Lapangan Kelayakan Sarana Laboratorium Komputer', 'Dinas Pendidikan Provinsi', 'Kepala Sekolah WB 2 harap mendampingi pengawas dinas selama peninjauan fasilitas fisik.', 'kepsek_wb2', 'surat_masuk_34.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(35, '018/INDOSAT/VIII/2026', '2026-08-02', 'Tawaran Kerjasama Praktek Kerja Lapangan (PKL) Bidang Digital Marketing', 'PT Indosat Ooredoo Hutchison', 'Kepala Program WB 2 segera mendata kuota siswa jurusan Pemasaran yang memenuhi kriteria.', 'keprog_wb2', 'surat_masuk_35.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(36, '033/DISPORA/VIII/2026', '2026-08-03', 'Undangan Turnamen Olahraga Antar Pelajar Se-Kabupaten', 'Dinas Pemuda dan Olahraga', 'Wakil Kesiswaan WB 2 mendaftarkan dan melatih tim futsal & basket perwakilan sekolah.', 'kesiswaan_wb2', 'surat_masuk_36.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(37, '055/BPMP/VIII/2026', '2026-08-04', 'Pemberitahuan Pendampingan Implementasi Kurikulum Merdeka (IKM)', 'Balai Penjaminan Mutu Pendidikan', 'Wakil Kurikulum WB 2 ikuti bimbingan teknis penyusunan modul proyek penguatan P5.', 'wakakur_wb2', 'surat_masuk_37.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(38, '068/UNIVERSITAS-ID/VIII/2026', '2026-08-05', 'Sosialisasi Program Beasiswa Jalur Prestasi Perguruan Tinggi Vokasi', 'Universitas Indonesia Vokasi', 'Guru pendamping menginformasikan syarat pendaftaran beasiswa kepada siswa kelas XII.', 'guru2', 'surat_masuk_38.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(39, '079/DISNAKER/VIII/2026', '2026-08-06', 'Undangan Partisipasi Job Fair & Temu Bursa Kerja Khusus (BKK)', 'Dinas Tenaga Kerja', 'Staff WB 2 harap menggandakan berkas data alumni serta brosur informasi pendaftaran BKK.', 'staff_wb2', 'surat_masuk_39.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(40, '091/PJTKI-MITRA/VIII/2026', '2026-08-07', 'Penawaran Program Pemagangan Kerja Industri Ke Jepang & Korea', 'LPK Bina Mandiri Internasional', 'Kepala Program WB 2 pelajari usulan MoU magang luar negeri dan siapkan sosialisasi ke ortu.', 'keprog_wb2', 'surat_masuk_40.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(41, '108/BNNK/VIII/2026', '2026-08-08', 'Undangan Workshop Kader Remaja Anti Narkoba Di Lingkungan Sekolah', 'Badan Narkotika Nasional Kabupaten', 'Wakil Kesiswaan WB 2 mengirimkan 5 perwakilan pengurus OSIS & MPK sebagai peserta kader.', 'kesiswaan_wb2', 'surat_masuk_41.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(42, '122/MKKS-SMK/VIII/2026', '2026-08-08', 'Undangan Musyawarah Kerja Kepala Sekolah (MKKS) SMK Wilayah 2', 'MKKS SMK Wilayah 2', 'Kepala Sekolah WB 2 wajib hadir dalam rapat koordinasi persiapan Uji Kompetensi Keahlian.', 'kepsek_wb2', 'surat_masuk_42.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(43, '139/ERLANGGA/VIII/2026', '2026-08-09', 'Katalog Spesifikasi Buku Teks Utama & Pendamping Kurikulum Merdeka', 'Penerbit Erlangga', 'Wakil Kurikulum WB 2 lakukan telaah kebutuhan buku pelajaran bersama para Ketua Program.', 'wakakur_wb2', 'surat_masuk_43.pdf', 'wb_2', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(44, '003/DINAS-PDK/VIII/2026', '2026-08-01', 'Surat Edaran Pelaksanaan Evaluasi Diri Satuan Pendidikan (EDSP)', 'Dinas Pendidikan dan Kebudayaan', 'Kepala Sekolah WB 3 bentuk tim kerja EDSP dan supervisi keterisian instrumen instansi.', 'kepsek_wb3', 'surat_masuk_44.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(45, '021/MICROSOFT/VIII/2026', '2026-08-02', 'Undangan Web Seminar Transformasi Digital Dalam Pembelajaran Klasikal', 'Microsoft Indonesia Education', 'Guru WB 3 ditugaskan mengikuti webinar dan melakukan pengimbasan ke rekan guru lainnya.', 'guru3', 'surat_masuk_45.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(46, '038/KOMINFO/VIII/2026', '2026-08-03', 'Program Beasiswa Coding & Cybersecurity Literacy Bagi Pelajar', 'Kementerian Komunikasi dan Informatika', 'Kepala Program WB 3 daftarkan siswa berbakat di bidang IT ke sistem pendaftaran Kominfo.', 'keprog_wb3', 'surat_masuk_46.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(47, '049/HIJAU-IND/VIII/2026', '2026-08-04', 'Permohonan Kerjasama Program Gerakan Sekolah Adiwiyata & Bank Sampah', 'Yayasan Hijau Indonesia', 'Wakil Kesiswaan WB 3 susun alur kerja pemilahan sampah organik dan kelola green house.', 'kesiswaan_wb3', 'surat_masuk_47.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(48, '062/KECAMATAN/VIII/2026', '2026-08-05', 'Undangan Upacara Bendera Peringatan HUT Kemerdekaan Republik Indonesia', 'Kantor Kecamatan Setempat', 'Wakil Kesiswaan WB 3 instruksikan pengurus Paskibra & paduan suara hadir sesuai gladi bersih.', 'kesiswaan_wb3', 'surat_masuk_48.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(49, '077/PLN-PERSERO/VIII/2026', '2026-08-06', 'Pemberitahuan Pemeliharaan Jaringan Listrik & Pemadaman Sementara', 'PT PLN (Persero) ULP', 'Staff WB 3 berkoordinasi dengan teknisi genset untuk menyalakan listrik cadangan lab komputer.', 'staff_wb3', 'surat_masuk_49.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(50, '089/BAN-PDM/VIII/2026', '2026-08-07', 'Jadwal Pelaksanaan Asesmen Akreditasi Sekolah TP 2026/2027', 'Badan Akreditasi Nasional PDM', 'Wakil Kurikulum WB 3 lakukan pengecekan akhir unggahan dokumen pada aplikasi SISPENA.', 'wakakur_wb3', 'surat_masuk_50.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(51, '104/POLSEK/VIII/2026', '2026-08-08', 'Koordinasi Keamanan dan Ketertiban Lingkungan Sekitar Sekolah', 'Polsek Setempat', 'Kepala Sekolah WB 3 pimpin rapat koordinasi dengan petugas satpam dan kepala dusun.', 'kepsek_wb3', 'surat_masuk_51.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(52, '119/SIEMENS/VIII/2026', '2026-08-08', 'Hibah Perangkat Trainer Otomasi Industri & Software Simulasi Edukasi', 'PT Siemens Indonesia', 'Kepala Program WB 3 berkoordinasi teknis untuk proses pengiriman dan serah terima alat hibah.', 'keprog_wb3', 'surat_masuk_52.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(53, '133/KOMITE/VIII/2026', '2026-08-09', 'Undangan Rapat Pleno Komite Sekolah & Orang Tua Siswa Kelas X', 'Komite Sekolah WB 3', 'Staff WB 3 cetak dan distribusikan surat undangan kepada seluruh wali murid kelas X.', 'staff_wb3', 'surat_masuk_53.pdf', 'wb_3', '2026-08-09 12:52:06', '2026-08-09 12:59:04'),
(54, '001/DINAS-PDK/VIII/2026', '2026-08-01', 'Undangan Rapat Koordinasi Asesmen Nasional (ANBK)', 'Dinas Pendidikan Kabupaten', 'Mohon Waka Kurikulum menyiapkan data simulasi ANBK dan menghadiri rapat koordinasi dinas.', 'wakakur_wb1', 'surat_masuk_54.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 12:59:04'),
(55, '012/PRAMUKA-CAB/VIII/2026', '2026-08-02', 'Permohonan Izin & Keikutsertaan Kegiatan Kemah Bhakti Penggalang', 'Kwartir Cabang Pramuka', 'Disetujui. Mohon Wakil Kesiswaan mengoordinasikan persetujuan orang tua dan pembina pramuka.', 'kesiswaan_wb1', 'surat_masuk_55.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 12:59:04'),
(56, '045/TELKOM-IND/VIII/2026', '2026-08-03', 'Pemberitahuan Program Magang Industri & Sertifikasi Jaringan ICT', 'PT Telkom Indonesia Tbk', 'Kepala Program agar segera menyeleksi siswa kelas XII jurusan TJKT untuk kuota magang 15 orang.', 'keprog_wb1', 'surat_masuk_56.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 12:59:04'),
(57, '102/KEMDIKBUD/VIII/2026', '2026-08-05', 'Edaran Pedoman Penyusunan Kurikulum Operasional Satuan Pendidikan (KOSP)', 'Kementerian Pendidikan dan Kebudayaan', 'Waka Kurikulum bersama tim pengembang kurikulum menyusun draf KOSP TP 2026/2027.', 'wakakur_wb1', 'surat_masuk_57.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 12:59:04'),
(58, '115/PUSKESMAS/VIII/2026', '2026-08-06', 'Pemberitahuan Pelaksanaan Penjaringan Kesehatan & Imunisasi Remaja', 'Puskesmas Kecamatan', 'Wakil Kesiswaan mohon siapkan jadwal kelas dan mendampingi tim medis di aula sekolah.', 'kesiswaan_wb1', 'surat_masuk_58.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 12:59:04'),
(60, '134/YAYASAN-WB/VIII/2026', '2026-08-08', 'Undangan Rapat Evaluasi Kinerja Triwulan dan Program Kerja Unit Sekolah', 'Yayasan Contoh', 'Kepala Sekolah wajib menghadiri serta menyiapkan materi pemaparan rekapitulasi capaian unit WB 1.', 'kepsek_wb1', 'surat_masuk_60.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 12:59:04'),
(61, '140/ASTRA-HONDA/VIII/2026', '2026-08-08', 'Persetujuan Kerjasama Bengkel Mitra & Pelaksanaan Uji Kompetensi Keahlian', 'PT Astra Honda Mobil', 'Kepala Program mohon siapkan sarana laboratorium bengkel untuk proses verifikasi kelayakan.', 'keprog_wb1', 'surat_masuk_61.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 15:33:26'),
(62, '155/POLRES/VIII/2026', '2026-08-09', 'Himbauan Sosialisasi Tertib Lalu Lintas & Pencegahan Kenakalan Remaja', 'Polsek Kecamatan', 'Kesiswaan atur jadwal pembina upacara Senin oleh Satlantas Polres serta sosialisasi keselamatan.', 'kesiswaan_wb1', 'surat_1786289405_6a789cfdd1367.pdf', 'wb_1', '2026-08-09 12:52:47', '2026-08-09 15:30:06'),
(63, '155/KABUPATEN/VIII/2026', '2026-08-12', 'Undangan Rapat Seleksi', 'Polres Kabupaten', 'Satlantas Polres serta sosialisasi keselamatan.', 'staff_wb1', 'surat_1786289645_6a789deda0915.pdf', 'wb_1', '2026-08-09 15:32:52', '2026-08-09 15:34:05'),
(64, '155/KOTA/VIII/2026', '2026-08-10', 'Undangan Rapat', 'Polda', 'Satlantas Polda serta sosialisasi keselamatan.', 'staff_wb1', 'surat_1786295600_6a78b530b201f.pdf', 'wb_1', '2026-08-09 17:13:20', NULL),
(65, '002/SM/XI/2027', '2026-08-20', 'Undangan Rapat', 'sekolah contoh', 'rapat undangan guru guru', 'guru1', 'surat_1787120975_6a854d4fc8435.pdf', 'wb_1', '2026-08-09 17:19:39', '2026-08-19 06:29:35'),
(66, '003/SM/XI/2026', '2026-08-16', 'fgh3', 'jakarta museum', 'fgh', 'kepsek_wb1', 'surat_1786817009_6a80a9f18ca28.pdf', 'wb_1', '2026-08-15 18:03:29', '2026-08-19 06:29:01'),
(67, '021/SM/XI/2026', '2026-08-19', 'ghfghf', 'hgfhfgh', 'fghfh', 'kepsek_wb1', 'surat_1787121713_6a85503105c2c.pdf', 'wb_1', '2026-08-19 06:41:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `unit_sekolah`
--

CREATE TABLE `unit_sekolah` (
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `secret_qr` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `unit_sekolah`
--

INSERT INTO `unit_sekolah` (`kode`, `nama`, `aktif`, `secret_qr`) VALUES
('wb_1', 'Unit 1', 1, 'CHANGE_ME_UNIT1'),
('wb_2', 'Unit 2', 1, 'CHANGE_ME_UNIT2'),
('wb_3', 'Unit 3', 1, 'CHANGE_ME_UNIT3');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `unit_sekolah` varchar(10) DEFAULT NULL,
  `level` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama`, `unit_sekolah`, `level`) VALUES
(1, 'admin_wb1', 'demo1234', 'Admin WB 1', 'wb_1', 'admin'),
(2, 'guru1', 'demo1234', 'Guru WB 1', 'wb_1', 'guru'),
(3, 'staff_wb1', 'demo1234', 'Staff', 'wb_1', 'staff'),
(4, 'kepsek_wb1', 'demo1234', 'Kepala Sekolah', 'wb_1', 'kepsek'),
(5, 'wakakur_wb1', 'demo1234', 'Wakil Kurikulum', 'wb_1', 'waka_kurikulum'),
(6, 'kesiswaan_wb1', 'demo1234', 'Wakil Kesiswaan', 'wb_1', 'kesiswaan'),
(7, 'keprog_wb1', 'demo1234', 'Kepala Program', 'wb_1', 'keprog'),
(8, 'keuangan', 'demo1234', 'Staff Keuangan', NULL, 'keuangan'),
(9, 'yayasan', 'demo1234', 'Ketua Yayasan', NULL, 'yayasan'),
(10, 'pembina_yayasan', 'demo1234', 'Pembina Yayasan', NULL, 'pembina_yayasan'),
(11, 'guru2', 'demo1234', 'Guru WB 2', 'wb_2', 'guru'),
(12, 'admin_wb2', 'demo1234', 'Admin WB 2', 'wb_2', 'admin'),
(13, 'admin_wb3', 'demo1234', 'Admin WB 3', 'wb_3', 'admin'),
(14, 'admin_yayasan', 'demo1234', 'Admin Yayasan', NULL, 'admin'),
(15, 'staff_wb2', 'demo1234', 'Staff WB 2', 'wb_2', 'staff'),
(16, 'kepsek_wb2', 'demo1234', 'Kepala Sekolah WB 2', 'wb_2', 'kepsek'),
(17, 'wakakur_wb2', 'demo1234', 'Wakil Kurikulum WB 2', 'wb_2', 'waka_kurikulum'),
(18, 'kesiswaan_wb2', 'demo1234', 'Wakil Kesiswaan WB 2', 'wb_2', 'kesiswaan'),
(19, 'keprog_wb2', 'demo1234', 'Kepala Program WB 2', 'wb_2', 'keprog'),
(20, 'guru3', 'demo1234', 'Guru WB 3', 'wb_3', 'guru'),
(21, 'staff_wb3', 'demo1234', 'Staff WB 3', 'wb_3', 'staff'),
(22, 'kepsek_wb3', 'demo1234', 'Kepala Sekolah WB 3', 'wb_3', 'kepsek'),
(23, 'wakakur_wb3', 'demo1234', 'Wakil Kurikulum WB 3', 'wb_3', 'waka_kurikulum'),
(24, 'kesiswaan_wb3', 'demo1234', 'Wakil Kesiswaan WB 3', 'wb_3', 'kesiswaan'),
(25, 'keprog_wb3', 'demo1234', 'Kepala Program WB 3', 'wb_3', 'keprog'),
(26, 'keuangan_wb1', 'demo1234', 'Staff Keuangan WB 1', 'wb_1', 'keuangan'),
(27, 'keuangan_wb2', 'demo1234', 'Staff Keuangan WB 2', 'wb_2', 'keuangan'),
(28, 'keuangan_wb3', 'demo1234', 'Staff Keuangan WB 3', 'wb_3', 'keuangan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kpi_evaluations`
--
ALTER TABLE `kpi_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_user_ind_period` (`user_id`,`indicator_id`,`periode`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_indicator_id` (`indicator_id`),
  ADD KEY `idx_target_role` (`target_role`),
  ADD KEY `idx_periode` (`periode`),
  ADD KEY `idx_unit_sekolah` (`unit_sekolah`);

--
-- Indexes for table `kpi_indicators`
--
ALTER TABLE `kpi_indicators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_perspective_id` (`perspective_id`),
  ADD KEY `idx_target_role` (`target_role`),
  ADD KEY `idx_unit_sekolah` (`unit_sekolah`);

--
-- Indexes for table `kpi_perspectives`
--
ALTER TABLE `kpi_perspectives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_target_role` (`target_role`),
  ADD KEY `idx_unit_sekolah` (`unit_sekolah`);

--
-- Indexes for table `proposal_kegiatan`
--
ALTER TABLE `proposal_kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pengaju_id` (`pengaju_id`),
  ADD KEY `fk_proposal_unit_sekolah` (`unit_sekolah`),
  ADD KEY `fk_proposal_val_kepsek` (`validasi_kepsek_by`),
  ADD KEY `fk_proposal_val_ketua_yayasan` (`validasi_ketua_yayasan_by`),
  ADD KEY `fk_proposal_val_keuangan` (`validasi_keuangan_by`);

--
-- Indexes for table `proposal_revisi`
--
ALTER TABLE `proposal_revisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposal_id` (`proposal_id`),
  ADD KEY `idx_revisi_oleh` (`revisi_oleh`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_kategori_id` (`kategori_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_surat_keluar_unit_sekolah` (`unit_sekolah`);

--
-- Indexes for table `surat_keluar_kategori`
--
ALTER TABLE `surat_keluar_kategori`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kode` (`kode`),
  ADD KEY `fk_surat_keluar_kategori_unit_sekolah` (`unit_sekolah`);

--
-- Indexes for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_nomor_surat_unit` (`nomor_surat`) USING BTREE,
  ADD KEY `idx_aktor_tujuan` (`aktor_tujuan`),
  ADD KEY `fk_surat_masuk_unit_sekolah` (`unit_sekolah`);

--
-- Indexes for table `unit_sekolah`
--
ALTER TABLE `unit_sekolah`
  ADD PRIMARY KEY (`kode`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uniq_username` (`username`),
  ADD KEY `fk_users_unit_sekolah` (`unit_sekolah`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kpi_evaluations`
--
ALTER TABLE `kpi_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `kpi_indicators`
--
ALTER TABLE `kpi_indicators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `kpi_perspectives`
--
ALTER TABLE `kpi_perspectives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `proposal_kegiatan`
--
ALTER TABLE `proposal_kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `proposal_revisi`
--
ALTER TABLE `proposal_revisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `surat_keluar_kategori`
--
ALTER TABLE `surat_keluar_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kpi_evaluations`
--
ALTER TABLE `kpi_evaluations`
  ADD CONSTRAINT `fk_kpi_eval_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `kpi_indicators` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kpi_eval_unit` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kpi_eval_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kpi_indicators`
--
ALTER TABLE `kpi_indicators`
  ADD CONSTRAINT `fk_kpi_ind_perspective` FOREIGN KEY (`perspective_id`) REFERENCES `kpi_perspectives` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_kpi_ind_unit` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kpi_perspectives`
--
ALTER TABLE `kpi_perspectives`
  ADD CONSTRAINT `fk_kpi_pers_unit` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `proposal_kegiatan`
--
ALTER TABLE `proposal_kegiatan`
  ADD CONSTRAINT `fk_proposal_pengaju` FOREIGN KEY (`pengaju_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_proposal_unit_sekolah` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `proposal_revisi`
--
ALTER TABLE `proposal_revisi`
  ADD CONSTRAINT `fk_revisi_oleh` FOREIGN KEY (`revisi_oleh`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_revisi_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal_kegiatan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `surat_keluar`
--
ALTER TABLE `surat_keluar`
  ADD CONSTRAINT `fk_surat_keluar_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `surat_keluar_kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_surat_keluar_unit_sekolah` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_surat_keluar_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `surat_keluar_kategori`
--
ALTER TABLE `surat_keluar_kategori`
  ADD CONSTRAINT `fk_surat_keluar_kategori_unit_sekolah` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD CONSTRAINT `fk_surat_masuk_aktor` FOREIGN KEY (`aktor_tujuan`) REFERENCES `users` (`username`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_surat_masuk_unit_sekolah` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_unit_sekolah` FOREIGN KEY (`unit_sekolah`) REFERENCES `unit_sekolah` (`kode`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
