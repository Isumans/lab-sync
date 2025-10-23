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

    <title>Reports</title>
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
                        <h1>Reports</h1>
                    </div>
                    <div>
                        <p class="MC-p">Reports-></p>
                    </div>
                    <div class="container-cards">
                        <div class="card, c-card">
                                <h3>Completed Reports (This Week)</h3>
                                <h1>10</h1>
                                <p>+2%</p>
                                <!-- <img src="assests/chart1.png" alt="Chart 1" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card, c-card">
                            <h3>Pending Reports</h3>
                            <h1>40</h1>
                            <p>+5%</p>
                            <!-- <img src="assests/chart2.png" alt="Chart 2" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card c-card">
                            <h3>Samples Processing</h3>
                            <h1>5</h1>
                            <p>-1%</p>
                            <!-- <img src="assests/chart3.png" alt="Chart 3" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card c-card">
                            <h3>Samples Processed (This Week)</h3>
                            <h1>2</h1>
                            <p>0%</p>
                            <!-- <img src="assests/chart4.png" alt="Chart 4" style="width:100%; height:auto;"> -->
                        </div>

                    </div>
                     <div class="nav-bar-container">
                        <div class="nav-bar-line">
                            <a class="navItem active" onclick="showSection('finalReports', event)" href="#">Final Reports</a>


                            <a class="navItem" onclick="showSection('testSamples', event)" href="#">Test Samples</a>

                            <a class="navItem" onclick="showSection('createReports', event)" href="#">Create Reports</a>


                        </div>
                    </div>
                    <div id="content-area" class="content-area">
                       <div id="finalReports" class="section">
                           <h2>Final Reports</h2>
                           <p>View and manage final reports here.</p>
                       </div>
                       <div id="testSamples" class="section" style="display:none;">
                           <h2>Test Samples</h2>
                           <p>View and manage test samples here.</p>
                       </div>
                       <div id="createReports" class="section" style="display:none;">
                           <h2>Create Reports</h2>
                           <p>Create new reports here.</p>
                           <?php require 'C:\xampp\htdocs\lab_sync\app\views\technicians\createReport.php'; ?>
                       </div>
                    </div>
                </div>
            </main>

        </div>
        <script src="/lab_sync/public/js/showSection.js"></script>
    </body>


</html>

            