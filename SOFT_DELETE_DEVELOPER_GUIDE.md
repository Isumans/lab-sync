# Soft Delete Developer Reference Guide

## API Reference

### Backend Methods

#### `inventoryModel::softDeleteItem($itemId, $deletedByUserId)`

**Purpose:** Performs soft deletion of an inventory item

**Parameters:**
- `$itemId` (int) - The ID of the inventory item to delete
- `$deletedByUserId` (int|null) - The user ID performing deletion (0 if system delete, null converts to 0)

**Returns:**
- `bool` - true if soft delete successful, false otherwise

**Example Usage:**
```php
$inventoryModel = new inventoryModel();
$success = $inventoryModel->softDeleteItem(123, $_SESSION['user_id']);

if ($success) {
    error_log("Item 123 deleted by user " . $_SESSION['user_id']);
} else {
    error_log("Failed to delete item 123");
}
```

**Database Changes:**
```sql
-- Before
UPDATE inventory 
SET deleted_date = '2026-04-07',
    deleted_time = '14:30:45',
    deleted_by = 5
WHERE inventory_id = 123
  AND deleted_date IS NULL;
```

---

#### `inventoryModel::deleteItem($itemId)`

**Purpose:** Backward-compatible method (now uses soft delete)

**Parameters:**
- `$itemId` (int) - The ID of the inventory item to delete

**Returns:**
- `bool` - true if successful, false otherwise

**Note:** This method is kept for backward compatibility and internally calls `softDeleteItem()` with `deleted_by = 0`.

**Example Usage:**
```php
// System delete (no user tracking)
$inventoryModel->deleteItem(123);
```

---

#### `inventoryModel::getAllItems()`

**Purpose:** Retrieve all active (non-deleted) inventory items

**Returns:**
- `array` - Array of inventory item arrays with keys:
  - `inventory_id`
  - `supplier_id`
  - `supplier_name`
  - `item_name`
  - `quantity`
  - `reorder_level`
  - `category_name`
  - `status`
  - `unit_cost`
  - `unit_of_measure`
  - `category_id`

**Example Usage:**
```php
$items = $inventoryModel->getAllItems();

foreach ($items as $item) {
    // Only active items are returned (never soft-deleted)
    echo $item['item_name'] . " - Qty: " . $item['quantity'];
}
```

**SQL Generated:**
```sql
SELECT i.inventory_id, i.supplier_id, s.supplier_name, i.item_name,
       i.quantity, i.reorder_level, ic.category_name, i.status,
       i.unit_cost, i.unit_of_measure, ic.category_id
FROM inventory i
LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
LEFT JOIN inventory_categories ic ON i.category_id = ic.category_id
WHERE i.deleted_date IS NULL AND i.deleted_time IS NULL
ORDER BY i.inventory_id ASC;
```

**Note:** If you delete an item, it will NOT appear in this result set.

---

#### `inventoryModel::getItemById($inventory_id)`

**Purpose:** Retrieve a specific active inventory item

**Parameters:**
- `$inventory_id` (int) - The inventory item ID

**Returns:**
- `array|null` - Item data if found and active, null if not found or deleted

**Example Usage:**
```php
$item = $inventoryModel->getItemById(123);

if ($item) {
    echo "Item: " . $item['item_name'];
} else {
    echo "Item not found or has been deleted";
}
```

---

#### `inventoryModel::getDashboardStats()`

**Purpose:** Get dashboard statistics for active inventory only

**Returns:**
- `array` - Stats with keys:
  - `total_items` - Count of active inventory items
  - `low_stock` - Count of items with quantity <= reorder_level
  - `out_of_stock` - Count of items with quantity = 0
  - `total_categories` - Count of all categories

**Example Usage:**
```php
$stats = $inventoryModel->getDashboardStats();

echo "Total Items: " . $stats['total_items'];
echo "Low Stock: " . $stats['low_stock'];
echo "Out of Stock: " . $stats['out_of_stock'];
```

**Important:** Soft-deleted items are automatically excluded from all counts.

---

### Frontend Methods

#### `handleSoftDelete(event)`

**Purpose:** AJAX handler for soft delete operations

**Parameters:**
- `event` - The JavaScript event object from form submission

