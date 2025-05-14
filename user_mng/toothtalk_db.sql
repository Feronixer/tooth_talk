-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 06:30 AM
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
-- Database: `toothtalk_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patient_no` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `treatment` varchar(255) NOT NULL,
  `patient_type` enum('New','Old') NOT NULL DEFAULT 'New',
  `status` enum('Pending','Confirmed','Completed','Rescheduled','Cancelled') NOT NULL DEFAULT 'Pending',
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_no`, `name`, `email`, `phone`, `address`, `birth_date`, `appointment_date`, `appointment_time`, `treatment`, `patient_type`, `status`, `reason`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'P20240001', 'Maria Dela Cruz', 'maria.delacruz@example.com', '09171234567', '123 Rizal St, Manila', '1990-05-15', '2024-05-15', '10:00:00', 'Cleaning', 'New', 'Confirmed', NULL, 'First time visit.', '2024-05-13 02:00:00', '2024-05-13 02:00:00'),
(2, 'P20240002', 'Juan Santos', 'juan.santos@example.com', '09229876543', '456 Bonifacio Ave, Quezon City', '1985-11-20', '2024-05-16', '14:30:00', 'Tooth Extraction', 'Old', 'Pending', NULL, 'Follow-up check required.', '2024-05-13 02:05:00', '2024-05-13 02:05:00'),
(3, 'P20240003', 'Anna Reyes', 'anna.reyes@example.com', '09991112233', '789 Aguinaldo Hw, Cavite', '1995-02-10', '2024-05-10', '09:00:00', 'Braces Adjustment', 'Old', 'Completed', NULL, 'Adjustment successful.', '2024-05-10 01:30:00', '2024-05-10 01:30:00'),
(4, 'P20240004', 'Luis Castro', NULL, '09185550000', '101 Mabini Rd, Pasig', '2000-07-25', '2024-05-17', '11:00:00', 'Consultation', 'New', 'Rescheduled', 'Patient requested new date due to conflict.', NULL, '2024-05-12 03:00:00', '2025-05-14 03:32:03'),
(5, 'P20240005', 'Sofia Garcia', 'sofia.garcia@example.com', '09278889900', '222 Luna St, Makati', '1978-12-01', '2024-05-18', '16:00:00', 'Root Canal', 'Old', 'Cancelled', 'Emergency travel.', 'Needs to reschedule soon.', '2024-05-11 08:00:00', '2024-05-13 08:00:00'),
(6, 'P20240006', 'Carlos Tan', 'carlos.tan@example.com', '09151231234', '333 P. Burgos, Mandaluyong', '1992-09-30', '2024-05-20', '13:00:00', 'Dental Implants', 'New', 'Pending', NULL, 'Requires pre-assessment.', '2024-05-13 05:00:00', '2024-05-13 05:00:00'),
(7, 'P20240007', 'Isabelle Lim', 'isabelle.lim@example.com', '09283214321', '444 Magsaysay Blvd, Manila', '1988-03-12', '2024-05-21', '09:30:00', 'Teeth Whitening', 'Old', 'Confirmed', NULL, NULL, '2024-05-13 01:30:00', '2024-05-13 01:30:00'),
(8, 'P20240008', 'Miguel Rodriguez', NULL, '09067890123', '555 Shaw Blvd, Pasig', '2001-06-05', '2024-05-22', '15:00:00', 'Filling', 'New', 'Completed', NULL, 'Minor cavity.', '2024-05-12 07:30:00', '2024-05-12 07:30:00'),
(9, 'P20240009', 'Patricia Go', 'patricia.go@example.com', '09390123456', '666 Ortigas Ave, Quezon City', '1999-01-20', '2024-05-23', '10:30:00', 'Veneers', 'Old', 'Pending', NULL, 'Consultation for veneers.', '2024-05-13 02:30:00', '2024-05-13 02:30:00'),
(10, 'P20240010', 'David Sy', 'david.sy@example.com', '09176543210', '777 EDSA, Makati', '1975-08-18', '2024-05-24', '14:00:00', 'Wisdom Tooth Extraction', 'New', 'Confirmed', NULL, 'X-ray provided.', '2024-05-13 06:00:00', '2024-05-13 06:00:00'),
(11, 'P20240011', 'Bea Alonzo', 'bea.alonzo@example.com', '09171112222', '888 Star St, Quezon City', '1987-10-17', '2024-05-25', '11:30:00', 'Check-up', 'Old', 'Pending', NULL, 'Regular check-up.', '2024-05-13 03:30:00', '2024-05-13 03:30:00'),
(12, 'P20240012', 'John Lloyd Cruz', 'jlc@example.com', '09203334444', '999 Moon Ave, Pasig', '1983-06-24', '2024-05-28', '09:00:00', 'Cleaning', 'Old', 'Confirmed', NULL, NULL, '2024-05-13 01:05:00', '2025-05-14 03:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Staff',
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `username`, `name`, `role`, `email`, `mobile`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'Angel', 'Angel Cuadernal', 'Staff', 'angelkyutie05@gmail.com', '0999888777', '$2y$10$examplehash1.NotRealButValidFormat', '2023-04-12 00:13:56', '2023-04-12 00:13:56'),
(2, 'Joy123', 'Aleck Joy Carpio', 'Staff', 'joyniwonwoo@gmail.com', '0999888778', '$2y$10$examplehash2.NotRealButValidFormat', '2023-04-13 02:20:00', '2023-04-13 02:20:00'),
(3, 'Japan', 'Dr. Justine Valera', 'Staff', 'justineisreal@gmail.com', '0999888779', '$2y$10$examplehash3.NotRealButValidFormat', '2023-04-10 01:00:00', '2024-05-13 07:30:00'),
(4, 'Jigz', 'Jessie Valera', 'Staff', 'jessiewalluvmeg@gmail.com', '0999888770', '$2y$10$examplehash4.NotRealButValidFormat', '2023-04-11 06:30:15', '2023-04-11 06:30:15'),
(5, 'DocHelen', 'Dr. Helen Parr', 'Staff', 'helen.parr@example.com', '09123456789', '$2y$10$examplehash5.NotRealButValidFormat', '2023-03-01 03:00:00', '2023-03-01 03:00:00'),
(6, 'NurseBob', 'Bob Minion', 'Staff', 'bob.minion@example.com', '09987654321', '$2y$10$examplehash6.NotRealButValidFormat', '2023-03-05 08:45:00', '2023-03-05 08:45:00'),
(7, 'AdminSue', 'Sue Storm', 'Staff', 'sue.storm@example.com', '09112233445', '$2y$10$examplehash7.NotRealButValidFormat', '2023-02-15 01:15:30', '2023-02-15 01:15:30'),
(8, 'TechTony', 'Tony Stark', 'Staff', 'tony.stark@example.com', '09556677889', '$2y$10$examplehash8.NotRealButValidFormat', '2023-02-20 05:00:00', '2025-05-14 02:57:44'),
(9, 'FrontDeskMay', 'May Parker', 'Staff', 'may.parker@example.com', '09332211009', '$2y$10$examplehash9.NotRealButValidFormat', '2023-01-10 00:00:00', '2023-01-10 00:00:00'),
(10, 'CleanerWallE', 'Wall-E Unit', 'Staff', 'walle@example.com', '09001122334', '$2y$10$examplehash10.NotRealButValidFormat', '2023-01-15 09:00:00', '2023-01-15 09:00:00'),
(11, 'User11', 'John Doe', 'Staff', 'john.doe@example.com', '09121112233', '$2y$10$examplehash11.NotRealButValidFormat', '2024-01-20 02:00:00', '2024-01-20 02:00:00'),
(12, 'User12', 'Jane Smith', 'Staff', 'jane.smith@example.com', '09122223344', '$2y$10$examplehash12.NotRealButValidFormat', '2024-01-22 03:00:00', '2024-01-22 03:00:00'),
(13, 'User13', 'Mike Brown', 'Staff', 'mike.brown@example.com', '09123334455', '$2y$10$examplehash13.NotRealButValidFormat', '2024-01-25 06:30:00', '2024-01-25 06:30:00'),
(14, 'User14', 'Lisa Green', 'Staff', 'lisa.green@example.com', '09124445566', '$2y$10$examplehash14.NotRealButValidFormat', '2024-02-01 01:30:00', '2024-02-01 01:30:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_no` (`patient_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
