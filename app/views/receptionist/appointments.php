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

    <title>Document</title>
</head>
    <title>Appointments</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
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
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=<?php echo $role; ?>">Create Appointment</a></button>
                    </div>
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
                                    <tr>
                                        <td>1</td>
                                        <td>1</td>
                                        <td>2023-10-01</td>
                                        <td>10:00 AM</td>
                                        <!-- <td>Blood Test</td> -->
                                        <td>
                                            <button class="Status">Cancel</button>
                                            <button class="Status">Reschedule</button>
                                        </td>
                                    </tr>
    
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
            </main>
    
</body>
</html>