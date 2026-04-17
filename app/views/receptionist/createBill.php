<?php
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}

$payload = isset($appointmentPayload) && is_array($appointmentPayload) ? $appointmentPayload : null;
$appointment = $payload['appointment'] ?? null;
$tests = $payload['tests'] ?? [];
$bill = isset($existingBill) && is_array($existingBill) ? $existingBill : null;

$safeAppointmentId = intval($_GET['appointment_id'] ?? 0);
$patientId = intval($appointment['patient_id'] ?? 0);
$patientName = (string) ($appointment['patient_name'] ?? ('Patient #' . $patientId));
$visitType = ucfirst((string) ($appointment['method'] ?? 'physical'));
$appointmentDate = (string) ($appointment['appointment_date'] ?? '');

$billItems = [];
if ($bill && !empty($bill['items'])) {
    foreach ($bill['items'] as $item) {
        $billItems[] = [
            'test_id' => intval($item['test_id'] ?? 0),
            'test_name' => (string) ($item['test_name'] ?? ''),
            'unit_price' => floatval($item['unit_price'] ?? 0),
            'quantity' => max(1, intval($item['quantity'] ?? 1)),
            'selected' => true,
            'is_custom' => ((string) ($item['notes'] ?? '')) === 'CUSTOM_ITEM',
        ];
    }
} else {
    foreach ($tests as $test) {
        $billItems[] = [
            'test_id' => intval($test['test_id'] ?? 0),
            'test_name' => (string) ($test['test_name'] ?? ''),
            'unit_price' => floatval($test['price'] ?? 0),
            'quantity' => 1,
            'selected' => true,
            'is_custom' => false,
        ];
    }
}

$initialDiscount = floatval($bill['discount_amount'] ?? 0);
$initialTaxPercent = 0.0;
$subtotalFromBill = floatval($bill['subtotal'] ?? 0);
$taxAmountFromBill = floatval($bill['tax_amount'] ?? 0);
if ($subtotalFromBill > 0) {
    $initialTaxPercent = round(($taxAmountFromBill / $subtotalFromBill) * 100, 2);
}

$existingStatus = strtoupper((string) ($bill['status'] ?? 'PENDING'));
$isLockedPaid = $existingStatus === 'PAID';
$isPartialPayment = $existingStatus === 'PARTIALLY_PAID';

$initialAmountTendered = 0.0;
if (!$isLockedPaid && !$isPartialPayment) {
    $initialAmountTendered = floatval($bill['paid_amount'] ?? 0);
}

$initialPaymentMethod = 'CASH';
if ($bill && !empty($bill['payments'][0]['payment_method'])) {
    $initialPaymentMethod = strtoupper((string) $bill['payments'][0]['payment_method']);
}
$initialReference = $bill['payments'][0]['reference_number'] ?? '';

$bootstrap = [
    'appointment_id' => $safeAppointmentId,
    'patient_id' => $patientId,
    'bill_id' => intval($bill['bill_id'] ?? 0),
    'patient_name' => $patientName,
    'appointment_date' => $appointmentDate,
    'visit_type' => $visitType,
    'items' => $billItems,
    'discount_amount' => $initialDiscount,
    'tax_percent' => $initialTaxPercent,
    'amount_tendered' => $initialAmountTendered,
    'payment_method' => $initialPaymentMethod,
    'reference_no' => (string) $initialReference,
    'status' => (string) ($bill['status'] ?? 'PENDING'),
    'bill_number' => (string) ($bill['bill_number'] ?? ''),
    'total_amount' => floatval($bill['total_amount'] ?? 0),
    'subtotal' => floatval($bill['subtotal'] ?? 0),
    'paid_amount' => floatval($bill['paid_amount'] ?? 0),
    'balance_due' => floatval($bill['balance_due'] ?? 0),
    'payments' => isset($bill['payments']) && is_array($bill['payments']) ? $bill['payments'] : [],
];
?>
<html>
<head>

    <title>Appointment Billing</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/billingStyles.css">
