<?php
session_start();
var_dump($_SESSION);
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>

    <title>Inventory</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
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
                        <h1>Inventory</h1>
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">+Create New Item</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Inventory-></p>
                    </div>
                    <div class="container-cards">
                        <div class="card, c-card">
                                <h3>Total Items</h3>
                                <h1>10</h1>
                                <p>+2%</p>
                                <!-- <img src="assests/chart1.png" alt="Chart 1" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card, c-card">
                            <h3>Low Stock Items</h3>
                            <h1>40</h1>
                            <p>+5%</p>
                            <!-- <img src="assests/chart2.png" alt="Chart 2" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card, c-card">
                            <h3>Out of Stock Items</h3>
                            <h1>5</h1>
                            <p>+1%</p>
                            <!-- <img src="assests/chart3.png" alt="Chart 3" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card, c-card">
                            <h3>Total Stock Value</h3>
                            <h1>$8,000</h1>
                            <!-- <p>+3%</p> -->
                            <!-- <img src="assests/chart4.png" alt="Chart 4" style="width:100%; height:auto;"> -->
                        </div>
                    </div>
                    <div class="search-and-filter patient-search">

                        <select class="search-option" id="item-name" name="item-name" required>
                            <option value="">Item Name</option>
                            <option value="Item ID">Item ID</option>
                        </select>
                        <input type="text" class="search-bar" placeholder="  Search Inventory...">
                        <button class="search-button">Search</button>
                    </div>
                    <div class="nav-bar-container">
                        <div class="nav-bar-line">
                            <a class="navItem active" onclick="showSection('allItems', event)" href="#">All Items</a>


                            <a class="navItem" onclick="showSection('stockHistory', event)" href="#">Stock History</a>

                            <a class="navItem" onclick="showSection('categories', event)" href="#">Categories</a>


                        </div>
                    </div>
                    <div id="content-area" class="content-area">
                       <div id="allItems" class="section">
                           <h2>All Items</h2>
                           <p>View and manage all inventory items here.</p>
                           <div class="user-list">
                                <table class="test-catalog-table">
                                    <thead>
                                        <tr>
                                            <th>Inventory ID</th>
                                            <th>Item Name</th>
                                            <th>Supplier ID</th>
                                            <th>Quantity</th>
                                            <th>Reorder Level</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (is_array($items)): ?>
                                        <?php foreach ($items as $item): ?>
                                            <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=edit_item" class="editForm">

                                                <tr>
                                                    <td><input id="inventory_id" class="form1" name="inventory_id" type="text" value="<?php echo htmlspecialchars($item['inventory_id']); ?>"></td>
                                                    <td><input id="item_name" class="form1" name="item_name" type="text" value="<?php echo htmlspecialchars($item['item_name']); ?>"></td>
                                                    <td><input id="supplier_id" class="form1" name="supplier_id" type="text" value="<?php echo htmlspecialchars($item['supplier_id']); ?>"></td>
                                                    <td><input id="quantity" class="form1" name="quantity" type="text" value="<?php echo htmlspecialchars($item['quantity']); ?>"></td>
                                                    <td><input id="reorder_level" class="form1" name="reorder_level" type="text" value="<?php echo htmlspecialchars($item['reorder_level']); ?>"></td>

                                                    <td>
                                                        <button id="edit" type="submit" name="edit" class="edit-button" onclick="showAlertAndSubmit(event,'edit')"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                                        <button id="delete" type="submit" name="delete" class="delete-button" onclick="showAlertAndSubmit(event,'delete')"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                                    </td>
                                                </tr>
                                            </form>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5">No items found or database error.</td></tr>
                                    <?php endif; ?>
                        
                                    </tbody>
                                </table>
                                </div>
                       </div>
                       <div id="stockHistory" class="section" style="display:none;">
                           <h2>Stock History</h2>
                           <p>View and manage stock history here.</p>
                       </div>
                       <div id="categories" class="section" style="display:none;">
                           <h2>Categories</h2>
                           <p>View and manage categories here.</p>
                       </div>

                    </div>
                </div>
            </main>
        </div>
        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>

    </body>
</html>