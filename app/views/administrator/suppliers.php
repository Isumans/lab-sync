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

    <title>Suppliers</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
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
                        <h1>Suppliers</h1>
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=supplierController&action=Register_supplier">Register New Supplier</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Suppliers-></p>
                    </div>
                    <div class="container-cards">
                        <div class="card, c-card">
                                <h3>Total Suppliers</h3>
                                <h1>10</h1>
                                <p>+2%</p>
                                <!-- <img src="assests/chart1.png" alt="Chart 1" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card, c-card">
                            <h3>Pending Supplies</h3>
                            <h1>40</h1>
                            <p>+5%</p>
                            <!-- <img src="assests/chart2.png" alt="Chart 2" style="width:100%; height:auto;"> -->
                        </div>
                    

                    </div>
                    <div class="search-and-filter patient-search">

                        <select class="search-option" id="supplier-name" name="supplier-name" required>
                            <option value="">Email</option>
                            <option value="John Doe">SupplierId</option>
                        </select>
                        <input type="text" class="search-bar" placeholder="  Search Suppliers...">

                    </div>
                    <div id="content-area" class="content_area">
                        <div id="all" class="section">
                            <h2>All Suppliers</h2>
                            <p>Manage your suppliers here.</p>
                            <div class="user-list">
                                <table class="test-catalog-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>John Doe</td>
                                            <td>johndoe@example.com</td>
                                            <td>
                                                <button class="edit-button">Edit</button>
                                                <button class="delete-button">Delete</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Jane Smith</td>
                                            <td>janesmith@example.com</td>
                                            <td>
                                                <button class="edit-button">Edit</button>
                                                <button class="delete-button">Delete</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

        </div>
        <script src="/lab_sync/public/js/showSection.js"></script>
    </body>


</html>

            </main>

        </div>
        <script src="/lab_sync/public/js/showSection.js"></script>
    </body>
        
        
</html>