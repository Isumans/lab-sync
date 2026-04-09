<?php
// Session already started in bootstrap.php and controller
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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
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
            z-index: 2000; 
            left: 0; 
            top: 0; 
            width: 100vw; 
            height: 100vh; 
            background-color: rgba(0, 0, 0, 0.5); 
            display: none; 
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 32px 32px 18px 32px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            width: 540px;
            max-width: 96vw;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            position: relative;
        }
        .edit-modal-wide {
            width: 540px;
            max-width: 96vw;
        }
        .edit-modal-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: #22303a;
        }
        .modal-content .form-group {
            margin-bottom: 18px;
        }
        .modal-content .form-group label {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 7px;
            color: #222;
        }
        .modal-content .form-group input,
        .modal-content .form-group select {
            font-size: 1.08rem;
            padding: 13px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 7px;
            background: #f8fafc;
            transition: border-color 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .edit-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 24px;
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
    <div class="page-wrapper">
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
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Supplier ID</th>
                                    <th>Quantity</th>
                                    <th>Category</th>
                                    <th>Reorder Level</th>
                                    <th>Unit Cost</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($items) && count($items) > 0): ?>
                                    <?php $rowNum = 1; foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo $rowNum++; ?></td>
                                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['supplier_id'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td><?php echo htmlspecialchars($item['reorder_level']); ?></td>
                                            <td><?php echo isset($item['unit_cost']) ? '$' . number_format($item['unit_cost'], 2) : '-'; ?></td>
                                            <td><?php echo isset($item['expiry_date']) && $item['expiry_date'] ? htmlspecialchars(date('M d, Y', strtotime($item['expiry_date']))) : '-'; ?></td>
                                            <td>
                                                <?php 
                                                    $statusClass = 'status-in-stock';
                                                    $status = $item['status'] ?? 'In Stock';
                                                    if ($status === 'Out of Stock') $statusClass = 'status-out-of-stock';
                                                    elseif ($status === 'Low Stock') $statusClass = 'status-low-stock';
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                                            </td>
                                            <td style="text-align: center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                                                <button class="action-btn-view" title="View" onclick='openViewModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, "UTF-8"); ?>)'>
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="display:block;margin:auto;">
                                                        <path d="M12 5C7 5 2.73 8.11 2 12c.73 3.89 5 7 10 7s9.27-3.11 10-7c-.73-3.89-5-7-10-7Zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10Zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" fill="#374151"/>
                                                        <circle cx="12" cy="12" r="1.5" fill="#fff"/>
                                                    </svg>
                                                </button>
                                                <button class="action-btn-edit" title="Edit" onclick="openEditModal(this, <?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                        <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                                <form method="post" action="/lab_sync/index.php?controller=inventoryController&action=delete_item" style="display:inline;">
                                                    <input type="hidden" name="inventory_id" value="<?php echo htmlspecialchars($item['inventory_id']); ?>">
                                                    <button type="submit" name="delete" class="action-btn-delete" title="Delete">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                        <!-- Redesigned View Item Modal -->
                                        <div id="viewItemModal" class="modal">
                                            <div class="modal-content redesigned-view-modal">
                                                <div class="modal-header">
                                                    <div class="header-content">
                                                        <h2>Inventory Item Details</h2>
                                                        <p id="view_inventory_id_display" class="uppercase-spaced">Inventory ID: #2</p>
                                                    </div>
                                                    <button type="button" class="modal-close-icon" onclick="closeViewModal()">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="modal-body-styled">
                                                    <div class="view-card-grid">
                                                        <!-- Item Profile -->
                                                        <div class="view-data-card">
                                                            <div class="view-card-label">ITEM PROFILE</div>
                                                            <div class="view-card-id" id="view_item_id_text">Item ID: #2</div>
                                                            <div class="view-card-value" id="view_item_name_text">Blood Collection Tubes</div>
                                                        </div>

                                                        <!-- Supplier Details -->
                                                        <div class="view-data-card">
                                                            <div class="view-card-label flex-between">
                                                                SUPPLIER DETAILS
                                                                <span id="view_status_pill_top" class="pill-minimal">In Stock</span>
                                                            </div>
                                                            <div class="view-card-id" id="view_supplier_id_text">Supplier ID: #2</div>
                                                            <div class="view-card-value" id="view_supplier_name_text">HealthPlus Distributors</div>
                                                        </div>

                                                        <!-- Stock Details -->
                                                        <div class="view-data-card">
                                                            <div class="view-card-label">STOCK DETAILS</div>
                                                            <div class="view-flex-row">
                                                                <div>
                                                                    <div class="view-sub-label">Current Quantity</div>
                                                                    <div class="view-card-value-mid" id="view_quantity_text">120 Units</div>
                                                                </div>
                                                                <div class="text-right">
                                                                    <div class="view-sub-label">Reorder Level</div>
                                                                    <div class="view-card-value-mid" id="view_reorder_level_text">30 Units</div>
                                                                </div>
                                                            </div>
                                                            <div id="view_status_pill_bottom" class="pill-minimal mt-8">In Stock</div>
                                                        </div>

                                                        <!-- Pricing Details -->
                                                        <div class="view-data-card">
                                                            <div class="view-card-label">STOCK DETAILS</div>
                                                            <div class="pricing-content">
                                                                <div class="price-row">
                                                                    <span class="price-text">Unit Cost</span>
                                                                    <span class="price-value-cyan" id="view_unit_cost_text">$0.00</span>
                                                                </div>
                                                                <div class="price-row">
                                                                    <span class="price-text">Total Cost</span>
                                                                    <span class="price-value-bold" id="view_total_cost_text">$0.00</span>
                                                                </div>
                                                                <div class="price-formula" id="view_formula_text">(120 units x $0.00) = $0.00</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Visualization Section -->
                                                    <div class="visualization-container">
                                                        <div class="viz-title">QUANTITY VS REORDER</div>
                                                        <div class="viz-labels">
                                                            <span class="viz-label-left" id="viz_reorder_label">reorder: 30</span>
                                                            <span class="viz-label-right" id="viz_current_label">current: <b>120</b></span>
                                                        </div>
                                                        <div class="viz-bar-bg">
                                                            <div class="viz-bar-fill" id="viz_bar_fill"></div>
                                                            <div class="viz-bar-marker" id="viz_bar_marker"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer-styled">
                                                    <button type="button" class="btn-cancel-new" onclick="closeViewModal()">Close</button>
                                                    <button type="button" class="btn-submit-new" id="view_edit_btn_action">Edit Item</button>
                                                </div>
                                            </div>
                                        </div>
                                        <style>
                                            .action-btn-view {
                                                width: 36px;
                                                height: 36px;
                                                border: none;
                                                border-radius: 6px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                cursor: pointer;
                                                background: #f3f4f6;
                                                color: #374151;
                                                margin-right: 0;
                                                transition: background 0.2s, color 0.2s;
                                            }
                                            .action-btn-view:hover {
                                                background: #e5e7eb;
                                                color: #111;
                                            #viewItemModal {
                                                position: fixed;
                                                top: 0;
                                                left: 0;
                                                width: 100vw;
                                                height: 100vh;
                                                background: rgba(0,0,0,0.5);
                                                display: none; 
                                                align-items: center;
                                                justify-content: center;
                                                z-index: 2000;
                                            }
                                            .redesigned-view-modal {
                                                width: 750px;
                                                max-width: 95vw;
                                                background: #fff;
                                                border-radius: 20px;
                                                box-shadow: 0 15px 50px rgba(0,0,0,0.15);
                                                display: flex;
                                                flex-direction: column;
                                                font-family: 'DM Sans', sans-serif;
                                                overflow: hidden;
                                            }
                                            .view-card-grid {
                                                display: grid;
                                                grid-template-columns: 1fr 1fr;
                                                gap: 16px;
                                                margin-bottom: 24px;
                                            }
                                            .view-data-card {
                                                background: #fbfcfe;
                                                border: 1px solid #f1f5f9;
                                                border-radius: 12px;
                                                padding: 16px 20px;
                                                display: flex;
                                                flex-direction: column;
                                            }
                                            .view-card-label {
                                                font-size: 11px;
                                                font-weight: 800;
                                                color: #94a3b8;
                                                letter-spacing: 0.8px;
                                                margin-bottom: 12px;
                                                text-transform: uppercase;
                                            }
                                            .flex-between { display: flex; justify-content: space-between; align-items: center; }
                                            .view-card-id {
                                                font-size: 13px;
                                                color: #64748b;
                                                margin-bottom: 4px;
                                            }
                                            .view-card-value {
                                                font-size: 1.15rem;
                                                font-weight: 700;
                                                color: #2c3e50;
                                            }
                                            .view-card-value-mid {
                                                font-size: 1rem;
                                                font-weight: 700;
                                                color: #2c3e50;
                                            }
                                            .view-sub-label {
                                                font-size: 12px;
                                                color: #94a3b8;
                                                font-weight: 600;
                                                margin-bottom: 4px;
                                            }
                                            .view-flex-row { display: flex; justify-content: space-between; }
                                            .pill-minimal {
                                                background: #eefdf6;
                                                color: #10b981;
                                                padding: 4px 14px;
                                                border-radius: 50px;
                                                font-size: 11px;
                                                font-weight: 700;
                                                display: inline-block;
                                            }
                                            .pill-minimal.out { background: #fef2f2; color: #ef4444; }
                                            .pill-minimal.low { background: #fffbeb; color: #f59e0b; }

                                            .pricing-content { display: flex; flex-direction: column; gap: 6px; }
                                            .price-row { display: flex; justify-content: space-between; align-items: baseline; }
                                            .price-text { font-size: 14px; color: #2c3e50; font-weight: 700; }
                                            .price-value-cyan { font-size: 1.15rem; color: #3dbdec; font-weight: 700; }
                                            .price-value-bold { font-size: 1.15rem; color: #1e293b; font-weight: 700; }
                                            .price-formula {
                                                font-size: 11px;
                                                color: #94a3b8;
                                                margin-top: 4px;
                                                font-weight: 500;
                                            }

                                            .visualization-container {
                                                border-top: 1px solid #f1f5f9;
                                                padding: 24px 0 10px 0;
                                            }
                                            .viz-title {
                                                font-size: 12px;
                                                font-weight: 800;
                                                color: #3dbdec;
                                                letter-spacing: 0.8px;
                                                margin-bottom: 12px;
                                                text-transform: uppercase;
                                            }
                                            .viz-labels {
                                                display: flex;
                                                justify-content: space-between;
                                                font-size: 14px;
                                                color: #64748b;
                                                margin-bottom: 10px;
                                            }
                                            .viz-bar-bg {
                                                height: 8px;
                                                background: #f1f5f9;
                                                border-radius: 4px;
                                                position: relative;
                                                overflow: visible;
                                            }
                                            .viz-bar-fill {
                                                height: 100%;
                                                background: #3dbdec;
                                                border-radius: 4px;
                                                transition: width 0.3s ease;
                                                position: absolute;
                                                left: 0;
                                                top: 0;
                                            }
                                            .viz-bar-marker {
                                                position: absolute;
                                                top: -4px;
                                                width: 3px;
                                                height: 16px;
                                                background: #f59e0b;
                                                box-shadow: 0 0 5px rgba(245, 158, 11, 0.4);
                                                z-index: 2;
                                                transition: left 0.3s ease;
                                            }

                                            .mt-10 { margin-top: 10px; }
                                            .text-right { text-align: right; }

                                            @media (max-width: 600px) {
                                                .view-card-grid { grid-template-columns: 1fr; }
                                                .redesigned-view-modal { width: 95vw; }
                                            }
                                        </style>

                                        <script>
                                        function openViewModal(item) {
                                            document.getElementById('viewItemModal').style.display = 'flex';
                                            
                                            // Header content
                                            document.getElementById('view_inventory_id_display').textContent = 'Inventory ID: ' + item.inventory_id;
                                            
                                            // Item Profile
                                            document.getElementById('view_item_id_text').textContent = 'Item ID: #' + (item.inventory_id || '—');
                                            document.getElementById('view_item_name_text').textContent = item.item_name || '— not set —';
                                            
                                            // Supplier Details
                                            document.getElementById('view_supplier_id_text').textContent = 'Supplier ID: #' + (item.supplier_id || '—');
                                            document.getElementById('view_supplier_name_text').textContent = item.supplier_name || '— not set —';
                                            
                                            // Stock Details
                                            var qty = parseInt(item.quantity) || 0;
                                            var reorder = parseInt(item.reorder_level) || 0;
                                            var unitSuffix = item.unit_of_measure || 'Units';
                                            document.getElementById('view_quantity_text').textContent = qty + ' ' + unitSuffix;
                                            document.getElementById('view_reorder_level_text').textContent = reorder + ' ' + unitSuffix;
                                            
                                            // Pricing Details
                                            var unitCost = parseFloat(item.unit_cost) || 0;
                                            var totalCost = qty * unitCost;
                                            document.getElementById('view_unit_cost_text').textContent = '$' + unitCost.toFixed(2);
                                            document.getElementById('view_total_cost_text').textContent = '$' + totalCost.toFixed(2);
                                            document.getElementById('view_formula_text').textContent = '(' + qty + ' units x $' + unitCost.toFixed(2) + ') = $' + totalCost.toFixed(2);
                                            
                                            // Status Pills
                                            var statusText = item.status || 'In Stock';
                                            var lowerStatus = statusText.toLowerCase();
                                            var pills = [document.getElementById('view_status_pill_top'), document.getElementById('view_status_pill_bottom')];
                                            
                                            pills.forEach(pill => {
                                                if (!pill) return;
                                                pill.textContent = statusText;
                                                pill.className = 'pill-minimal';
                                                if (lowerStatus === 'out of stock') pill.classList.add('out');
                                                else if (lowerStatus === 'low stock') pill.classList.add('low');
                                            });

                                            // Visualization
                                            document.getElementById('viz_reorder_label').textContent = 'reorder: ' + reorder;
                                            document.getElementById('viz_current_label').innerHTML = 'current: <b>' + qty + '</b>';
                                            
                                            var totalMax = Math.max(qty, reorder, 10); 
                                            var fillWidth = (qty / totalMax) * 100;
                                            var markerPos = (reorder / totalMax) * 100;
                                            
                                            document.getElementById('viz_bar_fill').style.width = Math.min(fillWidth, 100) + '%';
                                            document.getElementById('viz_bar_marker').style.left = Math.min(markerPos, 100) + '%';

                                            // Edit button action
                                            document.getElementById('view_edit_btn_action').onclick = function() {
                                                closeViewModal();
                                                openEditModal(null, item);
                                            };
                                        }

                                        function closeViewModal() {
                                            document.getElementById('viewItemModal').style.display = 'none';
                                        }

                                        function openEditModalFromView() {
                                            // Placeholder for modal transition
                                        }
                                        </script>
                                        <!-- Redesigned Edit Item Modal -->
                                        <div id="editItemModal" class="modal">
                                            <div class="modal-content redesigned-edit-modal">
                                                <div class="modal-header">
                                                    <div class="header-content">
                                                        <h2>Edit Inventory Item</h2>
                                                        <p class="subtitle uppercase-spaced">UPDATE THE PRODUCT DETAILS IN THE CENTRAL LABORATORY DATABASE.</p>
                                                    </div>
                                                    <button type="button" class="modal-close-icon" onclick="closeEditModal()">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <form id="editItemForm" method="post" action="/lab_sync/index.php?controller=inventoryController&action=update_item">
                                                    <input type="hidden" name="inventory_id" id="edit_inventory_id">
                                                    
                                                    <div class="modal-body-styled">
                                                        <!-- BASIC INFORMATION -->
                                                        <div class="form-section-card">
                                                            <h3 class="card-section-title">BASIC INFORMATION</h3>
                                                            <div class="grid-row">
                                                                <div class="form-field field-66">
                                                                    <label>Item Name</label>
                                                                    <input type="text" name="item_name" id="edit_item_name" placeholder="Blood Collection Tube" required>
                                                                </div>
                                                                <div class="form-field field-33">
                                                                    <label>Category</label>
                                                                    <select name="category_id" id="edit_category_id" required>
                                                                        <?php foreach ($categories as $cat): ?>
                                                                            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="grid-row" style="margin-top: 15px;">
                                                                <div class="form-field field-100">
                                                                    <label>Supplier</label>
                                                                    <select name="supplier_id" id="edit_supplier_id" required>
                                                                        <?php foreach ($suppliers as $sup): ?>
                                                                            <option value="<?php echo $sup['supplier_id']; ?>"><?php echo htmlspecialchars($sup['supplier_id'] . ' - ' . $sup['supplier_name']); ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- STOCK INFORMATION -->
                                                        <div class="form-section-card">
                                                            <h3 class="card-section-title">STOCK INFORMATION</h3>
                                                            <div class="grid-row">
                                                                <div class="form-field">
                                                                    <label>Unit Cost(Rs.)</label>
                                                                    <input type="number" name="unit_cost" id="edit_unit_cost" step="0.01" min="0" placeholder="0.00" required>
                                                                </div>
                                                                <div class="form-field">
                                                                    <label>Initial Quantity</label>
                                                                    <input type="number" name="quantity" id="edit_quantity" placeholder="0" required>
                                                                </div>
                                                            </div>
                                                            <div class="grid-row">
                                                                <div class="form-field">
                                                                    <label>Unit of Measure</label>
                                                                    <select name="unit_of_measure" id="edit_unit_of_measure">
                                                                        <option value="Units">Units</option>
                                                                        <option value="Boxes">Boxes</option>
                                                                        <option value="Packs">Packs</option>
                                                                        <option value="Liters">Liters</option>
                                                                        <option value="Tubes">Tubes</option>
                                                                        <option value="Grams">Grams</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-field">
                                                                    <label>Reorder Level</label>
                                                                    <input type="number" name="reorder_level" id="edit_reorder_level" placeholder="50" required>
                                                                </div>
                                                            </div>
                                                            <div class="grid-row">
                                                                <div class="form-field">
                                                                    <label>Purchase Date</label>
                                                                    <input type="date" name="purchase_date" id="edit_purchase_date">
                                                                </div>
                                                                <div class="form-field">
                                                                    <label>Expiry Date</label>
                                                                    <input type="date" name="expiry_date" id="edit_expiry_date" placeholder="mm/dd/yyyy">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer-styled">
                                                        <button type="button" class="btn-cancel-new" onclick="closeEditModal()">Cancel</button>
                                                        <button type="submit" class="btn-submit-new">Update Item</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <style>
                                            .redesigned-edit-modal {
                                                width: 850px;
                                                max-width: 98vw;
                                                max-height: 90vh;
                                                background: #fff;
                                                border-radius: 16px;
                                                box-shadow: 0 10px 40px rgba(0,0,0,0.15);
                                                overflow: hidden;
                                                display: flex;
                                                flex-direction: column;
                                                border: none;
                                                font-family: 'DM Sans', sans-serif;
                                            }
                                            .redesigned-edit-modal form {
                                                display: flex;
                                                flex-direction: column;
                                                flex: 1;
                                                overflow: hidden;
                                            }
                                            .modal-header {
                                                padding: 14px 10px;
                                                display: flex;
                                                justify-content: space-between;
                                                align-items: center;
                                                border-bottom: 1.5px solid #f1f5f9;
                                                background: #fff;
                                                flex-shrink: 0;
                                            }
                                            .header-content h2 {
                                                margin: 0;
                                                font-size: 1.15rem;
                                                font-weight: 700;
                                                color: #2c3e50;
                                            }
                                            .uppercase-spaced {
                                                text-transform: uppercase;
                                                letter-spacing: 0.8px;
                                                font-size: 10px;
                                                font-weight: 700;
                                                color: #94a3b8;
                                                margin-top: 2px;
                                            }
                                            .modal-close-icon {
                                                background: none;
                                                border: none;
                                                color: #64748b;
                                                cursor: pointer;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                padding: 6px;
                                                transition: all 0.2s;
                                            }
                                            .modal-close-icon:hover { 
                                                color: #ef4444; 
                                                background: #fee2e2;
                                                border-radius: 50%;
                                            }

                                            .modal-body-styled {
                                                padding: 12px 10px;
                                                background: #fff;
                                                overflow-y: auto;
                                                flex: 1;
                                            }
                                            .form-section-card {
                                                background: #fbfcfe;
                                                border: 1px solid #eef2f6;
                                                border-radius: 12px;
                                                padding: 10px 10px;
                                                margin-bottom: 12px;
                                            }
                                            .card-section-title {
                                                font-size: 10px;
                                                font-weight: 800;
                                                color: #475569;
                                                margin-bottom: 10px;
                                                margin-top: 0;
                                                letter-spacing: 0.8px;
                                                text-transform: uppercase;
                                            }
                                            .grid-row {
                                                display: flex;
                                                gap: 12px;
                                                margin-bottom: 12px;
                                            }
                                            .grid-row:last-child { margin-bottom: 0; }

                                            .form-field { flex: 1; display: flex; flex-direction: column; }
                                            .field-66 { flex: 2; }
                                            .field-33 { flex: 1; }
                                            .field-100 { flex: 1; width: 100%; }

                                            .form-field label {
                                                font-size: 10px;
                                                font-weight: 700;
                                                color: #475569;
                                                margin-bottom: 4px;
                                                text-transform: uppercase;
                                                letter-spacing: 0.3px;
                                            }
                                            .form-field input, 
                                            .form-field select {
                                                padding: 8px 12px;
                                                border: 1px solid #d1d5db;
                                                border-radius: 8px;
                                                font-size: 13px;
                                                background: #fff;
                                                color: #1e293b;
                                                transition: all 0.2s;
                                                width: 100%;
                                                box-sizing: border-box;
                                            }
                                            .form-field input::placeholder {
                                                color: #94a3b8;
                                            }
                                            .form-field input:focus, 
                                            .form-field select:focus {
                                                outline: none;
                                                border-color: #3dbdec;
                                                box-shadow: 0 0 0 3px rgba(61, 189, 236, 0.1);
                                            }
                                            
                                            .modal-footer-styled {
                                                padding: 12px 10px;
                                                display: flex;
                                                justify-content: flex-end;
                                                gap: 12px;
                                                border-top: 1.5px solid #f1f5f9;
                                                background: #fff;
                                                flex-shrink: 0;
                                            }
                                            .btn-cancel-new {
                                                background: #f1f5f9;
                                                color: #2c3e50;
                                                border: none;
                                                padding: 8px 18px;
                                                border-radius: 8px;
                                                font-weight: 700;
                                                font-size: 13px;
                                                cursor: pointer;
                                                transition: all 0.2s;
                                            }
                                            .btn-cancel-new:hover { background: #e2e8f0; }

                                            .btn-submit-new {
                                                background: #3dbdec;
                                                color: #fff;
                                                border: none;
                                                padding: 8px 24px;
                                                border-radius: 8px;
                                                font-weight: 700;
                                                font-size: 13px;
                                                cursor: pointer;
                                                transition: all 0.2s;
                                            }
                                            .btn-submit-new:hover { 
                                                background: #25aada; 
                                                transform: translateY(-1px);
                                            }

                                            @media (max-width: 600px) {
                                                .grid-row { flex-direction: column; gap: 8px; }
                                                .modal-header, .modal-body-styled, .modal-footer-styled { padding: 12px 10px; }
                                            }
                                        </style>

                                        <script>
                                        function openEditModal(btn, item) {
                                            document.getElementById('editItemModal').style.display = 'flex';
                                            document.getElementById('edit_inventory_id').value = item.inventory_id || '';
                                            document.getElementById('edit_item_name').value = item.item_name || '';
                                            document.getElementById('edit_category_id').value = item.category_id || '';
                                            document.getElementById('edit_supplier_id').value = item.supplier_id || '';
                                            document.getElementById('edit_quantity').value = item.quantity || '';
                                            document.getElementById('edit_reorder_level').value = item.reorder_level || '';
                                            document.getElementById('edit_unit_cost').value = item.unit_cost || '';
                                            document.getElementById('edit_unit_of_measure').value = item.unit_of_measure || 'Units';
                                            document.getElementById('edit_purchase_date').value = item.purchase_date ? item.purchase_date.split(' ')[0] : '';
                                            document.getElementById('edit_expiry_date').value = item.expiry_date ? item.expiry_date.split(' ')[0] : '';
                                        }
                                        function closeEditModal() {
                                            document.getElementById('editItemModal').style.display = 'none';
                                        }
                                        window.onclick = function(event) {
                                            var modal = document.getElementById('editItemModal');
                                            if (event.target == modal) {
                                                closeEditModal();
                                            }
                                        }
                                        </script>
                                        </tr>
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
                                    <th>Supplier Name</th>
                                    <th>Item Name</th>
                                    <th>Quantity Purchased</th>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                    <th>Purchase Date</th>
                                    <th>Expiry Date</th>
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
                                            <td><?php echo htmlspecialchars($purchase['expiry_date'] ?? '-'); ?></td>
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
                                                $icon = inventoryModel::getCategoryIcon($category['category_name']);
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
    <script src="/lab_sync/public/js/inventorySoftDelete.js"></script>
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
