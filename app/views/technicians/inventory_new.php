<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>
    <title>Inventory Management</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.9em;
        }
        .status-in-stock { background-color: #d4edda; color: #155724; }
        .status-low-stock { background-color: #fff3cd; color: #856404; }
        .status-out-of-stock { background-color: #f8d7da; color: #721c24; }
        
        .modal { position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); display: none; }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; border-radius: 8px; width: 400px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif; box-sizing: border-box; }
        .form-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .btn-primary { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary:hover { background-color: #45a049; }
        .btn-secondary { background-color: #ccc; color: black; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-secondary:hover { background-color: #bbb; }
        
        .category-section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 8px; background-color: #f9f9f9; }
        .category-header { font-size: 1.2em; font-weight: bold; color: #333; margin-bottom: 10px; }
        .category-items { list-style: none; padding-left: 20px; }
        .category-items li { padding: 5px 0; color: #555; }
        .category-items li:before { content: "→ "; color: #4CAF50; font-weight: bold; }
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
            <div class="Tmain-content">
                <div class="test-catalog-header">
                    <h1>Inventory Management System</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="add-test-button"><a href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">+Create New Item</a></button>
                        <button class="add-test-button" id="addCategoryBtn">+Add Category</button>
                    </div>
                </div>
                <div>
                    <p class="MC-p">Inventory Management →</p>
                </div>

                <!-- Dashboard Cards -->
                <div class="container-cards">
                    <div class="card c-card">
                        <h3>Total Items</h3>
                        <h1><?php echo $stats['total_items'] ?? 0; ?></h1>
                        <p>In System</p>
                    </div>
                    <div class="card c-card">
                        <h3>Low Stock Items</h3>
                        <h1><?php echo $stats['low_stock'] ?? 0; ?></h1>
                        <p>Need Reordering</p>
                    </div>
                    <div class="card c-card">
                        <h3>Out of Stock Items</h3>
                        <h1><?php echo $stats['out_of_stock'] ?? 0; ?></h1>
                        <p>Zero Quantity</p>
                    </div>
                    <div class="card c-card">
                        <h3>Total Categories</h3>
                        <h1><?php echo $stats['total_categories'] ?? 0; ?></h1>
                        <p>Categories</p>
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
                        <h2>All Inventory Items</h2>
                        <p>Manage all inventory items with supplier information and stock status.</p>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Supplier ID</th>
                                        <th>Supplier Name</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Category</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (is_array($items) && count($items) > 0): ?>
                                        <?php foreach ($items as $item): ?>
                                            <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=edit_item" class="editForm">
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['inventory_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['supplier_id'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($item['supplier_name'] ?? 'N/A'); ?></td>
                                                    <td><input class="form1" name="item_name" type="text" value="<?php echo htmlspecialchars($item['item_name']); ?>"></td>
                                                    <td><input class="form1" name="quantity" type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>"></td>
                                                    <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                                                    <td><input class="form1" name="reorder_level" type="number" value="<?php echo htmlspecialchars($item['reorder_level']); ?>"></td>
                                                    <td>
                                                        <?php 
                                                            $statusClass = 'status-in-stock';
                                                            $status = $item['status'] ?? 'In Stock';
                                                            if ($status === 'Out of Stock') $statusClass = 'status-out-of-stock';
                                                            elseif ($status === 'Low Stock') $statusClass = 'status-low-stock';
                                                        ?>
                                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="inventory_id" value="<?php echo htmlspecialchars($item['inventory_id']); ?>">
                                                        <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($item['supplier_id'] ?? ''); ?>">
                                                        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($item['category_id'] ?? ''); ?>">
                                                        <button type="submit" name="edit" class="edit-button" onclick="showAlertAndSubmit(event,'edit')"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                                        <button type="submit" name="delete" class="delete-button" onclick="showAlertAndSubmit(event,'delete')"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                                    </td>
                                                </tr>
                                            </form>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="9">No items found. <a href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">Add one now</a>.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ============ STOCK HISTORY TAB ============ -->
                    <div id="stockHistory" class="section" style="display:none;">
                        <h2>Stock Purchase History</h2>
                        <p>View recent inventory purchases and stock transactions.</p>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Purchase ID</th>
                                        <th>Supplier Name</th>
                                        <th>Item Name</th>
                                        <th>Quantity Purchased</th>
                                        <th>Unit Cost</th>
                                        <th>Total Cost</th>
                                        <th>Purchase Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (is_array($stockPurchases) && count($stockPurchases) > 0): ?>
                                        <?php foreach ($stockPurchases as $purchase): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($purchase['inventory_id']); ?></td>
                                                <td><?php echo htmlspecialchars($purchase['purchase_id']); ?></td>
                                                <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                                <td><?php echo htmlspecialchars($purchase['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($purchase['quantity_purchased']); ?></td>
                                                <td>$<?php echo number_format($purchase['unit_cost'], 2); ?></td>
                                                <td>$<?php echo number_format($purchase['total_cost'] ?? 0, 2); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($purchase['purchase_date']))); ?></td>
                                                <td><?php echo htmlspecialchars($purchase['notes'] ?? '-'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="9">No purchase history found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ============ CATEGORIES TAB ============ -->
                    <div id="categories" class="section" style="display:none;">
                        <h2>Inventory Categories</h2>
                        <p>View all inventory categories and their associated items.</p>
                        
                        <?php if (is_array($categoriesWithItems) && count($categoriesWithItems) > 0): ?>
                            <?php foreach ($categoriesWithItems as $category): ?>
                                <div class="category-section">
                                    <div class="category-header">
                                        📁 <?php echo htmlspecialchars($category['category_name']); ?>
                                        <span style="color: #999; font-size: 0.9em; font-weight: normal;">
                                            (<?php echo count($category['items'] ?? []); ?> items)
                                        </span>
                                        <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=edit_category" style="display: inline-block; float: right;">
                                            <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                            <button type="submit" name="edit_btn" class="edit-button" style="padding: 5px 10px; margin-right: 5px;"><img src="/lab_sync/public/assests/edit.png" alt="Edit" style="height: 16px;"></button>
                                            <button type="submit" name="delete_btn" class="delete-button" style="padding: 5px 10px;" onclick="showAlertAndSubmit(event,'delete')"><img src="/lab_sync/public/assests/delete.png" alt="Delete" style="height: 16px;"></button>
                                        </form>
                                    </div>
                                    <p style="color: #666; margin: 10px 0; font-size: 0.95em;">
                                        <?php echo htmlspecialchars($category['description'] ?? 'No description'); ?>
                                    </p>
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
                                        <p style="color: #999; margin: 10px 0;">No items in this category yet.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No categories found. <a href="#" onclick="showAddCategoryModal()">Create one now</a>.</p>
                        <?php endif; ?>

                        <div style="margin-top: 20px; padding: 20px; background-color: #f5f5f5; border-radius: 8px; border-left: 4px solid #4CAF50;">
                            <h3 style="margin-top: 0;">Add New Category</h3>
                            <button class="btn-primary" onclick="showAddCategoryModal()" style="margin-top: 10px;">+ Add Category</button>
                        </div>
                    </div>
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

        document.getElementById('addCategoryBtn').addEventListener('click', showAddCategoryModal);

        window.onclick = function(event) {
            const modal = document.getElementById('addCategoryModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>