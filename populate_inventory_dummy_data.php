<?php
/**
 * Populate Inventory with Dummy Data
 * This script adds dummy data to inventory, stock_purchases, and related tables
 */

require_once 'config/db.php';

$db = connect();

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Populating Inventory Database with Dummy Data</h2>";
echo "<p>This script will add sample inventory items and purchase records...</p>";
echo "<hr>";

echo "<hr>";

// Insert inventory items
$inventoryItems = [
    ['name' => 'Blood Collection Tube', 'qty' => 500, 'reorder' => 10, 'supplier' => 1, 'category' => 6, 'unit_cost' => 2.50, 'unit' => 'Box'],
    ['name' => 'Glucose Reagent', 'qty' => 100, 'reorder' => 20, 'supplier' => 1, 'category' => 7, 'unit_cost' => 15.00, 'unit' => 'Bottle'],
    ['name' => 'Latex Gloves (Box)', 'qty' => 200, 'reorder' => 50, 'supplier' => 2, 'category' => 8, 'unit_cost' => 5.00, 'unit' => 'Box'],
    ['name' => 'Pipette Tips (1000)', 'qty' => 50, 'reorder' => 15, 'supplier' => 2, 'category' => 4, 'unit_cost' => 12.00, 'unit' => 'Pack'],
    ['name' => 'Sodium Chloride Solution', 'qty' => 20, 'reorder' => 10, 'supplier' => 1, 'category' => 3, 'unit_cost' => 8.50, 'unit' => 'Bottle'],
    ['name' => 'Centrifuge Tubes', 'qty' => 300, 'reorder' => 50, 'supplier' => 2, 'category' => 1, 'unit_cost' => 3.75, 'unit' => 'Pack'],
    ['name' => 'Amino Acid Analyzer', 'qty' => 2, 'reorder' => 1, 'supplier' => 3, 'category' => 5, 'unit_cost' => 5000.00, 'unit' => 'Unit'],
    ['name' => 'Disinfectant Solution', 'qty' => 15, 'reorder' => 10, 'supplier' => 1, 'category' => 9, 'unit_cost' => 25.00, 'unit' => 'Liter'],
    ['name' => 'Petri Dishes (100)', 'qty' => 80, 'reorder' => 20, 'supplier' => 3, 'category' => 1, 'unit_cost' => 6.50, 'unit' => 'Pack'],
    ['name' => 'Microcentrifuge', 'qty' => 1, 'reorder' => 1, 'supplier' => 2, 'category' => 5, 'unit_cost' => 3500.00, 'unit' => 'Unit']
];

$inventoryIds = [];
$purchaseData = [];
$errors = [];
$successes = [];

echo "<p><strong>Inserting Inventory Items:</strong></p>";
foreach ($inventoryItems as $idx => $item) {
    $query = "INSERT INTO inventory (item_name, quantity, reorder_level, supplier_id, category_id, unit_cost, unit_of_measure, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, 'In Stock')";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'siiidss', $item['name'], $item['qty'], $item['reorder'], $item['supplier'], $item['category'], $item['unit_cost'], $item['unit']);
    
    if (mysqli_stmt_execute($stmt)) {
        $inv_id = mysqli_insert_id($db);
        $inventoryIds[] = $inv_id;
        echo "<p style='color: green;'>✓ Item inserted: {$item['name']} (ID: $inv_id)</p>";
        $successes[] = "Item: {$item['name']}";
    } else {
        echo "<p style='color: red;'>✗ Item insert failed: {$item['name']} - " . mysqli_error($db) . "</p>";
        $errors[] = "Item: {$item['name']} - " . mysqli_error($db);
    }
}

