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
        $sourceSupplierIds = isset($_POST['source_supplier_id']) && is_array($_POST['source_supplier_id']) ? $_POST['source_supplier_id'] : [];
        $sourceUnitCosts = isset($_POST['source_unit_cost']) && is_array($_POST['source_unit_cost']) ? $_POST['source_unit_cost'] : [];
        $primarySourceIndex = isset($_POST['primary_source_index']) ? trim((string) $_POST['primary_source_index']) : '';

        $validationErrors = [];
        $supplierSources = [];
        $seenSupplierIds = [];

        foreach ($sourceSupplierIds as $index => $sourceSupplierIdRaw) {
            $sourceSupplierId = intval($sourceSupplierIdRaw);
            $sourceUnitCostRaw = isset($sourceUnitCosts[$index]) ? trim((string) $sourceUnitCosts[$index]) : '';

            if ($sourceSupplierId <= 0 && $sourceUnitCostRaw === '') {
                continue;
            }

            if ($sourceSupplierId <= 0) {
                $validationErrors[] = 'Each supplier source row must include a supplier.';
                continue;
            }

            if (isset($seenSupplierIds[$sourceSupplierId])) {
                $validationErrors[] = 'Duplicate supplier source detected. Each supplier can only appear once.';
                continue;
            }
            $seenSupplierIds[$sourceSupplierId] = true;

            if ($sourceUnitCostRaw !== '' && !is_numeric($sourceUnitCostRaw)) {
                $validationErrors[] = 'Supplier source unit cost must be a valid number.';
                continue;
            }

            $supplierSources[] = [
                'supplier_id' => $sourceSupplierId,
                'unit_cost' => $sourceUnitCostRaw === '' ? null : floatval($sourceUnitCostRaw),
                'is_primary' => (string) $index === $primarySourceIndex ? 1 : 0,
            ];
        }

        if (empty($supplierSources) && $supplierId !== '') {
            $supplierSources[] = [
                'supplier_id' => intval($supplierId),
                'unit_cost' => $unitCost === '' ? null : floatval($unitCost),
                'is_primary' => 1,
            ];
        }

        if (!empty($supplierSources)) {
            $hasPrimary = false;
            foreach ($supplierSources as $entry) {
                if (!empty($entry['is_primary'])) {
                    $hasPrimary = true;
                    break;
                }
            }
            if (!$hasPrimary) {
                $supplierSources[0]['is_primary'] = 1;
            }
        }

        $primarySource = null;
        foreach ($supplierSources as $entry) {
            if (!empty($entry['is_primary'])) {
                $primarySource = $entry;
                break;
            }
        }

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
        if ($primarySource !== null && isset($primarySource['unit_cost']) && $primarySource['unit_cost'] !== null && !is_numeric((string) $primarySource['unit_cost'])) {
            $validationErrors[] = 'Primary supplier unit cost must be a valid number.';
        }

        if (!empty($validationErrors)) {
            $_SESSION['inventory_create_error'] = implode(' ', $validationErrors);
            header('Location: ' . route_url('inventoryController', 'add_inventory'));
            return;
        }

        $inventoryModel = new inventoryModel();
        $effectiveSupplierId = $supplierId === '' ? null : intval($supplierId);
        $effectiveUnitCost = $unitCost === '' ? null : $unitCost;

        if ($primarySource !== null) {
            $effectiveSupplierId = intval($primarySource['supplier_id']);
            $effectiveUnitCost = $primarySource['unit_cost'] === null ? null : (string) $primarySource['unit_cost'];
        }

        $success = $inventoryModel->addItem(
            $itemName,
            $quantity,
            $reorderLevel,
            $effectiveSupplierId,
            $categoryId === '' ? null : intval($categoryId),
            $unitOfMeasure,
            $effectiveUnitCost,
            $expiryDate === '' ? null : $expiryDate
        );

        if (!$success) {
            $errorMessage = $inventoryModel->getLastError();
            $_SESSION['inventory_create_error'] = $errorMessage !== '' ? $errorMessage : 'Unable to create inventory item.';
            header('Location: ' . route_url('inventoryController', 'add_inventory'));
            return;
        }

        $inventoryId = $inventoryModel->getLastInsertId();

        if (!empty($supplierSources)) {
            $savedSources = $inventoryModel->addInventorySupplierSources($inventoryId, $supplierSources);
            if (!$savedSources) {
                $_SESSION['inventory_create_error'] = $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Item created, but supplier sources were not saved.';
                header('Location: ' . route_url('inventoryController', 'add_inventory'));
                return;
            }
        }

        $historyNotes = 'Initial intake via inventory registration';
        if ($batchNumber !== '') {
            $historyNotes .= ' | Batch: ' . $batchNumber;
        }

        $intakeSupplierId = $supplierId === '' ? $effectiveSupplierId : intval($supplierId);
        $intakeUnitCost = $unitCost === '' ? $effectiveUnitCost : $unitCost;

        $inventoryModel->addStockHistoryEntry(
            $inventoryId,
            $quantity,
            $intakeUnitCost,
            $intakeSupplierId,
            $expiryDate === '' ? null : $expiryDate,
            $historyNotes
        );

        $_SESSION['inventory_create_success'] = 'Inventory item created successfully.';
        header('Location: ' . route_url('inventoryController', 'index'));
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

    public function getInventoryDetails() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo 'Invalid request method.';
            return;
        }

        $inventoryId = isset($_GET['inventory_id']) ? intval($_GET['inventory_id']) : 0;
        if ($inventoryId <= 0) {
            http_response_code(422);
            echo '<div class="inventory-details-error-state"><h3>Unable to load details</h3><p>Valid inventory ID is required.</p></div>';
            return;
        }

        $inventoryModel = new inventoryModel();
        $payload = $inventoryModel->getInventoryDetailsPayload($inventoryId);
        $error = $inventoryModel->getLastError();

        if ($error !== '') {
            http_response_code(500);
            echo '<div class="inventory-details-error-state"><h3>Unable to load details</h3><p>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p></div>';
            return;
        }

        if (!$payload || empty($payload['inventory'])) {
            http_response_code(404);
            echo '<div class="inventory-details-error-state"><h3>Not found</h3><p>Inventory item was not found.</p></div>';
            return;
        }

        $inventory = $payload['inventory'];
        $suppliers = isset($payload['suppliers']) && is_array($payload['suppliers']) ? $payload['suppliers'] : [];

        include VIEW_PATH . '/technicians/get_inventory_details.php';
    }

    public function getInventoryEditData() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $inventoryId = isset($_GET['inventory_id']) ? intval($_GET['inventory_id']) : 0;
        if ($inventoryId <= 0) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Valid inventory ID is required.'
            ]);
            return;
        }

        $inventoryModel = new inventoryModel();
        $payload = $inventoryModel->getInventoryDetailsPayload($inventoryId);
        $error = $inventoryModel->getLastError();

        if ($error !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $error
            ]);
            return;
        }

        if (!$payload || empty($payload['inventory'])) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Inventory item was not found.'
            ]);
            return;
        }

        $categories = $inventoryModel->getInventoryCategories();
        $categoriesError = $inventoryModel->getLastError();
        if ($categoriesError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $categoriesError
            ]);
            return;
        }

        $allSuppliers = $inventoryModel->searchSuppliers('', 500);
        $suppliersError = $inventoryModel->getLastError();
        if ($suppliersError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $suppliersError
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'inventory' => $payload['inventory'],
                'suppliers' => $payload['suppliers'],
                'categories' => $categories,
                'all_suppliers' => $allSuppliers,
            ]
        ]);
    }

    public function getSupplierEditData() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $supplierId = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;
        if ($supplierId <= 0) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Valid supplier ID is required.'
            ]);
            return;
        }

        $inventoryModel = new inventoryModel();
        $supplier = $inventoryModel->getSupplierById($supplierId);
        $error = $inventoryModel->getLastError();

        if ($error !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $error
            ]);
            return;
        }

        if (!$supplier) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Supplier not found.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $supplier
        ]);
    }

    public function updateSupplier() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $body = $this->getJsonRequestBody();
        $source = !empty($body) ? $body : $_POST;

        $supplierId = isset($source['supplier_id']) ? intval($source['supplier_id']) : 0;
        $supplierName = trim((string) ($source['supplier_name'] ?? ''));
        $contactNo = trim((string) ($source['contact_no'] ?? ''));
        $location = trim((string) ($source['location'] ?? ''));
        $email = trim((string) ($source['email'] ?? ''));

        if ($supplierId <= 0) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Valid supplier ID is required.'
            ]);
            return;
        }

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
        $updated = $inventoryModel->updateSupplier($supplierId, $supplierName, $contactNo, $location, $email);

        if (!$updated) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Failed to update supplier.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier updated successfully.',
            'data' => [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplierName,
                'contact_no' => $contactNo,
                'location' => $location,
                'email' => $email
            ]
        ]);
    }

    public function deleteSupplier() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $body = $this->getJsonRequestBody();
        $source = !empty($body) ? $body : $_POST;
        $supplierId = isset($source['supplier_id']) ? intval($source['supplier_id']) : 0;

        if ($supplierId <= 0) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Valid supplier ID is required.'
            ]);
            return;
        }

        $inventoryModel = new inventoryModel();
        $deleted = $inventoryModel->deleteSupplier($supplierId);

        if (!$deleted) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Failed to delete supplier.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Supplier deleted successfully.'
        ]);
    }

    public function edit_item() {
        $inventoryModel = new inventoryModel();
        $body = $this->getJsonRequestBody();

        if (!empty($body)) {
            header('Content-Type: application/json; charset=UTF-8');

            if (!empty($body['delete'])) {
                $itemId = isset($body['inventory_id']) ? intval($body['inventory_id']) : 0;
                if ($itemId <= 0) {
                    http_response_code(422);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Valid inventory ID is required.'
                    ]);
                    return;
                }

                $deleted = $inventoryModel->deleteItem($itemId, $this->getCurrentUserId());
                if (!$deleted) {
                    http_response_code(500);
                    echo json_encode([
                        'status' => 'error',
                        'message' => $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Failed to delete inventory item.'
                    ]);
                    return;
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Inventory item deleted successfully.'
                ]);
                return;
            }

            $itemId = isset($body['inventory_id']) ? intval($body['inventory_id']) : 0;
            $itemName = trim((string) ($body['item_name'] ?? ''));
            $quantity = isset($body['quantity']) ? intval($body['quantity']) : 0;
            $reorderLevel = isset($body['reorder_level']) ? intval($body['reorder_level']) : 0;
            $status = trim((string) ($body['status'] ?? 'In Stock'));
            $supplierIdRaw = isset($body['supplier_id']) ? trim((string) $body['supplier_id']) : '';
            $categoryIdRaw = isset($body['category_id']) ? trim((string) $body['category_id']) : '';
            $unitOfMeasure = trim((string) ($body['unit_of_measure'] ?? 'Units'));
            $unitCostRaw = isset($body['unit_cost']) ? trim((string) $body['unit_cost']) : '';
            $expiryDate = isset($body['expiry_date']) ? trim((string) $body['expiry_date']) : '';
            $supplierSourcesRaw = isset($body['supplier_sources']) && is_array($body['supplier_sources']) ? $body['supplier_sources'] : [];

            $validationErrors = [];

            if ($itemId <= 0) {
                $validationErrors[] = 'Valid inventory ID is required.';
            }
            if ($itemName === '') {
                $validationErrors[] = 'Item name is required.';
            }
            if ($quantity < 0) {
                $validationErrors[] = 'Quantity must be zero or greater.';
            }
            if ($reorderLevel < 0) {
                $validationErrors[] = 'Reorder level must be zero or greater.';
            }
            if ($unitOfMeasure === '') {
                $validationErrors[] = 'Unit of measure is required.';
            }
            if ($unitCostRaw !== '' && !is_numeric($unitCostRaw)) {
                $validationErrors[] = 'Item unit cost must be a valid number.';
            }
            if ($expiryDate !== '' && !$this->isValidIsoDate($expiryDate)) {
                $validationErrors[] = 'Expiry date must be in YYYY-MM-DD format.';
            }

            $supplierSources = [];
            $seenSupplierIds = [];
            foreach ($supplierSourcesRaw as $source) {
                if (!is_array($source)) {
                    continue;
                }

                $sourceSupplierId = isset($source['supplier_id']) ? intval($source['supplier_id']) : 0;
                $sourceUnitCostRaw = isset($source['unit_cost']) ? trim((string) $source['unit_cost']) : '';
                $sourceMinOrderRaw = isset($source['min_order_qty']) ? trim((string) $source['min_order_qty']) : '';
                $sourceLeadTimeRaw = isset($source['lead_time_days']) ? trim((string) $source['lead_time_days']) : '';
                $sourceIsPrimary = !empty($source['is_primary']) ? 1 : 0;

                if ($sourceSupplierId <= 0 && $sourceUnitCostRaw === '' && $sourceMinOrderRaw === '' && $sourceLeadTimeRaw === '') {
                    continue;
                }

                if ($sourceSupplierId <= 0) {
                    $validationErrors[] = 'Each supplier source row must include a supplier.';
                    continue;
                }

                if (isset($seenSupplierIds[$sourceSupplierId])) {
                    $validationErrors[] = 'Duplicate supplier source detected. Each supplier can only appear once.';
                    continue;
                }
                $seenSupplierIds[$sourceSupplierId] = true;

                if ($sourceUnitCostRaw !== '' && !is_numeric($sourceUnitCostRaw)) {
                    $validationErrors[] = 'Supplier source unit cost must be a valid number.';
                    continue;
                }
                if ($sourceMinOrderRaw !== '' && (!is_numeric($sourceMinOrderRaw) || intval($sourceMinOrderRaw) < 0)) {
                    $validationErrors[] = 'Min order quantity must be zero or greater.';
                    continue;
                }
                if ($sourceLeadTimeRaw !== '' && (!is_numeric($sourceLeadTimeRaw) || intval($sourceLeadTimeRaw) < 0)) {
                    $validationErrors[] = 'Lead time must be zero or greater.';
                    continue;
                }

                $supplierSources[] = [
                    'supplier_id' => $sourceSupplierId,
                    'unit_cost' => $sourceUnitCostRaw === '' ? null : floatval($sourceUnitCostRaw),
                    'min_order_qty' => $sourceMinOrderRaw === '' ? null : intval($sourceMinOrderRaw),
                    'lead_time_days' => $sourceLeadTimeRaw === '' ? null : intval($sourceLeadTimeRaw),
                    'is_primary' => $sourceIsPrimary,
                ];
            }

            if (empty($supplierSources) && $supplierIdRaw !== '') {
                $supplierSources[] = [
                    'supplier_id' => intval($supplierIdRaw),
                    'unit_cost' => $unitCostRaw === '' ? null : floatval($unitCostRaw),
                    'min_order_qty' => null,
                    'lead_time_days' => null,
                    'is_primary' => 1,
                ];
            }

            if (!empty($supplierSources)) {
                $hasPrimary = false;
                foreach ($supplierSources as $entry) {
                    if (!empty($entry['is_primary'])) {
                        $hasPrimary = true;
                        break;
                    }
                }
                if (!$hasPrimary) {
                    $supplierSources[0]['is_primary'] = 1;
                }
            }

            $primarySource = null;
            foreach ($supplierSources as $entry) {
                if (!empty($entry['is_primary'])) {
                    $primarySource = $entry;
                    break;
                }
            }

            $effectiveSupplierId = $supplierIdRaw === '' ? null : intval($supplierIdRaw);
            $effectiveUnitCost = $unitCostRaw === '' ? null : floatval($unitCostRaw);
            if ($primarySource !== null) {
                $effectiveSupplierId = intval($primarySource['supplier_id']);
                $effectiveUnitCost = $primarySource['unit_cost'];
            }

            if (!empty($validationErrors)) {
                http_response_code(422);
                echo json_encode([
                    'status' => 'error',
                    'message' => implode(' ', $validationErrors)
                ]);
                return;
            }

            $updated = $inventoryModel->updateItemFull(
                $itemId,
                $itemName,
                $quantity,
                $reorderLevel,
                $status,
                $effectiveSupplierId,
                $categoryIdRaw === '' ? null : intval($categoryIdRaw),
                $unitOfMeasure,
                $effectiveUnitCost,
                $expiryDate === '' ? null : $expiryDate
            );

            if (!$updated) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Failed to update inventory item.'
                ]);
                return;
            }

            $sourcesSaved = $inventoryModel->replaceInventorySupplierSources($itemId, $supplierSources);
            if (!$sourcesSaved) {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => $inventoryModel->getLastError() !== '' ? $inventoryModel->getLastError() : 'Failed to update supplier sources.'
                ]);
                return;
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Inventory item updated successfully.'
            ]);
            return;
        }

        // Logic to edit an existing inventory item
        if (isset($_POST['delete'])) {
            $itemId = $_POST['inventory_id'];
            // Logic to delete the item from the database
            $success=$inventoryModel->deleteItem($itemId, $this->getCurrentUserId());
            // Redirect back to inventory list after deletion
            if($success){
                header('Location: ' . route_url('inventoryController', 'index'));
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
                header('Location: ' . route_url('inventoryController', 'index'));
            } else {
                echo "Error updating item.";
            }
        }
    }

    private function getJsonRequestBody() {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower((string) $_SERVER['CONTENT_TYPE']) : '';
        if (strpos($contentType, 'application/json') === false) {
            return [];
        }

        $raw = file_get_contents('php://input');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
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

    private function getCurrentUserId() {

        return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    }

    // Add more methods as needed
}
?>