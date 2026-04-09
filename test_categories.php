<?php
require_once 'config/db.php';
require_once 'app/models/inventoryModel.php';

$db = connect();
$model = new inventoryModel($db);
$categories = $model->getAllCategories();

echo "<pre>";
echo "Categories from database:\n";
print_r($categories);
echo "</pre>";

$suppliers = $model->getAllSuppliers();
echo "<pre>";
echo "Suppliers from database:\n";
print_r($suppliers);
echo "</pre>";
?>
