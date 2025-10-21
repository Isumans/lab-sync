<?php
class inventoryModel {
    private $db;

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
    public function addItem($item_name, $quantity, $reorder_level, $supplier_id) {
        $query = "INSERT INTO inventory (item_name, quantity, reorder_level, supplier_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'siii', $item_name, $quantity, $reorder_level, $supplier_id);
        return mysqli_stmt_execute($stmt);
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

    // Add more methods as needed
}