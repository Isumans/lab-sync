<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/supplierModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class supplierController {
    private $db;
    private $supplierModel;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die('Database connection failed: ' . mysqli_connect_error());
        }

        $this->supplierModel = new supplierModel($this->db);
    }

    public function index($role = '') {
        $searchBy = $_GET['supplier-search-by'] ?? 'email';
        if (!in_array($searchBy, ['email', 'supplier_id'], true)) {
            $searchBy = 'email';
        }

        $searchQuery = trim($_GET['supplier-search-query'] ?? '');

        $suppliers = $this->supplierModel->getAllSuppliersWithItems($searchBy, $searchQuery);

        $totalSuppliers = count($suppliers);
        $totalItems = 0;
        foreach ($suppliers as $supplier) {
            $totalItems += (int) ($supplier['item_count'] ?? 0);
        }

        include VIEW_PATH . '/administrator/suppliers.php';
    }

    public function register($role = '') {
        include VIEW_PATH . '/administrator/regSupplier.php';
    }

    public function store($role = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('register', $role);
        }

        $name = trim($_POST['supplier_name'] ?? '');
        $email = trim($_POST['company_email'] ?? '');
        $contact = trim($_POST['phone_number'] ?? '');
        $items = $_POST['supplier_items'] ?? [];

        $_SESSION['supplier_old'] = [
            'supplier_name' => $name,
            'company_email' => $email,
            'phone_number' => $contact
        ];

        $validationError = $this->validateInput($name, $email, $contact, $items);
        if ($validationError !== null) {
            $_SESSION['supplier_error'] = $validationError;
            $this->redirect('register', $role);
        }

        if ($this->supplierModel->emailExists($email)) {
            $_SESSION['supplier_error'] = 'Email already exists for another supplier.';
            $this->redirect('register', $role);
        }

        $saved = $this->supplierModel->createSupplierWithItems($name, $email, $contact, $items);

        if ($saved) {
            unset($_SESSION['supplier_old']);
            $_SESSION['supplier_success'] = 'Supplier added successfully.';
            $this->redirect('index', $role);
        }

        $_SESSION['supplier_error'] = 'Unable to save supplier. Please try again.';
        $this->redirect('register', $role);
    }

    public function update($role = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index', $role);
        }

        $supplierId = (int) ($_POST['edit_supplier_id'] ?? 0);
        $name = trim($_POST['edit_supplier_name'] ?? '');
        $email = trim($_POST['edit_supplier_email'] ?? '');
        $contact = trim($_POST['edit_supplier_contact'] ?? '');
        $itemsRaw = trim($_POST['edit_supplier_items'] ?? '');
        $items = array_filter(array_map('trim', explode(',', $itemsRaw)), function ($item) {
            return $item !== '';
        });

        if ($supplierId <= 0) {
            $_SESSION['supplier_error'] = 'Invalid supplier selected.';
            $this->redirect('index', $role);
        }

        $validationError = $this->validateInput($name, $email, $contact, $items);
        if ($validationError !== null) {
            $_SESSION['supplier_error'] = $validationError;
            $this->redirect('index', $role);
        }

        if ($this->supplierModel->emailExists($email, $supplierId)) {
            $_SESSION['supplier_error'] = 'Email already exists for another supplier.';
            $this->redirect('index', $role);
        }

        $updated = $this->supplierModel->updateSupplierWithItems($supplierId, $name, $email, $contact, $items);

        if ($updated) {
            $_SESSION['supplier_success'] = 'Supplier updated successfully.';
            $this->redirect('index', $role);
        }

        $_SESSION['supplier_error'] = 'Unable to update supplier. Please try again.';
        $this->redirect('index', $role);
    }

    public function delete($role = '') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index', $role);
        }

        $supplierId = (int) ($_POST['supplier_id'] ?? 0);
        if ($supplierId <= 0) {
            $_SESSION['supplier_error'] = 'Invalid supplier selected.';
            $this->redirect('index', $role);
        }

        $deleted = $this->supplierModel->deleteSupplier($supplierId);

        if ($deleted) {
            $_SESSION['supplier_success'] = 'Supplier deleted successfully.';
        } else {
            $_SESSION['supplier_error'] = 'Unable to delete supplier.';
        }

        $this->redirect('index', $role);
    }

    private function validateInput($name, $email, $contact, $items) {
        if ($name === '' || $email === '' || $contact === '') {
            return 'Name, email, and contact number are required.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address.';
        }

        if (empty($items)) {
            return 'Please add at least one supplying item.';
        }

        return null;
    }

    private function redirect($action, $role = '') {
        $url = '/lab_sync/index.php?controller=supplierController&action=' . urlencode($action);
        if ($role !== '') {
            $url .= '&role=' . urlencode($role);
        }

        header('Location: ' . $url);
        exit();
    }
}
