<?php
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
$role = $_GET['user_role'] ?? '';
$createStatus = trim((string)($_GET['create_status'] ?? ''));

$statusMap = [
    'created_emailed' => ['type' => 'success', 'text' => 'Team member account created and onboarding email sent.'],
    'created_email_skipped' => ['type' => 'warning', 'text' => 'Team member account created, but email sending is disabled.'],
    'created_email_failed' => ['type' => 'warning', 'text' => 'Team member account created, but onboarding email failed to send.'],
    'resent_emailed' => ['type' => 'success', 'text' => 'Invite resent successfully and new credentials were emailed.'],
    'resent_email_skipped' => ['type' => 'warning', 'text' => 'Invite was reset, but email sending is currently disabled.'],
    'resent_email_failed' => ['type' => 'warning', 'text' => 'Invite was reset, but resend email failed to send.'],
    'duplicate_email' => ['type' => 'error', 'text' => 'A user with this email already exists.'],
    'invalid_input' => ['type' => 'error', 'text' => 'Please provide valid user details.'],
    'invalid_email' => ['type' => 'error', 'text' => 'Please enter a valid email address.'],
    'invalid_method' => ['type' => 'error', 'text' => 'Invalid request method for user creation.'],
    'unauthorized' => ['type' => 'error', 'text' => 'Only administrators can create team accounts.'],
    'user_not_found' => ['type' => 'error', 'text' => 'Selected user was not found.'],
    'invite_not_eligible' => ['type' => 'error', 'text' => 'Resend invite is allowed only for pending or inactive users.'],
    'password_reset_failed' => ['type' => 'error', 'text' => 'Unable to reset temporary credentials. Please try again.'],
    'prepare_failed' => ['type' => 'error', 'text' => 'Unable to create account due to a server error.'],
    'insert_failed' => ['type' => 'error', 'text' => 'Failed to create user account. Please try again.'],
    'create_failed' => ['type' => 'error', 'text' => 'Failed to create user account.'],
];

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
                <?php if (isset($statusMap[$createStatus])): ?>
                    <?php $status = $statusMap[$createStatus]; ?>
                    <div class="status-banner status-<?php echo htmlspecialchars($status['type']); ?>" style="margin: 12px 0 16px; padding: 12px 14px; border-radius: 10px; border: 1px solid #d0d7e2; background: #f7f9fc; color: #1f2a44;">
                        <?php echo htmlspecialchars($status['text']); ?>
                    </div>
                <?php endif; ?>
                <div class="nav-bar-container">
                    <div class="rd-slider-tabs" role="tablist" aria-label="Settings sections">
                        <a class="rd-slider-tab is-active" onclick="showSection('team', this, event)" href="#" style="text-decoration: none;">Team</a>

                        <a class="rd-slider-tab" onclick="showSection('partner-labs', this, event)" href="#" style="text-decoration: none;">Partner Labs</a>
                        
                        <a class="rd-slider-tab" onclick="showSection('configuration', this, event)" href="#" style="text-decoration: none;">Lab Configuration</a>

                        <a class="rd-slider-tab" onclick="showSection('general', this, event)" href="#" style="text-decoration: none;">General Settings</a>

                        <a class="rd-slider-tab" onclick="showSection('online-slots', this, event)" href="#" style="text-decoration: none;">Online Slots</a>

                    </div>
                </div>


                <div id="content-area" class="content-area" >
                    <?php require __DIR__ . '/settings/team_management.php'; ?>
                    <div id="partner-labs" class="section" style="display:none;"></div>
                    <div id="configuration" class="section" style="display:none;"></div>
                    <div id="general" class="section" style="display:none;"></div>
                    <div id="online-slots" class="section" style="display:none;"></div>
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

        <script src="/lab_sync/public/js/showSection.js?v=2"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
        <script src="/lab_sync/public/js/teamManagement.js"></script>
        <script src="/lab_sync/public/js/editUserModal.js?v=2"></script>
        <script src="/lab_sync/public/js/labConfig.js"></script>
        <script src="/lab_sync/public/js/generalSettings.js"></script>
        <script src="/lab_sync/public/js/partnerLabsFilter.js"></script>
        <script src="/lab_sync/public/js/settingsDeleteModal.js"></script>

    </body>
</html>