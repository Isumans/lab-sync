<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/inventoryModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
class inventoryController {
    private $db;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }
    public function index() {
        $inventoryModel = new inventoryModel($this->db);
        $items = $inventoryModel->getAllItems();
        $stockHistory = $inventoryModel->getStockHistoryDetails();
        $stockPurchases = $inventoryModel->getAllPurchases();
        $categories = $inventoryModel->getAllCategories();
        $stats = $inventoryModel->getDashboardStats();
        
        // Get categories with their associated items
        $categoriesWithItems = [];
        foreach ($categories as $category) {
            $categoriesWithItems[] = $inventoryModel->getCategoryWithItems($category['category_id']);
        }
        
        include VIEW_PATH . '/technicians/inventory.php';
    }

    public function add_inventory() {
        $inventoryModel = new inventoryModel($this->db);
        $categories = $inventoryModel->getAllCategories();
        $suppliers = $inventoryModel->getAllSuppliers();
        $errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
        unset($_SESSION['form_errors']);
        
        // Debug: Check if categories are loaded
        if (empty($categories)) {
            error_log("Warning: No categories loaded in add_inventory()");
        }
        
        include VIEW_PATH . '/technicians/addInventory.php';
    }
    
    public function store() {
        $item_name = trim($_POST['item_name'] ?? '');
        $quantity = $_POST['quantity'] ?? 0;
        $reorder_level = $_POST['reorder_level'] ?? 0;
        $supplier_id = $_POST['supplier_id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $unit_cost = $_POST['unit_cost'] ?? 0;
        $unit_of_measure = $_POST['unit_of_measure'] ?? 'Units';
        $expiry_date = $_POST['expiry_date'] ?? null;

        $errors = [];

        // Validation: Item name required
        if (empty($item_name)) {
            $errors['item_name'] = 'Item name is required.';
        }

        // Validation: Category required
        if (empty($category_id)) {
            $errors['category_id'] = 'Category is required.';
        }

        // Validation: Supplier ID required
        if (empty($supplier_id)) {
            $errors['supplier_id'] = 'Supplier ID is required.';
        } else {
            // Validation: Supplier ID must be numeric
            if (!is_numeric($supplier_id)) {
                $errors['supplier_id'] = 'Supplier ID must be a number.';
            } else {
                // Validation: Supplier must exist in database
                $inventoryModel = new inventoryModel($this->db);
                $supplier = $inventoryModel->getSupplierById($supplier_id);
                if (!$supplier) {
                    $errors['supplier_id'] = 'Supplier ID does not exist. Please enter a valid supplier ID.';
                }
            }
        }

        // Validation: Unit cost required and must be numeric
        if (empty($unit_cost)) {
            $errors['unit_cost'] = 'Unit cost is required.';
        } else {
            if (!is_numeric($unit_cost)) {
                $errors['unit_cost'] = 'Unit cost must be a number.';
            } elseif ($unit_cost < 0) {
                $errors['unit_cost'] = 'Unit cost cannot be negative.';
            }
        }

        // Validation: Quantity must be numeric and non-negative
        if (!is_numeric($quantity) || $quantity < 0) {
            $errors['quantity'] = 'Initial quantity must be a non-negative number.';
        }

        // Validation: Reorder level must be numeric and non-negative
        if (!is_numeric($reorder_level) || $reorder_level < 0) {
            $errors['reorder_level'] = 'Reorder level must be a non-negative number.';
        }

        // If there are errors, store them in session and redirect back to form
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            header('Location: /lab_sync/index.php?controller=inventoryController&action=add_inventory');
            exit();
        }

        // All validations passed, add item to database
        $inventoryModel = new inventoryModel($this->db);
        $success = $inventoryModel->addItem($item_name, $quantity, $reorder_level, $supplier_id, $category_id, $unit_cost, $unit_of_measure, $expiry_date);

        if ($success) {
            unset($_SESSION['form_data']);
            header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
        } else {
            $_SESSION['form_errors'] = ['general' => 'Error adding item to database. Please try again.'];
            header('Location: /lab_sync/index.php?controller=inventoryController&action=add_inventory');
        }
    }
    
    public function edit_item() {
        $inventoryModel = new inventoryModel($this->db);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $itemId = $_POST['inventory_id'] ?? null;
            $itemName = $_POST['item_name'] ?? '';
            $quantity = $_POST['quantity'] ?? 0;
            $reorderLevel = $_POST['reorder_level'] ?? 0;
            $supplierId = $_POST['supplier_id'] ?? null;
            $categoryId = $_POST['category_id'] ?? null;
            $unitCost = $_POST['unit_cost'] ?? 0;
            $unitOfMeasure = $_POST['unit_of_measure'] ?? 'Units';
            $expiryDate = $_POST['expiry_date'] ?? null;
            
            if (isset($_POST['delete'])) {
                // Authorize user - check if user is logged in
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'User not authorized']);
                    exit();
                }
                
                $userId = $_SESSION['user_id'];
                
                // Perform soft delete
                $success = $inventoryModel->softDeleteItem($itemId, $userId);
                if ($success) {
                    header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
                } else {
                    echo "Error deleting item.";
                }
            } elseif (isset($_POST['edit'])) {
                if (empty($itemName)) {
                    echo "Item name is required.";
                    return;
                }
                $success = $inventoryModel->updateItem($itemId, $itemName, $quantity, $reorderLevel, $supplierId, $categoryId, $unitCost, $unitOfMeasure, $expiryDate);
                if ($success) {
                    header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
                } else {
                    echo "Error updating item.";
                }
            }
        }
    }

    public function soft_delete_item() {
        // AJAX endpoint for soft delete with authorization
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjax()) {
            header('Content-Type: application/json');
            
            // Verify user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'User not authorized']);
                exit();
            }
            
            $itemId = $_POST['inventory_id'] ?? null;
            if (!$itemId || !is_numeric($itemId)) {
                echo json_encode(['success' => false, 'error' => 'Invalid inventory ID']);
                exit();
            }
            
            $userId = $_SESSION['user_id'];
            $inventoryModel = new inventoryModel($this->db);
            
            // Perform soft delete
            $success = $inventoryModel->softDeleteItem($itemId, $userId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Item deleted successfully',
                    'inventory_id' => $itemId
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete item']);
            }
            exit();
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit();
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function add_category() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_name = $_POST['category_name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (empty($category_name)) {
                echo "Category name is required.";
                return;
            }

            $inventoryModel = new inventoryModel($this->db);
            $success = $inventoryModel->addCategory($category_name, $description);

            if ($success) {
                header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
            } else {
                echo "Error adding category.";
            }
        }
    }

    public function edit_category() {
        $inventoryModel = new inventoryModel($this->db);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['delete_btn'])) {
                $category_id = $_POST['category_id'] ?? null;
                if ($category_id) {
                    // Authorize user
                    if (!isset($_SESSION['user_id'])) {
                        echo json_encode(['success' => false, 'error' => 'User not authorized']);
                        exit();
                    }
                    
                    $userId = $_SESSION['user_id'];
                    $success = $inventoryModel->softDeleteCategory($category_id, $userId);
                    if ($success) {
                        header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
                    } else {
                        echo "Error deleting category.";
                    }
                }
            } elseif (isset($_POST['edit_btn'])) {
                $category_id = $_POST['category_id'] ?? null;
                $category_name = $_POST['category_name'] ?? '';
                $description = $_POST['description'] ?? '';
                
                if ($category_id && !empty($category_name)) {
                    $success = $inventoryModel->updateCategory($category_id, $category_name, $description);
                    if ($success) {
                        header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
                    } else {
                        echo "Error updating category.";
                    }
                }
            }
        }
    }

    public function soft_delete_category() {
        // AJAX endpoint for soft delete category with authorization
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjax()) {
            header('Content-Type: application/json');
            
            // Verify user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'User not authorized']);
                exit();
            }
            
            $categoryId = $_POST['category_id'] ?? null;
            if (!$categoryId || !is_numeric($categoryId)) {
                echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
                exit();
            }
            
            $userId = $_SESSION['user_id'];
            $inventoryModel = new inventoryModel($this->db);
            
            // Perform soft delete
            $success = $inventoryModel->softDeleteCategory($categoryId, $userId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category deleted successfully',
                    'category_id' => $categoryId
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete category']);
            }
            exit();
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit();
    }

    public function soft_delete_purchase() {
        // AJAX endpoint for soft delete stock purchase with authorization
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjax()) {
            header('Content-Type: application/json');
            
            // Verify user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'User not authorized']);
                exit();
            }
            
            $purchaseId = $_POST['purchase_id'] ?? null;
            if (!$purchaseId || !is_numeric($purchaseId)) {
                echo json_encode(['success' => false, 'error' => 'Invalid purchase ID']);
                exit();
            }
            
            $userId = $_SESSION['user_id'];
            $inventoryModel = new inventoryModel($this->db);
            
            // Perform soft delete
            $success = $inventoryModel->softDeletePurchase($purchaseId, $userId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Purchase deleted successfully',
                    'purchase_id' => $purchaseId
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete purchase']);
            }
            exit();
        }
        
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
        exit();
    }

    public function add_item_to_category() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inventory_id = $_POST['inventory_id'] ?? null;
            $category_id = $_POST['category_id'] ?? null;

            if ($inventory_id && $category_id) {
                $inventoryModel = new inventoryModel($this->db);
                $success = $inventoryModel->addItemToCategory($inventory_id, $category_id);
                
                if ($success) {
                    header('Location: /lab_sync/index.php?controller=inventoryController&action=index#categories');
                } else {
                    echo "Error adding item to category.";
                }
            } else {
                echo "Invalid inventory ID or category ID.";
            }
        }
    }

    public function get_item_name() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $inventory_id = $_GET['inventory_id'] ?? null;

            if ($inventory_id) {
                $inventoryModel = new inventoryModel($this->db);
                $item = $inventoryModel->getItemById($inventory_id);
                
                header('Content-Type: application/json');
                if ($item) {
                    echo json_encode(['success' => true, 'item_name' => $item['item_name']]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Item not found']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid inventory ID']);
            }
        }
    }

    // Add more methods as needed
}
?>