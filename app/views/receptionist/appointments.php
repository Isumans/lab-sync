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
                        <button id="openCreateAppointment" class="add-test-button">Create Appointment</button>
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
                    </div>
                  
                    <div class="heading-row">
                        <h2 class="heading3">Online Appointment </h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <!-- <th>Test ID</th> -->
                                        <!-- <th>Actions</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointmentsOnline as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo $appointment['patient_id']; ?></td>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['appointment_time']; ?></td>
                                        <!-- <td>Blood Test</td> -->
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
                <div class="heading-row">
                        <h2 class="heading3">Physical/Call Appointments</h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <!-- <th>Test Type</th> -->
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                                <tbody>
                                                    <?php if (!empty($appointmentsPhysical)): ?>
                                                        <?php foreach ($appointmentsPhysical as $appointment): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                                                                <td><?php echo htmlspecialchars($appointment['patient_id']); ?></td>
                                                                <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                                                <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                                                <td>
                                                                    <button class="Status">Cancel</button>
                                                                    <button class="Status">Reschedule</button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr><td colspan="5">No physical appointments found.</td></tr>
                                                    <?php endif; ?>
                                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
            </main>
            <script src="/lab_sync/public/js/appointmentPopup.js"></script>
            <script src="/lab_sync/public/js/addTest.js"></script>
            <script src="/lab_sync/public/js/searchPatient.js"></script>
        </body>
</html>