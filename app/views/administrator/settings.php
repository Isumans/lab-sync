<?php
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
$role = $_GET['user_role'] ?? '';

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
        <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
        <link rel="stylesheet" href="/lab_sync/public/settingsDeleteModal.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <section class="reports-dashboard" aria-label="Reports Dashboard">
                    <?php
                    $pageTitle = 'Settings';
                    $pageBreadcrumbText = 'Settings->';
                    $pageActionHtml = '';
                    require __DIR__ . '/../../../public/partials/page-header.php';
                ?>
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
                </section>
                
            </main>
        </div>

        <!-- Shared delete confirmation modal (team management + partner labs) -->
        <div id="settingsDeleteModal" class="settings-delete-modal" aria-hidden="true">
            <div class="settings-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="settingsDeleteTitle">
                <div class="settings-delete-header">
                    <div class="settings-delete-icon" aria-hidden="true">!</div>
                    <h2 id="settingsDeleteTitle">Delete</h2>
                    <button type="button" id="settingsDeleteClose" class="settings-delete-close" aria-label="Close delete modal">&times;</button>
                </div>
                <p class="settings-delete-copy">Are you sure you want to delete this record? This action cannot be undone.</p>
                <div id="settingsDeleteAlert" class="settings-delete-alert" hidden></div>
                <div class="settings-delete-summary">
                    <div class="summary-label">Name</div>
                    <div id="settingsDeleteName" class="summary-value">—</div>
                </div>
                <button type="button" id="settingsDeleteConfirm" class="settings-delete-confirm-btn">Delete</button>
                <button type="button" id="settingsDeleteCancel" class="settings-delete-cancel-btn">Cancel</button>
                <div class="settings-delete-footer-note">SYSTEM: AUTHORIZATION REQUIRED</div>
            </div>
        </div>

        <script src="/lab_sync/public/js/showSection.js?v=1"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
        <script src="/lab_sync/public/js/teamManagement.js"></script>
        <script src="/lab_sync/public/js/editUserModal.js?v=2"></script>
        <script src="/lab_sync/public/js/labConfig.js"></script>
        <script src="/lab_sync/public/js/generalSettings.js"></script>
        <script src="/lab_sync/public/js/partnerLabsFilter.js"></script>
        <script src="/lab_sync/public/js/settingsDeleteModal.js"></script>

    </body>
</html>