<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Suppliers</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="Suppliers Dashboard">
                <?php
                    $pageTitle = 'Suppliers';
                    $pageBreadcrumbText = 'Suppliers->';
                    $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=supplierController&action=Register_supplier">Register New Supplier</a>';
                    require __DIR__ . '/../../../public/partials/page-header.php';
                ?>

                <section class="rd-filter-card" aria-label="Search and Filters">
                    <div class="rd-filter-grid">
                        <div class="rd-filter-field rd-filter-field-search">
                            <label for="suppSearch">Search Records</label>
                            <input id="suppSearch" type="text" placeholder="Search by Supplier Name, Supplier ID, Contact, or Email..." />
                        </div>

                        <div class="rd-filter-field">
                            <label for="suppStatus">Status</label>
                            <select id="suppStatus">
                                <option value="all">All Statuses</option>
                            </select>
                        </div>

                        <div class="rd-filter-field">
                            <label for="suppSortBy">Sort By</label>
                            <select id="suppSortBy">
                                <option value="created_at">Registered Date</option>
                                <option value="supplier_id">Supplier ID</option>
                                <option value="supplier_name">Supplier Name</option>
                                <option value="contact_no">Contact</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                    </div>

                    <div class="rd-filter-bottom-row">
                        <div class="rd-filter-date-range">
                            <div class="rd-filter-field">
                                <label for="suppDateFrom">Date Range</label>
                                <input id="suppDateFrom" type="date" />
                            </div>
                            <div class="rd-filter-field rd-filter-field-to">
                                <label for="suppDateTo" class="rd-hidden-label">End Date</label>
                                <input id="suppDateTo" type="date" />
                            </div>
                        </div>

                        <div class="rd-sort-direction-wrap">
                            <select id="suppSortDir" class="rd-sort-direction" aria-label="Sort direction">
                                <option value="desc">Newest First</option>
                                <option value="asc">Oldest First</option>
                            </select>
                            <button type="button" class="rd-clear-btn" id="suppClearBtn">Clear All Filters</button>
                        </div>
                    </div>
                </section>

                <section class="rd-table-card" aria-label="Suppliers Table">
                    <div class="rd-table-wrap">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th class="rd-sortable" data-sort="supplier_id" data-direction="desc">Supplier ID</th>
                                    <th class="rd-sortable" data-sort="supplier_name" data-direction="asc">Supplier Name</th>
                                    <th class="rd-sortable" data-sort="contact_no" data-direction="asc">Contact</th>
                                    <th class="rd-sortable" data-sort="location" data-direction="asc">Location</th>
                                    <th class="rd-sortable" data-sort="email" data-direction="asc">Email</th>
                                    <th class="rd-sortable is-active is-desc" data-sort="created_at" data-direction="desc">Registered Date</th>
                                </tr>
                            </thead>
                            <tbody id="suppTableBody"></tbody>
                        </table>
                    </div>

                    <div class="rd-table-footer">
                        <p id="suppShowingText">Showing 0-0 of 0 suppliers</p>
                        <div class="rd-pagination" id="suppPagination"></div>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script src="/lab_sync/public/js/suppliersDashboard.js?v=20260414"></script>
</body>
</html>
