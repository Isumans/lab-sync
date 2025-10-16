<?php
require_once 'C:\xampp\htdocs\lab_sync\app\models\TestCatalog_Model.php';
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
    public function index() {
        $conn1 = connect();
        $model = new TestCatalog($conn1);
        $packages = $model->getAllTests();
        if($packages === false) {
            echo "Error fetching tests.";
            return;
        }else{
            // extract(['packages' => $packages]);
            include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\test_catalog.php';
        }
    }
        
    public function add_test() {
        include 'C:\xampp\htdocs\lab_sync\app\views\receptionist\add_test.php';
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
}