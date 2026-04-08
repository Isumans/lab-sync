<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>


    <title>Create Appointment</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <!-- <link rel="stylesheet" href="/lab_sync/public/settingStyles.css"> -->
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/appointmentStyles.css">
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
                        <h1>Create Appointment</h1>
                    </div>
                    <div>
                        <p class="MC-p">Appointments->Create Appointment</p>
                    </div>

                    <?php
                        $prefillPatientId = (int)($prefillRequest['patient_id'] ?? 0);
                        $prefillPatientName = trim((string)($prefillRequest['patient_name'] ?? ''));
                        $prefillPatientEmail = trim((string)($prefillRequest['email'] ?? ''));
                        $prefillPatientLabel = trim($prefillPatientName . ($prefillPatientEmail !== '' ? ' (' . $prefillPatientEmail . ')' : ''));
                        $prefillDate = trim((string)($prefillRequest['preferred_date'] ?? ''));
                        $prefillTime = trim((string)($prefillRequest['preferred_time'] ?? ''));
                        $prefillRequestId = (int)($prefillRequest['request_id'] ?? 0);
                    ?>
                
                    <div class="heading-row">
                        <h2 class="heading2">
                            Add New Appointments
                        </h2>

                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="error" style="color: #b42318; margin-bottom: 12px;"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <?php if ($prefillRequestId > 0): ?>
                            <div style="margin: 10px 0 14px; padding: 10px 12px; background: #eef6ff; border: 1px solid #bcd6f5; border-radius: 8px; color: #0f4a8a;">
                                Processing prescription request #<?php echo $prefillRequestId; ?>.
                            </div>
                        <?php endif; ?>

                        <form action="/lab_sync/index.php?controller=appointmentsController&action=storeAppointment" method="POST" class="appointment-form formStyle"><br>
                            <?php if ($prefillRequestId > 0): ?>
                                <input type="hidden" name="prescription_request_id" value="<?php echo $prefillRequestId; ?>">
                            <?php endif; ?>

                            <label for="patient-name">Search patient by:
                                <select id="patient-search-by" name="patient-search-by" required>
                                    <option value="email">email</option>
                                    <option value="patient_name">patient_name</option>
                                </select>
                            </label>
                            <input type="text" class="search-bar" id="patient-search" placeholder="  Search patient..." autoComplete="off" value="<?php echo htmlspecialchars($prefillPatientLabel); ?>" <?php echo $prefillPatientId > 0 ? 'readonly' : ''; ?>>
                            <input type="hidden" id="patient-id" name="patient_id" required value="<?php echo $prefillPatientId > 0 ? (int)$prefillPatientId : ''; ?>">

                            <?php if ($prefillPatientId > 0): ?>
                                <div style="margin-top:6px; color:#475467; font-size:13px;">Patient is pre-selected from prescription queue.</div>
                            <?php endif; ?>

                            <div id="patient-suggestions" class="suggestion-box"></div>

                            <label for="booking-method">Booking Method:</label>
                            <select id="booking-method" name="booking_method" required>
                                <option value="Physical">Physical Visit</option>
                                <option value="Call">Call</option>
                            </select>

                            <div style="margin: 12px 0;">
                                <label style="display:flex; align-items:center; gap:8px; font-weight:600; color:#344054;">
                                    <input type="checkbox" id="home-collection-toggle" name="home_collection" value="1">
                                    Home sample collection
                                </label>
                                <div id="collection-address-wrap" style="display:none; margin-top:8px;">
                                    <label for="collection-address">Collection Address:</label>
                                    <input type="text" id="collection-address" name="collection_address" class="search-bar" placeholder="Enter the address for sample collection">
                                </div>
                            </div>

                            <label for="appointment-date">Appointment Date:</label>
                            <input type="date" id="appointment-date" name="appointment_date" required value="<?php echo htmlspecialchars($prefillDate); ?>">

                            <label for="appointment-time">Appointment Time:</label>
                            <input type="time" id="appointment-time" name="appointment_time" required value="<?php echo htmlspecialchars($prefillTime); ?>">

                            <div class="test-group">
                                <label for="test-id">Test Type:</label>
                                <select id="test-id" name="test_id" class="test-select" required>
                                    <option value="">Select Test Type</option>
                                    <?php foreach (($tests ?? []) as $test): ?>
                                        <option value="<?php echo htmlspecialchars($test['test_id']); ?>">
                                            <?php echo htmlspecialchars($test['test_name']); ?> - LKR <?php echo htmlspecialchars(number_format((float)$test['price'], 2)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        <br>
                        <button type="submit">Add New Appointment</button>
                        </form>
                    </div>
                </div>
         </main>
        </div>
        <script src="/lab_sync/public/js/searchPatient.js"></script>
        <script>
            const homeCollectionToggle = document.getElementById('home-collection-toggle');
            const collectionAddressWrap = document.getElementById('collection-address-wrap');
            const collectionAddressInput = document.getElementById('collection-address');

            function syncHomeCollectionField() {
                const enabled = homeCollectionToggle.checked;
                collectionAddressWrap.style.display = enabled ? 'block' : 'none';
                collectionAddressInput.required = enabled;
                if (!enabled) {
                    collectionAddressInput.value = '';
                }
            }

            homeCollectionToggle.addEventListener('change', syncHomeCollectionField);
            syncHomeCollectionField();
        </script>
    </body>
</html> 