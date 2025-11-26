-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 08:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pkl`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id_keluar` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `tujuan` varchar(25) NOT NULL,
  `nm_barang` varchar(25) NOT NULL,
  `jumlah` varchar(15) NOT NULL,
  `bg` varchar(15) NOT NULL,
  `tgl_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  `jenis` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_keluar`
--

INSERT INTO `barang_keluar` (`id_keluar`, `id_stok`, `tujuan`, `nm_barang`, `jumlah`, `bg`, `tgl_kirim`, `jenis`) VALUES
(12, 2, 'PBM 3', 'Malindo 8201 ', '200', 'BG8733NQ', '2025-04-25 09:56:53', NULL),
(13, 3, 'Limau', 'Malindo 9203 AA', '200', 'BG8732NQ', '2025-04-25 10:23:33', NULL),
(14, 7, 'SKM', 'Gold Extra BR1 Med', '200', 'BG8978IH', '2025-04-25 12:27:18', NULL),
(15, 2, 'Afat', 'Malindo 8201 ', '100', 'BG8732NQ', '2025-04-25 12:28:16', NULL),
(16, 7, 'Suak', 'Gold Extra BR1 Med', '250', 'BG8027AH', '2025-04-25 12:49:45', NULL),
(17, 3, 'SD1', 'Malindo 9203 AA', '250', 'BG8941NX', '2025-04-25 13:07:06', NULL),
(18, 8, 'SS', 'Gold Extra BR1 CC', '200', 'BG8027AH', '2025-04-26 02:01:49', NULL),
(19, 8, 'SS', 'Gold Extra BR1 CC', '200', 'BG8941NX', '2025-04-26 02:01:55', NULL),
(20, 8, 'SS', 'Gold Extra BR1 CC', '200', 'BG8732NQ', '2025-04-26 02:02:18', NULL),
(21, 6, 'CV.KURNIA ARGUNG', '611s-K', '160', 'BG8733NQ', '2025-04-26 02:47:37', NULL),
(22, 5, 'PBM 4', '612s', '200', 'BG8941NX', '2025-04-26 04:17:26', NULL),
(23, 5, 'SJ', '612s', '250', 'BG8027AH', '2025-04-26 05:01:16', NULL),
(24, 2, 'PSM', 'Malindo 8201 ', '100', 'BG8978IH', '2025-04-26 05:33:36', NULL),
(25, 6, 'PSM', '611s-K', '100', 'BG8978IH', '2025-04-26 07:01:03', NULL),
(26, 2, 'Talang Buluh', 'Malindo 8201 ', '150', 'BG8978IH', '2025-04-26 07:26:25', NULL),
(27, 9, 'SJ', 'Farm Chick', '250', 'BG8941NX', '2025-04-28 01:43:58', NULL),
(28, 8, 'KK', 'Gold Extra BR1 CC', '250', 'BG8027AH', '2025-04-28 02:35:00', NULL),
(29, 8, 'CV.KURNIA ARGUNG | BELITA', 'Gold Extra BR1 CC', '800', 'BG8941NX', '2025-04-28 02:37:13', NULL),
(30, 10, 'Ly.AA', 'Egg Max', '200', 'BG8732NQ', '2025-04-28 08:18:24', NULL),
(31, 10, 'Ly.AA', 'Egg Max', '100', 'BG8978IH', '2025-04-28 08:35:21', NULL),
(32, 10, 'Ly. ASP', 'Egg Max', '200', 'BG8027AH', '2025-04-28 08:39:21', NULL),
(33, 3, 'PBM 1 ', 'Malindo 9203 AA', '200', 'BG8732NQ', '2025-04-29 02:16:53', NULL),
(34, 4, 'SJ', '611s', '200', 'BG8732NQ', '2025-04-29 02:41:29', NULL),
(35, 10, 'Ly. ASP', 'Egg Max', '100', 'BG8978IH', '2025-04-29 04:17:31', NULL),
(36, 6, 'SJ', '611s-K', '250', 'BG8941NX', '2025-05-05 08:05:26', NULL),
(37, 2, 'PSM', 'Malindo 8201 ', '200', 'BG8978IH', '2025-05-05 11:06:09', NULL),
(38, 2, 'Suak', 'Malindo 8201 ', '150', 'BG8733NQ', '2025-05-07 03:35:07', NULL),
(39, 1, 'PSM', 'Malindo 8202 AA', '100', 'BG8027AH', '2025-05-07 05:31:31', NULL),
(40, 4, 'PSM', '611s', '130', 'BG8027AH', '2025-05-07 05:31:31', NULL),
(42, 1, 'SBA', 'Malindo 8202 AA', '100', 'BG8941NX', '2025-05-07 05:56:43', NULL),
(43, 3, 'SBA', 'Malindo 9203 AA', '120', 'BG8941NX', '2025-05-07 05:56:43', NULL),
(45, 3, 'SJ', 'Malindo 9203 AA', '150', 'BG8027AH', '2025-05-07 07:54:50', NULL),
(46, 7, 'SJ', 'Gold Extra BR1 Med', '100', 'BG8027AH', '2025-05-07 07:54:50', NULL),
(48, 2, 'SD 1', 'Malindo 8201 ', '100', 'BG8733NQ', '2025-05-07 08:19:46', NULL),
(49, 6, 'SD 1', '611s-K', '100', 'BG8733NQ', '2025-05-07 08:19:46', NULL),
(50, 1, 'Suak', 'Malindo 8202 AA', '100', 'BG8732NQ', '2025-05-12 02:09:16', NULL),
(51, 4, 'Suak', '611s', '100', 'BG8732NQ', '2025-05-12 02:09:16', NULL),
(53, 2, 'PSM', 'Malindo 8201 ', '100', 'BG8941NX', '2025-05-12 03:09:04', NULL),
(54, 6, 'PSM', '611s-K', '100', 'BG8941NX', '2025-05-12 03:09:04', NULL),
(55, 1, 'PSM', 'Malindo 8202 AA', '50', 'BG8941NX', '2025-05-12 03:09:04', NULL),
(56, 1, 'Afat', 'Malindo 8202 AA', '30', 'BG8941NX', '2025-05-13 02:15:01', NULL),
(57, 3, 'Afat', 'Malindo 9203 AA', '140', 'BG8941NX', '2025-05-13 02:15:01', NULL),
(58, 8, 'Afat', 'Gold Extra BR1 CC', '30', 'BG8941NX', '2025-05-13 02:15:01', NULL),
(62, 2, 'PBM 3', 'Malindo 8201 ', '100', 'BG8027AH', '2025-05-14 02:57:49', NULL),
(63, 6, 'PBM 3', '611s-K', '100', 'BG8027AH', '2025-05-14 02:57:49', NULL),
(64, 4, 'PBM 3', '611s', '50', 'BG8027AH', '2025-05-14 02:57:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `id_masuk` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `nm_barang` varchar(25) NOT NULL,
  `tgl_muat` date NOT NULL,
  `tgl_produksi` date NOT NULL,
  `jenis` varchar(25) NOT NULL,
  `jumlah` varchar(15) NOT NULL,
  `bg` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_masuk`
--

INSERT INTO `barang_masuk` (`id_masuk`, `id_stok`, `nm_barang`, `tgl_muat`, `tgl_produksi`, `jenis`, `jumlah`, `bg`) VALUES
(12, 2, 'Malindo 8201 ', '2025-05-05', '0000-00-00', 'Pre-Starter', '840', 'BG 8725 NQ'),
(13, 4, '611s', '2025-05-05', '0000-00-00', 'Starter', '100', 'BG 8027 AH'),
(14, 7, 'Gold Extra BR1 Med', '2025-05-05', '0000-00-00', 'Starter', '500', 'BE 9780 AUB'),
(15, 10, 'Egg Max', '2025-05-03', '2025-04-30', 'Layer Feed', '500', 'BG8669NP'),
(16, 10, 'Egg Max', '2025-05-03', '2025-04-30', 'Layer Feed', '500', 'BG 8660 NQ'),
(17, 8, 'Gold Extra BR1 CC', '2025-05-10', '2025-05-06', 'Finisher', '500', 'BG 8601 NQ'),
(18, 8, 'Gold Extra BR1 CC', '2025-05-10', '2025-05-07', 'Finisher', '840', 'BG 8408 OA');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `iduser` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(25) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`iduser`, `username`, `password`, `role`) VALUES
(1, 'prayoga', '13245', 'admin1'),
(3, 'kgudang', '13245', 'gudang1'),
(4, 'logistik', '13245', 'logistik1'),
(5, 'supir1', '13245', 'supir'),
(6, 'supir2', '13245', 'supir'),
(7, 'supir3', '13245', 'supir'),
(8, 'supir4', '13245', 'supir'),
(9, 'supir5', '13245', 'supir');

-- --------------------------------------------------------

--
-- Table structure for table `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id_pengiriman` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `tujuan` varchar(100) NOT NULL,
  `nm_barang` varchar(100) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `jumlah` varchar(10) NOT NULL,
  `status` enum('menunggu_konfirmasi','diterima','ditolak','pembuatan_surat_jalan','dalam_proses_muat','berhasil_dikirim','ditolak_supir') DEFAULT 'menunggu_konfirmasi',
  `tanggal_pengiriman` timestamp NOT NULL DEFAULT current_timestamp(),
  `dokumen_ttd` varchar(255) DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `plat_supir` varchar(20) DEFAULT NULL,
  `username_supir` varchar(50) DEFAULT NULL,
  `nama_supir` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_tonase` decimal(10,2) DEFAULT 0.00,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengiriman`
--

INSERT INTO `pengiriman` (`id_pengiriman`, `id_stok`, `tujuan`, `nm_barang`, `jenis`, `jumlah`, `status`, `tanggal_pengiriman`, `dokumen_ttd`, `waktu_selesai`, `plat_supir`, `username_supir`, `nama_supir`, `created_at`, `updated_at`, `total_tonase`, `catatan`) VALUES
(1, 0, 'PBM 1 ', 'Malindo 8201 ', 'Pre-Starter', '0', 'ditolak', '2025-04-22 04:26:04', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(4, 0, 'SJ', '611s-K', 'Pre-Starter', '', 'ditolak', '2025-04-22 07:55:08', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(5, 0, 'SJ', 'Gold Extra BR1 Med', 'Starter', '200', 'ditolak', '2025-04-22 07:57:20', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(6, 0, 'PBM 1 ', '611s-K', 'Pre-Starter', '200', 'ditolak', '2025-04-22 08:25:36', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(7, 4, 'Limau', '611s', 'Starter', '200', 'ditolak', '2025-04-24 04:43:19', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(8, 4, 'Suak', '611s', 'Starter', '200', 'ditolak', '2025-04-24 06:41:48', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(9, 2, 'Simpur', 'Malindo 8201 ', 'Pre-Starter', '200', 'pembuatan_surat_jalan', '2025-04-24 06:42:08', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(10, 2, 'PBM 2', 'Malindo 8201 ', 'Pre-Starter', '250', 'ditolak', '2025-04-24 06:53:35', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(11, 2, 'PBM 3', 'Malindo 8201 ', 'Pre-Starter', '250', 'ditolak', '2025-04-24 06:53:45', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(12, 2, 'SJ', 'Malindo 8201 ', 'Pre-Starter', '250', 'pembuatan_surat_jalan', '2025-04-24 06:53:53', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(13, 3, 'SR', 'Malindo 9203 AA', 'Finisher', '200', 'ditolak', '2025-04-25 01:30:21', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(14, 3, 'SB', 'Malindo 9203 AA', 'Finisher', '250', 'ditolak', '2025-04-25 01:30:32', NULL, NULL, NULL, NULL, NULL, '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(15, 1, 'Talang Buluh', 'Malindo 8202 AA', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 08:38:37', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(16, 8, 'Talang Ilir', 'Gold Extra BR1 CC', 'Finisher', '200', 'pembuatan_surat_jalan', '2025-04-25 08:39:08', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(17, 1, 'ASM', 'Malindo 8202 AA', 'Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 08:50:04', NULL, NULL, 'BG8978IH', NULL, 'Supir D', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(18, 1, 'PSM', 'Malindo 8202 AA', 'Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 08:51:46', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(19, 1, 'PSM', 'Malindo 8202 AA', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 08:51:56', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(20, 1, 'SJ', 'Malindo 8202 AA', 'Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 08:59:51', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(21, 1, 'PBM 1 ', 'Malindo 8202 AA', 'Starter', '500', 'pembuatan_surat_jalan', '2025-04-25 08:59:57', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(22, 5, 'PBM 1 ', '612s', 'Finisher', '200', 'pembuatan_surat_jalan', '2025-04-25 09:03:42', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(23, 8, 'Limau', 'Gold Extra BR1 CC', 'Finisher', '250', 'pembuatan_surat_jalan', '2025-04-25 09:03:51', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(24, 1, 'SBA', 'Malindo 8202 AA', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 09:12:16', NULL, NULL, 'BG8027AH', NULL, 'Supir E', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(25, 7, 'PSM', 'Gold Extra BR1 Med', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 09:12:28', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(26, 1, 'PBM 1 ', 'Malindo 8202 AA', 'Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 09:33:47', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(27, 7, 'PBM 1 ', 'Gold Extra BR1 Med', 'Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 09:33:56', NULL, NULL, 'BG8733NQ', NULL, 'Supir B', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(28, 7, 'PBM 1 ', 'Gold Extra BR1 Med', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 09:39:55', NULL, NULL, 'BG8027AH', NULL, 'Supir E', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(29, 8, 'PBM 1 ', 'Gold Extra BR1 CC', 'Finisher', '250', 'pembuatan_surat_jalan', '2025-04-25 09:40:01', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(30, 8, 'SD1', 'Gold Extra BR1 CC', 'Finisher', '250', 'pembuatan_surat_jalan', '2025-04-25 09:47:49', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(31, 7, 'SR', 'Gold Extra BR1 Med', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 09:48:06', NULL, NULL, 'BG8027AH', NULL, 'Supir E', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(32, 1, 'Kemang', 'Malindo 8202 AA', 'Starter', '250', 'pembuatan_surat_jalan', '2025-04-25 09:48:16', NULL, NULL, 'BG8941NX', NULL, 'Supir C', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(33, 2, 'PBM 3', 'Malindo 8201 ', 'Pre-Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 09:53:07', NULL, NULL, 'BG8733NQ', NULL, 'Supir B', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(34, 6, 'PBM 4', '611s-K', 'Pre-Starter', '200', 'pembuatan_surat_jalan', '2025-04-25 09:53:23', NULL, NULL, 'BG8978IH', NULL, 'Supir D', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(35, 2, 'Afat', 'Malindo 8201 ', 'Pre-Starter', '100', 'pembuatan_surat_jalan', '2025-04-25 10:04:43', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 12:28:16', 0.00, NULL),
(36, 3, 'Limau', 'Malindo 9203 AA', 'Finisher', '200', 'pembuatan_surat_jalan', '2025-04-25 10:21:20', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-25 10:43:40', '2025-04-25 10:43:40', 0.00, NULL),
(37, 7, 'SKM', '7', '7', '200', 'pembuatan_surat_jalan', '2025-04-25 12:07:22', NULL, NULL, 'BG8978IH', NULL, 'Supir D', '2025-04-25 12:07:22', '2025-04-25 12:27:18', 0.00, NULL),
(38, 7, 'Suak', '7', '7', '250', 'pembuatan_surat_jalan', '2025-04-25 12:48:33', NULL, NULL, 'BG8027AH', NULL, 'Supir E', '2025-04-25 12:48:33', '2025-04-25 12:49:45', 0.00, NULL),
(39, 3, 'SD1', '3', '3', '250', 'pembuatan_surat_jalan', '2025-04-25 13:06:51', NULL, NULL, 'BG8941NX', NULL, 'supir3', '2025-04-25 13:06:51', '2025-04-25 13:07:06', 0.00, NULL),
(40, 5, 'SS', '5', '5', '200', 'ditolak', '2025-04-26 01:58:41', NULL, NULL, NULL, NULL, NULL, '2025-04-26 01:58:41', '2025-04-26 02:57:05', 0.00, NULL),
(41, 8, 'SS', '8', '8', '200', 'pembuatan_surat_jalan', '2025-04-26 01:58:49', NULL, NULL, 'BG8732NQ', NULL, 'Supir A', '2025-04-26 01:58:49', '2025-04-26 02:02:18', 0.00, NULL),
(42, 5, 'Simpur', '5', '5', '250', 'ditolak', '2025-04-26 02:12:30', NULL, NULL, NULL, NULL, NULL, '2025-04-26 02:12:30', '2025-04-26 02:57:01', 0.00, NULL),
(43, 5, 'Limau', '5', '5', '250', 'ditolak', '2025-04-26 02:21:16', NULL, NULL, NULL, NULL, NULL, '2025-04-26 02:21:16', '2025-04-26 02:56:57', 0.00, NULL),
(44, 6, 'CV.KURNIA ARGUNG', '6', '6', '160', 'pembuatan_surat_jalan', '2025-04-26 02:44:59', NULL, NULL, 'BG8733NQ', NULL, 'Supir B', '2025-04-26 02:44:59', '2025-04-26 02:47:37', 0.00, NULL),
(45, 5, 'PBM 4', '5', '5', '200', '', '2025-04-26 04:16:03', 'signed_1745650503.pdf', '2025-04-26 13:55:03', 'BG8941NX', 'supir3', 'Supir C', '2025-04-26 04:16:03', '2025-04-26 06:55:03', 0.00, NULL),
(46, 5, 'SJ', '5', '5', '250', 'berhasil_dikirim', '2025-04-26 05:00:41', 'signed_1745650833.pdf', '2025-04-26 14:00:33', 'BG8027AH', 'supir5', 'Supir E', '2025-04-26 05:00:41', '2025-04-26 07:00:33', 0.00, NULL),
(47, 2, 'PSM', '2', '2', '100', 'berhasil_dikirim', '2025-04-26 05:32:18', 'signed_1745650748.pdf', '2025-04-26 13:59:08', 'BG8978IH', 'supir4', 'Supir D', '2025-04-26 05:32:18', '2025-04-26 06:59:08', 0.00, NULL),
(48, 6, 'PSM', '6', '6', '100', 'berhasil_dikirim', '2025-04-26 05:32:32', 'signed_1745652174.pdf', '2025-04-26 14:22:54', 'BG8978IH', 'supir4', 'Supir D', '2025-04-26 05:32:32', '2025-04-26 07:22:54', 0.00, NULL),
(49, 1, 'PSM', '1', '1', '250', 'ditolak', '2025-04-26 05:32:41', NULL, NULL, NULL, NULL, NULL, '2025-04-26 05:32:41', '2025-04-26 07:21:39', 0.00, NULL),
(50, 2, 'Talang Buluh', '2', '2', '150', 'dalam_proses_muat', '2025-04-26 07:24:52', NULL, NULL, 'BG8978IH', 'supir4', 'Supir D', '2025-04-26 07:24:52', '2025-04-26 07:27:05', 0.00, NULL),
(51, 9, 'SJ', '9', '9', '250', 'dalam_proses_muat', '2025-04-28 01:43:36', NULL, NULL, 'BG8941NX', 'supir3', 'Supir C', '2025-04-28 01:43:36', '2025-04-28 01:46:14', 0.00, NULL),
(52, 8, 'SD1', '8', '8', '250', 'ditolak', '2025-04-28 02:34:14', NULL, NULL, NULL, NULL, NULL, '2025-04-28 02:34:14', '2025-04-28 02:35:28', 0.00, NULL),
(53, 8, 'Pulau 1', '8', '8', '250', 'ditolak', '2025-04-28 02:34:27', NULL, NULL, NULL, NULL, NULL, '2025-04-28 02:34:27', '2025-04-28 02:35:23', 0.00, NULL),
(54, 8, 'KK', '8', '8', '250', 'dalam_proses_muat', '2025-04-28 02:34:45', NULL, NULL, 'BG8027AH', 'supir5', 'Supir E', '2025-04-28 02:34:45', '2025-04-28 02:36:03', 0.00, NULL),
(55, 8, 'CV.KURNIA ARGUNG | BELITANG', '8', '8', '800', '', '2025-04-28 02:36:37', NULL, NULL, 'BG8941NX', 'supir3', 'Supir C', '2025-04-28 02:36:37', '2025-04-28 07:07:20', 0.00, NULL),
(56, 10, 'Ly.AA', '10', '10', '160', '', '2025-04-28 04:09:54', NULL, NULL, NULL, NULL, NULL, '2025-04-28 04:09:54', '2025-04-28 07:44:06', 0.00, NULL),
(57, 3, 'Pulau 1', '3', '3', '180', '', '2025-04-28 07:26:02', NULL, NULL, NULL, NULL, NULL, '2025-04-28 07:26:02', '2025-04-28 07:44:14', 0.00, NULL),
(58, 10, 'Ly. ASP', '10', '10', '160', 'ditolak', '2025-04-28 07:45:05', NULL, NULL, NULL, NULL, NULL, '2025-04-28 07:45:05', '2025-04-28 07:45:16', 0.00, NULL),
(59, 10, 'Ly.AA', '10', '10', '200', 'ditolak', '2025-04-28 07:49:12', NULL, NULL, NULL, NULL, NULL, '2025-04-28 07:49:12', '2025-04-28 07:54:46', 0.00, NULL),
(60, 10, 'Ly. ASP', '10', '10', '200', 'ditolak', '2025-04-28 07:55:19', NULL, NULL, NULL, NULL, NULL, '2025-04-28 07:55:19', '2025-04-28 08:06:52', 0.00, NULL),
(61, 10, 'Ly.AA', '10', '10', '200', 'berhasil_dikirim', '2025-04-28 08:07:07', 'signed_1745828330.pdf', '2025-04-28 15:18:50', 'BG8732NQ', 'supir1', 'Supir A', '2025-04-28 08:07:07', '2025-04-28 08:18:50', 0.00, NULL),
(62, 10, 'Ly.AA', '10', '10', '100', 'ditolak_supir', '2025-04-28 08:35:06', NULL, NULL, 'BG8978IH', 'supir4', 'Supir D', '2025-04-28 08:35:06', '2025-04-28 08:37:57', 0.00, NULL),
(63, 10, 'Ly. ASP', '10', '10', '200', 'berhasil_dikirim', '2025-04-28 08:39:03', 'signed_1745829622.pdf', '2025-04-28 15:40:22', 'BG8027AH', 'supir5', 'Supir E', '2025-04-28 08:39:03', '2025-04-28 08:40:22', 0.00, NULL),
(64, 3, 'PBM 1 ', '3', '3', '200', 'dalam_proses_muat', '2025-04-29 02:16:29', NULL, NULL, 'BG8732NQ', 'supir1', 'Supir A', '2025-04-29 02:16:29', '2025-04-29 02:17:29', 0.00, NULL),
(65, 4, 'SJ', '4', '4', '200', 'berhasil_dikirim', '2025-04-29 02:40:54', 'signed_1745894626.pdf', '2025-04-29 09:43:46', 'BG8732NQ', 'supir1', 'Supir A', '2025-04-29 02:40:54', '2025-04-29 02:43:46', 0.00, NULL),
(66, 10, 'Ly. ASP', '10', '10', '100', 'berhasil_dikirim', '2025-04-29 04:15:35', 'signed_1745900287.pdf', '2025-04-29 11:18:07', 'BG8978IH', 'supir4', 'Supir D', '2025-04-29 04:15:35', '2025-04-29 04:18:07', 0.00, NULL),
(67, 6, 'SJ', '6', '6', '250', 'berhasil_dikirim', '2025-05-05 08:05:08', 'signed_1746432370.pdf', '2025-05-05 15:06:10', 'BG8941NX', 'supir3', 'Supir C', '2025-05-05 08:05:08', '2025-05-05 08:06:10', 0.00, NULL),
(68, 2, 'PSM', '2', '2', '200', 'berhasil_dikirim', '2025-05-05 10:59:14', 'signed_1746443667.pdf', '2025-05-05 18:14:27', 'BG8978IH', 'supir4', 'Supir D', '2025-05-05 10:59:14', '2025-05-05 11:14:27', 0.00, NULL),
(69, 0, 'PBM 1 ', '', '', '', 'menunggu_konfirmasi', '2025-05-06 12:14:37', NULL, NULL, NULL, NULL, NULL, '2025-05-06 12:14:37', '2025-05-06 12:14:37', 0.00, NULL),
(70, 0, 'asd', '', '', '', 'menunggu_konfirmasi', '2025-05-07 01:37:55', NULL, NULL, NULL, NULL, NULL, '2025-05-07 01:37:55', '2025-05-07 01:37:55', 0.00, NULL),
(71, 0, 'Simpur', '', '', '', 'menunggu_konfirmasi', '2025-05-07 03:19:39', NULL, NULL, NULL, NULL, NULL, '2025-05-07 03:19:39', '2025-05-07 03:19:39', 0.00, NULL),
(72, 0, 'Limau', '', '', '', 'menunggu_konfirmasi', '2025-05-07 03:31:10', NULL, NULL, NULL, NULL, NULL, '2025-05-07 03:31:10', '2025-05-07 03:31:10', 0.00, NULL),
(73, 2, 'Suak', '2', '2', '150', 'berhasil_dikirim', '2025-05-07 03:34:43', 'signed_1746588954.pdf', '2025-05-07 10:35:54', 'BG8733NQ', 'supir2', 'Supir B', '2025-05-07 03:34:43', '2025-05-07 03:35:54', 0.00, NULL),
(74, 0, 'Limau', '', '', '', 'menunggu_konfirmasi', '2025-05-07 03:53:27', NULL, NULL, NULL, NULL, NULL, '2025-05-07 03:53:27', '2025-05-07 03:53:27', 0.00, NULL),
(75, 0, 'PBM 1 ', '', '', '', 'ditolak', '2025-05-07 04:31:36', NULL, NULL, NULL, NULL, NULL, '2025-05-07 04:31:36', '2025-05-07 05:26:38', 0.00, NULL),
(76, 0, 'PSM', '', '', '', 'berhasil_dikirim', '2025-05-07 04:56:23', 'signed_1746597161.pdf', '2025-05-07 12:52:41', 'BG8027AH', 'supir5', 'Supir E', '2025-05-07 04:56:23', '2025-05-07 05:52:41', 0.00, NULL),
(77, 0, 'Suak', 'Malindo 8202 AA', 'Starter', '100', 'berhasil_dikirim', '2025-05-07 05:08:21', 'signed_1747015894.pdf', '2025-05-12 09:11:34', 'BG8732NQ', 'supir1', 'Ujang', '2025-05-07 05:08:21', '2025-05-12 02:11:34', 0.00, NULL),
(78, 0, 'SJ', '', '', '', 'berhasil_dikirim', '2025-05-07 05:17:07', 'signed_1746604635.pdf', '2025-05-07 14:57:15', 'BG8027AH', 'supir5', 'Parjo', '2025-05-07 05:17:07', '2025-05-07 07:57:15', 0.00, NULL),
(79, 0, 'SBA', '', '', '', 'berhasil_dikirim', '2025-05-07 05:30:34', 'signed_1747015955.pdf', '2025-05-12 09:12:35', 'BG8941NX', 'supir3', 'Sumardi', '2025-05-07 05:30:34', '2025-05-12 02:12:35', 0.00, NULL),
(80, 0, 'SD 1', '', '', '', 'berhasil_dikirim', '2025-05-07 08:19:08', 'signed_1746606046.pdf', '2025-05-07 15:20:46', 'BG8733NQ', 'supir2', 'Marno', '2025-05-07 08:19:08', '2025-05-07 08:20:46', 0.00, NULL),
(81, 0, 'SBA', '', '', '', 'ditolak', '2025-05-12 02:59:02', NULL, NULL, NULL, NULL, NULL, '2025-05-12 02:59:02', '2025-05-12 02:59:54', 0.00, NULL),
(82, 0, 'PSM', '', '', '', 'ditolak', '2025-05-12 03:07:31', NULL, NULL, NULL, NULL, NULL, '2025-05-12 03:07:31', '2025-05-12 03:07:59', 0.00, NULL),
(83, 0, 'PSM', '', '', '', 'dalam_proses_muat', '2025-05-12 03:08:34', NULL, NULL, 'BG8941NX', 'supir3', 'Sumardi', '2025-05-12 03:08:34', '2025-05-13 02:17:01', 12.50, NULL),
(84, 0, 'Kemang', '', '', '', 'ditolak', '2025-05-12 03:38:00', NULL, NULL, NULL, NULL, NULL, '2025-05-12 03:38:00', '2025-05-12 03:40:05', 0.00, 'Kelebihan Notase,mobil yang terkait tidak tersedia'),
(85, 0, 'Afat', '', '', '', 'dalam_proses_muat', '2025-05-13 02:14:21', NULL, NULL, 'BG8941NX', 'supir3', 'Sumardi', '2025-05-13 02:14:21', '2025-05-13 02:17:05', 10.00, NULL),
(86, 0, 'PBM 3', '', '', '', 'berhasil_dikirim', '2025-05-14 02:52:12', 'signed_1747191382.pdf', '2025-05-14 09:56:22', 'BG8027AH', 'supir5', 'Parjo', '2025-05-14 02:52:12', '2025-05-14 02:56:22', 12.50, NULL),
(87, 0, 'PBM 3', '', '', '', 'pembuatan_surat_jalan', '2025-05-14 02:57:31', NULL, NULL, 'BG8027AH', 'supir5', 'Parjo', '2025-05-14 02:57:31', '2025-05-14 02:57:49', 12.50, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pengiriman_detail`
--

CREATE TABLE `pengiriman_detail` (
  `id_detail` int(11) NOT NULL,
  `id_pengiriman` int(11) NOT NULL,
  `id_stok` int(11) NOT NULL,
  `jumlah` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengiriman_detail`
--

INSERT INTO `pengiriman_detail` (`id_detail`, `id_pengiriman`, `id_stok`, `jumlah`) VALUES
(1, 75, 6, '100'),
(2, 75, 2, '100'),
(3, 76, 1, '100'),
(4, 76, 4, '130'),
(5, 77, 1, '100'),
(6, 77, 4, '100'),
(7, 78, 3, '150'),
(8, 78, 7, '100'),
(9, 79, 1, '100'),
(10, 79, 3, '120'),
(11, 80, 2, '100'),
(12, 80, 6, '100'),
(13, 81, 3, '130'),
(14, 81, 8, '120'),
(15, 82, 2, '50'),
(16, 82, 6, '100'),
(17, 82, 1, '140'),
(18, 83, 2, '100'),
(19, 83, 6, '100'),
(20, 83, 1, '50'),
(21, 84, 4, '100'),
(22, 84, 7, '150'),
(23, 85, 1, '30'),
(24, 85, 3, '140'),
(25, 85, 8, '30'),
(26, 86, 2, '100'),
(27, 86, 6, '100'),
(28, 86, 4, '50'),
(29, 87, 2, '100'),
(30, 87, 6, '100'),
(31, 87, 4, '50');

-- --------------------------------------------------------

--
-- Table structure for table `stok`
--

CREATE TABLE `stok` (
  `id_stok` int(11) NOT NULL,
  `nm_barang` varchar(25) NOT NULL,
  `jenis` varchar(25) NOT NULL,
  `jumlah` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stok`
--

INSERT INTO `stok` (`id_stok`, `nm_barang`, `jenis`, `jumlah`) VALUES
(1, 'Malindo 8202 AA', 'Starter', '1170'),
(2, 'Malindo 8201 ', 'Pre-Starter', '1190'),
(3, 'Malindo 9203 AA', 'Finisher', '940'),
(4, '611s', 'Starter', '1220'),
(5, '612s', 'Finisher', '1550'),
(6, '611s-K', 'Pre-Starter', '990'),
(7, 'Gold Extra BR1 Med', 'Starter', '950'),
(8, 'Gold Extra BR1 CC', 'Finisher', '1160'),
(9, 'Farm Chick', 'Starter', '590'),
(10, 'Egg Max', 'Layer Feed', '1000'),
(11, 'BPS', 'Pre-Starter', '115');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id_keluar`),
  ADD KEY `fk_barangkeluar_stok` (`id_stok`);

--
-- Indexes for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`id_masuk`),
  ADD KEY `fk_barangmasuk_stok` (`id_stok`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`iduser`);

--
-- Indexes for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id_pengiriman`);

--
-- Indexes for table `pengiriman_detail`
--
ALTER TABLE `pengiriman_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_pengiriman` (`id_pengiriman`),
  ADD KEY `fk_stok` (`id_stok`);

--
-- Indexes for table `stok`
--
ALTER TABLE `stok`
  ADD PRIMARY KEY (`id_stok`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_keluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id_masuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id_pengiriman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `pengiriman_detail`
--
ALTER TABLE `pengiriman_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `stok`
--
ALTER TABLE `stok`
  MODIFY `id_stok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `fk_barangkeluar_stok` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`);

--
-- Constraints for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD CONSTRAINT `fk_barangmasuk_stok` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`);

--
-- Constraints for table `pengiriman_detail`
--
ALTER TABLE `pengiriman_detail`
  ADD CONSTRAINT `fk_pengiriman` FOREIGN KEY (`id_pengiriman`) REFERENCES `pengiriman` (`id_pengiriman`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stok` FOREIGN KEY (`id_stok`) REFERENCES `stok` (`id_stok`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
