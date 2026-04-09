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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">

    <title>Register New Supplier</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/regSupplier.css">
    <link rel="stylesheet" href="/lab_sync/public/regSupplierModal.css">
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
    <div class="page-wrapper">
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

                <form class="supplier-register-form"
                    action="/lab_sync/index.php?controller=supplierController&action=store<?php echo $roleParam; ?>"
                    method="POST">
                    <div class="supplier-register-block">
                         <h2 class="supplier-information-title">Supplier Details</h2>

                        <div class="supplier-register-fields">
                            <div class="supplier-register-field">
                                <label for="supplier_name">Name</label>
                                <input class="supplier-input-name" type="text" id="supplier_name" name="supplier_name"
                                    placeholder="Example Medical"
                                    value="<?php echo htmlspecialchars($supplierOld['supplier_name'] ?? ''); ?>"
                                    required>
                            </div>

                            <div class="supplier-register-field">
                                <label for="company_email">Email</label>
                                <input class="supplier-input-email" type="email" id="company_email" name="company_email"
                                    placeholder="e.g., contact@labsync.com"
                                    value="<?php echo htmlspecialchars($supplierOld['company_email'] ?? ''); ?>"
                                    required>
                            </div>

                            <div class="supplier-register-field">
                                <label for="phone_number">Contact Number</label>
                                <input class="supplier-input-phone" type="text" id="phone_number" name="phone_number"
                                    placeholder="0712345678"
                                    value="<?php echo htmlspecialchars($supplierOld['phone_number'] ?? ''); ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="supplier-register-items">
                        <div class="supplier-items-head">
                            <h2>Supplying Items</h2>
                            <button type="button" id="supplier-new-item-btn" class="supplier-new-item-btn">Add new item</button>
                        </div>

                        <div class="supplier-item-search-wrap">
                            <label for="supplier-item-search" class="supplier-item-search-label">Search Items</label>
                            <input type="text" id="supplier-item-search"
                                class="supplier-item-search supplier-input-search"
                                placeholder="Search and select items..." autocomplete="off">
                            <div id="supplier-item-results" class="supplier-item-results" role="listbox"
                                aria-label="Search results"></div>
                        </div>

                        <p id="supplier-item-empty" class="supplier-item-empty">Start typing to search for more items.
                        </p>
                        <div class="supplier-selected-table" aria-label="Selected supplier items">
                            <div class="supplier-selected-head">
                                <span>Item ID</span>
                                <span>Item Name</span>
                                <span>Category</span>
                                <span>Action</span>
                            </div>
                            <div id="supplier-selected-items" class="supplier-selected-body"></div>
                        </div>
                        <div id="supplier-item-hidden-inputs"></div>
                    </div>

                    <div class="supplier-register-actions">
                        <a class="supplier-register-cancel"
                            href="/lab_sync/index.php?controller=supplierController&action=index<?php echo $roleParam; ?>">Cancel</a>
                        <button class="supplier-register-submit" type="submit">Save Supplier</button>
                    </div>
                </form>
            </section>

            <?php require 'C:\xampp\htdocs\lab_sync\app\views\administrator\supplier_item_modal.php'; ?>
        </main>
    </div>
    <script src="/lab_sync/public/js/supplierItemPicker.js"></script>
</body>

</html>
