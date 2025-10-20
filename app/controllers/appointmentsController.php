<?php
require_once 'C:\xampp\htdocs\lab_sync\app\models\appointmentModel.php';
require_once 'C:\xampp\htdocs\lab_sync\app\models\patientModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
class appointmentsController {
    public function index() {
        // Logic to fetch and display appointments can be added here
    }

    public function storeAppointment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientId = $_POST['patient_id'];
            $appointmentDate = $_POST['appointment_date'];
            $appointmentTime = $_POST['appointment_time'];
            $reason = $_POST['reason'];

            $conn = connect();
            $model = new AppointmentModel($conn);
            $success = $model->createAppointment($patientId, $appointmentDate, $appointmentTime, $reason);
            if ($success) {
                echo "Appointment created successfully.";
                // Optionally redirect or load the appointments view
            } else {
                echo "Error creating appointment.";
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

}


?>