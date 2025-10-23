<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/homeModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class HomeController {
    private $model;
    private $db;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        $this->model = new HomeModel($this->db);
    }

    public function index() {
        $data = $this->model->getData();
        include VIEW_PATH . '/patient/home.php';
    }
     public function explore(){
        include VIEW_PATH . '/patient/explore.php';
     }
    public function dash(){
        include VIEW_PATH . '/patient/dashboard.php';
    }
    public function  signup(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = 'patient';

            // validate minimal inputs (optional, add more validation as needed)
            if ($username === '' || $email === '' || $password === '') {
                $error = 'Please fill all required fields.';
            } else {
                // prepare and check for existing user (use IF, not while)
                $checkUserStmt = $this->db->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
                if (!$checkUserStmt) {
                    // debug-friendly failure message — remove in production
                    die("Prepare failed: " . $this->db->error);
                }

                $checkUserStmt->bind_param("ss", $username, $email);
                $checkUserStmt->execute();
                $checkUserStmt->store_result();

                if ($checkUserStmt->num_rows > 0) {
                    $error = "Username or email already exists. Please choose another.";
                } else {
                    // register user: hash password and call model
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $user = $this->model->registerPatient($username, $email, $contact_number, $hashed, $role);

                    if ($user) {
                        header('Location: /lab_sync/index.php?controller=Auth&action=login');
                        exit;
                    } else {
                        $this->error = "Error during registration.";
                    }
                }

                $checkUserStmt->close();
            }
        }

        include VIEW_PATH . '/auth/patient_signup.php';
    }
    public function getTests(){

        $tests = $this->model->getAllTests();
        include VIEW_PATH . '/patient/explore.php';
    }
    public function bookTest(){
        $testName = $_GET['test'] ?? '';
        $tests = $this->model->getAllTests();
        include VIEW_PATH . '/patient/book.php';
    }
    public function bookAppointment() {
        // Start session and check if user is logged in
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /lab_sync/index.php?controller=Auth&action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php?controller=home&action=index');
            exit;
        }

        // Add debug logging
        error_log("Booking appointment - POST data: " . print_r($_POST, true));

        // Get and validate inputs
        $testId = $_POST['test_id'] ?? '';
        $date = $_POST['appointment_date'] ?? '';
        $time = $_POST['appointment_time'] ?? '';
        $patientId = $this->model->getPatientIdByUserId($_SESSION['user_id']);
        $method = 'Online';

        // Debug log the values
        error_log("testId: $testId, date: $date, time: $time, patientId: $patientId");

        if (!$testId || !$date || !$time || !$patientId) {
            $_SESSION['error'] = 'Please fill all required fields';
            error_log("Missing required fields - testId: $testId, date: $date, time: $time, patientId: $patientId");
            header('Location: /lab_sync/index.php?controller=home&action=book');
            exit;
        }

        // Book appointment with error checking
        $result = $this->model->createAppointment([
            'test_id' => $testId,
            'patient_id' => $patientId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'method' => $method
        ]);

        if ($result) {
            $_SESSION['success'] = 'Appointment booked successfully';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
        } else {
            error_log("Failed to create appointment - DB Error: " . $this->db->error);
            $_SESSION['error'] = 'Failed to book appointment';
            header('Location: /lab_sync/index.php?controller=home&action=book');
        }
        exit;
    }
    public function getAppointment(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $patientId = $this->model->getPatientIdByUserId($_SESSION['user_id']);
        $appointments = $this->model->getAllAppointments($patientId);
        include VIEW_PATH . '/patient/dashboard.php';
    }
    public function edit_appointment() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: /lab_sync/index.php?controller=Auth&action=login');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /lab_sync/index.php?controller=home&action=dashboard');
        exit();
    }

    $appointmentId = $_POST['appointment_id'] ?? '';
    $result = false;

    if (isset($_POST['edit'])) {
        $time = $_POST['time'] ?? '';
        $date = $_POST['date'] ?? '';

        if (!$time || !$date || !$appointmentId) {
            $_SESSION['error'] = 'Please fill all required fields';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
            exit();
        }

        $result = $this->model->updateAppointment($appointmentId, $time, $date);
        $_SESSION['success'] = 'Appointment updated successfully';
    }

    elseif (isset($_POST['delete'])) {
        if (!$appointmentId) {
            $_SESSION['error'] = 'Invalid appointment ID';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
            exit();
        }

        $result = $this->model->deleteAppointment($appointmentId);
        $_SESSION['success'] = 'Appointment deleted successfully';
    }

    if (!$result) {
        $_SESSION['error'] = 'Failed to update appointment';
    }

    header('Location: /lab_sync/index.php?controller=home&action=dashboard');
    exit();
}

}

?>