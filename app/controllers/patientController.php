<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/patientModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class patientController {
    public function index() {
        $conn1 = connect();
        $model = new patientModel($conn1);
        $patients = $model->getAllPatients();
        if($patients === false) {
            echo "Error fetching patients.";
            return;
        }else{
            // extract(['packages' => $packages]);
            $action = 'index';
            include VIEW_PATH . '/patients/patients.php';
        }
    }
        
    public function register_patient() {
        include VIEW_PATH . '/patients/register_patient.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patient_name = $_POST['patient_name'];
            $dob = $_POST['date_of_birth'];
            $gender = $_POST['gender'];
            $contact_no = $_POST['contact_no'];
            $email = $_POST['email'];

            $conn1 = connect();
            $model = new patientModel($conn1);
            $result = $model->registerPatient($patient_name, $dob, $gender, $contact_no, $email);
            if($result) {
                header('Location: /lab_sync/index.php?controller=patientController&action=index');
                echo "Patient registered successfully.";
            } else {
                echo "Error registering patient.";
            }
        }
    }
    public function edit_patient() {
        // Implementation for editing patient details
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $patient_id = $_POST['patient_id'];
            $patient_name = $_POST['patient_name'];
            $contact_number = $_POST['contact_number'];
            $email = $_POST['patient_email'];

            $conn1 = connect();
            $model = new patientModel($conn1);
            // Assuming you have an updatePatient method in your model
            if (isset($_POST['edit'])) {
                $success = $model->updatePatient($patient_id, $patient_name, $contact_number, $email);
                if ($success) {
                    header("Location: /lab_sync/index.php?controller=patientController&action=index");
                } else {
                    echo "Error updating patient.";
                }
            } elseif (isset($_POST['delete'])) {
                $success = $model->deletePatient($patient_id);
                if ($success) {
                    header("Location: /lab_sync/index.php?controller=patientController&action=index");
                    exit;

                } else {
                    echo "Error deleting patient.";
                }
            }
            
        }
    }
}