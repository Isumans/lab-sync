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
                        <h1>Add User</h1>
                    </div>
                    <div>
                        <p class="MC-p">Settings->Add User</p>
                    </div>
                    <br/>
                    <div>
                        <form class="formStyle" action="/lab_sync/index.php?controller=administratorController&action=create_user" method="POST">
                            <!-- Form fields for adding a user -->
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" required>

                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required>

                            <label for="role">Role:</label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="receptionist">Receptionist</option>
                                <option value="technician">Technician</option>
                            </select>
                            <!-- <label for="date_of_birth">Date of Birth:</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" required> -->
                            <label for="contact_number">Contact Number:</label>
                            <input type="tel" id="contact_number" name="contact_number" required>
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                            <button type="submit">Create User</button>

                        </form>
                    </div>
                 </div>
            </main>
        </div>
    </body>
</html>