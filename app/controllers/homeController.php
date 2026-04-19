<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/homeModel.php';
require_once MODEL_PATH . '/billingModel.php';
require_once MODEL_PATH . '/reportModel.php';
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
                        header('Location: ' . route_url('Auth', 'login'));
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

        $csrfToken = $this->ensureCsrfToken();

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
            header('Location: ' . route_url('Auth', 'index'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }

        $this->validateCsrfOrFail();

        $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);
        if ($patientId <= 0) {
            $_SESSION['error'] = 'Unable to identify patient profile.';
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }

        $notes = trim($_POST['notes'] ?? '');
        $preferredDate = trim($_POST['preferred_date'] ?? '');
        $preferredTime = trim($_POST['preferred_time'] ?? '');
        $homeCollection = !empty($_POST['home_collection']) ? 1 : 0;
        $collectionAddress = trim($_POST['collection_address'] ?? '');
        $relativePath = null;
        $fullPath = null;
        $requestType = 'HOME_VISIT_NO_PRESCRIPTION';

        if ($homeCollection && $collectionAddress === '') {
            $_SESSION['error'] = 'Please provide a collection address for home sample collection.';
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }

        $uploadError = isset($_FILES['prescription_file']) ? intval($_FILES['prescription_file']['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_OK) {
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            $originalName = $_FILES['prescription_file']['name'] ?? '';
            $tmpFile = $_FILES['prescription_file']['tmp_name'] ?? '';
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                $_SESSION['error'] = 'Allowed file types: PDF, JPG, JPEG, PNG.';
                header('Location: ' . route_url('home', 'get_help'));
                exit;
            }

            if ((int)$_FILES['prescription_file']['size'] > 5 * 1024 * 1024) {
                $_SESSION['error'] = 'File is too large. Maximum size is 5MB.';
                header('Location: ' . route_url('home', 'get_help'));
                exit;
            }

            $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'prescriptions';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                $_SESSION['error'] = 'Failed to create upload directory.';
                header('Location: ' . route_url('home', 'get_help'));
                exit;
            }

            $safeName = 'rx_' . $patientId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
            $relativePath = 'public/uploads/prescriptions/' . $safeName;

            if (!move_uploaded_file($tmpFile, $fullPath)) {
                $_SESSION['error'] = 'Failed to upload prescription. Please try again.';
                header('Location: ' . route_url('home', 'get_help'));
                exit;
            }

            $requestType = 'PRESCRIPTION';
        } elseif ($uploadError !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['error'] = 'Please upload a valid prescription file.';
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }

        if ($requestType !== 'PRESCRIPTION' && !$homeCollection) {
            $_SESSION['error'] = 'Upload a prescription file or select home sample collection.';
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }

        $saved = $this->model->createPrescriptionHelpRequest(
            $patientId,
            $relativePath,
            $notes,
            $preferredDate,
            $preferredTime,
            $homeCollection,
            $collectionAddress,
            $requestType
        );

        if (!$saved) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $_SESSION['error'] = $this->model->getLastError() ?: 'Failed to submit prescription request.';
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }

        $_SESSION['success'] = $requestType === 'PRESCRIPTION'
            ? 'Prescription submitted. Receptionist will contact you soon.'
            : 'Home visit request submitted. Receptionist will contact you soon.';
        header('Location: ' . route_url('home', 'get_help'));
        exit;
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
            $_SESSION['error'] = 'Security check failed. Please retry.';
            header('Location: ' . route_url('home', 'get_help'));
            exit;
        }
    }

    public function bookTest(){
        $selectedTestId = (int)($_GET['test'] ?? 0);
        $fromRequestId = (int)($_GET['from_request'] ?? 0);
        $preSelectedTestIds = [];
        if ($fromRequestId > 0) {
            $preSelectedTestIds = $this->model->getTestIdsForRequest($fromRequestId);
        }
        $tests = $this->model->getAllTests();
        $csrfToken = $this->ensureCsrfToken();
        include VIEW_PATH . '/patient/book.php';
    }
    public function bookAppointment() {
        // Start session and check if user is logged in
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . route_url('Auth', 'login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . route_url('home', 'index'));
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
            header('Location: ' . route_url('home', 'book'));
            exit;
        }

        if (count($testIds) === 0 || !$date || !$time || !$patientId) {
            $_SESSION['error'] = 'Please fill all required fields';
            header('Location: ' . route_url('home', 'book'));
            exit;
        }

        $slotAvailable = $this->model->isOnlineSlotCapacityAvailable($date, $time);
        if (!$slotAvailable) {
            $_SESSION['error'] = $this->model->getLastError() ?: 'Selected slot is no longer available. Please choose another slot.';
            header('Location: ' . route_url('home', 'book'));
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
            $fromRequestId = (int)($_POST['from_request'] ?? 0);
            if ($fromRequestId > 0) {
                $this->model->linkAppointmentToRequest($fromRequestId, (int)$result);
            }

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
            header('Location: ' . route_url('home', 'dashboard'));
        } else {
            $_SESSION['error'] = $this->model->getLastError() ?: 'Failed to book appointment';
            header('Location: ' . route_url('home', 'book'));
        }
        exit;
    }
    public function getAppointment(){
        $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);
        $appointments = $this->model->getAllAppointments($patientId);
        $prescriptionRequests = [];
        $requestTests = [];
        $patientBills = [];

        if ($patientId > 0) {
            $prescriptionRequests = $this->model->getPrescriptionRequestsByPatient($patientId, 20);
            $communicatedIds = [];
            foreach ($prescriptionRequests as $r) {
                if (strtolower(trim((string)($r['status'] ?? ''))) === 'communicated') {
                    $communicatedIds[] = (int)$r['request_id'];
                }
            }
            if (!empty($communicatedIds)) {
                $requestTests = $this->model->getTestsForRequests($communicatedIds);
            }
        }

        if ($patientId > 0) {
            $billingModel = new BillingModel(connect());
            $patientBills = $billingModel->getBillsByPatientId($patientId);
        }

        $patientReports = [];
        if ($patientId > 0) {
            $reportModel = new ReportModel(connect());
            $patientReports = $reportModel->getAuthorizedReportsByPatient($patientId);
        }

        $csrfToken = $this->ensureCsrfToken();
        include VIEW_PATH . '/patient/dashboard.php';
    }

    public function getAppointmentDetails() {
        header('Content-Type: text/html; charset=UTF-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo '<div class="appointment-details-error">Unauthorized request.</div>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo '<div class="appointment-details-error">Invalid request method.</div>';
            return;
        }

        $appointmentId = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
        if ($appointmentId <= 0) {
            http_response_code(400);
            echo '<div class="appointment-details-error">Invalid appointment ID.</div>';
            return;
        }

        $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);
        if ($patientId <= 0) {
            http_response_code(403);
            echo '<div class="appointment-details-error">Unable to identify patient profile.</div>';
            return;
        }

        $payload = $this->model->getPatientAppointmentDetailsPayload($appointmentId, $patientId);
        if ($payload === null) {
            http_response_code(404);
            echo '<div class="appointment-details-error">Appointment details not found.</div>';
            return;
        }

        $appointment = $payload['appointment'];
        $tests = $payload['tests'];
        $billing = $payload['billing'];
        include VIEW_PATH . '/patient/get_appointment_details.php';
    }

    public function edit_appointment() {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . route_url('Auth', 'login'));
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . route_url('home', 'dashboard'));
        exit();
    }

    $appointmentId = $_POST['appointment_id'] ?? '';
    $patientId = (int)$this->model->getPatientIdByUserId($_SESSION['user_id']);

    if ($patientId <= 0) {
        $_SESSION['error'] = 'Unable to identify patient profile.';
        header('Location: ' . route_url('home', 'dashboard'));
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
            header('Location: ' . route_url('home', 'dashboard'));
            exit();
        }

        if ($homeCollection && $collectionAddress === '') {
            $_SESSION['error'] = 'Please provide a collection address for home sample collection.';
            header('Location: ' . route_url('home', 'dashboard'));
            exit();
        }

        if (!$homeCollection) {
            $collectionAddress = '';
        }

        $result = $this->model->updateAppointment($appointmentId, $time, $date, $patientId, $homeCollection, $collectionAddress);
        if ($result) {
            $_SESSION['success'] = 'Appointment updated successfully';
        }
    }

    elseif (isset($_POST['delete'])) {
        if (!$appointmentId) {
            $_SESSION['error'] = 'Invalid appointment ID';
            header('Location: ' . route_url('home', 'dashboard'));
            exit();
        }

        $result = $this->model->deleteAppointment($appointmentId, $patientId);
        if ($result) {
            $_SESSION['success'] = 'Appointment cancelled successfully';
        }
    }

    if (!$result) {
        $_SESSION['error'] = $this->model->getLastError() ?: 'Failed to update appointment';
    }

    header('Location: ' . route_url('home', 'dashboard'));
    exit();
}

    public function getAvailableSlots() {
        header('Content-Type: application/json');
        $date = trim($_GET['date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            echo json_encode(['error' => 'Invalid date.']);
            return;
        }

        // Determine day group from date
        $dow = (int)date('N', strtotime($date)); // 1=Mon ... 7=Sun
        if ($dow >= 1 && $dow <= 5) {
            $dayGroup = 'mon_fri';
        } elseif ($dow === 6) {
            $dayGroup = 'sat';
        } else {
            $dayGroup = 'sun';
        }

        $slots = $this->model->getAvailableSlotsForDate($date, $dayGroup);

        // Format times as HH:MM for the frontend
        $formatted = [];
        foreach ($slots as $s) {
            $formatted[] = [
                'id'          => (int)$s['id'],
                'start_time'  => substr($s['start_time'], 0, 5),
                'end_time'    => substr($s['end_time'],   0, 5),
                'available'   => (bool)$s['available'],
            ];
        }
        echo json_encode($formatted);
    }

}

?>
