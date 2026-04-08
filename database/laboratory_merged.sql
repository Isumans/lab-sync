-- phpMyAdmin SQL Dump with Phase 2 Appointment Updates
-- version 5.2.1
-- Merged from existing_db.sql and laboratory.sql
-- Includes all existing data + appointment domain upgrades

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `laboratory`

-- --------------------------------------------------------

-- Table structure for table `appointment`

CREATE TABLE IF NOT EXISTS `appointment` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_date` date NOT NULL,
  `method` enum('online','physical','call') NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'Pending',
  `booking_channel` VARCHAR(40) NULL,
  `home_collection` TINYINT(1) NOT NULL DEFAULT 0,
  `collection_address` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `test_id` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert existing appointment data with defaults for new columns
INSERT INTO `appointment` (`appointment_id`, `patient_id`, `test_id`, `appointment_time`, `appointment_date`, `method`, `status`, `booking_channel`, `home_collection`, `collection_address`) VALUES
(3, 12, 2, '11:30:00', '2025-10-24', 'online', 'Pending', 'online_self', 0, NULL),
(5, 13, 1, '09:30:00', '2025-10-25', 'online', 'Pending', 'online_self', 0, NULL),
(8, 33, 1, '08:00:00', '2026-02-23', 'online', 'Pending', 'online_self', 0, NULL)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `booking_channel` = VALUES(`booking_channel`), `home_collection` = VALUES(`home_collection`), `collection_address` = VALUES(`collection_address`);

-- --------------------------------------------------------

-- Table structure for table `appointment_items` (NEW - for multi-test appointments)

CREATE TABLE IF NOT EXISTS `appointment_items` (
    `appointment_item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `test_id` INT NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `line_total` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_appointment_items_appointment_id` (`appointment_id`),
  KEY `idx_appointment_items_test_id` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `prescription_requests`

CREATE TABLE IF NOT EXISTS `prescription_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `prescription_file_path` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `home_collection` TINYINT(1) NOT NULL DEFAULT 0,
  `collection_address` VARCHAR(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `decision_action` varchar(40) DEFAULT NULL,
  `decision_by_user_id` int(11) DEFAULT NULL,
  `decision_at` datetime DEFAULT NULL,
  `linked_appointment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`request_id`),
  KEY `idx_prescription_patient` (`patient_id`),
  KEY `idx_prescription_status` (`status`),
  KEY `idx_prescription_decision_by` (`decision_by_user_id`),
  KEY `idx_prescription_linked_appointment` (`linked_appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `prescription_request_events`

CREATE TABLE IF NOT EXISTS `prescription_request_events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `idx_pre_request_id` (`request_id`),
  KEY `idx_pre_created_by` (`created_by_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `blog_categories`

CREATE TABLE IF NOT EXISTS `blog_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert blog categories (on duplicate key to preserve existing)
INSERT INTO `blog_categories` (`category_id`, `name`, `slug`) VALUES
(1, 'New Tests', 'new-tests'),
(2, 'Patient Instructions', 'patient-instructions'),
(3, 'Health Education', 'health-education')
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

-- --------------------------------------------------------

-- Table structure for table `blog_posts`

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_status_published` (`status`,`published_at`),
  KEY `idx_category` (`category_id`),
  KEY `idx_author` (`author_id`),
  KEY `idx_created` (`created_at`),
  KEY `fk_blog_author` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert existing blog posts
INSERT INTO `blog_posts` (`post_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category_id`, `author_id`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'hi', 'hi', 'hello', 'How to prevent and treat eye disease\r\nHow to prevent and treat ear disease\r\nHow to prevent and treat dental problems\r\nHow to prevent and treat skin diseases\r\nHow to prevent and treat digestive problems\r\nHow to prevent and treat respiratory problems\r\nHow to prevent and treat neurological problems\r\nHow to prevent and treat genetic disorders\r\nHow to prevent and treat autoimmune disorders\r\nHow to prevent and treat infectious diseases\r\nHow to prevent and treat sexually transmitted diseases\r\nHow to prevent and treat mental health problems\r\nHow to prevent and treat addiction\r\nHow to prevent and treat eating disorders\r\nHow to prevent and treat sleep disorders\r\nHow to prevent and treat hormonal imbalances\r\nHow to prevent and treat menopause symptoms\r\nHow to prevent and treat infertility\r\nHow to prevent and treat pregnancy complications\r\nHow to prevent and treat birth defects\r\nHow to prevent and treat childhood diseases\r\nHow to prevent and treat senior health problems\r\nHow to prevent and treat sexual health problems\r\nHow to prevent and treat disabilities\r\nHow to prevent and treat genetic predispositions\r\nHow to prevent and treat environmental health problems\r\nHow to prevent and treat occupational health problems\r\nHow to prevent and treat public health problems\r\n\r\n\r\n70 Listicle Blog Topics For Your Healthcare Blog\r\nList posts are another popular type of blog post that offer readers a quick and easy way to consume information. These health posts typically feature a numbered list of tips, resources, or insights that are relevant to your audience.\r\n\r\nTop 10 healthy breakfast ideas\r\n7 best exercises for a full-body workout\r\n5 simple ways to reduce stress\r\n10 foods to boost your immune system\r\n8 tips for getting a good night\'s sleep\r\n6 best stretches for relieving back pain\r\n9 natural remedies for headaches\r\n7 healthy snacks to curb your cravings\r\n5 best teas for relaxation\r\n10 ways to incorporate more fiber into your diet\r\n8 tips for maintaining healthy skin\r\n7 best vitamins for overall health\r\n5 ways to boost your mental health\r\n10 healthy dinner ideas\r\n8 tips for maintaining healthy joints\r\n7 easy ways to stay hydrated\r\n5 best exercises for improving cardiovascular health\r\n10 healthy lunch ideas\r\n8 tips for managing diabetes\r\n7 best exercises for toning your body\r\n5 ways to prevent colds and flu\r\n10 healthy snack ideas\r\n8 tips for managing stress through exercise\r\n7 best exercises for improving posture\r\n5 simple ways to improve your gut health\r\n10 healthy breakfast smoothie recipes\r\n8 tips for overcoming a weight loss plateau\r\n7 best exercises for relieving menstrual cramps\r\n5 best teas for digestion\r\n10 healthy salad recipes\r\n8 tips for dealing with anxiety\r\n7 best exercises for reducing bloating\r\n5 ways to reduce sugar intake\r\n10 healthy soup recipes\r\n8 tips for dealing with depression\r\n7 best exercises for improving balance\r\n5 natural remedies for acne\r\n10 healthy grain bowl recipes\r\n8 tips for managing arthritis\r\n7 best exercises for improving flexibility\r\n5 ways to reduce inflammation\r\n10 healthy vegetable recipes\r\n8 tips for preventing heart disease\r\n7 best exercises for building strength\r\n5 ways to prevent cancer\r\n10 healthy seafood recipes\r\n8 tips for preventing stroke\r\n7 best exercises for improving endurance\r\n5 ways to prevent Alzheimer\'s disease\r\n10 healthy meat recipes\r\n8 tips for preventing Parkinson\'s disease\r\n7 best exercises for improving coordination\r\n5 ways to prevent multiple sclerosis\r\n10 healthy vegetarian recipes\r\n8 tips for preventing liver disease\r\n7 best exercises for improving agility\r\n5 ways to prevent kidney disease\r\n10 healthy plant-based recipes\r\n8 tips for preventing lung disease\r\n7 best exercises for improving stamina\r\n5 ways to prevent eye disease\r\n10 healthy vegan recipes\r\n8 tips for preventing ear disease\r\n7 best exercises for improving speed\r\n5 ways to prevent dental problems\r\n10 healthy gluten-free recipes\r\n8 tips for preventing skin diseases\r\n7 best exercises for improving power\r\n5 ways to prevent digestive problems\r\n10 healthy low-carb recipes\r\n\r\n\r\n30 Interviews Headline Ideas For Your Healthcare Blog\r\nInterviews are blog posts that feature an interview with an expert, influencer, or thought leader in your health industry. They\'re great for providing unique insights and perspectives on a particular topic. Use interviews to provide thought leadership content and showcase your knowledge of your industry.\r\n\r\n"Expert insights: How to improve gut health"\r\n"In conversation with a nutritionist: Tips for a healthy diet"\r\n"From the mind of a fitness trainer: Workout tips for a healthy body"\r\n"Stress management with a mindfulness coach"\r\n"The sleep doctor\'s guide to a good night\'s sleep"\r\n"Quit smoking for good with the help of a tobacco cessation specialist"\r\n"Boosting your immune system with an immunologist"\r\n"Eating more plants with a plant-based diet expert"\r\n"Staying hydrated with a hydration specialist"\r\n"Reducing sugar intake with a dietitian"\r\n"Home workout plan with a personal trainer"\r\n"Managing back pain with a chiropractor"\r\n"Improving posture with a physical therapist"\r\n"Choosing the right vitamins with a nutritionist"\r\n"Coping with anxiety with a psychologist"\r\n"Dealing with depression with a mental health specialist"\r\n"Preventing colds with an infectious disease specialist"\r\n"Staying active during the workday with an ergonomics expert"\r\n"Reducing stress through exercise with a personal trainer"\r\n"Overcoming a weight loss plateau with a dietitian"\r\n"Improving cardiovascular health with a cardiologist"\r\n"Managing diabetes with an endocrinologist"\r\n"Incorporating more fiber into your diet with a dietitian"\r\n"Dealing with menstrual cramps with a gynecologist"\r\n"Treating and preventing headaches with a neurologist"\r\n"Preventing and treating acne with a dermatologist"\r\n"Reducing bloating with a gastroenterologist"\r\n"Improving skin health with a dermatologist"\r\n"Preventing and treating arthritis with a rheumatologist"\r\n"Improving joint health with an orthopedic specialist"', NULL, 3, 6, 'draft', '2026-02-18 06:20:20', '2026-02-18 10:50:10', '2026-04-04 17:54:59'),
(2, 'second poster', 'second-post', 'hello this is \r\npatients \r\ninstructionss', 'ieg8yvfe', 'images/blog/blog_69954de462f0c5.61489494.jpeg', 2, 6, 'archived', '2026-02-18 06:28:19', '2026-02-18 10:58:04', '2026-04-04 18:30:26'),
(3, 'hello isum', 'hello-isum', 'sjnfowurh\r\narwgwrg', 'SECTION A – THEORY QUESTIONS\r\nQuestion 1\r\n(a) Explain the CIA Triad in detail. (9 Marks)\r\nAnswer:\r\n\r\nThe CIA Triad is the fundamental model for information security. It consists of Confidentiality, Integrity, and Availability.\r\n\r\n1. Confidentiality\r\n\r\nConfidentiality ensures that sensitive information is accessible only to authorized users.\r\n\r\nIt prevents unauthorized disclosure of data such as personal records, financial information, or confidential business documents.\r\n\r\nMethods used to ensure confidentiality include:\r\n\r\nUser authentication\r\n\r\nAccess control mechanisms\r\n\r\nEncryption\r\n\r\nRole-based permissions\r\n\r\nExample:\r\nIn a hospital database, only doctors are allowed to view patient medical records. Receptionists cannot access diagnosis details.\r\n\r\n2. Integrity\r\n\r\nIntegrity ensures that data remains accurate, complete, and consistent throughout its lifecycle.\r\n\r\nIt prevents unauthorized modification or corruption of data.\r\n\r\nMethods used to maintain integrity:\r\n\r\nPrimary Key (PK) and Foreign Key (FK) constraints\r\n\r\nUNIQUE constraints\r\n\r\nHashing and checksums\r\n\r\nTransaction management (ACID properties)\r\n\r\nExample:\r\nA banking system ensures that account balances correctly reflect deposits and withdrawals without unauthorized changes.\r\n\r\n3. Availability\r\n\r\nAvailability ensures that data and systems are accessible when required by authorized users.\r\n\r\nIt prevents downtime or service disruption.\r\n\r\nMethods used to ensure availability:\r\n\r\nRegular backups\r\n\r\nDisaster recovery planning\r\n\r\nFault tolerance mechanisms\r\n\r\nRedundant systems\r\n\r\nExample:\r\nAn e-commerce website must remain operational 24/7 so customers can place orders.', '', 1, 6, 'published', '2026-02-24 08:44:02', '2026-02-24 13:13:39', '2026-02-24 13:14:02'),
(4, 'NEW BLOG POST ABOUT TEST', 'new-blog-post-about-test', 'A NEW TEST', 'Today, Glaukos Corporation announced clinical updates for several studies in their Corneal Health pipeline programs. Enrollment has begun for a second Phase 3 confirmatory trial for Epioxa (Epi-on), and promising Phase 2a results for GLK-301 (iLution – Dry Eye Disease) has encouraged Glaukos to advance GLK-301 into a Phase 2b clinical trial, which will begin in 2023.', 'images/blog/blog_69d0eff6948c28.96239221.webp', 1, 6, 'published', '2026-04-04 13:03:28', '2026-04-04 16:33:18', '2026-04-04 16:33:28'),
(5, 'Thyroid Function Screening Now Available', 'thyroid-function-screening-now-available-at-labsync', 'LabSync now offers a comprehensive thyroid function screening panel designed to support earlier detection of common thyroid imbalances and help patients monitor ongoing treatment with greater confidence.', 'LabSync is pleased to introduce a comprehensive thyroid function screening panel to our growing test catalog. Thyroid health plays an important role in regulating metabolism, energy levels, body temperature, heart rate, and overall hormonal balance. When thyroid hormone levels become too high or too low, patients may experience symptoms such as fatigue, unexplained weight changes, mood fluctuations, hair thinning, sensitivity to cold or heat, and difficulty concentrating.', 'images/blog/blog_69d0fb655c7195.70071111.jpg', 1, 6, 'published', '2026-04-04 13:52:16', '2026-04-04 17:22:05', '2026-04-04 17:42:05'),
(6, 'How to Prepare for a Lipid Profile Test', 'how-to-prepare-properly-for-a-lipid-profile-test', 'Proper preparation for a lipid profile can improve result accuracy and help your doctor better evaluate cholesterol and triglyceride levels as part of your heart health assessment.', 'A lipid profile test is commonly used to measure different types of fats in the blood, including total cholesterol, LDL cholesterol, HDL cholesterol, and triglycerides. These values can provide important information about cardiovascular risk and are often requested as part of a routine health screening, ongoing monitoring, or a broader medical evaluation.', 'images/blog/blog_69d10ce1633fc3.68189949.jpg', 2, 6, 'published', '2026-04-04 15:06:48', '2026-04-04 18:36:41', '2026-04-04 18:37:09'),
(7, 'What Patients Should Bring Visiting', 'what-patients-should-bring-before-visiting-the-laboratory', 'A little preparation before arriving at the laboratory can save time, reduce delays, and help patients complete their test visit more smoothly and with less stress.', 'Before coming to the laboratory, patients are encouraged to review any instructions they received about their test and make sure they bring the necessary documents or information. In many cases, this may include a doctor\'s referral, a valid identification document, appointment details, previous relevant reports, and a list of current medications if applicable. Having these items ready can make the registration process more efficient and reduce unnecessary delays.', 'images/blog/blog_69d10e058f6c55.93362067.jpg', 2, 6, 'published', '2026-04-04 15:11:56', '2026-04-04 18:41:33', '2026-04-04 20:28:53');

-- --------------------------------------------------------

-- Table structure for table `dashboard_users`

CREATE TABLE IF NOT EXISTS `dashboard_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('admin','receptionist','technician') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert dashboard users
INSERT INTO `dashboard_users` (`user_id`, `username`, `password`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin01', 'admin123', 'admin01@lab.com', 'admin', 'active', '2025-10-13 13:19:23', '2025-10-13 13:19:23'),
(2, 'reception01', 'recept123', 'reception01@lab.com', 'receptionist', 'active', '2025-10-13 13:19:23', '2025-10-13 13:19:23'),
(3, 'tech01', 'tech123', 'tech01@lab.com', 'technician', 'active', '2025-10-13 13:19:23', '2025-10-13 13:19:23')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- --------------------------------------------------------

-- Table structure for table `inventory`

CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(100) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `reorder_level` int(255) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`inventory_id`),
  KEY `fk_suppliers` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert inventory items
INSERT INTO `inventory` (`inventory_id`, `item_name`, `supplier_id`, `quantity`, `reorder_level`, `last_updated`) VALUES
(1, 'Glucose Test Kit', 1, 50, 10, '2025-10-20 22:52:11'),
(2, 'Blood Collection Tubes', 2, 120, 30, '2025-10-20 22:52:11'),
(3, 'Urine Sample Bottles', 1, 80, 20, '2025-10-20 22:52:11'),
(4, 'Microscope Slides', 3, 200, 40, '2025-10-20 22:52:11'),
(5, 'COVID-19 Rapid Test Kit', 2, 60, 15, '2025-10-20 22:52:11'),
(6, 'Latex Gloves', 3, 30, 50, '2025-10-21 08:45:45'),
(9, 'gloves', 2, 1000, 50, '2025-10-22 18:09:48')
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`);