**Behavior:**
1. Prevents default form submission
2. Shows confirmation dialog
3. Sends AJAX POST request to backend
4. Animates row removal on success
5. Shows success/error notification

**Example Integration:**
```javascript
// Automatically attached to all delete buttons
// No manual integration needed - it intercepts the form submission

// But if you need custom integration:
document.querySelector('.action-btn-delete').addEventListener('click', function(e) {
    handleSoftDelete(e);
});
```

---

#### `showNotification(message, type)`

**Purpose:** Display toast notification

**Parameters:**
- `message` (string) - The notification message
- `type` (string) - 'success' or 'error'

**Example Usage:**
```javascript
showNotification('Item deleted successfully', 'success');
showNotification('Error: Could not delete item', 'error');
```

---

### Controller Methods

#### `inventoryController::soft_delete_item()`

**Purpose:** AJAX endpoint for soft delete requests

**HTTP Method:** POST

**Request Headers Required:**
- `X-Requested-With: XMLHttpRequest` (AJAX detection)

**POST Parameters:**
- `inventory_id` (int) - The inventory item to delete

**Response:** JSON
```json
{
    "success": true,
    "message": "Item deleted successfully",
    "inventory_id": 123
}
```

**Error Response:**
```json
{
    "success": false,
    "error": "Error message here"
}
```

**Example Usage:**
```javascript
const formData = new FormData();
formData.append('inventory_id', 123);

fetch('/lab_sync/index.php?controller=inventoryController&action=soft_delete_item', {
    method: 'POST',
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Item deleted: ' + data.inventory_id);
    } else {
        console.error('Error: ' + data.error);
    }
})
.catch(error => console.error('Error:', error));
```

---

## SQL Reference

### Query for Active Items Only
```sql
SELECT * FROM inventory 
WHERE deleted_date IS NULL AND deleted_time IS NULL;
```

### Query for Deleted Items (Audit Trail)
```sql
SELECT inventory_id, item_name, deleted_date, deleted_time, deleted_by, 
       u.username -- if you have a users table
FROM inventory
WHERE deleted_date IS NOT NULL
ORDER BY deleted_date DESC;
```

### Query for Item Deletion History
```sql
SELECT i.inventory_id, i.item_name, i.deleted_date, i.deleted_time, i.deleted_by,
       u.username
FROM inventory i
LEFT JOIN users u ON i.deleted_by = u.user_id
WHERE i.inventory_id = 123
  AND i.deleted_date IS NOT NULL;
```

### Restore a Soft-Deleted Item
```sql
UPDATE inventory 
SET deleted_date = NULL, 
    deleted_time = NULL, 
    deleted_by = NULL
WHERE inventory_id = 123;
```

### Count Deleted Items
```sql
SELECT COUNT(*) as deleted_count 
FROM inventory 
WHERE deleted_date IS NOT NULL;
```

### Find Items Deleted by Specific User
```sql
SELECT inventory_id, item_name, deleted_date, deleted_time 
FROM inventory 
WHERE deleted_by = 5
ORDER BY deleted_date DESC;
```

---

## Integration Examples

### Adding Soft Delete to Other Tables

To add soft delete to another table (e.g., `suppliers`):

#### 1. Add Database Columns
```sql
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS deleted_date DATE NULL;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS deleted_time TIME NULL;
ALTER TABLE suppliers ADD COLUMN IF NOT EXISTS deleted_by INT NULL;
```

#### 2. Create Soft Delete Helper
```php
class supplierModel {
    private function getSoftDeleteFilter() {
        return "s.deleted_date IS NULL AND s.deleted_time IS NULL";
    }

    public function softDeleteSupplier($supplierId, $deletedByUserId) {
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        $deletedByUserId = $deletedByUserId ?? 0;

        $query = "UPDATE suppliers 
                  SET deleted_date = ?, deleted_time = ?, deleted_by = ?
                  WHERE supplier_id = ? AND deleted_date IS NULL";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ssii', $currentDate, $currentTime, $deletedByUserId, $supplierId);
        return mysqli_stmt_execute($stmt);
    }

    public function getAllSuppliers() {
        $query = "SELECT * FROM suppliers 
                  WHERE " . $this->getSoftDeleteFilter() . "
                  ORDER BY supplier_name ASC";
        $result = mysqli_query($this->db, $query);
        $suppliers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $suppliers[] = $row;
        }
        return $suppliers;
    }
}
```

