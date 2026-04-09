<?php
// Test script for soft delete functionality
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/bootstrap.php';
require_once MODEL_PATH . '/inventoryModel.php';

$conn = connect();

echo "=== Soft Delete Functionality Test ===\n\n";

// Test 1: Check if soft delete columns exist
echo "Test 1: Verify database columns exist\n";
echo "-----------------------------------\n";

$tables = ['inventory', 'inventory_categories', 'stock_purchases', 'stock_history'];
$allGood = true;

foreach ($tables as $table) {
    $query = "SHOW COLUMNS FROM $table WHERE Field LIKE 'deleted%'";
    $result = mysqli_query($conn, $query);
    $count = mysqli_num_rows($result);
    
    if ($count >= 3) {
        echo "✓ $table: All 3 soft delete columns found\n";
    } else {
        echo "✗ $table: Missing columns (found $count/3)\n";
        $allGood = false;
    }
}

echo "\n";

// Test 2: Check if indexes exist
echo "Test 2: Verify indexes exist\n";
echo "-----------------------------\n";

$indexesToCheck = [
    'inventory' => ['idx_deleted_date', 'idx_deleted_by'],
    'inventory_categories' => ['idx_deleted_date', 'idx_deleted_by'],
    'stock_purchases' => ['idx_deleted_date', 'idx_deleted_by'],
    'stock_history' => ['idx_deleted_date']
];

foreach ($indexesToCheck as $table => $indexes) {
    foreach ($indexes as $index) {
        $query = "SHOW INDEX FROM $table WHERE Key_name = '$index'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            echo "✓ $table.$index exists\n";
        } else {
            echo "✗ $table.$index missing\n";
            $allGood = false;
        }
    }
}

echo "\n";

// Test 3: Check if model methods exist
echo "Test 3: Model methods availability\n";
echo "----------------------------------\n";

$inventoryModel = new inventoryModel();
$methods = ['softDeleteItem', 'softDeleteCategory', 'softDeletePurchase', 'getAllItems', 'getAllCategories'];

foreach ($methods as $method) {
    if (method_exists($inventoryModel, $method)) {
        echo "✓ inventoryModel::$method() exists\n";
    } else {
        echo "✗ inventoryModel::$method() missing\n";
        $allGood = false;
    }
}

echo "\n";

// Test 4: Check controller methods
echo "Test 4: Controller methods availability\n";
echo "--------------------------------------\n";

require_once CONTROLLER_PATH . '/inventoryController.php';
$inventoryController = new inventoryController();
$controllerMethods = ['soft_delete_item', 'soft_delete_category', 'soft_delete_purchase', 'edit_item', 'edit_category'];

foreach ($controllerMethods as $method) {
    if (method_exists($inventoryController, $method)) {
        echo "✓ inventoryController::$method() exists\n";
    } else {
        echo "✗ inventoryController::$method() missing\n";
        $allGood = false;
    }
}

echo "\n";

// Test 5: Sample data check
echo "Test 5: Sample data in tables\n";
echo "----------------------------\n";

$tableStats = [];
foreach ($tables as $table) {
    $query = "SELECT COUNT(*) as cnt FROM $table";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    echo "  $table: " . $row['cnt'] . " records\n";
}

echo "\n";

// Final verdict
if ($allGood) {
    echo "✅ ALL TESTS PASSED - System is ready!\n";
} else {
    echo "⚠️  Some issues found - please review above\n";
}

echo "\n=== Test Complete ===\n";

mysqli_close($conn);
?>
