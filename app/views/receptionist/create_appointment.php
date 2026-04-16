<?php
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
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/appointmentFormStyles.css">

</head>
    <body>
        <!-- Navigation Bar -->
        <?php require __DIR__ . '/../../../public/navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require __DIR__ . '/../../../public/sidebar.php'; ?>

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
