<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/profileModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class ProfileController {
    private $db;

    public function __construct() {
        $this->db = connect();
    }

    public function viewProfile() {

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: /lab_sync/index.php?controller=Auth&action=index');
            exit();
        }

        $model = new ProfileModel($this->db);
        $patient = $model->getProfileByUserId($userId);

        include VIEW_PATH . '/patient/profile.php';
    }

    public function updateProfile() {

        $email = $_SESSION['email'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId || !$email) {
            header('Location: /lab_sync/index.php?controller=Auth&action=index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['pfName'] ?? '');
            // Patients are not allowed to change email from profile edit.
            $email1 = (string)$email;
            $contact_number = trim($_POST['pfContact'] ?? '');
            $gender = trim($_POST['pfGender'] ?? '');
            $address = trim($_POST['pfAddress'] ?? '');

            if ($name === '') {
                $_SESSION['error'] = 'Name is required.';
                header('Location: /lab_sync/index.php?controller=profile&action=view');
                exit();
            }

            $allowedGender = ['Male', 'Female', 'Other', ''];
            if (!in_array($gender, $allowedGender, true)) {
                $gender = '';
            }

            $model = new ProfileModel($this->db);

            // Get patient by session email
            $patient = $model->getPatientByEmail($email);
            $patientId = $patient['patient_id'] ?? null;

            if (!$patientId) {
                $profile = $model->getProfileByUserId((int)$userId);
                $patientId = $profile['patient_id'] ?? null;
            }

            if ($patientId) {
                $model->updatePatient($patientId, $name, $email1, $contact_number, $gender, $address);
            }

            if ($model->updateUser($userId, $name, $email1, $contact_number)) {
                $_SESSION['success'] = 'Profile updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update profile.';
            }

            header('Location: /lab_sync/index.php?controller=profile&action=view');
            exit();
        }

        header('Location: /lab_sync/index.php?controller=profile&action=view');
        exit();
    }

    public function uploadProfilePhoto() {
        header('Content-Type: application/json');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No file provided']);
            exit();
        }

        $file = $_FILES['photo'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File too large. Max 5MB']);
            exit();
        }

        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid file type']);
            exit();
        }

        if (!is_dir(__DIR__ . '/../../public/uploads/profiles')) {
            mkdir(__DIR__ . '/../../public/uploads/profiles', 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
        $filepath = __DIR__ . '/../../public/uploads/profiles/' . $filename;
        $publicPath = '/lab_sync/public/uploads/profiles/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Upload failed']);
            exit();
        }

        try {
            $model = new ProfileModel($this->db);
            $patient = $model->getProfileByUserId($userId);
            $patientId = $patient['patient_id'] ?? null;

            if ($patientId) {
                $model->updatePatientAvatar($patientId, $publicPath);
                echo json_encode(['success' => true, 'avatar_url' => $publicPath]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Patient not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function changePassword() {

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: /lab_sync/index.php?controller=Auth&action=index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php?controller=profile&action=view');
            exit();
        }

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $_SESSION['error'] = 'Please fill all password fields.';
            header('Location: /lab_sync/index.php?controller=profile&action=view');
            exit();
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'New password must be at least 8 characters.';
            header('Location: /lab_sync/index.php?controller=profile&action=view');
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Password confirmation does not match.';
            header('Location: /lab_sync/index.php?controller=profile&action=view');
            exit();
        }

        $model = new ProfileModel($this->db);
        $auth = $model->getUserAuthById($userId);
        $savedPassword = (string)($auth['password'] ?? '');

        $isValidCurrent = false;
        if ($savedPassword !== '') {
            if (password_verify($currentPassword, $savedPassword)) {
                $isValidCurrent = true;
            } elseif ($savedPassword === $currentPassword) {
                // Backward compatibility for legacy plain-text rows.
                $isValidCurrent = true;
            }
        }

        if (!$isValidCurrent) {
            $_SESSION['error'] = 'Current password is incorrect.';
            header('Location: /lab_sync/index.php?controller=profile&action=view');
            exit();
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        if ($model->updateUserPassword($userId, $newHash)) {
            $_SESSION['success'] = 'Password updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update password.';
        }

        header('Location: /lab_sync/index.php?controller=profile&action=view');
        exit();
    }
}
