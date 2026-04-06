<?php
// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (!defined('MODEL_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}
require_once MODEL_PATH . '/inventoryModel.php';
?>
<html>
<head>
    <title>Inventory Management</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
            min-width: 65px;
            text-align: center;
        }
        .status-in-stock { 
            background-color: #d1fae5; 
            color: #065f46; 
        }
        .status-low-stock { 
            background-color: #fff3cd; 
            color: #856404; 
        }
        .status-out-of-stock { 
            background-color: #f8d7da; 
            color: #721c24; 
        }

        /* Main Header */
        .main-content-header {
            margin-bottom: 30px;
        }

        .main-content-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 5px 0;
        }

        .main-content-header .MC-p {
            color: #9ca3af;
            font-size: 14px;
            margin: 0;
        }

        /* Inventory Header with Stats */
        .inventory-header-container {
            margin-bottom: 40px;
        }

        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .inventory-header h2 {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .add-inventory-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .add-inventory-button:hover {
            background-color: #2da5d4;
        }

        .add-inventory-button a {
            color: white;
            text-decoration: none;
        }

        /* Inventory Stats Grid */
        .inventory-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Modal Styling */
        .modal { 
            position: fixed; 
            z-index: 1; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.4); 
            display: none; 
        }

        .modal-content { 
            background-color: #fefefe; 
            margin: 10% auto; 
            padding: 20px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .close { 
            color: #aaa; 
            float: right; 
            font-size: 28px; 
            font-weight: bold; 
            cursor: pointer; 
        }

        .close:hover { 
            color: #000; 
        }

        .form-group { 
            margin-bottom: 15px; 
        }

        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 500;
            color: #1f2937;
        }

        .form-group input, 
        .form-group textarea, 
        .form-group select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #e5e7eb; 
            border-radius: 6px; 
            font-family: Arial, sans-serif; 
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(61, 189, 236, 0.1);
        }

        .form-actions { 
            display: flex; 
            gap: 10px; 
            justify-content: flex-end; 
            margin-top: 20px; 
        }

        .btn-primary { 
            background-color: var(--primary-color); 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover { 
            background-color: #2da5d4; 
        }

        .btn-secondary { 
            background-color: #e5e7eb; 
            color: #1f2937; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover { 
            background-color: #d1d5db; 
        }

        /* Category Section */
        .category-section { 
            margin-bottom: 20px; 
            border: 1px solid #e5e7eb; 
            padding: 20px; 
            border-radius: 8px; 
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .category-header { 
            font-size: 18px; 
            font-weight: 600; 
            color: #1f2937; 
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-header > div:last-child {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .category-header form {
            display: flex !important;
            flex-direction: row !important;
            gap: 8px !important;
            align-items: center !important;
        }

        .category-description {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .category-item-form {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }

        .category-item-form p {
            margin: 0 0 10px 0;
            font-weight: 500;
            color: #1f2937;
            font-size: 14px;
        }

        .category-item-form form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .category-item-form input,
        .category-item-form button {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
        }

        .category-items { 
            list-style: none; 
            padding: 0;
            margin: 0;
        }

        .category-items li { 
            padding: 10px 0; 
            color: #4b5563;
            border-bottom: 1px solid #f0f1f3;
            display: flex;
            align-items: center;
        }

        .category-items li:last-child {
            border-bottom: none;
        }

        .category-items li:before { 
            content: "→ "; 
            color: var(--primary-color); 
            font-weight: bold;
            margin-right: 10px;
        }

        /* Action Buttons */
        .action-btn-edit,
        .action-btn-delete {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .action-btn-edit {
            background-color: var(--primary-color);
            color: white;
        }

        .action-btn-edit:hover {
            background-color: #2da5d4;
            color: white;
        }

        .action-btn-delete {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .action-btn-delete:hover {
            background-color: #fecaca;
            color: #7f1d1d;
        }

        /* Inventory Table */
        .inventory-table-container {
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table thead {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .inventory-table thead tr th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .inventory-table tbody tr {
            border-bottom: 1px solid #f0f1f3;
            transition: background-color 0.2s ease;
        }

        .inventory-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .inventory-table tbody tr td {
            padding: 15px;
            color: #1f2937;
            font-size: 14px;
        }

        .inventory-table tbody tr td input {
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        .inventory-table tbody tr td input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .inventory-table tbody tr td[style*="text-align: center;"] {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Content Area */
        .content-area {
            margin-top: 20px;
        }

        .section {
            margin-top: 20px;
            padding: 0;
            border-radius: 10px;
            min-height: 300px;
        }

        .section h2 {
            margin-top: 0;
            color: var(--font-color);
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .section > p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <!-- Sidebar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <!-- Main Body Section -->
        <main class="main-content">
            <div class="main-content-header">
                <h1>Inventory Management</h1>
                <p class="MC-p">Inventory Management →</p>
            </div>

            <!-- Dashboard Stats -->
            <div class="inventory-header-container">
                <div class="inventory-stats-grid">
                    <div class="stat-card-team">
                        <div class="stat-label-team">Total Items</div>
                        <div class="stat-value-team stat-counter" data-target="<?php echo $stats['total_items'] ?? 0; ?>">0</div>
                        <div class="stat-change">In System</div>
                    </div>

                    <div class="stat-card-team">
                        <div class="stat-label-team">Low Stock Items</div>
                        <div class="stat-value-team stat-counter" data-target="<?php echo $stats['low_stock'] ?? 0; ?>">0</div>
                        <div class="stat-change">Need Reordering</div>
                    </div>

                    <div class="stat-card-team">
                        <div class="stat-label-team">Out of Stock Items</div>
                        <div class="stat-value-team stat-counter" data-target="<?php echo $stats['out_of_stock'] ?? 0; ?>">0</div>
                        <div class="stat-change">Zero Quantity</div>
                    </div>

                    <div class="stat-card-team">
                        <div class="stat-label-team">Total Categories</div>
                        <div class="stat-value-team stat-counter" data-target="<?php echo $stats['total_categories'] ?? 0; ?>">0</div>
                        <div class="stat-change">Categories</div>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="nav-bar-container">
                <div class="nav-bar-line">
                    <a class="navItem active" onclick="showSection('allItems', event)" href="#">All Items</a>
                    <a class="navItem" onclick="showSection('stockHistory', event)" href="#">Stock History</a>
                    <a class="navItem" onclick="showSection('categories', event)" href="#">Categories</a>
                </div>
            </div>

            <div id="content-area" class="content-area">
                <!-- ============ ALL ITEMS TAB ============ -->
                <div id="allItems" class="section">
                    <div class="inventory-header">
                        <h2>All Inventory Items</h2>
                        <button class="add-inventory-button">
                            <a href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">+ Create New Item</a>
                        </button>
                    </div>
                    <p>Manage all inventory items with supplier information and stock status.</p>
                    
                    <div class="inventory-table-container">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Supplier ID</th>
                                    <th>Quantity</th>
                                    <th>Category</th>
                                    <th>Reorder Level</th>
                                    <th>Status</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($items) && count($items) > 0): ?>
                                    <?php foreach ($items as $item): ?>
                                        <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=edit_item">
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['inventory_id']); ?></td>
                                                <td><input name="item_name" type="text" value="<?php echo htmlspecialchars($item['item_name']); ?>"></td>
                                                <td><?php echo htmlspecialchars($item['supplier_id'] ?? '-'); ?></td>
                                                <td><input name="quantity" type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>"></td>
                                                <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                                                <td><input name="reorder_level" type="number" value="<?php echo htmlspecialchars($item['reorder_level']); ?>"></td>
                                                <td>
                                                    <?php 
                                                        $statusClass = 'status-in-stock';
                                                        $status = $item['status'] ?? 'In Stock';
                                                        if ($status === 'Out of Stock') $statusClass = 'status-out-of-stock';
                                                        elseif ($status === 'Low Stock') $statusClass = 'status-low-stock';
                                                    ?>
                                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="hidden" name="inventory_id" value="<?php echo htmlspecialchars($item['inventory_id']); ?>">
                                                    <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($item['supplier_id'] ?? ''); ?>">
                                                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($item['category_id'] ?? ''); ?>">
                                                    <button type="submit" name="edit" class="action-btn-edit" title="Edit" onclick="showAlertAndSubmit(event,'edit')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                    <button type="submit" name="delete" class="action-btn-delete" title="Delete" onclick="showAlertAndSubmit(event,'delete')">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </form>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <p>No items found. <a href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">Add one now</a></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============ STOCK HISTORY TAB ============ -->
                <div id="stockHistory" class="section" style="display:none;">
                    <h2>Stock Purchase History</h2>
                    <p>View recent inventory purchases and stock transactions.</p>
                    
                    <div class="inventory-table-container">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Purchase ID</th>
                                    <th>Supplier ID</th>
                                    <th>Item Name</th>
                                    <th>Quantity Purchased</th>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                    <th>Purchase Date</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($stockHistory) && count($stockHistory) > 0): ?>
                                    <?php foreach ($stockHistory as $history): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($history['inventory_id']); ?></td>
                                            <td><?php echo htmlspecialchars($history['purchase_id'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($history['supplier_id'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($history['item_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($history['quantity']); ?></td>
                                            <td>Rs. <?php echo number_format($history['unit_cost'] ?? 0, 2); ?></td>
                                            <td>Rs. <?php echo number_format(($history['quantity'] * ($history['unit_cost'] ?? 0)), 2); ?></td>
                                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($history['created_at']))); ?></td>
                                            <td><?php echo htmlspecialchars($history['expiry_date'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <p>No stock history records found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============ CATEGORIES TAB ============ -->
                <div id="categories" class="section" style="display:none;">
                    <div class="inventory-header">
                        <h2>Inventory Categories</h2>
                        <button class="add-inventory-button" onclick="showAddCategoryModal()">+ Add Category</button>
                    </div>
                    <p>Manage all inventory categories and their associated items.</p>
                    
                    <?php if (is_array($categoriesWithItems) && count($categoriesWithItems) > 0): ?>
                        <?php foreach ($categoriesWithItems as $category): ?>
                            <div class="category-section">
                                <div class="category-header">
                                    <div>
                                        <span style="font-size: 20px; margin-right: 10px;">
                                            <?php 
                                                $inventoryModel = new inventoryModel();
                                                $icon = $inventoryModel->getCategoryIcon($category['category_name']);
                                                echo htmlspecialchars($icon);
                                            ?> 
                                        </span>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                        <span style="color: #9ca3af; font-size: 13px; font-weight: normal; margin-left: 10px;">
                                            (<?php echo count($category['items'] ?? []); ?> items)
                                        </span>
                                    </div>
                                    <div>
                                        <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=edit_category">
                                            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                            <button type="submit" name="edit_btn" class="action-btn-edit" title="Edit">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button type="submit" name="delete_btn" class="action-btn-delete" title="Delete" onclick="showAlertAndSubmit(event,'delete')">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <p class="category-description">
                                    <?php echo htmlspecialchars($category['description'] ?? 'No description'); ?>
                                </p>

                                <!-- Add Item to Category Form -->
                                <div class="category-item-form">
                                    <p>Add Item to this Category:</p>
                                    <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=add_item_to_category">
                                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                        <input type="number" name="inventory_id" id="item_id_<?php echo $category['category_id']; ?>" 
                                               placeholder="Enter item ID" required style="flex: 1;">
                                        <input type="text" id="item_name_<?php echo $category['category_id']; ?>" 
                                               placeholder="Item name" disabled style="flex: 1; background-color: #f3f4f6;">
                                        <button type="submit" class="btn-primary" style="padding: 8px 16px; margin: 0;">Add to Category</button>
                                    </form>
                                </div>

                                <!-- Items List -->
                                <?php if (is_array($category['items']) && count($category['items']) > 0): ?>
                                    <ul class="category-items">
                                        <?php foreach ($category['items'] as $item): ?>
                                            <li>
                                                <strong>ID <?php echo htmlspecialchars($item['inventory_id']); ?>:</strong> 
                                                <?php echo htmlspecialchars($item['item_name']); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p style="color: #9ca3af; margin: 10px 0;">No items in this category yet.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No categories found. <a href="#" onclick="showAddCategoryModal()">Create one now</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddCategoryModal()">&times;</span>
            <h2>Add New Category</h2>
            <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=add_category">
                <div class="form-group">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3" placeholder="Describe this category..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Add Category</button>
                    <button type="button" class="btn-secondary" onclick="closeAddCategoryModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/lab_sync/public/js/showSection.js"></script>
    <script src="/lab_sync/public/js/showAlert.js"></script>
    <script>
        function showAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'block';
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('addCategoryModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Animate counter function
        function animateCounter(element, duration = 1500) {
            const target = parseInt(element.getAttribute('data-target'), 10);
            const increment = target / (duration / 16); // Assuming 60fps
            let current = 0;
            
            const counter = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(counter);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 16);
        }

        // Start counter animation when page loads
        function startCounterAnimation() {
            const counters = document.querySelectorAll('.stat-counter');
            counters.forEach(counter => {
                counter.textContent = '0'; // Reset to 0
                animateCounter(counter);
            });
        }

        // Call animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            startCounterAnimation();

            // Find all category item input fields and attach listeners
            const categoryInputs = document.querySelectorAll('input[name="inventory_id"]');
            categoryInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    const itemId = this.value.trim();
                    const categoryId = this.closest('form').querySelector('input[name="category_id"]').value;
                    const itemNameField = document.getElementById('item_name_' + categoryId);
                    
                    if (itemId && itemNameField) {
                        fetch('/lab_sync/index.php?controller=inventoryController&action=get_item_name&inventory_id=' + itemId)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    itemNameField.value = data.item_name;
                                } else {
                                    itemNameField.value = 'Item not found';
                                    itemNameField.style.color = 'red';
                                }
                            })
                            .catch(error => {
                                itemNameField.value = 'Error loading item';
                                itemNameField.style.color = 'red';
                            });
                    }
                });
            });
        });
    </script>
</body>
</html>