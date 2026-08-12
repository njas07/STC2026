-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 12, 2026 at 04:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stc2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `nama_lomba` varchar(100) NOT NULL,
  `tipe` enum('individu','tim') NOT NULL,
  `min_anggota` int(11) NOT NULL,
  `max_anggota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `nama_lomba`, `tipe`, `min_anggota`, `max_anggota`) VALUES
(1, 'Web Design', 'individu', 1, 1),
(2, 'Infografis', 'tim', 3, 3),
(3, 'Microsoft Excel', 'individu', 1, 1),
(4, 'Speed Typing', 'individu', 1, 1),
(5, 'Cerdas Cermat', 'tim', 3, 3),
(6, 'Free Fire', 'tim', 4, 5),
(7, 'Mobile Legends', 'tim', 5, 6);

-- --------------------------------------------------------

--
-- Table structure for table `individual_registrations`
--

CREATE TABLE `individual_registrations` (
  `id` bigint(20) NOT NULL,
  `registration_id` bigint(20) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `nis_nisn` varchar(50) NOT NULL,
  `asal_instansi_sekolah` varchar(200) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `individual_registrations`
--

INSERT INTO `individual_registrations` (`id`, `registration_id`, `nama_lengkap`, `nis_nisn`, `asal_instansi_sekolah`, `kelas`, `email`, `no_whatsapp`) VALUES
(1, 1, 'Test Peserta Web Design', '123456789', 'SMK TI STC 2026', 'X PPLG', 'test@stc2026.com', '081234567890');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) NOT NULL,
  `registration_id` bigint(20) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `bukti_pembayaran` varchar(500) NOT NULL,
  `status_pembayaran` enum('MENUNGGU_VERIFIKASI','VALID','TIDAK_VALID') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `registration_id`, `metode_pembayaran`, `jumlah_bayar`, `tanggal_pembayaran`, `bukti_pembayaran`, `status_pembayaran`, `created_at`) VALUES
(1, 1, 'Transfer Bank', 100000.00, '2026-08-11', 'uploads/payments/REG-1-PAY-1786424492-6a7aacac04184.jpg', 'VALID', '2026-08-11 05:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `kode_pendaftaran` varchar(30) NOT NULL,
  `tipe_pendaftaran` enum('individu','tim') NOT NULL,
  `status` enum('MENUNGGU_VERIFIKASI','DIVERIFIKASI','DITOLAK') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `user_id`, `competition_id`, `kode_pendaftaran`, `tipe_pendaftaran`, `status`, `created_at`) VALUES
(1, 1, 1, 'STC-WEB-20260811055852', 'individu', 'DIVERIFIKASI', '2026-08-12 00:55:04');

-- --------------------------------------------------------

--
-- Table structure for table `registration_declarations`
--

CREATE TABLE `registration_declarations` (
  `id` bigint(20) NOT NULL,
  `registration_id` bigint(20) NOT NULL,
  `data_benar` tinyint(1) NOT NULL,
  `setuju_ketentuan` tinyint(1) NOT NULL,
  `sudah_melakukan_pembayaran` tinyint(1) NOT NULL,
  `sudah_follow_instagram` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_documents`
--

CREATE TABLE `registration_documents` (
  `id` bigint(20) NOT NULL,
  `registration_id` bigint(20) NOT NULL,
  `team_member_id` bigint(20) DEFAULT NULL,
  `jenis_dokumen` enum('kartu_pelajar','surat_keterangan','bukti_follow_ig_sekolah','bukti_follow_ig_stc','bukti_pembayaran') NOT NULL,
  `nama_file_asli` varchar(255) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `ukuran_file` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_documents`
--

INSERT INTO `registration_documents` (`id`, `registration_id`, `team_member_id`, `jenis_dokumen`, `nama_file_asli`, `nama_file`, `file_path`, `mime_type`, `ukuran_file`) VALUES
(1, 1, NULL, 'kartu_pelajar', 'test-kartu-pelajar.jpg', 'REG-1-1786421453-6a7aa0cde54f4.jpg', 'uploads/documents/REG-1-1786421453-6a7aa0cde54f4.jpg', 'image/jpeg', 34940);

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` bigint(20) NOT NULL,
  `registration_id` bigint(20) NOT NULL,
  `nomor_anggota` int(11) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `nis_nisn` varchar(50) NOT NULL,
  `kelas` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_registrations`
--

CREATE TABLE `team_registrations` (
  `id` bigint(20) NOT NULL,
  `registration_id` bigint(20) NOT NULL,
  `nama_tim` varchar(150) NOT NULL,
  `nama_sekolah` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `no_whatsapp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('peserta','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `no_whatsapp`, `password`, `role`, `created_at`) VALUES
(1, 'Test Peserta STC', 'test@stc2026.com', '081234567890', '$2y$10$.Li6iRBcTHHopKvjj54Zl.U5GOyQ7pB9LPAdATaahEgBEd4jLYwei', 'peserta', '2026-08-11 03:37:20'),
(2, 'Admin STC 2026', 'NJas', '081234567890', '$2y$10$nVhZbZKP6yWAvgfJCXpv.u3qPICyhdxAns65.AoVmK5U61D1ePqU.', 'admin', '2026-08-11 12:44:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_lomba` (`nama_lomba`);

--
-- Indexes for table `individual_registrations`
--
ALTER TABLE `individual_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_id` (`registration_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pendaftaran` (`kode_pendaftaran`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `user_id_2` (`user_id`);

--
-- Indexes for table `registration_declarations`
--
ALTER TABLE `registration_declarations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `registration_documents`
--
ALTER TABLE `registration_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`),
  ADD KEY `team_member_id` (`team_member_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `team_registrations`
--
ALTER TABLE `team_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `registration_id` (`registration_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `individual_registrations`
--
ALTER TABLE `individual_registrations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registration_declarations`
--
ALTER TABLE `registration_declarations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registration_documents`
--
ALTER TABLE `registration_documents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_registrations`
--
ALTER TABLE `team_registrations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `individual_registrations`
--
ALTER TABLE `individual_registrations`
  ADD CONSTRAINT `individual_registrations_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `registration_declarations`
--
ALTER TABLE `registration_declarations`
  ADD CONSTRAINT `registration_declarations_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registration_documents`
--
ALTER TABLE `registration_documents`
  ADD CONSTRAINT `registration_documents_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `registration_documents_ibfk_2` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `team_registrations`
--
ALTER TABLE `team_registrations`
  ADD CONSTRAINT `team_registrations_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
