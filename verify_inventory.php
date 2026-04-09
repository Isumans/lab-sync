<?php
require_once 'config/db.php';
require_once 'app/models/inventoryModel.php';

$db = connect();
$model = new inventoryModel($db);

echo "<h2>Verification of Database Data</h2>";
echo "<hr>";

// Test Stock Purchases
$purchases = $model->getAllPurchases();
echo "<p><strong>Stock Purchases Count:</strong> " . count($purchases) . "</p>";
if (count($purchases) > 0) {
    echo "<p><strong>First Purchase:</strong></p>";
    echo "<ul>";
    echo "<li>Item Name: " . htmlspecialchars($purchases[0]['item_name']) . "</li>";
    echo "<li>Inventory ID: " . $purchases[0]['inventory_id'] . "</li>";
    echo "<li>Purchase ID: " . $purchases[0]['purchase_id'] . "</li>";
    echo "<li>Quantity: " . $purchases[0]['quantity_purchased'] . "</li>";
    echo "<li>Cost: " . $purchases[0]['unit_cost'] . "</li>";
    echo "</ul>";
}

echo "<hr>";

// Test Categories with Icons
$categories = $model->getAllCategories();
echo "<p><strong>Categories Count:</strong> " . count($categories) . "</p>";
if (count($categories) > 0) {
    echo "<p><strong>Category Icons:</strong></p>";
    echo "<ul>";
    foreach ($categories as $cat) {
        $icon = inventoryModel::getCategoryIcon($cat['category_name']);
        echo "<li>" . $icon . " " . htmlspecialchars($cat['category_name']) . "</li>";
    }
    echo "</ul>";
}

echo "<hr>";

// Test Getting Item by ID
$item = $model->getItemById(40);
if ($item) {
    echo "<p><strong>Sample Item (ID 40):</strong></p>";
    echo "<ul>";
    echo "<li>Name: " . htmlspecialchars($item['item_name']) . "</li>";
    echo "<li>Quantity: " . $item['quantity'] . "</li>";
    echo "</ul>";
} else {
    echo "<p>Item ID 40 not found</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to home</a> | <a href='index.php?controller=inventoryController&action=index'>Go to Inventory</a></p>";
?>
