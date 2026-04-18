<?php
if (!function_exists('appointmentDetailsEscape')) {
    function appointmentDetailsEscape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('normalizeAppointmentWorkflowStatus')) {
    function normalizeAppointmentWorkflowStatus($value) {
        $status = strtoupper(trim((string) $value));
        $map = [
            'PENDING' => 'PENDING',
            'NEW' => 'PENDING',
            'IN_PROGRESS' => 'IN_PROGRESS',
            'IN PROGRESS' => 'IN_PROGRESS',
            'PROCESSING' => 'IN_PROGRESS',
            'PROC' => 'IN_PROGRESS',
            'COMPLETED' => 'COMPLETED',
            'COMPLETE' => 'COMPLETED',
            'DONE' => 'COMPLETED',
            'AUTHORIZED' => 'AUTHORIZED',
            'AUTHORISED' => 'AUTHORIZED',
            'APPROVED' => 'AUTHORIZED',
            'PRINTED' => 'PRINTED',
            'PRINT' => 'PRINTED',
        ];

        return $map[$status] ?? 'PENDING';
    }
}

$patientName = $appointment['patient_name'] ?? 'Unknown Patient';
$patientId = $appointment['patient_id'] ?? 'N/A';
$gender = $appointment['gender'] ?? 'N/A';
$contact = $appointment['contact_number'] ?? 'N/A';
$appointmentDateRaw = $appointment['appointment_date'] ?? null;
$appointmentTimeRaw = $appointment['appointment_time'] ?? null;
$appointmentMethod = $appointment['method'] ?? 'N/A';
$appointmentRef = isset($appointment['appointment_id']) ? ('APP-' . $appointment['appointment_id']) : 'N/A';

$formattedDate = 'N/A';
if (!empty($appointmentDateRaw)) {
    $timestamp = strtotime((string) $appointmentDateRaw);
    if ($timestamp !== false) {
        $formattedDate = date('Y-m-d', $timestamp);
    }
}

$formattedTime = 'N/A';
if (!empty($appointmentTimeRaw)) {
    $timestamp = strtotime((string) $appointmentTimeRaw);
    if ($timestamp !== false) {
        $formattedTime = date('h:i A', $timestamp);
    }
}

$ageDisplay = 'N/A';
if (!empty($appointment['date_of_birth'])) {
    try {
        $dob = new DateTime((string) $appointment['date_of_birth']);
        $now = new DateTime();
        $ageDisplay = (string) $dob->diff($now)->y;
    } catch (Exception $e) {
        $ageDisplay = 'N/A';
    }
}

$billNumber    = $billing['bill_number']     ?? ($billing['reference'] ?? 'N/A');
$billDate      = $billing['bill_date']       ?? '';
$subtotal      = isset($billing['subtotal'])       && is_numeric($billing['subtotal'])       ? number_format((float)$billing['subtotal'],       2) : null;
$discountAmt   = isset($billing['discount_amount']) && is_numeric($billing['discount_amount']) ? number_format((float)$billing['discount_amount'], 2) : null;
$taxAmt        = isset($billing['tax_amount'])      && is_numeric($billing['tax_amount'])      ? number_format((float)$billing['tax_amount'],      2) : null;
$totalFee      = isset($billing['total_amount'])    && is_numeric($billing['total_amount'])    ? number_format((float)$billing['total_amount'],    2)
               : (isset($billing['total_fee'])      && is_numeric($billing['total_fee'])       ? number_format((float)$billing['total_fee'],       2) : '0.00');
