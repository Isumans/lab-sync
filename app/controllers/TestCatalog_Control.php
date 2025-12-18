<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/TestCatalog_Model.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

// class TestCatalogController {
//     public function index() {
//         connect();
//         global $conn; 
//         $model = new TestCatalog($conn);
//         $packages = $model->getAllTests();
//         extract(['packages' => $packages]);
//         include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\test_catalog.php';
//     }
// }
class TestCatalogController {

    private $db;

     public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Verify authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /lab_sync/index.php?controller=Auth&action=login');
            exit;
        }
        $this->db = connect();
    }
    public function index($role) {
        $role=$_GET['role'] ?? '';
        $model = new TestCatalog($this->db);
        $packages = $model->getAllTests();
        if($packages === false) {
            echo "Error fetching tests.";
            return;
        }else{
            // extract(['packages' => $packages]);
            $action = 'index';
            include VIEW_PATH . '/receptionist/test_catalog.php';
        }
    }
        
    public function add_test($role) {
        $role=$_GET['role'] ?? '';
        include VIEW_PATH . '/receptionist/add_test.php';
    }

    public function store($role) {
        $role=$_GET['role'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $testName = trim($_POST['test-name'] ?? '');
            $category = trim($_POST['test-category'] ?? '');
            $price = trim($_POST['test-price'] ?? '');
            $description = trim($_POST['test-description'] ?? '');
            // $code = $_POST['test-code'];

            $errors = [];
            if ($testName === '') $errors[] = 'Test name is required.';
            if ($price === '' || !is_numeric($price)) $errors[] = 'Price is required and must be numeric.';
            if (count($errors) > 0) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
                header("Location: /lab_sync/index.php?controller=TestCatalog&action=add_test&role=" . urlencode($role));
                exit;
            }

            $conn1 = connect();
            $model2 = new TestCatalog($conn1);
            $success = $model2->addTest($testName, $category, (float)$price, $description);
            if ($success) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Test added successfully.'];
                header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                exit;
            } else {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error adding test.'];
                header("Location: /lab_sync/index.php?controller=TestCatalog&action=add_test&role=" . urlencode($role));
                exit;
            }
        }
    }
    public function edit_test($role) {
        $role = $_GET['role'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $testId = trim($_POST['test_id'] ?? '');
            $testName = trim($_POST['test_name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $price = trim($_POST['price'] ?? '');

            $conn1 = connect();
            $model2 = new TestCatalog($conn1);
            if (isset($_POST['edit'])) {
                $errors = [];
                if ($testId === '' || !ctype_digit((string)$testId)) $errors[] = 'Invalid test id.';
                if ($testName === '') $errors[] = 'Test name is required.';
                if ($price === '' || !is_numeric($price)) $errors[] = 'Price is required and must be numeric.';
                if (count($errors) > 0) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => implode(' ', $errors)];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }

                $success = $model2->updateTest((int)$testId, $testName, $category, (float)$price);
                if ($success) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Test updated successfully.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error updating test.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }
            } elseif (isset($_POST['delete'])) {
                if ($testId === '' || !ctype_digit((string)$testId)) {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid test id for deletion.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }
                $success = $model2->deleteTest((int)$testId);
                if ($success) {
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Test deleted successfully.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;

                } else {
                    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error deleting test.'];
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index&role=" . urlencode($role));
                    exit;
                }
            }
        }
    }
}