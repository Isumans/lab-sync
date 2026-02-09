<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  
}

require_once MODEL_PATH . '/authModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class AuthController {
    private $db;
    public function __construct() {
        $this->db = connect();
    }

    public function login() {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $model = new AuthModel($this->db);
            $user = $model->verifyUser($email, $password);
            if ($user) {
                // ensure session started (bootstrap should start it)
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'] ?? $user['id'] ?? null;
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['user_role'] = $user['role'] ?? '';
                
                // Redirect based on user role
                $role = $_SESSION['user_role'] ?? '';
                if ($role === 'admin' || $role === 'receptionist' || $role === 'technician') {
                    header('Location: /lab_sync/index.php?controller=home&action=index');
                } else {
                    // Default to patient dashboard
                    header('Location: /lab_sync/index.php?controller=home&action=dashboard');
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
        include VIEW_PATH . '/auth/dash_login.php';
    }

    public function logout() {
        // logout action
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: /lab_sync/index.php');
        exit;
    }
}