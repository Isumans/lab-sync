<?php
// settings.php

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
                <div class="main-content-header">
                    <h1>Settings</h1>
                    <p class="MC-p">Settings-></p>
                </div>
                <div class="nav-bar-container">
                    <div class="nav-bar-line">
                        <a class="navItem" onclick="showSection('team', event)" href="#">Team</a>


                        <a class="navItem" onclick="showSection('partner-labs', event)" href="#">Partner Labs</a>

                        
                        <a class="navItem" onclick="showSection('configuration', event)" href="#">Lab Configuration</a>

                        <a class="navItem" onclick="showSection('general', event)" href="#">General Settings</a>

                    </div>
                </div>
                
                
                <div id="content-area" class="content-area">
                    <div id="team" class="section active">
                        <h2>Team Section</h2>
                        <p>Manage your team members here.</p>
                        <div class="search-and-filter">
                            <input type="text" class="search-bar" placeholder="  Search Users...">
                        </div>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example user rows -->
                                    <tr>
                                        <td>John Doe</td>
                                        <td>john.doe@example.com</td>
                                        <td>Admin</td>
                                        <td>Active</td>
                                        <td>
                                            <button class="edit-button"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                            <button class="delete-button"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="partner-labs" class="section" style="display:none;">
                        <h2>Partner Labs Section</h2>
                        <p>Manage partner labs here.</p>
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
    </body>
</html>