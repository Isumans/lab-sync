<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/patientModel.php';
require_once APP_PATH . '/services/EmailService.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
class appointmentsController {
    public function index() {
        $appointmentsModel = new AppointmentModel(connect());
        $appointmentsOnline = $appointmentsModel->getAllAppointmentsByMethod("online");
        $appointmentsPhysical = $appointmentsModel->getAllAppointmentsExceptMethod("online");
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

        include VIEW_PATH . '/receptionist/create_Appointment.php';
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

    public function storeAppointment() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patientId = (int)($_POST['patient_id'] ?? 0);
            $testId = (int)($_POST['test_id'] ?? 0);
            $appointmentDate = trim($_POST['appointment_date'] ?? '');
            $appointmentTime = trim($_POST['appointment_time'] ?? '');
            $method = trim($_POST['booking_method'] ?? 'Physical');
            $prescriptionRequestId = (int)($_POST['prescription_request_id'] ?? 0);
            $homeCollection = !empty($_POST['home_collection']) ? 1 : 0;
            $collectionAddress = trim($_POST['collection_address'] ?? '');

            if ($patientId <= 0 || $testId <= 0 || $appointmentDate === '' || $appointmentTime === '') {
                $_SESSION['error'] = 'Please select a patient, test, date and time.';
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment');
                exit;
            }

            if ($homeCollection && $collectionAddress === '') {
                $_SESSION['error'] = 'Please provide a collection address for home sample collection.';
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment');
                exit;
            }

            $conn = connect();
            $model = new AppointmentModel($conn);

            $hasConflict = $model->hasTimeSlotConflict($appointmentDate, $appointmentTime);
            if ($hasConflict) {
                $_SESSION['error'] = 'Selected slot is already taken. Please choose a different date or time.';
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment');
                exit;
            }

            $success = $model->createReceptionistAppointment($patientId, $testId, $appointmentDate, $appointmentTime, $method, 'Pending', $homeCollection, $collectionAddress);
            if ($success) {
                $appointmentId = (int)$conn->insert_id;

                if ($prescriptionRequestId > 0) {
                    $decisionBy = (int)($_SESSION['user_id'] ?? 0);
                    $model->markPrescriptionRequestBooked($prescriptionRequestId, $appointmentId, $decisionBy);
                }

                $patientModel = new patientModel($conn);
                $patient = $patientModel->getPatientById($patientId);
                if ($patient && !empty($patient['email'])) {
                    $payload = $model->getAppointmentEmailPayload($appointmentId);
                    if ($payload) {
                        $mailer = new EmailService();
                        $mailer->sendAppointmentBookedEmail(
                            $patient['email'],
                            $patient['patient_name'] ?? 'Patient',
                            $payload
                        );
                    }
                }

                $_SESSION['success'] = 'Appointment created successfully.';
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=index');
                exit;
            } else {
                $_SESSION['error'] = 'Error creating appointment.';
                header('Location: /lab_sync/index.php?controller=appointmentsController&action=createAppointment');
                exit;
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