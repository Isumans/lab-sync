<?php
// Apply Soft Delete Migration for Categories and Purchases
$servername = "localhost";
$username = "root";
$password = "";
$database = "laboratory";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database successfully!\n\n";

// Migration SQL statements
$migrations = [
    // Add soft delete columns to inventory_categories table
    "ALTER TABLE inventory_categories ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;",
    "ALTER TABLE inventory_categories ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;",
    "ALTER TABLE inventory_categories ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;",
    
    // Add indexes for inventory_categories
    "ALTER TABLE inventory_categories ADD INDEX IF NOT EXISTS idx_deleted_date (deleted_date);",
    "ALTER TABLE inventory_categories ADD INDEX IF NOT EXISTS idx_deleted_by (deleted_by);",
    
    // Add soft delete columns to stock_purchases table
    "ALTER TABLE stock_purchases ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;",
    "ALTER TABLE stock_purchases ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;",
    "ALTER TABLE stock_purchases ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;",
    
    // Add indexes for stock_purchases
    "ALTER TABLE stock_purchases ADD INDEX IF NOT EXISTS idx_deleted_date (deleted_date);",
    "ALTER TABLE stock_purchases ADD INDEX IF NOT EXISTS idx_deleted_by (deleted_by);"
];

// Execute each migration
foreach ($migrations as $index => $sql) {
    echo "Running migration " . ($index + 1) . " of " . count($migrations) . "...\n";
    echo "SQL: " . substr($sql, 0, 60) . "...\n";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Success\n\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n\n";
    }
}

// Verify changes
echo "\n=== VERIFICATION ===\n\n";

echo "Inventory Categories table columns:\n";
$result = $conn->query("SHOW COLUMNS FROM inventory_categories WHERE Field LIKE 'deleted%';");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "  No columns found!\n";
}

echo "\nStock Purchases table columns:\n";
$result = $conn->query("SHOW COLUMNS FROM stock_purchases WHERE Field LIKE 'deleted%';");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "  No columns found!\n";
}

echo "\n✅ Migration completed successfully!\n";
$conn->close();
?>
