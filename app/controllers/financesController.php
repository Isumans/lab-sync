<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/billingModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class financesController {
    private $db;
    private $billingModel;

    public function __construct() {
        $this->ensureSession();
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        $this->billingModel = new BillingModel($this->db);
    }

    public function index($role = '') {
        include __DIR__ . '/../views/administrator/finances.php';
    }

    public function listBills() {
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

        $filters = $this->collectListFiltersFromQuery();

        $validationError = $this->validateListFilters($filters);
        if ($validationError !== '') {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $validationError
            ]);
            return;
        }

        $rows = $this->billingModel->getBillsList($filters, $page, $perPage);
        $listError = $this->billingModel->getLastError();
        if ($listError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $listError
            ]);
            return;
        }

        $total = $this->billingModel->countBills($filters);
        $countError = $this->billingModel->getLastError();
        if ($countError !== '') {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $countError
            ]);
            return;
        }

        $formatted = array_map(function ($row) {
            $appointmentNumericId = intval($row['appointment_id'] ?? 0);
            $rawStatus = strtoupper((string) ($row['status'] ?? 'PENDING'));
            $statusMeta = $this->mapStatus($rawStatus);
            $paymentMethod = strtoupper((string) ($row['latest_payment_method'] ?? ''));

            return [
                'billId' => intval($row['bill_id'] ?? 0),
                'appointmentNumericId' => $appointmentNumericId,
                'appointmentId' => 'APT-' . str_pad((string) $appointmentNumericId, 4, '0', STR_PAD_LEFT),
                'patientName' => (string) ($row['patient_name'] ?? 'Unknown Patient'),
                'totalAmount' => floatval($row['total_amount'] ?? 0),
                'amountPaid' => floatval($row['paid_amount'] ?? 0),
                'paymentMethod' => $this->formatPaymentMethod($paymentMethod),
                'financialStatus' => $statusMeta['label'],
                'statusKey' => $statusMeta['key'],
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

    public function exportBillsCsv() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo 'Invalid request method.';
            return;
        }

        $filters = $this->collectListFiltersFromQuery();
        $validationError = $this->validateListFilters($filters);
        if ($validationError !== '') {
            http_response_code(400);
            echo $validationError;
            return;
        }

        $rows = $this->billingModel->getBillsForExport($filters);
        $listError = $this->billingModel->getLastError();
        if ($listError !== '') {
            http_response_code(500);
            echo $listError;
            return;
        }

        $fileName = 'finances_report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            http_response_code(500);
            echo 'Unable to generate CSV.';
            return;
        }

        fputcsv($output, [
            'Bill ID',
            'Bill Date',
            'Appointment ID',
            'Patient Name',
            'Total Amount (LKR)',
            'Amount Paid (LKR)',
            'Outstanding Amount (LKR)',
            'Payment Method',
            'Financial Status',
        ]);

        foreach ($rows as $row) {
            $appointmentNumericId = intval($row['appointment_id'] ?? 0);
            $totalAmount = floatval($row['total_amount'] ?? 0);
            $amountPaid = floatval($row['paid_amount'] ?? 0);
            $outstanding = max(0, $totalAmount - $amountPaid);
            $statusMeta = $this->mapStatus(strtoupper((string)($row['status'] ?? 'PENDING')));
            $paymentMethod = $this->formatPaymentMethod(strtoupper((string)($row['latest_payment_method'] ?? '')));

            fputcsv($output, [
                intval($row['bill_id'] ?? 0),
                (string)($row['bill_date'] ?? ''),
                'APT-' . str_pad((string)$appointmentNumericId, 4, '0', STR_PAD_LEFT),
                (string)($row['patient_name'] ?? 'Unknown Patient'),
                number_format($totalAmount, 2, '.', ''),
                number_format($amountPaid, 2, '.', ''),
                number_format($outstanding, 2, '.', ''),
                $paymentMethod,
                $statusMeta['label'],
            ]);
        }

        fclose($output);
    }

    public function exportBillsPrint() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo 'Invalid request method.';
            return;
        }

        $filters = $this->collectListFiltersFromQuery();
        $validationError = $this->validateListFilters($filters);
        if ($validationError !== '') {
            http_response_code(400);
            echo $validationError;
            return;
        }

        $rows = $this->billingModel->getBillsForExport($filters);
        $listError = $this->billingModel->getLastError();
        if ($listError !== '') {
            http_response_code(500);
            echo $listError;
            return;
        }

        $exportRows = [];
        $summaryTotals = [
            'total_amount' => 0.0,
            'paid_amount' => 0.0,
            'outstanding_amount' => 0.0,
        ];

        foreach ($rows as $row) {
            $appointmentNumericId = intval($row['appointment_id'] ?? 0);
            $totalAmount = floatval($row['total_amount'] ?? 0);
            $amountPaid = floatval($row['paid_amount'] ?? 0);
            $outstanding = max(0, $totalAmount - $amountPaid);
            $statusMeta = $this->mapStatus(strtoupper((string)($row['status'] ?? 'PENDING')));
            $paymentMethod = $this->formatPaymentMethod(strtoupper((string)($row['latest_payment_method'] ?? '')));

            $summaryTotals['total_amount'] += $totalAmount;
            $summaryTotals['paid_amount'] += $amountPaid;
            $summaryTotals['outstanding_amount'] += $outstanding;

            $exportRows[] = [
                'bill_id' => intval($row['bill_id'] ?? 0),
                'bill_date' => (string)($row['bill_date'] ?? ''),
                'appointment_id' => 'APT-' . str_pad((string)$appointmentNumericId, 4, '0', STR_PAD_LEFT),
                'patient_name' => (string)($row['patient_name'] ?? 'Unknown Patient'),
                'total_amount' => $totalAmount,
                'paid_amount' => $amountPaid,
                'outstanding_amount' => $outstanding,
                'payment_method' => $paymentMethod,
                'financial_status' => $statusMeta['label'],
            ];
        }

        $reportGeneratedAt = date('Y-m-d H:i:s');
        $filterSummary = $this->buildFilterSummary($filters);
        include __DIR__ . '/../views/administrator/finances_export_print.php';
    }

    public function sendReminder() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ]);
            return;
        }

        $input = $this->parseInput();
        $billId = isset($input['bill_id']) ? intval($input['bill_id']) : 0;

        if ($billId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid bill ID.'
            ]);
            return;
        }

        $bill = $this->billingModel->getBillById($billId);
        if (!$bill) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Bill not found.'
            ]);
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $reminderLine = '[' . $timestamp . '] Reminder sent by User #' . $userId;
        $existingNotes = trim((string) ($bill['notes'] ?? ''));
        $updatedNotes = $existingNotes === '' ? $reminderLine : ($existingNotes . "\n" . $reminderLine);

        $stmt = $this->db->prepare('UPDATE bills SET notes = ?, updated_at = CURRENT_TIMESTAMP WHERE bill_id = ?');
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to prepare reminder update.'
            ]);
            return;
        }

        $stmt->bind_param('si', $updatedNotes, $billId);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to send reminder.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Reminder sent successfully.'
        ]);
    }

    public function deleteBill() {
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ]);
            return;
        }

        $input = $this->parseInput();
        $billId = isset($input['bill_id']) ? intval($input['bill_id']) : 0;

        if ($billId <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid bill ID.'
            ]);
            return;
        }

        $bill = $this->billingModel->getBillById($billId);
        if (!$bill) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Bill not found.'
            ]);
            return;
        }

        $currentStatus = strtoupper((string) ($bill['status'] ?? ''));
        if ($currentStatus === 'PAID') {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot cancel a paid bill.'
            ]);
            return;
        }

        if ($currentStatus === 'CANCELLED') {
            echo json_encode([
                'status' => 'success',
                'message' => 'Bill is already cancelled.'
            ]);
            return;
        }

        $cancelledStatus = 'CANCELLED';
        $stmt = $this->db->prepare('UPDATE bills SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE bill_id = ?');
        if ($stmt === false) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to prepare bill cancellation.'
            ]);
            return;
        }

        $stmt->bind_param('si', $cancelledStatus, $billId);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to cancel bill.'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Bill cancelled successfully.'
        ]);
    }

    private function parseInput() {
        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
        return $input;
    }

    private function collectListFiltersFromQuery() {
        return [
            'search' => isset($_GET['search']) ? trim((string) $_GET['search']) : '',
            'status' => isset($_GET['status']) ? trim(strtolower((string) $_GET['status'])) : 'all',
            'payment_method' => isset($_GET['payment_method']) ? trim(strtolower((string) $_GET['payment_method'])) : 'all',
            'from_date' => isset($_GET['from_date']) ? trim((string) $_GET['from_date']) : '',
            'to_date' => isset($_GET['to_date']) ? trim((string) $_GET['to_date']) : '',
        ];
    }

    private function ensureSession() {
    }

    private function mapStatus($status) {
        if ($status === 'PAID') {
            return ['key' => 'paid-in-full', 'label' => 'Paid in Full'];
        }

        if ($status === 'PARTIALLY_PAID') {
            return ['key' => 'partially-paid', 'label' => 'Partially Paid'];
        }

        if ($status === 'CANCELLED') {
            return ['key' => 'claim-submitted', 'label' => 'Claim Submitted'];
        }

        return ['key' => 'unpaid', 'label' => 'Unpaid'];
    }

    private function formatPaymentMethod($method) {
        if ($method === 'CASH') {
            return 'Cash';
        }

        if ($method === 'CARD') {
            return 'Card';
        }

        if ($method === 'TRANSFER') {
            return 'Transfer';
        }

        return '-';
    }

    private function validateListFilters($filters) {
        $allowedStatus = ['all', 'paid_in_full', 'unpaid', 'partially_paid', 'claim_submitted'];
        $allowedPaymentMethods = ['all', 'cash', 'card', 'transfer'];

        $status = strtolower((string)($filters['status'] ?? 'all'));
        if (!in_array($status, $allowedStatus, true)) {
            return 'Invalid status filter.';
        }

        $paymentMethod = strtolower((string)($filters['payment_method'] ?? 'all'));
        if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
            return 'Invalid payment method filter.';
        }

        $fromDate = (string)($filters['from_date'] ?? '');
        $toDate = (string)($filters['to_date'] ?? '');

        if ($fromDate !== '' && !$this->isValidDateYmd($fromDate)) {
            return 'Invalid from date format.';
        }

        if ($toDate !== '' && !$this->isValidDateYmd($toDate)) {
            return 'Invalid to date format.';
        }

        if ($fromDate !== '' && $toDate !== '' && strcmp($fromDate, $toDate) > 0) {
            return 'From date cannot be later than to date.';
        }

        return '';
    }

    private function buildFilterSummary($filters) {
        $parts = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $parts[] = 'Search: ' . $search;
        }

        $status = strtolower((string)($filters['status'] ?? 'all'));
        if ($status !== 'all') {
            $parts[] = 'Status: ' . ucwords(str_replace('_', ' ', $status));
        }

        $paymentMethod = strtolower((string)($filters['payment_method'] ?? 'all'));
        if ($paymentMethod !== 'all') {
            $parts[] = 'Payment: ' . ucfirst($paymentMethod);
        }

        $fromDate = trim((string)($filters['from_date'] ?? ''));
        $toDate = trim((string)($filters['to_date'] ?? ''));
        if ($fromDate !== '' || $toDate !== '') {
            $parts[] = 'Date Range: ' . ($fromDate !== '' ? $fromDate : '-') . ' to ' . ($toDate !== '' ? $toDate : '-');
        }

        if (empty($parts)) {
            return 'No filters applied';
        }

        return implode(' | ', $parts);
    }

    private function isValidDateYmd($value) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return false;
        }

        $parts = explode('-', $value);
        if (count($parts) !== 3) {
            return false;
        }

        return checkdate(intval($parts[1]), intval($parts[2]), intval($parts[0]));
    }
}