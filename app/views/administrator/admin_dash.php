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
        <link rel="stylesheet" href="/lab_sync/public/dashboardCards.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <!-- Main Body Section -->
            <main class="main-content">
                <h1>Dashboard</h1>
                <br />
                <h3>Quick Overview</h3>
                <br />
                
                <!-- Metric Cards Section -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon user-icon">👤</div>
                        <div class="metric-content">
                            <h3>Total Patients past week</h3>
                            <p class="metric-value">40,689</p>
                            <span class="metric-change up">↑ 8.5% Up from previous</span>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon order-icon">📦</div>
                        <div class="metric-content">
                            <h3>Samples Collected past week</h3>
                            <p class="metric-value">10293</p>
                            <span class="metric-change up">↑ 1.3% Up from past week</span>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon sales-icon">📈</div>
                        <div class="metric-content">
                            <h3>Payments pending</h3>
                            <p class="metric-value">$89,000</p>
                            <span class="metric-change down">↓ 4.3% Down from yesterday</span>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon pending-icon">⏱️</div>
                        <div class="metric-content">
                            <h3>Pending Reports</h3>
                            <p class="metric-value">2040</p>
                            <span class="metric-change up">↑ 1.8% Up from yesterday</span>
                        </div>
                    </div>
                </div>

                <!-- Sales Details Section -->
                <div class="sales-section">
                    <div class="section-header">
                        <h2>Revenue Details</h2>
                        <select class="month-selector">
                            <option>October</option>
                            <option>November</option>
                            <option>December</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                
            </main>
        </div>
    </body>
    <script src="/lab_sync/public/js/revenueStat.js"></script>
</html>