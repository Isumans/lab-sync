<?php
require_once 'C:\xampp\htdocs\lab_sync\app\models\authModel.php';
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
                session_start();
                $_SESSION['user'] = $user;
                header('Location: /lab_sync/index.php?controller=dashboard&action=index');
                exit;
            } else {
                $error = "Invalid username or password";
            }
        }
        include 'C:\xampp\htdocs\lab_sync\app\views\auth\dash_login.php';
    }
    
}


?>