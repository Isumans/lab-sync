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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Appointment</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentFormStyles.css">

</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="page-wrapper">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <div class="Tmain-content">
                <div class="test-catalog-header">
                    <h1>Create Appointment</h1>
                </div>
                <div>
                    <p class="MC-p"><a href="javascript:history.back()" style="color: var(--primary-color); text-decoration: none;">Appointments-></a>Create-Appointment</p></br>
                </div>

                <div>
                    <?php include VIEW_PATH . '/receptionist/appointment_form.php'; ?>

                </div>
            </div>
        </main>
    </div>

    <script src="/lab_sync/public/js/appointmentForm.js"></script>
    <script src="/lab_sync/public/js/searchPatient.js"></script>
</body>
</html>

