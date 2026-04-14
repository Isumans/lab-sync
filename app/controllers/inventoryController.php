<?php
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
        include VIEW_PATH . '/technicians/inventory.php';
    }

    public function listInventory() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : 7;

        $filters = [
            'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
            'status' => isset($_GET['status']) ? trim(strtolower((string) $_GET['status'])) : 'all',
            'from_date' => isset($_GET['from_date']) ? trim((string) $_GET['from_date']) : '',
            'to_date' => isset($_GET['to_date']) ? trim((string) $_GET['to_date']) : '',
        ];

        $sortBy = isset($_GET['sort_by']) ? trim((string) $_GET['sort_by']) : 'last_updated';
        $sortDir = isset($_GET['sort_dir']) ? trim((string) $_GET['sort_dir']) : 'desc';

        $inventoryModel = new inventoryModel();
        $rows = $inventoryModel->getInventoryList($filters, $page, $perPage, $sortBy, $sortDir);
        $listError = $inventoryModel->getLastError();

        if ($listError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $listError
            ]);
            return;
        }

        $total = $inventoryModel->countInventory($filters);
        $countError = $inventoryModel->getLastError();
        if ($countError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $countError
            ]);
            return;
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        echo json_encode([
            'status' => 'success',
            'data' => $rows,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    public function listSuppliers() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : 7;

        $filters = [
            'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
            'status' => isset($_GET['status']) ? trim(strtolower((string) $_GET['status'])) : 'all',
            'from_date' => isset($_GET['from_date']) ? trim((string) $_GET['from_date']) : '',
            'to_date' => isset($_GET['to_date']) ? trim((string) $_GET['to_date']) : '',
        ];

        $sortBy = isset($_GET['sort_by']) ? trim((string) $_GET['sort_by']) : 'created_at';
        $sortDir = isset($_GET['sort_dir']) ? trim((string) $_GET['sort_dir']) : 'desc';

        $inventoryModel = new inventoryModel();
        $rows = $inventoryModel->getSupplierList($filters, $page, $perPage, $sortBy, $sortDir);
        $listError = $inventoryModel->getLastError();

        if ($listError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $listError
            ]);
            return;
        }

        $total = $inventoryModel->countSuppliers($filters);
        $countError = $inventoryModel->getLastError();
        if ($countError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $countError
            ]);
            return;
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        echo json_encode([
            'status' => 'success',
            'data' => $rows,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    public function add_inventory() {
        $inventoryModel = new inventoryModel();
        $categories = $inventoryModel->getInventoryCategories();
        $suppliers = $inventoryModel->searchSuppliers('', 100);
        include VIEW_PATH . '/technicians/addInventory.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Invalid request method.';
            return;
        }

        $itemName = trim((string) ($_POST['item_name'] ?? ''));
        $quantity = intval($_POST['quantity'] ?? 0);
        $reorderLevel = intval($_POST['reorder_level'] ?? 0);
        $supplierId = isset($_POST['supplier_id']) ? trim((string) $_POST['supplier_id']) : '';
        $categoryId = isset($_POST['category_id']) ? trim((string) $_POST['category_id']) : '';
        $unitOfMeasure = trim((string) ($_POST['unit_of_measure'] ?? 'Units'));
        $unitCost = isset($_POST['unit_cost']) ? trim((string) $_POST['unit_cost']) : '';
        $expiryDate = isset($_POST['expiry_date']) ? trim((string) $_POST['expiry_date']) : '';
        $batchNumber = isset($_POST['batch_number']) ? trim((string) $_POST['batch_number']) : '';

        $validationErrors = [];

        if ($itemName === '') {
            $validationErrors[] = 'Item name is required.';
        }
        if ($quantity < 0) {
            $validationErrors[] = 'Initial quantity must be zero or greater.';
        }
        if ($reorderLevel < 0) {
            $validationErrors[] = 'Reorder level must be zero or greater.';
        }
        if ($unitOfMeasure === '') {
            $validationErrors[] = 'Unit of measure is required.';
        }
        if ($unitCost !== '' && !is_numeric($unitCost)) {
            $validationErrors[] = 'Unit cost must be a valid number.';
        }
        if ($expiryDate !== '' && !$this->isValidIsoDate($expiryDate)) {
            $validationErrors[] = 'Expiry date must be in YYYY-MM-DD format.';
        }

        if (!empty($validationErrors)) {
            $_SESSION['inventory_create_error'] = implode(' ', $validationErrors);
            header('Location: /lab_sync/index.php?controller=inventoryController&action=add_inventory');
            return;
        }

        $inventoryModel = new inventoryModel();
        $success = $inventoryModel->addItem(
            $itemName,
            $quantity,
            $reorderLevel,
            $supplierId === '' ? null : intval($supplierId),
            $categoryId === '' ? null : intval($categoryId),
            $unitOfMeasure,
            $unitCost === '' ? null : $unitCost,
            $expiryDate === '' ? null : $expiryDate
        );

        if (!$success) {
            $errorMessage = $inventoryModel->getLastError();
            $_SESSION['inventory_create_error'] = $errorMessage !== '' ? $errorMessage : 'Unable to create inventory item.';
            header('Location: /lab_sync/index.php?controller=inventoryController&action=add_inventory');
            return;
        }

        $inventoryId = $inventoryModel->getLastInsertId();
        $historyNotes = 'Initial intake via inventory registration';
        if ($batchNumber !== '') {
            $historyNotes .= ' | Batch: ' . $batchNumber;
        }

        $inventoryModel->addStockHistoryEntry(
            $inventoryId,
            $quantity,
            $unitCost === '' ? null : $unitCost,
            $supplierId === '' ? null : intval($supplierId),
            $expiryDate === '' ? null : $expiryDate,
            $historyNotes
        );

        $_SESSION['inventory_create_success'] = 'Inventory item created successfully.';
        header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
    }

    public function searchSuppliers() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;

        $inventoryModel = new inventoryModel();
        $suppliers = $inventoryModel->searchSuppliers($search, $limit);
        $error = $inventoryModel->getLastError();

        if ($error !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $error
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $suppliers
        ]);
    }

    public function createSupplier() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $supplierName = trim((string) ($_POST['supplier_name'] ?? ''));
        $contactNo = trim((string) ($_POST['contact_no'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));

        if ($supplierName === '') {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Supplier name is required.'
            ]);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Please provide a valid email address.'
            ]);
            return;
        }

        $inventoryModel = new inventoryModel();
        $newId = $inventoryModel->createSupplier($supplierName, $contactNo, $location, $email);

        if ($newId === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Failed to create supplier.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier created successfully.',
            'data' => [
                'supplier_id' => $newId,
                'supplier_name' => $supplierName,
                'contact_no' => $contactNo,
                'location' => $location,
                'email' => $email
            ]
        ]);
    }
    public function edit_item() {
        $inventoryModel = new inventoryModel();
        // Logic to edit an existing inventory item
        if (isset($_POST['delete'])) {
            $itemId = $_POST['inventory_id'];
            // Logic to delete the item from the database
            $success=$inventoryModel->deleteItem($itemId);
            // Redirect back to inventory list after deletion
            if($success){
                header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
            } else {
                echo "Error deleting item.";
            }
            
        }elseif(isset($_POST['edit'])) {
            $itemId = $_POST['inventory_id'];
            $itemName = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $reorderLevel = $_POST['reorder_level'];
            $supplierId = $_POST['supplier_id'];
            // Logic to update the item details in the database
            $success=$inventoryModel->updateItem($itemId, $itemName, $quantity, $reorderLevel, $supplierId);
            // Redirect back to inventory list after update
            if($success){
                header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
            } else {
                echo "Error updating item.";
            }
        }
    }

    private function isValidIsoDate($dateValue) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
            return false;
        }

        $parts = explode('-', $dateValue);
        if (count($parts) !== 3) {
            return false;
        }

        return checkdate(intval($parts[1]), intval($parts[2]), intval($parts[0]));
    }

    // Add more methods as needed
}
?>