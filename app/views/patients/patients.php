<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}

$patientsPayload = [];
if (is_array($patients)) {
    foreach ($patients as $patient) {
        $patientsPayload[] = [
            'patient_id' => (int)($patient['patient_id'] ?? 0),
            'patient_name' => (string)($patient['patient_name'] ?? ''),
            'email' => (string)($patient['email'] ?? ''),
            'contact_number' => (string)($patient['contact_number'] ?? ''),
            'gender' => (string)($patient['gender'] ?? ''),
            'date_of_birth' => (string)($patient['date_of_birth'] ?? '')
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patients</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
    <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentEditModal.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentDeleteModal.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="Reports Dashboard">
                <?php
                $pageTitle = 'Patients';
                $pageBreadcrumbText = 'Patients->';
                $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=patientController&action=register_patient&role=' . rawurlencode((string)$role) . '">+ Register Walk-in Patient</a>';
                require __DIR__ . '/../../../public/partials/page-header.php';
            ?>

            

            <section class="rd-filter-card" aria-label="Patient search and filters">
                <div class="rd-filter-grid">
                    <div class="rd-filter-field rd-filter-field-search">
                        <label for="ptSearch">Search Patients</label>
                        <input id="ptSearch" type="text" placeholder="Search by name, ID, email, or contact...">
                    </div>

                    <div class="rd-filter-field">
                        <label for="ptGender">Gender</label>
                        <select id="ptGender">
                            <option value="all">All</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div class="rd-filter-field">
                        <label for="ptSortBy">Sort By</label>
                        <select id="ptSortBy">
                            <option value="date_of_birth">Date of Birth</option>
                            <option value="patient_name">Patient Name</option>
                            <option value="patient_id">Patient ID</option>
                            <option value="gender">Gender</option>
                        </select>
                    </div>
                </div>

                <div class="rd-filter-bottom-row">
                    <div class="rd-filter-date-range">
                        <div class="rd-filter-field">
                            <label for="ptDateFrom">Date of Birth Range</label>
                            <input id="ptDateFrom" type="date">
                        </div>
                        <div class="rd-filter-field rd-filter-field-to">
                            <label for="ptDateTo" class="rd-hidden-label">End Date</label>
                            <input id="ptDateTo" type="date">
                        </div>
                    </div>

                    <div class="patient-sort-direction-wrap">
                        <select id="ptSortDir" class="patient-sort-direction" aria-label="Sort direction">
                            <option value="desc">Newest First</option>
                            <option value="asc">Oldest First</option>
                        </select>
                        <button type="button" class="rd-clear-btn" id="ptClearBtn">Clear All Filters</button>
                    </div>
                </div>
            </section>

            <section class="rd-table-card" aria-label="Patients table">
                <div class="rd-table-wrap">
                    <table class="rd-table patient-rd-table">
                        <thead>
                            <tr>
                                <th class="pt-sortable" data-sort="patient_name" role="button" tabindex="0" aria-label="Sort by patient">Patient</th>
                                <th class="pt-sortable" data-sort="gender" role="button" tabindex="0" aria-label="Sort by gender">Gender</th>
                                <th>Contact</th>
                                <th class="pt-sortable is-active is-desc" data-sort="date_of_birth" role="button" tabindex="0" aria-label="Sort by date of birth">Date of Birth</th>
                                <th class="rd-th-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ptTableBody">
                            <tr class="rd-empty-row">
                                <td colspan="5">Loading patients...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rd-table-footer">
                    <p id="ptResultSummary">Showing 0 to 0 of 0 patients</p>
                    <div class="rd-pagination" id="ptPagination"></div>
                </div>
            </section>







            </section>
            
        </main>
    </div>

    <div class="appointment-edit-modal" id="patientAdminEditModal" aria-hidden="true">
        <div class="appointment-edit-dialog patient-admin-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="patientAdminEditTitle">
            <form id="editPatientForm" method="post" action="/lab_sync/index.php?controller=patientController&action=edit_patient&role=<?php echo urlencode($role); ?>">
                <div class="appointment-edit-header">
                    <div>
                        <h2 id="patientAdminEditTitle">Edit Patient</h2>
                        <p class="appointment-edit-subtitle">UPDATE PATIENT INFORMATION</p>
                    </div>
                    <button type="button" class="appointment-edit-close" id="patientAdminEditClose" aria-label="Close">&times;</button>
                </div>

                <div class="appointment-edit-alert" id="patientAdminEditAlert" hidden></div>

                <div class="appointment-edit-body">
                    <input type="hidden" name="patient_id" id="patientAdminPatientId" value="">

                    <section class="edit-section-card">
                        <div class="edit-section-title">
                            <span class="section-icon" aria-hidden="true">&#128100;</span>
                            <h3>Patient Summary</h3>
                        </div>
                        <div class="patient-readonly-card">
                            <div class="patient-identity">
                                <span class="patient-avatar" id="patientAdminInitials" aria-hidden="true">PT</span>
                                <div>
                                    <p class="patient-name" id="patientAdminDisplayName">Patient</p>
                                    <p class="patient-pid" id="patientAdminDisplayEmail">email@example.com</p>
                                </div>
                            </div>
                            <span class="readonly-badge" id="patientAdminDisplayId">PID-0</span>
                        </div>
                    </section>

                    <section class="edit-section-card">
                        <div class="edit-section-title">
                            <span class="section-icon" aria-hidden="true">&#9998;</span>
                            <h3>Account Details</h3>
                        </div>

                        <div class="patient-admin-form-grid">
                            <div class="patient-admin-field patient-admin-field-full">
                                <label class="edit-label" for="patientAdminName">Patient Name</label>
                                <div class="date-input-wrap">
                                    <input type="text" id="patientAdminName" name="patient_name" maxlength="120" required>
                                </div>
                            </div>

                            <div class="patient-admin-field">
                                <label class="edit-label" for="patientAdminEmail">Email Address</label>
                                <div class="date-input-wrap">
                                    <input type="email" id="patientAdminEmail" name="patient_email" maxlength="120" required>
                                </div>
                            </div>

                            <div class="patient-admin-field">
                                <label class="edit-label" for="patientAdminContact">Contact Number</label>
                                <div class="date-input-wrap">
                                    <input type="tel" id="patientAdminContact" name="contact_number" maxlength="25" pattern="^[0-9+()\-\s]{7,25}$" title="Use 7-25 characters: digits, space, plus, parentheses, or hyphen." required>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="appointment-edit-footer">
                    <button type="button" class="edit-cancel-btn" id="patientAdminEditCancel">Cancel</button>
                    <button type="submit" name="edit" value="1" class="edit-submit-btn" id="patientAdminEditSubmit">
                        <span aria-hidden="true">&#128190;</span> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="appointment-delete-modal" id="patientAdminDeleteModal" aria-hidden="true">
        <div class="appointment-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="patientAdminDeleteTitle">
            <div class="appointment-delete-header">
                <span class="delete-icon-wrap" aria-hidden="true">!</span>
                <h2 id="patientAdminDeleteTitle">Archive Patient</h2>
                <button type="button" class="appointment-delete-close" id="patientAdminDeleteClose" aria-label="Close">&times;</button>
            </div>

            <p class="appointment-delete-copy">This will archive the patient record and hide it from the Patients section.</p>
            <div class="appointment-delete-alert" id="patientAdminDeleteAlert" hidden></div>

            <div class="appointment-delete-summary">
                <span class="summary-label">Patient</span>
                <div class="summary-value" id="patientAdminDeleteName">Patient</div>

                <span class="summary-label">Email</span>
                <div class="summary-value" id="patientAdminDeleteEmail">email@example.com</div>

                <span class="summary-label">Contact</span>
                <div class="summary-value" id="patientAdminDeleteContact">N/A</div>
            </div>

            <form id="deletePatientForm" method="post" action="/lab_sync/index.php?controller=patientController&action=edit_patient&role=<?php echo urlencode($role); ?>">
                <input type="hidden" name="patient_id" id="patientAdminDeletePatientId" value="">
                <button type="submit" name="delete" value="1" class="delete-confirm-btn" id="patientAdminDeleteConfirm">Archive Patient</button>
                <button type="button" class="delete-cancel-btn" id="patientAdminDeleteCancel">Keep Patient</button>
            </form>

            <div class="appointment-delete-footer-note">Archive Action • Hidden From Patients List</div>
        </div>
    </div>

    <script>
    window.patientTableConfig = {
        role: <?php echo json_encode((string)$role); ?>,
        patients: <?php echo json_encode($patientsPayload); ?>
    };
    </script>
    <script src="/lab_sync/public/js/patient.js"></script>
    <script src="/lab_sync/public/js/showAlert.js"></script>
</body>
</html>