// Prepare purchase data
$purchaseRecords = [
    ['item_idx' => 0, 'supplier' => 1, 'qty_purchased' => 500, 'unit_cost' => 2.50, 'total' => 1250.00, 'date' => '2026-03-15', 'notes' => 'Regular stock replenishment'],
    ['item_idx' => 1, 'supplier' => 1, 'qty_purchased' => 100, 'unit_cost' => 15.00, 'total' => 1500.00, 'date' => '2026-03-10', 'notes' => 'Bulk order for monthly supply'],
    ['item_idx' => 2, 'supplier' => 2, 'qty_purchased' => 200, 'unit_cost' => 5.00, 'total' => 1000.00, 'date' => '2026-03-12', 'notes' => 'Standard safety equipment order'],
    ['item_idx' => 3, 'supplier' => 2, 'qty_purchased' => 50, 'unit_cost' => 12.00, 'total' => 600.00, 'date' => '2026-03-08', 'notes' => 'Lab consumables'],
    ['item_idx' => 4, 'supplier' => 1, 'qty_purchased' => 20, 'unit_cost' => 8.50, 'total' => 170.00, 'date' => '2026-03-05', 'notes' => 'Chemical solutions for testing'],
    ['item_idx' => 5, 'supplier' => 2, 'qty_purchased' => 300, 'unit_cost' => 3.75, 'total' => 1125.00, 'date' => '2026-03-18', 'notes' => 'Additional tubes for centrifuge'],
    ['item_idx' => 6, 'supplier' => 3, 'qty_purchased' => 2, 'unit_cost' => 5000.00, 'total' => 10000.00, 'date' => '2026-02-28', 'notes' => 'New laboratory equipment'],
    ['item_idx' => 7, 'supplier' => 1, 'qty_purchased' => 15, 'unit_cost' => 25.00, 'total' => 375.00, 'date' => '2026-03-20', 'notes' => 'Sterilization supplies'],
    ['item_idx' => 8, 'supplier' => 3, 'qty_purchased' => 80, 'unit_cost' => 6.50, 'total' => 520.00, 'date' => '2026-03-14', 'notes' => 'Culture media supplies'],
    ['item_idx' => 9, 'supplier' => 2, 'qty_purchased' => 1, 'unit_cost' => 3500.00, 'total' => 3500.00, 'date' => '2026-01-15', 'notes' => 'Microcentrifuge equipment']
];

echo "<p><strong>Inserting Purchase Records:</strong></p>";
foreach ($purchaseRecords as $idx => $purchase) {
    if (!isset($inventoryIds[$purchase['item_idx']])) {
        echo "<p style='color: red;'>✗ Skipped purchase: no corresponding inventory item</p>";
        continue;
    }
    
    $inv_id = $inventoryIds[$purchase['item_idx']];
    $query = "INSERT INTO stock_purchases (inventory_id, supplier_id, quantity_purchased, unit_cost, total_cost, purchase_date, notes) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, 'iiiidss', $inv_id, $purchase['supplier'], $purchase['qty_purchased'], $purchase['unit_cost'], $purchase['total'], $purchase['date'], $purchase['notes']);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✓ Purchase record inserted for item ID: $inv_id</p>";
        $successes[] = "Purchase for inventory ID: $inv_id";
    } else {
        echo "<p style='color: red;'>✗ Purchase insert failed for item ID: $inv_id - " . mysqli_error($db) . "</p>";
        $errors[] = "Purchase for inventory ID: $inv_id - " . mysqli_error($db);
    }
}

echo "<hr>";
echo "<p><strong>Summary:</strong></p>";
echo "<p style='color: green;'>Successes: " . count($successes) . "</p>";
if (count($errors) > 0) {
    echo "<p style='color: red;'>Errors: " . count($errors) . "</p>";
    foreach ($errors as $error) {
        echo "<p style='color: red;'>  - " . htmlspecialchars($error) . "</p>";
    }
}

echo "<p><strong>Verification:</strong></p>";
$countItems = mysqli_query($db, "SELECT COUNT(*) as count FROM inventory WHERE category_id IS NOT NULL");
$row = mysqli_fetch_assoc($countItems);
echo "<p>Inventory items with categories: " . $row['count'] . "</p>";

$countPurchases = mysqli_query($db, "SELECT COUNT(*) as count FROM stock_purchases");
$row = mysqli_fetch_assoc($countPurchases);
echo "<p>Stock purchase records: " . $row['count'] . "</p>";

echo "<hr>";
echo "<p><a href='/lab_sync/app/views/technicians/inventory.php'>Go back to inventory</a> or <a href='/lab_sync/index.php'>Go to home</a></p>";

mysqli_close($db);
?>
