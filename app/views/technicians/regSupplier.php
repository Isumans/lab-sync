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
    <title>Create Supplier</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="supplier create">
                <?php
                $pageTitle = 'Create Supplier';
                $pageBreadcrumbText = 'Suppliers->Create Supplier';
                $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=supplierController&action=index">Back to Suppliers</a>';
                require __DIR__ . '/../../../public/partials/page-header.php';
            ?>

            <form id="supplierCreateForm" class="inv-create-layout" novalidate>
                <section class="inv-create-main-column">
                    <article class="inv-create-card">
                        <header class="inv-create-card-header">
                            <h2>Supplier Details</h2>
                            <span>Step 01</span>
                        </header>

                        <div class="inv-create-grid-single">
                            <label for="supplier_name">Supplier Name <span>(Required)</span></label>
                            <input type="text" id="supplier_name" name="supplier_name" placeholder="e.g. Northlane Medical Distributors" required>
                        </div>

                        <div class="inv-create-grid-two">
                            <div>
                                <label for="contact_no">Contact Number</label>
                                <input type="text" id="contact_no" name="contact_no" placeholder="e.g. 0771234567">
                            </div>
                            <div>
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="supplier@example.com">
                            </div>
                        </div>

                        <div class="inv-create-grid-single">
                            <label for="location">Location / Address</label>
                            <input type="text" id="location" name="location" placeholder="e.g. No 15, Main Street, Colombo">
                        </div>
                    </article>
                </section>

                <aside class="inv-create-side-column">
                    <article class="inv-create-compliance-card">
                        <h3>Registration Notes</h3>
                        <p>Provide at least a supplier name and one communication channel to make inventory sourcing easier for your team.</p>
                    </article>

                    <article class="inv-create-card inv-create-summary-card">
                        <h3>Submission Status</h3>
                        <p>Required Fields <strong id="supplierFieldStatus" class="is-incomplete">Incomplete</strong></p>
                    </article>

                    <div id="supplierCreateMessage" class="inv-supplier-message" hidden></div>

                    <div class="inv-create-footer-actions">
                        <a class="inv-create-btn-secondary" href="/lab_sync/index.php?controller=supplierController&action=index">Cancel</a>
                        <button type="submit" class="inv-create-btn-primary">Register Supplier</button>
                    </div>
                </aside>
            </form>

            </section>
            
        </main>
    </div>

    <script src="/lab_sync/public/js/regSupplier.js?v=20260414"></script>
</body>
</html> 