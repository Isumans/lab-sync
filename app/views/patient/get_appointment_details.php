<?php
if (!function_exists('patientAppointmentDetailsEscape')) {
    function patientAppointmentDetailsEscape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('normalizePatientWorkflowStatus')) {
    function normalizePatientWorkflowStatus($value) {
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

$patientName = $appointment['patient_name'] ?? 'Patient';
$patientId = $appointment['patient_id'] ?? 'N/A';
$gender = $appointment['gender'] ?? 'N/A';
$contact = $appointment['contact_number'] ?? 'N/A';
$appointmentRef = isset($appointment['appointment_id']) ? ('APP-' . str_pad((string)intval($appointment['appointment_id']), 4, '0', STR_PAD_LEFT)) : 'N/A';

$appointmentDateRaw = $appointment['appointment_date'] ?? null;
$appointmentTimeRaw = $appointment['appointment_time'] ?? null;
$status = (string)($appointment['appointment_status'] ?? 'Pending');

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
        $dob = new DateTime((string)$appointment['date_of_birth']);
        $now = new DateTime();
        $ageDisplay = (string)$dob->diff($now)->y;
    } catch (Exception $e) {
        $ageDisplay = 'N/A';
    }
}

$totalFee = number_format((float)($appointment['total_price'] ?? 0), 2);
$paymentStatus = strtoupper((string)($billing['status'] ?? 'PENDING'));
$billStatusClass = match($paymentStatus) {
    'PAID' => 'paid',
    'PARTIALLY_PAID' => 'partial',
    'CANCELLED' => 'cancelled',
    default => 'pending',
};

if (empty($tests)) {
    $summaryRaw = trim((string)($appointment['tests_summary'] ?? ''));
    if ($summaryRaw !== '') {
        $names = array_filter(array_map('trim', explode(',', $summaryRaw)), static fn($v) => $v !== '');
        if (!empty($names)) {
            $fallbackStatus = normalizePatientWorkflowStatus($appointment['appointment_status'] ?? 'PENDING');
            $tests = array_map(static function ($name) use ($fallbackStatus) {
                return [
                    'test_name' => $name,
                    'category' => 'General',
                    'status' => $fallbackStatus,
                ];
            }, array_values(array_unique($names)));
        }
    }
}
?>
<div class="appointment-details-shell">
    <div class="appointment-details-header">
        <div>
            <h2>Appointment Details: #<?php echo patientAppointmentDetailsEscape($appointmentRef); ?></h2>
            <p class="appointment-details-sub">Reference ID: <?php echo patientAppointmentDetailsEscape($appointmentRef); ?></p>
        </div>
    </div>

    <div class="appointment-details-grid">
        <section class="appointment-card appointment-profile">
            <h3>Patient Profile</h3>
            <div class="appointment-card-body">
                <div class="profile-name"><?php echo patientAppointmentDetailsEscape($patientName); ?></div>
                <div class="profile-id">PID: <?php echo patientAppointmentDetailsEscape($patientId); ?></div>
                <div class="profile-meta-grid">
                    <div>
                        <span class="label">Gender</span>
                        <strong><?php echo patientAppointmentDetailsEscape($gender ?: 'N/A'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Age</span>
                        <strong><?php echo patientAppointmentDetailsEscape($ageDisplay); ?></strong>
                    </div>
                    <div>
                        <span class="label">Contact</span>
                        <strong><?php echo patientAppointmentDetailsEscape($contact ?: 'N/A'); ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="appointment-card appointment-tests">
            <h3>Test List and Progress</h3>
            <div class="appointment-card-body">
                <?php if (!empty($tests)): ?>
                    <?php foreach ($tests as $test): ?>
                        <?php $workflowStatus = normalizePatientWorkflowStatus($test['status'] ?? 'PENDING'); ?>
                        <div class="test-row">
                            <div class="test-main">
                                <div class="test-name"><?php echo patientAppointmentDetailsEscape($test['test_name'] ?? 'Unknown Test'); ?></div>
                                <span class="test-category-badge"><?php echo patientAppointmentDetailsEscape($test['category'] ?? 'General'); ?></span>
                            </div>
                            <div class="workflow">
                                <span class="stage <?php echo $workflowStatus === 'PENDING' ? 'is-active' : ''; ?>">Pending</span>
                                <span class="stage <?php echo $workflowStatus === 'IN_PROGRESS' ? 'is-active' : ''; ?>">Proc.</span>
                                <span class="stage <?php echo $workflowStatus === 'COMPLETED' ? 'is-active' : ''; ?>">Comp.</span>
                                <span class="stage <?php echo $workflowStatus === 'AUTHORIZED' ? 'is-active' : ''; ?>">Auth.</span>
                                <span class="stage <?php echo $workflowStatus === 'PRINTED' ? 'is-active' : ''; ?>">Print.</span>
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
                    <strong><?php echo patientAppointmentDetailsEscape($formattedDate); ?></strong>
                </div>
                <div>
                    <span class="label">Time</span>
                    <strong><?php echo patientAppointmentDetailsEscape($formattedTime); ?></strong>
                </div>
                <div>
                    <span class="label">Status</span>
                    <strong><?php echo patientAppointmentDetailsEscape($status); ?></strong>
                </div>
            </div>
        </section>

        <section class="appointment-card appointment-billing">
            <h3>Billing Summary</h3>
            <div class="appointment-card-body billing-body">
                <div class="billing-row billing-total-row">
                    <span class="label">Estimated Total</span>
                    <div class="amount">LKR <?php echo patientAppointmentDetailsEscape($totalFee); ?></div>
                </div>
                <div class="billing-row">
                    <span class="label">Status</span>
                    <div class="pill <?php echo patientAppointmentDetailsEscape($billStatusClass); ?>">
                        <?php echo patientAppointmentDetailsEscape(str_replace('_', ' ', $paymentStatus)); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
