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
        <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
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
                    <div class="main-content-header">
                        <div class="main-topic">
                            <h1>Inventory</h1>
                            <a class="add-user-button" href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">+ Create New Item</a>
                        </div>
                        <p class="MC-p">Inventory-&gt;</p>
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
                           <div class="team-table-container">
                                <table class="team-users-table">
                                    <thead>
                                        <tr>
                                            <th>INVENTORY ID</th>
                                            <th>ITEM NAME</th>
                                            <th>SUPPLIER ID</th>
                                            <th>QUANTITY</th>
                                            <th>REORDER LEVEL</th>
                                            <th>ACTIONS</th>
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
                                                    <td class="user-actions">
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
                                        <tr><td colspan="6" style="text-align: center; padding: 40px;">No items found or database error.</td></tr>
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