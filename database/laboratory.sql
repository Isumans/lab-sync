-- LabSync Laboratory Schema Updates
-- Consolidated SQL for appointment domain phase 2

-- Phase 2 Appointment Domain Upgrade
-- Run this script once in your MySQL/MariaDB database.

ALTER TABLE appointment
    ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT 'Pending' AFTER method,
    ADD COLUMN IF NOT EXISTS booking_channel VARCHAR(40) NULL AFTER status,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER booking_channel,
    ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER updated_at;

UPDATE appointment
SET status = 'Pending'
WHERE status IS NULL OR status = '';

UPDATE appointment
SET booking_channel = CASE
    WHEN LOWER(method) = 'online' THEN 'online_self'
    WHEN LOWER(method) = 'call' THEN 'receptionist_phone'
    ELSE 'receptionist_walkin'
END
WHERE booking_channel IS NULL OR booking_channel = '';

CREATE TABLE IF NOT EXISTS appointment_items (
    appointment_item_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    test_id INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    line_total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_items_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_appointment_items_test
        FOREIGN KEY (test_id) REFERENCES tests(test_id)
        ON DELETE RESTRICT
);

INSERT INTO appointment_items (appointment_id, test_id, unit_price, quantity, line_total)
SELECT a.appointment_id, a.test_id, COALESCE(t.price, 0), 1, COALESCE(t.price, 0)
FROM appointment a
LEFT JOIN tests t ON t.test_id = a.test_id
WHERE NOT EXISTS (
    SELECT 1
    FROM appointment_items ai
    WHERE ai.appointment_id = a.appointment_id
      AND ai.test_id = a.test_id
);

-- -------------------------------------------------------------------
-- Blog System Schema
-- -------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `blog_categories` (
    `category_id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL,
    PRIMARY KEY (`category_id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
    `post_id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL,
    `excerpt` TEXT NOT NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `category_id` INT(11) DEFAULT NULL,
    `author_id` INT(11) NOT NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`post_id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `idx_status_published` (`status`, `published_at`),
    KEY `idx_category` (`category_id`),
    KEY `idx_author` (`author_id`),
    KEY `idx_created` (`created_at`),
    CONSTRAINT `fk_blog_category` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`category_id`) ON DELETE SET NULL,
    CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blog_categories` (`name`, `slug`) VALUES
('New Tests', 'new-tests'),
('Patient Instructions', 'patient-instructions'),
('Health Education', 'health-education')
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

SELECT 'Laboratory schema updates completed successfully!' AS message;
