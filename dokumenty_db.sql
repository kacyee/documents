-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 22, 2025 at 03:13 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dokumenty_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `barcode` varchar(50) NOT NULL,
  `defendant_name` varchar(255) NOT NULL,
  `plaintiff_name` varchar(255) NOT NULL,
  `case_number` varchar(100) NOT NULL,
  `case_type` enum('civil','criminal') NOT NULL,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `location_id`, `barcode`, `defendant_name`, `plaintiff_name`, `case_number`, `case_type`, `is_archived`, `created_at`, `archived_at`) VALUES
(1, 1, 'DOC1752317040e8c57cd5', 'Patryk Puślecki', 'Rafał Lis', 'C110-15 C115-20', 'civil', 1, '2025-07-12 10:44:00', '2025-07-14 08:58:03'),
(2, 2, 'DOC175231739541faed08', 'Patryk Puślecki', 'Rafał Lis', 'C059 D0912', 'civil', 1, '2025-07-12 10:49:55', '2025-07-14 10:24:34'),
(3, 8, 'DOC175248506016a43a04', 'Patryk Puślecki', 'Michał Krawczyk', 'B90 C-342', 'criminal', 0, '2025-07-14 09:24:20', NULL),
(4, 3, 'DOC1752487056a1eff281', 'Michał Anioł', 'Jerzy Kliczko', '', 'civil', 1, '2025-07-14 09:57:36', '2025-07-14 10:24:37'),
(5, 10, 'DOC1752487113a0423014', 'Patryk Puślecki', 'Jerzy Kliczko', 'CZC1', 'criminal', 0, '2025-07-14 09:58:33', NULL),
(6, 8, 'DOC17525001824180985c', 'Patryk Patryk', 'Łukasz Rabka', 'C90 3221', 'criminal', 0, '2025-07-14 13:36:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `location_code` varchar(10) NOT NULL,
  `shelf_type` enum('civil','criminal') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `location_code`, `shelf_type`, `created_at`) VALUES
(1, 'A1', 'civil', '2025-07-12 10:42:49'),
(2, 'A2', 'civil', '2025-07-12 10:42:53'),
(3, 'A3', 'civil', '2025-07-12 10:42:55'),
(4, 'A4', 'civil', '2025-07-12 10:43:01'),
(5, 'B1', 'civil', '2025-07-12 10:43:06'),
(6, 'B2', 'civil', '2025-07-12 10:43:09'),
(8, 'A1', 'criminal', '2025-07-14 09:03:37'),
(9, 'B2', 'criminal', '2025-07-14 09:58:20'),
(10, 'B3', 'criminal', '2025-07-14 09:58:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_admin`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2025-07-12 10:41:27'),
(2, 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-07-12 10:41:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_location_shelf` (`location_code`,`shelf_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