#### 3. Update Controller
```php
public function soft_delete_supplier() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not authorized']);
        exit();
    }

    $supplierId = $_POST['supplier_id'] ?? null;
    if (!$supplierId) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit();
    }

    $supplierModel = new supplierModel();
    $success = $supplierModel->softDeleteSupplier($supplierId, $_SESSION['user_id']);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Deleted successfully' : 'Failed to delete'
    ]);
    exit();
}
```

---

## Error Handling

### Common Error Scenarios

#### User Not Authenticated
```php
if (!isset($_SESSION['user_id'])) {
    return ['success' => false, 'error' => 'User must be logged in'];
}
```

#### Item Already Deleted
```php
// softDeleteItem checks: WHERE ... AND deleted_date IS NULL
// This prevents re-deletion
```

#### Invalid Item ID
```php
if (!$itemId || !is_numeric($itemId)) {
    return ['success' => false, 'error' => 'Invalid inventory ID'];
}
```

#### Database Connection Error
```php
if (!$stmt) {
    error_log("Prepare failed: " . mysqli_error($this->db));
    return ['success' => false, 'error' => 'Database error'];
}
```

---

## Performance Tuning

### Index Usage
```sql
-- Check if indexes are being used
EXPLAIN 
SELECT * FROM inventory 
WHERE deleted_date IS NULL AND deleted_time IS NULL;

-- Check index statistics
SHOW INDEX FROM inventory WHERE Key_name IN ('idx_deleted_date', 'idx_deleted_by');
```

### Query Optimization
```sql
-- Use composite index for common queries
ALTER TABLE inventory 
ADD INDEX idx_deleted_active (deleted_date, category_id)
WHERE deleted_date IS NULL;
```

---

## Logging & Audit Trail

### Log Deletion Events
```php
// In your soft delete handler
error_log("INVENTORY DELETE: Item $itemId deleted by User $userId at " . date('Y-m-d H:i:s'));
```

### Query Deletion History
```php
public function getDeletionAuditTrail($limit = 50) {
    $query = "SELECT i.inventory_id, i.item_name, i.deleted_date, i.deleted_time, i.deleted_by,
             COUNT(sh.history_id) as history_count
             FROM inventory i
             LEFT JOIN stock_history sh ON i.inventory_id = sh.inventory_id AND sh.deleted_date IS NULL
             WHERE i.deleted_date IS NOT NULL
             GROUP BY i.inventory_id
             ORDER BY i.deleted_date DESC
             LIMIT ?";
    $stmt = mysqli_prepare($this->db, $query);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $trail = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $trail[] = $row;
    }
    return $trail;
}
```

---

## Testing Examples

### Unit Test
```php
class InventoryModelTest {
    public function testSoftDeleteItem() {
        $model = new inventoryModel();
        
        // Get an item ID
        $items = $model->getAllItems();
        $testItemId = $items[0]['inventory_id'];
        
        // Soft delete it
        $result = $model->softDeleteItem($testItemId, 5);
        $this->assertTrue($result);
        
        // Verify it's gone from active items
        $items = $model->getAllItems();
        $ids = array_column($items, 'inventory_id');
        $this->assertNotContains($testItemId, $ids);
    }
}
```

### Integration Test
```javascript
// Test soft delete AJAX endpoint
async function testSoftDeleteEndpoint() {
    const formData = new FormData();
    formData.append('inventory_id', 123);

    const response = await fetch(
        '/lab_sync/index.php?controller=inventoryController&action=soft_delete_item',
        {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        }
    );

    const data = await response.json();
    console.assert(data.success === true, 'Soft delete should succeed');
    console.log('✓ Soft delete endpoint working');
}
```

---

## Migration Guide

### From Hard Delete to Soft Delete

If you have existing code using hard deletes:

**Before:**
```php
$db->query("DELETE FROM inventory WHERE inventory_id = " . $id);
```

**After:**
```php
$inventoryModel = new inventoryModel();
$inventoryModel->softDeleteItem($id, $_SESSION['user_id']);
```

---

**Documentation Version:** 1.0
**Last Updated:** April 7, 2026
**Compatibility:** PHP 7.2+, MySQL 5.7+
