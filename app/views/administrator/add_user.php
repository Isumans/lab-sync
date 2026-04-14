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
        <title>Settings</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/add_user_styles.css">
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
                    <?php
                        $pageTitle = 'Add User';
                        $pageBreadcrumbText = 'Settings->Add User';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>
                    <div class="add-user-form-container">
                        <form action="/lab_sync/index.php?controller=administratorController&action=create_user" method="POST">
                            
                            <!-- Account Information Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <span class="section-icon">ℹ️</span>
                                    <h2>Account Information</h2>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="required">Username</label>
                                        <input type="text" name="username" placeholder="e.g. pdoe_tech" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="required">Password</label>
                                        <div class="password-wrapper">
                                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="required">Email Address</label>
                                        <input type="email" name="email" placeholder="john.doe@labsystem.com" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="required">Contact Number</label>
                                        <input type="tel" name="contact_number" placeholder="+1 (555) 000-0000" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Role Assignment Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <span class="section-icon">🔐</span>
                                    <h2>Role Assignment</h2>
                                </div>

                                <div class="role-grid">
                                    <div class="role-option">
                                        <input type="radio" id="role_admin" name="role" value="admin">
                                        <label for="role_admin">
                                            <span class="role-title">System Administrator</span>
                                            <span class="role-description">Full system access, managing users, laboratory settings, and security audits.</span>
                                        </label>
                                    </div>

                                    <div class="role-option">
                                        <input type="radio" id="role_receptionist" name="role" value="receptionist">
                                        <label for="role_receptionist">
                                            <span class="role-title">Receptionist</span>
                                            <span class="role-description">Manage patient registration, appointment scheduling, and front-desk billing workflows.</span>
                                        </label>
                                    </div>

                                    <div class="role-option">
                                        <input type="radio" id="role_technician" name="role" value="technician">
                                        <label for="role_technician">
                                            <span class="role-title">Laboratory Technician</span>
                                            <span class="role-description">Direct access to lab results entry, sample processing queues, and equipment calibration logs.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
                                <button type="submit" class="btn-save">Save User</button>
                            </div>

                        </form>
                    </div>
                 </div>
            </main>
        </div>
    </body>
</html>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.style.color = 'var(--primary-color)';
    } else {
        passwordInput.type = 'password';
        toggleBtn.style.color = '#a0aec0';
    }
}
</script>