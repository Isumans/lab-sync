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

    <title>Register New Supplier</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/suppliers.css">
</head>
    <body>
        <?php
        $supplierError = $_SESSION['supplier_error'] ?? '';
        $supplierSuccess = $_SESSION['supplier_success'] ?? '';
        $supplierOld = $_SESSION['supplier_old'] ?? [];
        unset($_SESSION['supplier_error'], $_SESSION['supplier_success']);

        $role = $_GET['role'] ?? '';
        $roleParam = $role !== '' ? '&role=' . urlencode($role) : '';
        ?>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="main-content-header supplier-register-header">
                    <h1 class="supplier-register-title">Register New Supplier</h1>
                    <p class="supplier-register-breadcrumb">Suppliers -> Register New Supplier</p>
                </div>

                <section class="section suppliers-section supplier-register-section">
                    <?php if ($supplierError !== ''): ?>
                        <p class="supplier-alert supplier-alert-error"><?php echo htmlspecialchars($supplierError); ?></p>
                    <?php endif; ?>

                    <?php if ($supplierSuccess !== ''): ?>
                        <p class="supplier-alert supplier-alert-success"><?php echo htmlspecialchars($supplierSuccess); ?></p>
                    <?php endif; ?>

                    <form class="supplier-register-form" action="/lab_sync/index.php?controller=supplierController&action=store<?php echo $roleParam; ?>" method="POST">
                        <h2 class="supplier-register-form-title">Supplier Information</h2>

                        <div class="supplier-register-fields">
                            <div class="supplier-register-field">
                                <label for="supplier_name">Name</label>
                                <input type="text" id="supplier_name" name="supplier_name" placeholder="ABC Diagnostics" value="<?php echo htmlspecialchars($supplierOld['supplier_name'] ?? ''); ?>" required>
                            </div>

                            <div class="supplier-register-field">
                                <label for="company_email">Email</label>
                                <input type="email" id="company_email" name="company_email" placeholder="supplier@company.com" value="<?php echo htmlspecialchars($supplierOld['company_email'] ?? ''); ?>" required>
                            </div>

                            <div class="supplier-register-field">
                                <label for="phone_number">Contact Number</label>
                                <input type="text" id="phone_number" name="phone_number" placeholder="0712345678" value="<?php echo htmlspecialchars($supplierOld['phone_number'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="supplier-register-items">
                            <h2>Supplying Items</h2>

                            <div class="supplier-item-search-wrap">
                                <label for="supplier-item-search" class="supplier-item-search-label">Search Items</label>
                                <input type="text" id="supplier-item-search" class="supplier-item-search" placeholder="Type item name to search..." autocomplete="off">
                                <div id="supplier-item-results" class="supplier-item-results" role="listbox" aria-label="Search results"></div>
                            </div>

                            <h3 class="supplier-selected-title">Selected Items</h3>
                            <div id="supplier-selected-items" class="supplier-register-items-list"></div>
                            <p id="supplier-item-empty" class="supplier-item-empty">No items selected.</p>
                            <div id="supplier-item-hidden-inputs"></div>
                        </div>

                        <div class="supplier-register-actions">
                            <a class="supplier-register-cancel" href="/lab_sync/index.php?controller=supplierController&action=index<?php echo $roleParam; ?>">Cancel</a>
                            <button class="supplier-register-submit" type="submit">Save Supplier</button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
        <script src="/lab_sync/public/js/supplierItemPicker.js"></script>
    </body>
</html>