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


    <title>Create Supplier</title>
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
                        <h1>Create Supplier</h1>
                    </div>
                    <div>
                        <p class="MC-p">Suppliers->Create Supplier</p>
                    </div>
                
                    <div class="heading-row">
                        <h2 class="heading2">
                            Add New Supplier
                        </h2>

                        <form action="/lab_sync/index.php?controller=appointmentController&action=storeAppointment" class="appointment-form formStyle"><br>


                            <div id="patient-suggestions" class="suggestion-box"></div>

                            <label for="appointment-date">Name:</label>
                            <input type="date" id="appointment-date" name="appointment-date" required>

                            <label for="appointment-time">Contact:</label>
                            <input type="time" id="appointment-time" name="appointment-time" required>
                            
                            <div id="additional-tests">
                            <div class="test-group">
                                <label for="test-type-1">Item 1 type:</label>
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
                        <button type="submit">Add New Supplier
                        </form>
                    </div>
                </div>
         </main>
        </div>
        <script src="/lab_sync/public/js/addTest.js"></script>
        <script src="/lab_sync/public/js/searchPatient.js"></script>
    </body>
</html> 