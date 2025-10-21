<?php
require_once 'app/models/inventoryModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';
class inventoryController {
    private $db;

    public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }
    public function index() {
        $inventoryModel = new inventoryModel();
        $items = $inventoryModel->getAllItems();
        include 'C:\xampp\htdocs\lab_sync\app\views\technicians\inventory.php';
    }

    public function add_inventory() {
        include 'C:\xampp\htdocs\lab_sync\app\views\technicians\add_inventory.php';
    }
    public function store() {
        // Logic to store a new inventory item in the database
        $item_name = $_POST['item_name'];
        $quantity = $_POST['quantity'];
        $reorder_level = $_POST['reorder_level'];
        $supplier_id = $_POST['supplier_id'];

        $inventoryModel = new inventoryModel();
        $success = $inventoryModel->addItem($item_name, $quantity, $reorder_level, $supplier_id);

        if ($success) {
            // Redirect back to inventory list after successful insertion
            header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
        } else {
            echo "Error adding item: " . mysqli_error($this->db);
        }
    }
    public function edit_item() {
        $inventoryModel = new inventoryModel();
        // Logic to edit an existing inventory item
        if (isset($_POST['delete'])) {
            $itemId = $_POST['inventory_id'];
            // Logic to delete the item from the database
            $success=$inventoryModel->deleteItem($itemId);
            // Redirect back to inventory list after deletion
            if($success){
                header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
            } else {
                echo "Error deleting item.";
            }
            
        }elseif(isset($_POST['edit'])) {
            $itemId = $_POST['inventory_id'];
            $itemName = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $reorderLevel = $_POST['reorder_level'];
            $supplierId = $_POST['supplier_id'];
            // Logic to update the item details in the database
            $success=$inventoryModel->updateItem($itemId, $itemName, $quantity, $reorderLevel, $supplierId);
            // Redirect back to inventory list after update
            if($success){
                header('Location: /lab_sync/index.php?controller=inventoryController&action=index');
            } else {
                echo "Error updating item.";
            }
        }
    }

    // Add more methods as needed
}
?>