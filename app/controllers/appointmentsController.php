<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/patientModel.php';
require_once APP_PATH . '/services/EmailService.php';
require_once APP_PATH . '/services/SmsService.php';
require_once __DIR__ . '/../../config/db.php';
class appointmentsController {
    public function index($role = '') {
        // Logic to fetch and display appointments can be added here
        $appointmentsModel = new AppointmentModel(connect());
        $appointmentsOnline = $appointmentsModel->getAllAppointmentsByMethod("online");
        $appointmentsPhysical = $appointmentsModel->getAllAppointmentsByMethod("physical");
        $csrfToken = $this->ensureCsrfToken();
        include VIEW_PATH . '/receptionist/appointments.php';
    }

    public function storeAppointment($role = '') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail(false, 'appointmentsController', 'index');

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
                header('Location: ' . route_url('appointmentsController', 'index'));
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

public function createAppointment($role = '') {
    
    include VIEW_PATH . '/receptionist/create_appointment.php';
}

public function filterAppointments() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : 7;

    $method = isset($_GET['method']) ? strtolower(trim((string) $_GET['method'])) : '';
    if ($method === '' && isset($_GET['filter'])) {
        $method = strtolower(trim((string) $_GET['filter']));
    }
    if (!in_array($method, ['all', 'online', 'physical', 'call', 'home_visit'], true)) {
        $method = 'all';
    }

    $sortBy = isset($_GET['sort_by']) ? trim((string) $_GET['sort_by']) : 'appointment_date';
    $sortDir = isset($_GET['sort_dir']) ? trim((string) $_GET['sort_dir']) : 'desc';

    $filters = [
        'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
        'method' => $method,
        'from_date' => isset($_GET['from_date']) ? trim((string) $_GET['from_date']) : '',
        'to_date' => isset($_GET['to_date']) ? trim((string) $_GET['to_date']) : '',
    ];

    $appointmentsModel = new AppointmentModel(connect());
    $appointments = $appointmentsModel->getAppointmentsList($filters, $page, $perPage, $sortBy, $sortDir);
    $totalItems = $appointmentsModel->countAppointments($filters);
    $totalPages = max(1, (int) ceil($totalItems / $perPage));

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointments loaded successfully.',
        'data' => $appointments,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $totalItems,
            'total_pages' => $totalPages,
        ]
    ]);
}

public function filterPrescriptionRequests() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : 7;

    $requestType = isset($_GET['request_type']) ? strtolower(trim((string) $_GET['request_type'])) : 'all';
    if (!in_array($requestType, ['all', 'home_visit', 'onsite'], true)) {
        $requestType = 'all';
    }

    $sortBy = isset($_GET['sort_by']) ? trim((string) $_GET['sort_by']) : 'created_at';
    $sortDir = isset($_GET['sort_dir']) ? trim((string) $_GET['sort_dir']) : 'desc';

    $filters = [
        'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
        'request_type' => $requestType,
        'from_date' => isset($_GET['from_date']) ? trim((string) $_GET['from_date']) : '',
        'to_date' => isset($_GET['to_date']) ? trim((string) $_GET['to_date']) : '',
    ];

    $appointmentsModel = new AppointmentModel(connect());
    $rows = $appointmentsModel->getPrescriptionRequestsList($filters, $page, $perPage, $sortBy, $sortDir);
    $totalItems = $appointmentsModel->countPrescriptionRequests($filters);
    $totalPages = max(1, (int) ceil($totalItems / $perPage));

    echo json_encode([
        'status' => 'success',
        'message' => 'Prescription requests loaded successfully.',
        'data' => $rows,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $totalItems,
            'total_pages' => $totalPages,
        ]
    ]);
}

public function getPrescriptionRequestManageData() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $requestId = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
    if ($requestId <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request ID.'
        ]);
        return;
    }

    $model = new AppointmentModel(connect());
    $payload = $model->getPrescriptionRequestManagePayload($requestId);
    if ($payload === null) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Request not found.'
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Prescription request details loaded successfully.',
        'data' => $payload
    ]);
}

