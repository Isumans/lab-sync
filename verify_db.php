<?php
// Direct database check
$conn = mysqli_connect('localhost', 'root', '', 'laboratory');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check categories table
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM inventory_categories");
$row = mysqli_fetch_assoc($result);
echo "Total categories in database: " . $row['count'] . "<br>";

// Show all categories
$result = mysqli_query($conn, "SELECT * FROM inventory_categories");
while ($category = mysqli_fetch_assoc($result)) {
    echo "ID: " . $category['category_id'] . " - Name: " . $category['category_name'] . "<br>";
}

// Check suppliers table
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM suppliers");
$row = mysqli_fetch_assoc($result);
echo "<br>Total suppliers in database: " . $row['count'] . "<br>";

// Show all suppliers
$result = mysqli_query($conn, "SELECT * FROM suppliers");
while ($supplier = mysqli_fetch_assoc($result)) {
    echo "ID: " . $supplier['supplier_id'] . " - Name: " . $supplier['supplier_name'] . "<br>";
}

mysqli_close($conn);
?>
