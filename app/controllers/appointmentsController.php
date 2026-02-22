<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/patientModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
class appointmentsController {
    public function index() {
        // Logic to fetch and display appointments can be added here
        $appointmentsModel = new AppointmentModel(connect());
        $appointmentsOnline = $appointmentsModel->getAllAppointmentsbyMethod("online");
        $appointmentsPhysical = $appointmentsModel->getAllAppointmentsbyMethod("physical");
        include VIEW_PATH . '/receptionist/appointments.php';
    }

    public function storeAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientId = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
            $appointmentDate = $_POST['appointment_date'] ?? '';
            $appointmentTime = $_POST['appointment_time'] ?? '';
            $reason = $_POST['reason'] ?? '';
            $method = $_POST['method'] ?? 'physical';

            if ($patientId <= 0) {
                echo "Error: patient_id is missing or invalid.";
                return;
            }

            $conn = connect();
            $model = new AppointmentModel($conn);
            $success = $model->createAppointment($patientId, $appointmentDate, $appointmentTime, $reason, $method);
            if ($success) {
                // Redirect back to appointments page to show saved appointment
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=index');
                exit();
            } else {
                $err = $model->getLastError();
                echo "Error creating appointment.";
                if ($err) {
                    echo " Details: " . htmlspecialchars($err);
                } elseif ($conn && $conn->error) {
                    echo " DB error: " . htmlspecialchars($conn->error);
                } else {
                    echo " (no DB error available).";
                }
            }
        }
    }
    public function searchPatients() {
    header('Content-Type: application/json');
    
    $type = $_GET['type'] ?? '';
    $query = $_GET['query'] ?? '';

    // require_once 'C:\xampp\htdocs\lab_sync\app\models\patientModel.php';
    $model1 = new patientModel(connect());

    $results = $model1->searchPatients($type, $query);
    echo json_encode($results);
}

public function filterAppointments() {
    header('Content-Type: application/json');
    
    $filter = $_GET['filter'] ?? 'all';
    $appointmentsModel = new AppointmentModel(connect());
    
    $appointments = [];
    
    if ($filter === 'online') {
        $appointments = $appointmentsModel->getAllAppointmentsbyMethod("online");
    } elseif ($filter === 'physical') {
        $appointments = $appointmentsModel->getAllAppointmentsbyMethod("physical");
    } else { // 'all'
        $online = $appointmentsModel->getAllAppointmentsbyMethod("online");
        $physical = $appointmentsModel->getAllAppointmentsbyMethod("physical");
        $appointments = array_merge($online, $physical);
    }
    
    echo json_encode($appointments);
}

}


?>