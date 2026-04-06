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
    <title>Add New Inventory Item</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <link rel="stylesheet" href="/lab_sync/public/inventoryStyles.css">
    <style>
        .form-section { 
            margin: 20px 0; 
            padding: 15px; 
            background-color: #f9f9f9; 
            border-left: 4px solid #3DBDEC; 
            border-radius: 4px; 
        }
        .form-section h3 { 
            margin-top: 0; 
            color: #333; 
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }
        .form-group input.error,
        .form-group select.error {
            border-color: #c33;
            background-color: #fee;
        }
        .error-message {
            color: #c33;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }
        .error-message.show {
            display: block;
        }
        .btn-primary-submit {
            background-color: #3DBDEC;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .btn-primary-submit:hover {
            background-color: #2da5d4;
        }
        .cancel-button {
            background-color: #e5e7eb;
            color: #333;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 10px;
            display: inline-block;
            font-weight: 500;
            transition: background-color 0.3s ease;
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
                    <h1>Add New Inventory Item</h1>
                </div>
                <div>
                    <p class="MC-p">Inventory → Add New Item</p>
                </div>
                <br/>

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

                <div>
                    <form class="formStyle" action="/lab_sync/index.php?controller=inventoryController&action=store" method="POST">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3>Basic Information</h3>
                            
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

                        <!-- Supplier Information -->
                        <div class="form-section">
                            <h3>Supplier Information</h3>
                            
                            <div class="form-group">
                                <label for="supplier_id">Supplier ID: <span style="color: red;">*</span></label>
                                <input type="number" id="supplier_id" name="supplier_id" required placeholder="Enter supplier ID" 
                                       value="<?php echo htmlspecialchars($formData['supplier_id'] ?? ''); ?>"
                                       class="<?php echo isset($errors['supplier_id']) ? 'error' : ''; ?>">
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
                        </div>

                        <!-- Stock Information -->
                        <div class="form-section">
                            <h3>Stock Information</h3>
                            
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

                        <div style="margin-top: 20px;">
                            <button type="submit" class="btn-primary-submit">Add Item to Inventory</button>
                            <a href="/lab_sync/index.php?controller=inventoryController&action=index" class="cancel-button">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>