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
    <title>Create Appointment</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <div class="Tmain-content">
                <div class="test-catalog-header">
                    <h1>Create Appointment</h1>
                </div>
                <div>
                    <p class="MC-p">Appointments->Create</p>
                </div>

                <div>
                    <?php include __DIR__ . '/appointment_form.php'; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="/lab_sync/public/js/addTest.js"></script>
    <script src="/lab_sync/public/js/searchPatient.js"></script>
</body>
</html>
