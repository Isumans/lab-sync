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
    <title>Finances</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="Reports Dashboard">
                <?php
                    $pageTitle = 'Finances';
                    $pageBreadcrumbText = 'Finances->';
                    $pageActionHtml = '';
                    require __DIR__ . '/../../../public/partials/page-header.php';
                ?>

                <!-- <div class="rd-header-row">
                    <h1 class="rd-title">Reports</h1>
                    <p class="MC-p">Appointments-></p>
                    <div class="rd-header-actions">
                        <button type="button" class="rd-btn rd-btn-muted" id="rdExportBtn">Export CSV</button>
                        <button type="button" class="rd-btn rd-btn-primary" id="rdGenerateBtn">Generate Report</button>
                    </div>
                </div> -->

                <section class="rd-filter-card" aria-label="Search and Filters">
                    <div class="rd-filter-grid">
                        <div class="rd-filter-field rd-filter-field-search">
                            <label for="rdSearch">Search Invoices</label>
                            <input id="rdSearch" type="text" placeholder="Search by Patient Name, Bill No, or Appointment ID..." />
                        </div>

                        <div class="rd-filter-field">
                            <label for="rdStatus">Status</label>
                            <select id="rdStatus">
                                <option value="all">All Statuses</option>
                                <option value="paid_in_full">Paid in Full</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="partially_paid">Partially Paid</option>
                                <option value="claim_submitted">Claim Submitted</option>
                            </select>
                        </div>

                        <div class="rd-filter-field">
                            <label for="rdPaymentMethod">Payment Method</label>
                            <select id="rdPaymentMethod">
                                <option value="all">All Methods</option>
                                <option value="card">Card</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="rd-filter-bottom-row">
                        <div class="rd-filter-date-range">
                            <div class="rd-filter-field">
                                <label for="rdDateFrom">Date Range</label>
                                <input id="rdDateFrom" type="date" />
                            </div>
                            <div class="rd-filter-field rd-filter-field-to">
                                <label for="rdDateTo" class="rd-hidden-label">End Date</label>
                                <input id="rdDateTo" type="date" />
                            </div>
                        </div>

                        <button type="button" class="rd-clear-btn" id="rdClearBtn">Clear All Filters</button>
                    </div>
                </section>

                <section class="rd-table-card" aria-label="Reports Table">
                    <div class="rd-table-wrap">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th>Appointment ID</th>
                                    <th>Patient Name</th>
                                    <th>Total Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Payment Method</th>
                                    <th>Financial Status</th>
                                    <th class="rd-th-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="rdTableBody"></tbody>
                        </table>
                    </div>

                    <div class="rd-table-footer">
                        <p id="rdShowingText">Showing 0-0 of 0 invoices</p>
                        <div class="rd-pagination" id="rdPagination"></div>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script src="/lab_sync/public/js/billingDashboard.js?v=20260415b"></script>
</body>
</html>
