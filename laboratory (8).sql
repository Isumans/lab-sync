-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 19, 2026 at 09:09 AM
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
-- Database: `laboratory`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) DEFAULT NULL,
  `appointment_time` time NOT NULL,
  `appointment_date` date NOT NULL,
  `method` enum('online','physical','call') NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `booking_channel` varchar(40) DEFAULT NULL,
  `home_collection` tinyint(1) NOT NULL DEFAULT 0,
  `collection_address` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `referred_by` varchar(100) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `sample_type` varchar(50) DEFAULT NULL,
  `sample_datetime` datetime DEFAULT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `time_slot_id` int(11) DEFAULT NULL COMMENT 'FK to online_booking_slots.id — set for online bookings only'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `patient_id`, `test_id`, `appointment_time`, `appointment_date`, `method`, `status`, `booking_channel`, `home_collection`, `collection_address`, `updated_at`, `created_at`, `notes`, `referred_by`, `deleted_at`, `deleted_by`, `sample_type`, `sample_datetime`, `payment_status`, `payment_reference`, `time_slot_id`) VALUES
(8, 1, NULL, '12:08:00', '2026-04-05', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', '', NULL, '2026-04-04 00:51:50', 6, NULL, NULL, 'pending', NULL, NULL),
(9, 1, NULL, '16:36:00', '2026-04-01', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(10, 1, NULL, '16:00:00', '2026-04-03', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(11, 1, NULL, '20:12:00', '2026-04-04', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, '2026-04-04 20:15:54', 6, NULL, NULL, 'pending', NULL, NULL),
(12, 1, NULL, '08:00:00', '2026-04-05', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', '', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(13, 1, NULL, '09:42:00', '2026-04-05', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(14, 1, NULL, '11:00:00', '2026-04-08', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(15, 7, NULL, '15:24:00', '2026-04-12', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(16, 9, NULL, '21:00:00', '2026-04-12', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(17, 1, NULL, '21:42:00', '2026-04-13', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(18, 1, NULL, '15:04:00', '2026-04-13', 'physical', 'Pending', 'receptionist_walkin', 0, NULL, '2026-04-17 11:47:23', '2026-04-17 11:47:23', '', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(19, 25, 1, '09:30:00', '2026-04-24', 'online', 'Pending', 'online_self', 0, NULL, '2026-04-17 12:11:14', '2026-04-17 12:11:14', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(20, 25, 1, '10:00:00', '2026-04-25', 'online', 'Pending', 'online_self', 0, NULL, '2026-04-17 12:14:03', '2026-04-17 12:12:04', NULL, NULL, NULL, NULL, NULL, NULL, 'paid', 'APT-20', NULL),
(21, 25, 1, '08:30:00', '2026-04-18', 'online', 'Pending', 'online_self', 0, NULL, '2026-04-17 12:48:42', '2026-04-17 12:48:07', NULL, NULL, NULL, NULL, NULL, NULL, 'paid', 'APT-21', NULL),
(22, 25, 30, '11:00:00', '2026-04-18', 'online', 'Pending', 'online_self', 1, 'colombo', '2026-04-17 15:13:25', '2026-04-17 15:12:47', NULL, NULL, NULL, NULL, NULL, NULL, 'paid', 'APT-22', NULL),
(23, 25, 4, '10:00:00', '2026-04-18', 'online', 'Pending', 'online_self', 0, NULL, '2026-04-17 17:00:09', '2026-04-17 16:59:34', NULL, NULL, NULL, NULL, NULL, NULL, 'paid', 'APT-23', NULL),
(24, 25, 30, '09:30:00', '2026-04-19', 'online', 'Pending', 'online_self', 0, NULL, '2026-04-18 06:11:47', '2026-04-18 06:11:47', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL),
(25, 25, 30, '09:00:00', '2026-04-19', 'online', 'Pending', 'online_self', 0, NULL, '2026-04-18 06:15:06', '2026-04-18 06:13:39', NULL, NULL, NULL, NULL, NULL, NULL, 'paid', 'APT-25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointment_items`
--

CREATE TABLE `appointment_items` (
  `appointment_item_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_items`
--

INSERT INTO `appointment_items` (`appointment_item_id`, `appointment_id`, `test_id`, `unit_price`, `quantity`, `line_total`, `created_at`) VALUES
(1, 19, 1, 100.00, 1, 100.00, '2026-04-17 12:11:14'),
(2, 20, 1, 100.00, 1, 100.00, '2026-04-17 12:12:04'),
(3, 21, 1, 100.00, 1, 100.00, '2026-04-17 12:48:07'),
(4, 22, 30, 130.00, 1, 130.00, '2026-04-17 15:12:47'),
(5, 23, 4, 1200.00, 1, 1200.00, '2026-04-17 16:59:34'),
(6, 24, 30, 130.00, 1, 130.00, '2026-04-18 06:11:47'),
(7, 25, 30, 130.00, 1, 130.00, '2026-04-18 06:13:39');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_tests`
--

CREATE TABLE `appointment_tests` (
  `appointment_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','COMPLETED','AUTHORIZED','PRINTED') DEFAULT 'PENDING',
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_to` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `authorized_by` int(11) DEFAULT NULL,
  `authorized_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_tests`
--

INSERT INTO `appointment_tests` (`appointment_id`, `test_id`, `status`, `status_updated_at`, `assigned_to`, `completed_at`, `authorized_by`, `authorized_at`) VALUES
(8, 8, 'IN_PROGRESS', '2026-04-03 11:35:32', NULL, NULL, NULL, NULL),
(8, 20, 'IN_PROGRESS', '2026-04-03 12:30:52', 6, NULL, NULL, NULL),
(8, 21, 'IN_PROGRESS', '2026-04-03 12:31:02', 6, NULL, NULL, NULL),
(9, 20, 'PENDING', '2026-04-01 11:06:20', NULL, NULL, NULL, NULL),
(9, 21, 'PENDING', '2026-04-01 11:06:20', NULL, NULL, NULL, NULL),
(10, 17, 'PENDING', '2026-04-03 06:28:00', NULL, NULL, NULL, NULL),
(10, 21, 'PENDING', '2026-04-03 06:28:00', NULL, NULL, NULL, NULL),
(11, 17, 'IN_PROGRESS', '2026-04-04 14:43:11', 6, NULL, NULL, NULL),
(12, 17, 'PENDING', '2026-04-04 14:44:53', NULL, NULL, NULL, NULL),
(12, 20, 'PENDING', '2026-04-04 14:44:53', NULL, NULL, NULL, NULL),
(13, 17, 'PENDING', '2026-04-17 07:08:14', NULL, NULL, NULL, NULL),
(14, 3, 'IN_PROGRESS', '2026-04-07 04:31:08', 6, NULL, NULL, NULL),
(15, 29, 'AUTHORIZED', '2026-04-13 15:54:11', 6, '2026-04-13 21:24:05', 11, '2026-04-13 21:24:11'),
(16, 29, 'AUTHORIZED', '2026-04-13 08:51:13', 6, '2026-04-12 21:35:17', 11, '2026-04-13 14:21:13'),
(16, 30, 'AUTHORIZED', '2026-04-13 14:51:32', 6, '2026-04-12 21:10:17', 11, '2026-04-13 20:21:32'),
(17, 29, 'AUTHORIZED', '2026-04-13 15:50:27', 6, '2026-04-13 21:20:22', 11, '2026-04-13 21:20:27'),
(17, 30, 'AUTHORIZED', '2026-04-13 15:52:44', 6, '2026-04-13 21:22:38', 11, '2026-04-13 21:22:44'),
(18, 29, 'AUTHORIZED', '2026-04-13 15:46:13', 6, '2026-04-13 15:08:40', 11, '2026-04-13 21:16:13'),
(19, 1, 'PENDING', '2026-04-17 12:11:14', NULL, NULL, NULL, NULL),
(20, 1, 'IN_PROGRESS', '2026-04-17 12:52:29', 6, NULL, NULL, NULL),
(21, 1, 'IN_PROGRESS', '2026-04-17 12:55:20', 6, NULL, NULL, NULL),
(22, 30, 'AUTHORIZED', '2026-04-18 17:48:53', 11, '2026-04-18 23:18:49', 11, '2026-04-18 23:18:53'),
(23, 4, 'PENDING', '2026-04-17 16:59:34', NULL, NULL, NULL, NULL),
(24, 30, 'AUTHORIZED', '2026-04-18 17:24:50', 6, '2026-04-18 22:52:42', 11, '2026-04-18 22:54:50'),
(25, 30, 'AUTHORIZED', '2026-04-18 06:41:46', 12, '2026-04-18 12:09:05', 11, '2026-04-18 12:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL,
  `bill_number` varchar(50) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `bill_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('DRAFT','PENDING','PARTIALLY_PAID','PAID','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`bill_id`, `bill_number`, `appointment_id`, `patient_id`, `bill_date`, `due_date`, `subtotal`, `discount_amount`, `tax_amount`, `total_amount`, `paid_amount`, `balance_due`, `status`, `status_updated_at`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BILL-000001', 17, 1, '2026-04-14', NULL, 250.00, 0.00, 0.00, 250.00, 250.00, 0.00, 'DRAFT', '2026-04-14 05:04:32', NULL, 6, '2026-04-13 20:42:06', '2026-04-14 05:04:32'),
(2, 'BILL-000002', 18, 1, '2026-04-14', NULL, 120.00, 0.00, 0.00, 120.00, 120.00, 0.00, 'PAID', '2026-04-15 16:55:39', NULL, 6, '2026-04-14 04:50:30', '2026-04-15 16:55:39'),
(3, 'BILL-000003', 16, 9, '2026-04-14', NULL, 250.00, 30.00, 26.40, 246.40, 246.40, 0.00, 'PAID', '2026-04-14 08:53:13', NULL, 6, '2026-04-14 06:16:55', '2026-04-14 08:53:13'),
(4, 'BILL-000004', 15, 7, '2026-04-14', NULL, 120.00, 0.00, 0.00, 120.00, 120.00, 0.00, 'PAID', '2026-04-14 06:54:20', NULL, 6, '2026-04-14 06:41:22', '2026-04-14 06:54:20'),
(5, 'BILL-000005', 13, 1, '2026-04-17', NULL, 200.00, 0.00, 0.00, 200.00, 199.87, 0.13, 'PARTIALLY_PAID', '2026-04-17 13:00:12', NULL, 6, '2026-04-17 13:00:12', '2026-04-17 13:00:12'),
(6, 'BILL-000006', 25, 25, '2026-04-18', NULL, 130.00, 0.00, 0.00, 130.00, 130.00, 0.00, 'PAID', '2026-04-18 06:15:06', NULL, NULL, '2026-04-18 06:15:06', '2026-04-18 06:15:06'),
(7, 'BILL-000007', 22, 25, '2026-04-18', NULL, 130.00, 0.00, 0.00, 130.00, 130.00, 0.00, 'PAID', '2026-04-18 18:06:09', NULL, 35, '2026-04-18 18:06:09', '2026-04-18 18:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `bill_item_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `test_id` int(11) DEFAULT NULL,
  `test_name` varchar(150) NOT NULL,
  `quantity` int(3) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill_items`
--

INSERT INTO `bill_items` (`bill_item_id`, `bill_id`, `test_id`, `test_name`, `quantity`, `unit_price`, `discount_amount`, `line_total`, `notes`, `created_at`) VALUES
(11, 2, 29, 'Renal Function', 1, 120.00, 0.00, 120.00, NULL, '2026-04-14 04:50:52'),
(12, 1, 29, 'Renal Function', 1, 120.00, 0.00, 120.00, NULL, '2026-04-14 05:04:32'),
(13, 1, 30, 'SERUM LIPID PROFILE', 1, 130.00, 0.00, 130.00, NULL, '2026-04-14 05:04:32'),
(24, 3, 29, 'Renal Function', 1, 120.00, 0.00, 120.00, NULL, '2026-04-14 06:40:50'),
(25, 3, 30, 'SERUM LIPID PROFILE', 1, 130.00, 0.00, 130.00, NULL, '2026-04-14 06:40:50'),
(28, 4, 29, 'Renal Function', 1, 120.00, 0.00, 120.00, NULL, '2026-04-14 06:54:20'),
(29, 5, 17, 'blood', 1, 200.00, 0.00, 200.00, NULL, '2026-04-17 13:00:12'),
(30, 6, 30, 'SERUM LIPID PROFILE', 1, 130.00, 0.00, 130.00, NULL, '2026-04-18 06:15:06'),
(31, 7, 30, 'SERUM LIPID PROFILE', 1, 130.00, 0.00, 130.00, NULL, '2026-04-18 18:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_users`
--

CREATE TABLE `dashboard_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('admin','receptionist','technician') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dashboard_users`
--

INSERT INTO `dashboard_users` (`user_id`, `username`, `password`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin01', 'admin123', 'admin01@lab.com', 'admin', 'active', '2025-10-13 13:19:23', '2025-10-13 13:19:23'),
(2, 'reception01', 'recept123', 'reception01@lab.com', 'receptionist', 'active', '2025-10-13 13:19:23', '2025-10-13 13:19:23'),
(3, 'tech01', 'tech123', 'tech01@lab.com', 'technician', 'active', '2025-10-13 13:19:23', '2025-10-13 13:19:23');

-- --------------------------------------------------------

--
-- Table structure for table `general_settings`
--

CREATE TABLE `general_settings` (
  `id` int(11) NOT NULL,
  `sms_alerts` tinyint(1) DEFAULT 1,
  `email_reports` tinyint(1) DEFAULT 1,
  `password_policy` varchar(50) DEFAULT '60',
  `session_timeout` int(11) DEFAULT 15,
  `language` varchar(50) DEFAULT 'en_US',
  `timezone` varchar(100) DEFAULT 'America/New_York',
  `currency` varchar(20) DEFAULT 'LKR',
  `date_format` varchar(20) DEFAULT 'dd/mm/yyyy',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `sms_alerts`, `email_reports`, `password_policy`, `session_timeout`, `language`, `timezone`, `currency`, `date_format`, `updated_at`) VALUES
(1, 0, 1, '90', 45, 'en_US', 'Asia/Kolkata', 'LKR', 'dd/mm/yyyy', '2026-03-03 15:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `reorder_level` int(255) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'In Stock',
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `unit_of_measure` varchar(50) DEFAULT 'Units',
  `expiry_date` date DEFAULT NULL,
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `supplier_id`, `quantity`, `reorder_level`, `last_updated`, `category_id`, `status`, `unit_cost`, `unit_of_measure`, `expiry_date`, `deleted_date`, `deleted_time`, `deleted_by`) VALUES
(1, 'Glucose Test Kit', 1, 50, 10, '2025-10-20 22:52:11', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(2, 'Blood Collection Tubes', 2, 120, 30, '2025-10-20 22:52:11', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(3, 'Urine Sample Bottles', 1, 80, 20, '2025-10-20 22:52:11', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(4, 'Microscope Slides', 3, 200, 40, '2025-10-20 22:52:11', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(5, 'COVID-19 Rapid Test Kit', 2, 60, 15, '2025-10-20 22:52:11', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(6, 'Latex Gloves', 3, 30, 50, '2025-10-21 08:45:45', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(9, 'gloves', 2, 1000, 50, '2025-10-22 18:09:48', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL),
(10, 'uni', 2, 100, 10, '2026-03-29 15:59:31', NULL, 'In Stock', NULL, 'Units', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `lab_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_configuration`
--

CREATE TABLE `lab_configuration` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(255) NOT NULL DEFAULT '',
  `accreditation` varchar(100) DEFAULT '',
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT '',
  `email` varchar(150) DEFAULT '',
  `logo_path` varchar(255) DEFAULT '',
  `hours_mon_fri_open` time DEFAULT '08:00:00',
  `hours_mon_fri_close` time DEFAULT '17:00:00',
  `hours_mon_fri_enabled` tinyint(1) DEFAULT 1,
  `hours_sat_open` time DEFAULT '09:00:00',
  `hours_sat_close` time DEFAULT '14:00:00',
  `hours_sat_enabled` tinyint(1) DEFAULT 1,
  `hours_sun_open` time DEFAULT '12:00:00',
  `hours_sun_close` time DEFAULT '12:00:00',
  `hours_sun_enabled` tinyint(1) DEFAULT 0,
  `allow_walkins` tinyint(1) DEFAULT 1,
  `auto_email_reports` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_configuration`
--

INSERT INTO `lab_configuration` (`id`, `lab_name`, `accreditation`, `address`, `phone`, `email`, `logo_path`, `hours_mon_fri_open`, `hours_mon_fri_close`, `hours_mon_fri_enabled`, `hours_sat_open`, `hours_sat_close`, `hours_sat_enabled`, `hours_sun_open`, `hours_sun_close`, `hours_sun_enabled`, `allow_walkins`, `auto_email_reports`, `updated_at`) VALUES
(1, 'LabSync', 'ISO-15189-2023', 'NO: 91 Reid avenue, colombo 07', '+94 77 123 4567', 'labsync@gmail.com', '/lab_sync/public/uploads/lab_logo_1776092943.png', '08:00:00', '17:00:00', 1, '09:00:00', '14:00:00', 1, '12:00:00', '12:00:00', 0, 1, 0, '2026-04-13 15:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `online_booking_slots`
--

CREATE TABLE `online_booking_slots` (
  `id` int(11) NOT NULL,
  `day_group` enum('mon_fri','sat','sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `max_patients` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Processing','Completed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `test_id` int(11) DEFAULT NULL,
  `outsourced_lab_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Processing','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `outsourced_labs`
--

CREATE TABLE `outsourced_labs` (
  `outsourced_lab_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partner_labs`
--

CREATE TABLE `partner_labs` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_phone` varchar(20) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_labs`
--

INSERT INTO `partner_labs` (`id`, `lab_name`, `email`, `contact_person_name`, `contact_person_phone`, `website`, `address`, `created_at`) VALUES
(1, 'niroka', 'niroka@lab.com', 'samn', '0714033320', '', 'no, 1 at this mawata at this location', '2026-02-16 09:22:35'),
(2, 'niroka', 'niroka@lab.com', 'Isum Manmitha', '0702836488', '', 'n0 3 at this street', '2026-02-16 09:23:27'),
(3, 'niroka', 'isumanmitha@gmail.com', 'Isum Manmitha', '0702836488', '', 'wertyuiofbgs2', '2026-02-16 09:25:01'),
(4, 'mokakhari', 'mokakahari@labs.com', 'Isum Manmitha', '1234567890', '', 'at this address', '2026-02-16 09:32:31'),
(5, 'help', 'help@lab.com', 'he he', '1234567890', '', 'some where or no where', '2026-02-16 09:42:21'),
(6, 'isum', 'isum@gmail.com', 'isum', '1234567890', '', 'this city', '2026-02-18 05:29:15');

-- --------------------------------------------------------

--
-- Table structure for table `partner_lab_tests`
--

CREATE TABLE `partner_lab_tests` (
  `id` int(11) NOT NULL,
  `partner_lab_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner_lab_tests`
--

INSERT INTO `partner_lab_tests` (`id`, `partner_lab_id`, `test_id`) VALUES
(1, 3, 1),
(2, 3, 2),
(3, 4, 1),
(4, 4, 2),
(5, 4, 3),
(6, 5, 1),
(7, 5, 4),
(8, 5, 5),
(9, 6, 1),
(10, 6, 2),
(11, 6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `uhid` varchar(50) DEFAULT NULL,
  `patient_name` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `uhid`, `patient_name`, `date_of_birth`, `gender`, `email`, `contact_number`, `created_at`, `updated_date`, `address`, `avatar_path`, `date_of_death`, `blood_group`) VALUES
(1, NULL, 'Isuru Perera', '1998-04-15', 'Male', 'isuru.perera@example.com', '0712045678', '2025-10-17 14:48:40', '2025-10-18 07:01:16', NULL, NULL, NULL, NULL),
(2, NULL, 'Nadeesha Fernando', '2000-09-21', 'Female', 'nadeesha.fernando@example.com', '0779876543', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL, NULL, NULL),
(3, NULL, 'Kasun Jayasinghe', '1995-12-02', 'Male', 'kasun.jayasinghe@example.com', '0751122334', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL, NULL, NULL),
(4, NULL, 'Rashmi Silva', '1999-06-10', 'Female', 'rashmi.silva@example.com', '0769988776', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL, NULL, NULL),
(5, NULL, 'Tharindu De Alwis', '1997-02-27', 'Male', 'tharindu.alwis@example.com', '0784455667', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL, NULL, NULL),
(7, NULL, 'saman kumara', '2025-10-16', 'Male', 'ucsc@gmail.com', '1234567890', '2025-10-20 08:33:26', '2025-10-20 08:33:26', NULL, NULL, NULL, NULL),
(8, NULL, 'manmitha', NULL, NULL, 'abc@gmail.com', '123456789', '2025-10-22 11:41:59', '2025-10-22 11:41:59', NULL, NULL, NULL, NULL),
(9, NULL, 'saman', NULL, NULL, 'saman@gmail.com', '12345678', '2025-10-22 11:43:33', '2025-10-22 11:43:33', NULL, NULL, NULL, NULL),
(11, NULL, 'karuni', NULL, 'Male', 'karu@gmail.com', '123456789', '2025-10-22 12:40:39', '2025-10-22 13:33:35', '', NULL, NULL, NULL),
(12, NULL, 'surin', NULL, 'Male', 'surini@gmail.com', '12345678', '2025-10-22 17:52:55', '2025-10-22 18:05:46', '', NULL, NULL, NULL),
(13, NULL, 'patient', NULL, NULL, 'patient@gmail.com', '123456789', '2025-10-22 18:37:41', '2025-10-22 18:37:41', NULL, NULL, NULL, NULL),
(20, NULL, 'yasindu', NULL, NULL, 'yas@gmail.com', '1234567890', '2025-10-23 04:27:41', '2025-10-23 04:27:41', NULL, NULL, NULL, NULL),
(22, NULL, 'yasindu6', NULL, NULL, 'yasindu6@gmail.com', '1234567890', '2025-10-23 05:26:29', '2025-10-23 05:26:29', NULL, NULL, NULL, NULL),
(23, NULL, 'sachindra', NULL, NULL, 'sachindrasenevirathna03@gmail.com', '0702345678', '2026-04-16 07:54:41', '2026-04-16 07:54:41', NULL, NULL, NULL, NULL),
(24, NULL, 'sachindra', NULL, NULL, 'sachindrasenevirathne2003@gmail.com', '0771234567', '2026-04-16 07:56:40', '2026-04-16 07:56:40', NULL, NULL, NULL, NULL),
(25, NULL, 'isumPatient', NULL, 'Male', 'isum.manmitha7@gmail.com', '0702836488', '2026-04-16 11:43:57', '2026-04-16 11:45:32', '', NULL, NULL, NULL),
(27, NULL, 'yasi', '2026-01-20', 'Other', NULL, '0712345678', '2026-04-18 13:14:52', '2026-04-18 13:14:52', NULL, NULL, NULL, 'A');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('CASH','CARD','TRANSFER') NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `bill_id`, `payment_amount`, `payment_method`, `reference_number`, `payment_date`, `notes`, `received_by`, `created_at`) VALUES
(1, 1, 250.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-13 20:42:06'),
(2, 1, 150.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-13 20:49:12'),
(3, 1, 250.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 04:46:38'),
(4, 2, 110.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 04:50:52'),
(5, 3, 200.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:23:48'),
(6, 3, 200.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:35:04'),
(7, 3, 200.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:37:03'),
(8, 3, 200.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:40:50'),
(9, 4, 120.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:41:22'),
(10, 4, 120.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:41:36'),
(11, 4, 120.00, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 06:54:20'),
(12, 3, 46.40, 'CASH', '', '2026-04-14', NULL, 6, '2026-04-14 08:53:13'),
(13, 2, 10.00, 'CASH', '', '2026-04-15', NULL, 6, '2026-04-15 16:55:39'),
(14, 5, 199.87, 'CASH', '', '2026-04-17', NULL, 6, '2026-04-17 13:00:12'),
(15, 6, 130.00, 'CARD', 'APT-25', '2026-04-18', NULL, NULL, '2026-04-18 06:15:06'),
(16, 7, 130.00, 'CASH', '', '2026-04-18', NULL, 35, '2026-04-18 18:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_requests`
--

CREATE TABLE `prescription_requests` (
  `request_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `request_type` varchar(40) NOT NULL DEFAULT 'PRESCRIPTION',
  `visit_type` varchar(40) NOT NULL DEFAULT 'ONSITE',
  `prescription_file_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `home_collection` tinyint(1) NOT NULL DEFAULT 0,
  `collection_address` varchar(255) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `decision_action` varchar(40) DEFAULT NULL,
  `decision_by_user_id` int(11) DEFAULT NULL,
  `decision_at` datetime DEFAULT NULL,
  `linked_appointment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription_requests`
--

INSERT INTO `prescription_requests` (`request_id`, `patient_id`, `request_type`, `visit_type`, `prescription_file_path`, `notes`, `symptoms`, `preferred_date`, `preferred_time`, `home_collection`, `collection_address`, `status`, `decision_action`, `decision_by_user_id`, `decision_at`, `linked_appointment_id`, `created_at`, `updated_at`) VALUES
(1, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776370515_9c82f06e.png', '', NULL, '2026-04-18', '17:45:00', 0, NULL, 'Pending', NULL, NULL, NULL, NULL, '2026-04-16 20:15:15', '2026-04-16 20:15:15'),
(2, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776370526_f1466480.png', '', NULL, '2026-04-18', '17:45:00', 0, NULL, 'Pending', NULL, NULL, NULL, NULL, '2026-04-16 20:15:26', '2026-04-16 20:15:26'),
(3, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776371081_ecf3d02b.png', '', NULL, '2026-04-25', '19:54:00', 0, NULL, 'Pending', NULL, NULL, NULL, NULL, '2026-04-16 20:24:41', '2026-04-16 20:24:41'),
(4, 25, 'PRESCRIPTION', 'HOME_VISIT', 'public/uploads/prescriptions/rx_25_1776372120_591e2a2d.png', '', NULL, '2026-04-24', NULL, 0, 'colombo', 'Booked', 'sent_to_patient', 6, '2026-04-17 18:24:28', 22, '2026-04-16 20:42:00', '2026-04-17 15:13:25'),
(5, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776375919_652df919.jpeg', '', NULL, '2026-04-25', NULL, 0, '', 'Communicated', 'sent_to_patient', 6, '2026-04-17 11:45:50', NULL, '2026-04-16 21:45:19', '2026-04-17 06:15:50'),
(8, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776408949_d3ae8242.png', '', NULL, '2026-04-18', '18:25:00', 0, '', 'Communicated', 'sent_to_patient', 6, '2026-04-17 12:26:13', NULL, '2026-04-17 06:55:49', '2026-04-17 06:56:13'),
(9, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776409152_6443be6d.png', '', NULL, '2026-04-18', '18:28:00', 0, '', 'Communicated', 'sent_to_patient', 6, '2026-04-17 12:30:26', NULL, '2026-04-17 06:59:13', '2026-04-17 07:00:26'),
(10, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776430166_c03e2b4d.png', '', NULL, NULL, NULL, 0, '', 'Booked', 'sent_to_patient', 6, '2026-04-17 18:20:53', 23, '2026-04-17 12:49:26', '2026-04-17 17:00:09'),
(11, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776491126_e12d3fb8.pdf', '', NULL, NULL, NULL, 0, '', 'Booked', 'sent_to_patient', 12, '2026-04-18 11:37:47', 25, '2026-04-18 05:45:26', '2026-04-18 06:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_request_events`
--

CREATE TABLE `prescription_request_events` (
  `event_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription_request_events`
--

INSERT INTO `prescription_request_events` (`event_id`, `request_id`, `event_type`, `old_status`, `new_status`, `note`, `created_by_user_id`, `created_at`) VALUES
(1, 5, 'communicated', 'Pending', 'Communicated', 'Sent to patient from receptionist manage modal.', 6, '2026-04-17 06:15:50'),
(2, 8, 'communicated', 'Pending', 'Communicated', 'Sent to patient from receptionist manage modal.', 6, '2026-04-17 06:56:13'),
(3, 9, 'communicated', 'Pending', 'Communicated', 'Sent to patient from receptionist manage modal.', 6, '2026-04-17 07:00:26'),
(4, 10, 'communicated', 'Pending', 'Communicated', 'Sent to patient from receptionist manage modal.', 6, '2026-04-17 12:50:53'),
(5, 4, 'communicated', 'Pending', 'Communicated', 'Sent to patient from receptionist manage modal.', 6, '2026-04-17 12:54:28'),
(6, 11, 'communicated', 'Pending', 'Communicated', 'Sent to patient from receptionist manage modal.', 12, '2026-04-18 06:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_request_tests`
--

CREATE TABLE `prescription_request_tests` (
  `request_test_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription_request_tests`
--

INSERT INTO `prescription_request_tests` (`request_test_id`, `request_id`, `test_id`, `unit_price`, `quantity`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 5, 5, 1800.00, 1, 1800.00, '2026-04-17 06:15:50', '2026-04-17 06:15:50'),
(2, 8, 5, 1800.00, 1, 1800.00, '2026-04-17 06:56:13', '2026-04-17 06:56:13'),
(3, 9, 1, 100.00, 1, 100.00, '2026-04-17 07:00:26', '2026-04-17 07:00:26'),
(4, 9, 21, 1200.00, 1, 1200.00, '2026-04-17 07:00:26', '2026-04-17 07:00:26'),
(5, 10, 4, 1200.00, 1, 1200.00, '2026-04-17 12:50:53', '2026-04-17 12:50:53'),
(6, 4, 30, 130.00, 1, 130.00, '2026-04-17 12:54:28', '2026-04-17 12:54:28'),
(7, 11, 30, 130.00, 1, 130.00, '2026-04-18 06:07:47', '2026-04-18 06:07:47');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `test_id` int(11) DEFAULT NULL,
  `reference_number` varchar(50) NOT NULL,
  `uhid` varchar(50) DEFAULT NULL,
  `referred_by` varchar(100) DEFAULT NULL,
  `sample_type` varchar(50) DEFAULT NULL,
  `sample_datetime` datetime DEFAULT NULL,
  `report_datetime` datetime DEFAULT NULL,
  `page_count` int(3) DEFAULT 1,
  `general_comments` text DEFAULT NULL,
  `pdf_relative_path` varchar(255) DEFAULT NULL,
  `pdf_original_name` varchar(190) DEFAULT NULL,
  `pdf_mime_type` varchar(80) DEFAULT 'application/pdf',
  `pdf_file_size` bigint(20) DEFAULT NULL,
  `pdf_generated_at` datetime DEFAULT NULL,
  `pdf_generated_by` int(11) DEFAULT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `pathologist_id` int(11) DEFAULT NULL,
  `status` enum('DRAFT','COMPLETED','AUTHORIZED','PRINTED') DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `appointment_id`, `test_id`, `reference_number`, `uhid`, `referred_by`, `sample_type`, `sample_datetime`, `report_datetime`, `page_count`, `general_comments`, `pdf_relative_path`, `pdf_original_name`, `pdf_mime_type`, `pdf_file_size`, `pdf_generated_at`, `pdf_generated_by`, `technician_id`, `pathologist_id`, `status`, `created_at`) VALUES
(1, 16, NULL, 'REF-16-29-20260413134651', NULL, NULL, NULL, NULL, '2026-04-13 13:46:51', 1, NULL, '2026/04/report_app16_test29_20260413_134651.pdf', 'report_app16_test29_20260413_134651.pdf', 'application/pdf', 1094, '2026-04-13 13:46:51', 6, NULL, NULL, 'AUTHORIZED', '2026-04-13 11:46:51'),
(2, 16, 30, 'REF-16-30-20260413_170914', NULL, NULL, NULL, NULL, '2026-04-13 17:09:14', 1, NULL, '2026/04/report_app16_test30_20260413_170914.pdf', 'report_app16_test30_20260413_170914.pdf', 'application/pdf', 75385, '0000-00-00 00:00:00', 6, NULL, NULL, 'AUTHORIZED', '2026-04-13 15:02:49'),
(3, 16, 29, 'REF-16-29-20260413_174447', NULL, NULL, NULL, NULL, '2026-04-13 17:44:47', 1, NULL, '2026/04/report_app16_test29_20260413_174447.pdf', 'report_app16_test29_20260413_174447.pdf', 'application/pdf', 74970, '0000-00-00 00:00:00', 6, NULL, NULL, 'AUTHORIZED', '2026-04-13 15:43:53'),
(4, 18, 29, 'REF-18-29-20260413_174614', NULL, NULL, NULL, NULL, '2026-04-13 17:46:14', 1, NULL, '2026/04/report_app18_test29_20260413_174614.pdf', 'report_app18_test29_20260413_174614.pdf', 'application/pdf', 74985, '2026-04-13 17:46:14', 11, NULL, NULL, 'AUTHORIZED', '2026-04-13 15:46:14'),
(5, 17, 29, 'REF-17-29-20260413_175028', NULL, NULL, NULL, NULL, '2026-04-13 17:50:28', 1, NULL, '2026/04/report_app17_test29_20260413_175028.pdf', 'report_app17_test29_20260413_175028.pdf', 'application/pdf', 74985, '2026-04-13 17:50:28', 11, NULL, NULL, 'AUTHORIZED', '2026-04-13 15:50:28'),
(6, 17, 30, 'REF-17-30-20260413_175245', NULL, NULL, NULL, NULL, '2026-04-13 17:52:45', 1, NULL, '2026/04/report_app17_test30_20260413_175245.pdf', 'report_app17_test30_20260413_175245.pdf', 'application/pdf', 75395, '2026-04-13 17:52:45', 11, NULL, NULL, 'AUTHORIZED', '2026-04-13 15:52:45'),
(7, 15, 29, 'REF-15-29-20260413_175411', NULL, NULL, NULL, NULL, '2026-04-13 17:54:11', 1, NULL, '2026/04/report_app15_test29_20260413_175411.pdf', 'report_app15_test29_20260413_175411.pdf', 'application/pdf', 74981, '2026-04-13 17:54:11', 11, NULL, NULL, 'AUTHORIZED', '2026-04-13 15:54:11'),
(8, 25, 30, 'REF-25-30-20260418_084147', NULL, NULL, NULL, NULL, '2026-04-18 08:41:47', 1, NULL, '2026/04/report_app25_test30_20260418_084147.pdf', 'report_app25_test30_20260418_084147.pdf', 'application/pdf', 75422, '2026-04-18 08:41:47', 11, NULL, NULL, 'AUTHORIZED', '2026-04-18 06:41:47'),
(9, 24, 30, 'REF-24-30-20260418_192451', NULL, NULL, NULL, NULL, '2026-04-18 19:24:51', 1, NULL, NULL, NULL, 'application/pdf', NULL, '2026-04-18 19:24:51', 11, NULL, NULL, 'AUTHORIZED', '2026-04-18 17:24:51'),
(10, 22, 30, 'REF-22-30-20260418_194853', NULL, NULL, NULL, NULL, '2026-04-18 19:48:53', 1, NULL, NULL, NULL, 'application/pdf', NULL, '2026-04-18 19:48:53', 11, NULL, NULL, 'AUTHORIZED', '2026-04-18 17:48:53');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `request_id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` int(11) NOT NULL,
  `request_type` enum('PRESCRIPTION','HOME_VISIT_NO_PRESCRIPTION') NOT NULL,
  `visit_type` enum('ONSITE','HOME_VISIT') NOT NULL,
  `prescription_file_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `collection_address` varchar(255) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `decision_action` varchar(40) DEFAULT NULL,
  `decision_by_user_id` int(11) DEFAULT NULL,
  `decision_at` datetime DEFAULT NULL,
  `linked_appointment_id` int(11) DEFAULT NULL,
  `source_table` varchar(40) DEFAULT NULL,
  `source_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`request_id`, `patient_id`, `request_type`, `visit_type`, `prescription_file_path`, `notes`, `symptoms`, `preferred_date`, `preferred_time`, `collection_address`, `status`, `decision_action`, `decision_by_user_id`, `decision_at`, `linked_appointment_id`, `source_table`, `source_id`, `created_at`, `updated_at`) VALUES
(1, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776370515_9c82f06e.png', '', NULL, '2026-04-18', '17:45:00', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-16 20:15:15', '2026-04-16 20:15:15'),
(2, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776370526_f1466480.png', '', NULL, '2026-04-18', '17:45:00', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-16 20:15:26', '2026-04-16 20:15:26'),
(3, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776371081_ecf3d02b.png', '', NULL, '2026-04-25', '19:54:00', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-16 20:24:41', '2026-04-16 20:24:41'),
(4, 25, 'PRESCRIPTION', 'HOME_VISIT', 'public/uploads/prescriptions/rx_25_1776372120_591e2a2d.png', '', NULL, '2026-04-24', NULL, 'colombo', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-16 20:42:00', '2026-04-16 20:42:00'),
(5, 25, 'PRESCRIPTION', 'ONSITE', 'public/uploads/prescriptions/rx_25_1776375919_652df919.jpeg', '', NULL, '2026-04-25', NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-16 21:45:19', '2026-04-16 21:45:19');

-- --------------------------------------------------------

--
-- Table structure for table `stock_history`
--

CREATE TABLE `stock_history` (
  `history_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_purchases`
--

CREATE TABLE `stock_purchases` (
  `purchase_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `quantity_purchased` int(11) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(12,2) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_no`, `location`, `email`, `created_at`, `updated_at`) VALUES
(1, 'ABC Medical Supplies', '0771234567', 'Colombo', 'abcmed@example.com', '2025-10-20 22:29:10', '2025-10-20 22:29:10'),
(2, 'HealthPlus Distributors', '0712345678', 'Kandy', 'healthplus@example.com', '2025-10-20 22:29:10', '2025-10-20 22:29:10'),
(3, 'BioTech Lab', '0759876543', 'Galle', 'biotechlabs@example.com', '2025-10-20 22:29:10', '2026-04-16 07:33:59');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `test_id` int(11) NOT NULL,
  `test_name` varchar(150) NOT NULL,
  `department` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_outsourced` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `test_code` varchar(50) NOT NULL,
  `lab_id` varchar(50) DEFAULT NULL,
  `print_name` varchar(150) NOT NULL,
  `default_unit` varchar(40) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `print_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `decimals` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `validation_required` tinyint(1) NOT NULL DEFAULT 0,
  `report_comments` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `methodology` varchar(255) DEFAULT NULL,
  `default_comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`test_id`, `test_name`, `department`, `description`, `price`, `is_outsourced`, `created_at`, `test_code`, `lab_id`, `print_name`, `default_unit`, `cost_price`, `discount_percent`, `print_order`, `decimals`, `is_active`, `validation_required`, `report_comments`, `updated_at`, `methodology`, `default_comment`) VALUES
(1, 'Complete Blood Count (CBC)', 'Other', '', 100.00, 0, '2025-09-02 08:24:24', 'T1', NULL, 'Complete Blood Count (CBC)', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(2, 'Liver Function Test (LFT)', 'Other', 'Evaluates liver enzymes, proteins, and bilirubin levels', 2500.00, 0, '2025-09-02 08:24:24', 'T2', NULL, 'Liver Function Test (LFT)', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(3, 'Thyroid Function Test (TFT)', 'Other', 'Assesses T3, T4, and TSH levels for thyroid performance', 3000.00, 1, '2025-09-02 08:24:24', 'T3', NULL, 'Thyroid Function Test (TFT)', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(4, 'Blood Sugar Test', 'Other', 'Measures fasting and random blood glucose levels', 1200.00, 0, '2025-09-02 08:24:24', 'T4', NULL, 'Blood Sugar Test', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(5, 'Cholesterol Test', 'Blood Test', 'Checks total cholesterol, HDL, LDL, and triglycerides', 1800.00, 0, '2025-09-02 08:24:24', 'T5', NULL, 'Cholesterol Test', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(6, 'COVID-19 PCR Test', 'Other', 'Detects SARS-CoV-2 virus genetic material', 6000.00, 1, '2025-09-02 08:24:24', 'T6', NULL, 'COVID-19 PCR Test', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(7, 'Urine Routine Test', 'Other', '', 1000.00, 0, '2025-09-02 08:24:24', 'T7', NULL, 'Urine Routine Test', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(8, 'Kidney Function Test (KFT)', 'Other', 'Measures urea, creatinine, and electrolytes', 2200.00, 1, '2025-09-02 08:24:24', 'T8', NULL, 'Kidney Function Test (KFT)', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(9, 'sdfghjm', 'urine', NULL, 12344.00, 0, '2025-10-15 13:42:20', 'T9', NULL, 'sdfghjm', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(10, 'ghjk', 'molecular', NULL, 1234.00, 0, '2025-10-15 13:54:55', 'T10', NULL, 'ghjk', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(17, 'blood', 'blood', '', 200.00, 0, '2025-10-17 08:16:09', 'T17', NULL, 'blood', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(20, 'pressure test', 'imaging', 'a test to check the blood pressure', 1200.00, 0, '2025-10-22 18:07:38', 'T20', NULL, 'pressure test', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(21, 'Blood Test', 'blood', 'shghjjkdjljkcl', 1200.00, 0, '2025-10-23 04:30:35', 'T21', NULL, 'Blood Test', 'N/A', 0.00, 0.00, 0, 2, 1, 0, NULL, '2026-04-07 07:44:51', NULL, NULL),
(27, 'indigo123', 'Biochemistry', '', 150.00, 0, '2026-04-07 08:04:29', 'j', 'ge', 'crea', 'mg/dl', 150.00, 0.00, 0, 2, 0, 0, 'no idea', '2026-04-07 08:04:29', NULL, NULL),
(29, 'Renal Function', 'Biochemistry', '', 120.00, 0, '2026-04-12 09:53:14', 'T021', 'loo1', 'CREATININE AND eGFR..', 'mg/dl', 120.00, 0.00, 0, 2, 1, 1, '', '2026-04-12 09:53:14', NULL, NULL),
(30, 'SERUM LIPID PROFILE', 'biochemistry', '', 130.00, 0, '2026-04-12 15:30:01', 'T003', 'L001', 'SERUM LIPID PROFILE', 'mg /dl', 130.00, 0.00, 0, 2, 1, 0, '', '2026-04-18 22:55:41', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `test_comments`
--

CREATE TABLE `test_comments` (
  `comment_id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `display_order` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_comments`
--

INSERT INTO `test_comments` (`comment_id`, `result_id`, `comment_text`, `display_order`) VALUES
(2, 2, 'normal conditions for each', 0),
(3, 3, 'normal conditions for each', 0),
(4, 4, 'normal conditions for each', 0),
(6, 1, 'no abnormal conditions', 0),
(10, 1, 'no abnormal conditions', 999),
(11, 2, 'normal conditions for each', 999),
(12, 5, 'Everthing normal', 0),
(13, 6, 'Everthing normal', 0),
(14, 7, 'Everthing normal', 0),
(15, 5, 'Everthing normal', 999),
(16, 10, 'nothing speacila', 0),
(17, 10, 'nothing speacila', 999),
(18, 11, 'The Cholesterol level is too high, pls contact your doctor immediately', 0),
(19, 12, 'The Cholesterol level is too high, pls contact your doctor immediately', 0),
(20, 13, 'The Cholesterol level is too high, pls contact your doctor immediately', 0),
(21, 11, 'The Cholesterol level is too high, pls contact your doctor immediately', 999),
(22, 14, 'no specaility', 0),
(23, 15, 'no specaility', 0),
(24, 16, 'no specaility', 0),
(25, 14, 'no specaility', 999),
(26, 17, 'no annomalies , everything normal', 0),
(27, 18, 'no annomalies , everything normal', 0),
(28, 19, 'no annomalies , everything normal', 0),
(29, 17, 'no annomalies , everything normal', 999);

-- --------------------------------------------------------

--
-- Table structure for table `test_partner_charges`
--

CREATE TABLE `test_partner_charges` (
  `test_partner_charge_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `partner_lab_id` int(11) NOT NULL,
  `external_test_code` varchar(60) DEFAULT NULL,
  `charge_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_partner_charges`
--

INSERT INTO `test_partner_charges` (`test_partner_charge_id`, `test_id`, `partner_lab_id`, `external_test_code`, `charge_cost`, `created_at`, `updated_at`) VALUES
(1, 27, 1, 'e01', 120.00, '2026-04-07 08:04:29', '2026-04-07 08:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `test_reference_ranges`
--

CREATE TABLE `test_reference_ranges` (
  `range_id` int(11) NOT NULL,
  `range_label` varchar(50) DEFAULT NULL,
  `interpretation` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `range_index` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gender` enum('ALL','M','F') NOT NULL DEFAULT 'ALL',
  `age_min` decimal(6,2) DEFAULT NULL,
  `age_max` decimal(6,2) DEFAULT NULL,
  `ref_min` decimal(12,4) DEFAULT NULL,
  `ref_max` decimal(12,4) DEFAULT NULL,
  `critical_value` decimal(12,4) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_reference_ranges`
--

INSERT INTO `test_reference_ranges` (`range_id`, `range_label`, `interpretation`, `unit_id`, `range_index`, `gender`, `age_min`, `age_max`, `ref_min`, `ref_max`, `critical_value`, `is_primary`, `created_at`) VALUES
(1, NULL, NULL, 1, 0, 'M', 0.00, 20.00, 10.0000, 20.0000, 23.0000, 0, '2026-04-07 08:04:29'),
(2, 'normal range', NULL, 3, 0, 'ALL', 40.00, 60.00, 0.6000, 0.9000, NULL, 0, '2026-04-12 09:53:14'),
(3, 'Desirable', NULL, 4, 0, 'ALL', 40.00, 60.00, 0.0000, 200.0000, NULL, 0, '2026-04-12 15:30:01'),
(4, 'Borderline', NULL, 4, 1, 'ALL', 40.00, 60.00, 201.0000, 239.0000, NULL, 0, '2026-04-12 15:30:01'),
(5, 'High', NULL, 4, 2, 'ALL', 40.00, 60.00, 2401000.0000, 500.0000, NULL, 0, '2026-04-12 15:30:01'),
(6, 'normal range', NULL, 5, 0, 'ALL', 40.00, 60.00, 10.0000, 20.0000, NULL, 0, '2026-04-12 15:30:01'),
(7, 'better', NULL, 6, 0, 'ALL', 40.00, 60.00, 1.3000, 1.6000, NULL, 0, '2026-04-12 15:30:01'),
(8, 'best', NULL, 6, 1, 'ALL', 40.00, 60.00, 1.6100, 100.0000, NULL, 0, '2026-04-12 15:30:01');

-- --------------------------------------------------------

--
-- Table structure for table `test_results`
--

CREATE TABLE `test_results` (
  `result_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `measured_value` decimal(12,4) DEFAULT NULL,
  `flag` enum('N','L','H','LL','HH') DEFAULT 'N',
  `entered_by` int(11) DEFAULT NULL,
  `entered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_results`
--

INSERT INTO `test_results` (`result_id`, `appointment_id`, `test_id`, `unit_id`, `measured_value`, `flag`, `entered_by`, `entered_at`, `verified_by`, `verified_at`) VALUES
(1, 16, 29, 3, 0.8000, 'N', 6, '2026-04-12 16:05:17', NULL, NULL),
(2, 16, 30, 4, 123.0000, 'N', 6, '2026-04-12 15:40:17', NULL, NULL),
(3, 16, 30, 5, 14.0000, 'N', 6, '2026-04-12 15:40:17', NULL, NULL),
(4, 16, 30, 6, 1.5000, 'N', 6, '2026-04-12 15:40:17', NULL, NULL),
(5, 17, 30, 4, 100.0000, 'N', 11, '2026-04-13 15:52:38', NULL, NULL),
(6, 17, 30, 5, 10.2500, 'N', 11, '2026-04-13 15:52:38', NULL, NULL),
(7, 17, 30, 6, 1.5000, 'N', 11, '2026-04-13 15:52:38', NULL, NULL),
(8, 18, 29, 3, 0.7000, 'N', 6, '2026-04-13 09:38:40', NULL, NULL),
(9, 17, 29, 3, 0.8200, 'N', 11, '2026-04-13 15:50:22', NULL, NULL),
(10, 15, 29, 3, 0.6200, 'N', 11, '2026-04-13 15:54:05', NULL, NULL),
(11, 25, 30, 4, 100.0000, 'N', 11, '2026-04-18 06:39:05', NULL, NULL),
(12, 25, 30, 5, 15.0000, 'N', 11, '2026-04-18 06:39:05', NULL, NULL),
(13, 25, 30, 6, 18.0000, 'H', 11, '2026-04-18 06:39:05', NULL, NULL),
(14, 24, 30, 4, 100.0000, 'N', 6, '2026-04-18 17:22:42', NULL, NULL),
(15, 24, 30, 5, 15.0000, 'N', 6, '2026-04-18 17:22:42', NULL, NULL),
(16, 24, 30, 6, 1.4000, 'N', 6, '2026-04-18 17:22:42', NULL, NULL),
(17, 22, 30, 4, 10.0000, 'N', 11, '2026-04-18 17:48:49', NULL, NULL),
(18, 22, 30, 5, 10.0000, 'N', 11, '2026-04-18 17:48:49', NULL, NULL),
(19, 22, 30, 6, 1.4000, 'N', 11, '2026-04-18 17:48:49', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `test_units`
--

CREATE TABLE `test_units` (
  `unit_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `unit_index` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `value_name` varchar(100) NOT NULL,
  `unit_name` varchar(40) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `conversion_factor` decimal(10,6) DEFAULT NULL,
  `conversion_target_unit` varchar(40) DEFAULT NULL,
  `report_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_units`
--

INSERT INTO `test_units` (`unit_id`, `test_id`, `unit_index`, `value_name`, `unit_name`, `is_default`, `created_at`, `conversion_factor`, `conversion_target_unit`, `report_note`) VALUES
(1, 27, 0, 'fbs', 'mg/dl', 1, '2026-04-07 08:04:29', NULL, NULL, NULL),
(3, 29, 0, 'SERUM CREATININE (ENZYMATIC)', 'mg/dl', 1, '2026-04-12 09:53:14', NULL, NULL, NULL),
(4, 30, 0, 'SERUM CREATININE (ENZYMATIC)', 'mg/dl', 1, '2026-04-12 15:30:01', NULL, NULL, NULL),
(5, 30, 1, 'SERUM TRIGLYCERIDES', 'mg/dl', 0, '2026-04-12 15:30:01', NULL, NULL, NULL),
(6, 30, 2, 'CHOLESTEROL-H.D.L.', 'md/dl', 0, '2026-04-12 15:30:01', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `role` enum('admin','receptionist','technician','patient') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `must_change_password`, `email`, `contact_number`, `role`, `status`, `created_at`, `updated_at`) VALUES
(3, 'tech01', 'tech123hashed', 0, 'tech01@example.com', '0773456789', 'technician', 'active', '2025-10-20 13:33:50', '2025-10-20 13:33:50'),
(4, 'patient01', 'patient123hashed', 0, 'patient01@example.com', '0774567890', 'patient', 'active', '2025-10-20 13:33:50', '2025-10-20 13:33:50'),
(5, 'patient02', 'patient456hashed', 0, 'patient02@example.com', '0775678901', 'patient', 'active', '2025-10-20 13:33:50', '2025-10-20 13:33:50'),
(6, 'admin1', '$2y$10$AhtaBI36yrUs3RQM88wI6.2ijAd7otDnUju3KtLIB/Y9dLQwanSqC', 0, 'isumanmitha@gmail.com', '0712340678', 'admin', 'active', '2025-10-20 20:45:17', '2026-04-15 21:07:39'),
(8, 'admin2', '$2y$10$gGR.AibzYrfgG7FHxb8BbuoEok8Iba4U6IviXqvqj86kUuSuYK0Xy', 0, 'udemy4ucsc@gmail.com', '0712340678', 'admin', 'active', '2025-10-21 12:36:54', '2025-10-21 12:36:54'),
(10, 'patient123', '$2y$10$p8yspwoq7kJ07Y1gfxIlgOk9byNPi9iUebwoPK4/QS5Ma3E0xLQhK', 0, 'pasidu@gmail.com', '0712340678', 'patient', 'active', '2025-10-21 21:16:09', '2025-10-21 21:16:09'),
(11, 'tech1', '$2y$10$0UfWVdPIZTJAs/AvY.faP.fhgxUGMOyMpfvZrnxFHvlOJgrfvD3UW', 0, 'ucsc@gmail.com', '1234567890', 'technician', 'active', '2025-10-22 02:58:29', '2025-10-22 02:58:29'),
(12, 'recep1', '$2y$10$wosfvpPY1DfIeu6egbt8/O.80THZEZpxBgHJfAjWEGuy1CUr6ts7i', 0, 'niyumineth@gmail.com', '1234567890', 'receptionist', 'active', '2025-10-22 02:59:19', '2025-10-22 02:59:19'),
(13, 'manmitha', '$2y$10$gFIpaPfyFOpY5HkEhf/.Ce0THUQ92pTi3/n9tUpA0Fs3dB5t29FXy', 0, 'abc@gmail.com', '123456789', 'patient', 'active', '2025-10-22 11:41:59', '2025-10-22 11:41:59'),
(14, 'saman', '$2y$10$bEyprN5vhjSJt6L0tYCe7e12j.5OXpamofZ2M2CSp49NYhjOx8qmi', 0, 'saman@gmail.com', '12345678', 'patient', 'active', '2025-10-22 11:43:33', '2025-10-22 11:43:33'),
(15, 'kumara', '$2y$10$MOMt46Zb.R4RpLk1RkLLfOa9Tjs1edwoWWBjhgGTyea7oChqQpa16', 0, 'kumara@gmail.com', '0712340678', 'patient', 'active', '2025-10-22 11:57:43', '2025-10-22 11:57:43'),
(16, 'sam', '$2y$10$4/9ftqTv7GohhMnEsYQv8.4kw16grzs/rx86zuUz6hfzBkbLz30SO', 0, 'sam@gmail.com', '0712340678', 'patient', 'active', '2025-10-22 12:02:25', '2025-10-22 12:02:25'),
(17, 'samy', '$2y$10$J12Hpr5SOtHcswAwyx3/XOtjK/DaT1mlhQFldSgaetaJsL7ozoXIi', 0, 'samy@gmail.com', '0712340678', 'patient', 'active', '2025-10-22 12:06:18', '2025-10-22 12:06:18'),
(18, 'sami', '$2y$10$rfZQ/UkztaXbRirIcmSgU.Hd6a9snGOMxM1Z4SuMfXsXqkQBdIjBW', 0, 'sami@gmail.com', '123456789', 'patient', 'active', '2025-10-22 12:15:38', '2025-10-22 12:15:38'),
(19, 'suri', '$2y$10$j.aoKQbRi2tvQGzuRqcLq.sY32WI/00polu19XnZ/SUayzg9Pr4v6', 0, 'suri@gmail.com', '123456789', 'patient', 'active', '2025-10-22 12:37:26', '2025-10-22 12:37:26'),
(20, 'karuni', '$2y$10$btyVt.lsFbQGSQaWeyHDYOo2OQpWE0pJWr4LGonE0dCHe2cLMB7My', 0, 'karu@gmail.com', '123456789', 'patient', 'active', '2025-10-22 12:40:39', '2025-10-22 13:33:35'),
(21, 'surini', '$2y$10$v7PSYqpvnfpr1FtqjJAaheBvtCZpk0yFkzia5sMNIeFZ1rAzwyUB2', 0, 'surini@gmail.com', '12345678', 'patient', 'active', '2025-10-22 17:52:55', '2025-10-22 17:58:10'),
(22, 'admin4', '$2y$10$0OaIL0oNy/g8mR0KHsgUT.bq9VOQVIqnqPCR/ENJtS6sqYRpguuOi', 0, 'acbs@gmail.com', '123456789', 'admin', 'active', '2025-10-22 18:28:24', '2025-10-23 04:12:16'),
(23, 'patient', '$2y$10$1T.GsX2Yr83RiKiiYKntDeCGm17roZIHgTey.LkbDYtPCWJa7qHfe', 0, 'patient@gmail.com', '123456789', 'patient', 'active', '2025-10-22 18:37:41', '2025-10-22 18:37:41'),
(27, 'admin3', '$2y$10$pLXz4Ai0My.Rj6OyiAbYU.FHXS9BAO44LEk3n4kn50AN3m/JMigBm', 0, 'admin3@gmail.com', '`123456789', 'admin', 'active', '2025-10-23 04:14:18', '2025-10-23 04:14:18'),
(28, 'yasindu', '$2y$10$ek5ST2dAnlcoFAG8vhViIOu4mp9gqIhD4ZTK1TwpMDezcH7z0Ql3G', 0, 'yas@gmail.com', '1234567890', 'patient', 'active', '2025-10-23 04:27:41', '2025-10-23 04:27:41'),
(30, 'yasindu6', '$2y$10$b2KHWTOhVVS9PLtI.glBsuxwKuT.sd2Dug6Iv/.r94ZDhsD0w3dyK', 0, 'yasindu6@gmail.com', '1234567890', 'patient', 'active', '2025-10-23 05:26:29', '2025-10-23 05:26:29'),
(31, 'MLT1', '$2y$10$87czWpgyHjMgoQH/kjoH8ulHfGpevV0DUVOIQeqPk/cDk9bvr.Zsq', 0, 'mlt@gmail.com', '0712345678', 'admin', 'active', '2026-02-16 08:28:33', '2026-02-16 08:28:33'),
(32, '', '$2y$10$aIzptgfD3PRykjfwwV.sf.Y6YE.SX1nqMzIm9pGYo.L8Ao2vBX5tC', 0, 'sachindrasenevirathna03@gmail.com', '0702345678', 'patient', 'active', '2026-04-16 07:54:41', '2026-04-16 07:54:41'),
(33, '', '$2y$10$WWujfBXP.Xnh9Q3xS.lpluvIhN9N4C/i0TJqOuSvePfXaYvVZeJPK', 0, 'sachindrasenevirathne2003@gmail.com', '0771234567', 'patient', 'active', '2026-04-16 07:56:40', '2026-04-16 07:56:40'),
(34, 'isumPatient', '$2y$10$v5Q0eI71Ly26WVXdWJ2sROe4wfYeNJp0itZIOU33oXpNAg.Q7lTtq', 0, 'isum.manmitha7@gmail.com', '0702836488', 'patient', 'active', '2026-04-16 11:43:57', '2026-04-16 11:45:32'),
(35, 'nullUser', '$2y$10$VAFI2DB60.65dfvWSXktl.uG7vO7gJvroDQfEKUucM0BATW1fBE3S', 0, 'nullp56@gmail.com', '07023237474', 'receptionist', 'active', '2026-04-18 17:17:21', '2026-04-18 17:20:16'),
(36, 'resultUser', '$2y$10$ijkjl6tPB.1zF4BrtVzDSO17sfTiesJROMDO9btp0IM.APOAGlQAe', 1, 'resultse61@gmail.com', '0721231234', 'technician', 'active', '2026-04-18 17:43:49', '2026-04-18 17:43:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_2fa`
--

CREATE TABLE `user_2fa` (
  `twofa_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `method` enum('TOTP','SMS','EMAIL') NOT NULL DEFAULT 'TOTP',
  `secret_key` varchar(255) DEFAULT NULL,
  `recovery_codes` text DEFAULT NULL,
  `last_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_2fa`
--

INSERT INTO `user_2fa` (`twofa_id`, `user_id`, `is_enabled`, `method`, `secret_key`, `recovery_codes`, `last_verified_at`, `created_at`, `updated_at`) VALUES
(1, 13, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(2, 22, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(3, 27, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(4, 6, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:43:21'),
(5, 20, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(6, 15, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(7, 31, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(8, 12, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(9, 10, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(10, 4, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(11, 5, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(12, 23, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(13, 16, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(14, 14, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(15, 18, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(16, 17, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(17, 19, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(18, 21, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(19, 3, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(20, 11, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(21, 8, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(22, 28, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(23, 30, 0, 'TOTP', NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(121, 35, 0, 'TOTP', NULL, NULL, NULL, '2026-04-18 17:19:29', '2026-04-18 17:19:29'),
(128, 36, 0, 'TOTP', NULL, NULL, NULL, '2026-04-18 22:28:12', '2026-04-18 22:28:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `pref_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `sms_alerts` tinyint(1) NOT NULL DEFAULT 0,
  `quiet_hours_start` time DEFAULT NULL,
  `quiet_hours_end` time DEFAULT NULL,
  `theme_mode` enum('Light','Dark','System') NOT NULL DEFAULT 'System',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`pref_id`, `user_id`, `email_notifications`, `sms_alerts`, `quiet_hours_start`, `quiet_hours_end`, `theme_mode`, `created_at`, `updated_at`) VALUES
(1, 13, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(2, 22, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(3, 27, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(4, 6, 1, 0, '22:00:00', '07:00:00', 'System', '2026-04-15 20:00:13', '2026-04-15 20:43:34'),
(5, 20, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(6, 15, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(7, 31, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(8, 12, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(9, 10, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(10, 4, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(11, 5, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(12, 23, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(13, 16, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(14, 14, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(15, 18, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(16, 17, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(17, 19, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(18, 21, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(19, 3, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(20, 11, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(21, 8, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(22, 28, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(23, 30, 1, 0, NULL, NULL, 'System', '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(121, 35, 1, 0, NULL, NULL, 'System', '2026-04-18 17:19:29', '2026-04-18 17:19:29'),
(128, 36, 1, 0, NULL, NULL, 'System', '2026-04-18 22:28:12', '2026-04-18 22:28:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile_details`
--

CREATE TABLE `user_profile_details` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(120) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `residential_address` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profile_details`
--

INSERT INTO `user_profile_details` (`profile_id`, `user_id`, `full_name`, `date_of_birth`, `gender`, `residential_address`, `avatar_path`, `created_at`, `updated_at`) VALUES
(1, 3, 'tech01', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(2, 4, 'patient01', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(3, 5, 'patient02', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(4, 6, 'admin1', '2004-01-29', 'Male', NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:54:44'),
(5, 8, 'admin2', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(6, 10, 'patient123', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(7, 11, 'tech1', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(8, 12, 'recep1', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(9, 13, 'manmitha', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(10, 14, 'saman', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(11, 15, 'kumara', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(12, 16, 'sam', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(13, 17, 'samy', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(14, 18, 'sami', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(15, 19, 'suri', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(16, 20, 'karuni', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(17, 21, 'surini', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(18, 22, 'admin4', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(19, 23, 'patient', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(20, 27, 'admin3', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(21, 28, 'yasindu', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(22, 30, 'yasindu6', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(23, 31, 'MLT1', NULL, NULL, NULL, NULL, '2026-04-15 20:00:13', '2026-04-15 20:00:13'),
(120, 35, 'nullp56@gmail.com', NULL, NULL, NULL, NULL, '2026-04-18 17:19:29', '2026-04-18 17:19:29'),
(127, 36, 'resultse61@gmail.com', NULL, NULL, NULL, NULL, '2026-04-18 22:28:12', '2026-04-18 22:28:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `user_session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `php_session_id` varchar(128) NOT NULL,
  `session_token` char(64) NOT NULL,
  `device_label` varchar(120) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `logged_in_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime NOT NULL DEFAULT current_timestamp(),
  `logged_out_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`user_session_id`, `user_id`, `php_session_id`, `session_token`, `device_label`, `ip_address`, `user_agent`, `logged_in_at`, `last_activity`, `logged_out_at`, `is_active`) VALUES
(1, 6, 'iaug3t06di17m6ubcng7epsv8i', 'ff7294aea63d7214d507fcdf7885cb321fbae6fe1c32f1f49a471d1b614f2b0b', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 02:16:16', '2026-04-16 13:23:07', '2026-04-16 13:23:07', 0),
(2, 33, '54rk503rhvgu3uovh0tcsestuq', 'c461a4da6ca8ef72aba8f3ffa0a46ad00027820efa69f28437b1c01043dd8b80', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 13:26:55', '2026-04-16 15:07:31', '2026-04-16 15:07:31', 0),
(3, 6, 'hnbmio14av00u0me8ddmkgu0ga', 'c47b69b1b18a57f6fe0b8dc8a3227d2d237f91a9458c731579838dcb3f6b922b', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 15:08:00', '2026-04-16 17:12:44', '2026-04-16 17:12:44', 0),
(4, 6, '44riqc5n3pa0vf9f6ucic1d4qh', '53ee6610216e4710382245576b2f666c58c7412753ea7208479289648b4f907b', 'Windows - Edge', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-16 15:24:48', '2026-04-16 15:24:56', '2026-04-16 22:13:16', 0),
(5, 34, 'snfgjbvbk7g0f5un8ha3edrspo', '98a4f501bef12f72d24f90c5c3d298178a3de5201696f50d6540e729c00d1c9b', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 17:15:09', '2026-04-16 18:38:09', '2026-04-16 18:38:09', 0),
(6, 6, 'qmcj1uobuo22d13svgar9bm5rf', '937de54d0cfcbb291fe699520046774d1a96ccc275d78c71d0516d264ec52690', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 18:38:31', '2026-04-16 19:17:29', '2026-04-16 19:17:29', 0),
(7, 34, '6rfqfljr50bk3lr6hcn8dlqjm9', '327380afc9244bea4fde1f29662b6605e0315e7eb24aa311d395234da025d31c', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 19:17:55', '2026-04-16 19:19:07', '2026-04-16 19:19:07', 0),
(8, 6, 'eteoi9bu4ck1dus50264t35539', 'f3883d0de8efab9b582a57bf7821425cd21c2ea0023a5f1214d286221465b7d7', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 19:19:40', '2026-04-16 19:19:40', '2026-04-16 22:12:06', 0),
(9, 6, '9962hrsg03tps6f8thdld3dttc', '9c937c6ac5f3148aaa860971c3b43f312af0247f0d2c712a90c59f8d54ce410a', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 22:00:01', '2026-04-16 22:21:53', '2026-04-16 22:21:53', 0),
(10, 34, 'jv2m7ugenlqpvc28slf4a1t9q4', '6a4ce230530d128abfa0bf3d5b962226e6a7f563e421263df7e5d9a01e63fbad', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 22:22:16', '2026-04-16 22:23:22', '2026-04-16 22:23:22', 0),
(11, 6, '481f372rcnhspe4nmq6qtsf83s', '69c8c1cade76ba39edda7841266ef4ce159d7b06bf284f2dacbe8e6a0b26cb8f', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-16 22:23:47', '2026-04-17 01:09:49', '2026-04-17 01:09:49', 0),
(12, 34, 'b4rfpsv9k5cb9aqd30986hujal', '9f54fc58650a60fa28bb4c84e65dbd4eb8239d3313878da1a324d4ae971a99b4', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 01:10:04', '2026-04-17 09:22:56', '2026-04-17 09:22:56', 0),
(13, 6, '6g3qpnu35efmaj72kav2995h36', '5d0daf74b9cf6d6236f8dd484c53a3da2d4d224a83fd7818daf822181b9f0ac4', 'Windows - Edge', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-17 01:13:06', '2026-04-17 01:13:06', '2026-04-17 10:39:47', 0),
(14, 6, 'oun9vcs0g7re1qn66cmrs1se8t', 'c2dffa5af7ba98df207502fae24bf68926b961ef5ab253976a642fa703d42fd2', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 09:23:18', '2026-04-17 09:23:18', NULL, 1),
(15, 6, '048qgvmo0akrstkunkn46momku', 'f482184550e6e8315bb7cfa066b2204468e51b36bca276b09b0310504854ad32', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-17 10:33:04', '2026-04-18 08:03:31', NULL, 1),
(16, 34, 'l32qq2usp48k0833ug96kh7m7b', 'f13a186ea144b9026eec98b1c8e2dfc44429b3018555b6dc45b3610fb262ee9f', 'Windows - Edge', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-17 11:35:26', '2026-04-17 18:16:53', '2026-04-17 18:16:53', 0),
(17, 34, 'iplbuqi7na1b8i0cv1gi8eds3v', '46e822d12bb9240add8864752fd3b8c98f9bfaa0c3544b6e821888dadfc56b4e', 'Windows - Edge', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-17 18:17:20', '2026-04-17 18:17:20', NULL, 1),
(18, 6, 'hf0dhslrhoibnlvhqvmnu0ds2n', 'a3d2978c065e2ad0d573b7babd9196a6b5c35c487b93de20df970a57380fd964', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 10:20:10', '2026-04-18 16:12:40', '2026-04-18 16:12:40', 0),
(19, 34, 'b8hkso7nk67kt4eskki8s6h1a2', 'bad2999935feafca84707aa173278dfb7fd12fea29a68b40df2b3223feccae32', 'Windows - Edge', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-18 10:20:38', '2026-04-18 10:20:38', NULL, 1),
(20, 12, 'hnkn0kkhui3h8dcq5ug0mqv0km', '3884825d3bc89d231ab7776028d1001dde7aa1588199df7e1eea295a1889c0f0', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 11:26:51', '2026-04-18 11:28:05', NULL, 1),
(21, 11, 'ije7upbq0tscfhi1f3u2cif6do', '785d7d281b7e1bbdc3d5f1a8b0ca4357de89345f3aaaa48eda9fefa6ad4063df', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 11:32:32', '2026-04-18 11:32:32', NULL, 1),
(22, 34, '387nkgrdvbkqn477urt8rtmg27', '20480ca97d1c00085dba05cb5a5ce6ee139ffef8230d500ba36015dd0254c1ab', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 16:13:14', '2026-04-18 16:27:01', '2026-04-18 16:27:01', 0),
(23, 6, 'v6iev2s8sdq0cgfgm8uipcpo6m', '38f6ab067bd93236f6fcbdc22e8f48505751979c509f85e213a57b3696937535', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 16:27:12', '2026-04-18 16:27:12', NULL, 1),
(24, 6, 'kqb7mj2t6ejh8a1mfmat8dkku7', 'be8b8072940386b689ab124b6a55f1627e9264da97393c9c75edd07fa08015e3', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 22:45:32', '2026-04-19 09:18:39', NULL, 1),
(25, 35, 'm6mtsq0v5vofgjs7d8n373tlug', '5165a7ec7152b3537791930ebc274f7925fd3cd48c89d514217e4db987b19feb', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 22:49:29', '2026-04-19 03:56:33', NULL, 1),
(26, 11, '9d3i8mnvehsj444ublie93cnmq', '2b2de1e8a59d10226b74159372a8a30fd9c46ccb6754dc4fb0907d5f67e2dbd2', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 22:54:38', '2026-04-18 22:54:38', NULL, 1),
(27, 34, '3qknr6ltkto1sevpv7pj0q710l', 'b39b85057f757456b55ca913fb4a174a93abb7b3b9d26e599337a8e13489fa2e', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-18 23:37:13', '2026-04-18 23:37:13', NULL, 1),
(28, 36, 'lpjqdl4sen5u59hde544qc0056', '4b095b90e369f15644c15ad777e7ac52759633ff5b96e0b789706e89ba9dfc86', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-19 03:57:59', '2026-04-19 04:13:08', NULL, 1),
(29, 6, 'su4f91u3hbgph1o8rljcteo2tu', 'df806c73f04237009187b2e04763b66788e1ac236f069e736f44c8c5096f6b92', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-19 11:57:07', '2026-04-19 12:29:34', NULL, 1),
(30, 35, 'n1g3vb0r8jidk8rtib0mo5mg6q', '022feb69d8b8ab3248f1639b9f270986dff86629d216a8abe9099ecdc76713c4', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-19 12:15:12', '2026-04-19 12:15:12', NULL, 1),
(31, 34, 'fk0kku6u35b6vkqi8djelbmcnr', 'c0a7654de5bdd6ecf5cae10a904a85fdbc47a3b2149b2566dd8ee375bb7c2933', 'Windows - Chrome', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-19 12:23:54', '2026-04-19 12:23:54', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `deleted_by` (`deleted_by`),
  ADD KEY `idx_appointment_time_slot` (`time_slot_id`,`appointment_date`);

--
-- Indexes for table `appointment_items`
--
ALTER TABLE `appointment_items`
  ADD PRIMARY KEY (`appointment_item_id`),
  ADD KEY `fk_appointment_items_appointment` (`appointment_id`),
  ADD KEY `fk_appointment_items_test` (`test_id`);

--
-- Indexes for table `appointment_tests`
--
ALTER TABLE `appointment_tests`
  ADD PRIMARY KEY (`appointment_id`,`test_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD UNIQUE KEY `uk_bills_bill_number` (`bill_number`),
  ADD UNIQUE KEY `uk_bills_appointment_id` (`appointment_id`),
  ADD KEY `idx_bills_patient_id` (`patient_id`),
  ADD KEY `idx_bills_bill_date` (`bill_date`),
  ADD KEY `idx_bills_status` (`status`),
  ADD KEY `fk_bills_created_by` (`created_by`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`bill_item_id`),
  ADD KEY `idx_bill_items_bill_id` (`bill_id`),
  ADD KEY `idx_bill_items_test_id` (`test_id`);

--
-- Indexes for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `general_settings`
--
ALTER TABLE `general_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_suppliers` (`supplier_id`),
  ADD KEY `idx_deleted_date` (`deleted_date`),
  ADD KEY `idx_deleted_by` (`deleted_by`),
  ADD KEY `idx_i_deleted_date` (`deleted_date`),
  ADD KEY `idx_i_deleted_by` (`deleted_by`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`lab_id`);

--
-- Indexes for table `lab_configuration`
--
ALTER TABLE `lab_configuration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `online_booking_slots`
--
ALTER TABLE `online_booking_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slot` (`day_group`,`start_time`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `outsourced_lab_id` (`outsourced_lab_id`);

--
-- Indexes for table `outsourced_labs`
--
ALTER TABLE `outsourced_labs`
  ADD PRIMARY KEY (`outsourced_lab_id`);

--
-- Indexes for table `partner_labs`
--
ALTER TABLE `partner_labs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `partner_lab_tests`
--
ALTER TABLE `partner_lab_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partner_lab_id` (`partner_lab_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uhid` (`uhid`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payments_bill_id` (`bill_id`),
  ADD KEY `idx_payments_payment_date` (`payment_date`),
  ADD KEY `idx_payments_method` (`payment_method`),
  ADD KEY `fk_payments_received_by` (`received_by`);

--
-- Indexes for table `prescription_requests`
--
ALTER TABLE `prescription_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_pr_patient` (`patient_id`);

--
-- Indexes for table `prescription_request_events`
--
ALTER TABLE `prescription_request_events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_pre_request_id` (`request_id`),
  ADD KEY `idx_pre_created_by` (`created_by_user_id`);

--
-- Indexes for table `prescription_request_tests`
--
ALTER TABLE `prescription_request_tests`
  ADD PRIMARY KEY (`request_test_id`),
  ADD UNIQUE KEY `uk_request_test` (`request_id`,`test_id`),
  ADD KEY `idx_request_tests_request` (`request_id`),
  ADD KEY `idx_request_tests_test` (`test_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_reports_appointment_status` (`appointment_id`,`status`),
  ADD KEY `idx_reports_test` (`test_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_sr_patient` (`patient_id`),
  ADD KEY `idx_sr_status` (`status`),
  ADD KEY `idx_sr_type_visit` (`request_type`,`visit_type`),
  ADD KEY `idx_sr_preferred_date` (`preferred_date`),
  ADD KEY `idx_sr_created_at` (`created_at`),
  ADD KEY `fk_sr_decision_user` (`decision_by_user_id`),
  ADD KEY `fk_sr_appointment` (`linked_appointment_id`);

--
-- Indexes for table `stock_history`
--
ALTER TABLE `stock_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_inventory_id` (`inventory_id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_deleted_date` (`deleted_date`),
  ADD KEY `idx_sh_deleted_date` (`deleted_date`);

--
-- Indexes for table `stock_purchases`
--
ALTER TABLE `stock_purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `idx_purchase_date` (`purchase_date`),
  ADD KEY `idx_inventory_id` (`inventory_id`),
  ADD KEY `idx_sp_deleted_date` (`deleted_date`),
  ADD KEY `fk_sp_supplier` (`supplier_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`test_id`),
  ADD UNIQUE KEY `test_code` (`test_code`),
  ADD KEY `idx_tests_department` (`department`),
  ADD KEY `idx_tests_name` (`test_name`),
  ADD KEY `idx_tests_active` (`is_active`);

--
-- Indexes for table `test_comments`
--
ALTER TABLE `test_comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indexes for table `test_partner_charges`
--
ALTER TABLE `test_partner_charges`
  ADD PRIMARY KEY (`test_partner_charge_id`),
  ADD UNIQUE KEY `uk_test_lab_charge` (`test_id`,`partner_lab_id`),
  ADD KEY `idx_partner_charge_test` (`test_id`),
  ADD KEY `idx_partner_charge_lab` (`partner_lab_id`);

--
-- Indexes for table `test_reference_ranges`
--
ALTER TABLE `test_reference_ranges`
  ADD PRIMARY KEY (`range_id`),
  ADD UNIQUE KEY `uk_unit_range_index` (`unit_id`,`range_index`),
  ADD KEY `idx_ranges_unit` (`unit_id`);

--
-- Indexes for table `test_results`
--
ALTER TABLE `test_results`
  ADD PRIMARY KEY (`result_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `test_id` (`test_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `test_units`
--
ALTER TABLE `test_units`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `uk_test_unit_index` (`test_id`,`unit_index`),
  ADD KEY `idx_test_units_test` (`test_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_2fa`
--
ALTER TABLE `user_2fa`
  ADD PRIMARY KEY (`twofa_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`pref_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_profile_details`
--
ALTER TABLE `user_profile_details`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`user_session_id`),
  ADD UNIQUE KEY `uq_user_sessions_token` (`session_token`),
  ADD KEY `idx_user_sessions_user_active` (`user_id`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `appointment_items`
--
ALTER TABLE `appointment_items`
  MODIFY `appointment_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `bill_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `lab_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_configuration`
--
ALTER TABLE `lab_configuration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `online_booking_slots`
--
ALTER TABLE `online_booking_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `outsourced_labs`
--
ALTER TABLE `outsourced_labs`
  MODIFY `outsourced_lab_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partner_labs`
--
ALTER TABLE `partner_labs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `partner_lab_tests`
--
ALTER TABLE `partner_lab_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `prescription_requests`
--
ALTER TABLE `prescription_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `prescription_request_events`
--
ALTER TABLE `prescription_request_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prescription_request_tests`
--
ALTER TABLE `prescription_request_tests`
  MODIFY `request_test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `request_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_history`
--
ALTER TABLE `stock_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_purchases`
--
ALTER TABLE `stock_purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `test_comments`
--
ALTER TABLE `test_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `test_partner_charges`
--
ALTER TABLE `test_partner_charges`
  MODIFY `test_partner_charge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test_reference_ranges`
--
ALTER TABLE `test_reference_ranges`
  MODIFY `range_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `test_results`
--
ALTER TABLE `test_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `test_units`
--
ALTER TABLE `test_units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_2fa`
--
ALTER TABLE `user_2fa`
  MODIFY `twofa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `pref_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `user_profile_details`
--
ALTER TABLE `user_profile_details`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `user_session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `appointment_items`
--
ALTER TABLE `appointment_items`
  ADD CONSTRAINT `fk_appointment_items_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointment_items_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`);

--
-- Constraints for table `appointment_tests`
--
ALTER TABLE `appointment_tests`
  ADD CONSTRAINT `fk_appt_tests_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_tests_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`);

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `fk_bills_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`appointment_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bills_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bills_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON UPDATE CASCADE;

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `fk_bill_items_bill` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bill_items_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_suppliers` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`lab_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`),
  ADD CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`outsourced_lab_id`) REFERENCES `outsourced_labs` (`outsourced_lab_id`);

--
-- Constraints for table `partner_lab_tests`
--
ALTER TABLE `partner_lab_tests`
  ADD CONSTRAINT `partner_lab_tests_ibfk_1` FOREIGN KEY (`partner_lab_id`) REFERENCES `partner_labs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partner_lab_tests_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_bill` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `prescription_requests`
--
ALTER TABLE `prescription_requests`
  ADD CONSTRAINT `fk_pr_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_request_events`
--
ALTER TABLE `prescription_request_events`
  ADD CONSTRAINT `fk_pre_created_by` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pre_request` FOREIGN KEY (`request_id`) REFERENCES `prescription_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_request_tests`
--
ALTER TABLE `prescription_request_tests`
  ADD CONSTRAINT `fk_request_tests_request` FOREIGN KEY (`request_id`) REFERENCES `prescription_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_request_tests_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`);

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `fk_sr_appointment` FOREIGN KEY (`linked_appointment_id`) REFERENCES `appointment` (`appointment_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sr_decision_user` FOREIGN KEY (`decision_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sr_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_purchases`
--
ALTER TABLE `stock_purchases`
  ADD CONSTRAINT `fk_sp_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sp_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE;

--
-- Constraints for table `test_partner_charges`
--
ALTER TABLE `test_partner_charges`
  ADD CONSTRAINT `fk_partner_charge_lab` FOREIGN KEY (`partner_lab_id`) REFERENCES `partner_labs` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_partner_charge_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `test_reference_ranges`
--
ALTER TABLE `test_reference_ranges`
  ADD CONSTRAINT `fk_ranges_unit` FOREIGN KEY (`unit_id`) REFERENCES `test_units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `test_results`
--
ALTER TABLE `test_results`
  ADD CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`appointment_id`),
  ADD CONSTRAINT `test_results_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`),
  ADD CONSTRAINT `test_results_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `test_units` (`unit_id`);

--
-- Constraints for table `test_units`
--
ALTER TABLE `test_units`
  ADD CONSTRAINT `fk_test_units_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_2fa`
--
ALTER TABLE `user_2fa`
  ADD CONSTRAINT `fk_user_2fa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `fk_user_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_profile_details`
--
ALTER TABLE `user_profile_details`
  ADD CONSTRAINT `fk_user_profile_details_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
