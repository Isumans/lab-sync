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
                
                    <div class="heading-row">
                        <h2 class="heading2">
                            Add New Appointments
                        </h2>

                        <form id="appointment-form" method="post" action="/lab_sync/index.php?controller=appointmentsController&action=storeAppointment" class="appointment-form formStyle" onsubmit="return ensurePatientSelected();"><br>
                            <label for="patient-name">Patient patient by:
                                <select id="patient-search-by" name="patient_search_by" required>
                                    <option value="email">email</option>
                                    <option value="patient_name">patient_name</option>
                                </select>
                            </label>
                            <input type="text" class="search-bar" id="patient-search" name="patient_search" placeholder="  Search patient..." autoComplete="off">
                            <input type="hidden" id="patient_id" name="patient_id" value="">

                            <div id="patient-suggestions" class="suggestion-box"></div>

                            <label for="appointment-date">Appointment Date:</label>
                            <input type="date" id="appointment-date" name="appointment_date" required>

                            <label for="appointment-time">Appointment Time:</label>
                            <input type="time" id="appointment-time" name="appointment_time" required>

                            <label for="reason">Reason (optional):</label>
                            <input type="text" id="reason" name="reason">
                            
                            <div id="additional-tests">
                            <div class="test-group">
                                <label for="test-type-1">Test 1 type:</label>
                                <select id="test-type-1" name="test-types[]" class="test-select" required>
                                    <option value="">Select Test Type</option>
                                    <option value="Blood Test">Blood Test</option>
                                    <option value="Urine Test">Urine Test</option>
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="MRI">MRI</option>
                                    <option value="CT Scan">CT Scan</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <button type="button" id="add-test-button" class="add-button">+ Add Another Test</button>
                        <br>
                        <button type="submit" name="create_appointment">Add New Appointment</button>
                        </form>
                    </div>
                </div>
         </main>
        </div>
        <script>
            // Prevent submitting an appointment without a selected existing patient
            function ensurePatientSelected(){
                const pid = document.getElementById('patient_id').value.trim();
                if(!pid){
                    alert('Please select an existing patient from the suggestions before submitting the appointment.');
                    document.getElementById('patient-search').focus();
                    return false;
                }
                return true;
            }
        </script>
        <script src="/lab_sync/public/js/addTest.js"></script>
        <script src="/lab_sync/public/js/searchPatient.js"></script>
    </body>
</html>