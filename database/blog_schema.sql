-- LabSync Blog System Database Schema
-- Run this file manually in phpMyAdmin or MySQL client before using the blog feature

-- Create blog_categories table
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create blog_posts table
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

-- Seed default categories
INSERT INTO `blog_categories` (`name`, `slug`) VALUES
('New Tests', 'new-tests'),
('Patient Instructions', 'patient-instructions'),
('Health Education', 'health-education')
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

-- Success message
SELECT 'Blog tables created successfully!' AS message;
