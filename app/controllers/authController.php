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
                $_SESSION['session_token'] = bin2hex(random_bytes(32));

                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                $deviceLabel = $this->buildDeviceLabel($userAgent);
                $model->startTrackedSession(
                    intval($_SESSION['user_id'] ?? 0),
                    session_id(),
                    $_SESSION['session_token'],
                    $deviceLabel,
                    $ipAddress,
                    $userAgent
                );

                $mustChangePassword = $model->isPasswordChangeRequired(intval($_SESSION['user_id'] ?? 0));
                $_SESSION['must_change_password'] = $mustChangePassword ? 1 : 0;
                if ($mustChangePassword) {
                    $_SESSION['password_change_prompt_dismissed'] = 0;
                } else {
                    unset($_SESSION['password_change_prompt_dismissed']);
                }

                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
                }
                
                // Redirect based on user role
                $role = $_SESSION['user_role'] ?? '';
                if ($role === 'admin' || $role === 'receptionist' || $role === 'technician') {
                    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
                } else {
                    // Patients land on the public home/index page first.
                    header('Location: /lab_sync/index.php');
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
        $sessionToken = $_SESSION['session_token'] ?? '';
        if ($sessionToken !== '') {
            $model = new AuthModel($this->db);
            $model->closeTrackedSession($sessionToken);
        }

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

    private function buildDeviceLabel($userAgent) {
        $ua = strtolower((string)$userAgent);

        $os = 'Unknown OS';
        if (strpos($ua, 'windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($ua, 'mac os') !== false || strpos($ua, 'macintosh') !== false) {
            $os = 'macOS';
        } elseif (strpos($ua, 'android') !== false) {
            $os = 'Android';
        } elseif (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'ios') !== false) {
            $os = 'iOS';
        } elseif (strpos($ua, 'linux') !== false) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (strpos($ua, 'edg/') !== false) {
            $browser = 'Edge';
        } elseif (strpos($ua, 'chrome/') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($ua, 'firefox/') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($ua, 'safari/') !== false && strpos($ua, 'chrome/') === false) {
            $browser = 'Safari';
        }

        return $os . ' - ' . $browser;
    }
}