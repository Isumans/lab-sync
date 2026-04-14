<?php
session_start();
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
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
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
        </main>
    </div>

    <style>
    #editModal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.4); }
    #editModal .modal-content { background: #fff; margin: 6% auto; padding: 20px; border-radius: 6px; width: 92%; max-width: 600px; }
    #editModal .close { float: right; font-size: 24px; font-weight: bold; cursor: pointer; }
    #editPatientForm .form-row { margin-bottom: 10px; }
    #editPatientForm label { display: block; font-weight: 600; margin-bottom: 4px; }
    #editPatientForm input[type=text], #editPatientForm input[type=email] { width: 100%; padding: 8px; box-sizing: border-box; }
    #editPatientForm .actions { text-align: right; margin-top: 12px; }
    </style>

    <div id="editModal">
        <div class="modal-content">
            <span id="editModalClose" class="close">&times;</span>
            <h3>Edit Patient</h3>
            <form id="editPatientForm" method="post" action="/lab_sync/index.php?controller=patientController&action=edit_patient&role=<?php echo urlencode($role); ?>">
                <input type="hidden" name="patient_id" value="">
                <div class="form-row">
                    <label for="patient_name">Name</label>
                    <input type="text" id="patient_name" name="patient_name" required>
                </div>
                <div class="form-row">
                    <label for="patient_email">Email</label>
                    <input type="email" id="patient_email" name="patient_email" required>
                </div>
                <div class="form-row">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number">
                </div>
                <div class="actions">
                    <button type="button" id="cancelEdit">Cancel</button>
                    <button type="submit" name="edit" value="1">Save changes</button>
                    <button type="submit" name="delete" value="1" style="margin-left:8px; background:#c33; color:#fff;">Delete</button>
                </div>
            </form>
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
