<?php
require_once 'config/db.php';
require_once 'app/models/inventoryModel.php';

$model = new inventoryModel();
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
