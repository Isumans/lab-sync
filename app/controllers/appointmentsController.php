<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/patientModel.php';
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
    }

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
    if (!in_array($method, ['all', 'online', 'physical', 'call'], true)) {
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
    $listError = $appointmentsModel->getLastError();
    $total = 0;
    $usedFallback = false;

    if ($listError === '') {
        $total = $appointmentsModel->countAppointments($filters);
        $countError = $appointmentsModel->getLastError();
        if ($countError !== '') {
            $listError = $countError;
        }
    }

    if ($listError !== '') {
        // Fallback: use legacy list methods and apply filtering/sorting/pagination in PHP.
        $usedFallback = true;
        $legacyOnline = $appointmentsModel->getAllAppointmentsbyMethod('online');
        $legacyPhysical = $appointmentsModel->getAllAppointmentsbyMethod('physical');
        $legacyCall = $appointmentsModel->getAllAppointmentsbyMethod('call');

        $fallbackRows = array_merge($legacyOnline ?: [], $legacyPhysical ?: [], $legacyCall ?: []);

        if (in_array($filters['method'], ['online', 'physical', 'call'], true)) {
            $targetMethod = $filters['method'];
            $fallbackRows = array_values(array_filter($fallbackRows, function ($row) use ($targetMethod) {
                $rowMethod = strtolower(trim((string) ($row['method'] ?? '')));
                if ($targetMethod === 'physical') {
                    return in_array($rowMethod, ['physical', 'call'], true);
                }
                return $rowMethod === $targetMethod;
            }));
        }

        $search = strtolower(trim((string) $filters['search']));
        if ($search !== '') {
            $fallbackRows = array_values(array_filter($fallbackRows, function ($row) use ($search) {
                $patientName = strtolower((string) ($row['patient_name'] ?? ($row['patient_display_name'] ?? '')));
                $appointmentId = strtolower((string) ($row['appointment_id'] ?? ''));
                $methodValue = strtolower((string) ($row['method'] ?? ''));
                return strpos($patientName, $search) !== false
                    || strpos($appointmentId, $search) !== false
                    || strpos($methodValue, $search) !== false;
            }));
        }

        $fromDate = trim((string) $filters['from_date']);
        if ($fromDate !== '') {
            $fallbackRows = array_values(array_filter($fallbackRows, function ($row) use ($fromDate) {
                $rowDate = (string) ($row['appointment_date'] ?? '');
                return $rowDate !== '' && $rowDate >= $fromDate;
            }));
        }

        $toDate = trim((string) $filters['to_date']);
        if ($toDate !== '') {
            $fallbackRows = array_values(array_filter($fallbackRows, function ($row) use ($toDate) {
                $rowDate = (string) ($row['appointment_date'] ?? '');
                return $rowDate !== '' && $rowDate <= $toDate;
            }));
        }

        $sortAllowlist = [
            'appointment_id' => 'appointment_id',
            'patient_name' => 'patient_name',
            'appointment_date' => 'appointment_date',
            'appointment_time' => 'appointment_time',
            'method' => 'method',
        ];

        $sortKey = $sortAllowlist[strtolower(trim((string) $sortBy))] ?? 'appointment_date';
        $direction = strtolower(trim((string) $sortDir)) === 'asc' ? 1 : -1;

        usort($fallbackRows, function ($a, $b) use ($sortKey, $direction) {
            $aValue = $a[$sortKey] ?? '';
            $bValue = $b[$sortKey] ?? '';

            if (is_numeric($aValue) && is_numeric($bValue)) {
                $cmp = intval($aValue) <=> intval($bValue);
            } else {
                $cmp = strcmp(strtolower((string) $aValue), strtolower((string) $bValue));
            }

            return $cmp * $direction;
        });

        $total = count($fallbackRows);
        $offset = ($page - 1) * $perPage;
        $appointments = array_slice($fallbackRows, $offset, $perPage);
    }

    $totalPages = max(1, (int) ceil($total / $perPage));

    $payload = [
        'status' => 'success',
        'data' => $appointments,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
        ],
    ];

    if ($usedFallback) {
        $payload['fallback_used'] = true;
    }

    echo json_encode($payload);
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