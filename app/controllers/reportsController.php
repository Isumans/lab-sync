<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/reportModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class reportsController {
    public function index($role = '') {
        include VIEW_PATH . '/technicians/reports.php';
    }

    public function listReports() {
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
        $perPage = isset($_GET['per_page']) ? max(1, min(50, intval($_GET['per_page']))) : 10;

        $filters = [
            'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
            'status' => isset($_GET['status']) ? trim(strtolower((string) $_GET['status'])) : 'all',
            'test_type' => isset($_GET['test_type']) ? trim(strtolower((string) $_GET['test_type'])) : 'all',
            'from_date' => isset($_GET['from_date']) ? trim((string) $_GET['from_date']) : '',
            'to_date' => isset($_GET['to_date']) ? trim((string) $_GET['to_date']) : '',
        ];

        $model = new ReportModel(connect());
        $rows = $model->getReportsList($filters, $page, $perPage);
        $listError = $model->getLastError();
        if ($listError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $listError
            ]);
            return;
        }

        $total = $model->countReports($filters);
        $countError = $model->getLastError();
        if ($countError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $countError
            ]);
            return;
        }

        $formatted = array_map(function ($row) {
            return [
                'appointmentId' => 'APP-' . str_pad((string) intval($row['appointment_id'] ?? 0), 4, '0', STR_PAD_LEFT),
                'appointmentNumericId' => intval($row['appointment_id'] ?? 0),
                'patientName' => (string) ($row['patient_name'] ?? 'Unknown Patient'),
                'date' => (string) ($row['appointment_date'] ?? ''),
                'status' => (string) ($row['status_label'] ?? 'pending'),
                'testType' => (string) ($row['test_types'] ?? ''),
                'progress' => intval($row['overall_progress'] ?? 0),
                'completed' => intval($row['completed_tests'] ?? 0),
                'total' => intval($row['total_tests'] ?? 0),
            ];
        }, $rows);

        $totalPages = max(1, (int) ceil($total / $perPage));

        echo json_encode([
            'status' => 'success',
            'data' => $formatted,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    public function createReport() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $testId = isset($input['test_id']) ? intval($input['test_id']) : 0;
        $reportContent = isset($input['report_content']) ? trim((string) $input['report_content']) : '';

        if ($testId <= 0 || $reportContent === '') {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Test ID and report content are required.'
            ]);
            return;
        }

        $model = new ReportModel(connect());
        $success = $model->createReport($testId, $reportContent);
        $createError = $model->getLastError();
        if (!$success) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $createError !== '' ? $createError : 'Failed to create report.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Report created successfully.'
        ]);
    }

    public function getReportDetails() {
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

        $model = new ReportModel(connect());
        $payload = $model->getReportDetailsPayload($appointmentId);

        if ($payload === null) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Report details not found.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $payload
        ]);
    }

    public function getEnterValuesContext() {
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
        $testId = isset($_GET['test_id']) ? intval($_GET['test_id']) : 0;
        if ($appointmentId <= 0 || $testId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid appointment or test ID.'
            ]);
            return;
        }

        $model = new ReportModel(connect());
        $context = $model->getEnterValuesContext($appointmentId, $testId);
        $contextError = $model->getLastError();

        if ($context === null) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => $contextError !== '' ? $contextError : 'Enter values context not found.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $context
        ]);
    }

    public function saveEnterValues() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request payload.'
            ]);
            return;
        }

        $appointmentId = isset($input['appointment_id']) ? intval($input['appointment_id']) : 0;
        $testId = isset($input['test_id']) ? intval($input['test_id']) : 0;
        $mode = isset($input['mode']) ? strtolower(trim((string) $input['mode'])) : 'draft';
        $results = isset($input['results']) && is_array($input['results']) ? $input['results'] : [];
        $remarks = isset($input['remarks']) ? trim((string) $input['remarks']) : '';
        $markAsReady = $mode === 'ready';
        $enteredBy = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

        if ($appointmentId <= 0 || $testId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid appointment or test ID.'
            ]);
            return;
        }

        if (empty($results)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'No test values provided.'
            ]);
            return;
        }

        $model = new ReportModel(connect());
        $saveOutcome = $model->saveEnterValues($appointmentId, $testId, $results, $remarks, $markAsReady, $enteredBy);
        $saveError = $model->getLastError();

        if ($saveOutcome === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $saveError !== '' ? $saveError : 'Failed to save test values.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => $markAsReady ? 'Results saved and marked as ready.' : 'Draft values saved successfully.',
            'data' => $saveOutcome
        ]);
    }

    public function viewReport($appointmentId = 0) {
        if ($appointmentId <= 0) {
            http_response_code(400);
            echo 'Invalid appointment ID.';
            return;
        }

        $model = new ReportModel(connect());
        $payload = $model->getReportDetailsPayload($appointmentId);

        if ($payload === null) {
            http_response_code(404);
            echo 'Report details not found.';
            return;
        }

        $appointment = $payload['appointment'];
        $tests = $payload['tests'];
        $billing = $payload['billing'];
        $summary = $payload['summary'];

        include VIEW_PATH . '/technicians/report_details.php';
    }

    public function details($role = '') {
        $appointmentId = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
        if ($appointmentId <= 0) {
            http_response_code(400);
            echo 'Invalid appointment ID.';
            return;
        }

        $model = new ReportModel(connect());
        $payload = $model->getReportDetailsPayload($appointmentId);

        if ($payload === null) {
            http_response_code(404);
            echo 'Report details not found.';
            return;
        }

        $appointment = $payload['appointment'];
        $tests = $payload['tests'];
        $billing = $payload['billing'];
        $summary = $payload['summary'];

        include VIEW_PATH . '/technicians/report_details.php';
    }
}
