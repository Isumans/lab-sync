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

$paymentStatus = strtoupper((string) ($billing['payment_status'] ?? 'PENDING'));
$totalFee = isset($billing['total_fee']) && is_numeric($billing['total_fee']) ? number_format((float) $billing['total_fee'], 2) : '0.00';
$billingRef = $billing['reference'] ?? 'N/A';
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
                        <?php $testCategory = $test['category'] ?? ($test['department'] ?? 'General'); ?>
                        <div class="test-row">
                            <div class="test-main">
                                <div class="test-name"><?php echo appointmentDetailsEscape($test['test_name'] ?? 'Unknown Test'); ?></div>
                                <div class="test-category"><?php echo appointmentDetailsEscape($testCategory); ?></div>
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
                                <span class="stage <?php echo $status === 'PRINTED' ? 'is-active' : ''; ?>">Print.</span>
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
                <div>
                    <span class="label">Total Fee</span>
                    <div class="amount">$<?php echo appointmentDetailsEscape($totalFee); ?></div>
                </div>
                <div>
                    <span class="label">Payment</span>
                    <div class="pill <?php echo $paymentStatus === 'PAID' ? 'paid' : 'pending'; ?>">
                        <?php echo appointmentDetailsEscape($paymentStatus); ?>
                    </div>
                    <div class="billing-ref">Ref: <?php echo appointmentDetailsEscape($billingRef); ?></div>
                </div>
            </div>
        </section>
    </div>
</div>
