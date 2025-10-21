<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  
}

require_once MODEL_PATH . '/authModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class AuthController {
    private $error = '';
    private $db;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }
    public function login() {
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $model = new AuthModel($this->db);
            $username = $_POST['username'];
            $password = $_POST['password'];
            echo "Username: $username, Password: $password"; // Debug line
            $user = $model->verifyUser($username, $password);
            if ($user) {
                // Set session and redirect to dashboard
                // session_start();
                // $_SESSION['user'] = $user;
                // header('Location: /lab_sync/index.php?controller=dashboard&action=index');

                if($user['role'] == 'admin'|| $user['role'] == 'receptionist'|| $user['role'] == 'technician'){
                    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
                } elseif($user['role'] == 'patient'){
                    header('Location: /lab_sync/index.php?controller=home&action=index');
                }

                exit;
            } else {
                $error = "Invalid username or password";
            }
        }
        include VIEW_PATH . '/auth/dash_login.php';
    }
    
}


?>