-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 07:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `vendor_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `delegations`
--

CREATE TABLE `delegations` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `can_add_driver` tinyint(1) NOT NULL DEFAULT 0,
  `can_add_vehicle` tinyint(1) NOT NULL DEFAULT 0,
  `can_upload_docs` tinyint(1) NOT NULL DEFAULT 0,
  `can_assign_vehicle` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delegations`
--

INSERT INTO `delegations` (`id`, `vendor_id`, `can_add_driver`, `can_add_vehicle`, `can_upload_docs`, `can_assign_vehicle`) VALUES
(1, 3, 1, 1, 1, 1),
(2, 4, 1, 1, 1, 0),
(3, 5, 0, 0, 1, 0),
(4, 6, 1, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `assigned_vehicle_id` int(11) DEFAULT NULL,
  `license_file` varchar(255) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `vendor_id`, `name`, `license_number`, `assigned_vehicle_id`, `license_file`, `license_expiry`, `created_at`) VALUES
(5, 4, 'Ravi Sharma', 'DL123456789', 5, NULL, '2026-03-15', '2025-07-20 06:25:50'),
(6, 5, 'Sunil Kumar', 'PB987654321', 6, NULL, '2025-12-01', '2025-07-20 06:25:50'),
(7, 3, 'Amit Singh', 'CH456789123', 7, NULL, '2024-11-10', '2025-07-20 06:25:50'),
(8, 2, 'Neeraj Bansal', 'HR678912345', 8, NULL, '2027-05-20', '2025-07-20 06:25:50'),
(9, 2, 'Ravi Sharma', 'DL123456789', NULL, NULL, '2026-03-15', '2025-07-20 06:26:10'),
(10, 2, 'Sunil Kumar', 'PB987654321', NULL, NULL, '2025-12-01', '2025-07-20 06:26:10'),
(11, 3, 'Jatin', 'HDHDKS-DHJKSA', NULL, NULL, '2025-07-31', '2025-07-20 17:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `driver_docs`
--

CREATE TABLE `driver_docs` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `doc_type` varchar(100) NOT NULL,
  `doc_file` varchar(255) NOT NULL,
  `expiry_date` date NOT NULL,
  `uploaded_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_docs`
--

INSERT INTO `driver_docs` (`id`, `driver_id`, `doc_type`, `doc_file`, `expiry_date`, `uploaded_on`) VALUES
(1, 6, 'RC', '../uploads/1752992966_Screenshot 2025-07-20 105552.png', '2025-07-09', '2025-07-20 06:29:26'),
(3, 5, 'DL', '../uploads/1753022534_Screenshot_2025-06-24_120507.png', '2025-07-23', '2025-07-20 14:42:14'),
(4, 7, 'Permit', '../uploads/1753024024_Screenshot_2025-06-25_203625.png', '2027-08-28', '2025-07-20 15:07:04'),
(5, 10, 'Pollution', '../uploads/1753024157_Screenshot_2025-06-30_151456.png', '2021-05-04', '2025-07-20 15:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` enum('super_vendor','sub_vendor') NOT NULL,
  `role` enum('super','regional','city','local') NOT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `type`, `role`, `vendor_id`, `created_at`, `status`, `last_login`) VALUES
(2, 'Admin', 'admin@example.com', '$2y$10$.jzjh449OHvkhAkLNN8THOaHKVO4RJ0QLJlDWddt6wscuucgaV5o.', 'super_vendor', 'super', 2, '2025-07-20 05:08:36', 'active', NULL),
(3, 'Gunika Bansal', 'Gunikabansal@gmail.com', '$2y$10$blWkocEnphphqWzji2lcvOomN07GQ6GN50aKm.8fxb9fWrFdFCINi', 'sub_vendor', 'regional', 2, '2025-07-20 06:08:16', 'active', NULL),
(4, 'Gunika', 'imgunika19@gmail.com', '$2y$10$1AARkhnbpTWbG7WUjyI1YeB.qgmQs2anSmX6Q5rHqBY.zbdD7eswi', 'sub_vendor', 'city', 3, '2025-07-20 06:09:13', 'active', NULL),
(5, 'Mansi Kumari', 'mansi@gmail.com', '$2y$10$AXwz1NR3c1JaJF.H/ooBZuoi7OsVVQT1A8R0PR6eZhN9MHcILLy2S', 'sub_vendor', 'local', 4, '2025-07-20 06:44:57', 'inactive', NULL),
(6, 'sneha goswami', 'sneha@gmail.com', '$2y$10$LWgerMD2Lye0G61dV/Oj5Omah8YLkftsddCoroKUv8Eqn5TUWVDZu', 'sub_vendor', 'local', 4, '2025-07-20 07:17:50', 'inactive', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `seating_capacity` int(11) DEFAULT NULL,
  `rc_file` varchar(255) DEFAULT NULL,
  `permit_file` varchar(255) DEFAULT NULL,
  `pollution_file` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_driver_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `vendor_id`, `registration_no`, `model`, `fuel_type`, `seating_capacity`, `rc_file`, `permit_file`, `pollution_file`, `status`, `created_at`, `assigned_driver_id`) VALUES
(5, 5, 'HR26AB1234', 'Maruti Swift', 'Petrol', 5, 'rc1.pdf', 'permit1.pdf', 'pollution1.pdf', 'active', '2025-07-20 06:24:13', 7),
(6, 2, 'DL10CD5678', 'Hyundai i20', 'Diesel', 5, 'rc2.pdf', 'permit2.pdf', 'pollution2.pdf', 'active', '2025-07-20 06:24:13', NULL),
(7, 3, 'PB08EF9876', 'Mahindra Bolero', 'Diesel', 7, 'rc3.pdf', 'permit3.pdf', 'pollution3.pdf', 'inactive', '2025-07-20 06:24:13', NULL),
(8, 6, 'CH01GH4321', 'Tata Nexon', 'Electric', 5, 'rc4.pdf', 'permit4.pdf', 'pollution4.pdf', 'active', '2025-07-20 06:24:13', 8),
(9, 2, 'PB11CE5432', 'Tata Ace', 'Petrol', 6, NULL, NULL, NULL, 'active', '2025-07-20 08:05:50', NULL),
(10, 2, 'PB11CE5432', 'Tata Ace', 'Petrol', 6, NULL, NULL, NULL, 'active', '2025-07-20 08:05:58', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `delegations`
--
ALTER TABLE `delegations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_vehicle_id` (`assigned_vehicle_id`),
  ADD KEY `drivers_ibfk_1` (`vendor_id`);

--
-- Indexes for table `driver_docs`
--
ALTER TABLE `driver_docs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_driver_assignment` (`assigned_driver_id`),
  ADD KEY `vehicles_ibfk_1` (`vendor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `delegations`
--
ALTER TABLE `delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `driver_docs`
--
ALTER TABLE `driver_docs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delegations`
--
ALTER TABLE `delegations`
  ADD CONSTRAINT `delegations_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `drivers_ibfk_2` FOREIGN KEY (`assigned_vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `driver_docs`
--
ALTER TABLE `driver_docs`
  ADD CONSTRAINT `driver_docs_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_driver_assignment` FOREIGN KEY (`assigned_driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`);
COMMIT;