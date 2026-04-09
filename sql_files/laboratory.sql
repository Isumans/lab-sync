-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 07:48 AM
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
-- Database: `laboratory`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_date` date NOT NULL,
  `method` enum('online','physical','call') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `patient_id`, `test_id`, `appointment_time`, `appointment_date`, `method`) VALUES
(3, 12, 2, '11:30:00', '2025-10-24', 'online'),
(5, 13, 1, '09:30:00', '2025-10-25', 'online'),
(8, 12, 1, '08:30:00', '2025-10-26', 'physical'),
(9, 13, 2, '10:00:00', '2025-10-26', 'online'),
(10, 12, 3, '11:15:00', '2025-10-27', 'physical'),
(11, 13, 1, '13:00:00', '2025-10-27', 'online'),
(12, 12, 2, '14:45:00', '2025-10-28', 'physical'),
(13, 13, 3, '09:00:00', '2025-10-29', 'physical'),
(14, 12, 1, '15:30:00', '2025-10-29', 'online'),
(15, 13, 2, '16:00:00', '2025-10-30', 'physical'),
(16, 12, 3, '10:30:00', '2025-10-31', 'online'),
(17, 13, 1, '12:00:00', '2025-10-31', 'physical');

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
  `unit_of_measure` varchar(50) DEFAULT 'Units',
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'In Stock',
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `supplier_id`, `quantity`, `reorder_level`, `last_updated`, `category_id`, `unit_of_measure`, `unit_cost`, `status`, `deleted_date`, `deleted_time`, `deleted_by`) VALUES
(1, 'Glucose Test Kit', 1, 50, 10, '2026-04-07 05:15:54', NULL, 'Units', NULL, 'In Stock', '2026-04-07', '07:15:54', 6),
(2, 'Blood Collection Tubes', 2, 120, 30, '2025-10-20 22:52:11', NULL, 'Units', NULL, 'In Stock', NULL, NULL, NULL),
(3, 'Urine Sample Bottles', 1, 80, 20, '2025-10-20 22:52:11', NULL, 'Units', NULL, 'In Stock', NULL, NULL, NULL),
(4, 'Microscope Slides', 3, 200, 40, '2025-10-20 22:52:11', NULL, 'Units', NULL, 'In Stock', NULL, NULL, NULL),
(5, 'COVID-19 Rapid Test Kit', 2, 60, 15, '2025-10-20 22:52:11', NULL, 'Units', NULL, 'In Stock', NULL, NULL, NULL),
(6, 'Latex Gloves', 3, 30, 50, '2025-10-21 08:45:45', NULL, 'Units', NULL, 'In Stock', NULL, NULL, NULL),
(9, 'gloves', 2, 1000, 50, '2025-10-22 18:09:48', NULL, 'Units', NULL, 'In Stock', NULL, NULL, NULL),
(30, 'Blood Collection Tube', 1, 500, 10, '2026-04-04 12:19:48', 6, 'Box', 2.50, 'In Stock', NULL, NULL, NULL),
(31, 'Glucose Reagent', 1, 100, 20, '2026-04-04 12:19:48', 7, 'Bottle', 15.00, 'In Stock', NULL, NULL, NULL),
(32, 'Latex Gloves (Box)', 2, 200, 50, '2026-04-04 12:19:48', 8, 'Box', 5.00, 'In Stock', NULL, NULL, NULL),
(33, 'Pipette Tips (1000)', 2, 50, 15, '2026-04-04 12:19:48', 4, 'Pack', 12.00, 'In Stock', NULL, NULL, NULL),
(34, 'Sodium Chloride Solution', 1, 20, 10, '2026-04-04 12:19:48', 3, 'Bottle', 8.50, 'In Stock', NULL, NULL, NULL),
(35, 'Centrifuge Tubes', 2, 300, 50, '2026-04-04 12:19:48', 1, 'Pack', 3.75, 'In Stock', NULL, NULL, NULL),
(36, 'Amino Acid Analyzer', 3, 2, 1, '2026-04-04 12:19:48', 5, 'Unit', 5000.00, 'In Stock', NULL, NULL, NULL),
(37, 'Disinfectant Solution', 1, 15, 10, '2026-04-04 12:19:48', 9, 'Liter', 25.00, 'In Stock', NULL, NULL, NULL),
(38, 'Petri Dishes (100)', 3, 80, 20, '2026-04-04 12:19:48', 1, 'Pack', 6.50, 'Low Stock', NULL, NULL, NULL),
(39, 'Microcentrifuge', 2, 1, 1, '2026-04-04 12:19:48', 5, 'Unit', 3500.00, 'In Stock', NULL, NULL, NULL),
(40, 'Blood Collection Tube', 1, 500, 10, '2026-04-04 12:21:09', 6, 'Box', 2.50, 'In Stock', NULL, NULL, NULL),
(41, 'Glucose Reagent', 1, 100, 20, '2026-04-04 12:21:09', 7, 'Bottle', 15.00, 'In Stock', NULL, NULL, NULL),
(42, 'Latex Gloves (Box)', 2, 200, 50, '2026-04-04 12:21:09', 8, 'Box', 5.00, 'In Stock', NULL, NULL, NULL),
(43, 'Pipette Tips (1000)', 2, 50, 15, '2026-04-04 12:21:09', 4, 'Pack', 12.00, 'In Stock', NULL, NULL, NULL),
(44, 'Sodium Chloride Solution', 1, 20, 10, '2026-04-04 12:21:09', 3, 'Bottle', 8.50, 'In Stock', NULL, NULL, NULL),
(45, 'Centrifuge Tubes', 2, 300, 50, '2026-04-04 12:21:09', 1, 'Pack', 3.75, 'In Stock', NULL, NULL, NULL),
(46, 'Amino Acid Analyzer', 3, 2, 1, '2026-04-04 12:21:09', 5, 'Unit', 5000.00, 'In Stock', NULL, NULL, NULL),
(47, 'Disinfectant Solution', 1, 15, 10, '2026-04-04 12:21:09', 9, 'Liter', 25.00, 'In Stock', NULL, NULL, NULL),
(48, 'Petri Dishes (100)', 3, 80, 20, '2026-04-04 12:21:09', 1, 'Pack', 6.50, 'In Stock', NULL, NULL, NULL),
(49, 'Microcentrifuge', 2, 1, 1, '2026-04-04 13:42:26', 5, 'Units', 0.00, 'In Stock', NULL, NULL, NULL);

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

--
-- Dumping data for table `inventory_categories`
--

INSERT INTO `inventory_categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`, `deleted_date`, `deleted_time`, `deleted_by`) VALUES
(1, 'Medical Supplies', 'General medical supplies and consumables', '2026-04-04 11:33:55', '2026-04-07 04:40:48', '2026-04-07', '06:40:48', 6),
(2, 'Laboratory Equipment', 'Lab equipment and machinery', '2026-04-04 11:33:55', '2026-04-04 11:33:55', NULL, NULL, NULL),
(3, 'Chemicals', 'Chemical reagents and solutions', '2026-04-04 11:33:55', '2026-04-04 11:33:55', NULL, NULL, NULL),
(4, 'Consumables', 'Disposable items: gloves, masks, pipette tips', '2026-04-04 11:33:55', '2026-04-04 11:49:05', NULL, NULL, NULL),
(5, 'Equipment', 'Laboratory equipment: centrifuge, analyzer, microscope', '2026-04-04 11:33:55', '2026-04-04 11:49:05', NULL, NULL, NULL),
(6, 'Blood Tests', 'Blood collection tubes, lancets, and blood test supplies', '2026-04-04 11:49:05', '2026-04-04 11:49:05', NULL, NULL, NULL),
(7, 'Reagents', 'Chemical reagents and solutions for lab testing', '2026-04-04 11:49:05', '2026-04-04 11:49:05', NULL, NULL, NULL),
(8, 'Safety Equipment', 'PPE and safety equipment for laboratory staff', '2026-04-04 11:49:05', '2026-04-04 11:49:05', NULL, NULL, NULL),
(9, 'Sterilization Supplies', 'Disinfectants, sterilization solutions, and supplies', '2026-04-04 11:49:05', '2026-04-04 11:49:05', NULL, NULL, NULL);

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
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `patient_name`, `date_of_birth`, `gender`, `email`, `contact_number`, `created_at`, `updated_date`, `address`) VALUES
(1, 'Isuru Perera', '1998-04-15', 'Male', 'isuru.perera@example.com', '0712045678', '2025-10-17 14:48:40', '2025-10-18 07:01:16', NULL),
(2, 'Nadeesha Fernando', '2000-09-21', 'Female', 'nadeesha.fernando@example.com', '0779876543', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(3, 'Kasun Jayasinghe', '1995-12-02', 'Male', 'kasun.jayasinghe@example.com', '0751122334', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(4, 'Rashmi Silva', '1999-06-10', 'Female', 'rashmi.silva@example.com', '0769988776', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(5, 'Tharindu De Alwis', '1997-02-27', 'Male', 'tharindu.alwis@example.com', '0784455667', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(7, 'saman kumara', '2025-10-16', 'Male', 'ucsc@gmail.com', '1234567890', '2025-10-20 08:33:26', '2025-10-20 08:33:26', NULL),
(11, 'karuni', NULL, 'Male', 'karu@gmail.com', '123456789', '2025-10-22 12:40:39', '2025-10-22 13:33:35', ''),
(12, 'surin', NULL, 'Male', 'surini@gmail.com', '12345678', '2025-10-22 17:52:55', '2025-10-22 18:05:46', ''),
(13, 'patient', NULL, NULL, 'patient@gmail.com', '123456789', '2025-10-22 18:37:41', '2025-10-22 18:37:41', NULL),
(27, 'malani rajapakshe', '2011-06-14', 'Female', 'malini11@gmail.com', '0724586571', '2026-02-09 08:50:01', '2026-02-09 08:50:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_history`
--

