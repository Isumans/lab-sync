<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/billingModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class BillingController {
    private $db;
    private $billingModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verify authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /lab_sync/index.php?controller=Auth&action=login');
            exit;
        }
        
        $this->db = connect();
        $this->billingModel = new BillingModel($this->db);
    }

    public function index() {
        // Display billing list
        $bills = $this->billingModel->getAllBills();
        include VIEW_PATH . '/receptionist/billing.php';
    }

    public function create_bill() {
        // Get all tests and doctors for the form
        $tests = $this->billingModel->getAllTests();
        $doctors = $this->billingModel->getAllDoctors();
        
        // Generate invoice number
        $invoiceNumber = $this->billingModel->generateInvoiceNumber();
        
        include VIEW_PATH . '/receptionist/createBill.php';
    }

    public function store_bill() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $billingData = [
                'patient_name' => trim($_POST['patient_name'] ?? ''),
                'patient_age_gender' => trim($_POST['patient_age_gender'] ?? ''),
                'patient_phone' => trim($_POST['patient_phone'] ?? ''),
                'referring_doctor' => trim($_POST['referring_doctor'] ?? ''),
                'billing_notes' => trim($_POST['billing_notes'] ?? ''),
                'service_fee' => floatval($_POST['service_fee'] ?? 0),
                'discount_code' => trim($_POST['discount_code'] ?? ''),
                'tests' => $_POST['tests'] ?? []
            ];

            $result = $this->billingModel->createBill($billingData);
            
            if ($result) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Bill created successfully!'
                ];
                header('Location: /lab_sync/index.php?controller=billingController&action=index');
                exit;
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Failed to create bill. Please try again.'
                ];
            }
        }
        
        header('Location: /lab_sync/index.php?controller=billingController&action=index');
        exit;
    }
}
?>
