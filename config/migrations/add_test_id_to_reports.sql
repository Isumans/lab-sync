-- Migration: Add test_id to reports table
-- Run this manually in phpMyAdmin if not already done

ALTER TABLE `reports` ADD COLUMN `test_id` INT NULL AFTER `appointment_id`;
ALTER TABLE `reports` ADD KEY `idx_reports_test` (`test_id`);
