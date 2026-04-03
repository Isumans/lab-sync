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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointments</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
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
        <?php require __DIR__ . '/../../../public/navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require __DIR__ . '/../../../public/sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <?php
                    $pageTitle = 'Appointments';
                    $pageBreadcrumbText = 'Appointments->';
                    $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment">+ Create Appointment</a>';
                    require __DIR__ . '/../../../public/partials/page-header.php';
                ?>


                <section class="rd-filter-card" aria-label="Search and Filters">
                    <div class="rd-filter-grid">
                        <div class="rd-filter-field rd-filter-field-search">
                            <label for="aptSearch">Search Appointments</label>
                            <input id="aptSearch" type="text" placeholder="Search by Patient Name, Appointment ID, or Type..." />
                        </div>

                        <div class="rd-filter-field">
                            <label for="aptMethod">Appointment Type</label>
                            <select id="aptMethod">
                                <option value="all">All Appointments</option>
                                <option value="online">Online</option>
                                <option value="physical">Physical/Call</option>
                            </select>
                        </div>

                        <div class="rd-filter-field">
                            <label for="aptSortBy">Sort By</label>
                            <select id="aptSortBy">
                                <option value="appointment_date">Date</option>
                                <option value="appointment_time">Time</option>
                                <option value="patient_name">Patient Name</option>
                                <option value="appointment_id">Appointment ID</option>
                                <option value="method">Type</option>
                            </select>
                        </div>
                    </div>

                    <div class="rd-filter-bottom-row">
                        <div class="rd-filter-date-range">
                            <div class="rd-filter-field">
                                <label for="aptDateFrom">Date Range</label>
                                <input id="aptDateFrom" type="date" />
                            </div>
                            <div class="rd-filter-field rd-filter-field-to">
                                <label for="aptDateTo" class="rd-hidden-label">End Date</label>
                                <input id="aptDateTo" type="date" />
                            </div>
                        </div>

                        <div class="rd-sort-direction-wrap">
                            <select id="aptSortDir" class="rd-sort-direction" aria-label="Sort direction">
                                <option value="desc">Newest First</option>
                                <option value="asc">Oldest First</option>
                            </select>
                            <button type="button" class="rd-clear-btn" id="aptClearBtn">Clear All Filters</button>
                        </div>
                    </div>
                </section>

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
                                <div class="summary-label">ID</div>
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

                <section class="rd-table-card" aria-label="Appointments Table">
                    <div class="rd-table-wrap">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th class="rd-sortable is-active" data-sort="appointment_id" data-direction="desc">Appointment ID</th>
                                    <th class="rd-sortable" data-sort="patient_name" data-direction="asc">Patient Name</th>
                                    <th class="rd-sortable" data-sort="appointment_date" data-direction="desc">Date</th>
                                    <th class="rd-sortable" data-sort="appointment_time" data-direction="desc">Time</th>
                                    <th class="rd-sortable" data-sort="method" data-direction="asc">Type</th>
                                    <th class="rd-th-right">Billing</th>
                                    <th class="rd-th-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="aptTableBody"></tbody>
                        </table>
                    </div>

                    <div class="rd-table-footer">
                        <p id="aptShowingText">Showing 0-0 of 0 appointments</p>
                        <div class="rd-pagination" id="aptPagination"></div>
                    </div>
                </section>
            </main>
            <script src="/lab_sync/public/js/appointmentPopup.js"></script>
            <script src="/lab_sync/public/js/addTest.js"></script>
            <script src="/lab_sync/public/js/showSection.js"></script>
            <script src="/lab_sync/public/js/showSection.js"></script>
            <script src="/lab_sync/public/js/searchPatient.js"></script>
            <script src="/lab_sync/public/js/appointmentForm.js"></script>
            <script src="/lab_sync/public/js/appointmentDetailsModal.js"></script>
            <script src="/lab_sync/public/js/appointmentEditModal.js"></script>
            <script src="/lab_sync/public/js/appointmentDeleteModal.js"></script>
            <script src="/lab_sync/public/js/appointmentForm.js"></script>
            <script src="/lab_sync/public/js/appointmentDetailsModal.js"></script>
            <script src="/lab_sync/public/js/appointmentEditModal.js"></script>
            <script src="/lab_sync/public/js/appointmentDeleteModal.js"></script>
        </body>
</html>