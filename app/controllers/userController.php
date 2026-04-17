<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/userModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class userController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }

        $this->userModel = new userModel($this->db);
    }

    public function index($role) {
        $this->ensureStaffSession();

        $userId = intval($_SESSION['user_id'] ?? 0);
        $fallbackName = trim((string)($_SESSION['email'] ?? ''));

        $this->userModel->ensureSupportRows($userId, $fallbackName);
        $this->userModel->touchSession($_SESSION['session_token'] ?? '');

        $profileData = $this->userModel->getStaffProfileData($userId);
        if (!$profileData) {
            $this->setFlash('error', 'Unable to load profile information.');
            header('Location: ' . route_url('home', 'index'));
            exit();
        }

        $activeSessions = $this->userModel->listActiveSessions($userId);
        $csrfToken = $this->ensureCsrfToken();

        include VIEW_PATH . '/userProfile.php';
    }

    public function saveProfile() {
        $this->ensureStaffSession();
        $this->validateCsrfOrFail();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $payload = [
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            // Email is intentionally immutable in profile editing flows.
            'email' => trim((string)($_SESSION['email'] ?? '')),
            'contact_number' => trim((string)($_POST['contact_number'] ?? '')),
            'date_of_birth' => trim((string)($_POST['date_of_birth'] ?? '')),
            'gender' => trim((string)($_POST['gender'] ?? '')),
            'residential_address' => trim((string)($_POST['residential_address'] ?? '')),
        ];

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please provide a valid email address.');
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        if ($payload['gender'] !== '' && !in_array($payload['gender'], ['Male', 'Female', 'Other'], true)) {
            $payload['gender'] = '';
        }

        $saved = $this->userModel->updateStaffProfile(intval($_SESSION['user_id']), $payload);
        if ($saved) {
            $_SESSION['email'] = $payload['email'];
            $this->setFlash('success', 'Profile information updated successfully.');
        } else {
            $this->setFlash('error', 'Unable to save profile details.');
        }

        header('Location: ' . route_url('userController', 'user'));
        exit();
    }

    public function updatePreferences() {
        $this->ensureStaffSession();
        $this->validateCsrfOrFail();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $payload = [
            'email_notifications' => isset($_POST['email_notifications']) ? 1 : 0,
            'sms_alerts' => isset($_POST['sms_alerts']) ? 1 : 0,
            'quiet_hours_start' => trim((string)($_POST['quiet_hours_start'] ?? '')),
            'quiet_hours_end' => trim((string)($_POST['quiet_hours_end'] ?? '')),
            'theme_mode' => trim((string)($_POST['theme_mode'] ?? 'System')),
        ];

        $saved = $this->userModel->updateUserPreferences(intval($_SESSION['user_id']), $payload);
        if ($saved) {
            $this->setFlash('success', 'Preferences updated successfully.');
        } else {
            $this->setFlash('error', 'Unable to update preferences. Run the latest profile migration first.');
        }

        header('Location: ' . route_url('userController', 'user'));
        exit();
    }

    public function changePassword() {
        $this->ensureStaffSession();
        $this->validateCsrfOrFail();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $currentPassword = trim((string)($_POST['current_password'] ?? ''));
        $newPassword = trim((string)($_POST['new_password'] ?? ''));
        $confirmPassword = trim((string)($_POST['confirm_password'] ?? ''));

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $this->setFlash('error', 'Please complete all password fields.');
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        if (strlen($newPassword) < 8) {
            $this->setFlash('error', 'New password must contain at least 8 characters.');
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New password and confirmation do not match.');
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $result = $this->userModel->changePassword(intval($_SESSION['user_id']), $currentPassword, $newPassword);
        $this->setFlash($result['ok'] ? 'success' : 'error', $result['message']);

        header('Location: ' . route_url('userController', 'user'));
        exit();
    }

    public function toggleTwoFactor() {
        $this->ensureStaffSession();
        $this->validateCsrfOrFail();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $enable = isset($_POST['enable_2fa']) ? 1 : 0;
        $ok = $this->userModel->updateTwoFactor(intval($_SESSION['user_id']), $enable === 1);

        if ($ok) {
            $this->setFlash('success', $enable ? 'Two-factor authentication enabled.' : 'Two-factor authentication disabled.');
        } else {
            $this->setFlash('error', 'Unable to update 2FA settings. Run the latest profile migration first.');
        }

        header('Location: ' . route_url('userController', 'user'));
        exit();
    }

    public function revokeSession() {
        $this->ensureStaffSession();
        $this->validateCsrfOrFail();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $sessionId = intval($_POST['user_session_id'] ?? 0);
        if ($sessionId <= 0) {
            $this->setFlash('error', 'Invalid session selected.');
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }

        $ok = $this->userModel->revokeSession(
            intval($_SESSION['user_id']),
            $sessionId,
            (string)($_SESSION['session_token'] ?? '')
        );

        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Session revoked successfully.' : 'Unable to revoke that session.');
        header('Location: ' . route_url('userController', 'user'));
        exit();
    }

    private function ensureStaffSession() {

        $userId = intval($_SESSION['user_id'] ?? 0);
        $role = (string)($_SESSION['user_role'] ?? '');
        $allowedRoles = ['admin', 'receptionist', 'technician'];

        if ($userId <= 0 || !in_array($role, $allowedRoles, true)) {
            header('Location: ' . route_url('Auth', 'index'));
            exit();
        }
    }

    private function ensureCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        }

        return $_SESSION['csrf_token'];
    }

    private function validateCsrfOrFail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
        $requestToken = (string)($_POST['csrf_token'] ?? '');

        if ($sessionToken === '' || $requestToken === '' || !hash_equals($sessionToken, $requestToken)) {
            $this->setFlash('error', 'Security check failed. Please retry.');
            header('Location: ' . route_url('userController', 'user'));
            exit();
        }
    }

    private function setFlash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}