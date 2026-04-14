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
    <title>Inventory</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="Inventory Dashboard">
                <?php
                    $pageTitle = 'Inventory';
                    $pageBreadcrumbText = 'Inventory->';
                    $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">+ Create New Item</a>';
                    require __DIR__ . '/../../../public/partials/page-header.php';
                ?>

                <section class="rd-filter-card" aria-label="Search and Filters">
                    <div class="rd-filter-grid">
                        <div class="rd-filter-field rd-filter-field-search">
                            <label for="invSearch">Search Inventory</label>
                            <input id="invSearch" type="text" placeholder="Search by Item Name, Inventory ID, or Supplier ID..." />
                        </div>

                        <div class="rd-filter-field">
                            <label for="invStatus">Status</label>
                            <select id="invStatus">
                                <option value="all">All Statuses</option>
                                <option value="in stock">In Stock</option>
                                <option value="low stock">Low Stock</option>
                                <option value="out of stock">Out of Stock</option>
                            </select>
                        </div>

                        <div class="rd-filter-field">
                            <label for="invSortBy">Sort By</label>
                            <select id="invSortBy">
                                <option value="last_updated">Last Updated</option>
                                <option value="inventory_id">Inventory ID</option>
                                <option value="item_name">Item Name</option>
                                <option value="supplier_id">Supplier ID</option>
                                <option value="quantity">Quantity</option>
                                <option value="reorder_level">Reorder Level</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>

                    <div class="rd-filter-bottom-row">
                        <div class="rd-filter-date-range">
                            <div class="rd-filter-field">
                                <label for="invDateFrom">Date Range</label>
                                <input id="invDateFrom" type="date" />
                            </div>
                            <div class="rd-filter-field rd-filter-field-to">
                                <label for="invDateTo" class="rd-hidden-label">End Date</label>
                                <input id="invDateTo" type="date" />
                            </div>
                        </div>

                        <div class="rd-sort-direction-wrap">
                            <select id="invSortDir" class="rd-sort-direction" aria-label="Sort direction">
                                <option value="desc">Newest First</option>
                                <option value="asc">Oldest First</option>
                            </select>
                            <button type="button" class="rd-clear-btn" id="invClearBtn">Clear All Filters</button>
                        </div>
                    </div>
                </section>

                <section class="rd-table-card" aria-label="Inventory Table">
                    <div class="rd-table-wrap">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th class="rd-sortable" data-sort="inventory_id" data-direction="desc">Inventory ID</th>
                                    <th class="rd-sortable" data-sort="item_name" data-direction="asc">Item Name</th>
                                    <th class="rd-sortable" data-sort="supplier_id" data-direction="asc">Supplier ID</th>
                                    <th class="rd-sortable" data-sort="quantity" data-direction="desc">Quantity</th>
                                    <th class="rd-sortable" data-sort="reorder_level" data-direction="desc">Reorder Level</th>
                                    <th class="rd-sortable" data-sort="status" data-direction="asc">Status</th>
                                    <th class="rd-sortable is-active is-desc" data-sort="last_updated" data-direction="desc">Last Updated</th>
                                    <th class="rd-th-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="invTableBody"></tbody>
                        </table>
                    </div>

                    <div class="rd-table-footer">
                        <p id="invShowingText">Showing 0-0 of 0 items</p>
                        <div class="rd-pagination" id="invPagination"></div>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <script src="/lab_sync/public/js/showAlert.js"></script>
    <script src="/lab_sync/public/js/inventoryDashboard.js?v=20260414"></script>
</body>
</html>