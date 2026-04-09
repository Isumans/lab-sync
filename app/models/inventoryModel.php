<?php
class inventoryModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /* ========== SOFT DELETE HELPER ========== */
    private function getSoftDeleteFilter() {
        return "i.deleted_date IS NULL AND i.deleted_time IS NULL";
    }

    /* ========== ALL ITEMS ========== */
    public function getAllItems() {
        $query = "SELECT i.inventory_id, i.supplier_id, s.supplier_name, i.item_name, 
                         i.quantity, i.reorder_level, ic.category_name, i.status,
                         i.unit_cost, i.unit_of_measure, ic.category_id, i.expiry_date
                  FROM inventory i
                  LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
                  LEFT JOIN inventory_categories ic ON i.category_id = ic.category_id
                  WHERE " . $this->getSoftDeleteFilter() . "
                  ORDER BY i.inventory_id ASC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        return $items;
    }

    public function addItem($item_name, $quantity, $reorder_level, $supplier_id, $category_id = null, $unit_cost = 0, $unit_of_measure = 'Units', $expiry_date = null) {
        $query = "INSERT INTO inventory (item_name, quantity, reorder_level, supplier_id, category_id, unit_cost, unit_of_measure, expiry_date, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'In Stock')";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'siiidsss', $item_name, $quantity, $reorder_level, $supplier_id, $category_id, $unit_cost, $unit_of_measure, $expiry_date);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            $inventory_id = mysqli_insert_id($this->db);
            $this->addStockHistory($inventory_id, $quantity, 'Added', 'New item created');
        }
        return $result;
    }

    public function deleteItem($itemId) {
        // Soft delete - no longer performs hard delete
        // This method is kept for backward compatibility but uses soft delete
        return $this->softDeleteItem($itemId, null);
    }

    public function softDeleteItem($itemId, $deletedByUserId) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        $query = "UPDATE inventory 
                  SET deleted_date = ?, 
                      deleted_time = ?, 
                      deleted_by = ?
                  WHERE inventory_id = ? AND deleted_date IS NULL";
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        // If no user ID provided, use 0 (system delete)
        if ($deletedByUserId === null) {
            $deletedByUserId = 0;
        }
        
        mysqli_stmt_bind_param($stmt, 'ssii', $currentDate, $currentTime, $deletedByUserId, $itemId);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Execute failed: " . mysqli_error($this->db));
            return false;
        }
        
        // Check if the update was successful (at least one row was affected)
        return mysqli_stmt_affected_rows($stmt) > 0;
    }

    public function updateItem($itemId, $itemName, $quantity, $reorderLevel, $supplierId, $category_id = null, $unit_cost = 0, $unit_of_measure = 'Units', $expiry_date = null) {
        $getQuery = "SELECT quantity FROM inventory WHERE inventory_id = ? AND " . $this->getSoftDeleteFilter();
        $getStmt = mysqli_prepare($this->db, $getQuery);
        mysqli_stmt_bind_param($getStmt, 'i', $itemId);
        mysqli_stmt_execute($getStmt);
        $result = mysqli_stmt_get_result($getStmt);
        $oldItem = mysqli_fetch_assoc($result);
        $oldQuantity = $oldItem['quantity'] ?? 0;

        $query = "UPDATE inventory SET item_name = ?, quantity = ?, reorder_level = ?, supplier_id = ?, category_id = ?, unit_cost = ?, unit_of_measure = ?, expiry_date = ? WHERE inventory_id = ? AND " . $this->getSoftDeleteFilter();
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'siiiidssi', $itemName, $quantity, $reorderLevel, $supplierId, $category_id, $unit_cost, $unit_of_measure, $expiry_date, $itemId);
        $updateResult = mysqli_stmt_execute($stmt);

        if ($updateResult && $quantity != $oldQuantity) {
            $difference = $quantity - $oldQuantity;
            $action = $difference > 0 ? 'Added' : 'Removed';
            $this->addStockHistory($itemId, abs($difference), $action, 'Quantity updated');
            $this->updateItemStatus($itemId);
        }
        return $updateResult;
    }

    public function updateItemStatus($itemId) {
        $query = "SELECT quantity, reorder_level FROM inventory WHERE inventory_id = ? AND " . $this->getSoftDeleteFilter();
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $itemId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        
        if ($item) {
            $status = 'In Stock';
            if ($item['quantity'] == 0) {
                $status = 'Out of Stock';
            } elseif ($item['quantity'] <= $item['reorder_level']) {
                $status = 'Low Stock';
            }
            
            $updateQuery = "UPDATE inventory SET status = ? WHERE inventory_id = ? AND " . $this->getSoftDeleteFilter();
            $updateStmt = mysqli_prepare($this->db, $updateQuery);
            mysqli_stmt_bind_param($updateStmt, 'si', $status, $itemId);
            mysqli_stmt_execute($updateStmt);
        }
    }

    /* Stock History Methods */
    public function getStockHistory() {
        $query = "SELECT sh.*, i.item_name FROM stock_history sh 
                  JOIN inventory i ON sh.inventory_id = i.inventory_id
                  WHERE i.deleted_date IS NULL AND i.deleted_time IS NULL
                  AND sh.deleted_date IS NULL AND sh.deleted_time IS NULL
                  ORDER BY sh.created_at DESC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $history = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        return $history;
    }

    public function addStockHistory($inventory_id, $quantity, $action, $notes = '') {
        $query = "INSERT INTO stock_history (inventory_id, quantity, action, notes, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'iiss', $inventory_id, $quantity, $action, $notes);
        return mysqli_stmt_execute($stmt);
    }

    public function getStockHistoryDetails() {
        $query = "SELECT sh.history_id, sh.inventory_id, sh.purchase_id, sh.quantity, sh.action, 
                         sh.unit_cost, sh.supplier_id, sh.expiry_date, sh.notes, sh.created_at,
                         i.item_name, s.supplier_name
                  FROM stock_history sh
                  LEFT JOIN inventory i ON sh.inventory_id = i.inventory_id
                  LEFT JOIN suppliers s ON sh.supplier_id = s.supplier_id
                  WHERE i.deleted_date IS NULL AND i.deleted_time IS NULL
                  AND sh.deleted_date IS NULL AND sh.deleted_time IS NULL
                  ORDER BY sh.created_at DESC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $history = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        return $history;
    }

    /* ========== STOCK PURCHASES ========== */
    public function getAllPurchases() {
        $query = "SELECT sp.purchase_id, sp.inventory_id, i.item_name, sp.supplier_id, s.supplier_name,
                         sp.quantity_purchased, sp.unit_cost, sp.total_cost, sp.purchase_date, sp.notes, sp.expiry_date
                  FROM stock_purchases sp
                  JOIN inventory i ON sp.inventory_id = i.inventory_id
                  JOIN suppliers s ON sp.supplier_id = s.supplier_id
                  WHERE sp.deleted_date IS NULL AND sp.deleted_time IS NULL
                  AND i.deleted_date IS NULL AND i.deleted_time IS NULL
                  ORDER BY sp.purchase_date DESC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $purchases = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $purchases[] = $row;
        }
        return $purchases;
    }

    public function softDeletePurchase($purchaseId, $deletedByUserId) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        $query = "UPDATE stock_purchases 
                  SET deleted_date = ?, 
                      deleted_time = ?, 
                      deleted_by = ?
                  WHERE purchase_id = ? AND deleted_date IS NULL";
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        if ($deletedByUserId === null) {
            $deletedByUserId = 0;
        }
        
        mysqli_stmt_bind_param($stmt, 'ssii', $currentDate, $currentTime, $deletedByUserId, $purchaseId);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Execute failed: " . mysqli_error($this->db));
            return false;
        }
        
        return mysqli_stmt_affected_rows($stmt) > 0;
    }

    public function addPurchase($inventory_id, $supplier_id, $quantity_purchased, $unit_cost, $total_cost, $purchase_date, $notes = '') {
        $query = "INSERT INTO stock_purchases (inventory_id, supplier_id, quantity_purchased, unit_cost, total_cost, purchase_date, notes) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'iiiidss', $inventory_id, $supplier_id, $quantity_purchased, $unit_cost, $total_cost, $purchase_date, $notes);
        return mysqli_stmt_execute($stmt);
    }

    /* Category Methods */
    public function getAllCategories() {
        $query = "SELECT * FROM inventory_categories WHERE deleted_date IS NULL AND deleted_time IS NULL ORDER BY category_name ASC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        return $categories;
    }

    public function getCategoryWithItems($category_id) {
        $categoryQuery = "SELECT * FROM inventory_categories WHERE category_id = ?";
        $categoryStmt = mysqli_prepare($this->db, $categoryQuery);
        mysqli_stmt_bind_param($categoryStmt, 'i', $category_id);
        mysqli_stmt_execute($categoryStmt);
        $categoryResult = mysqli_stmt_get_result($categoryStmt);
        $category = mysqli_fetch_assoc($categoryResult);

        if ($category) {
            $itemsQuery = "SELECT inventory_id, item_name FROM inventory WHERE category_id = ? AND deleted_date IS NULL AND deleted_time IS NULL ORDER BY item_name ASC";
            $itemsStmt = mysqli_prepare($this->db, $itemsQuery);
            mysqli_stmt_bind_param($itemsStmt, 'i', $category_id);
            mysqli_stmt_execute($itemsStmt);
            $itemsResult = mysqli_stmt_get_result($itemsStmt);
            $items = [];
            while ($item = mysqli_fetch_assoc($itemsResult)) {
                $items[] = $item;
            }
            $category['items'] = $items;
        }

        return $category;
    }

    public function addCategory($category_name, $description = '') {
        $query = "INSERT INTO inventory_categories (category_name, description) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $category_name, $description);
        return mysqli_stmt_execute($stmt);
    }

    public function updateCategory($category_id, $category_name, $description = '') {
        $query = "UPDATE inventory_categories SET category_name = ?, description = ? WHERE category_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $category_name, $description, $category_id);
        return mysqli_stmt_execute($stmt);
    }

    public function deleteCategory($category_id) {
        // Soft delete - no longer performs hard delete
        return $this->softDeleteCategory($category_id, null);
    }

    public function softDeleteCategory($category_id, $deletedByUserId) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        $query = "UPDATE inventory_categories 
                  SET deleted_date = ?, 
                      deleted_time = ?, 
                      deleted_by = ?
                  WHERE category_id = ? AND deleted_date IS NULL";
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->db));
            return false;
        }
        
        if ($deletedByUserId === null) {
            $deletedByUserId = 0;
        }
        
        mysqli_stmt_bind_param($stmt, 'ssii', $currentDate, $currentTime, $deletedByUserId, $category_id);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Execute failed: " . mysqli_error($this->db));
            return false;
        }
        
        return mysqli_stmt_affected_rows($stmt) > 0;
    }

    public function getCategoryCount() {
        $query = "SELECT COUNT(*) as count FROM inventory_categories WHERE deleted_date IS NULL AND deleted_time IS NULL";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }

    /* ========== SUPPLIERS ========== */
    public function getAllSuppliers() {
        $query = "SELECT * FROM suppliers ORDER BY supplier_name ASC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $suppliers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $suppliers[] = $row;
        }
        return $suppliers;
    }

    public function getSupplierById($supplier_id) {
        $query = "SELECT * FROM suppliers WHERE supplier_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $supplier_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    /* ========== CATEGORY ICONS & MANAGEMENT ========== */
    public static function getCategoryIcon($category_name) {
        $icons = [
            'Blood Tests' => '🩸',
            'Reagents' => '⚗️',
            'Consumables' => '📦',
            'Equipment' => '⚙️',
            'Safety Equipment' => '🛡️',
            'Sterilization Supplies' => '🧼',
            'Medical Supplies' => '💊',
            'Chemicals' => '☣️',
            'Laboratory Equipment' => '🔬'
        ];
        
        // Use the first matching key or a default icon
        foreach ($icons as $key => $icon) {
            if (stripos($category_name, $key) !== false || stripos($key, $category_name) !== false) {
                return $icon;
            }
        }
        
        // Default icon if no match found
        return '📁';
    }

    public function addItemToCategory($inventory_id, $category_id) {
        $query = "UPDATE inventory SET category_id = ? WHERE inventory_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $category_id, $inventory_id);
        return mysqli_stmt_execute($stmt);
    }

    public function getItemById($inventory_id) {
        $query = "SELECT inventory_id, item_name, supplier_id, quantity, reorder_level, category_id, status, unit_cost, unit_of_measure  
                  FROM inventory WHERE inventory_id = ? AND deleted_date IS NULL AND deleted_time IS NULL";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $inventory_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function getAllItems_ForSelection() {
        $query = "SELECT inventory_id, item_name FROM inventory WHERE deleted_date IS NULL AND deleted_time IS NULL ORDER BY item_name ASC";
        $result = mysqli_query($this->db, $query);
        if (!$result) {
            die("Query failed: " . mysqli_error($this->db));
        }
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        return $items;
    }

    /* ========== DASHBOARD STATS ========== */
    public function getDashboardStats() {
        $stats = [];
        $softDeleteFilter = "deleted_date IS NULL AND deleted_time IS NULL";
        
        $query = "SELECT COUNT(*) as count FROM inventory WHERE " . $softDeleteFilter;
        $result = mysqli_query($this->db, $query);
        $stats['total_items'] = mysqli_fetch_assoc($result)['count'];
        
        $query = "SELECT COUNT(*) as count FROM inventory WHERE quantity <= reorder_level AND quantity > 0 AND " . $softDeleteFilter;
        $result = mysqli_query($this->db, $query);
        $stats['low_stock'] = mysqli_fetch_assoc($result)['count'];
        
        $query = "SELECT COUNT(*) as count FROM inventory WHERE quantity = 0 AND " . $softDeleteFilter;
        $result = mysqli_query($this->db, $query);
        $stats['out_of_stock'] = mysqli_fetch_assoc($result)['count'];
        
        $query = "SELECT COUNT(*) as count FROM inventory_categories";
        $result = mysqli_query($this->db, $query);
        $stats['total_categories'] = mysqli_fetch_assoc($result)['count'];
        
        return $stats;
    }
    }