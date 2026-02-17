<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
    $role=$_GET['user_role'] ?? '';
}

?>


<html>
    <head>
        <title>Settings</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/formStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/partnerLabForm.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="main-content-header">
                    <h1>Settings</h1>
                    <p class="MC-p">Settings-></p>
                </div>
                <div class="nav-bar-container">
                    <div class="nav-bar-line">
                        <a class="navItem active" onclick="showSection('team', event)" href="#">Team</a>

                        <a class="navItem" onclick="showSection('partner-labs', event)" href="#">Partner Labs</a>
                        
                        <a class="navItem" onclick="showSection('configuration', event)" href="#">Lab Configuration</a>

                        <a class="navItem" onclick="showSection('general', event)" href="#">General Settings</a>

                    </div>
                </div>
                
                
                <div id="content-area" class="content-area" >
                    <?php require __DIR__ . '/settings/team_management.php'; ?>
                    <?php require __DIR__ . '/settings/partner_labs.php'; ?>
                    <?php require __DIR__ . '/settings/lab_configuration.php'; ?>
                    <?php require __DIR__ . '/settings/general_settings.php'; ?>
                </div>
            </main>
        </div>

        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
        <script src="/lab_sync/public/js/teamManagement.js"></script>
    </body>
</html>