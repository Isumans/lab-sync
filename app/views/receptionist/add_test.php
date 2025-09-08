<?php


?>

<html>
    <head>
        <title>Test Catalog</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div >
                    <h1>Add New Test</h1>
                    <p class="MC-p">Dashboard>Test-Catalog>Add-New-Test</p>
                    <form class="Tmain-content formStyle" action="/lab_sync/index.php?controller=TestCatalog&action=store" method="POST">
                        <label for="test-name">Test Name:</label>
                        <input type="text" id="test-name" name="test-name" required>
                        <label for="test-category">Category:</label>
                        <select id="test-category" name="test-category" required>
                            <option value="">Select Category</option>
                            <option value="blood">Blood Tests</option>
                            <option value="urine">Urine Tests</option>
                            <option value="imaging">Imaging</option>
                            <option value="molecular">Molecular Tests</option>

                        </select>
                        <label for="test-code">Test Code:</label>
                        <input type="text" id="test-code" name="test-code" required>
                        <label for="test-description">Description:</label>
                        <textarea id="test-description" name="test-description" required></textarea>

                        <label for="test-price">Price:</label>
                        <input type="number" id="test-price" name="test-price" required>

                        <label for="test-status">Status:</label>
                        <select id="test-status" name="test-status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <button type="submit">Add Test</button>
                    </form>
                </div>
            </main>
        </div>
    </body>
</html>