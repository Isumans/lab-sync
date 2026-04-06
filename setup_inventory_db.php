<?php
/**
 * Inventory Database Setup - Updated Schema
 * This script creates all necessary tables for the inventory system
 */

require_once 'config/db.php';

$db = connect();

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Inventory Database Setup - Medical Laboratory</h2>";
echo "<p>Initializing inventory system tables...</p>";
echo "<hr>";

$sql_statements = [
    // Create suppliers table
    "CREATE TABLE IF NOT EXISTS suppliers (
        supplier_id INT AUTO_INCREMENT PRIMARY KEY,
        supplier_name VARCHAR(150) NOT NULL,
        contact_person VARCHAR(100),
        phone VARCHAR(20),
        email VARCHAR(100),
        address TEXT,
        city VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Create inventory_categories table
    "CREATE TABLE IF NOT EXISTS inventory_categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    // Update inventory table to include category and improve structure
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS category_id INT,
     ADD COLUMN IF NOT EXISTS unit_of_measure VARCHAR(50) DEFAULT 'Units',
     ADD COLUMN IF NOT EXISTS unit_cost DECIMAL(10, 2),
     ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'In Stock'",
    
    // Create stock_purchases table for purchase history
    "CREATE TABLE IF NOT EXISTS stock_purchases (
        purchase_id INT AUTO_INCREMENT PRIMARY KEY,
        inventory_id INT NOT NULL,
        supplier_id INT NOT NULL,
        quantity_purchased INT NOT NULL,
        unit_cost DECIMAL(10, 2),
        total_cost DECIMAL(12, 2),
        purchase_date DATE NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE,
        INDEX idx_purchase_date (purchase_date),
        INDEX idx_inventory_id (inventory_id)
    )",
    
    // Create stock_history table
    "CREATE TABLE IF NOT EXISTS stock_history (
        history_id INT AUTO_INCREMENT PRIMARY KEY,
        inventory_id INT NOT NULL,
        quantity INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE,
        INDEX idx_inventory_id (inventory_id),
        INDEX idx_created_at (created_at)
    )",
    
    // Insert medical laboratory categories
    "INSERT INTO inventory_categories (category_name, description) VALUES
    ('Blood Tests', 'Blood collection tubes, lancets, and blood test supplies'),
    ('Reagents', 'Chemical reagents and solutions for lab testing'),
    ('Consumables', 'Disposable items: gloves, masks, pipette tips'),
    ('Equipment', 'Laboratory equipment: centrifuge, analyzer, microscope'),
    ('Safety Equipment', 'PPE and safety equipment for laboratory staff'),
    ('Sterilization Supplies', 'Disinfectants, sterilization solutions, and supplies')
    ON DUPLICATE KEY UPDATE description = VALUES(description)",
    
    // Insert sample suppliers
    "INSERT INTO suppliers (supplier_name, contact_person, phone, email, city) VALUES
    ('Roche Diagnostics', 'John Smith', '+1-555-0100', 'contact@roche.com', 'Basel'),
    ('Abbott Diagnostics', 'Sarah Johnson', '+1-555-0200', 'info@abbott.com', 'Chicago'),
    ('Siemens Healthcare', 'Michael Brown', '+1-555-0300', 'support@siemens.com', 'Munich'),
    ('Bio-Rad Laboratories', 'Emma Davis', '+1-555-0400', 'sales@biorad.com', 'California'),
    ('Thermo Fisher Scientific', 'David Wilson', '+1-555-0500', 'orders@thermofisher.com', 'Massachusetts')
    ON DUPLICATE KEY UPDATE phone = VALUES(phone)"
];

$errors = [];
$successes = [];

foreach ($sql_statements as $index => $sql) {
    if (mysqli_query($db, $sql)) {
        $successes[] = "Statement " . ($index + 1) . " executed successfully";
    } else {
        $errors[] = "Error in statement " . ($index + 1) . ": " . mysqli_error($db);
    }
}

// Add foreign keys if they don't exist
$fk_statements = [
    "ALTER TABLE inventory ADD CONSTRAINT fk_inventory_category FOREIGN KEY (category_id) REFERENCES inventory_categories(category_id) ON DELETE SET NULL",
    "ALTER TABLE inventory ADD CONSTRAINT fk_inventory_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL"
];

foreach ($fk_statements as $fk_sql) {
    if (@mysqli_query($db, $fk_sql)) {
        $successes[] = "Foreign key added successfully";
    }
}

// Display results
echo "<h3 style='color: #333;'>Setup Results:</h3>";

if (!empty($successes)) {
    echo "<div style='color: green; border: 2px solid green; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong style='font-size: 1.1em;'>✓ Successful Operations:</strong><br>";
    foreach ($successes as $success) {
        echo "✓ " . htmlspecialchars($success) . "<br>";
    }
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='color: #d32f2f; border: 2px solid #d32f2f; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong style='font-size: 1.1em;'>⚠ Notes (OK if tables already exist):</strong><br>";
    foreach ($errors as $error) {
        echo "⚠ " . htmlspecialchars($error) . "<br>";
    }
    echo "</div>";
}

// Verify tables and columns
echo "<h3 style='color: #333;'>Table Verification:</h3>";

$tables_to_check = [
    'suppliers' => [],
    'inventory_categories' => [],
    'stock_history' => [],
    'stock_purchases' => [],
    'inventory' => ['category_id', 'status', 'unit_cost']
];

foreach ($tables_to_check as $table => $columns_to_verify) {
    $result = mysqli_query($db, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<span style='color: green; font-weight: bold;'>✓ Table '$table' exists</span>";
        
        if (!empty($columns_to_verify)) {
            foreach ($columns_to_verify as $col) {
                $col_result = mysqli_query($db, "SHOW COLUMNS FROM $table LIKE '$col'");
                if (mysqli_num_rows($col_result) > 0) {
                    echo " | <span style='color: green;'>✓ '$col'</span>";
                } else {
                    echo " | <span style='color: orange;'>⚠ '$col'</span>";
                }
            }
        }
        echo "<br>";
    } else {
        echo "<span style='color: red; font-weight: bold;'>✗ Table '$table' NOT FOUND</span><br>";
    }
}

echo "<hr>";
echo "<p style='font-size: 1.1em; color: #333;'>";
echo "<a href='/lab_sync/index.php?controller=inventoryController&action=index' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>✓ Return to Inventory</a>";
echo "</p>";

mysqli_close($db);
?>
