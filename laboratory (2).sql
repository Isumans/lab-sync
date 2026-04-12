-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 12:38 PM
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
  `appointment_time` time NOT NULL,
  `appointment_date` date NOT NULL,
  `method` enum('online','physical','call') NOT NULL,
  `notes` text DEFAULT NULL,
  `referred_by` varchar(100) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `sample_type` varchar(50) DEFAULT NULL,
  `sample_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `patient_id`, `appointment_time`, `appointment_date`, `method`, `notes`, `referred_by`, `deleted_at`, `deleted_by`, `sample_type`, `sample_datetime`) VALUES
(8, 1, '12:08:00', '2026-04-05', 'physical', '', NULL, '2026-04-04 00:51:50', 6, NULL, NULL),
(9, 1, '16:36:00', '2026-04-01', 'physical', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 1, '16:00:00', '2026-04-03', 'physical', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 1, '20:12:00', '2026-04-04', 'physical', NULL, NULL, '2026-04-04 20:15:54', 6, NULL, NULL),
(12, 1, '08:00:00', '2026-04-05', 'physical', '', NULL, NULL, NULL, NULL, NULL),
(13, 1, '09:42:00', '2026-04-07', 'physical', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 1, '11:00:00', '2026-04-08', 'physical', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 7, '15:24:00', '2026-04-12', 'physical', NULL, NULL, NULL, NULL, NULL, NULL);

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
(13, 17, 'PENDING', '2026-04-07 04:12:51', NULL, NULL, NULL, NULL),
(14, 3, 'IN_PROGRESS', '2026-04-07 04:31:08', 6, NULL, NULL, NULL),
(15, 29, 'IN_PROGRESS', '2026-04-12 10:01:43', 6, NULL, NULL, NULL);

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
  `currency` varchar(20) DEFAULT 'USD',
  `date_format` varchar(20) DEFAULT 'dd/mm/yyyy',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `sms_alerts`, `email_reports`, `password_policy`, `session_timeout`, `language`, `timezone`, `currency`, `date_format`, `updated_at`) VALUES
(1, 0, 1, '90', 45, 'en_US', 'Asia/Kolkata', 'USD', 'dd/mm/yyyy', '2026-03-03 15:33:22');

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
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `supplier_id`, `quantity`, `reorder_level`, `last_updated`) VALUES
(1, 'Glucose Test Kit', 1, 50, 10, '2025-10-20 22:52:11'),
(2, 'Blood Collection Tubes', 2, 120, 30, '2025-10-20 22:52:11'),
(3, 'Urine Sample Bottles', 1, 80, 20, '2025-10-20 22:52:11'),
(4, 'Microscope Slides', 3, 200, 40, '2025-10-20 22:52:11'),
(5, 'COVID-19 Rapid Test Kit', 2, 60, 15, '2025-10-20 22:52:11'),
(6, 'Latex Gloves', 3, 30, 50, '2025-10-21 08:45:45'),
(9, 'gloves', 2, 1000, 50, '2025-10-22 18:09:48'),
(10, 'uni', 2, 100, 10, '2026-03-29 15:59:31');

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
(1, 'Green Valley Medical', 'ISO-15189-2023', 'Suite 402, Medical Arts Building, Downtown Metro', '+94 77 123 4567', 'contact@metrodiagnostics.com', '/lab_sync/public/uploads/lab_logo_1772551364.jpg', '08:00:00', '17:00:00', 1, '09:00:00', '14:00:00', 1, '12:00:00', '12:00:00', 0, 1, 0, '2026-03-04 05:16:32');

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
  `date_of_death` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `uhid`, `patient_name`, `date_of_birth`, `gender`, `email`, `contact_number`, `created_at`, `updated_date`, `address`, `date_of_death`) VALUES
(1, NULL, 'Isuru Perera', '1998-04-15', 'Male', 'isuru.perera@example.com', '0712045678', '2025-10-17 14:48:40', '2025-10-18 07:01:16', NULL, NULL),
(2, NULL, 'Nadeesha Fernando', '2000-09-21', 'Female', 'nadeesha.fernando@example.com', '0779876543', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL),
(3, NULL, 'Kasun Jayasinghe', '1995-12-02', 'Male', 'kasun.jayasinghe@example.com', '0751122334', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL),
(4, NULL, 'Rashmi Silva', '1999-06-10', 'Female', 'rashmi.silva@example.com', '0769988776', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL),
(5, NULL, 'Tharindu De Alwis', '1997-02-27', 'Male', 'tharindu.alwis@example.com', '0784455667', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL, NULL),
(7, NULL, 'saman kumara', '2025-10-16', 'Male', 'ucsc@gmail.com', '1234567890', '2025-10-20 08:33:26', '2025-10-20 08:33:26', NULL, NULL),
(8, NULL, 'manmitha', NULL, NULL, 'abc@gmail.com', '123456789', '2025-10-22 11:41:59', '2025-10-22 11:41:59', NULL, NULL),
(9, NULL, 'saman', NULL, NULL, 'saman@gmail.com', '12345678', '2025-10-22 11:43:33', '2025-10-22 11:43:33', NULL, NULL),
(11, NULL, 'karuni', NULL, 'Male', 'karu@gmail.com', '123456789', '2025-10-22 12:40:39', '2025-10-22 13:33:35', '', NULL),
(12, NULL, 'surin', NULL, 'Male', 'surini@gmail.com', '12345678', '2025-10-22 17:52:55', '2025-10-22 18:05:46', '', NULL),
(13, NULL, 'patient', NULL, NULL, 'patient@gmail.com', '123456789', '2025-10-22 18:37:41', '2025-10-22 18:37:41', NULL, NULL),
(20, NULL, 'yasindu', NULL, NULL, 'yas@gmail.com', '1234567890', '2025-10-23 04:27:41', '2025-10-23 04:27:41', NULL, NULL),
(22, NULL, 'yasindu6', NULL, NULL, 'yasindu6@gmail.com', '1234567890', '2025-10-23 05:26:29', '2025-10-23 05:26:29', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `reference_number` varchar(50) NOT NULL,
  `uhid` varchar(50) DEFAULT NULL,
  `referred_by` varchar(100) DEFAULT NULL,
  `sample_type` varchar(50) DEFAULT NULL,
  `sample_datetime` datetime DEFAULT NULL,
  `report_datetime` datetime DEFAULT NULL,
  `page_count` int(3) DEFAULT 1,
  `general_comments` text DEFAULT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `pathologist_id` int(11) DEFAULT NULL,
  `status` enum('DRAFT','COMPLETED','AUTHORIZED','PRINTED') DEFAULT 'DRAFT',
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
(3, 'BioTech Labs', '0759876543', 'Galle', 'biotechlabs@example.com', '2025-10-20 22:29:10', '2025-10-20 22:29:10');

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
(29, 'Renal Function', 'Biochemistry', '', 120.00, 0, '2026-04-12 09:53:14', 'T021', 'loo1', 'CREATININE AND eGFR..', 'mg/dl', 120.00, 0.00, 0, 2, 1, 1, '', '2026-04-12 09:53:14', NULL, NULL);

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
(2, 'normal range', NULL, 3, 0, 'ALL', 40.00, 60.00, 0.6000, 0.9000, NULL, 0, '2026-04-12 09:53:14');

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
(3, 29, 0, 'SERUM CREATININE (ENZYMATIC)', 'mg/dl', 1, '2026-04-12 09:53:14', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
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

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `contact_number`, `role`, `status`, `created_at`, `updated_at`) VALUES
(3, 'tech01', 'tech123hashed', 'tech01@example.com', '0773456789', 'technician', 'active', '2025-10-20 13:33:50', '2025-10-20 13:33:50'),
(4, 'patient01', 'patient123hashed', 'patient01@example.com', '0774567890', 'patient', 'active', '2025-10-20 13:33:50', '2025-10-20 13:33:50'),
(5, 'patient02', 'patient456hashed', 'patient02@example.com', '0775678901', 'patient', 'active', '2025-10-20 13:33:50', '2025-10-20 13:33:50'),
(6, 'admin1', '$2y$10$tDkXoH4NzoaPt9hjIpZ1aO1RNMktISO7ayDKhQIG.IpE5iJeB6ImO', 'isumanmitha@gmail.com', '0712340678', 'admin', 'active', '2025-10-20 20:45:17', '2025-10-21 10:09:49'),
(8, 'admin2', '$2y$10$gGR.AibzYrfgG7FHxb8BbuoEok8Iba4U6IviXqvqj86kUuSuYK0Xy', 'udemy4ucsc@gmail.com', '0712340678', 'admin', 'active', '2025-10-21 12:36:54', '2025-10-21 12:36:54'),
(10, 'patient123', '$2y$10$p8yspwoq7kJ07Y1gfxIlgOk9byNPi9iUebwoPK4/QS5Ma3E0xLQhK', 'pasidu@gmail.com', '0712340678', 'patient', 'active', '2025-10-21 21:16:09', '2025-10-21 21:16:09'),
(11, 'tech1', '$2y$10$0UfWVdPIZTJAs/AvY.faP.fhgxUGMOyMpfvZrnxFHvlOJgrfvD3UW', 'ucsc@gmail.com', '1234567890', 'technician', 'active', '2025-10-22 02:58:29', '2025-10-22 02:58:29'),
(12, 'recep1', '$2y$10$wosfvpPY1DfIeu6egbt8/O.80THZEZpxBgHJfAjWEGuy1CUr6ts7i', 'niyumineth@gmail.com', '1234567890', 'receptionist', 'active', '2025-10-22 02:59:19', '2025-10-22 02:59:19'),
(13, 'manmitha', '$2y$10$gFIpaPfyFOpY5HkEhf/.Ce0THUQ92pTi3/n9tUpA0Fs3dB5t29FXy', 'abc@gmail.com', '123456789', 'patient', 'active', '2025-10-22 11:41:59', '2025-10-22 11:41:59'),
(14, 'saman', '$2y$10$bEyprN5vhjSJt6L0tYCe7e12j.5OXpamofZ2M2CSp49NYhjOx8qmi', 'saman@gmail.com', '12345678', 'patient', 'active', '2025-10-22 11:43:33', '2025-10-22 11:43:33'),
(15, 'kumara', '$2y$10$MOMt46Zb.R4RpLk1RkLLfOa9Tjs1edwoWWBjhgGTyea7oChqQpa16', 'kumara@gmail.com', '0712340678', 'patient', 'active', '2025-10-22 11:57:43', '2025-10-22 11:57:43'),
(16, 'sam', '$2y$10$4/9ftqTv7GohhMnEsYQv8.4kw16grzs/rx86zuUz6hfzBkbLz30SO', 'sam@gmail.com', '0712340678', 'patient', 'active', '2025-10-22 12:02:25', '2025-10-22 12:02:25'),
(17, 'samy', '$2y$10$J12Hpr5SOtHcswAwyx3/XOtjK/DaT1mlhQFldSgaetaJsL7ozoXIi', 'samy@gmail.com', '0712340678', 'patient', 'active', '2025-10-22 12:06:18', '2025-10-22 12:06:18'),
(18, 'sami', '$2y$10$rfZQ/UkztaXbRirIcmSgU.Hd6a9snGOMxM1Z4SuMfXsXqkQBdIjBW', 'sami@gmail.com', '123456789', 'patient', 'active', '2025-10-22 12:15:38', '2025-10-22 12:15:38'),
(19, 'suri', '$2y$10$j.aoKQbRi2tvQGzuRqcLq.sY32WI/00polu19XnZ/SUayzg9Pr4v6', 'suri@gmail.com', '123456789', 'patient', 'active', '2025-10-22 12:37:26', '2025-10-22 12:37:26'),
(20, 'karuni', '$2y$10$btyVt.lsFbQGSQaWeyHDYOo2OQpWE0pJWr4LGonE0dCHe2cLMB7My', 'karu@gmail.com', '123456789', 'patient', 'active', '2025-10-22 12:40:39', '2025-10-22 13:33:35'),
(21, 'surini', '$2y$10$v7PSYqpvnfpr1FtqjJAaheBvtCZpk0yFkzia5sMNIeFZ1rAzwyUB2', 'surini@gmail.com', '12345678', 'patient', 'active', '2025-10-22 17:52:55', '2025-10-22 17:58:10'),
(22, 'admin4', '$2y$10$0OaIL0oNy/g8mR0KHsgUT.bq9VOQVIqnqPCR/ENJtS6sqYRpguuOi', 'acbs@gmail.com', '123456789', 'admin', 'active', '2025-10-22 18:28:24', '2025-10-23 04:12:16'),
(23, 'patient', '$2y$10$1T.GsX2Yr83RiKiiYKntDeCGm17roZIHgTey.LkbDYtPCWJa7qHfe', 'patient@gmail.com', '123456789', 'patient', 'active', '2025-10-22 18:37:41', '2025-10-22 18:37:41'),
(27, 'admin3', '$2y$10$pLXz4Ai0My.Rj6OyiAbYU.FHXS9BAO44LEk3n4kn50AN3m/JMigBm', 'admin3@gmail.com', '`123456789', 'admin', 'active', '2025-10-23 04:14:18', '2025-10-23 04:14:18'),
(28, 'yasindu', '$2y$10$ek5ST2dAnlcoFAG8vhViIOu4mp9gqIhD4ZTK1TwpMDezcH7z0Ql3G', 'yas@gmail.com', '1234567890', 'patient', 'active', '2025-10-23 04:27:41', '2025-10-23 04:27:41'),
(30, 'yasindu6', '$2y$10$b2KHWTOhVVS9PLtI.glBsuxwKuT.sd2Dug6Iv/.r94ZDhsD0w3dyK', 'yasindu6@gmail.com', '1234567890', 'patient', 'active', '2025-10-23 05:26:29', '2025-10-23 05:26:29'),
(31, 'MLT1', '$2y$10$87czWpgyHjMgoQH/kjoH8ulHfGpevV0DUVOIQeqPk/cDk9bvr.Zsq', 'mlt@gmail.com', '0712345678', 'admin', 'active', '2026-02-16 08:28:33', '2026-02-16 08:28:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `deleted_by` (`deleted_by`);

--
-- Indexes for table `appointment_tests`
--
ALTER TABLE `appointment_tests`
  ADD PRIMARY KEY (`appointment_id`,`test_id`),
  ADD KEY `test_id` (`test_id`);

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
  ADD KEY `fk_suppliers` (`supplier_id`);

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
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `test_comments`
--
ALTER TABLE `test_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_partner_charges`
--
ALTER TABLE `test_partner_charges`
  MODIFY `test_partner_charge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `test_reference_ranges`
--
ALTER TABLE `test_reference_ranges`
  MODIFY `range_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `test_results`
--
ALTER TABLE `test_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_units`
--
ALTER TABLE `test_units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
-- Constraints for table `appointment_tests`
--
ALTER TABLE `appointment_tests`
  ADD CONSTRAINT `fk_appt_tests_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointment` (`appointment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appt_tests_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
