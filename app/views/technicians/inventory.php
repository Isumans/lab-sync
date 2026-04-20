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
    <title>Inventory</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryDetailsModal.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryEditModal.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryDeleteModal.css">
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

    <div id="inventoryDetailsModal" class="inventory-details-modal" aria-hidden="true">
        <div class="inventory-details-dialog" role="dialog" aria-modal="true" aria-labelledby="inventoryDetailsTitle">
            <div class="inventory-details-topbar">
                <div id="inventoryDetailsTitle" class="inventory-details-title">Inventory Details</div>
                <button id="inventoryDetailsClose" type="button" class="inventory-details-close" aria-label="Close details modal">&times;</button>
            </div>
            <div id="inventoryDetailsBody" class="inventory-details-body"></div>
        </div>
    </div>

    <div id="deleteInventoryModal" class="inventory-delete-modal" aria-hidden="true">
        <div class="inventory-delete-dialog" role="dialog" aria-modal="true" aria-labelledby="deleteInventoryTitle">
            <div class="inventory-delete-header">
                <span class="inventory-delete-icon-wrap" aria-hidden="true">!</span>
                <h2 id="deleteInventoryTitle">Delete Item</h2>
                <button id="deleteInventoryClose" type="button" class="inventory-delete-close" aria-label="Close delete modal">&times;</button>
            </div>

            <p class="inventory-delete-copy">This action will archive this inventory item from active records. You can only restore it manually from the database.</p>

            <div id="deleteInventoryAlert" class="inventory-delete-alert" hidden></div>

            <div class="inventory-delete-summary">
                <div class="inventory-delete-summary-label">Inventory Item</div>
                <div id="deleteInventoryNumber" class="inventory-delete-summary-value">INV-0000</div>
                <div class="inventory-delete-summary-label">Item Name</div>
                <div id="deleteInventoryItemName" class="inventory-delete-summary-value">Unknown Item</div>
            </div>

            <button type="button" id="deleteInventoryConfirm" class="inventory-delete-confirm-btn">Delete Item</button>
            <button type="button" id="deleteInventoryCancel" class="inventory-delete-cancel-btn">Cancel</button>

            <div class="inventory-delete-footer-note">SOFT DELETE • AUDIT TRAIL ENABLED</div>
        </div>
    </div>

    <div id="editInventoryModal" class="inventory-edit-modal" aria-hidden="true">
        <div class="inventory-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="editInventoryTitle">
            <form id="editInventoryForm">
                <input type="hidden" id="editInventoryId" name="inventory_id" value="">

                <div class="inventory-edit-header">
                    <div>
                        <h2 id="editInventoryTitle">Edit Inventory Item</h2>
                        <p class="inventory-edit-subtitle">INVENTORY ITEM AND SUPPLIER SOURCES</p>
                    </div>
                    <button id="editInventoryClose" type="button" class="inventory-edit-close" aria-label="Close edit inventory modal">&times;</button>
                </div>

                <div id="editInventoryAlert" class="inventory-edit-alert" hidden></div>

                <div class="inventory-edit-body">
                    <section class="inventory-edit-section-card">
                        <div class="inventory-edit-section-title">
                            <h3>Item Details</h3>
                        </div>

                        <div class="inventory-edit-grid-two">
                            <div class="inventory-edit-field">
                                <label for="editItemName">Item Name</label>
                                <input type="text" id="editItemName" name="item_name" required>
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemStatus">Status</label>
                                <select id="editItemStatus" name="status" required>
                                    <option value="In Stock">In Stock</option>
                                    <option value="Low Stock">Low Stock</option>
                                    <option value="Out of Stock">Out of Stock</option>
                                </select>
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemQuantity">Quantity</label>
                                <input type="number" id="editItemQuantity" name="quantity" min="0" required>
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemReorder">Reorder Level</label>
                                <input type="number" id="editItemReorder" name="reorder_level" min="0" required>
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemUnit">Unit of Measure</label>
                                <input type="text" id="editItemUnit" name="unit_of_measure" placeholder="Units">
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemCategory">Category</label>
                                <select id="editItemCategory" name="category_id">
                                    <option value="">Select category</option>
                                </select>
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemSupplier">Primary Supplier</label>
                                <select id="editItemSupplier" name="supplier_id">
                                    <option value="">Select supplier</option>
                                </select>
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemUnitCost">Item Unit Cost (LKR)</label>
                                <input type="number" id="editItemUnitCost" name="unit_cost" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <div class="inventory-edit-field">
                                <label for="editItemExpiry">Expiry Date</label>
                                <input type="date" id="editItemExpiry" name="expiry_date">
                            </div>
                        </div>
                    </section>

                    <section class="inventory-edit-section-card">
                        <div class="inventory-edit-section-title inventory-edit-section-title-with-action">
                            <h3>Supplier Sources</h3>
                            <button type="button" id="addEditSourceRow" class="inventory-edit-add-source-btn">+ Add Source</button>
                        </div>

                        <div id="editSupplierSourcesList" class="inventory-edit-sources-list"></div>
                        <div class="inventory-edit-sources-hint">Set one source as primary to align item-level supplier and cost.</div>
                    </section>
                </div>

                <div class="inventory-edit-footer">
                    <button type="button" id="editInventoryCancel" class="inventory-edit-cancel-btn">Cancel</button>
                    <button type="submit" id="editInventorySubmit" class="inventory-edit-submit-btn">Update Item</button>
                </div>
            </form>
        </div>
    </div>

    <template id="editSupplierSourceRowTemplate">
        <div class="inventory-edit-source-row" data-row-index="__INDEX__">
            <div class="inventory-edit-source-grid">
                <div class="inventory-edit-field">
                    <label>Supplier</label>
                    <select class="inventory-edit-source-supplier">
                        <option value="">Select supplier</option>
                    </select>
                </div>
                <div class="inventory-edit-field">
                    <label>Unit Cost (LKR)</label>
                    <input type="number" class="inventory-edit-source-unit-cost" min="0" step="0.01" placeholder="0.00">
                </div>
                <div class="inventory-edit-field">
                    <label>Min Order Qty</label>
                    <input type="number" class="inventory-edit-source-min-order" min="0" placeholder="Optional">
                </div>
                <div class="inventory-edit-field">
                    <label>Lead Time Days</label>
                    <input type="number" class="inventory-edit-source-lead-time" min="0" placeholder="Optional">
                </div>
                <div class="inventory-edit-field inventory-edit-primary-wrap">
                    <label>Primary</label>
                    <input type="radio" name="inventory_edit_primary_source" class="inventory-edit-source-primary" value="__INDEX__">
                </div>
            </div>
            <button type="button" class="inventory-edit-remove-source-btn" aria-label="Remove supplier source">Remove</button>
        </div>
    </template>

    <script src="/lab_sync/public/js/showAlert.js"></script>
    <script src="/lab_sync/public/js/inventoryDashboard.js?v=20260415-3"></script>
    <script src="/lab_sync/public/js/inventoryDetailsModal.js?v=20260415-1"></script>
    <script src="/lab_sync/public/js/inventoryEditModal.js?v=20260415-1"></script>
    <script src="/lab_sync/public/js/inventoryDeleteModal.js?v=20260415-1"></script>
</body>
</html>