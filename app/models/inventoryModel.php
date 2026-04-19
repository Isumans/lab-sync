<?php
class inventoryModel {
    private $db;
    private $lastError = '';

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }

    public function getAllItems() {
        $query = "SELECT * FROM inventory WHERE deleted_date IS NULL OR deleted_date = '0000-00-00'";
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

    public function getInventoryList($filters, $page = 1, $perPage = 7, $sortBy = 'last_updated', $sortDir = 'desc') {
        $this->lastError = '';
        $page = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $offset = ($page - 1) * $perPage;

        list($sql, $types, $params) = $this->buildInventoryBaseSql($filters, false);

        $sortMap = [
            'inventory_id' => 'i.inventory_id',
            'item_name' => 'i.item_name',
            'supplier_id' => 'i.supplier_id',
            'quantity' => 'i.quantity',
            'reorder_level' => 'i.reorder_level',
            'status' => 'i.status',
            'last_updated' => 'i.last_updated',
        ];

        $sortKey = strtolower(trim((string) $sortBy));
        if (!isset($sortMap[$sortKey])) {
            $sortKey = 'last_updated';
        }

        $direction = strtolower(trim((string) $sortDir)) === 'asc' ? 'ASC' : 'DESC';
        $sql .= ' ORDER BY ' . $sortMap[$sortKey] . ' ' . $direction . ', i.inventory_id DESC LIMIT ? OFFSET ?';
        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if ($stmt === null) {
            return [];
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getInventoryList: ' . $stmt->error;
            error_log($this->lastError);
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countInventory($filters) {
        $this->lastError = '';
        list($sql, $types, $params) = $this->buildInventoryBaseSql($filters, true);

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if ($stmt === null) {
            return 0;
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in countInventory: ' . $stmt->error;
            error_log($this->lastError);
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ? intval($row['total_rows'] ?? 0) : 0;
    }

    public function getSupplierList($filters, $page = 1, $perPage = 7, $sortBy = 'created_at', $sortDir = 'desc') {
        $this->lastError = '';

        if (!$this->tableExists('suppliers')) {
            return [];
        }

        $page = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $offset = ($page - 1) * $perPage;

        list($sql, $types, $params) = $this->buildSupplierBaseSql($filters, false);

        $sortMap = [
            'supplier_id' => 's.supplier_id',
            'supplier_name' => 's.supplier_name',
            'contact_no' => 's.contact_no',
            'location' => 's.location',
            'email' => 's.email',
            'created_at' => 's.created_at',
            'updated_at' => 's.updated_at',
        ];

        $sortKey = strtolower(trim((string) $sortBy));
        if (!isset($sortMap[$sortKey])) {
            $sortKey = 'created_at';
        }

        $direction = strtolower(trim((string) $sortDir)) === 'asc' ? 'ASC' : 'DESC';
        $sql .= ' ORDER BY ' . $sortMap[$sortKey] . ' ' . $direction . ', s.supplier_id DESC LIMIT ? OFFSET ?';
        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if ($stmt === null) {
            return [];
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getSupplierList: ' . $stmt->error;
            error_log($this->lastError);
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countSuppliers($filters) {
        $this->lastError = '';

        if (!$this->tableExists('suppliers')) {
            return 0;
        }

        list($sql, $types, $params) = $this->buildSupplierBaseSql($filters, true);

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if ($stmt === null) {
            return 0;
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in countSuppliers: ' . $stmt->error;
            error_log($this->lastError);
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ? intval($row['total_rows'] ?? 0) : 0;
    }

    public function getLastError() {
        return $this->lastError;
    }
    public function addItem($item_name, $quantity, $reorder_level, $supplier_id = null, $category_id = null, $unit_of_measure = 'Units', $unit_cost = null, $expiry_date = null) {
        $this->lastError = '';

        $query = "INSERT INTO inventory (item_name, quantity, reorder_level, supplier_id, category_id, unit_of_measure, unit_cost, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in addItem: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        $safeSupplierId = ($supplier_id === null || $supplier_id === '') ? null : intval($supplier_id);
        $safeCategoryId = ($category_id === null || $category_id === '') ? null : intval($category_id);
        $safeUnit = trim((string) $unit_of_measure) !== '' ? trim((string) $unit_of_measure) : 'Units';
        $safeUnitCost = ($unit_cost === null || $unit_cost === '') ? null : floatval($unit_cost);
        $safeExpiryDate = ($expiry_date === null || trim((string) $expiry_date) === '') ? null : trim((string) $expiry_date);

        mysqli_stmt_bind_param(
            $stmt,
            'siiiisss',
            $item_name,
            $quantity,
            $reorder_level,
            $safeSupplierId,
            $safeCategoryId,
            $safeUnit,
            $safeUnitCost,
            $safeExpiryDate
        );

        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            $this->lastError = 'Execute failed in addItem: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
        }

        return $ok;
    }

    public function getLastInsertId() {
        return intval(mysqli_insert_id($this->db));
    }

    public function getInventoryCategories() {
        $this->lastError = '';

        if (!$this->tableExists('inventory_categories')) {
            return [];
        }

        $query = "SELECT category_id, category_name FROM inventory_categories WHERE deleted_date IS NULL OR deleted_date = '0000-00-00' ORDER BY category_name ASC";
        $result = mysqli_query($this->db, $query);

        if (!$result) {
            $this->lastError = 'Query failed in getInventoryCategories: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return [];
        }

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function searchInventoryItems($search = '', $limit = 10) {
        $this->lastError = '';
        $safeLimit = max(1, min(50, intval($limit)));
        $keyword = trim((string) $search);

        if ($keyword === '') {
            $query = "SELECT inventory_id, item_name FROM inventory WHERE deleted_date IS NULL OR deleted_date = '0000-00-00' ORDER BY item_name ASC LIMIT ?";
            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                $this->lastError = 'Prepare failed in searchInventoryItems: ' . mysqli_error($this->db);
                error_log($this->lastError);
                return [];
            }
            mysqli_stmt_bind_param($stmt, 'i', $safeLimit);
        } else {
            $query = "SELECT inventory_id, item_name FROM inventory WHERE (item_name LIKE CONCAT('%', ?, '%')) AND (deleted_date IS NULL OR deleted_date = '0000-00-00') ORDER BY item_name ASC LIMIT ?";
            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                $this->lastError = 'Prepare failed in searchInventoryItems: ' . mysqli_error($this->db);
                error_log($this->lastError);
                return [];
            }
            mysqli_stmt_bind_param($stmt, 'si', $keyword, $safeLimit);
        }

        if (!mysqli_stmt_execute($stmt)) {
            $this->lastError = 'Execute failed in searchInventoryItems: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return [];
        }

        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function searchSuppliers($search = '', $limit = 20) {
        $this->lastError = '';
        if (!$this->tableExists('suppliers')) {
            return [];
        }

        $safeLimit = max(1, min(100, intval($limit)));
        $keyword = trim((string) $search);

        if ($keyword === '') {
            $query = "SELECT supplier_id, supplier_name, contact_no, location, email FROM suppliers ORDER BY supplier_name ASC LIMIT ?";
            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                $this->lastError = 'Prepare failed in searchSuppliers: ' . mysqli_error($this->db);
                error_log($this->lastError);
                return [];
            }
            mysqli_stmt_bind_param($stmt, 'i', $safeLimit);
        } else {
            $query = "SELECT supplier_id, supplier_name, contact_no, location, email FROM suppliers WHERE supplier_name LIKE CONCAT('%', ?, '%') OR contact_no LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%') ORDER BY supplier_name ASC LIMIT ?";
            $stmt = mysqli_prepare($this->db, $query);
            if (!$stmt) {
                $this->lastError = 'Prepare failed in searchSuppliers: ' . mysqli_error($this->db);
                error_log($this->lastError);
                return [];
            }
            mysqli_stmt_bind_param($stmt, 'sssi', $keyword, $keyword, $keyword, $safeLimit);
        }

        if (!mysqli_stmt_execute($stmt)) {
            $this->lastError = 'Execute failed in searchSuppliers: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return [];
        }

        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function createSupplier($supplierName, $contactNo = null, $location = null, $email = null) {
        $this->lastError = '';
        if (!$this->tableExists('suppliers')) {
            $this->lastError = 'Suppliers table was not found.';
            return false;
        }

        $query = "INSERT INTO suppliers (supplier_name, contact_no, location, email) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in createSupplier: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        $safeName = trim((string) $supplierName);
        $safeContact = trim((string) $contactNo);
        $safeLocation = trim((string) $location);
        $safeEmail = trim((string) $email);

        $safeContact = $safeContact === '' ? null : $safeContact;
        $safeLocation = $safeLocation === '' ? null : $safeLocation;
        $safeEmail = $safeEmail === '' ? null : $safeEmail;

        mysqli_stmt_bind_param($stmt, 'ssss', $safeName, $safeContact, $safeLocation, $safeEmail);
        $ok = mysqli_stmt_execute($stmt);

        if (!$ok) {
            $this->lastError = 'Execute failed in createSupplier: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return false;
        }

        return intval(mysqli_insert_id($this->db));
    }

    public function getSupplierById($supplierId) {
        $this->lastError = '';

        if (!$this->tableExists('suppliers')) {
            $this->lastError = 'Suppliers table was not found.';
            return null;
        }

        $safeSupplierId = intval($supplierId);
        if ($safeSupplierId <= 0) {
            return null;
        }

        $query = 'SELECT supplier_id, supplier_name, contact_no, location, email FROM suppliers WHERE supplier_id = ? LIMIT 1';
        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in getSupplierById: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $safeSupplierId);
        if (!mysqli_stmt_execute($stmt)) {
            $this->lastError = 'Execute failed in getSupplierById: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return null;
        }

        $result = mysqli_stmt_get_result($stmt);
        return $result ? mysqli_fetch_assoc($result) : null;
    }

    public function updateSupplier($supplierId, $supplierName, $contactNo = null, $location = null, $email = null) {
        $this->lastError = '';

        if (!$this->tableExists('suppliers')) {
            $this->lastError = 'Suppliers table was not found.';
            return false;
        }

        $safeSupplierId = intval($supplierId);
        $safeName = trim((string) $supplierName);
        $safeContact = trim((string) $contactNo);
        $safeLocation = trim((string) $location);
        $safeEmail = trim((string) $email);

        $safeContact = $safeContact === '' ? null : $safeContact;
        $safeLocation = $safeLocation === '' ? null : $safeLocation;
        $safeEmail = $safeEmail === '' ? null : $safeEmail;

        $query = 'UPDATE suppliers SET supplier_name = ?, contact_no = ?, location = ?, email = ? WHERE supplier_id = ?';
        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in updateSupplier: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ssssi', $safeName, $safeContact, $safeLocation, $safeEmail, $safeSupplierId);
        if (!mysqli_stmt_execute($stmt)) {
            $this->lastError = 'Execute failed in updateSupplier: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return false;
        }

        return mysqli_stmt_affected_rows($stmt) >= 0;
    }

    public function deleteSupplier($supplierId) {
        $this->lastError = '';

        if (!$this->tableExists('suppliers')) {
            $this->lastError = 'Suppliers table was not found.';
            return false;
        }

        $safeSupplierId = intval($supplierId);
        if ($safeSupplierId <= 0) {
            $this->lastError = 'Valid supplier ID is required.';
            return false;
        }

        $query = 'DELETE FROM suppliers WHERE supplier_id = ?';
        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in deleteSupplier: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $safeSupplierId);
        if (!mysqli_stmt_execute($stmt)) {
            $this->lastError = 'Execute failed in deleteSupplier: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return false;
        }

        if (mysqli_stmt_affected_rows($stmt) === 0) {
            $this->lastError = 'Supplier not found.';
            return false;
        }

        return true;
    }

    public function addStockHistoryEntry($inventoryId, $quantity, $unitCost = null, $supplierId = null, $expiryDate = null, $notes = '') {
        $this->lastError = '';
        if (!$this->tableExists('stock_history')) {
            return true;
        }

        $inventoryId = intval($inventoryId);
        $quantity = intval($quantity);
        $action = 'Initial Intake';

        $unitCostSql = ($unitCost === null || $unitCost === '') ? 'NULL' : (string) floatval($unitCost);
        $supplierIdSql = ($supplierId === null || $supplierId === '') ? 'NULL' : (string) intval($supplierId);

        $expirySql = 'NULL';
        if ($expiryDate !== null && trim((string) $expiryDate) !== '') {
            $expirySql = "'" . mysqli_real_escape_string($this->db, trim((string) $expiryDate)) . "'";
        }

        $safeNotes = mysqli_real_escape_string($this->db, trim((string) $notes));
        $safeAction = mysqli_real_escape_string($this->db, $action);

        $query = "INSERT INTO stock_history (inventory_id, quantity, action, unit_cost, supplier_id, expiry_date, notes) VALUES (" .
            $inventoryId . ", " .
            $quantity . ", '" . $safeAction . "', " .
            $unitCostSql . ", " .
            $supplierIdSql . ", " .
            $expirySql . ", '" . $safeNotes . "')";

        $ok = mysqli_query($this->db, $query);
        if (!$ok) {
            $this->lastError = 'Query failed in addStockHistoryEntry: ' . mysqli_error($this->db);
            error_log($this->lastError);
        }

        return $ok;
    }

    public function addInventorySupplierSources($inventoryId, $sources) {
        $this->lastError = '';

        if (!$this->tableExists('inventory_supplier_sources')) {
            return true;
        }

        $safeInventoryId = intval($inventoryId);
        if ($safeInventoryId <= 0) {
            $this->lastError = 'Invalid inventory ID for supplier source save.';
            return false;
        }

        $normalized = [];
        foreach ((array) $sources as $source) {
            $supplierId = isset($source['supplier_id']) ? intval($source['supplier_id']) : 0;
            if ($supplierId <= 0) {
                continue;
            }

            $unitCost = null;
            if (isset($source['unit_cost']) && $source['unit_cost'] !== '' && $source['unit_cost'] !== null) {
                $unitCost = floatval($source['unit_cost']);
            }

            $isPrimary = !empty($source['is_primary']) ? 1 : 0;

            $normalized[] = [
                'supplier_id' => $supplierId,
                'unit_cost' => $unitCost,
                'is_primary' => $isPrimary,
            ];
        }

        if (empty($normalized)) {
            return true;
        }

        $hasPrimary = false;
        foreach ($normalized as $entry) {
            if ($entry['is_primary'] === 1) {
                $hasPrimary = true;
                break;
            }
        }
        if (!$hasPrimary) {
            $normalized[0]['is_primary'] = 1;
        }

        $query = 'INSERT INTO inventory_supplier_sources '
            . '(inventory_id, supplier_id, unit_cost, is_primary, is_active, first_seen_date, last_purchase_date) '
            . 'VALUES (?, ?, ?, ?, 1, CURDATE(), CURDATE()) '
            . 'ON DUPLICATE KEY UPDATE '
            . 'unit_cost = COALESCE(VALUES(unit_cost), unit_cost), '
            . 'is_primary = VALUES(is_primary), '
            . 'is_active = 1, '
            . 'updated_at = CURRENT_TIMESTAMP';

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in addInventorySupplierSources: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        foreach ($normalized as $entry) {
            $supplierId = $entry['supplier_id'];
            $unitCost = $entry['unit_cost'];
            $isPrimary = $entry['is_primary'];

            mysqli_stmt_bind_param($stmt, 'iidi', $safeInventoryId, $supplierId, $unitCost, $isPrimary);
            if (!mysqli_stmt_execute($stmt)) {
                $this->lastError = 'Execute failed in addInventorySupplierSources: ' . mysqli_stmt_error($stmt);
                error_log($this->lastError);
                return false;
            }
        }

        return true;
    }

    public function getInventoryDetailsPayload($inventoryId) {
        $this->lastError = '';

        $safeInventoryId = intval($inventoryId);
        if ($safeInventoryId <= 0) {
            return null;
        }

        $itemSql = 'SELECT i.inventory_id, i.item_name, i.quantity, i.reorder_level, i.status, '
            . 'i.supplier_id, i.category_id, i.unit_of_measure, i.unit_cost, i.expiry_date, i.last_updated, '
            . 'c.category_name, s.supplier_name AS primary_supplier_name '
            . 'FROM inventory i '
            . 'LEFT JOIN inventory_categories c ON c.category_id = i.category_id '
            . 'LEFT JOIN suppliers s ON s.supplier_id = i.supplier_id '
            . 'WHERE i.inventory_id = ? LIMIT 1';

        $itemStmt = mysqli_prepare($this->db, $itemSql);
        if (!$itemStmt) {
            $this->lastError = 'Prepare failed in getInventoryDetailsPayload(item): ' . mysqli_error($this->db);
            error_log($this->lastError);
            return null;
        }

        mysqli_stmt_bind_param($itemStmt, 'i', $safeInventoryId);
        if (!mysqli_stmt_execute($itemStmt)) {
            $this->lastError = 'Execute failed in getInventoryDetailsPayload(item): ' . mysqli_stmt_error($itemStmt);
            error_log($this->lastError);
            return null;
        }

        $itemResult = mysqli_stmt_get_result($itemStmt);
        $inventory = $itemResult ? mysqli_fetch_assoc($itemResult) : null;
        if (!$inventory) {
            return null;
        }

        $suppliers = [];
        if ($this->tableExists('inventory_supplier_sources')) {
            $supplierSql = 'SELECT iss.source_id, iss.supplier_id, iss.unit_cost, iss.min_order_qty, '
                . 'iss.lead_time_days, iss.is_primary, sup.supplier_name '
                . 'FROM inventory_supplier_sources iss '
                . 'LEFT JOIN suppliers sup ON sup.supplier_id = iss.supplier_id '
                . 'WHERE iss.inventory_id = ? AND iss.is_active = 1 '
                . 'ORDER BY iss.is_primary DESC, sup.supplier_name ASC, iss.source_id ASC';

            $supplierStmt = mysqli_prepare($this->db, $supplierSql);
            if (!$supplierStmt) {
                $this->lastError = 'Prepare failed in getInventoryDetailsPayload(suppliers): ' . mysqli_error($this->db);
                error_log($this->lastError);
                return null;
            }

            mysqli_stmt_bind_param($supplierStmt, 'i', $safeInventoryId);
            if (!mysqli_stmt_execute($supplierStmt)) {
                $this->lastError = 'Execute failed in getInventoryDetailsPayload(suppliers): ' . mysqli_stmt_error($supplierStmt);
                error_log($this->lastError);
                return null;
            }

            $supplierResult = mysqli_stmt_get_result($supplierStmt);
            $suppliers = $supplierResult ? mysqli_fetch_all($supplierResult, MYSQLI_ASSOC) : [];
        }

        if (empty($suppliers) && !empty($inventory['supplier_id'])) {
            $suppliers[] = [
                'source_id' => null,
                'supplier_id' => intval($inventory['supplier_id']),
                'unit_cost' => $inventory['unit_cost'],
                'min_order_qty' => null,
                'lead_time_days' => null,
                'is_primary' => 1,
                'supplier_name' => $inventory['primary_supplier_name'] ?? null,
            ];
        }

        return [
            'inventory' => $inventory,
            'suppliers' => $suppliers,
        ];
    }

    public function deleteItem($itemId, $deletedBy = null) {
        $this->lastError = '';

        $safeItemId = intval($itemId);
        if ($safeItemId <= 0) {
            $this->lastError = 'Valid inventory ID is required for delete.';
            return false;
        }

        $safeDeletedBy = $deletedBy === null ? null : intval($deletedBy);
        $query = "UPDATE inventory SET deleted_date = CURDATE(), deleted_time = CURTIME(), deleted_by = ? WHERE inventory_id = ? AND (deleted_date IS NULL OR deleted_date = '0000-00-00')";
        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in deleteItem: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'ii', $safeDeletedBy, $safeItemId);
        if (!mysqli_stmt_execute($stmt)) {
            $this->lastError = 'Execute failed in deleteItem: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return false;
        }

        if (mysqli_stmt_affected_rows($stmt) < 1) {
            $this->lastError = 'Inventory item not found or already deleted.';
            return false;
        }

        return true;
    }
    public function updateItem($itemId, $itemName, $quantity, $reorderLevel, $supplierId) {
        $query = "UPDATE inventory SET item_name = ?, quantity = ?, reorder_level = ?, supplier_id = ? WHERE inventory_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'siiii', $itemName, $quantity, $reorderLevel, $supplierId, $itemId);
        return mysqli_stmt_execute($stmt);
    }

    public function updateItemFull($itemId, $itemName, $quantity, $reorderLevel, $status, $supplierId, $categoryId, $unitOfMeasure, $unitCost, $expiryDate) {
        $this->lastError = '';

        $safeItemId = intval($itemId);
        if ($safeItemId <= 0) {
            $this->lastError = 'Invalid inventory item ID.';
            return false;
        }

        $safeItemName = trim((string) $itemName);
        $safeQuantity = intval($quantity);
        $safeReorderLevel = intval($reorderLevel);
        $safeStatus = trim((string) $status);
        $safeSupplierId = ($supplierId === null || $supplierId === '') ? null : intval($supplierId);
        $safeCategoryId = ($categoryId === null || $categoryId === '') ? null : intval($categoryId);
        $safeUnitOfMeasure = trim((string) $unitOfMeasure);
        $safeUnitCost = ($unitCost === null || $unitCost === '') ? null : floatval($unitCost);
        $safeExpiryDate = ($expiryDate === null || trim((string) $expiryDate) === '') ? null : trim((string) $expiryDate);

        if ($safeStatus === '') {
            $safeStatus = 'In Stock';
        }
        if ($safeUnitOfMeasure === '') {
            $safeUnitOfMeasure = 'Units';
        }

        $query = 'UPDATE inventory '
            . 'SET item_name = ?, quantity = ?, reorder_level = ?, status = ?, supplier_id = ?, category_id = ?, '
            . 'unit_of_measure = ?, unit_cost = ?, expiry_date = ? '
            . 'WHERE inventory_id = ?';

        $stmt = mysqli_prepare($this->db, $query);
        if (!$stmt) {
            $this->lastError = 'Prepare failed in updateItemFull: ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        mysqli_stmt_bind_param(
            $stmt,
            'siisiisdsi',
            $safeItemName,
            $safeQuantity,
            $safeReorderLevel,
            $safeStatus,
            $safeSupplierId,
            $safeCategoryId,
            $safeUnitOfMeasure,
            $safeUnitCost,
            $safeExpiryDate,
            $safeItemId
        );

        $ok = mysqli_stmt_execute($stmt);
        if (!$ok) {
            $this->lastError = 'Execute failed in updateItemFull: ' . mysqli_stmt_error($stmt);
            error_log($this->lastError);
            return false;
        }

        return true;
    }

    public function replaceInventorySupplierSources($inventoryId, $sources) {
        $this->lastError = '';

        if (!$this->tableExists('inventory_supplier_sources')) {
            return true;
        }

        $safeInventoryId = intval($inventoryId);
        if ($safeInventoryId <= 0) {
            $this->lastError = 'Invalid inventory ID for supplier source replacement.';
            return false;
        }

        $normalized = [];
        foreach ((array) $sources as $source) {
            $supplierId = isset($source['supplier_id']) ? intval($source['supplier_id']) : 0;
            if ($supplierId <= 0) {
                continue;
            }

            $unitCost = null;
            if (isset($source['unit_cost']) && $source['unit_cost'] !== null && $source['unit_cost'] !== '') {
                $unitCost = floatval($source['unit_cost']);
            }

            $minOrderQty = null;
            if (isset($source['min_order_qty']) && $source['min_order_qty'] !== null && $source['min_order_qty'] !== '') {
                $minOrderQty = intval($source['min_order_qty']);
            }

            $leadTimeDays = null;
            if (isset($source['lead_time_days']) && $source['lead_time_days'] !== null && $source['lead_time_days'] !== '') {
                $leadTimeDays = intval($source['lead_time_days']);
            }

            $isPrimary = !empty($source['is_primary']) ? 1 : 0;

            $normalized[] = [
                'supplier_id' => $supplierId,
                'unit_cost' => $unitCost,
                'min_order_qty' => $minOrderQty,
                'lead_time_days' => $leadTimeDays,
                'is_primary' => $isPrimary,
            ];
        }

        $deleteQuery = 'DELETE FROM inventory_supplier_sources WHERE inventory_id = ?';
        $deleteStmt = mysqli_prepare($this->db, $deleteQuery);
        if (!$deleteStmt) {
            $this->lastError = 'Prepare failed in replaceInventorySupplierSources(delete): ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        mysqli_stmt_bind_param($deleteStmt, 'i', $safeInventoryId);
        if (!mysqli_stmt_execute($deleteStmt)) {
            $this->lastError = 'Execute failed in replaceInventorySupplierSources(delete): ' . mysqli_stmt_error($deleteStmt);
            error_log($this->lastError);
            return false;
        }

        if (empty($normalized)) {
            return true;
        }

        $hasPrimary = false;
        foreach ($normalized as $entry) {
            if ($entry['is_primary'] === 1) {
                $hasPrimary = true;
                break;
            }
        }
        if (!$hasPrimary) {
            $normalized[0]['is_primary'] = 1;
        }

        $insertQuery = 'INSERT INTO inventory_supplier_sources '
            . '(inventory_id, supplier_id, unit_cost, min_order_qty, lead_time_days, is_primary, is_active, first_seen_date, last_purchase_date) '
            . 'VALUES (?, ?, ?, ?, ?, ?, 1, CURDATE(), CURDATE())';

        $insertStmt = mysqli_prepare($this->db, $insertQuery);
        if (!$insertStmt) {
            $this->lastError = 'Prepare failed in replaceInventorySupplierSources(insert): ' . mysqli_error($this->db);
            error_log($this->lastError);
            return false;
        }

        foreach ($normalized as $entry) {
            $supplierId = $entry['supplier_id'];
            $unitCost = $entry['unit_cost'];
            $minOrderQty = $entry['min_order_qty'];
            $leadTimeDays = $entry['lead_time_days'];
            $isPrimary = $entry['is_primary'];

            mysqli_stmt_bind_param(
                $insertStmt,
                'iidiii',
                $safeInventoryId,
                $supplierId,
                $unitCost,
                $minOrderQty,
                $leadTimeDays,
                $isPrimary
            );

            if (!mysqli_stmt_execute($insertStmt)) {
                $this->lastError = 'Execute failed in replaceInventorySupplierSources(insert): ' . mysqli_stmt_error($insertStmt);
                error_log($this->lastError);
                return false;
            }
        }

        return true;
    }

    private function buildInventoryBaseSql($filters, $countOnly) {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';
        $status = isset($filters['status']) ? strtolower(trim((string) $filters['status'])) : 'all';
        $fromDate = isset($filters['from_date']) ? trim((string) $filters['from_date']) : '';
        $toDate = isset($filters['to_date']) ? trim((string) $filters['to_date']) : '';

        $where = [];
        $types = '';
        $params = [];

        if ($search !== '') {
            $where[] = '(i.item_name LIKE CONCAT(\'%\', ?, \'%\') OR CAST(i.inventory_id AS CHAR) LIKE CONCAT(\'%\', ?, \'%\') OR CAST(COALESCE(i.supplier_id, 0) AS CHAR) LIKE CONCAT(\'%\', ?, \'%\'))';
            $types .= 'sss';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if ($status !== '' && $status !== 'all') {
            $where[] = 'LOWER(COALESCE(i.status, \'\')) = ?';
            $types .= 's';
            $params[] = $status;
        }

        if ($fromDate !== '') {
            $where[] = 'DATE(i.last_updated) >= ?';
            $types .= 's';
            $params[] = $fromDate;
        }

        if ($toDate !== '') {
            $where[] = 'DATE(i.last_updated) <= ?';
            $types .= 's';
            $params[] = $toDate;
        }

        $where[] = '(i.deleted_date IS NULL OR i.deleted_date = \'0000-00-00\')';
        $whereSql = implode(' AND ', $where);

        if ($countOnly) {
            $sql = 'SELECT COUNT(*) AS total_rows FROM inventory i WHERE ' . $whereSql;
        } else {
            $sql = 'SELECT i.inventory_id, i.item_name, i.supplier_id, i.quantity, i.reorder_level, i.status, i.last_updated FROM inventory i WHERE ' . $whereSql;
        }

        return [$sql, $types, $params];
    }

    private function buildSupplierBaseSql($filters, $countOnly) {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';
        $fromDate = isset($filters['from_date']) ? trim((string) $filters['from_date']) : '';
        $toDate = isset($filters['to_date']) ? trim((string) $filters['to_date']) : '';

        $where = [];
        $types = '';
        $params = [];

        if ($search !== '') {
            $where[] = '(s.supplier_name LIKE CONCAT(\'%\', ?, \'%\') OR COALESCE(s.contact_no, \'\') LIKE CONCAT(\'%\', ?, \'%\') OR COALESCE(s.email, \'\') LIKE CONCAT(\'%\', ?, \'%\') OR CAST(s.supplier_id AS CHAR) LIKE CONCAT(\'%\', ?, \'%\'))';
            $types .= 'ssss';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if ($fromDate !== '') {
            $where[] = 'DATE(s.created_at) >= ?';
            $types .= 's';
            $params[] = $fromDate;
        }

        if ($toDate !== '') {
            $where[] = 'DATE(s.created_at) <= ?';
            $types .= 's';
            $params[] = $toDate;
        }

        $whereSql = empty($where) ? '1=1' : implode(' AND ', $where);

        if ($countOnly) {
            $sql = 'SELECT COUNT(*) AS total_rows FROM suppliers s WHERE ' . $whereSql;
        } else {
            $sql = 'SELECT s.supplier_id, s.supplier_name, s.contact_no, s.location, s.email, s.created_at, s.updated_at FROM suppliers s WHERE ' . $whereSql;
        }

        return [$sql, $types, $params];
    }

    private function prepareAndBind($sql, $types, $params) {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        if ($types !== '' && !empty($params)) {
            $bindParams = [$types];
            foreach ($params as $index => $value) {
                $bindParams[] = &$params[$index];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindParams);
        }

        return $stmt;
    }

    private function tableExists($tableName) {
        $safeTable = mysqli_real_escape_string($this->db, (string) $tableName);
        $query = "SHOW TABLES LIKE '" . $safeTable . "'";
        $result = mysqli_query($this->db, $query);
        return $result && mysqli_num_rows($result) > 0;
    }

    // Add more methods as needed
}