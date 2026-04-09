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

    <title>Suppliers</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/suppliers.css">
    <link rel="stylesheet" href="/lab_sync/public/supplierEditModal.css">
    <link rel="stylesheet" href="/lab_sync/public/supplierDeleteModal.css">
    <link rel="stylesheet" href="/lab_sync/public/supplierViewModal.css">
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
        <div class="container">
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
                                    <th class="supplier-col-id">SUPPLIER ID</th>
                                    <th class="supplier-col-name">NAME</th>
                                    <th class="supplier-col-email">EMAIL</th>
                                    <th class="supplier-col-contact">CONTACT NUMBER</th>
                                    <th class="supplier-col-items">SUPPLYING ITEMS</th>
                                    <th class="supplier-col-actions">ACTIONS</th>
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
                                            <td class="user-actions supplier-actions-cell">
                                                <button
                                                    type="button"
                                                    class="action-btn-view"
                                                    title="View"
                                                    aria-label="View supplier"
                                                    data-supplier-id="<?php echo $supplierId; ?>"
                                                    data-supplier-name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"
                                                    data-supplier-items="<?php echo htmlspecialchars($supplierItems, ENT_QUOTES); ?>"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M1.5 12s3.8-6 10.5-6 10.5 6 10.5 6-3.8 6-10.5 6S1.5 12 1.5 12z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </button>
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
                                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M16.5 3.5a2.1 2.1 0 113 3L7 19l-4 1 1-4 12.5-12.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="action-btn-delete"
                                                    title="Delete"
                                                    data-supplier-id="<?php echo $supplierId; ?>"
                                                    data-supplier-name="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>"
                                                >
                                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                        <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="supplier-empty-row">No suppliers available. Add your first supplier.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php require 'C:\xampp\htdocs\lab_sync\app\views\administrator\supplier_edit_modal.php'; ?>

                    <?php require 'C:\xampp\htdocs\lab_sync\app\views\administrator\supplier_delete_modal.php'; ?>

                    <?php require 'C:\xampp\htdocs\lab_sync\app\views\administrator\supplier_view_modal.php'; ?>
                </section>
            </main>

        </div>
        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/suppliersViewModal.js"></script>
        <script src="/lab_sync/public/js/suppliersEditModal.js"></script>
        <script src="/lab_sync/public/js/suppliersDeleteModal.js"></script>
    </body>


</html>