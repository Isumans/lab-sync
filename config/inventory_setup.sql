-- Database Setup for Inventory Management System
-- Run these queries to set up the necessary tables for inventory functionality

-- 1. Create inventory_categories table if it doesn't exist
CREATE TABLE IF NOT EXISTS inventory_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Add category_id column to inventory table if it doesn't exist
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS category_id INT, 
ADD CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES inventory_categories(category_id) ON DELETE SET NULL;

-- 3. Create stock_history table for tracking inventory changes
CREATE TABLE IF NOT EXISTS stock_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    action VARCHAR(50) NOT NULL, -- 'Added', 'Removed', etc.
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE,
    INDEX idx_inventory_id (inventory_id),
    INDEX idx_created_at (created_at)
);

-- 4. Insert some default categories (optional)
INSERT INTO inventory_categories (category_name, description) VALUES
('Medical Supplies', 'General medical supplies and consumables'),
('Laboratory Equipment', 'Lab equipment and machinery'),
('Chemicals', 'Chemical reagents and solutions'),
('Consumables', 'Disposable items and consumables'),
('Equipment', 'Reusable equipment and instruments')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Verify the tables were created
SHOW TABLES LIKE 'inventory%';
SHOW TABLES LIKE 'stock%';
