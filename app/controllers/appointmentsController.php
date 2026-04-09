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
        include VIEW_PATH . '/receptionist/appointments.php';
    }

    public function createAppointment() {
        $appointmentsModel = new AppointmentModel(connect());
        $tests = $appointmentsModel->getAllTests();

        $prefillRequestId = (int)($_GET['request_id'] ?? 0);
        $prefillRequest = null;
        if ($prefillRequestId > 0) {
            $prefillRequest = $appointmentsModel->getPrescriptionRequestById($prefillRequestId);
            if (!$prefillRequest) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['error'] = 'Prescription request not found.';
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
                exit;
            }
        }

        include VIEW_PATH . '/receptionist/create_appointment.php';
    }

    public function prescriptionQueue() {
        $appointmentsModel = new AppointmentModel(connect());
        $statusFilter = trim((string)($_GET['status'] ?? 'pending'));

        if ($statusFilter === 'all') {
            $requests = $appointmentsModel->getPrescriptionRequests('all');
        } elseif ($statusFilter === 'processed') {
            $requests = array_values(array_filter(
                $appointmentsModel->getPrescriptionRequests('all'),
                function ($row) {
                    return strtolower((string)($row['status'] ?? '')) !== 'pending';
                }
            ));
        } else {
            $statusFilter = 'pending';
            $requests = $appointmentsModel->getPrescriptionRequests('Pending');
        }

        include VIEW_PATH . '/receptionist/prescription_queue.php';
    }

    public function prescriptionDecisionReport() {
        $appointmentsModel = new AppointmentModel(connect());

        $filters = [
            'status' => trim((string)($_GET['status'] ?? '')),
            'decision_action' => trim((string)($_GET['decision_action'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
            'decision_by_user_id' => (int)($_GET['decision_by_user_id'] ?? 0),
        ];

        $reportRows = $appointmentsModel->getPrescriptionDecisionReport($filters);

        if (isset($_GET['format']) && strtolower((string)$_GET['format']) === 'csv') {
            $filename = 'prescription_decisions_' . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);

            $out = fopen('php://output', 'w');
            if ($out) {
                fputcsv($out, [
                    'Request ID',
                    'Patient',
                    'Status',
                    'Decision Action',
                    'Decision By User ID',
                    'Decision By Username',
                    'Linked Appointment ID',
                    'Decision At',
                    'Requested At',
                    'Notes'
                ]);

                foreach ($reportRows as $row) {
                    fputcsv($out, [
                        (int)($row['request_id'] ?? 0),
                        (string)($row['patient_name'] ?? ('Patient #' . (int)($row['patient_id'] ?? 0))),
                        (string)($row['status'] ?? ''),
                        (string)($row['decision_action'] ?? ''),
                        (int)($row['decision_by_user_id'] ?? 0),
                        (string)($row['decision_by_username'] ?? ''),
                        (string)($row['linked_appointment_id'] ?? ''),
                        (string)($row['decision_at'] ?? ''),
                        (string)($row['created_at'] ?? ''),
                        (string)($row['notes'] ?? ''),
                    ]);
                }

                fclose($out);
            }

            exit;
        }

        $summary = $appointmentsModel->getPrescriptionDecisionSummary();
        include VIEW_PATH . '/receptionist/prescription_decisions.php';
    }

    public function prescriptionRequestDetails() {
        $requestId = (int)($_GET['request_id'] ?? 0);
        if ($requestId <= 0) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = 'Invalid prescription request id.';
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $appointmentsModel = new AppointmentModel(connect());
        $request = $appointmentsModel->getPrescriptionRequestById($requestId);
        if (!$request) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = 'Prescription request not found.';
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $events = $appointmentsModel->getPrescriptionRequestEvents($requestId);
        include VIEW_PATH . '/receptionist/prescription_request_details.php';
    }

    public function processPrescriptionDecision() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
        $decision = strtolower(trim((string)($_POST['decision_action'] ?? $_POST['decision'] ?? '')));
        $note = trim((string)($_POST['note'] ?? ''));
        $appointmentId = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
        $decisionByUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

        if ($requestId <= 0) {
            $_SESSION['error'] = 'Invalid prescription request id.';
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $appointmentsModel = new AppointmentModel(connect());

        if ($decision === 'book_for_patient') {
            if ($appointmentId <= 0) {
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment&request_id=' . $requestId);
                exit;
            }

            $ok = $appointmentsModel->markPrescriptionRequestBooked($requestId, $appointmentId, $decisionByUserId);
            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Prescription request marked as booked.'
                : ($appointmentsModel->getLastError() ?: 'Unable to mark prescription request as booked.');
        } elseif ($decision === 'self_book') {
            $ok = $appointmentsModel->markPrescriptionRequestSelfBooking($requestId, $note, $decisionByUserId);
            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Patient has been asked to self-book.'
                : ($appointmentsModel->getLastError() ?: 'Unable to process self-booking decision.');
        } else {
            $_SESSION['error'] = 'Invalid prescription decision action.';
        }

        header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
        exit;
    }

    public function storeAppointment($role = '') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientId = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
            $appointmentDate = $_POST['appointment_date'] ?? '';
            $appointmentTime = $_POST['appointment_time'] ?? '';
            $reason = $_POST['reason'] ?? '';
            $method = $_POST['method'] ?? ($_POST['booking_method'] ?? 'physical');
            $selectedRaw = $_POST['selected_test_ids'] ?? ($_POST['test_id'] ?? '');
            $selectedTestIds = $this->parseSelectedTestIds($selectedRaw);

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

            if ($success && isset($_POST['prescription_request_id'])) {
                $requestId = intval($_POST['prescription_request_id']);
                if ($requestId > 0) {
                    $decisionByUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
                    $model->markPrescriptionRequestBooked($requestId, (int)$success, $decisionByUserId);
                }
            }

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
        $this->ensureSessionStarted();

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

    }

    public function prescriptionQueue() {
        $this->ensureSessionStarted();

        $status = strtolower(trim((string)($_GET['status'] ?? 'pending')));
        if (!in_array($status, ['pending', 'processed', 'all'], true)) {
            $status = 'pending';
        }

        $model = new AppointmentModel(connect());
        if ($status === 'pending') {
            $requests = $model->getPrescriptionRequests('Pending');
        } elseif ($status === 'all') {
            $requests = $model->getPrescriptionRequests('all');
        } else {
            $allRequests = $model->getPrescriptionRequests('all');
            $requests = array_values(array_filter($allRequests, function ($row) {
                return strtolower((string)($row['status'] ?? 'pending')) !== 'pending';
            }));
        }

        include VIEW_PATH . '/receptionist/prescription_queue.php';
    }

    public function prescriptionDecisionReport() {
        $this->ensureSessionStarted();

        $filters = [
            'status' => trim((string)($_GET['status'] ?? '')),
            'decision_action' => trim((string)($_GET['decision_action'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
            'decision_by_user_id' => (int)($_GET['decision_by_user_id'] ?? 0),
        ];

        $model = new AppointmentModel(connect());
        $reportRows = $model->getPrescriptionDecisionReport($filters);
        $summary = $model->getPrescriptionDecisionSummary();

        if (strtolower((string)($_GET['format'] ?? '')) === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="prescription_decisions_' . date('Ymd_His') . '.csv"');

            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'request_id',
                'patient_id',
                'patient_name',
                'status',
                'decision_action',
                'decision_by_user_id',
                'decision_by_username',
                'linked_appointment_id',
                'decision_at',
                'created_at',
                'notes',
            ]);

            foreach ($reportRows as $row) {
                fputcsv($output, [
                    $row['request_id'] ?? '',
                    $row['patient_id'] ?? '',
                    $row['patient_name'] ?? '',
                    $row['status'] ?? '',
                    $row['decision_action'] ?? '',
                    $row['decision_by_user_id'] ?? '',
                    $row['decision_by_username'] ?? '',
                    $row['linked_appointment_id'] ?? '',
                    $row['decision_at'] ?? '',
                    $row['created_at'] ?? '',
                    preg_replace('/\s+/', ' ', (string)($row['notes'] ?? '')),
                ]);
            }

            fclose($output);
            exit;
        }

        include VIEW_PATH . '/receptionist/prescription_decisions.php';
    }

    public function prescriptionRequestDetails() {
        $this->ensureSessionStarted();

        $requestId = (int)($_GET['request_id'] ?? 0);
        if ($requestId <= 0) {
            $_SESSION['error'] = 'Invalid prescription request ID.';
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $model = new AppointmentModel(connect());
        $request = $model->getPrescriptionRequestById($requestId);
        if (!$request) {
            $_SESSION['error'] = 'Prescription request not found.';
            header('Location: /lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue');
            exit;
        }

        $events = $model->getPrescriptionRequestEvents($requestId);
        include VIEW_PATH . '/receptionist/prescription_request_details.php';
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
        $legacyOnline = $appointmentsModel->getAllAppointmentsByMethod('online');
        $legacyPhysical = $appointmentsModel->getAllAppointmentsByMethod('physical');
        $legacyCall = $appointmentsModel->getAllAppointmentsByMethod('call');

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