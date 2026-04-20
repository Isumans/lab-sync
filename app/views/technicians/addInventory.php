<?php
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}

$categories = isset($categories) && is_array($categories) ? $categories : [];
$suppliers = isset($suppliers) && is_array($suppliers) ? $suppliers : [];

$createError = $_SESSION['inventory_create_error'] ?? '';
$createSuccess = $_SESSION['inventory_create_success'] ?? '';
unset($_SESSION['inventory_create_error'], $_SESSION['inventory_create_success']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Item</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css?v=20260414-1">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">

</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="Inventory create">
                     <?php
                $pageTitle = 'Inventory';
                $pageBreadcrumbText = 'Inventory->Create New Item';
                $pageActionHtml = '<a class="add-user-button" href="/lab_sync/index.php?controller=inventoryController&action=index">Back to Inventory</a>';
                require __DIR__ . '/../../../public/partials/page-header.php';
            ?>

            <?php if ($createError !== ''): ?>
                <div class="inv-create-banner is-error"><?php echo htmlspecialchars($createError); ?></div>
            <?php endif; ?>
            <?php if ($createSuccess !== ''): ?>
                <div class="inv-create-banner is-success"><?php echo htmlspecialchars($createSuccess); ?></div>
            <?php endif; ?>

            <form id="inventoryCreateForm" class="inv-create-layout" action="/lab_sync/index.php?controller=inventoryController&action=store" method="POST">
                <section class="inv-create-main-column">
                    <article class="inv-create-card">
                        <header class="inv-create-card-header">
                            <h2>Item Definition</h2>
                        </header>

                        <div class="inv-create-grid-single">
                            <label for="item_name">Item Name <span>(Required)</span></label>
                            <input type="text" id="item_name" name="item_name" placeholder="e.g Sterile Nitrile Gloves - Large" required>
                        </div>

                        <div class="inv-create-grid-two">
                            
                            <div>
                                <label for="unit_of_measure">Unit of Measure</label>
                                <select id="unit_of_measure" name="unit_of_measure" required>
                                    <option value="Units">Units</option>
                                    <option value="Box">Box</option>
                                    <option value="Pack">Pack</option>
                                    <option value="Kit">Kit</option>
                                    <option value="Vial">Vial</option>
                                    <option value="Bottle">Bottle</option>
                                </select>
                            </div>
                        </div>

                        <div class="inv-create-grid-single">
                            <label for="reorder_level">Reorder Level</label>
                            <input type="number" id="reorder_level" name="reorder_level" min="0" value="0" required>
                            <small>Alert when stock drops below this number.</small>
                        </div>
                    </article>

                    <article class="inv-create-card">
                        <header class="inv-create-card-header">
                            <h2>Supplier Sources</h2>
                            <span>Step 02</span>
                        </header>

                        <div class="inv-create-grid-single">
                            <label>Supplier Price Sources</label>
                            <p class="inv-source-help">Add one or more suppliers for this item and set each supplier's unit cost.</p>
                            <div id="supplierSourcesList" class="inv-supplier-sources-list"></div>
                            <input type="hidden" id="primary_source_index" name="primary_source_index" value="0">
                            <button type="button" id="addSupplierSourceRow" class="inv-create-btn-secondary inv-source-add-btn">+ Add Another Supplier Source</button>
                        </div>

                        <div class="inv-create-grid-single">
                            <label for="supplierSearch">Search Existing Supplier</label>
                            <input type="text" id="supplierSearch" placeholder="Type supplier name, contact, or email...">
                            <div id="supplier-suggestions" class="supplier-suggestions" style="display: none;"></div>
                        </div>

                        <div class="inv-create-inline-actions">
                            <div class="inv-create-grid-single">
                                <label for="supplier_id">Initial Intake Supplier</label>
                                <select id="supplier_id" name="supplier_id">
                                    <option value="">Select supplier (optional)</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo intval($supplier['supplier_id']); ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button id="openSupplierModal" type="button" class="inv-create-btn-secondary">Create New Supplier</button>
                        </div>
                    </article>
                </section>

                <aside class="inv-create-side-column">
                    <article class="inv-create-card inv-create-side-card">
                        <header class="inv-create-card-header">
                            <h2>Stock Intake</h2>
                        </header>

                        <div class="inv-create-grid-single">
                            <label for="quantity">Initial Quantity</label>
                            <input type="number" id="quantity" name="quantity" min="0" value="0" required>
                        </div>

                        <div class="inv-create-grid-single">
                            <label for="batch_number">Batch Number</label>
                            <input type="text" id="batch_number" name="batch_number" placeholder="BTCH-0000">
                        </div>

                        <div class="inv-create-grid-single">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date">
                        </div>

                    </article>


                    <article class="inv-create-card inv-create-summary-card">
                        <h3>Registration Summary</h3>
                        <p>Intake Value: <strong id="summaryIntakeValue">LKR 0.00</strong></p>
                        <p>Compliance: <strong id="summaryCompliance" class="is-incomplete">Incomplete</strong></p>
                    </article>

                    <div class="inv-create-footer-actions">
                        <a class="inv-create-btn-secondary" href="/lab_sync/index.php?controller=inventoryController&action=index">Cancel</a>
                        <button type="submit" class="inv-create-btn-primary">Create Item</button>
                    </div>
                </aside>
            </form>
            
            </section>
       
        </main>
    </div>

    <div id="createSupplierModal" class="inv-supplier-modal" aria-hidden="true">
        <div class="inv-supplier-dialog" role="dialog" aria-modal="true" aria-labelledby="createSupplierTitle">
            <div class="inv-supplier-header">
                <h3 id="createSupplierTitle">Create New Supplier</h3>
                <button id="closeSupplierModal" type="button" aria-label="Close supplier modal">&times;</button>
            </div>

            <form id="createSupplierForm" class="inv-supplier-form">
                <div id="supplierFormMessage" class="inv-supplier-message" hidden></div>

                <label for="supplier_name">Supplier Name <span>(Required)</span></label>
                <input type="text" id="supplier_name" name="supplier_name" required>

                <label for="supplier_contact">Contact Number</label>
                <input type="text" id="supplier_contact" name="contact_no">

                <label for="supplier_location">Location</label>
                <input type="text" id="supplier_location" name="location">

                <label for="supplier_email">Email</label>
                <input type="email" id="supplier_email" name="email">

                <div class="inv-supplier-actions">
                    <button type="button" id="cancelSupplierModal" class="inv-create-btn-secondary">Cancel</button>
                    <button type="submit" class="inv-create-btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <template id="supplierSourceRowTemplate">
        <div class="inv-supplier-source-row" data-row-index="__INDEX__">
            <div class="inv-source-row-fields">
                <div class="inv-source-field">
                    <label>Supplier</label>
                    <select name="source_supplier_id[]" class="inv-source-supplier-select">
                        <option value="">Select supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo intval($supplier['supplier_id']); ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="inv-source-field">
                    <label>Unit Cost (LKR)</label>
                    <input type="number" name="source_unit_cost[]" class="inv-source-unit-cost" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="inv-source-field inv-source-primary-wrap">
                    <label>Primary</label>
                    <input type="radio" name="source_primary_radio" class="inv-source-primary-radio" value="__INDEX__">
                </div>
            </div>
            <button type="button" class="inv-source-remove-btn" aria-label="Remove supplier source">Remove</button>
        </div>
    </template>

    <script src="/lab_sync/public/js/inventoryCreateItem.js?v=20260415-2"></script>
</body>
</html>