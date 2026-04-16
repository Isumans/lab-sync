<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/homeModel.php';
require_once APP_PATH . '/services/EmailService.php';
require_once APP_PATH . '/services/SmsService.php';
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
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = 'patient';

            // validate minimal inputs (optional, add more validation as needed)
            if ($name === '' || $email === '' || $password === '') {
                $error = 'Please fill all required fields.';
            } else {
                // prepare and check for existing user (use IF, not while)
                $checkUserStmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
                if (!$checkUserStmt) {
                    // debug-friendly failure message — remove in production
                    die("Prepare failed: " . $this->db->error);
                }

                $checkUserStmt->bind_param("s", $email);
                $checkUserStmt->execute();
                $checkUserStmt->store_result();

                if ($checkUserStmt->num_rows > 0) {
                    $error = "Email already exists. Please choose another.";
                } else {
                    // register user: hash password and call model
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $user = $this->model->registerPatient($name, $email, $contact_number, $hashed, $role);

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

    public function appointmentOptions() {
        include VIEW_PATH . '/patient/appointment_options.php';
    }

    public function getHelp() {

        $requests = [];
        if (isset($_SESSION['user_id'])) {
            $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);
            if ($patientId > 0) {
                $requests = $this->model->getPrescriptionRequestsByPatient($patientId, 10);
            }
        }

        include VIEW_PATH . '/patient/get_help.php';
    }

    public function submitPrescriptionHelp() {

        if (!isset($_SESSION['user_id'])) {
            header('Location: /lab_sync/index.php?controller=Auth&action=index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);
        if ($patientId <= 0) {
            $_SESSION['error'] = 'Unable to identify patient profile.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        if (!isset($_FILES['prescription_file']) || $_FILES['prescription_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please upload a valid prescription file.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $originalName = $_FILES['prescription_file']['name'] ?? '';
        $tmpFile = $_FILES['prescription_file']['tmp_name'] ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $_SESSION['error'] = 'Allowed file types: PDF, JPG, JPEG, PNG.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        if ((int)$_FILES['prescription_file']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'File is too large. Maximum size is 5MB.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'prescriptions';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $_SESSION['error'] = 'Failed to create upload directory.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $safeName = 'rx_' . $patientId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
        $relativePath = 'public/uploads/prescriptions/' . $safeName;

        if (!move_uploaded_file($tmpFile, $fullPath)) {
            $_SESSION['error'] = 'Failed to upload prescription. Please try again.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $notes = trim($_POST['notes'] ?? '');
        $preferredDate = trim($_POST['preferred_date'] ?? '');
        $preferredTime = trim($_POST['preferred_time'] ?? '');
        $homeCollection = !empty($_POST['home_collection']) ? 1 : 0;
        $collectionAddress = trim($_POST['collection_address'] ?? '');

        if ($homeCollection && $collectionAddress === '') {
            $_SESSION['error'] = 'Please provide a collection address for home sample collection.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $saved = $this->model->createPrescriptionHelpRequest(
            $patientId,
            $relativePath,
            $notes,
            $preferredDate,
            $preferredTime,
            $homeCollection,
            $collectionAddress
        );

        if (!$saved) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $_SESSION['error'] = $this->model->getLastError() ?: 'Failed to submit prescription request.';
            header('Location: /lab_sync/index.php?controller=home&action=get_help');
            exit;
        }

        $_SESSION['success'] = 'Prescription submitted. Receptionist will contact you soon.';
        header('Location: /lab_sync/index.php?controller=home&action=get_help');
        exit;
    }

    public function bookTest(){
        $selectedTestId = (int)($_GET['test'] ?? 0);
        $tests = $this->model->getAllTests();
        include VIEW_PATH . '/patient/book.php';
    }
    public function bookAppointment() {
        // Start session and check if user is logged in
        
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
        $testIds = $_POST['test_ids'] ?? [];
        if (!is_array($testIds)) {
            $testIds = [];
        }

        // Backward compatibility with older single-test forms
        $singleTestId = (int)($_POST['test_id'] ?? 0);
        if ($singleTestId > 0 && !in_array($singleTestId, array_map('intval', $testIds), true)) {
            $testIds[] = $singleTestId;
        }

        $date = $_POST['appointment_date'] ?? '';
        $time = $_POST['appointment_time'] ?? '';
        $homeCollection = !empty($_POST['home_collection']) ? 1 : 0;
        $collectionAddress = trim($_POST['collection_address'] ?? '');
        $patientId = $this->model->getPatientIdByUserId($_SESSION['user_id']);
        $method = 'Online';

        if ($homeCollection && $collectionAddress === '') {
            $_SESSION['error'] = 'Please provide a collection address for home sample collection.';
            header('Location: /lab_sync/index.php?controller=home&action=book');
            exit;
        }

        if (count($testIds) === 0 || !$date || !$time || !$patientId) {
            $_SESSION['error'] = 'Please fill all required fields';
            header('Location: /lab_sync/index.php?controller=home&action=book');
            exit;
        }

        $hasConflict = $this->model->hasTimeSlotConflict($date, $time);
        if ($hasConflict) {
            $_SESSION['error'] = 'Selected slot is already taken. Please choose a different date or time.';
            header('Location: /lab_sync/index.php?controller=home&action=book');
            exit;
        }

        $result = $this->model->createOnlineAppointmentWithItems([
            'patient_id' => $patientId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'method' => $method,
            'status' => 'Pending',
            'booking_channel' => 'online_self',
            'home_collection' => $homeCollection,
            'collection_address' => $collectionAddress
        ], $testIds);

        if ($result) {
            $contact = $this->model->getPatientContactByUserId($_SESSION['user_id']);
            if ($contact) {
                $payload = $this->model->getAppointmentEmailPayload((int)$result);
                if ($payload) {
                    if (!empty($contact['email'])) {
                        $mailer = new EmailService();
                        $mailer->sendAppointmentBookedEmail(
                            $contact['email'],
                            $contact['patient_name'] ?? 'Patient',
                            $payload
                        );
                    }

                    if (!empty($contact['contact_number'])) {
                        $smsService = new SmsService();
                        $smsService->sendAppointmentBookedSms(
                            $contact['contact_number'],
                            $contact['patient_name'] ?? 'Patient',
                            $payload
                        );
                    }
                }
            }

            $_SESSION['success'] = 'Appointment booked successfully';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
        } else {
            $_SESSION['error'] = $this->model->getLastError() ?: 'Failed to book appointment';
            header('Location: /lab_sync/index.php?controller=home&action=book');
        }
        exit;
    }
    public function getAppointment(){
        $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);
        $appointments = $this->model->getAllAppointments($patientId);
        $prescriptionRequests = [];

        if ($patientId > 0) {
            $prescriptionRequests = $this->model->getPrescriptionRequestsByPatient($patientId, 20);
        }

        include VIEW_PATH . '/patient/dashboard.php';
    }
    public function edit_appointment() {

    if (!isset($_SESSION['user_id'])) {
        header('Location: /lab_sync/index.php?controller=Auth&action=login');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /lab_sync/index.php?controller=home&action=dashboard');
        exit();
    }

    $appointmentId = $_POST['appointment_id'] ?? '';
    $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);

    if ($patientId <= 0) {
        $_SESSION['error'] = 'Unable to identify patient profile.';
        header('Location: /lab_sync/index.php?controller=home&action=dashboard');
        exit();
    }

    $result = false;

    if (isset($_POST['edit'])) {
        $time = $_POST['time'] ?? '';
        $date = $_POST['date'] ?? '';
        $homeCollection = !empty($_POST['home_collection']) ? 1 : 0;
        $collectionAddress = trim($_POST['collection_address'] ?? '');

        if (!$time || !$date || !$appointmentId) {
            $_SESSION['error'] = 'Please fill all required fields';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
            exit();
        }

        if ($homeCollection && $collectionAddress === '') {
            $_SESSION['error'] = 'Please provide a collection address for home sample collection.';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
            exit();
        }

        if (!$homeCollection) {
            $collectionAddress = '';
        }

        $result = $this->model->updateAppointment($appointmentId, $time, $date, $patientId, $homeCollection, $collectionAddress);
        $_SESSION['success'] = 'Appointment updated successfully';
    }

    elseif (isset($_POST['delete'])) {
        if (!$appointmentId) {
            $_SESSION['error'] = 'Invalid appointment ID';
            header('Location: /lab_sync/index.php?controller=home&action=dashboard');
            exit();
        }

        $result = $this->model->deleteAppointment($appointmentId, $patientId);
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