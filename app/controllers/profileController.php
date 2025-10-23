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

        $email = $_SESSION['email'] ?? null;
        if (!$email) {
            header('Location: /lab_sync/index.php?controller=Auth&action=index');
            exit();
        }

        $model = new ProfileModel($this->db);
        $patient = $model->getPatientByEmail($email);

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
            $name = $_POST['pfName'] ?? '';
            $email1 = $_POST['pfEmail'] ?? '';
            $contact_number = $_POST['pfContact'] ?? '';
            $gender = $_POST['pfGender'] ?? '';
            $address = $_POST['pfAddress'] ?? '';

            $model = new ProfileModel($this->db);

            // Get patient by session email
            $patient = $model->getPatientByEmail($email);
            $patientId = $patient['patient_id'] ?? null;
            if(!$patientId){
                header('Location: /lab_sync/index.php?controller=Auth&action=index');
                exit();
            }

            if ($patientId) {
                $model->updatePatient($patientId, $name, $email1, $contact_number, $gender, $address);
            }

            if ($model->updateUser($userId, $name, $email1, $contact_number)) {
                // Update session email if changed
                $_SESSION['email'] = $email1;

                header('Location: /lab_sync/index.php?controller=profile&action=view');
                exit();
            }
        }
    }
}
