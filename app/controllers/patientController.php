<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/patientModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class patientController {
    public function index($role) {
        $conn1 = connect();
        $model = new patientModel($conn1);
        $patients = $model->getAllPatients();
        if($patients === false) {
            echo "Error fetching patients.";
            return;
        }else{
            // extract(['packages' => $packages]);

            $action = 'index';
            $role = $_GET['role'] ?? '';
            include VIEW_PATH . '/patients/patients.php';
        }
    }
        
    public function register_patient($role) {
        $role=$_GET['role'] ?? '';
        include VIEW_PATH . '/patients/register_patient.php';
    }

    public function register($role) {
        $role = $_GET['role'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patient_name = trim((string)($_POST['patient_name'] ?? ''));
            $dob = trim((string)($_POST['date_of_birth'] ?? ''));
            $gender = strtolower(trim((string)($_POST['gender'] ?? '')));
            $contact_no = trim((string)($_POST['contact_no'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));

            $validation = $this->validatePatientRegistration($patient_name, $dob, $gender, $contact_no, $email);
            if (!$validation['ok']) {
                http_response_code(400);
                echo $validation['message'];
                return;
            }

            $conn1 = connect();
            $model = new patientModel($conn1);
            $result = $model->registerPatient($patient_name, $dob, $gender, $contact_no, $email);
            if($result) {
                header('Location: /lab_sync/index.php?controller=patientController&action=index&role=' . urlencode($role));
                exit;
            } else {
                echo "Error registering patient.";
            }
        }
    }
    public function edit_patient($role) {
        $role = $_GET['role'] ?? '';
        // Implementation for editing patient details
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $patient_id = intval($_POST['patient_id'] ?? 0);
            $patient_name = trim((string)($_POST['patient_name'] ?? ''));
            $contact_number = trim((string)($_POST['contact_number'] ?? ''));
            $email = trim((string)($_POST['patient_email'] ?? ''));

            if ($patient_id <= 0) {
                http_response_code(400);
                echo "Invalid patient reference.";
                return;
            }

            $conn1 = connect();
            $model = new patientModel($conn1);
            // Assuming you have an updatePatient method in your model
            if (isset($_POST['edit'])) {
                $validation = $this->validatePatientUpdate($patient_name, $contact_number, $email);
                if (!$validation['ok']) {
                    http_response_code(400);
                    echo $validation['message'];
                    return;
                }

                $success = $model->updatePatient($patient_id, $patient_name, $contact_number, $email);
                if ($success) {
                    header("Location: /lab_sync/index.php?controller=patientController&action=index&role=" . urlencode($role));
                    exit;
                } else {
                    echo "Error updating patient.";
                }
            } elseif (isset($_POST['delete'])) {
                $success = $model->deletePatient($patient_id);
                if ($success) {
                    header("Location: /lab_sync/index.php?controller=patientController&action=index&role=" . urlencode($role));
                    exit;

                } else {
                    echo "Error deleting patient.";
                }
            }
            
        }
    }

    private function validatePatientRegistration($patientName, $dob, $gender, $contactNo, $email) {
        if ($patientName === '' || strlen($patientName) > 120) {
            return ['ok' => false, 'message' => 'Patient name is required and must be at most 120 characters.'];
        }

        if (!$this->isValidDate($dob)) {
            return ['ok' => false, 'message' => 'Date of birth format is invalid.'];
        }

        if (strtotime($dob) > time()) {
            return ['ok' => false, 'message' => 'Date of birth cannot be in the future.'];
        }

        if (!in_array($gender, ['male', 'female', 'other'], true)) {
            return ['ok' => false, 'message' => 'Gender value is invalid.'];
        }

        if (!$this->isValidPhone($contactNo)) {
            return ['ok' => false, 'message' => 'Contact number format is invalid.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
            return ['ok' => false, 'message' => 'Email is invalid.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    private function validatePatientUpdate($patientName, $contactNo, $email) {
        if ($patientName === '' || strlen($patientName) > 120) {
            return ['ok' => false, 'message' => 'Patient name is required and must be at most 120 characters.'];
        }

        if (!$this->isValidPhone($contactNo)) {
            return ['ok' => false, 'message' => 'Contact number format is invalid.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
            return ['ok' => false, 'message' => 'Email is invalid.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    private function isValidDate($value) {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function isValidPhone($value) {
        return preg_match('/^[0-9+()\-\s]{7,25}$/', $value) === 1;
    }
}