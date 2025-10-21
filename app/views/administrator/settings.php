<?php
// settings.php

?>


<html>
    <head>
        <title>Settings</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/formStyles.css">
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
                    <div id="team" class="section" class="Tmain-content">
                        <div class="test-catalog-header">
                            <h2>Team Section</h2>
                            <button class="add-test-button" ><a href="/lab_sync/index.php?controller=administratorController&action=add_user">+Add New User</a></button>
                        </div>
                        <div>
                            <p>Manage your team members here.</p>
                        </div>
                        <div class="search-and-filter">
                            <input type="text" class="search-bar" placeholder="  Search Users...">
                        </div>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example user rows -->
                                     <?php if (is_array($users)): ?>
                                     <?php foreach ($users as $user): ?>
                                    <form method="POST" action="/lab_sync/index.php?controller=administratorController&action=manageUser">
                                    <tr>
                                        <td><input class="form1" type="text" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>"></td>
                                        <td><input class="form1" type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"></td>
                                        <td><input class="form1" type="text" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"></td>
                                        <td><input class="form1" type="text" name="role" value="<?php echo htmlspecialchars($user['role']); ?>"></td>
                                        <td><input class="form1" type="text" name="status" value="<?php echo htmlspecialchars($user['status']); ?>"></td>
                                        <td>
                                            <button id="edit" type="submit" name="edit" class="edit-button" onclick="showAlertAndSubmit(event,'edit')"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                            <button id="delete" type="submit" name="delete" class="delete-button" onclick="showAlertAndSubmit(event,'delete')"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                        </td>
                                    </tr>
                                    </form>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="partner-labs" class="section" style="display:none;">
                        <h2>Partner Labs Section</h2>
                        <p>Manage partner labs here.</p>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Lab ID</th>
                                        <th>Lab Name</th>
                                        <th>Contact Person</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example partner lab rows -->
                                    <tr>
                                        <td>1</td>
                                        <td>Lab A</td>
                                        <td>John Doe</td>
                                        <td>john.doe@example.com</td>
                                        <td>123-456-7890</td>
                                        <td>
                                            <button class="edit-button"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                            <button class="delete-button"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="configuration" class="section" style="display:none;">
                        <h2>Lab Configuration Section</h2>
                        <p>Configure lab settings here.</p>
                    </div>
                    <div id="general" class="section" style="display:none;">
                        <h2>General Settings Section</h2>
                        <p>Adjust general settings here.</p>
                    </div>
                        
                </div>
            </main>
        </div>

        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
    </body>
</html>