CREATE TABLE `stock_history` (
  `history_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `purchase_id` int(11) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_history`
--

INSERT INTO `stock_history` (`history_id`, `inventory_id`, `quantity`, `action`, `notes`, `created_at`, `purchase_id`, `unit_cost`, `supplier_id`, `expiry_date`, `deleted_date`, `deleted_time`, `deleted_by`) VALUES
(1, 47, 15, '0', 'Purchased from supplier', '2026-03-19 18:30:00', 18, 25.00, 1, '2027-03-20', NULL, NULL, NULL),
(2, 45, 300, '0', 'Purchased from supplier', '2026-03-17 18:30:00', 16, 3.00, 2, '2028-03-18', NULL, NULL, NULL),
(3, 40, 500, '0', 'Purchased from supplier', '2026-03-14 18:30:00', 11, 2.00, 1, '2028-03-15', NULL, NULL, NULL),
(4, 48, 80, '0', 'Purchased from supplier', '2026-03-13 18:30:00', 19, 6.00, 3, '2029-03-14', NULL, NULL, NULL),
(5, 42, 200, '0', 'Purchased from supplier', '2026-03-11 18:30:00', 13, 5.00, 2, '2029-03-12', NULL, NULL, NULL),
(6, 41, 100, '0', 'Purchased from supplier', '2026-03-09 18:30:00', 12, 15.00, 1, '2027-03-10', NULL, NULL, NULL),
(7, 43, 50, '0', 'Purchased from supplier', '2026-03-07 18:30:00', 14, 12.00, 2, '2030-03-08', NULL, NULL, NULL),
(8, 44, 20, '0', 'Purchased from supplier', '2026-03-04 18:30:00', 15, 8.00, 1, '2027-03-05', NULL, NULL, NULL),
(9, 46, 2, '0', 'Purchased from supplier', '2026-02-27 18:30:00', 17, 5000.00, 3, '2030-02-28', NULL, NULL, NULL),
(10, 49, 1, '0', 'Purchased from supplier', '2026-01-14 18:30:00', 20, 3500.00, 2, '2030-01-15', NULL, NULL, NULL);

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
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `deleted_date` date DEFAULT NULL,
  `deleted_time` time DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_purchases`
--

INSERT INTO `stock_purchases` (`purchase_id`, `inventory_id`, `supplier_id`, `quantity_purchased`, `unit_cost`, `total_cost`, `purchase_date`, `notes`, `created_at`, `expiry_date`, `deleted_date`, `deleted_time`, `deleted_by`) VALUES
(11, 40, 1, 500, 2.00, 1250.00, '2026-03-15', 'Regular stock replenishment', '2026-04-04 12:21:10', '2028-03-15', NULL, NULL, NULL),
(12, 41, 1, 100, 15.00, 1500.00, '2026-03-10', 'Bulk order for monthly supply', '2026-04-04 12:21:10', '2027-03-10', NULL, NULL, NULL),
(13, 42, 2, 200, 5.00, 1000.00, '2026-03-12', 'Standard safety equipment order', '2026-04-04 12:21:10', '2029-03-12', NULL, NULL, NULL),
(14, 43, 2, 50, 12.00, 600.00, '2026-03-08', 'Lab consumables', '2026-04-04 12:21:10', '2030-03-08', NULL, NULL, NULL),
(15, 44, 1, 20, 8.00, 170.00, '2026-03-05', 'Chemical solutions for testing', '2026-04-04 12:21:10', '2027-03-05', NULL, NULL, NULL),
(16, 45, 2, 300, 3.00, 1125.00, '2026-03-18', 'Additional tubes for centrifuge', '2026-04-04 12:21:10', '2028-03-18', NULL, NULL, NULL),
(17, 46, 3, 2, 5000.00, 10000.00, '2026-02-28', 'New laboratory equipment', '2026-04-04 12:21:10', '2030-02-28', NULL, NULL, NULL),
(18, 47, 1, 15, 25.00, 375.00, '2026-03-20', 'Sterilization supplies', '2026-04-04 12:21:10', '2027-03-20', NULL, NULL, NULL),
(19, 48, 3, 80, 6.00, 520.00, '2026-03-14', 'Culture media supplies', '2026-04-04 12:21:10', '2029-03-14', NULL, NULL, NULL),
(20, 49, 2, 1, 3500.00, 3500.00, '2026-01-15', 'Microcentrifuge equipment', '2026-04-04 12:21:10', '2030-01-15', NULL, NULL, NULL);

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
  `test_name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_outsourced` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`test_id`, `test_name`, `category`, `description`, `price`, `is_outsourced`, `created_at`) VALUES
(1, 'Complete Blood Count (CBC)', 'Other', '', 100.00, 0, '2025-09-02 08:24:24'),
(2, 'Liver Function Test (LFT)', 'Other', 'Evaluates liver enzymes, proteins, and bilirubin levels', 2500.00, 0, '2025-09-02 08:24:24'),
(3, 'Thyroid Function Test (TFT)', 'Other', 'Assesses T3, T4, and TSH levels for thyroid performance', 3000.00, 1, '2025-09-02 08:24:24'),
(4, 'Blood Sugar Test', 'Other', 'Measures fasting and random blood glucose levels', 1200.00, 0, '2025-09-02 08:24:24'),
(5, 'Cholesterol Test', 'Blood Test', 'Checks total cholesterol, HDL, LDL, and triglycerides', 1800.00, 0, '2025-09-02 08:24:24'),
(6, 'COVID-19 PCR Test', 'Other', 'Detects SARS-CoV-2 virus genetic material', 6000.00, 1, '2025-09-02 08:24:24'),
(7, 'Urine Routine Test', 'Other', '', 1000.00, 0, '2025-09-02 08:24:24'),
(8, 'Kidney Function Test (KFT)', 'Other', 'Measures urea, creatinine, and electrolytes', 2200.00, 1, '2025-09-02 08:24:24'),
(10, 'ghjk', 'molecular', NULL, 1234.00, 0, '2025-10-15 13:54:55'),
(17, 'blood', 'blood', '', 200.00, 0, '2025-10-17 08:16:09'),
(20, 'pressure test', 'imaging', 'a test to check the blood pressure', 1200.00, 0, '2025-10-22 18:07:38'),
(21, 'Blood Test', 'blood', 'shghjjkdjljkcl', 1200.00, 0, '2025-10-23 04:30:35');

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
(30, 'yasindu6', '$2y$10$b2KHWTOhVVS9PLtI.glBsuxwKuT.sd2Dug6Iv/.r94ZDhsD0w3dyK', 'yasindu6@gmail.com', '1234567890', 'patient', 'active', '2025-10-23 05:26:29', '2025-10-23 05:26:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `fk_suppliers` (`supplier_id`),
  ADD KEY `fk_category` (`category_id`),
  ADD KEY `idx_deleted_date` (`deleted_date`),
  ADD KEY `idx_deleted_by` (`deleted_by`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `idx_deleted_date` (`deleted_date`),
  ADD KEY `idx_deleted_by` (`deleted_by`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`lab_id`);

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
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `stock_history`
--
ALTER TABLE `stock_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_inventory_id` (`inventory_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_deleted_date` (`deleted_date`);

--
-- Indexes for table `stock_purchases`
--
ALTER TABLE `stock_purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `idx_purchase_date` (`purchase_date`),
  ADD KEY `idx_inventory_id` (`inventory_id`),
  ADD KEY `idx_deleted_date` (`deleted_date`),
  ADD KEY `idx_deleted_by` (`deleted_by`);

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
  ADD PRIMARY KEY (`test_id`);

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
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `lab_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `stock_history`
--
ALTER TABLE `stock_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_purchases`
--
ALTER TABLE `stock_purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`category_id`) ON DELETE SET NULL,
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
-- Constraints for table `stock_history`
--
ALTER TABLE `stock_history`
  ADD CONSTRAINT `stock_history_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_purchases`
--
ALTER TABLE `stock_purchases`
  ADD CONSTRAINT `stock_purchases_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_purchases_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_items`
--

CREATE TABLE `supplier_items` (
  `supplier_item_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD PRIMARY KEY (`supplier_item_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- AUTO_INCREMENT for table `supplier_items`
--
ALTER TABLE `supplier_items`
  MODIFY `supplier_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD CONSTRAINT `supplier_items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
