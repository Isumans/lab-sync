<?php
// require 'C:\xampp\htdocs\lab_sync\config\db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}



?>


<html>
    <head>
        <title>Dashboard</title>
        <!-- <link rel="stylesheet" href="stle1.css"> -->
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
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
                <h1>Dashboard</h1><br>
                <h3>Quick Stats</h3>
                <div class="container-cards">
                    <div class="card">
                        <h2>Total Patients Within Year</h2>
                        <p>1,250</p>
                    </div>
                    <div class="card">
                        <h2>Pending Appointments</h2>
                        <p>45</p>
                    </div>
                    <div class="card">
                        <h2>Tests Conducted Today</h2>
                        <p>320</p>
                    </div>
                     <div class="card">
                        <h2>Week's Appointments</h2>
                        <p>45</p>
                    </div>
                    <div class="card">
                        <h2>Tests Conducted Today</h2>
                        <p>320</p>
                    </div>
                </div>
                <div class="container-cards">
                    <div class="wide-card">
                        <h2>OutSourced Tests</h2><br>
                        <p>120</p>
                    </div>
                </div>
                <br>
                <br>
                <div>
                    <h2>Charts/Graphs</h2>
                    <div class="container-cards">
                        <div class="card, c-card">
                            <h3>Monthly Test Volume</h3>
                            <!-- <img src="assests/chart1.png" alt="Chart 1" style="width:100%; height:auto;"> -->
                    </div>
                    <div class="card, c-card">
                        <h3>Revenue Trends</h3>
                        <!-- <img src="assests/chart2.png" alt="Chart 2" style="width:100%; height:auto;"> -->
                    </div>
                    <div class="card, c-card">
                        <h3>Patient Demographics</h3>
                        <!-- <img src="assests/chart3.png" alt="Chart 3" style="width:100%; height:auto;"> -->
                    </div>
                </div>
                <div>
                    <h2>Today's Appointements/ Orders</h2>
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient Name</th>
                                <th>Test Type</th>
                                <th >Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>09:00 AM</td>
                                <td>John Doe</td>
                                <td>Blood Test</td>
                                <td ><div class="status">Completed</div></td>
                            </tr>
                            <tr>
                                <td>10:30 AM</td>
                                <td>Jane Smith</td>
                                <td>X-Ray</td>
                                <td ><div class="Status">
                                        Pending
                                    </div></td>
                            </tr>
                            <tr>
                                <td>11:00 AM</td>
                                <td>Mike Johnson</td>
                                <td>MRI Scan</td>
                                <td><div class="Status">
                                        In Progress
                                    </div></td>
                            </tr>
                            <tr>
                                <td>01:00 PM</td>
                                <td>Emily Davis</td>
                                <td>CT Scan</td>
                                <td ><div class="Status">
                                        Completed
                                    </div></td>
                            </tr>
                            <tr>
                                <td>02:30 PM</td>
                                <td>Chris Brown</td>
                                <td>Ultrasound</td>
                                <td >
                                    <div class="Status">
                                        Completed
                                    </div></td>
                            </tr>
                        </tbody>
                </div>
                
            </main>
        </div>
    </body>
</html>