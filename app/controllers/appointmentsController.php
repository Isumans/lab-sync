<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/patientModel.php';
require_once APP_PATH . '/services/EmailService.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
class appointmentsController {
    public function index($role = '') {
        // Logic to fetch and display appointments can be added here
        $appointmentsModel = new AppointmentModel(connect());
        $appointmentsOnline = $appointmentsModel->getAllAppointmentsbyMethod("online");
        $appointmentsPhysical = $appointmentsModel->getAllAppointmentsbyMethod("physical");
        include VIEW_PATH . '/receptionist/appointments.php';
    }

    public function storeAppointment($role = '') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientId = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
            $appointmentDate = $_POST['appointment_date'] ?? '';
            $appointmentTime = $_POST['appointment_time'] ?? '';
            $reason = $_POST['reason'] ?? '';
            $method = $_POST['method'] ?? 'physical';
            $selectedTestIds = $this->parseSelectedTestIds($_POST['selected_test_ids'] ?? '');

            if ($patientId <= 0) {
                echo "Error: patient_id is missing or invalid.";
                return;
            }

            if ($appointmentDate === '' || $appointmentTime === '') {
                echo "Error: appointment_date and appointment_time are required.";
                return;
            }

            if (empty($selectedTestIds)) {
                echo "Error: Please select at least one test.";
                return;
            }

            $conn = connect();
            $model = new AppointmentModel($conn);
            $success = $model->createAppointmentWithTests(
                $patientId,
                $appointmentDate,
                $appointmentTime,
                $reason,
                $method,
                $selectedTestIds
            );
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

        header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment');
        exit;
    }

    public function processPrescriptionDecision() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $decision = trim($_POST['decision'] ?? '');
        $decisionNote = trim($_POST['decision_note'] ?? '');

        if ($requestId <= 0 || $decision === '') {
            $_SESSION['error'] = 'Invalid decision request.';
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $appointmentsModel = new AppointmentModel(connect());

        if ($decision === 'book_for_patient') {
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment&request_id=' . $requestId);
            exit;
        }

        if ($decision === 'self_book') {
            $decisionBy = (int)($_SESSION['user_id'] ?? 0);
            $ok = $appointmentsModel->markPrescriptionRequestSelfBooking($requestId, $decisionNote, $decisionBy);
            if ($ok) {
                $_SESSION['success'] = 'Request marked as Self Booking Requested.';

                $request = $appointmentsModel->getPrescriptionRequestById($requestId);
                if ($request && !empty($request['email'])) {
                    $mailer = new EmailService();
                    $patientName = $request['patient_name'] ?? 'Patient';
                    $subject = 'Update on your prescription request - LabSync';
                    $selfBookLink = (defined('BASE_URL') ? BASE_URL : '/lab_sync') . '/index.php?controller=home&action=appointment_options';
                    $noteBlock = $decisionNote !== ''
                        ? '<p><strong>Receptionist note:</strong> ' . htmlspecialchars($decisionNote) . '</p>'
                        : '';
                    $html = '
                        <html>
                        <body style="font-family: Arial, sans-serif; color: #243046; line-height:1.6;">
                            <h3 style="margin-bottom: 8px;">LabSync Prescription Request Update</h3>
                            <p>Dear ' . htmlspecialchars((string)$patientName) . ',</p>
                            <p>Your prescription request has been reviewed. Please proceed with self-booking your tests using the Test Appointment option in your dashboard.</p>
                            ' . $noteBlock . '
                            <p>You can continue here: <a href="' . htmlspecialchars($selfBookLink) . '">Test Appointment</a></p>
                            <p>Thank you,<br>LabSync Team</p>
                        </body>
                        </html>
                    ';
                    $mailer->sendEmail($request['email'], $patientName, $subject, $html);
                }
            } else {
                $_SESSION['error'] = 'Unable to update request status. It may already be processed.';
            }

            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $_SESSION['error'] = 'Unknown decision action.';
        header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
        exit;
    private function parseSelectedTestIds($rawValue) {
        $parts = [];
        if (is_array($rawValue)) {
            $parts = $rawValue;
        } else {
            $parts = explode(',', (string) $rawValue);
        }
        $ids = [];

        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed === '' || !ctype_digit($trimmed)) {
                continue;
            }
            $ids[] = intval($trimmed);
        }

        return array_values(array_unique($ids));
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

public function createAppointment($role) {
    
    include VIEW_PATH . '/receptionist/create_appointment.php';
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

public function getAppointmentDetails() {
    header('Content-Type: text/html; charset=UTF-8');

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

    $model = new AppointmentModel(connect());
    $payload = $model->getAppointmentDetailsPayload($appointmentId);

    if ($payload === null) {
        http_response_code(404);
        echo '<div class="appointment-details-error">Appointment details not found.</div>';
        return;
    }

    $appointment = $payload['appointment'];
    $tests = $payload['tests'];
    $billing = $payload['billing'];
    include VIEW_PATH . '/receptionist/get_appointment_details.php';
}

public function getAppointmentEditData() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $appointmentId = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
    if ($appointmentId <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid appointment ID.'
        ]);
        return;
    }

    $model = new AppointmentModel(connect());
    $payload = $model->getAppointmentEditPayload($appointmentId);

    if ($payload === null) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Appointment not found.'
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $payload
    ]);
}

