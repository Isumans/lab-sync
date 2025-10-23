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
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $model = new AuthModel($this->db);
            $user = $model->verifyUser($username, $password);
            if ($user) {
                // ensure session started (bootstrap should start it)
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'] ?? $user['id'] ?? null;
                $_SESSION['username'] = $user['username'] ?? $user['name'] ?? '';
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['user_role'] = $user['role'] ?? '';
                header('Location: /lab_sync/index.php?controller=home&action=index');
                exit;
            } else {
                $error = 'Invalid credentials';
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