public function savePrescriptionRequestManagement() {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method.'
        ]);
        return;
    }

    $this->validateCsrfOrFail(true);

    $input = $_POST;
    if (empty($input)) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }

    $requestId = isset($input['request_id']) ? intval($input['request_id']) : 0;
    $preferredDate = isset($input['preferred_date']) ? trim((string) $input['preferred_date']) : '';
    $preferredTime = isset($input['preferred_time']) ? trim((string) $input['preferred_time']) : '';
    $collectionAddress = isset($input['collection_address']) ? trim((string) $input['collection_address']) : '';
    $note = isset($input['note']) ? trim((string) $input['note']) : '';
    $testIds = $this->parseSelectedTestIds($input['tests'] ?? []);
    $actorUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($requestId <= 0) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request ID.'
        ]);
        return;
    }

    if (empty($testIds)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Please select at least one test before saving.'
        ]);
        return;
    }

    $model = new AppointmentModel(connect());
    $ok = $model->savePrescriptionRequestManagement($requestId, [
        'preferred_date' => $preferredDate,
        'preferred_time' => $preferredTime,
        'collection_address' => $collectionAddress,
        'note' => $note,
        'tests' => $testIds,
    ], $actorUserId);

    if (!$ok) {
        $message = $model->getLastError() ?: 'Failed to save request management data.';
        $statusCode = 500;
        if (stripos($message, 'required') !== false || stripos($message, 'invalid') !== false || stripos($message, 'not found') !== false) {
            $statusCode = 400;
        }
        http_response_code($statusCode);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        return;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Request updated and sent to patient successfully.'
    ]);
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
        'message' => 'Appointment edit data loaded successfully.',
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
        'message' => 'Tests loaded successfully.',
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

    $this->validateCsrfOrFail(true);

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

    $this->validateCsrfOrFail(true);

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

    $this->validateCsrfOrFail(true);

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

public function prescriptionQueue() {
    $statusFilter = isset($_GET['status']) ? strtolower(trim((string) $_GET['status'])) : 'pending';
    $model = new AppointmentModel(connect());
    $csrfToken = $this->ensureCsrfToken();

    if ($statusFilter === 'all') {
        $requests = $model->getPrescriptionRequests('all');
    } elseif ($statusFilter === 'processed') {
        $all = $model->getPrescriptionRequests('all');
        $requests = array_values(array_filter($all, function ($row) {
            return strtolower((string) ($row['status'] ?? '')) !== 'pending';
        }));
    } else {
        $requests = $model->getPrescriptionRequests('pending');
    }

    include VIEW_PATH . '/receptionist/prescription_queue.php';
}

public function prescriptionDecisionReport() {
    $model = new AppointmentModel(connect());
    $rows = $model->getPrescriptionRequests('all');

    $filters = [
        'status' => trim((string) ($_GET['status'] ?? '')),
        'decision_action' => trim((string) ($_GET['decision_action'] ?? '')),
        'date_from' => trim((string) ($_GET['date_from'] ?? '')),
        'date_to' => trim((string) ($_GET['date_to'] ?? '')),
        'decision_by_user_id' => intval($_GET['decision_by_user_id'] ?? 0),
    ];

    $reportRows = array_values(array_filter($rows, function ($row) use ($filters) {
        if ($filters['status'] !== '' && stripos((string) ($row['status'] ?? ''), $filters['status']) === false) {
            return false;
        }

        if ($filters['decision_action'] !== '' && strtolower((string) ($row['decision_action'] ?? '')) !== strtolower($filters['decision_action'])) {
            return false;
        }

        if ($filters['decision_by_user_id'] > 0 && intval($row['decision_by_user_id'] ?? 0) !== $filters['decision_by_user_id']) {
            return false;
        }

        $decisionAt = (string) ($row['decision_at'] ?? '');
        if ($filters['date_from'] !== '' && ($decisionAt === '' || substr($decisionAt, 0, 10) < $filters['date_from'])) {
            return false;
        }

        if ($filters['date_to'] !== '' && ($decisionAt === '' || substr($decisionAt, 0, 10) > $filters['date_to'])) {
            return false;
        }

        return true;
    }));

    $summary = [
        'total_requests' => count($reportRows),
        'pending' => 0,
        'processed' => 0,
        'booked_by_receptionist' => 0,
        'self_book_requested' => 0,
    ];

    foreach ($reportRows as $row) {
        $status = strtolower((string) ($row['status'] ?? ''));
        $decision = strtolower((string) ($row['decision_action'] ?? ''));

        if ($status === 'pending') {
            $summary['pending'] += 1;
        } else {
            $summary['processed'] += 1;
        }

        if ($decision === 'book_for_patient') {
            $summary['booked_by_receptionist'] += 1;
        }

        if ($decision === 'self_book') {
            $summary['self_book_requested'] += 1;
        }
    }

    if (isset($_GET['format']) && strtolower((string) $_GET['format']) === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="prescription_decisions_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['request_id', 'patient_name', 'status', 'decision_action', 'decision_by_user_id', 'decision_by_username', 'linked_appointment_id', 'decision_at', 'created_at']);
        foreach ($reportRows as $row) {
            fputcsv($out, [
                $row['request_id'] ?? '',
                $row['patient_name'] ?? '',
                $row['status'] ?? '',
                $row['decision_action'] ?? '',
                $row['decision_by_user_id'] ?? '',
                $row['decision_by_username'] ?? '',
                $row['linked_appointment_id'] ?? '',
                $row['decision_at'] ?? '',
                $row['created_at'] ?? '',
            ]);
        }
        fclose($out);
        return;
    }

    include VIEW_PATH . '/receptionist/prescription_decisions.php';
}