$paidAmount    = isset($billing['paid_amount'])     && is_numeric($billing['paid_amount'])     ? number_format((float)$billing['paid_amount'],     2) : null;
$balanceDue    = isset($billing['balance_due'])     && is_numeric($billing['balance_due'])     ? number_format((float)$billing['balance_due'],     2) : null;
$paymentStatus = strtoupper((string)($billing['status'] ?? $billing['payment_status'] ?? 'PENDING'));
$billStatusClass = match($paymentStatus) {
    'PAID'           => 'paid',
    'PARTIALLY_PAID' => 'partial',
    'CANCELLED'      => 'cancelled',
    default          => 'pending',
};
?>
<div class="appointment-details-shell">
    <div class="appointment-details-header">
        <div>
            <h2>Appointment Details: #<?php echo appointmentDetailsEscape($appointmentRef); ?></h2>
            <p class="appointment-details-sub">Reference ID: <?php echo appointmentDetailsEscape($appointmentRef); ?></p>
        </div>
    </div>

    <div class="appointment-details-grid">
        <section class="appointment-card appointment-profile">
            <h3>Patient Profile</h3>
            <div class="appointment-card-body">
                <div class="profile-name"><?php echo appointmentDetailsEscape($patientName); ?></div>
                <div class="profile-id">PID: <?php echo appointmentDetailsEscape($patientId); ?></div>
                <div class="profile-meta-grid">
                    <div>
                        <span class="label">Gender</span>
                        <strong><?php echo appointmentDetailsEscape($gender); ?></strong>
                    </div>
                    <div>
                        <span class="label">Age</span>
                        <strong><?php echo appointmentDetailsEscape($ageDisplay); ?></strong>
                    </div>
                    <div>
                        <span class="label">Contact</span>
                        <strong><?php echo appointmentDetailsEscape($contact); ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="appointment-card appointment-tests">
            <h3>Test List and Progress</h3>
            <div class="appointment-card-body">
                <?php if (!empty($tests)): ?>
                    <?php foreach ($tests as $test): ?>
                        <?php $status = normalizeAppointmentWorkflowStatus($test['status'] ?? 'PENDING'); ?>
                        <?php $testId = isset($test['test_id']) ? intval($test['test_id']) : 0; ?>
                        <?php $appointmentId = isset($appointment['appointment_id']) ? intval($appointment['appointment_id']) : 0; ?>
                        <div class="test-row">
                            <div class="test-main">
                                <div class="test-name"><?php echo appointmentDetailsEscape($test['test_name'] ?? 'Unknown Test'); ?></div>
                                <div class="test-category"><?php echo appointmentDetailsEscape($test['category'] ?? 'General'); ?></div>
                            </div>
                            <div class="workflow">
                                <span class="stage <?php echo $status === 'PENDING' ? 'is-active' : ''; ?>">Pending</span>
                                <?php if ($status === 'PENDING' && $testId > 0 && $appointmentId > 0): ?>
                                    <button
                                        type="button"
                                        class="stage stage-proc-action js-proc-stage"
                                        data-appointment-id="<?php echo appointmentDetailsEscape($appointmentId); ?>"
                                        data-test-id="<?php echo appointmentDetailsEscape($testId); ?>"
                                        aria-label="Move test to In Progress"
                                    >
                                        Proc.
                                    </button>
                                <?php else: ?>
                                    <span class="stage <?php echo $status === 'IN_PROGRESS' ? 'is-active' : ''; ?>">Proc.</span>
                                <?php endif; ?>
                                <span class="stage <?php echo $status === 'COMPLETED' ? 'is-active' : ''; ?>">Comp.</span>
                                <span class="stage <?php echo $status === 'AUTHORIZED' ? 'is-active' : ''; ?>">Auth.</span>
                                <?php if ($status === 'AUTHORIZED' && $testId > 0 && $appointmentId > 0): ?>
                                    <button
                                        type="button"
                                        class="stage stage-print-action js-print-stage"
                                        data-appointment-id="<?php echo appointmentDetailsEscape($appointmentId); ?>"
                                        data-test-id="<?php echo appointmentDetailsEscape($testId); ?>"
                                        aria-label="Open report PDF"
                                    >
                                        Print.
                                    </button>
                                <?php else: ?>
                                    <span class="stage <?php echo $status === 'PRINTED' ? 'is-active' : ''; ?>">Print.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="details-empty">No tests linked to this appointment.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="appointment-card appointment-info">
            <h3>Appointment Info</h3>
            <div class="appointment-card-body info-grid">
                <div>
                    <span class="label">Date</span>
                    <strong><?php echo appointmentDetailsEscape($formattedDate); ?></strong>
                </div>
                <div>
                    <span class="label">Time</span>
                    <strong><?php echo appointmentDetailsEscape($formattedTime); ?></strong>
                </div>
                <div>
                    <span class="label">Status</span>
                    <strong><?php echo appointmentDetailsEscape(ucfirst(strtolower((string) $appointmentMethod))); ?></strong>
                </div>
            </div>
        </section>

        <section class="appointment-card appointment-billing">
            <h3>Billing Summary</h3>
            <div class="appointment-card-body billing-body">
                <div class="billing-row">
                    <span class="label">Bill #</span>
                    <strong><?php echo appointmentDetailsEscape($billNumber); ?></strong>
                </div>
                <?php if (!empty($billDate)): ?>
                <div class="billing-row">
                    <span class="label">Bill Date</span>
                    <strong><?php echo appointmentDetailsEscape($billDate); ?></strong>
                </div>
                <?php endif; ?>
                <?php if ($subtotal !== null): ?>
                <div class="billing-row">
                    <span class="label">Subtotal</span>
                    <span>$<?php echo appointmentDetailsEscape($subtotal); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($discountAmt !== null && (float)str_replace(',', '', $discountAmt) > 0): ?>
                <div class="billing-row">
                    <span class="label">Discount</span>
                    <span>-$<?php echo appointmentDetailsEscape($discountAmt); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($taxAmt !== null && (float)str_replace(',', '', $taxAmt) > 0): ?>
                <div class="billing-row">
                    <span class="label">Tax</span>
                    <span>$<?php echo appointmentDetailsEscape($taxAmt); ?></span>
                </div>
                <?php endif; ?>
                <div class="billing-row billing-total-row">
                    <span class="label">Total</span>
                    <div class="amount">$<?php echo appointmentDetailsEscape($totalFee); ?></div>
                </div>
                <?php if ($paidAmount !== null): ?>
                <div class="billing-row">
                    <span class="label">Paid</span>
                    <span>$<?php echo appointmentDetailsEscape($paidAmount); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($balanceDue !== null): ?>
                <div class="billing-row">
                    <span class="label">Balance Due</span>
                    <strong>$<?php echo appointmentDetailsEscape($balanceDue); ?></strong>
                </div>
                <?php endif; ?>
                <div class="billing-row">
                    <span class="label">Status</span>
                    <div class="pill <?php echo appointmentDetailsEscape($billStatusClass); ?>">
                        <?php echo appointmentDetailsEscape(str_replace('_', ' ', $paymentStatus)); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
