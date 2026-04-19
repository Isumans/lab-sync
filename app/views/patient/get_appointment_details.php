<?php
if (!function_exists('patientAppointmentDetailsEscape')) {
    function patientAppointmentDetailsEscape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
            <h3>Test List</h3>
            <div class="appointment-card-body">
                <?php if (!empty($tests)): ?>
                    <?php foreach ($tests as $test): ?>
                        <div class="test-row">
                            <div class="test-main">
                                <div class="test-name"><?php echo patientAppointmentDetailsEscape($test['test_name'] ?? 'Unknown Test'); ?></div>
                                <div class="test-category"><?php echo patientAppointmentDetailsEscape($test['category'] ?? 'General'); ?></div>
                            </div>
                            <div class="workflow">
                                <span class="stage is-active"><?php echo patientAppointmentDetailsEscape(strtoupper((string)($test['status'] ?? 'PENDING'))); ?></span>
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