public function prescriptionRequestDetails() {
    $requestId = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
    if ($requestId <= 0) {
        http_response_code(400);
        echo 'Invalid request_id';
        return;
    }

    $model = new AppointmentModel(connect());
    $requests = $model->getPrescriptionRequests('all');
    $request = null;
    foreach ($requests as $row) {
        if (intval($row['request_id'] ?? 0) === $requestId) {
            $request = $row;
            break;
        }
    }

    if ($request === null) {
        http_response_code(404);
        echo 'Prescription request not found.';
        return;
    }

    $events = $model->getPrescriptionRequestEvents($requestId);
    include VIEW_PATH . '/receptionist/prescription_request_details.php';
}

public function processPrescriptionDecision() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Invalid request method.';
        return;
    }

    $this->validateCsrfOrFail(false, 'appointmentsController', 'prescriptionQueue');

    $requestId = intval($_POST['request_id'] ?? 0);
    $decision = strtolower(trim((string) ($_POST['decision'] ?? '')));
    $note = trim((string) ($_POST['decision_note'] ?? ''));

    if ($requestId <= 0 || !in_array($decision, ['book_for_patient', 'self_book'], true)) {
        $_SESSION['error'] = 'Invalid request decision payload.';
        header('Location: ' . route_url('appointmentsController', 'prescriptionQueue'));
        exit();
    }

    $status = $decision === 'book_for_patient' ? 'Booked by Receptionist' : 'Self Book Requested';
    $decisionUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    $conn = connect();
    $model = new AppointmentModel($conn);
    $ok = false;

    if ($model->tableExists('prescription_requests')) {
        $sql = "
            UPDATE prescription_requests
            SET status = ?,
                decision_action = ?,
                decision_by_user_id = ?,
                decision_at = NOW(),
                updated_at = NOW()
            WHERE request_id = ?
        ";
        $stmt = $conn->prepare($sql);
        if ($stmt !== false) {
            $stmt->bind_param('ssii', $status, $decision, $decisionUserId, $requestId);
            $ok = $stmt->execute();
            $stmt->close();
        }
    }

    if ($ok) {
        $_SESSION['success'] = 'Prescription request updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update prescription request decision.';
    }

    header('Location: ' . route_url('appointmentsController', 'prescriptionQueue'));
    exit();
}

private function parseSelectedTestIds($rawValue) {
    if (is_array($rawValue)) {
        $values = $rawValue;
    } else {
        $rawString = trim((string) $rawValue);
        if ($rawString === '') {
            return [];
        }
        $values = explode(',', $rawString);
    }

    $ids = [];
    foreach ($values as $value) {
        $id = intval(trim((string) $value));
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    return array_values($ids);
}

private function ensureCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['csrf_token'];
}

private function validateCsrfOrFail($expectsJson = false, $redirectController = 'appointmentsController', $redirectAction = 'index') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');
    $requestToken = (string)($_POST['csrf_token'] ?? '');
    if ($requestToken === '') {
        $requestToken = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }

    if ($sessionToken !== '' && $requestToken !== '' && hash_equals($sessionToken, $requestToken)) {
        return;
    }

    if ($expectsJson) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Security check failed. Please refresh and retry.'
        ]);
        exit;
    }

    $_SESSION['error'] = 'Security check failed. Please retry.';
    header('Location: ' . route_url($redirectController, $redirectAction));
    exit;
}


}


?>