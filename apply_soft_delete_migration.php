<?php
// Apply Soft Delete Migration
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
    // Add soft delete columns to inventory table
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;",
    "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;",
    
    // Add indexes
    "ALTER TABLE inventory ADD INDEX IF NOT EXISTS idx_deleted_date (deleted_date);",
    "ALTER TABLE inventory ADD INDEX IF NOT EXISTS idx_deleted_by (deleted_by);",
    
    // Add soft delete columns to stock_history table
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;",
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;",
    "ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;",
    
    // Add index for stock_history
    "ALTER TABLE stock_history ADD INDEX IF NOT EXISTS idx_deleted_date (deleted_date);"
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

echo "Inventory table columns:\n";
$result = $conn->query("SHOW COLUMNS FROM inventory WHERE Field LIKE 'deleted%';");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "  No columns found!\n";
}

echo "\nStock History table columns:\n";
$result = $conn->query("SHOW COLUMNS FROM stock_history WHERE Field LIKE 'deleted%';");
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
