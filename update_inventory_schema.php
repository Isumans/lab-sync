<?php
/**
 * Update Inventory Database Schema
 * Adds expiry dates and enhances stock history tracking
 */

require_once 'config/db.php';

$db = connect();

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Updating Inventory Database Schema</h2>";
echo "<p>Adding expiry dates and enhancing stock tracking...</p>";
echo "<hr>";

$alterStatements = [
    // Add expiry_date to stock_purchases
    "ALTER TABLE stock_purchases ADD COLUMN IF NOT EXISTS expiry_date DATE",
    
    // Enhance stock_history with purchase details
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS purchase_id INT",
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS unit_cost DECIMAL(10, 2)",
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS supplier_id INT",
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS expiry_date DATE",
];

$successes = [];
$errors = [];

foreach ($alterStatements as $index => $sql) {
    if (@mysqli_query($db, $sql)) {
        echo "<p style='color: green;'>✓ Statement " . ($index + 1) . " executed successfully</p>";
        $successes[] = "Statement " . ($index + 1);
    } else {
        echo "<p style='color: orange;'>⚠ Statement " . ($index + 1) . " (may already exist): " . mysqli_error($db) . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Schema Update Summary:</strong></p>";
echo "<p style='color: green;'>Successes: " . count($successes) . "</p>";

// Verify columns
echo "<hr>";
echo "<p><strong>Column Verification:</strong></p>";

$columns_to_check = [
    'stock_purchases' => ['expiry_date'],
    'stock_history' => ['purchase_id', 'unit_cost', 'supplier_id', 'expiry_date']
];

foreach ($columns_to_check as $table => $columns) {
    foreach ($columns as $col) {
        $col_result = mysqli_query($db, "SHOW COLUMNS FROM $table LIKE '$col'");
        if (mysqli_num_rows($col_result) > 0) {
            echo "<p style='color: green;'>✓ Column '$col' exists in '$table'</p>";
        } else {
            echo "<p style='color: red;'>✗ Column '$col' NOT FOUND in '$table'</p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='/lab_sync/index.php'>Go to home</a> or <a href='/lab_sync/index.php?controller=inventoryController&action=index'>Go to inventory</a></p>";

mysqli_close($db);
?>
