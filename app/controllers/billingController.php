<?php


require_once MODEL_PATH . '/appointmentModel.php';
require_once MODEL_PATH . '/billingModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class billingController {
    public function index() {
        include VIEW_PATH . '/receptionist/billing.php';
    }

    public function registerBilling() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . route_url('Auth', 'index'));
            exit();
        }

        $appointmentId = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
        $appointmentPayload = null;
        $existingBill = null;

        if ($appointmentId > 0) {
            $appointmentModel = new AppointmentModel(connect());
            $appointmentPayload = $appointmentModel->getAppointmentDetailsPayload($appointmentId);

            $billingModel = new BillingModel(connect());
            $existingBill = $billingModel->getBillByAppointmentId($appointmentId);
        }

        include VIEW_PATH . '/receptionist/createBill.php';
    }

    public function saveDraft() {
        $this->save(false);
    }

    public function finalizeBill() {
        $this->save(true);
    }

    public function printInvoice() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . route_url('Auth', 'index'));
            exit();
        }

        $billId = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;
        if ($billId <= 0) {
            http_response_code(400);
            echo 'Invalid bill ID.';
            return;
        }

        $billingModel = new BillingModel(connect());
        $bill = $billingModel->getBillById($billId);

        if ($bill === null) {
            http_response_code(404);
            echo 'Bill not found.';
            return;
        }

        if (($_SESSION['user_role'] ?? '') === 'patient') {
            require_once MODEL_PATH . '/homeModel.php';
            $homeModel      = new HomeModel(connect());
            $currentPatient = $homeModel->getPatientIdByUserId((int) $_SESSION['user_id']);
            if ((int) $bill['patient_id'] !== $currentPatient) {
                http_response_code(403);
                echo 'Access denied.';
                return;
            }
        }

        $appointmentModel = new AppointmentModel(connect());
        $appointmentPayload = $appointmentModel->getAppointmentDetailsPayload(intval($bill['appointment_id']));
        $appointment = $appointmentPayload['appointment'] ?? null;

        $patientName = $appointment['patient_name'] ?? ('Patient #' . intval($bill['patient_id'] ?? 0));
        $appointmentDate = $appointment['appointment_date'] ?? ($bill['bill_date'] ?? '');

        $branding  = $this->getInvoiceBranding();
        $html      = $this->buildInvoicePdfHtml($bill, $patientName, $appointmentDate, $branding);
        $autoPrint = isset($_GET['auto_print']) && $_GET['auto_print'] === '1';

        if ($autoPrint) {
            $html = str_replace('</body>', '<script>window.addEventListener("load",function(){window.print();});</script></body>', $html);
        }

        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
    }

    private function buildInvoicePdfHtml($bill, $patientName, $appointmentDate, $branding = []) {
        $billItems = isset($bill['items']) && is_array($bill['items']) ? $bill['items'] : [];

        $billNumber      = htmlspecialchars((string) ($bill['bill_number'] ?? ''));
        $billDate        = htmlspecialchars((string) ($bill['bill_date'] ?? ''));
        $patientName     = htmlspecialchars((string) $patientName);
        $appointmentDate = htmlspecialchars((string) $appointmentDate);
        $status          = htmlspecialchars((string) ($bill['status'] ?? ''));

        $subtotal   = number_format(floatval($bill['subtotal'] ?? 0), 2);
        $discount   = number_format(floatval($bill['discount_amount'] ?? 0), 2);
        $tax        = number_format(floatval($bill['tax_amount'] ?? 0), 2);
        $grandTotal = number_format(floatval($bill['total_amount'] ?? 0), 2);
        $paid       = number_format(floatval($bill['paid_amount'] ?? 0), 2);
        $balance    = number_format(floatval($bill['balance_due'] ?? 0), 2);

        $labName    = htmlspecialchars((string) ($branding['lab_name'] ?? 'LabSync'));
        $labAddress = htmlspecialchars((string) ($branding['address'] ?? ''));
        $labPhone   = htmlspecialchars((string) ($branding['phone'] ?? ''));
        $labEmail   = htmlspecialchars((string) ($branding['email'] ?? ''));
        $labAcc     = htmlspecialchars((string) ($branding['accreditation'] ?? ''));

        $logoSrc  = (string) ($branding['logo_src'] ?? '');
        $logoHtml = $logoSrc !== '' ? "<img src=\"" . htmlspecialchars($logoSrc) . "\" alt=\"Lab Logo\" style=\"width:42px;height:42px;\">" : '';

        $rowsHtml = '';
        foreach ($billItems as $item) {
            $name = htmlspecialchars((string) ($item['test_name'] ?? ''));
            $unit = number_format(floatval($item['unit_price'] ?? 0), 2);
            $qty  = intval($item['quantity'] ?? 0);
            $line = number_format(floatval($item['line_total'] ?? 0), 2);
            $rowsHtml .= "
                <tr>
                    <td style='padding:8px;border:1px solid #dee2e6;'>{$name}</td>
                    <td style='padding:8px;border:1px solid #dee2e6;text-align:right;'>LKR {$unit}</td>
                    <td style='padding:8px;border:1px solid #dee2e6;text-align:center;'>{$qty}</td>
                    <td style='padding:8px;border:1px solid #dee2e6;text-align:right;'>LKR {$line}</td>
                </tr>
            ";
        }

        if ($rowsHtml === '') {
            $rowsHtml = "<tr><td colspan='4' style='padding:10px;border:1px solid #dee2e6;text-align:center;color:#777;'>No bill items</td></tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {$billNumber}</title>
<style>
    @page { size: A4; margin: 10mm; }
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; padding: 0; }
        .invoice-page { box-shadow: none; margin: 0; max-width: 100%; }
    }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #1f2937; margin: 0; padding: 20px 0 40px; background: #e8e8e8; }
    .invoice-page { max-width: 794px; margin: 0 auto; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.18); }
    .invoice-body { padding: 0 0 20px; }
    .header { background:#1f4d75; color:#fff; padding:7px 10px; }
    .header-table { width:100%; border-collapse:collapse; }
    .header-table td { border:none; vertical-align:middle; }
    .logo-cell { width:58px; }
    .logo-box { width:48px; height:48px; background:#ffffff; border:1px solid #d9e3ed; text-align:center; }
    .brand-cell { padding-left:10px; }
    .brand-name { margin:0; font-size:19pt; font-weight:700; letter-spacing:.3px; color:#ffffff; line-height:1.0; }
    .brand-line { margin:2px 0 0; font-size:9pt; color:#d6e5f5; }
    .section-title { background:#1a3a5c; color:#fff; padding:7px 12px; margin-top:12px; font-weight:700; font-size:10pt; }
    .panel { border:1px solid #dee2e6; border-top:none; padding:10px 12px; }
    .info-row { margin:4px 0; }
    .lbl { display:inline-block; width:130px; color:#6b7280; font-weight:700; }
    table { width:100%; border-collapse:collapse; }
    thead th { background:#2c5f8a; color:#fff; padding:8px; border:1px solid #1a3a5c; font-size:9.5pt; }
    .totals { width:360px; margin-left:auto; margin-top:12px; border-collapse:collapse; }
    .totals td { border:1px solid #dee2e6; padding:7px 10px; font-size:9.5pt; }
    .totals .k { font-weight:700; color:#4b5563; }
    .totals .v { text-align:right; }
    .grand .k, .grand .v { font-size:11pt; font-weight:800; background:#f3f6fa; }
</style>
</head>
<body>
<div class="no-print" style="max-width:794px; margin:0 auto 12px; text-align:right;">
    <button onclick="window.print()" style="padding:6px 16px; background:#1a3a5c; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">Print / Save as PDF</button>
</div>
<div class="invoice-page">
<div class="invoice-body">

<div class="header">
    <table class="header-table">
        <tr>
            <td class="logo-cell"><div class="logo-box">{$logoHtml}</div></td>
            <td class="brand-cell">
                <p class="brand-name">{$labName}</p>
                <p class="brand-line">{$labAddress}</p>
                <p class="brand-line">Tel: {$labPhone} | Email: {$labEmail} | Accreditation: {$labAcc}</p>
            </td>
        </tr>
    </table>
</div>

<div class="section-title">Invoice Details</div>
<div class="panel">
    <div class="info-row"><span class="lbl">Bill No</span> {$billNumber}</div>
    <div class="info-row"><span class="lbl">Bill Date</span> {$billDate}</div>
    <div class="info-row"><span class="lbl">Patient</span> {$patientName}</div>
    <div class="info-row"><span class="lbl">Appointment Date</span> {$appointmentDate}</div>
    <div class="info-row"><span class="lbl">Status</span> {$status}</div>
</div>

<div class="section-title">Billable Items</div>
<table>
    <thead>
        <tr>
            <th style="text-align:left;">Test Name</th>
            <th style="text-align:right;">Unit Price</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>{$rowsHtml}</tbody>
</table>

<table class="totals">
    <tr><td class="k">Subtotal</td><td class="v">LKR {$subtotal}</td></tr>
    <tr><td class="k">Discount</td><td class="v">LKR {$discount}</td></tr>
    <tr><td class="k">Tax</td><td class="v">LKR {$tax}</td></tr>
    <tr class="grand"><td class="k">Grand Total</td><td class="v">LKR {$grandTotal}</td></tr>
    <tr><td class="k">Paid</td><td class="v">LKR {$paid}</td></tr>
    <tr><td class="k">Balance</td><td class="v">LKR {$balance}</td></tr>
</table>

</div>
</div>
</body>
</html>
HTML;
    }

    private function getInvoiceBranding() {
        $labName = 'LabSync';
        $logoPath = '';

        $address = 'No: 91 Reid avenue, colombo 07';
        $phone = '+94 77 123 4567';
        $email = 'labsync@gmail.com';
        $accreditation = 'ISO-15189-2023';

        $result = @connect()->query("SELECT lab_name, logo_path, address, phone, email, accreditation FROM lab_configuration LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['lab_name'])) {
                $labName = (string) $row['lab_name'];
            }
            if (!empty($row['logo_path'])) {
                $logoPath = (string) $row['logo_path'];
            }
            if (!empty($row['address'])) {
                $address = (string) $row['address'];
            }
            if (!empty($row['phone'])) {
                $phone = (string) $row['phone'];
            }
            if (!empty($row['email'])) {
                $email = (string) $row['email'];
            }
            if (!empty($row['accreditation'])) {
                $accreditation = (string) $row['accreditation'];
            }
        }

        if ($logoPath === '') {
            $logoPath = '/lab_sync/public/assests/Labsync-3.png';
        }

        $logoSrc = strpos($logoPath, 'uploads/') !== false
            ? '/lab_sync/public/uploads/' . basename($logoPath)
            : $logoPath;

        return [
            'lab_name'      => $labName,
            'logo_src'      => $logoSrc,
            'address'       => $address,
            'phone'         => $phone,
            'email'         => $email,
            'accreditation' => $accreditation,
        ];
    }

    private function save($finalize) {
        header('Content-Type: application/json; charset=UTF-8');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized.'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method.'
            ]);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput, true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid billing payload.'
            ]);
            return;
        }

        $validation = $this->validateSavePayload($payload);
        if (!$validation['ok']) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $validation['message']
            ]);
            return;
        }

        $payload = $validation['payload'];

        $billingModel = new BillingModel(connect());
        $saved = $billingModel->saveBill($payload, $finalize, intval($_SESSION['user_id']));

        if ($saved === null) {
            $errorMessage = $billingModel->getLastError() ?: 'Failed to save bill.';
            $lowerError = strtolower($errorMessage);

            if (strpos($lowerError, 'cannot be edited') !== false) {
                http_response_code(409);
            } elseif (strpos($lowerError, 'not allowed') !== false || strpos($lowerError, 'greater than zero') !== false || strpos($lowerError, 'invalid') !== false) {
                http_response_code(400);
            } else {
                http_response_code(500);
            }

            echo json_encode([
                'status' => 'error',
                'message' => $errorMessage
            ]);
            return;
        }

        $response = [
            'status' => 'success',
            'message' => $finalize ? 'Bill finalized successfully.' : 'Draft saved successfully.',
            'data' => [
                'bill_id' => intval($saved['bill_id'] ?? 0),
                'bill_number' => (string) ($saved['bill_number'] ?? ''),
                'status' => (string) ($saved['status'] ?? ''),
                'total_amount' => floatval($saved['total_amount'] ?? 0),
                'paid_amount' => floatval($saved['paid_amount'] ?? 0),
                'balance_due' => floatval($saved['balance_due'] ?? 0),
            ]
        ];

        if ($finalize) {
            $response['data']['print_url'] = '/lab_sync/index.php?controller=billingController&action=printInvoice&bill_id=' . intval($saved['bill_id']) . '&auto_print=1';
        }

        echo json_encode($response);
    }

    private function validateSavePayload($payload) {
        $appointmentId = intval($payload['appointment_id'] ?? 0);
        $patientId = intval($payload['patient_id'] ?? 0);

        if ($appointmentId <= 0 || $patientId <= 0) {
            return [
                'ok' => false,
                'message' => 'Invalid appointment or patient reference.'
            ];
        }

        $discountAmount = $this->sanitizeMoney($payload['discount_amount'] ?? 0);
        $taxPercent = $this->sanitizePercent($payload['tax_percent'] ?? 0);
        $amountTendered = $this->sanitizeMoney($payload['amount_tendered'] ?? 0);

        if ($discountAmount === null || $taxPercent === null || $amountTendered === null) {
            return [
                'ok' => false,
                'message' => 'Invalid billing amount fields.'
            ];
        }

        $paymentMethod = strtoupper(trim((string)($payload['payment_method'] ?? 'CASH')));
        if (!in_array($paymentMethod, ['CASH', 'CARD', 'TRANSFER'], true)) {
            return [
                'ok' => false,
                'message' => 'Invalid payment method.'
            ];
        }

        $referenceNo = trim((string)($payload['reference_no'] ?? ''));
        if (!$this->isValidReferenceNo($referenceNo)) {
            return [
                'ok' => false,
                'message' => 'Reference number contains invalid characters or is too long.'
            ];
        }

        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
        $sanitizedItems = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $testName = trim((string)($item['test_name'] ?? ''));
            if ($testName !== '' && strlen($testName) > 150) {
                return [
                    'ok' => false,
                    'message' => 'Test name is too long.'
                ];
            }

            $quantity = intval($item['quantity'] ?? 1);
            if ($quantity < 1 || $quantity > 1000) {
                return [
                    'ok' => false,
                    'message' => 'Invalid quantity value.'
                ];
            }

            $unitPrice = $this->sanitizeMoney($item['unit_price'] ?? 0);
            if ($unitPrice === null) {
                return [
                    'ok' => false,
                    'message' => 'Invalid unit price value.'
                ];
            }

            $sanitizedItems[] = [
                'test_id' => max(0, intval($item['test_id'] ?? 0)),
                'test_name' => $testName,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'selected' => !empty($item['selected']),
                'is_custom' => !empty($item['is_custom']),
            ];
        }

        return [
            'ok' => true,
            'message' => '',
            'payload' => [
                'appointment_id' => $appointmentId,
                'patient_id' => $patientId,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'amount_tendered' => $amountTendered,
                'payment_method' => $paymentMethod,
                'reference_no' => $referenceNo,
                'items' => $sanitizedItems,
            ]
        ];
    }

    private function sanitizeMoney($value) {
        if (!is_numeric($value)) {
            return null;
        }

        $amount = round(floatval($value), 2);
        if ($amount < 0 || $amount > 100000000) {
            return null;
        }

        return $amount;
    }

    private function sanitizePercent($value) {
        if (!is_numeric($value)) {
            return null;
        }

        $percent = round(floatval($value), 2);
        if ($percent < 0 || $percent > 100) {
            return null;
        }

        return $percent;
    }

    private function isValidReferenceNo($value) {
        if ($value === '') {
            return true;
        }

        if (strlen($value) > 64) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9_\-\/ ]+$/', $value) === 1;
    }
}
