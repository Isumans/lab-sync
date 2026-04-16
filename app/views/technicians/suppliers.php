<?php
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
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/supplierEditModal.css">
    <link rel="stylesheet" href="/lab_sync/public/supplierDeleteModal.css">
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
                                    <th class="rd-th-right">Actions</th>
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

                <div id="editSupplierModal" class="supplier-edit-modal" aria-hidden="true">
                    <div class="supplier-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="editSupplierTitle">
                        <form id="editSupplierForm" novalidate>
                            <input type="hidden" id="editSupplierId" name="supplier_id" value="">

                            <div class="supplier-edit-header">
                                <div>
                                    <h2 id="editSupplierTitle">Edit Supplier</h2>
                                    <p class="supplier-edit-subtitle">SUPPLIER INFORMATION UPDATE</p>
                                </div>
                                <button id="editSupplierClose" type="button" class="supplier-edit-close" aria-label="Close edit modal">&times;</button>
                            </div>

                            <div id="editSupplierAlert" class="supplier-edit-alert" hidden></div>

                            <div class="supplier-edit-body">
                                <section class="supplier-edit-section-card">
                                    <div class="supplier-edit-section-title">
                                        <h3>Supplier Details</h3>
                                    </div>

                                    <div class="supplier-edit-grid-two">
                                        <div class="supplier-edit-field">
                                            <label for="editSupplierName">Supplier Name</label>
                                            <input type="text" id="editSupplierName" name="supplier_name" required>
                                        </div>
                                        <div class="supplier-edit-field">
                                            <label for="editSupplierContact">Contact Number</label>
                                            <input type="text" id="editSupplierContact" name="contact_no" placeholder="e.g. 0771234567">
                                        </div>
                                        <div class="supplier-edit-field">
                                            <label for="editSupplierLocation">Location</label>
                                            <input type="text" id="editSupplierLocation" name="location" placeholder="City, Region">
                                        </div>
                                        <div class="supplier-edit-field">
                                            <label for="editSupplierEmail">Email Address</label>
                                            <input type="email" id="editSupplierEmail" name="email" placeholder="supplier@example.com">
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="supplier-edit-footer">
                                <button type="button" id="editSupplierCancel" class="supplier-edit-cancel-btn">Cancel</button>
                                <button type="submit" id="editSupplierSubmit" class="supplier-edit-submit-btn">Update Supplier</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="deleteSupplierModal" class="supplier-delete-modal" aria-hidden="true">
                    <div class="supplier-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="deleteSupplierTitle">
                        <div class="supplier-delete-header">
                            <div class="supplier-delete-icon-wrap" aria-hidden="true">!</div>
                            <h2 id="deleteSupplierTitle">Delete Supplier</h2>
                            <button type="button" id="deleteSupplierClose" class="supplier-delete-close" aria-label="Close delete modal">&times;</button>
                        </div>

                        <p class="supplier-delete-copy">Are you sure you want to delete this supplier? This action cannot be undone.</p>
                        <div id="deleteSupplierAlert" class="supplier-delete-alert" hidden></div>

                        <div class="supplier-delete-summary">
                            <div class="supplier-summary-label">ID</div>
                            <div id="deleteSupplierNumber" class="supplier-summary-value">SUP-0000</div>

                            <div class="supplier-summary-label">SUPPLIER NAME</div>
                            <div id="deleteSupplierName" class="supplier-summary-value">Unknown Supplier</div>
                        </div>

                        <button type="button" id="deleteSupplierConfirm" class="supplier-delete-confirm-btn">Delete Supplier</button>
                        <button type="button" id="deleteSupplierCancel" class="supplier-delete-cancel-btn">Cancel</button>
                        <div class="supplier-delete-footer-note">SYSTEM: AUTHORIZATION REQUIRED</div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="/lab_sync/public/js/suppliersDashboard.js?v=20260414"></script>
    <script src="/lab_sync/public/js/supplierEditModal.js?v=20260415"></script>
    <script src="/lab_sync/public/js/supplierDeleteModal.js?v=20260415"></script>
</body>
</html>
