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

    </body>
</html>