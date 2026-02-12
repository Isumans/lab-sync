<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="main-content-header">
                    <h1>Appointments</h1>
                    <p class="MC-p">Appointments-></p>
                </div>

                <!-- Appointment Header with Stats -->
                <div class="team-header-container">
                    <div class="team-header">
                        <h2>Appointment Management</h2>
                        <button id="openCreateAppointment" class="add-user-button">+ Create Appointment</button>
                    </div>

                    <!-- Stats Cards -->
                    <div class="team-stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="stat-card-team">
                            <div class="stat-label-team">TOTAL APPOINTMENTS</div>
                            <div class="stat-value-team countup" data-target="<?php echo count($appointmentsOnline ?? []) + count($appointmentsPhysical ?? []); ?>">0</div>
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
                    <button class="team-filter-button">↓ Filter</button>
                </div>

                <!-- Appointment Tabs -->
                <div class="nav-bar-container" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="team-tabs">
                        <button class="team-tab active" data-filter="all" onclick="filterAppointments('all', this, event)">All Appointments</button>
                        <button class="team-tab" data-filter="online" onclick="filterAppointments('online', this, event)">Online</button>
                        <button class="team-tab" data-filter="physical" onclick="filterAppointments('physical', this, event)">Physical/Call</button>
                    </div>
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
                    <!-- All Appointments Section -->
                    <div id="all" class="section">
                        <div class="team-table-container">
                            <table class="team-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">APPOINTMENT ID</th>
                                        <th style="width: 25%;">PATIENT ID</th>
                                        <th style="width: 20%;text-align: center;">DATE</th>
                                        <th style="width: 15%;text-align: center;">TIME</th>
                                        <th style="width: 10%;text-align: center;">TYPE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $allAppointments = array_merge(
                                        $appointmentsOnline ?? [],
                                        $appointmentsPhysical ?? []
                                    );
                                    if (!empty($allAppointments)): ?>
                                        <?php foreach ($allAppointments as $appointment): ?>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 40px;">
                                                <p>No appointments found. <a href="#" onclick="document.getElementById('openCreateAppointment').click()">Create your first appointment</a></p>
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
                                        <th style="width: 30%;">APPOINTMENT ID</th>
                                        <th style="width: 25%;">PATIENT ID</th>
                                        <th style="width: 20%;text-align: center;">DATE</th>
                                        <th style="width: 25%;text-align: center;">TIME</th>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" style="text-align: center; padding: 40px;">No online appointments found.</td></tr>
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
                                        <th style="width: 25%;">APPOINTMENT ID</th>
                                        <th style="width: 20%;">PATIENT ID</th>
                                        <th style="width: 20%;text-align: center;">DATE</th>
                                        <th style="width: 15%;text-align: center;">TIME</th>
                                        <th style="width: 20%;text-align: center;">ACTIONS</th>
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
                                                    <div style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                                                        <button type="button" class="action-btn-edit" title="Reschedule" onclick="alert('Reschedule functionality')">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="action-btn-delete" title="Cancel" onclick="alert('Cancel functionality')">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" style="text-align: center; padding: 40px;">No physical appointments found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <script src="/lab_sync/public/js/appointmentPopup.js"></script>
            <script src="/lab_sync/public/js/appointmentFilter.js"></script>
            <script src="/lab_sync/public/js/addTest.js"></script>
            <script src="/lab_sync/public/js/searchPatient.js"></script>
        </body>
</html>