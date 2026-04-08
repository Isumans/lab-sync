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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_SESSION['email'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId || !$email) {
            header('Location: /lab_sync/index.php?controller=Auth&action=index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['pfName'] ?? '');
            $email1 = trim($_POST['pfEmail'] ?? '');
            $contact_number = trim($_POST['pfContact'] ?? '');
            $gender = trim($_POST['pfGender'] ?? '');
            $address = trim($_POST['pfAddress'] ?? '');

            if ($name === '' || $email1 === '') {
                $_SESSION['error'] = 'Name and email are required.';
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
                // Update session email if changed
                $_SESSION['email'] = $email1;
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

    public function changePassword() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
