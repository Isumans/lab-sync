<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}

$appointmentsOnline = $appointmentsOnline ?? [];
$appointmentsPhysical = $appointmentsPhysical ?? [];
$allAppointments = array_merge($appointmentsOnline, $appointmentsPhysical);
usort($allAppointments, function ($left, $right) {
    $leftTs = strtotime(($left['appointment_date'] ?? '') . ' ' . ($left['appointment_time'] ?? '')) ?: 0;
    $rightTs = strtotime(($right['appointment_date'] ?? '') . ' ' . ($right['appointment_time'] ?? '')) ?: 0;
    return $rightTs <=> $leftTs;
});
$role = $role ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointments</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentPopup.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
</head>
<body>
        <!-- Navigation Bar -->
        <?php require PUBLIC_PATH . '/navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require PUBLIC_PATH . '/sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Appointments</h1>
                    </div>
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div style="color:#067647; margin: 8px 0;"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div style="color:#b42318; margin: 8px 0;"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    <div>
                        <p class="MC-p">Appointments-></p>
                    </div>
                <!-- Appointment Header with Stats -->
                <div class="team-header-container">
                    <div class="team-header">
                        <h2>Appointment Management</h2>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <button class="add-user-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&role=<?php echo urlencode($role ?? ''); ?>">Decisions Report</a></button>
                            <button class="add-user-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=<?php echo urlencode($role); ?>">+ Create Appointment</a></button>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="team-stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="stat-card-team">
                            <div class="stat-label-team">TOTAL APPOINTMENTS</div>
                            <div class="stat-value-team countup" data-target="<?php echo count($allAppointments); ?>">0</div>
                            <div class="stat-change"><span class="countup-percent" data-target="5">0</span>% from last month</div>
                        </div>

                        <div class="stat-card-team">
                            <div class="stat-label-team">UPCOMING THIS WEEK</div>
                            <div class="stat-value-team countup" data-target="<?php echo count($appointmentsPhysical ?? []); ?>">0</div>
                            <div class="stat-change"><span class="countup-percent" data-target="3">0</span>% increase</div>
                        </div>
                    </div>
                </div>

                <!-- Search and Controls -->
                <div class="team-controls">
                    <input type="text" class="team-search-bar" placeholder="🔍 Search Appointments..." id="appointmentSearchInput">
                </div>

                <!-- Create Appointment Modal -->
                <div id="createAppointmentModal" class="modal" aria-hidden="true">
                    <div class="modal-content">
                        <span id="closeModal" class="close">&times;</span>
                        <h2>Create Appointment</h2>
                        <div id="modalMessage"></div>
                        <?php include __DIR__ . '/appointment_form.php'; ?>
                    </div>
                </div>

                <div id="content-area" class="content-area">
                    <div class="section" style="display:block;">
                        <div class="team-table-container">
                            <table class="team-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">APPOINTMENT ID</th>
                                        <th style="width: 15%;">PATIENT ID</th>
                                        <th style="width: 14%;text-align: center;">DATE</th>
                                        <th style="width: 14%;text-align: center;">TIME</th>
                                        <th style="width: 14%;text-align: center;">TYPE</th>
                                        <th style="width: 14%;text-align: center;">BILLING</th>
                                        <th style="width: 14%;text-align: center;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($allAppointments)): ?>
                                        <?php foreach ($allAppointments as $appointment): ?>
                                            <?php
                                                $patientLabel = trim((string) ($appointment['patient_name'] ?? ''));
                                                if ($patientLabel === '' || ctype_digit($patientLabel)) {
                                                    $patientLabel = trim((string) ($appointment['patient_display_name'] ?? ''));
                                                }
                                                if ($patientLabel === '') {
                                                    $patientLabel = (string) ($appointment['patient_id'] ?? 'N/A');
                                                }
                                            ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($appointment['appointment_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($appointment['patient_id']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                                <td style="text-align: center;">
                                                    <span class="status-badge <?php echo isset($appointment['method']) && $appointment['method'] === 'online' ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo isset($appointment['method']) ? ucfirst($appointment['method']) : 'N/A'; ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="billing-action-btn" onclick="window.location.href='/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=<?php echo htmlspecialchars($appointment['appointment_id']); ?>'" title="<?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>">
                                                        <?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>
                                                    </button>
                                                </td>
                                                <td class="user-actions" style="align-items: center; justify-content: center;">
                                                    <button type="button" class="action-btn-edit edit-btn" title="Edit" onclick="alert('Edit functionality coming soon')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-delete delete-btn" title="Delete" onclick="alert('Delete functionality coming soon')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 40px;">
                                                <p>No appointments found. <a href="#" onclick="const modal = document.getElementById('createAppointmentModal'); if (modal) { modal.style.display = 'block'; modal.setAttribute('aria-hidden', 'false'); } return false;">Create your first appointment</a></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
            <script src="/lab_sync/public/js/showSection.js"></script>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Online Appointments Section -->
                    <div id="online" class="section" style="display:none;">
                        <div class="team-table-container">
                            <table class="team-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 17%;">APPOINTMENT ID</th>
                                        <th style="width: 17%;">PATIENT ID</th>
                                        <th style="width: 17%;text-align: center;">DATE</th>
                                        <th style="width: 17%;text-align: center;">TIME</th>
                                        <th style="width: 16%;text-align: center;">BILLING</th>
                                        <th style="width: 16%;text-align: center;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($appointmentsOnline)): ?>
                                        <?php foreach ($appointmentsOnline as $appointment): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($appointment['appointment_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($appointment['patient_id']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="billing-action-btn" onclick="window.location.href='/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=<?php echo htmlspecialchars($appointment['appointment_id']); ?>'" title="<?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>">
                                                        <?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>
                                                    </button>
                                                </td>
                                                <td class="user-actions" style="align-items: center; justify-content: center;">
                                                    <button type="button" class="action-btn-edit edit-btn" title="Edit" onclick="alert('Edit functionality coming soon')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-delete delete-btn" title="Delete" onclick="alert('Delete functionality coming soon')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" style="text-align: center; padding: 40px;">No online appointments found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Physical/Call Appointments Section -->
                    <div id="physical" class="section" style="display:none;">
                        <div class="team-table-container">
                            <table class="team-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 17%;">APPOINTMENT ID</th>
                                        <th style="width: 17%;">PATIENT ID</th>
                                        <th style="width: 17%;text-align: center;">DATE</th>
                                        <th style="width: 17%;text-align: center;">TIME</th>
                                        <th style="width: 16%;text-align: center;">BILLING</th>
                                        <th style="width: 16%;text-align: center;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($appointmentsPhysical)): ?>
                                        <?php foreach ($appointmentsPhysical as $appointment): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($appointment['appointment_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($appointment['patient_id']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="billing-action-btn" onclick="window.location.href='/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=<?php echo htmlspecialchars($appointment['appointment_id']); ?>'" title="<?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>">
                                                        <?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>
                                                    </button>
                                                </td>
                                                <td class="user-actions" style="align-items: center; justify-content: center;">
                                                    <button type="button" class="action-btn-edit edit-btn" title="Edit" onclick="alert('Edit functionality coming soon')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-delete delete-btn" title="Delete" onclick="alert('Delete functionality coming soon')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" style="text-align: center; padding: 40px;">No physical appointments found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <script src="/lab_sync/public/js/appointmentPopup.js"></script>
            <script src="/lab_sync/public/js/addTest.js"></script>
            <script src="/lab_sync/public/js/searchPatient.js"></script>
        </body>
</html>
