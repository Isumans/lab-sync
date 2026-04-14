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
        $query = "SELECT * FROM inventory";
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
    public function deleteItem($itemId) {
        $query = "DELETE FROM inventory WHERE inventory_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $itemId);
        return mysqli_stmt_execute($stmt);
    }
    public function updateItem($itemId, $itemName, $quantity, $reorderLevel, $supplierId) {
        $query = "UPDATE inventory SET item_name = ?, quantity = ?, reorder_level = ?, supplier_id = ? WHERE inventory_id = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'siiii', $itemName, $quantity, $reorderLevel, $supplierId, $itemId);
        return mysqli_stmt_execute($stmt);
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

        $whereSql = empty($where) ? '1=1' : implode(' AND ', $where);

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