-- --------------------------------------------------------

-- Table structure for table `labs`

CREATE TABLE IF NOT EXISTS `labs` (
  `lab_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`lab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `orders`

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Processing','Completed','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `patient_id` (`patient_id`),
  KEY `lab_id` (`lab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `order_details`

CREATE TABLE IF NOT EXISTS `order_details` (
  `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `test_id` int(11) DEFAULT NULL,
  `outsourced_lab_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Processing','Completed') DEFAULT 'Pending',
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `test_id` (`test_id`),
  KEY `outsourced_lab_id` (`outsourced_lab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `outsourced_labs`

CREATE TABLE IF NOT EXISTS `outsourced_labs` (
  `outsourced_lab_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`outsourced_lab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- Table structure for table `patients`

CREATE TABLE IF NOT EXISTS `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_name` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert patients
INSERT INTO `patients` (`patient_id`, `patient_name`, `date_of_birth`, `gender`, `email`, `contact_number`, `created_at`, `updated_date`, `address`) VALUES
(1, 'Isuru Perera', '1998-04-15', 'Male', 'isuru.perera@example.com', '0712045678', '2025-10-17 14:48:40', '2025-10-18 07:01:16', NULL),
(2, 'Nadeesha Fernando', '2000-09-21', 'Female', 'nadeesha.fernando@example.com', '0779876543', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(3, 'Kasun Jayasinghe', '1995-12-02', 'Male', 'kasun.jayasinghe@example.com', '0751122334', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(4, 'Rashmi Silva', '1999-06-10', 'Female', 'rashmi.silva@example.com', '0769988776', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(5, 'Tharindu De Alwis', '1997-02-27', 'Male', 'tharindu.alwis@example.com', '0784455667', '2025-10-17 14:48:40', '2025-10-17 14:48:40', NULL),
(7, 'saman kumara', '2025-10-16', 'Male', 'ucsc@gmail.com', '1234567890', '2025-10-20 08:33:26', '2025-10-20 08:33:26', NULL),
(8, 'manmitha', NULL, NULL, 'abc@gmail.com', '123456789', '2025-10-22 11:41:59', '2025-10-22 11:41:59', NULL),
(9, 'saman', NULL, NULL, 'saman@gmail.com', '12345678', '2025-10-22 11:43:33', '2025-10-22 11:43:33', NULL),
(11, 'karuni', NULL, 'Male', 'karu@gmail.com', '123456789', '2025-10-22 12:40:39', '2025-10-22 13:33:35', ''),
(12, 'surin', NULL, 'Male', 'surini@gmail.com', '12345678', '2025-10-22 17:52:55', '2025-10-22 18:05:46', ''),
(13, 'patient', NULL, NULL, 'patient@gmail.com', '123456789', '2025-10-22 18:37:41', '2025-10-22 18:37:41', NULL),
(20, 'yasindu', NULL, NULL, 'yas@gmail.com', '1234567890', '2025-10-23 04:27:41', '2025-10-23 04:27:41', NULL),
(22, 'yasindu6', NULL, NULL, 'yasindu6@gmail.com', '1234567890', '2025-10-23 05:26:29', '2025-10-23 05:26:29', NULL),
(23, 'iw', NULL, NULL, 'a@gmail.com', 'wwww', '2025-12-26 18:17:15', '2025-12-26 18:17:15', NULL),
(24, 'isuman', NULL, NULL, 'isum@gmail.com', '0712345678', '2026-01-28 02:25:51', '2026-01-28 02:25:51', NULL),
(25, 'isum', NULL, NULL, 'yasindudesilva2@gmail.com', '0719688583', '2026-01-28 02:34:48', '2026-01-28 02:34:48', NULL),
(26, 'isum', NULL, NULL, 'isum3@gmail.com', '0712345678', '2026-01-28 02:35:45', '2026-01-28 02:35:45', NULL),
(27, 'Yasindu De Silva', NULL, NULL, 'yasindudesilva10@gmail.com', '0719658588', '2026-01-28 02:48:29', '2026-01-28 02:48:29', NULL),
(28, 's', NULL, NULL, 'one@gmail.com', '01111111111', '2026-01-28 03:38:51', '2026-01-28 03:38:51', NULL),
(29, 'Yasindu De Silva', NULL, NULL, 'yasindudesilva1@gmail.com', '0719658583', '2026-01-28 03:43:36', '2026-01-28 03:43:36', NULL),
(30, 'yas', NULL, NULL, 'yss@gmail.com', '0123456789', '2026-01-31 04:49:54', '2026-01-31 04:49:54', NULL),
(31, 'Yasindu De Silva', NULL, 'Male', 'yasindudesilva11@gmail.com', '0719658583', '2026-02-17 15:34:07', '2026-02-17 16:44:31', ''),
(33, 'sachi', NULL, NULL, 'sachi@gmail.com', '0719658583', '2026-04-07 11:52:31', '2026-04-07 11:52:31', NULL)
ON DUPLICATE KEY UPDATE `patient_name` = VALUES(`patient_name`);

-- --------------------------------------------------------

-- Table structure for table `suppliers`

CREATE TABLE IF NOT EXISTS `suppliers` (
  `supplier_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(150) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`supplier_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert suppliers
INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_no`, `location`, `email`, `created_at`, `updated_at`) VALUES
(1, 'ABC Medical Supplies', '0771234567', 'Colombo', 'abcmed@example.com', '2025-10-20 22:29:10', '2025-10-20 22:29:10'),
(2, 'HealthPlus Distributors', '0712345678', 'Kandy', 'healthplus@example.com', '2025-10-20 22:29:10', '2025-10-20 22:29:10'),
(3, 'BioTech Labs', '0759876543', 'Galle', 'biotechlabs@example.com', '2025-10-20 22:29:10', '2025-10-20 22:29:10')
ON DUPLICATE KEY UPDATE `supplier_name` = VALUES(`supplier_name`);

-- --------------------------------------------------------

-- Table structure for table `tests`

CREATE TABLE IF NOT EXISTS `tests` (
  `test_id` int(11) NOT NULL AUTO_INCREMENT,
  `test_name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_outsourced` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert tests
INSERT INTO `tests` (`test_id`, `test_name`, `category`, `description`, `price`, `is_outsourced`, `created_at`) VALUES
(1, 'Complete Blood Count (CBC)', 'Other', '', 100.00, 0, '2025-09-02 08:24:24'),
(2, 'Liver Function Test (LFT)', 'Other', 'Evaluates liver enzymes, proteins, and bilirubin levels', 2500.00, 0, '2025-09-02 08:24:24'),
(3, 'Thyroid Function Test (TFT)', 'Other', 'Assesses T3, T4, and TSH levels for thyroid performance', 3000.00, 1, '2025-09-02 08:24:24'),
(4, 'Blood Sugar Test', 'Other', 'Measures fasting and random blood glucose levels', 1200.00, 0, '2025-09-02 08:24:24'),
(5, 'Cholesterol Test', 'Blood Test', 'Checks total cholesterol, HDL, LDL, and triglycerides', 1800.00, 0, '2025-09-02 08:24:24'),
(6, 'COVID-19 PCR Test', 'Other', 'Detects SARS-CoV-2 virus genetic material', 6000.00, 1, '2025-09-02 08:24:24'),
(7, 'Urine Routine Test', 'Other', '', 1000.00, 0, '2025-09-02 08:24:24'),
(8, 'Kidney Function Test (KFT)', 'Other', 'Measures urea, creatinine, and electrolytes', 2200.00, 1, '2025-09-02 08:24:24'),
(9, 'sdfghjm', 'urine', NULL, 12344.00, 0, '2025-10-15 13:42:20'),
(10, 'ghjk', 'molecular', NULL, 1234.00, 0, '2025-10-15 13:54:55'),
(17, 'blood', 'blood', '', 200.00, 0, '2025-10-17 08:16:09'),
(20, 'pressure test', 'imaging', 'a test to check the blood pressure', 1200.00, 0, '2025-10-22 18:07:38'),
(21, 'Blood Test', 'blood', 'shghjjkdjljkcl', 1200.00, 0, '2025-10-23 04:30:35')
ON DUPLICATE KEY UPDATE `test_name` = VALUES(`test_name`);

-- Seed appointment_items from existing appointments (only if not already seeded)
INSERT INTO `appointment_items` (`appointment_id`, `test_id`, `unit_price`, `quantity`, `line_total`)
SELECT a.`appointment_id`, a.`test_id`, COALESCE(t.`price`, 0), 1, COALESCE(t.`price`, 0)
FROM `appointment` a
LEFT JOIN `tests` t ON t.`test_id` = a.`test_id`
WHERE NOT EXISTS (
    SELECT 1
    FROM `appointment_items` ai
    WHERE ai.`appointment_id` = a.`appointment_id`
      AND ai.`test_id` = a.`test_id`
);

-- --------------------------------------------------------

-- Table structure for table `users`

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `role` enum('admin','receptionist','technician','patient') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert users
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
(31, 'iw', '$2y$10$S1dsbkNI5FTUM4esvV7n8eWPQEpNr9/HbDJcVsh.0SD54V/80YbeW', 'a@gmail.com', 'wwww', 'patient', 'active', '2025-12-26 18:17:15', '2025-12-26 18:17:15'),
(32, '', '$2y$10$xNkTa9w2LdL6uVCpbKEQ3ueXAK3nqZfmK2j.4Jdgh0t/S0tv94sBy', 'isum@gmail.com', '0712345678', 'patient', 'active', '2026-01-28 02:25:51', '2026-01-28 02:25:51'),
(33, '', '$2y$10$ZNP/MTlmbl5XKKTJwCKRQuALasigPL9lI0yAucinlIb50E52xEWi2', 'yasindudesilva2@gmail.com', '0719688583', 'patient', 'active', '2026-01-28 02:34:48', '2026-01-28 02:34:48'),
(34, '', '$2y$10$VYpnd.UlijijzgJooNyIPOXlwMxAkVkRSWxlrMlPBN2UyOTEsKIHC', 'isum3@gmail.com', '0712345678', 'patient', 'active', '2026-01-28 02:35:45', '2026-01-28 02:35:45'),
(35, '', '$2y$10$.zTr5XxDZBIAPaPnL6vTnO1S8x9hel5zgI5mAZDzJ0Qem9tXuAD5m', 'yasindudesilva10@gmail.com', '0719658588', 'patient', 'active', '2026-01-28 02:48:29', '2026-01-28 02:48:29'),
(36, '', '$2y$10$yUO1ucFCCgJhfH0yCpkfwO/8DyYTzU.navPiSL7WZ32LSWBHdtnx2', 'one@gmail.com', '01111111111', 'patient', 'active', '2026-01-28 03:38:51', '2026-01-28 03:38:51'),
(37, '', '$2y$10$AwjYED6pwlOVuyJSndHpfuF4LskXc0K1SX8SiKxvqLKuoMkfk1OMG', 'yasindudesilva1@gmail.com', '0719658583', 'patient', 'active', '2026-01-28 03:43:36', '2026-01-28 03:43:36'),
(38, '', '$2y$10$M5SSo3Ft7/aiZhJqhEpgMuXq4vTa9CDIvenK3yOtXJAULO6Xk1rfO', 'yss@gmail.com', '0123456789', 'patient', 'active', '2026-01-31 04:49:54', '2026-01-31 04:49:54'),
(39, 'Yasindu De Silva', '$2y$10$sBa8nxwoyo8JH5nAbeb.vO68wY.KIJo20cQIQYTpIXGyoVcDnrsVC', 'yasindudesilva11@gmail.com', '0719658583', 'patient', 'active', '2026-02-17 15:34:07', '2026-02-17 16:44:31'),
(40, '', '$2y$10$5w7xp/fzAhNYY7YCPN5i6.iWNQliZQN4P9zpEvwSlFAk4/W5oJkVe', 'sachi@gmail.com', '0719658583', 'patient', 'active', '2026-04-07 11:52:31', '2026-04-07 11:52:31')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- --------------------------------------------------------

-- AUTO_INCREMENT for all tables

ALTER TABLE `appointment` AUTO_INCREMENT = 9;
ALTER TABLE `blog_categories` AUTO_INCREMENT = 4;
ALTER TABLE `blog_posts` AUTO_INCREMENT = 8;
ALTER TABLE `dashboard_users` AUTO_INCREMENT = 4;
ALTER TABLE `inventory` AUTO_INCREMENT = 10;
ALTER TABLE `labs` AUTO_INCREMENT = 1;
ALTER TABLE `orders` AUTO_INCREMENT = 1;
ALTER TABLE `order_details` AUTO_INCREMENT = 1;
ALTER TABLE `outsourced_labs` AUTO_INCREMENT = 1;
ALTER TABLE `patients` AUTO_INCREMENT = 34;
ALTER TABLE `suppliers` AUTO_INCREMENT = 4;
ALTER TABLE `tests` AUTO_INCREMENT = 27;
ALTER TABLE `users` AUTO_INCREMENT = 41;
ALTER TABLE `appointment_items` AUTO_INCREMENT = 1;
ALTER TABLE `prescription_requests` AUTO_INCREMENT = 1;
ALTER TABLE `prescription_request_events` AUTO_INCREMENT = 1;

-- --------------------------------------------------------
-- Foreign key constraints (added after all tables exist)

ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`);

ALTER TABLE `appointment_items`
  ADD CONSTRAINT `fk_appointment_items_appointment`
    FOREIGN KEY (`appointment_id`) REFERENCES `appointment`(`appointment_id`)
    ON DELETE CASCADE,
  ADD CONSTRAINT `fk_appointment_items_test`
    FOREIGN KEY (`test_id`) REFERENCES `tests`(`test_id`)
    ON DELETE RESTRICT;

ALTER TABLE `prescription_requests`
  ADD CONSTRAINT `fk_prescription_requests_patient`
    FOREIGN KEY (`patient_id`) REFERENCES `patients`(`patient_id`)
    ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prescription_requests_decision_user`
    FOREIGN KEY (`decision_by_user_id`) REFERENCES `users`(`user_id`)
    ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prescription_requests_appointment`
    FOREIGN KEY (`linked_appointment_id`) REFERENCES `appointment`(`appointment_id`)
    ON DELETE SET NULL;

ALTER TABLE `prescription_request_events`
  ADD CONSTRAINT `fk_pre_request`
    FOREIGN KEY (`request_id`) REFERENCES `prescription_requests`(`request_id`)
    ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pre_created_by`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`user_id`)
    ON DELETE SET NULL;

ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_suppliers` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`lab_id`);

ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`test_id`),
  ADD CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`outsourced_lab_id`) REFERENCES `outsourced_labs` (`outsourced_lab_id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