public function searchTests() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
    $model = new AppointmentModel(connect());
    $tests = $model->searchTestsCatalog($query, 20);

    echo json_encode([
        'status' => 'success',
        'data' => $tests
    ]);
}

public function updateAppointment() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }

    $appointmentId = isset($input['appointment_id']) ? intval($input['appointment_id']) : 0;
    $appointmentDate = isset($input['appointment_date']) ? trim((string) $input['appointment_date']) : '';
    $appointmentTime = isset($input['appointment_time']) ? trim((string) $input['appointment_time']) : '';
    $reason = isset($input['reason']) ? trim((string) $input['reason']) : '';
    $testIds = $this->parseSelectedTestIds($input['tests'] ?? []);

    if ($appointmentId <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid appointment ID.'
        ]);
        return;
    }

    if ($appointmentDate === '' || $appointmentTime === '') {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Appointment date and time are required.'
        ]);
        return;
    }

    if (empty($testIds)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Please select at least one test.'
        ]);
        return;
    }

    $model = new AppointmentModel(connect());
    $success = $model->updateAppointmentWithTests(
        $appointmentId,
        $appointmentDate,
        $appointmentTime,
        $reason,
        $testIds
    );

    if (!$success) {
        $errorMessage = $model->getLastError() ?: 'Failed to update appointment.';
        $statusCode = 500;

        if (
            stripos($errorMessage, 'can only be modified') !== false ||
            stripos($errorMessage, 'required') !== false ||
            stripos($errorMessage, 'invalid') !== false ||
            stripos($errorMessage, 'not found') !== false
        ) {
            $statusCode = 400;
        }

        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $errorMessage
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment updated successfully.'
    ]);
}
public function deleteAppointment() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }

    $appointmentId = isset($input['appointment_id']) ? intval($input['appointment_id']) : 0;

    if ($appointmentId <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid appointment ID.'
        ]);
        return;
    }

    $actorUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    $model = new AppointmentModel(connect());
    $success = $model->deleteAppointment($appointmentId, $actorUserId);

    if (!$success) {
        $errorMessage = $model->getLastError() ?: 'Failed to delete appointment.';
        $statusCode = 500;

        if (
            stripos($errorMessage, 'can only be modified') !== false ||
            stripos($errorMessage, 'not found') !== false ||
            stripos($errorMessage, 'already deleted') !== false ||
            stripos($errorMessage, 'authenticated user') !== false
        ) {
            $statusCode = 400;
        }

        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $errorMessage
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment deleted successfully.'
    ]);
}

public function updateTestStatus() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }

    $appointmentId = isset($input['appointment_id']) ? intval($input['appointment_id']) : 0;
    $testId = isset($input['test_id']) ? intval($input['test_id']) : 0;
    $actorUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    if ($appointmentId <= 0 || $testId <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid appointment_id or test_id.'
        ]);
        return;
    }

    $model = new AppointmentModel(connect());
    $result = $model->startTestInProgress($appointmentId, $testId, $actorUserId);
    if ($result === false) {
        $errorMessage = $model->getLastError() ?: 'Failed to update test status.';
        $statusCode = 500;

        if (
            stripos($errorMessage, 'invalid') !== false ||
            stripos($errorMessage, 'pending') !== false ||
            stripos($errorMessage, 'not found') !== false
        ) {
            $statusCode = 400;
        }

        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $errorMessage
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Test status updated to IN_PROGRESS.',
        'data' => $result
    ]);
}


}


?>