</head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                 <div class="Tmain-content">
                    <?php
                        $pageTitle = 'Appointment Billing';
                        $pageBreadcrumbText = 'Appointments->Create Bill';
                        $pageActionHtml = '<a href="/lab_sync/index.php?controller=appointmentsController&action=index&role=receptionist" class="add-user-button">Back to Appointments</a>';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>
                    <?php if ($appointment === null || $safeAppointmentId <= 0): ?>
                        <div class="billing-empty-state">
                            <h3>Appointment not found</h3>
                            <p>Select a valid appointment from the appointment table and click Create Bill again.</p>
                        </div>
                    <?php else: ?>
                    <div class="billingFormArea" id="billingApp">
                        <section class="billing-left-panel">
                            <div class="billing-patient-header">
                                <div class="billing-patient-meta">
                                    <h3><?php echo htmlspecialchars($patientName); ?></h3>
                                    <p><?php echo htmlspecialchars($appointmentDate); ?> • <?php echo htmlspecialchars($visitType); ?></p>
                                </div>
                                <span class="billing-status-badge" id="billingStatusBadge">STATUS <?php echo htmlspecialchars(str_replace('_', ' ', $existingStatus)); ?></span>
                            </div>

                            <div class="billing-card">
                                <div class="billing-card-head">
                                    <h4>Billable Items</h4>
                                    <button type="button" class="billing-add-item" id="addCustomItemBtn">+ Add Custom Item</button>
                                </div>
                                <div class="billing-table-wrap">
                                    <table class="billing-items-table">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Test Name</th>
                                                <th>Unit Price</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="billItemsBody"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="billing-card">
                                <h4>Payment Settlement</h4>
                                <div class="billing-summary-row">
                                    <span>Previously Paid</span>
                                    <strong id="previouslyPaidValue">0.00</strong>
                                </div>
                                <div class="billing-summary-row">
                                    <span>Remaining Payable</span>
                                    <strong id="remainingPayableValue">0.00</strong>
                                </div>
                                <div class="payment-method-grid" id="paymentMethodGrid">
                                    <button type="button" class="payment-method-card" data-method="CASH">Cash</button>
                                    <button type="button" class="payment-method-card" data-method="CARD">Card</button>
                                    <button type="button" class="payment-method-card" data-method="TRANSFER">Transfer</button>
                                </div>
                                <div class="billing-field-grid">
                                    <label>
                                        New Payment Amount (LKR)
                                        <input type="number" id="amountTenderedInput" min="0" step="0.01" inputmode="decimal">
                                    </label>
                                    <label>
                                        Reference Number (Optional)
                                        <input type="text" id="referenceNumberInput" placeholder="TXN12345678" maxlength="64" pattern="[A-Za-z0-9_\-\/ ]*" title="Use letters, numbers, space, slash, hyphen, or underscore.">
                                    </label>
                                </div>
                            </div>

                            <div class="billing-focus-bar">
                                <div>
                                    <span>Total Due</span>
                                    <strong id="focusTotalDue">0.00</strong>
                                </div>
                                <div>
                                    <span>Amount Paid</span>
                                    <strong id="focusAmountPaid">0.00</strong>
                                </div>
                                <div>
                                    <span>Balance to be Paid</span>
                                    <strong id="focusBalanceDue">0.00</strong>
                                </div>
                            </div>
                        </section>

                        <aside class="billing-right-panel">
                            <div class="billing-summary-card">
                                <h4>Financial Summary</h4>
                                <div class="billing-summary-row"><span>Subtotal</span><strong id="summarySubtotal">0.00</strong></div>
                                <div class="billing-summary-row billing-inline-input">
                                    <span>Discount</span>
                                    <input type="number" id="discountInput" min="0" step="0.01" value="0" inputmode="decimal">
                                </div>
                                <div class="billing-summary-row billing-inline-input">
                                    <span>Tax (%)</span>
                                    <input type="number" id="taxPercentInput" min="0" max="100" step="0.01" value="0" inputmode="decimal">
                                </div>
                                <div class="billing-summary-row grand-total"><span>Grand Total</span><strong id="summaryGrandTotal">0.00</strong></div>
                                <button type="button" class="billing-primary-btn" id="saveAndPrintBtn">Save & Print Invoice</button>
                                <button type="button" class="billing-secondary-btn" id="saveDraftBtn">Save as Draft</button>
                                <button type="button" class="billing-link-btn" id="cancelBillingBtn">Cancel</button>
                                <p class="billing-note" id="billingFormMessage"></p>
                            </div>
                        </aside>
                    </div>
                    <script>
                        window.__BILLING_BOOTSTRAP = <?php echo json_encode($bootstrap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
                    </script>
                    <script src="/lab_sync/public/js/createBill.js?v=20260414c"></script>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </body>
</html>