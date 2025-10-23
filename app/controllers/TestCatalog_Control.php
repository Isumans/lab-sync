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
    public function index() {
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

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $testName = $_POST['test-name'];
            $category = $_POST['test-category'];
            $price = $_POST['test-price'];
            $description = $_POST['test-description'];
            // $code = $_POST['test-code'];

            $conn1 = connect();
            $model2 = new TestCatalog($conn1);
            $success = $model2->addTest($testName, $category, $price, $description);
            if ($success) {
                return $this->index();
            } else {
                echo "Error adding test.";
            }
        }
    }
    public function edit_test() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $testId = $_POST['test_id'];
            $testName = $_POST['test_name'];
            $category = $_POST['category'];
            $price = $_POST['price'];

            $conn1 = connect();
            $model2 = new TestCatalog($conn1);
            if (isset($_POST['edit'])) {
                $success = $model2->updateTest($testId, $testName, $category, $price);
                if ($success) {
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index");
                } else {
                    echo "Error updating test.";
                }
            } elseif (isset($_POST['delete'])) {
                $success = $model2->deleteTest($testId);
                if ($success) {
                    header("Location: /lab_sync/index.php?controller=TestCatalog&action=index");
                    exit;

                } else {
                    echo "Error deleting test.";
                }
            }
        }
    }
}