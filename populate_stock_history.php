<?php
/**
 * Populate Stock History and Add Expiry Dates
 */

require_once 'config/db.php';

$db = connect();

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<h2>Populating Stock History with Expiry Dates</h2>";
echo "<p>Adding stock history records with purchase details and expiry dates...</p>";
echo "<hr>";

// First, clear existing stock_history data to avoid duplicates
mysqli_query($db, "DELETE FROM stock_history WHERE history_id > 0");

// Get all purchases
$purchasesQuery = "SELECT sp.purchase_id, sp.inventory_id, sp.supplier_id, sp.quantity_purchased, 
                          sp.unit_cost, sp.total_cost, sp.purchase_date, i.item_name
                   FROM stock_purchases sp
                   JOIN inventory i ON sp.inventory_id = i.inventory_id
                   ORDER BY sp.purchase_date DESC";

$purchasesResult = mysqli_query($db, $purchasesQuery);
if (!$purchasesResult) {
    die("Query failed: " . mysqli_error($db));
}

$expiryDates = [
    'Blood Collection Tube' => date('Y-m-d', strtotime('2028-03-15')),
    'Glucose Reagent' => date('Y-m-d', strtotime('2027-03-10')),
    'Latex Gloves (Box)' => date('Y-m-d', strtotime('2029-03-12')),
    'Pipette Tips (1000)' => date('Y-m-d', strtotime('2030-03-08')),
    'Sodium Chloride Solution' => date('Y-m-d', strtotime('2027-03-05')),
    'Centrifuge Tubes' => date('Y-m-d', strtotime('2028-03-18')),
    'Amino Acid Analyzer' => date('Y-m-d', strtotime('2030-02-28')),
    'Disinfectant Solution' => date('Y-m-d', strtotime('2027-03-20')),
    'Petri Dishes (100)' => date('Y-m-d', strtotime('2029-03-14')),
    'Microcentrifuge' => date('Y-m-d', strtotime('2030-01-15')),
];

// Update stock_purchases with expiry dates
echo "<p><strong>Updating Expiry Dates in stock_purchases:</strong></p>";
$updateCount = 0;
while ($purchase = mysqli_fetch_assoc($purchasesResult)) {
    $itemName = $purchase['item_name'];
    $expiryDate = $expiryDates[$itemName] ?? date('Y-m-d', strtotime('+2 years'));
    
    $updateQuery = "UPDATE stock_purchases SET expiry_date = ? WHERE purchase_id = ?";
    $stmt = mysqli_prepare($db, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'si', $expiryDate, $purchase['purchase_id']);
    
    if (mysqli_stmt_execute($stmt)) {
        $updateCount++;
        echo "<p style='color: green;'>✓ Updated expiry date for purchase ID: " . $purchase['purchase_id'] . " (Item: $itemName) - Expiry: $expiryDate</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to update purchase ID: " . $purchase['purchase_id'] . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Creating Stock History Records:</strong></p>";

// Get all purchases again for stock_history
$purchasesResult = mysqli_query($db, $purchasesQuery);
$historyCount = 0;

while ($purchase = mysqli_fetch_assoc($purchasesResult)) {
    $expiryDate = $expiryDates[$purchase['item_name']] ?? date('Y-m-d', strtotime('+2 years'));
    
    $historyQuery = "INSERT INTO stock_history 
                    (inventory_id, purchase_id, quantity, action, unit_cost, supplier_id, expiry_date, notes, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($db, $historyQuery);
    $action = 'Purchase';
    $notes = "Purchased from supplier";
    $timestamp = date('Y-m-d H:i:s', strtotime($purchase['purchase_date']));
    $unit_cost = floatval($purchase['unit_cost']);
    
    mysqli_stmt_bind_param(
        $stmt,
        'iisidisss',
        $purchase['inventory_id'],
        $purchase['purchase_id'],
        $purchase['quantity_purchased'],
        $action,
        $unit_cost,
        $purchase['supplier_id'],
        $expiryDate,
        $notes,
        $timestamp
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $historyCount++;
        echo "<p style='color: green;'>✓ Stock history created for: " . htmlspecialchars($purchase['item_name']) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create history for item ID: " . $purchase['inventory_id'] . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>Summary:</strong></p>";
echo "<p>✓ Updated expiry dates: " . $updateCount . " records</p>";
echo "<p>✓ Created stock history: " . $historyCount . " records</p>";

// Verify
$historyCheck = mysqli_query($db, "SELECT COUNT(*) as count FROM stock_history");
$row = mysqli_fetch_assoc($historyCheck);
echo "<p>Total stock history records: " . $row['count'] . "</p>";

echo "<hr>";
echo "<p><a href='/lab_sync/index.php'>Go to home</a> or <a href='/lab_sync/index.php?controller=inventoryController&action=index'>Go to inventory</a></p>";

mysqli_close($db);
?>
