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
    <link rel="stylesheet" href="/lab_sync/public/appointmentFormStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentPopup.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentDetailsModal.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentEditModal.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentDeleteModal.css">
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
                <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Appointments</h1>
                        <div style="display:flex; gap:8px;">
                            <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&role=<?php echo urlencode($role ?? ''); ?>">Decisions Report</a></button>
                            <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue&role=<?php echo urlencode($role ?? ''); ?>">Prescription Queue</a></button>
                            <button class="add-test-button" ><a href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=<?php echo $role; ?>">Create Appointment</a></button>
                        </div>
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
                    <div class="heading-row">
                        <h2 class="heading3">Online Appointment </h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Tests</th>
                                        <th>Items</th>
                                        <th>Total (LKR)</th>
                                        <th>Home Collection</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointmentsOnline as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo $appointment['patient_id']; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['tests_summary'] ?? ($appointment['test_name'] ?? ('Test #' . $appointment['test_id']))); ?></td>
                                        <td><?php echo htmlspecialchars((string)($appointment['item_count'] ?? 1)); ?></td>
                                        <td><?php echo htmlspecialchars(number_format((float)($appointment['total_price'] ?? $appointment['test_price'] ?? 0), 2)); ?></td>
                                        <td><?php echo !empty($appointment['home_collection']) ? 'Yes' : 'No'; ?><?php if (!empty($appointment['collection_address'])): ?><div style="font-size:12px; color:#667085;"><?php echo htmlspecialchars($appointment['collection_address']); ?></div><?php endif; ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_status'] ?? 'Pending'); ?></td>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['appointment_time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>

                <!-- Appointment Header with Stats -->
                <div class="team-header-container">
                    <div class="team-header">
                        <h2>Appointment Management</h2>
                        <button  class="add-user-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment">+ Create Appointment</a></button>
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

                <div id="appointmentDetailsModal" class="appointment-details-modal" aria-hidden="true">
                    <div class="appointment-details-dialog" role="dialog" aria-modal="true" aria-labelledby="appointmentDetailsTitle">
                        <div class="appointment-details-topbar">
                            <div id="appointmentDetailsTitle" class="appointment-details-title">Appointment Details</div>
                            <button id="appointmentDetailsClose" class="appointment-details-close" type="button" aria-label="Close details">&times;</button>
                        </div>
                        <div id="appointmentDetailsBody" class="appointment-details-body"></div>
                    </div>
                </div>

                <div id="editAppointmentModal" class="appointment-edit-modal" aria-hidden="true">
                    <div class="appointment-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="editAppointmentTitle">
                        <form id="editAppointmentForm" novalidate>
                            <input type="hidden" id="editAppointmentId" name="appointment_id" value="">

                            <div class="appointment-edit-header">
                                <div>
                                    <h2 id="editAppointmentTitle">Edit Appointment: #APP-000000</h2>
                                    <p class="appointment-edit-subtitle">CLINICAL PROCEDURE UPDATE</p>
                                </div>
                                <button id="editAppointmentClose" type="button" class="appointment-edit-close" aria-label="Close edit modal">&times;</button>
                            </div>

                            <div id="editAppointmentAlert" class="appointment-edit-alert" hidden></div>

                            <div class="appointment-edit-body">
                                <section class="edit-section-card">
                                    <div class="edit-section-title">
                                        <span class="section-icon" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M8 8.5C9.38071 8.5 10.5 7.38071 10.5 6C10.5 4.61929 9.38071 3.5 8 3.5C6.61929 3.5 5.5 4.61929 5.5 6C5.5 7.38071 6.61929 8.5 8 8.5Z" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M3 13C3 10.7909 5.23858 9 8 9C10.7614 9 13 10.7909 13 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <h3>Patient Information</h3>
                                    </div>

                                    <div class="patient-readonly-card">
                                        <div class="patient-identity">
                                            <span class="patient-avatar" aria-hidden="true">
                                                <svg width="18" height="18" viewBox="0 0 16 16" fill="none">
                                                    <path d="M8 8.5C9.38071 8.5 10.5 7.38071 10.5 6C10.5 4.61929 9.38071 3.5 8 3.5C6.61929 3.5 5.5 4.61929 5.5 6C5.5 7.38071 6.61929 8.5 8 8.5Z" stroke="currentColor" stroke-width="1.4"/>
                                                    <path d="M3 13C3 10.7909 5.23858 9 8 9C10.7614 9 13 10.7909 13 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                </svg>
                                            </span>
                                            <div>
                                                <p id="editPatientName" class="patient-name">Patient Name</p>
                                                <p id="editPatientPid" class="patient-pid">PID: N/A</p>
                                            </div>
                                        </div>
                                        <span class="readonly-badge">READ-ONLY</span>
                                    </div>
                                </section>

                                <section class="edit-section-card">
                                    <div class="edit-section-title">
                                        <span class="section-icon" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M4 1.75V3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                <path d="M12 1.75V3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                <rect x="2.5" y="3" width="11" height="10.5" rx="2" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M2.5 5.75H13.5" stroke="currentColor" stroke-width="1.4"/>
                                            </svg>
                                        </span>
                                        <h3>Schedule Details</h3>
                                        <span class="today-pill">TODAY</span>
                                    </div>

                                    <div class="schedule-grid">
                                        <div>
                                            <label class="edit-label" for="editAppointmentDate">Appointment Date</label>
                                            <div class="date-input-wrap">
                                                <span class="date-icon" aria-hidden="true">📅</span>
                                                <input type="date" id="editAppointmentDate" name="appointment_date" required>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="edit-label">Select Time</label>
                                            <div class="time-slot-grid" id="editTimeSlots">
                                                <button type="button" class="time-slot" data-time="NOW">NOW</button>
                                                <button type="button" class="time-slot" data-time="08:00:00">08:00 AM</button>
                                                <button type="button" class="time-slot" data-time="09:30:00">09:30 AM</button>
                                                <button type="button" class="time-slot" data-time="11:00:00">11:00 AM</button>
                                                <button type="button" class="time-slot" data-time="13:30:00">01:30 PM</button>
                                                <button type="button" class="time-slot" data-time="15:00:00">03:00 PM</button>
                                                <button type="button" class="time-slot" data-time="16:30:00">04:30 PM</button>
                                            </div>
                                            <input type="hidden" id="editAppointmentTime" name="appointment_time" value="">
                                        </div>
                                    </div>
                                </section>

                                <section class="edit-section-card">
                                    <div class="edit-section-title">
                                        <span class="section-icon" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M6.9 11.2C9.2196 11.2 11.1 9.3196 11.1 7C11.1 4.68041 9.2196 2.8 6.9 2.8C4.58041 2.8 2.7 4.68041 2.7 7C2.7 9.3196 4.58041 11.2 6.9 11.2Z" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M10.4 10.5L13.3 13.4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <h3>Test Selection</h3>
                                    </div>

                                    <div class="test-search-wrap">
                                        <span class="search-icon" aria-hidden="true">🔍</span>
                                        <input type="search" id="editTestSearch" placeholder="Search test catalog..." autocomplete="off">
                                    </div>

                                    <div id="editTestSearchResults" class="test-search-results" hidden></div>

                                    <div class="test-tag-row">
                                        <div id="editSelectedTests" class="test-tags"></div>
                                        <button type="button" id="editAddTestBtn" class="add-new-test-btn">+ ADD NEW</button>
                                    </div>
                                </section>

                                <section class="edit-section-card">
                                    <div class="edit-section-title">
                                        <span class="section-icon" aria-hidden="true">
                                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                                                <path d="M3 2.75H13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                <path d="M3 6.75H13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                <path d="M3 10.75H9" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                        <h3>Reason for Visit / Clinical Notes</h3>
                                    </div>

                                    <textarea id="editAppointmentReason" name="reason" rows="5" placeholder="Enter updated clinical notes or reasons for modification..."></textarea>
                                </section>
                            </div>

                            <div class="appointment-edit-footer">
                                <button type="button" id="editAppointmentCancel" class="edit-cancel-btn">CANCEL</button>
                                <button type="submit" id="editAppointmentSubmit" class="edit-submit-btn">
                                    <span aria-hidden="true">💾</span>
                                    Update Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="deleteAppointmentModal" class="appointment-delete-modal" aria-hidden="true"> 
                    <div class="appointment-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="deleteAppointmentTitle"> 
                        <div class="appointment-delete-header"> 
                            <div class="delete-icon-wrap" aria-hidden="true">
                                !
                            </div> 
                            <h2 id="deleteAppointmentTitle">
                                Delete Appointment
                            </h2> 
                            <button type="button" id="deleteAppointmentClose" class="appointment-delete-close" aria-label="Close delete modal">
                                &times;
                            </button> 
                        </div>
                            <p class="appointment-delete-copy">
                                Are you sure you want to delete this appointment? This action cannot be undone.
                            </p>
                            <div id="deleteAppointmentAlert" class="appointment-delete-alert" hidden>

                            </div>
                            <div class="appointment-delete-summary">
                                <div class="summary-label">APPOINTMENT ID</div>
                                <div id="deleteAppointmentNumber" class="summary-value">#APP-0000</div>

                                <div class="summary-label">PATIENT NAME</div>
                                <div id="deleteAppointmentPatient" class="summary-value">Unknown Patient</div>
                            </div>
                            <button type="button" id="deleteAppointmentConfirm" class="delete-confirm-btn">
                            Delete Appointment
                        </button>

                        <button type="button" id="deleteAppointmentCancel" class="delete-cancel-btn">
                            Cancel
                        </button>
                        <div class="appointment-delete-footer-note">
                            SYSTEM: AUTHORIZATION REQUIRED
                        </div>
                    </div>
                        
                    </div>





                <div id="appointmentEditToast" class="appointment-edit-toast" aria-live="polite" hidden></div>

                <div id="content-area" class="content-area">
                    <!-- All Appointments Section -->
                    <div id="all" class="section">
                        <div class="team-table-container">
                            <table class="team-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 15%;">APPOINTMENT ID</th>
                                        <th style="width: 15%;">PATIENT NAME</th>
                                        <th style="width: 14%;text-align: center;">DATE</th>
                                        <th style="width: 14%;text-align: center;">TIME</th>
                                        <th style="width: 14%;text-align: center;">TYPE</th>
                                        <th style="width: 14%;text-align: center;">BILLING</th>
                                        <th style="width: 14%;text-align: center;">ACTIONS</th>
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
                                            <?php $patientLabel = $appointment['patient_name'] ?? ($appointment['patient_display_name'] ?? ($appointment['patient_id'] ?? 'N/A')); ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($appointment['appointment_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars((string) $patientLabel); ?></td>
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
                                                    <button type="button" class="action-btn-view view-btn js-view-details-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" title="View Details">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-edit edit-btn js-edit-appointment-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" title="Edit">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-delete delete-btn js-delete-appointment-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" data-patient-name="<?php echo htmlspecialchars((string) $patientLabel); ?>" title="Delete">
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
                                                <p>No appointments found. <a href="#" onclick="document.getElementById('openCreateAppointment').click()">Create your first appointment</a></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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
                                        <th style="width: 17%;">PATIENT NAME</th>
                                        <th style="width: 17%;text-align: center;">DATE</th>
                                        <th style="width: 17%;text-align: center;">TIME</th>
                                        <th style="width: 16%;text-align: center;">BILLING</th>
                                        <th style="width: 16%;text-align: center;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($appointmentsOnline)): ?>
                                        <?php foreach ($appointmentsOnline as $appointment): ?>
                                            <?php $patientLabel = $appointment['patient_name'] ?? ($appointment['patient_display_name'] ?? ($appointment['patient_id'] ?? 'N/A')); ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($appointment['appointment_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars((string) $patientLabel); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="billing-action-btn" onclick="window.location.href='/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=<?php echo htmlspecialchars($appointment['appointment_id']); ?>'" title="<?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>">
                                                        <?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>
                                                    </button>
                                                </td>
                                                <td class="user-actions" style="align-items: center; justify-content: center;">
                                                    <button type="button" class="action-btn-view view-btn js-view-details-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" title="View Details">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-edit edit-btn js-edit-appointment-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" title="Edit">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-delete delete-btn js-delete-appointment-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" data-patient-name="<?php echo htmlspecialchars((string) $patientLabel); ?>" title="Delete">
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
                                        <th style="width: 17%;">PATIENT NAME</th>
                                        <th style="width: 17%;text-align: center;">DATE</th>
                                        <th style="width: 17%;text-align: center;">TIME</th>
                                        <th style="width: 16%;text-align: center;">BILLING</th>
                                        <th style="width: 16%;text-align: center;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($appointmentsPhysical)): ?>
                                        <?php foreach ($appointmentsPhysical as $appointment): ?>
                                            <?php $patientLabel = $appointment['patient_name'] ?? ($appointment['patient_display_name'] ?? ($appointment['patient_id'] ?? 'N/A')); ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($appointment['appointment_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars((string) $patientLabel); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                                <td style="text-align: center;"><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="billing-action-btn" onclick="window.location.href='/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=<?php echo htmlspecialchars($appointment['appointment_id']); ?>'" title="<?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>">
                                                        <?php echo (isset($appointment['bill_id']) && $appointment['bill_id']) ? 'View Bill' : 'Create Bill'; ?>
                                                    </button>
                                                </td>
                                                <td class="user-actions" style="align-items: center; justify-content: center;">
                                                    <button type="button" class="action-btn-view view-btn js-view-details-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" title="View Details">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            <path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-edit edit-btn js-edit-appointment-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" title="Edit">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="action-btn-delete delete-btn js-delete-appointment-btn" data-appointment-id="<?php echo htmlspecialchars($appointment['appointment_id']); ?>" data-patient-name="<?php echo htmlspecialchars((string) $patientLabel); ?>" title="Delete" >
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
            <script src="/lab_sync/public/js/appointmentFilter.js"></script>
            <script src="/lab_sync/public/js/addTest.js"></script>
            <script src="/lab_sync/public/js/showSection.js"></script>
            <script src="/lab_sync/public/js/searchPatient.js"></script>
            <script src="/lab_sync/public/js/appointmentForm.js"></script>
            <script src="/lab_sync/public/js/appointmentDetailsModal.js"></script>
            <script src="/lab_sync/public/js/appointmentEditModal.js"></script>
            <script src="/lab_sync/public/js/appointmentDeleteModal.js"></script>
        </body>
</html>