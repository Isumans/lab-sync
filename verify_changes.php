<?php
require_once 'config/db.php';

$db = connect();

echo "<h2>Stock History Table Verification</h2>";
echo "<hr>";

// Check stock_history table
$query = "SELECT sh.history_id, sh.inventory_id, sh.purchase_id, sh.quantity, sh.action, 
                 sh.unit_cost, sh.supplier_id, sh.expiry_date, sh.created_at,
                 i.item_name
          FROM stock_history sh
          LEFT JOIN inventory i ON sh.inventory_id = i.inventory_id
          ORDER BY sh.created_at DESC LIMIT 5";

$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<p><strong>Stock History Records (First 5):</strong></p>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Inv ID</th><th>Item</th><th>Qty</th><th>Unit Cost</th><th>Supplier</th><th>Expiry</th><th>Date</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['history_id'] . "</td>";
        echo "<td>" . $row['inventory_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>Rs. " . number_format($row['unit_cost'], 2) . "</td>";
        echo "<td>" . $row['supplier_id'] . "</td>";
        echo "<td>" . ($row['expiry_date'] ?? '-') . "</td>";
        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No stock history records found!</p>";
}

echo "<hr>";

// Check inventory items with categories
echo "<p><strong>Inventory Items with Categories (First 5):</strong></p>";
$query = "SELECT i.inventory_id, i.item_name, i.quantity, ic.category_name
          FROM inventory i
          LEFT JOIN inventory_categories ic ON i.category_id = ic.category_id
          ORDER BY i.item_name ASC LIMIT 5";

$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Item Name</th><th>Quantity</th><th>Category</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['inventory_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . htmlspecialchars($row['category_name'] ?? 'Uncategorized') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No inventory items found!</p>";
}

echo "<hr>";
echo "<p><a href='index.php?controller=inventoryController&action=index'>Go to Inventory</a></p>";

mysqli_close($db);
?>
