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

    <title>Suppliers</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/suppliers.css">
</head>
    <body>
        <?php
        $supplierError = $_SESSION['supplier_error'] ?? '';
        $supplierSuccess = $_SESSION['supplier_success'] ?? '';
        unset($_SESSION['supplier_error'], $_SESSION['supplier_success']);

        $role = $_GET['role'] ?? '';
        $roleParam = $role !== '' ? '&role=' . urlencode($role) : '';
        $suppliers = $suppliers ?? [];
        $totalSuppliers = $totalSuppliers ?? count($suppliers);
        $totalItems = $totalItems ?? 0;
        $searchBy = $searchBy ?? ($_GET['supplier-search-by'] ?? 'email');
        $searchQuery = $searchQuery ?? ($_GET['supplier-search-query'] ?? '');
        ?>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="page-wrapper">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="main-content-header">
                    <h1>Suppliers</h1>
                    <p class="MC-p">Suppliers-></p>
                </div>

                <section class="section suppliers-section">
                    <div class="team-header-container">
                        <div class="team-header">
                            <h2>Supplier Management</h2>
                            <button class="add-user-button"><a href="/lab_sync/index.php?controller=supplierController&action=register<?php echo $roleParam; ?>">Register New Supplier</a></button>
                        </div>

                        <div class="team-stats-grid">
                            <div class="stat-card-team">
                                <div class="stat-label-team">TOTAL SUPPLIERS</div>
                                <div class="stat-value-team"><?php echo (int) $totalSuppliers; ?></div>
                                <div class="stat-change">Live from database</div>
                            </div>

                            <div class="stat-card-team">
                                <div class="stat-label-team">TOTAL SUPPLY ITEMS</div>
                                <div class="stat-value-team"><?php echo (int) $totalItems; ?></div>
                                <div class="stat-change">Across all suppliers</div>
                            </div>
                        </div>
                    </div>

                    <?php if ($supplierError !== ''): ?>
                        <p class="supplier-alert supplier-alert-error"><?php echo htmlspecialchars($supplierError); ?></p>
                    <?php endif; ?>

                    <?php if ($supplierSuccess !== ''): ?>
                        <p class="supplier-alert supplier-alert-success"><?php echo htmlspecialchars($supplierSuccess); ?></p>
                    <?php endif; ?>

                    <form class="team-controls" method="GET" action="/lab_sync/index.php">
                        <input type="hidden" name="controller" value="supplierController">
                        <input type="hidden" name="action" value="index">
                        <?php if ($role !== ''): ?>
                            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                        <?php endif; ?>

                        <select class="supplier-filter" id="supplier-search-by" name="supplier-search-by" aria-label="Search by">
                            <option value="email" <?php echo $searchBy === 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="supplier_id" <?php echo $searchBy === 'supplier_id' ? 'selected' : ''; ?>>Supplier ID</option>
                        </select>

                        <input
                            type="text"
                            class="team-search-bar"
                            name="supplier-search-query"
                            placeholder="Search suppliers..."
                            aria-label="Search suppliers"
                            value="<?php echo htmlspecialchars($searchQuery); ?>"
                        >

                        <button type="submit" class="supplier-modal-save">Search</button>
                        <a class="supplier-register-cancel" href="/lab_sync/index.php?controller=supplierController&action=index<?php echo $roleParam; ?>">Reset</a>
                    </form>

                    <div class="team-table-container">
                        <table class="team-users-table" aria-label="All suppliers table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">SUPPLIER ID</th>
                                    <th style="width: 22%;">NAME</th>
                                    <th style="width: 18%;">EMAIL</th>
                                    <th style="width: 14%;">CONTACT NUMBER</th>
                                    <th style="width: 23%;">SUPPLYING ITEMS</th>
                                    <th style="width: 15%; text-align: center;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($suppliers)): ?>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <?php
                                        $name = $supplier['supplier_name'] ?? '';
                                        $email = $supplier['email'] ?? '';
                                        $contact = $supplier['contact_no'] ?? '';
                                        $supplierItems = $supplier['supplying_items'] ?? '';
                                        $supplierId = (int) ($supplier['supplier_id'] ?? 0);

                                        $initialsParts = preg_split('/\s+/', trim($name));
                                        $initials = '';
                                        foreach (array_slice($initialsParts, 0, 2) as $part) {
                                            $initials .= strtoupper(substr($part, 0, 1));
                                        }
                                        if ($initials === '') {
                                            $initials = 'NA';
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $supplierId; ?></td>
                                            <td class="user-name-cell">
                                                <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                                <div class="user-info">
                                                    <div class="user-name"><?php echo htmlspecialchars($name); ?></div>
                                                    <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($email); ?></td>
                                            <td><?php echo htmlspecialchars($contact); ?></td>
                                            <td><?php echo htmlspecialchars($supplierItems !== '' ? $supplierItems : 'No items listed'); ?></td>
                                            <td class="user-actions" style="justify-content:center;">
                                                <button
                                                    type="button"
                                                    class="action-btn-edit edit-supplier-btn"
                                                    title="Edit"
                                                    data-supplier-id="<?php echo $supplierId; ?>"
                                                    data-supplier-name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"
                                                    data-supplier-email="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>"
                                                    data-supplier-contact="<?php echo htmlspecialchars($contact, ENT_QUOTES); ?>"
                                                    data-supplier-items="<?php echo htmlspecialchars($supplierItems, ENT_QUOTES); ?>"
                                                >
                                                    <img src="/lab_sync/public/assests/edit.png" alt="Edit">
                                                </button>
                                                <button
                                                    type="button"
                                                    class="action-btn-delete"
                                                    title="Delete"
                                                    data-supplier-id="<?php echo $supplierId; ?>"
                                                    data-supplier-name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"
                                                >
                                                    <img src="/lab_sync/public/assests/delete.png" alt="Delete">
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;">No suppliers available. Add your first supplier.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="editSupplierModal" class="supplier-modal-overlay" aria-hidden="true">
                        <div class="supplier-modal" role="dialog" aria-modal="true" aria-labelledby="editSupplierTitle">
                            <div class="supplier-modal-header">
                                <h3 id="editSupplierTitle">Edit Supplier</h3>
                                <button type="button" id="closeSupplierModal" class="supplier-modal-close" aria-label="Close">&times;</button>
                            </div>

                            <form id="editSupplierForm" class="supplier-modal-form" method="POST" action="/lab_sync/index.php?controller=supplierController&action=update<?php echo $roleParam; ?>">
                                <div class="supplier-modal-field">
                                    <label for="edit_supplier_id">Supplier ID</label>
                                    <input id="edit_supplier_id" name="edit_supplier_id" type="text" readonly>
                                </div>

                                <div class="supplier-modal-field">
                                    <label for="edit_supplier_name">Name</label>
                                    <input id="edit_supplier_name" name="edit_supplier_name" type="text" required>
                                </div>

                                <div class="supplier-modal-field">
                                    <label for="edit_supplier_email">Email</label>
                                    <input id="edit_supplier_email" name="edit_supplier_email" type="email" required>
                                </div>

                                <div class="supplier-modal-field">
                                    <label for="edit_supplier_contact">Contact Number</label>
                                    <input id="edit_supplier_contact" name="edit_supplier_contact" type="text" required>
                                </div>

                                <div class="supplier-modal-field">
                                    <label for="edit_supplier_items">Supplying Items</label>
                                    <textarea id="edit_supplier_items" name="edit_supplier_items" rows="3" required></textarea>
                                </div>

                                <div class="supplier-modal-field">
                                    <label for="edit_supplier_item_search">Add New Item</label>
                                    <input
                                        id="edit_supplier_item_search"
                                        type="text"
                                        placeholder="Add new item..."
                                        autocomplete="off"
                                    >
                                    <div id="edit_supplier_item_results" class="supplier-item-results" role="listbox" aria-label="Edit supplier item results"></div>
                                </div>

                                <div class="supplier-modal-actions">
                                    <button type="button" id="cancelSupplierEdit" class="supplier-modal-cancel">Cancel</button>
                                    <button type="submit" class="supplier-modal-save">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <form id="deleteSupplierForm" method="POST" action="/lab_sync/index.php?controller=supplierController&action=delete<?php echo $roleParam; ?>" style="display:none;">
                        <input type="hidden" id="delete_supplier_id" name="supplier_id" value="">
                    </form>
                </section>
            </main>

        </div>
        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/suppliersEditModal.js"></script>
    </body>


</html>
