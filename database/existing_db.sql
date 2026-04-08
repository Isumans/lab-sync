-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 08, 2026 at 08:20 AM
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
(8, 33, 1, '08:00:00', '2026-02-23', 'online');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`category_id`, `name`, `slug`) VALUES
(1, 'New Tests', 'new-tests'),
(2, 'Patient Instructions', 'patient-instructions'),
(3, 'Health Education', 'health-education');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `post_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `excerpt` text NOT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`post_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category_id`, `author_id`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'hi', 'hi', 'hello', 'How to prevent and treat eye disease\r\nHow to prevent and treat ear disease\r\nHow to prevent and treat dental problems\r\nHow to prevent and treat skin diseases\r\nHow to prevent and treat digestive problems\r\nHow to prevent and treat respiratory problems\r\nHow to prevent and treat neurological problems\r\nHow to prevent and treat genetic disorders\r\nHow to prevent and treat autoimmune disorders\r\nHow to prevent and treat infectious diseases\r\nHow to prevent and treat sexually transmitted diseases\r\nHow to prevent and treat mental health problems\r\nHow to prevent and treat addiction\r\nHow to prevent and treat eating disorders\r\nHow to prevent and treat sleep disorders\r\nHow to prevent and treat hormonal imbalances\r\nHow to prevent and treat menopause symptoms\r\nHow to prevent and treat infertility\r\nHow to prevent and treat pregnancy complications\r\nHow to prevent and treat birth defects\r\nHow to prevent and treat childhood diseases\r\nHow to prevent and treat senior health problems\r\nHow to prevent and treat sexual health problems\r\nHow to prevent and treat disabilities\r\nHow to prevent and treat genetic predispositions\r\nHow to prevent and treat environmental health problems\r\nHow to prevent and treat occupational health problems\r\nHow to prevent and treat public health problems\r\n\r\n\r\n70 Listicle Blog Topics For Your Healthcare Blog\r\nList posts are another popular type of blog post that offer readers a quick and easy way to consume information. These health posts typically feature a numbered list of tips, resources, or insights that are relevant to your audience.\r\n\r\nTop 10 healthy breakfast ideas\r\n7 best exercises for a full-body workout\r\n5 simple ways to reduce stress\r\n10 foods to boost your immune system\r\n8 tips for getting a good night’s sleep\r\n6 best stretches for relieving back pain\r\n9 natural remedies for headaches\r\n7 healthy snacks to curb your cravings\r\n5 best teas for relaxation\r\n10 ways to incorporate more fiber into your diet\r\n8 tips for maintaining healthy skin\r\n7 best vitamins for overall health\r\n5 ways to boost your mental health\r\n10 healthy dinner ideas\r\n8 tips for maintaining healthy joints\r\n7 easy ways to stay hydrated\r\n5 best exercises for improving cardiovascular health\r\n10 healthy lunch ideas\r\n8 tips for managing diabetes\r\n7 best exercises for toning your body\r\n5 ways to prevent colds and flu\r\n10 healthy snack ideas\r\n8 tips for managing stress through exercise\r\n7 best exercises for improving posture\r\n5 simple ways to improve your gut health\r\n10 healthy breakfast smoothie recipes\r\n8 tips for overcoming a weight loss plateau\r\n7 best exercises for relieving menstrual cramps\r\n5 best teas for digestion\r\n10 healthy salad recipes\r\n8 tips for dealing with anxiety\r\n7 best exercises for reducing bloating\r\n5 ways to reduce sugar intake\r\n10 healthy soup recipes\r\n8 tips for dealing with depression\r\n7 best exercises for improving balance\r\n5 natural remedies for acne\r\n10 healthy grain bowl recipes\r\n8 tips for managing arthritis\r\n7 best exercises for improving flexibility\r\n5 ways to reduce inflammation\r\n10 healthy vegetable recipes\r\n8 tips for preventing heart disease\r\n7 best exercises for building strength\r\n5 ways to prevent cancer\r\n10 healthy seafood recipes\r\n8 tips for preventing stroke\r\n7 best exercises for improving endurance\r\n5 ways to prevent Alzheimer’s disease\r\n10 healthy meat recipes\r\n8 tips for preventing Parkinson’s disease\r\n7 best exercises for improving coordination\r\n5 ways to prevent multiple sclerosis\r\n10 healthy vegetarian recipes\r\n8 tips for preventing liver disease\r\n7 best exercises for improving agility\r\n5 ways to prevent kidney disease\r\n10 healthy plant-based recipes\r\n8 tips for preventing lung disease\r\n7 best exercises for improving stamina\r\n5 ways to prevent eye disease\r\n10 healthy vegan recipes\r\n8 tips for preventing ear disease\r\n7 best exercises for improving speed\r\n5 ways to prevent dental problems\r\n10 healthy gluten-free recipes\r\n8 tips for preventing skin diseases\r\n7 best exercises for improving power\r\n5 ways to prevent digestive problems\r\n10 healthy low-carb recipes\r\n\r\n\r\n30 Interviews Headline Ideas For Your Healthcare Blog\r\nInterviews are blog posts that feature an interview with an expert, influencer, or thought leader in your health industry. They’re great for providing unique insights and perspectives on a particular topic. Use interviews to provide thought leadership content and showcase your knowledge of your industry.\r\n\r\n“Expert insights: How to improve gut health”\r\n“In conversation with a nutritionist: Tips for a healthy diet”\r\n“From the mind of a fitness trainer: Workout tips for a healthy body”\r\n“Stress management with a mindfulness coach”\r\n“The sleep doctor’s guide to a good night’s sleep”\r\n“Quit smoking for good with the help of a tobacco cessation specialist”\r\n“Boosting your immune system with an immunologist”\r\n“Eating more plants with a plant-based diet expert”\r\n“Staying hydrated with a hydration specialist”\r\n“Reducing sugar intake with a dietitian”\r\n“Home workout plan with a personal trainer”\r\n“Managing back pain with a chiropractor”\r\n“Improving posture with a physical therapist”\r\n“Choosing the right vitamins with a nutritionist”\r\n“Coping with anxiety with a psychologist”\r\n“Dealing with depression with a mental health specialist”\r\n“Preventing colds with an infectious disease specialist”\r\n“Staying active during the workday with an ergonomics expert”\r\n“Reducing stress through exercise with a personal trainer”\r\n“Overcoming a weight loss plateau with a dietitian”\r\n“Improving cardiovascular health with a cardiologist”\r\n“Managing diabetes with an endocrinologist”\r\n“Incorporating more fiber into your diet with a dietitian”\r\n“Dealing with menstrual cramps with a gynecologist”\r\n“Treating and preventing headaches with a neurologist”\r\n“Preventing and treating acne with a dermatologist”\r\n“Reducing bloating with a gastroenterologist”\r\n“Improving skin health with a dermatologist”\r\n“Preventing and treating arthritis with a rheumatologist”\r\n“Improving joint health with an orthopedic specialist”', NULL, 3, 6, 'draft', '2026-02-18 06:20:20', '2026-02-18 10:50:10', '2026-04-04 17:54:59'),
(2, 'second poster', 'second-post', 'hello this is \r\npatients \r\ninstructionss', 'ieg8yvfe', 'images/blog/blog_69954de462f0c5.61489494.jpeg', 2, 6, 'archived', '2026-02-18 06:28:19', '2026-02-18 10:58:04', '2026-04-04 18:30:26'),
(3, 'hello isum', 'hello-isum', 'sjnfowurh\r\narwgwrg', 'SECTION A – THEORY QUESTIONS\r\nQuestion 1\r\n(a) Explain the CIA Triad in detail. (9 Marks)\r\nAnswer:\r\n\r\nThe CIA Triad is the fundamental model for information security. It consists of Confidentiality, Integrity, and Availability.\r\n\r\n1. Confidentiality\r\n\r\nConfidentiality ensures that sensitive information is accessible only to authorized users.\r\n\r\nIt prevents unauthorized disclosure of data such as personal records, financial information, or confidential business documents.\r\n\r\nMethods used to ensure confidentiality include:\r\n\r\nUser authentication\r\n\r\nAccess control mechanisms\r\n\r\nEncryption\r\n\r\nRole-based permissions\r\n\r\nExample:\r\nIn a hospital database, only doctors are allowed to view patient medical records. Receptionists cannot access diagnosis details.\r\n\r\n2. Integrity\r\n\r\nIntegrity ensures that data remains accurate, complete, and consistent throughout its lifecycle.\r\n\r\nIt prevents unauthorized modification or corruption of data.\r\n\r\nMethods used to maintain integrity:\r\n\r\nPrimary Key (PK) and Foreign Key (FK) constraints\r\n\r\nUNIQUE constraints\r\n\r\nHashing and checksums\r\n\r\nTransaction management (ACID properties)\r\n\r\nExample:\r\nA banking system ensures that account balances correctly reflect deposits and withdrawals without unauthorized changes.\r\n\r\n3. Availability\r\n\r\nAvailability ensures that data and systems are accessible when required by authorized users.\r\n\r\nIt prevents downtime or service disruption.\r\n\r\nMethods used to ensure availability:\r\n\r\nRegular backups\r\n\r\nDisaster recovery planning\r\n\r\nFault tolerance mechanisms\r\n\r\nRedundant systems\r\n\r\nExample:\r\nAn e-commerce website must remain operational 24/7 so customers can place orders.\r\n\r\nQuestion 2\r\nCompare DAC, MAC, and RBAC access control models. (10 Marks)\r\nAnswer:\r\n\r\nAccess control models define how permissions are assigned and managed in a database system.\r\n\r\n1. Discretionary Access Control (DAC)\r\n\r\nIn DAC, the owner of an object controls access to that object.\r\n\r\nKey characteristics:\r\n\r\nOwner decides who can access data\r\n\r\nUses GRANT and REVOKE commands\r\n\r\nFlexible and simple\r\n\r\nRisk of over-granting permissions\r\n\r\nExample SQL:\r\nGRANT SELECT ON Customers TO Employee1;\r\nREVOKE SELECT ON Customers FROM Employee1;\r\n\r\nAdvantages:\r\n\r\nEasy to implement\r\n\r\nFlexible\r\n\r\nDisadvantages:\r\n\r\nDifficult to manage in large organizations\r\n\r\nCan lead to inconsistent permissions\r\n\r\n2. Mandatory Access Control (MAC)\r\n\r\nIn MAC, access is controlled by a central authority based on security classifications.\r\n\r\nKey characteristics:\r\n\r\nSystem enforces access rules\r\n\r\nData labeled (Confidential, Secret, Top Secret)\r\n\r\nUsers cannot change permissions\r\n\r\nUsed in:\r\n\r\nMilitary systems\r\n\r\nGovernment agencies\r\n\r\nHealthcare environments\r\n\r\nAdvantages:\r\n\r\nVery secure\r\n\r\nPrevents data misuse\r\n\r\nDisadvantages:\r\n\r\nLess flexible\r\n\r\nHard to implement\r\n\r\n3. Role-Based Access Control (RBAC)\r\n\r\nIn RBAC, access is assigned based on user roles rather than individuals.\r\n\r\nKey characteristics:\r\n\r\nRoles define permissions\r\n\r\nUsers assigned to roles\r\n\r\nScalable and manageable\r\n\r\nExample:\r\nAdmin → Full control\r\nManager → View and approve\r\nEmployee → View only\r\n\r\nAdvantages:\r\n\r\nEasy to maintain\r\n\r\nReduces errors\r\n\r\nScales well\r\n\r\nDisadvantages:\r\n\r\nRequires careful role planning\r\n\r\nComparison Summary:\r\n\r\nDAC → Owner controls\r\nMAC → System controls\r\nRBAC → Role controls\r\n\r\nQuestion 3\r\nExplain Authentication and Authorization with examples. (6 Marks)\r\nAnswer:\r\n\r\nAuthentication is the process of verifying the identity of a user.\r\n\r\nIt answers the question: “Who are you?”\r\n\r\nExamples:\r\n\r\nPassword login\r\n\r\nOTP verification\r\n\r\nBiometric fingerprint\r\n\r\nAuthorization is the process of determining what an authenticated user is allowed to do.\r\n\r\nIt answers the question: “What can you do?”\r\n\r\nExamples:\r\n\r\nRead data\r\n\r\nInsert records\r\n\r\nUpdate grades\r\n\r\nDelete files\r\n\r\nAuthentication always occurs before authorization.\r\n\r\nQuestion 4\r\nExplain Row-Level Security (RLS) and Views as security mechanisms. (8 Marks)\r\nAnswer:\r\nRow-Level Security (RLS)\r\n\r\nRow-Level Security restricts access to specific rows within a table.\r\n\r\nDifferent users can see different records in the same table.\r\n\r\nExample:\r\nA lecturer can only see marks of students in their own class.\r\n\r\nBenefits:\r\n\r\nFine-grained access control\r\n\r\nImproves confidentiality\r\n\r\nPrevents unauthorized viewing\r\n\r\nViews as Security Layers\r\n\r\nA view is a virtual table created from a SQL query.\r\n\r\nIt does not store data physically but displays selected data.\r\n\r\nUses of views in security:\r\n\r\nHide sensitive columns (e.g., salary, NIC)\r\n\r\nRestrict rows\r\n\r\nEnforce access control without changing base tables\r\n\r\nExample:\r\nCREATE VIEW student_view AS\r\nSELECT student_id, name FROM Students;\r\n\r\nViews help implement data protection policies.\r\n\r\nSECTION B – ENCRYPTION & SECURITY\r\nQuestion 5\r\nExplain types of encryption used in database security. (9 Marks)\r\nAnswer:\r\n\r\nEncryption converts readable data into an unreadable format using a secret key.\r\n\r\n1. Encryption at Rest\r\n\r\nProtects stored data.\r\n\r\nCovers:\r\n\r\nTable files\r\n\r\nDatabase backups\r\n\r\nLogs\r\n\r\nDisk storage\r\n\r\nPurpose:\r\nPrevents data theft if storage devices are compromised.\r\n\r\n2. Encryption in Transit\r\n\r\nProtects data while it is being transmitted between systems.\r\n\r\nExample:\r\n\r\nClient ↔ Database\r\n\r\nApplication ↔ Server\r\n\r\nUses:\r\n\r\nHTTPS\r\n\r\nTLS/SSL\r\n\r\nPrevents:\r\n\r\nPassword sniffing\r\n\r\nData interception\r\n\r\n3. Column-Level Encryption\r\n\r\nEncrypts specific sensitive columns inside a table.\r\n\r\nUsed for:\r\n\r\nNational ID numbers\r\n\r\nPassport numbers\r\n\r\nCredit card numbers\r\n\r\nMedical information\r\n\r\nExample MySQL functions:\r\nAES_ENCRYPT()\r\nAES_DECRYPT()\r\n\r\nColumn-level encryption ensures that highly sensitive data is protected even if the database is accessed.\r\n\r\nQuestion 6\r\nDiscuss common database threats and their impact. (8 Marks)\r\nAnswer:\r\n\r\nCommon database threats include:\r\n\r\nSQL Injection\r\nAttackers insert malicious SQL queries to manipulate database operations.\r\n\r\nWeak Passwords\r\nEasy-to-guess passwords increase unauthorized access risk.\r\n\r\nPrivilege Escalation\r\nUsers gain higher permissions than allowed.\r\n\r\nInsider Threats\r\nAuthorized users misuse access.\r\n\r\nUnencrypted Backups\r\nSensitive data exposed if backups are stolen.\r\n\r\nImpact of Weak Database Security:\r\n\r\nLoss of trust\r\n\r\nFinancial loss\r\n\r\nIdentity theft\r\n\r\nLegal penalties (GDPR/PDPA)\r\n\r\nOperational disruption\r\n\r\nOrganizations may face fines and reputational damage.\r\n\r\nSECTION C – SCENARIO QUESTIONS\r\nQuestion 7\r\n\r\nA hospital assigns roles as Doctor, Nurse, and Receptionist. Doctors can see all patient records, nurses can see only assigned patients, and receptionists can view only appointment schedules.\r\n\r\n(a) Identify the access control model used.\r\n(b) Explain how it works. (8 Marks)\r\nAnswer:\r\n\r\n(a) The model used is Role-Based Access Control (RBAC).\r\n\r\n(b) In RBAC, permissions are assigned to roles rather than individual users.\r\n\r\nDoctor role → Full access to patient records\r\nNurse role → Limited to assigned patients (row-level restriction)\r\nReceptionist role → Access only to appointment data\r\n\r\nUsers are assigned to roles, and roles determine permissions. This improves scalability and security.\r\n\r\nQuestion 8\r\n\r\nA military system classifies documents as Low, Medium, and High. A soldier tries to access a High-level document but only has Low clearance.\r\n\r\n(a) Identify the access control model.\r\n(b) Explain the system decision. (6 Marks)\r\nAnswer:\r\n\r\n(a) Mandatory Access Control (MAC)\r\n\r\n(b) In MAC, access is based on security classification levels.\r\n\r\nUsers can only access data at or below their clearance level.\r\n\r\nSince the soldier has Low clearance and the document is High-level, the system will deny access.\r\n\r\nMAC is system-enforced and cannot be overridden by users.', '', 1, 6, 'published', '2026-02-24 08:44:02', '2026-02-24 13:13:39', '2026-02-24 13:14:02'),
(4, 'NEW BLOG POST ABOUT TEST', 'new-blog-post-about-test', 'A NEW TEST', 'Today, Glaukos Corporation announced clinical updates for several studies in their Corneal Health pipeline programs. Enrollment has begun for a second Phase 3 confirmatory trial for Epioxa (Epi-on), and promising Phase 2a results for GLK-301 (iLution – Dry Eye Disease) has encouraged Glaukos to advance GLK-301 into a Phase 2b clinical trial, which will begin in 2023.\r\n\r\n“These clinical updates represent meaningful milestones for two of our key corneal Health pipeline programs and we look forward to continuing to advance both of these important programs forward in 2023,” Thomas Burns, Glaukos chairman and chief executive officer, said in a press release. “We continue to successfully invest in and advance our robust pipeline of novel, dropless platform technologies designed to meaningfully advance the standard of care and improve outcomes for patients suffering from chronic eye diseases.”\r\n\r\nEpioxa Phase 3 confirmatory trial\r\nEnrollment began for the second Phase 3 confirmatory pivotal trial for Epioxa (Epi-on), which is a next-generation corneal cross-linking therapy for keratoconus. Its predecessor, Photrexa or Epi-off, is the only FDA-approved treatment to slow and halt the progression of keratoconus.\r\n\r\nGlaukos intends to investigate Epioxa in 290 randomized subjects, all of whom should be enrolled by the end of 2023. Previously, Glaukos’ first Phase 3 pivotal trial for Epioxa met the pre-specified primary efficacy endpoint and had been confirmed by the FDA that the results would support the submission and review of a new drug Application (NDA) with support from this second trial.\r\n\r\nGLK-301 (iLution – Dry Eye Disease)\r\nAdditionally, Glaukos shared topline results from the first-in-human Phase 2a clinical trial for GLK-301 (iLution – Dry Eye Disease). GLK-301 is designed to treat the signs and symptoms of dry eye disease (DED). The sterile ophthalmic topical cream is applied to the eyelids twice daily, and the results demonstrated an improved quality of tear film, or tear break-up time, and a correlating improvement in quality of vision, or reduction in blurred vision.\r\n\r\nIn total, 218 subjects were enrolled in the Phase 2a multi-center, randomized, double-masked, placebo-controlled trial, which spanned across clinical sites in the United States. The trial investigated the safety and efficacy of 3 dose levels of GLK-301 administered twice daily to eyelids vs. placebo over 28 days with a 14-day safety follow-up period.\r\n\r\nGLK-301 is the first drug candidate to leverage Glaukos’ iLution platform’s patented cream-based drug formulations. The dropless formula is cream-based, to be applied to the outer surface of the eyelid for transdermal delivery of active compounds to treat eye disorders. The cream delivers pilocarpine through the dermis of the eyelid to the eye, correcting issues without drops.\r\n\r\nBased on the positive topline results, Glaukos will move forward with GLK-301, with plans to begin a Phase 2b clinical trial in 2023.', 'images/blog/blog_69d0eff6948c28.96239221.webp', 1, 6, 'published', '2026-04-04 13:03:28', '2026-04-04 16:33:18', '2026-04-04 16:33:28'),
(5, 'Thyroid Function Screening Now Available', 'thyroid-function-screening-now-available-at-labsync', 'LabSync now offers a comprehensive thyroid function screening panel designed to support earlier detection of common thyroid imbalances and help patients monitor ongoing treatment with greater confidence.', 'LabSync is pleased to introduce a comprehensive thyroid function screening panel to our growing test catalog. Thyroid health plays an important role in regulating metabolism, energy levels, body temperature, heart rate, and overall hormonal balance. When thyroid hormone levels become too high or too low, patients may experience symptoms such as fatigue, unexplained weight changes, mood fluctuations, hair thinning, sensitivity to cold or heat, and difficulty concentrating.\r\n\r\nThis screening panel is intended to support patients who have been advised by their doctor to investigate possible thyroid-related symptoms or to monitor an existing thyroid condition. By measuring key indicators commonly used in thyroid assessment, the test can help clinicians build a clearer picture of thyroid activity and determine whether additional evaluation or treatment adjustments may be needed.\r\n\r\nAt LabSync, we understand that many patients seek not only accurate diagnostics but also a smoother and more informed testing experience. That is why this new screening option has been added to our digital test catalog, making it easier for patients to review available services and prepare for their laboratory visit. Clear instructions and timely updates are part of our effort to make healthcare interactions more convenient and less stressful.\r\n\r\nPatients are encouraged to follow any preparation guidance provided by their doctor or laboratory staff before sample collection. If you are currently taking thyroid medication, please speak with your doctor about whether any specific timing instructions apply before your test. As always, test results should be interpreted by a qualified medical professional in the context of your symptoms, medical history, and any ongoing treatment plan.', 'images/blog/blog_69d0fb655c7195.70071111.jpg', 1, 6, 'published', '2026-04-04 13:52:16', '2026-04-04 17:22:05', '2026-04-04 17:42:05'),
(6, 'How to Prepare for a Lipid Profile Test', 'how-to-prepare-properly-for-a-lipid-profile-test', 'Proper preparation for a lipid profile can improve result accuracy and help your doctor better evaluate cholesterol and triglyceride levels as part of your heart health assessment.', 'A lipid profile test is commonly used to measure different types of fats in the blood, including total cholesterol, LDL cholesterol, HDL cholesterol, and triglycerides. These values can provide important information about cardiovascular risk and are often requested as part of a routine health screening, ongoing monitoring, or a broader medical evaluation.\r\n\r\nTo help ensure the most reliable results, patients are often advised to follow specific preparation instructions before the test. In many cases, fasting for a number of hours may be recommended, especially if triglyceride levels are being assessed. During this fasting period, patients are generally asked to avoid food and beverages other than plain water. Drinking water is usually allowed and may even help make blood collection easier.\r\n\r\nPatients should also avoid unusually heavy meals, alcohol intake, and strenuous physical activity shortly before testing unless their doctor provides different guidance. If you are taking regular medication, it is best to ask your doctor whether it should be continued as usual before the test. Medical advice may vary depending on your personal condition and treatment plan.\r\n\r\nOn the day of your appointment, try to arrive on time and bring any documents or referral notes related to the requested test. Wearing comfortable clothing may also help if blood collection is required. Through LabSync, our aim is to give patients clearer preparation guidance so their visit feels more organized, efficient, and reassuring from start to finish.', 'images/blog/blog_69d10ce1633fc3.68189949.jpg', 2, 6, 'published', '2026-04-04 15:06:48', '2026-04-04 18:36:41', '2026-04-04 18:37:09'),
(7, 'What Patients Should Bring Visiting', 'what-patients-should-bring-before-visiting-the-laboratory', 'A little preparation before arriving at the laboratory can save time, reduce delays, and help patients complete their test visit more smoothly and with less stress.', 'Before coming to the laboratory, patients are encouraged to review any instructions they received about their test and make sure they bring the necessary documents or information. In many cases, this may include a doctor’s referral, a valid identification document, appointment details, previous relevant reports, and a list of current medications if applicable. Having these items ready can make the registration process more efficient and reduce unnecessary delays.\r\n\r\nIt is also important to confirm whether the requested test requires fasting, a morning sample, medication timing adjustments, or any other specific preparation. Patients who are unsure should contact the laboratory or speak with their healthcare provider in advance. Arriving without the proper preparation may sometimes affect result quality or require the test to be rescheduled.\r\n\r\nWearing comfortable clothing can also be helpful, especially for blood collection procedures. Patients who feel anxious about sample collection may benefit from arriving a little early and informing staff if they have had dizziness, fainting, or previous difficulty during blood draws. Good communication with the laboratory team helps create a safer and more comfortable experience.\r\n\r\nAt LabSync, we aim to support patients before they even step into the laboratory. Clear guidance, organized booking information, and practical preparation steps can make a significant difference in how confident and prepared a patient feels. A smoother visit not only saves time but also contributes to a more positive healthcare experience overall.', 'images/blog/blog_69d10e058f6c55.93362067.jpg', 2, 6, 'published', '2026-04-04 15:11:56', '2026-04-04 18:41:33', '2026-04-04 20:28:53');

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
(9, 'gloves', 2, 1000, 50, '2025-10-22 18:09:48');

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
(33, 'sachi', NULL, NULL, 'sachi@gmail.com', '0719658583', '2026-04-07 11:52:31', '2026-04-07 11:52:31', NULL);

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
(9, 'sdfghjm', 'urine', NULL, 12344.00, 0, '2025-10-15 13:42:20'),
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
(40, '', '$2y$10$5w7xp/fzAhNYY7YCPN5i6.iWNQliZQN4P9zpEvwSlFAk4/W5oJkVe', 'sachi@gmail.com', '0719658583', 'patient', 'active', '2026-04-07 11:52:31', '2026-04-07 11:52:31');

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
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_status_published` (`status`,`published_at`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `fk_blog_author` (`author_id`);

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
  ADD KEY `fk_suppliers` (`supplier_id`);

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
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dashboard_users`
--
ALTER TABLE `dashboard_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
