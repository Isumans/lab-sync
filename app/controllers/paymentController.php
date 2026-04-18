<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

require_once MODEL_PATH . '/homeModel.php';
require_once MODEL_PATH . '/billingModel.php';
require_once APP_PATH . '/services/EmailService.php';
require_once APP_PATH . '/services/SmsService.php';
require_once __DIR__ . '/../../config/db.php';

class paymentController {
    private $model;
    private $payhereConfig;

    public function __construct() {
        $db = connect();
        $this->model = new HomeModel($db);
        $this->payhereConfig = require __DIR__ . '/../../config/payhere.php';
    }

    public function initiate() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $testIds = $body['test_ids'] ?? [];
        if (!is_array($testIds)) {
            $testIds = [];
        }

        $date              = trim($body['appointment_date'] ?? '');
        $time              = trim($body['appointment_time'] ?? '');
        $homeCollection    = !empty($body['home_collection']) ? 1 : 0;
        $collectionAddress = trim($body['collection_address'] ?? '');
        $fromRequestId     = (int)($body['from_request'] ?? 0);

        $patientId = $this->model->getPatientIdByUserId($_SESSION['user_id']);

        if (count($testIds) === 0 || !$date || !$time || !$patientId) {
            echo json_encode(['status' => 'error', 'message' => 'Missing required booking fields']);
            exit;
        }

        if ($homeCollection && $collectionAddress === '') {
            echo json_encode(['status' => 'error', 'message' => 'Collection address is required for home collection']);
            exit;
        }

        if ($this->model->hasTimeSlotConflict($date, $time)) {
            echo json_encode(['status' => 'error', 'message' => 'Selected slot is already taken. Please choose a different time.']);
            exit;
        }

        $appointmentId = $this->model->createOnlineAppointmentWithItems([
            'patient_id'         => $patientId,
            'appointment_date'   => $date,
            'appointment_time'   => $time,
            'method'             => 'Online',
            'status'             => 'Pending',
            'booking_channel'    => 'online_self',
            'home_collection'    => $homeCollection,
            'collection_address' => $collectionAddress,
        ], $testIds);

        if (!$appointmentId) {
            echo json_encode(['status' => 'error', 'message' => $this->model->getLastError() ?: 'Failed to create appointment']);
            exit;
        }

        $amount  = $this->model->getTestsTotal(array_map('intval', $testIds));
        $orderId = 'APT-' . $appointmentId;

        $merchantId     = $this->payhereConfig['merchant_id'];
        $merchantSecret = $this->payhereConfig['merchant_secret'];
        $hash = strtoupper(md5(
            $merchantId .
            $orderId .
            number_format($amount, 2, '.', '') .
            'LKR' .
            strtoupper(md5($merchantSecret))
        ));

        $contact   = $this->model->getPatientContactByUserId($_SESSION['user_id']);
        $nameParts = explode(' ', $contact['patient_name'] ?? '', 2);

        echo json_encode([
            'status'         => 'success',
            'appointment_id' => $appointmentId,
            'order_id'       => $orderId,
            'merchant_id'    => $merchantId,
            'hash'           => $hash,
            'amount'         => number_format($amount, 2, '.', ''),
            'currency'       => 'LKR',
            'sandbox'        => (bool)$this->payhereConfig['sandbox'],
            'return_url'     => $this->payhereConfig['return_url'],
            'cancel_url'     => $this->payhereConfig['cancel_url'],
            'notify_url'     => $this->payhereConfig['notify_url'],
            'first_name'     => $nameParts[0] ?? '',
            'last_name'      => $nameParts[1] ?? '',
            'email'          => $contact['email'] ?? '',
            'phone'          => $contact['contact_number'] ?? '',
            'from_request'   => $fromRequestId,
        ]);
        exit;
    }

    public function confirmPayment() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
            exit;
        }

        $body          = json_decode(file_get_contents('php://input'), true) ?? [];
        $appointmentId = (int)($body['appointment_id'] ?? 0);
        $orderId       = trim($body['order_id'] ?? '');
        $fromRequestId = (int)($body['from_request'] ?? 0);

        if (!$appointmentId) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid appointment']);
            exit;
        }

        $this->model->updatePaymentStatus($appointmentId, 'paid', $orderId);

        $billingModel = new BillingModel(connect());
        $billingModel->createBillFromOnlinePayment($appointmentId, $orderId);

        if ($fromRequestId > 0) {
            $this->model->linkAppointmentToRequest($fromRequestId, $appointmentId);
        }

        $contact = $this->model->getPatientContactByUserId($_SESSION['user_id']);
        if ($contact) {
            $payload = $this->model->getAppointmentEmailPayload($appointmentId);
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

        $_SESSION['success'] = 'Appointment booked and payment confirmed!';
        echo json_encode([
            'status'   => 'success',
            'redirect' => '/lab_sync/index.php?controller=home&action=dashboard',
        ]);
        exit;
    }

    // Server-to-server Payhere notification — used when deployed on a public URL
    public function notify() {
        $merchantId     = $this->payhereConfig['merchant_id'];
        $merchantSecret = $this->payhereConfig['merchant_secret'];

        $orderId         = $_POST['order_id'] ?? '';
        $payhereAmount   = $_POST['payhere_amount'] ?? '';
        $payhereCurrency = $_POST['payhere_currency'] ?? '';
        $statusCode      = $_POST['status_code'] ?? '';
        $md5sig          = $_POST['md5sig'] ?? '';

        $expectedSig = strtoupper(md5(
            $merchantId .
            $orderId .
            $payhereAmount .
            $payhereCurrency .
            $statusCode .
            strtoupper(md5($merchantSecret))
        ));

        if (!hash_equals($expectedSig, $md5sig)) {
            http_response_code(400);
            exit;
        }

        if ($statusCode == '2') {
            $appointmentId = (int)str_replace('APT-', '', $orderId);
            if ($appointmentId > 0) {
                $this->model->updatePaymentStatus($appointmentId, 'paid', $orderId);
                $billingModel = new BillingModel(connect());
                $billingModel->createBillFromOnlinePayment($appointmentId, $orderId);
            }
        }

        http_response_code(200);
        exit;
    }
}
