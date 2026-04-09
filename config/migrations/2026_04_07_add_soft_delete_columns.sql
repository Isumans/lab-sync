-- Migration: Add soft delete columns to inventory tables
-- Date: April 7, 2026
-- Description: Adds deleted_date, deleted_time, and deleted_by columns for soft delete functionality

-- Add soft delete columns to inventory table
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;

-- Add index for efficient filtering of soft-deleted items
ALTER TABLE inventory ADD INDEX IF NOT EXISTS idx_deleted_date (deleted_date);
ALTER TABLE inventory ADD INDEX IF NOT EXISTS idx_deleted_by (deleted_by);

-- Add soft delete columns to stock_history table  
ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;
ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;
ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;

-- Add index for efficient filtering of soft-deleted stock history records
ALTER TABLE stock_history ADD INDEX IF NOT EXISTS idx_deleted_date (deleted_date);

-- Verify the changes
SHOW COLUMNS FROM inventory WHERE Field LIKE 'deleted%';
SHOW COLUMNS FROM stock_history WHERE Field LIKE 'deleted%';
