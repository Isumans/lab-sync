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

    <title>Add New Item</title>
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
                    </div>
                    <div>
                        <p class="MC-p">Inventory->Create New Item</p>
                    </div>
                    <br/>
                     <div>
                        <form class="formStyle" action="/lab_sync/index.php?controller=inventoryController&action=store" method="POST">
                            <!-- Form fields for adding a new inventory item -->
                            <label for="item_name">Item Name:</label>
                            <input type="text" id="item_name" name="item_name" required>

                            <label for="quantity">Item quantity:</label>
                            <input type="text" id="quantity" name="quantity" required>

                            <label for="reorder_level">Reorder Level:</label>
                            <input type="number" id="reorder_level" name="reorder_level" required>

                            <label for="supplier_id">Supplier ID:</label>
                            <input type="text" id="supplier_id" name="supplier_id" required>
                            <button type="submit">Add Item</button>

                        </form>
                    </div>
                    
    </body>
</html>