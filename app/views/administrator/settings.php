<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
$role = $_GET['user_role'] ?? '';

?>


<html>
    <head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
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
        <div class="page-wrapper">
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
                        <a class="navItem active" onclick="showSection('team', this, event)" href="#">Team</a>

                        <a class="navItem" onclick="showSection('partner-labs', this, event)" href="#">Partner Labs</a>
                        
                        <a class="navItem" onclick="showSection('configuration', this, event)" href="#">Lab Configuration</a>

                        <a class="navItem" onclick="showSection('general', this, event)" href="#">General Settings</a>

                    </div>
                </div>
                
                
                <div id="content-area" class="content-area" >
                    <?php require __DIR__ . '/settings/team_management.php'; ?>
                    <div id="partner-labs" class="section" style="display:none;"></div>
                    <div id="configuration" class="section" style="display:none;"></div>
                    <div id="general" class="section" style="display:none;"></div>
                </div>
            </main>
        </div>

        <script src="/lab_sync/public/js/showSection.js?v=1"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
        <script src="/lab_sync/public/js/teamManagement.js"></script>
        <script src="/lab_sync/public/js/editUserModal.js?v=2"></script>
        <script src="/lab_sync/public/js/labConfig.js"></script>
        <script src="/lab_sync/public/js/generalSettings.js"></script>
        
    </body>
</html>
