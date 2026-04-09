<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
// Initialize variables with defaults from controller
if (!isset($errors)) $errors = [];
if (!isset($formData)) $formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
if (!isset($suppliers)) $suppliers = [];
if (!isset($categories)) $categories = [];
?>
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <title>Add New Inventory Item</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
    <style>
        body {
            background: #f5f6fa;
        }
        .inventory-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(60,80,120,0.07);
            width: 100%;
            max-width: 100%;
            margin: 2vh 0 0 0;
            padding: 18px 24px 12px 24px;
            min-height: unset;
            height: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }
            margin-bottom: 24px;
            color: #222;
        }
        .form-section {
            margin: 14px 0 10px 0;
        }
        .form-section h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: #222;
        }
        .form-row {
            display: flex;
            gap: 14px;
            margin-bottom: 10px;
        }
        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 0.98rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: #222;
        }
        .form-group input,
        .form-group select {
            font-size: 0.98rem;
            padding: 7px 10px;
            border: 1.2px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #3dbdec;
            outline: none;
        }
        .form-group input.error,
        .form-group select.error {
            border-color: #c33;
            background-color: #fee;
        }
        .error-message {
            color: #c33;
            font-size: 13px;
            margin-top: 4px;
            display: none;
        }
        .error-message.show {
            display: block;
        }
        .btn-primary-submit {
            background-color: #3DBDEC;
            color: white;
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        .btn-primary-submit:hover {
            background-color: #2699c7;
        }
        .cancel-button {
            background-color: #e5e7eb;
            color: #333;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            margin-left: 10px;
            transition: background-color 0.2s;
        }
        .cancel-button:hover {
            background-color: #d1d5db;
        }
        .success-alert {
            background-color: #efe;
            border: 1px solid #cfc;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #262;
        }
        .error-alert {
            background-color: #fee;
            border: 1px solid #fcc;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #c33;
        }
        .error-alert h4 {
            margin: 0 0 10px 0;
            color: #a22;
        }
        .error-alert ul {
            margin: 0;
            padding-left: 20px;
        }
        .error-alert li {
            margin: 5px 0;
        }
        .add-supplier-btn {
            background: #e6f6fb;
            color: #2699c7;
            border: none;
            border-radius: 6px;
            padding: 7px 14px;
            font-weight: 600;
            font-size: 0.98rem;
            margin-left: 8px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .add-supplier-btn:hover {
            background: #d0f0fa;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.3);
        }
        .modal-overlay.active {
            display: block;
        }
        .modal-content {
            background: #fff;
            max-width: 400px;
            margin: 60px auto;
            padding: 30px 24px 18px 24px;
            border-radius: 14px;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .modal-content h2 {
            margin-top: 0;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .modal-close-btn {
            position: absolute;
            top: 10px;
            right: 16px;
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
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
            <div class="Tmain-content">
                <!-- Removed upper page title and spacing -->

                <?php if (!empty($errors)): ?>
                    <div class="error-alert">
                        <h4>Error! Please fix the following issues:</h4>
                        <ul>
                            <?php foreach ($errors as $field => $message): ?>
                                <li><?php echo htmlspecialchars($message); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                                <!-- Inventory Management Header -->
                                <div style="background:#f5f6fa; padding:24px 32px 8px 32px; border-radius:8px 8px 0 0;">
                                    <h1 style="font-size:1.725rem; font-weight:600; color:#22303a; margin-bottom:0; line-height:1.1;">Add New Inventory Item</h1>
                                    <a href="/lab_sync/index.php?controller=inventoryController&action=index" style="color:#3dbdec; font-size:0.825rem; margin-top:8px; text-decoration:none; display:inline-block; font-weight:500;">
                                        Inventory Management &rarr;
                                    </a>
                                </div>
                                <div class="inventory-card">
                    <!-- Removed form card title -->
                    <form class="formStyle" action="/lab_sync/index.php?controller=inventoryController&action=store" method="POST">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3>Basic Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="item_name">Item Name: <span style="color: red;">*</span></label>
                                    <input type="text" id="item_name" name="item_name" required placeholder="Enter item name (e.g., Blood Collection Tube)" 
                                           value="<?php echo htmlspecialchars($formData['item_name'] ?? ''); ?>"
                                           class="<?php echo isset($errors['item_name']) ? 'error' : ''; ?>">
                                    <?php if (isset($errors['item_name'])): ?>
                                        <div class="error-message show"><?php echo htmlspecialchars($errors['item_name']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="category_id">Category: <span style="color: red;">*</span></label>
                                    <select id="category_id" name="category_id" required class="<?php echo isset($errors['category_id']) ? 'error' : ''; ?>">
                                        <option value="">-- Select a Category --</option>
                                        <?php 
                                        if (isset($categories) && is_array($categories) && count($categories) > 0) { 
                                            foreach ($categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category['category_id']); ?>"
                                                        <?php echo ($formData['category_id'] ?? '') == $category['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </option>
                                            <?php endforeach;
                                        } else { ?>
                                            <option value="">No categories available</option>
                                        <?php } ?>
                                    </select>
                                    <?php if (isset($errors['category_id'])): ?>
                                        <div class="error-message show"><?php echo htmlspecialchars($errors['category_id']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Information -->
                        <div class="form-section">
                            <h3>Supplier Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="supplier_id">Supplier ID: <span style="color: red;">*</span></label>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input type="number" id="supplier_id" name="supplier_id" required placeholder="Enter supplier ID" 
                                               value="<?php echo htmlspecialchars($formData['supplier_id'] ?? ''); ?>"
                                               class="<?php echo isset($errors['supplier_id']) ? 'error' : ''; ?>">
                                        <button type="button" class="add-supplier-btn" onclick="document.getElementById('addSupplierModal').classList.add('active')">
                                            <span style="font-size:1.2em;line-height:1;">&#43;</span> Add new supplier
                                        </button>
                                    </div>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Available suppliers: 
                                        <strong><?php 
                                            if (isset($suppliers) && is_array($suppliers) && count($suppliers) > 0) {
                                                echo implode(', ', array_map(function($s) { return $s['supplier_id']; }, $suppliers));
                                            } else {
                                                echo 'None';
                                            }
                                        ?></strong>
                                    </div>
                                    <?php if (isset($errors['supplier_id'])): ?>
                                        <div class="error-message show"><?php echo htmlspecialchars($errors['supplier_id']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <!-- Removed unit cost from Supplier Information -->
                            </div>
                        </div>

                        <!-- Stock Information -->
                        <div class="form-section">
                            <h3>Stock Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="quantity">Initial Quantity: <span style="color: red;">*</span></label>
                                    <input type="number" id="quantity" name="quantity" required placeholder="Enter quantity" min="0"
                                           value="<?php echo htmlspecialchars($formData['quantity'] ?? ''); ?>"
                                           class="<?php echo isset($errors['quantity']) ? 'error' : ''; ?>">
                                    <?php if (isset($errors['quantity'])): ?>
                                        <div class="error-message show"><?php echo htmlspecialchars($errors['quantity']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="unit_of_measure">Unit of Measure: <span style="color: red;">*</span></label>
                                    <select id="unit_of_measure" name="unit_of_measure" required>
                                        <option value="Units" <?php echo ($formData['unit_of_measure'] ?? 'Units') == 'Units' ? 'selected' : ''; ?>>Units</option>
                                        <option value="Boxes" <?php echo ($formData['unit_of_measure'] ?? '') == 'Boxes' ? 'selected' : ''; ?>>Boxes</option>
                                        <option value="Packs" <?php echo ($formData['unit_of_measure'] ?? '') == 'Packs' ? 'selected' : ''; ?>>Packs</option>
                                        <option value="Cartons" <?php echo ($formData['unit_of_measure'] ?? '') == 'Cartons' ? 'selected' : ''; ?>>Cartons</option>
                                        <option value="Bottles" <?php echo ($formData['unit_of_measure'] ?? '') == 'Bottles' ? 'selected' : ''; ?>>Bottles</option>
                                        <option value="Tubes" <?php echo ($formData['unit_of_measure'] ?? '') == 'Tubes' ? 'selected' : ''; ?>>Tubes</option>
                                        <option value="Kits" <?php echo ($formData['unit_of_measure'] ?? '') == 'Kits' ? 'selected' : ''; ?>>Kits</option>
                                        <option value="Milliliters" <?php echo ($formData['unit_of_measure'] ?? '') == 'Milliliters' ? 'selected' : ''; ?>>Milliliters (mL)</option>
                                        <option value="Grams" <?php echo ($formData['unit_of_measure'] ?? '') == 'Grams' ? 'selected' : ''; ?>>Grams (g)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="unit_cost">Unit Cost ($): <span style="color: red;">*</span></label>
                                    <input type="number" id="unit_cost" name="unit_cost" required placeholder="0.00" min="0" step="0.01"
                                           value="<?php echo htmlspecialchars($formData['unit_cost'] ?? ''); ?>"
                                           class="<?php echo isset($errors['unit_cost']) ? 'error' : ''; ?>"
                                           onkeypress="return /[0-9.]/.test(String.fromCharCode(event.which))">
                                    <?php if (isset($errors['unit_cost'])): ?>
                                        <div class="error-message show"><?php echo htmlspecialchars($errors['unit_cost']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label for="reorder_level">Reorder Level: <span style="color: red;">*</span></label>
                                    <input type="number" id="reorder_level" name="reorder_level" required placeholder="Enter reorder level" min="0"
                                           value="<?php echo htmlspecialchars($formData['reorder_level'] ?? ''); ?>"
                                           class="<?php echo isset($errors['reorder_level']) ? 'error' : ''; ?>">
                                    <?php if (isset($errors['reorder_level'])): ?>
                                        <div class="error-message show"><?php echo htmlspecialchars($errors['reorder_level']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="purchase_date">Purchase Date:</label>
                                    <input type="date" id="purchase_date" name="purchase_date" value="<?php echo htmlspecialchars($formData['purchase_date'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date:</label>
                                    <input type="date" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($formData['expiry_date'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <button type="submit" class="btn-primary-submit">Add Item to Inventory</button>
                            <a href="/lab_sync/index.php?controller=inventoryController&action=index" class="cancel-button">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Add Supplier Modal -->
<div id="addSupplierModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Add New Supplier</h2>
        <form id="addSupplierForm" method="POST" action="/lab_sync/index.php?controller=supplierController&action=store_from_inventory" onsubmit="return validateSupplierForm();">
            <div class="form-group">
                <label for="supplier_name">Name:</label>
                <input type="text" id="supplier_name" name="supplier_name" required placeholder="Supplier Name">
            </div>
            <div class="form-group">
                <label for="supplier_contact">Contact Number:</label>
                <input type="text" id="supplier_contact" name="supplier_contact" required placeholder="Contact Number">
            </div>
            <div class="form-group">
                <label for="supplier_email">Email:</label>
                <input type="email" id="supplier_email" name="supplier_email" required placeholder="Email">
            </div>
            <div style="margin-top:18px; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('addSupplierModal').classList.remove('active')" class="cancel-button">Cancel</button>
                <button type="submit" class="btn-primary-submit">Save Supplier</button>
            </div>
        </form>
        <button onclick="document.getElementById('addSupplierModal').classList.remove('active')" class="modal-close-btn">&times;</button>
    </div>
</div>
<script>
function validateSupplierForm() {
    // Basic validation for modal form
    var name = document.getElementById('supplier_name').value.trim();
    var contact = document.getElementById('supplier_contact').value.trim();
    var email = document.getElementById('supplier_email').value.trim();
    if (!name || !contact || !email) {
        alert('All fields are required.');
        return false;
    }
    // Optionally add more validation here
    return true;
}
</script>
</body>
</html>
