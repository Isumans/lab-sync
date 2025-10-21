<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/homeModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class HomeController {
    private $model;
    private $db;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        $this->model = new HomeModel($this->db);
    }

    public function index() {
        $data = $this->model->getData();
        include VIEW_PATH . '/patient/home.php';
    }
     public function explore(){
        include VIEW_PATH . '/patient/explore.php';
     }
    public function dash(){
        include VIEW_PATH . '/patient/dashboard.php';
    }
    public function  signup(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $contact_number = $_POST['contact_number'];
            $password = $_POST['password'];
            $role = 'patient';

            $user=$this->model->registerPatient($username, $email, $contact_number, $password, role: $role);
            if($user){
                // Redirect to login page after successful registration
                header('Location: /lab_sync/index.php?controller=Auth&action=login');
                exit;
            } else{
                echo "Error during registration.";
            }
            
        }
        include VIEW_PATH . '/auth/patient_signup.php';
    }
}
?>