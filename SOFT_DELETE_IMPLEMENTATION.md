# Soft Delete Implementation for Inventory Management

## Overview
This document details the comprehensive soft delete functionality implemented for all inventory operations. Soft deletes mark items as deleted without removing them from the database, maintaining data integrity and audit trails.

---

## 1. Database Changes

### Migration: `2026_04_07_add_soft_delete_columns.sql`

Three new columns were added to `inventory` and `stock_history` tables:

```sql
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;
ALTER TABLE inventory ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;

ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_date DATE NULL DEFAULT NULL;
ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_time TIME NULL DEFAULT NULL;
ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS deleted_by INT NULL DEFAULT NULL;
```

**Columns Description:**
- `deleted_date`: Records the date when the item was soft-deleted (NULL if not deleted)
- `deleted_time`: Records the time when the item was soft-deleted (NULL if not deleted)
- `deleted_by`: Records the user ID who performed the soft delete (0 for system deletes)

**Indexes Added:**
- `idx_deleted_date` on both tables for efficient filtering
- `idx_deleted_by` on inventory table for audit trail tracking

---

## 2. Backend Implementation

### Model: `inventoryModel.php`

#### New Soft Delete Helper Method
```php
private function getSoftDeleteFilter() {
    return "i.deleted_date IS NULL AND i.deleted_time IS NULL";
}
```
Provides consistent WHERE clause across all queries to exclude soft-deleted items.

#### Soft Delete Methods

**`softDeleteItem($itemId, $deletedByUserId)`**
- Performs actual soft delete by updating deletion tracking columns
- Parameters:
  - `$itemId`: ID of inventory item to delete
  - `$deletedByUserId`: User ID performing the delete (0 for system)
- Returns: `true` if successful, `false` otherwise
- Includes validation to prevent re-deletion of already deleted items

**`deleteItem($itemId)`**
- Updated for backward compatibility
- Now calls `softDeleteItem()` instead of hard deleting

#### Updated Query Methods
All query methods now include soft delete filtering:

1. **`getAllItems()`** - Filters WHERE clause exclude soft-deleted items
2. **`getItemById($inventory_id)`** - Only returns active items
3. **`getStockHistory()`** - Excludes soft-deleted inventory and stock records
4. **`getStockHistoryDetails()`** - Filters both inventory and stock history deletions
5. **`getCategoryWithItems($category_id)`** - Shows only active items in categories
6. **`getAllItems_ForSelection()`** - Dropdown excludes deleted items
7. **`updateItemStatus($itemId)`** - Only updates active items
8. **`updateItem()`** - Only edits active items
9. **`getDashboardStats()`** - Dashboard counts exclude soft-deleted items

### Controller: `inventoryController.php`

#### Updated `edit_item()` Action
- Added authorization check using `$_SESSION['user_id']`
- Calls `softDeleteItem()` with authenticated user ID
- Maintains backward compatibility with edit functionality

#### New `soft_delete_item()` AJAX Endpoint
```php
public function soft_delete_item()
```
- Dedicated AJAX endpoint for client-side soft delete requests
- Validates request method and AJAX header
- Verifies user authentication
- Validates inventory ID parameter
- Returns JSON response with success/error status
- Logs user ID who performed deletion

#### Helper Method
```php
private function isAjax()
```
- Validates if request is XMLHttpRequest
- Ensures API endpoints are called correctly

---

## 3. Frontend Implementation

### JavaScript: `inventorySoftDelete.js`

This new dedicated JavaScript file handles all soft delete client-side operations:

#### Core Functions

**`initializeSoftDeleteHandlers()`**
- Runs on page load
- Attaches delete event listeners to all delete buttons
- Prepares forms for AJAX interception

**`handleSoftDelete(event)`**
- Main handler for delete operations
- Shows confirmation dialog with item name
- Performs AJAX call to backend
- Handles response and updates UI
- Removes deleted row from table with fade animation
- Shows success/error notification
- Updates dashboard statistics

**`showNotification(message, type)`**
- Creates styled toast notification
- Auto-dismisses after 4 seconds
- Supports 'success' and 'error' types
- Uses CSS animations for smooth appearance/disappearance

#### AJAX Request
- Sends POST request with `X-Requested-With: XMLHttpRequest` header
- Passes inventory_id parameter
- Handles success response by removing row from DOM
- Handles errors gracefully

### Updated Files

**`showAlert.js`** - Modified to dispatch to soft delete handler
```php
function showAlertAndSubmit(event, action) {
    if (action === 'delete') {
        // Routes to handleSoftDelete if available
        if (typeof handleSoftDelete === 'function') {
            handleSoftDelete(event);
        }
    }
}
```

**`inventory.php`** - Updated script includes
- Removed: `showAlert.js` (replaced with soft delete handler)
- Added: `inventorySoftDelete.js`

---

## 4. Features & Capabilities

### Soft Delete Benefits
✅ Data preservation for audit trails
✅ Ability to restore items if needed
✅ User tracking (who deleted what and when)
✅ Non-destructive operations
✅ Compliance with data retention policies

### User Experience
✅ Confirmation dialog before deletion
✅ Real-time row removal from view
✅ Success/error notifications
✅ Smooth animations
✅ No page reload required

### Security & Authorization
✅ User login validation required
✅ User ID recorded with deletion
✅ AJAX header validation
✅ Prepared statements prevent SQL injection
✅ Session-based authorization

### Data Integrity
✅ Soft deleted items excluded from all counts
✅ Stock history maintained
✅ Category associations preserved
✅ Related data remains accessible for audit

---

## 5. Database Schema

### Inventory Table Structure (after migration)
```
inventory (
    inventory_id INT (PK)
    item_name VARCHAR(255)
    quantity INT
    reorder_level INT
    supplier_id INT (FK)
    category_id INT (FK)
    unit_cost DECIMAL
    unit_of_measure VARCHAR(50)
    status ENUM
    ...
    deleted_date DATE NULL ← NEW
    deleted_time TIME NULL ← NEW
    deleted_by INT NULL ← NEW
    INDEX idx_deleted_date (deleted_date) ← NEW
    INDEX idx_deleted_by (deleted_by) ← NEW
)
```

### Stock History Table Structure (after migration)
```
stock_history (
    history_id INT (PK)
    inventory_id INT (FK)
    quantity INT
    action VARCHAR(50)
    ...
    deleted_date DATE NULL ← NEW
    deleted_time TIME NULL ← NEW
    deleted_by INT NULL ← NEW
    INDEX idx_deleted_date (deleted_date) ← NEW
)
```

---

## 6. Implementation Workflow

### Delete Operation Flow

1. **User Action**
   - User clicks Delete button on inventory item row

2. **Client-Side**
   - JavaScript intercepts form submission
   - Shows confirmation dialog with item name
   - If confirmed, prepares AJAX request

3. **AJAX Request**
   - Sends POST to `soft_delete_item` endpoint
   - Includes inventory_id and X-Requested-With header
   - Passes through session authentication

4. **Backend Processing**
   - Validates AJAX request
   - Checks user authorization
   - Verifies inventory ID exists and not already deleted
   - Updates inventory record with:
     - Current date (deleted_date)
     - Current time (deleted_time)
     - User ID (deleted_by)

5. **Response**
   - Returns JSON with success/error status
   - Includes inventory_id if successful

6. **Frontend Update**
   - CSS animation fades out deleted row
   - Removes row from DOM
   - Shows success notification
   - Updates dashboard statistics
   - No page reload required

---

## 7. Query Examples

### Retrieve Only Active Items
```sql
SELECT * FROM inventory 
WHERE deleted_date IS NULL AND deleted_time IS NULL;
```

### View All Deletions (Audit Trail)
```sql
SELECT inventory_id, item_name, deleted_date, deleted_time, deleted_by 
FROM inventory 
WHERE deleted_date IS NOT NULL;
```

### Restore Deleted Item
```sql
UPDATE inventory 
SET deleted_date = NULL, deleted_time = NULL, deleted_by = NULL
WHERE inventory_id = ?;
```

---

## 8. Files Modified/Created

### New Files
- ✅ `config/migrations/2026_04_07_add_soft_delete_columns.sql`
- ✅ `public/js/inventorySoftDelete.js`

### Modified Files
- ✅ `app/models/inventoryModel.php`
  - Added soft delete helper method
  - Updated all 9+ query methods
  - Fixed authorization checks

- ✅ `app/controllers/inventoryController.php`
  - Updated edit_item() with soft delete
  - Added soft_delete_item() AJAX endpoint
  - Added isAjax() helper

- ✅ `app/views/technicians/inventory.php`
  - Changed script includes
  - Now loads inventorySoftDelete.js

- ✅ `public/js/showAlert.js`
  - Updated to dispatch soft delete requests
  - Maintains edit operation support

---

## 9. Testing Checklist

- [ ] Run migration SQL to add columns
- [ ] Verify new columns exist in database
- [ ] Delete an inventory item
  - [ ] Confirmation dialog appears
  - [ ] Row disappears from table
  - [ ] Success notification shows
  - [ ] Dashboard stats update
- [ ] Verify deleted item doesn't appear in:
  - [ ] All Items table
  - [ ] Category listings
  - [ ] Dropdown selections
  - [ ] Dashboard statistics
- [ ] Test without authentication (should fail)
- [ ] Test AJAX error handling
- [ ] Verify deleted_date, deleted_time, deleted_by populated
- [ ] Check audit trail in database

---

## 10. Future Enhancements

- [ ] Admin interface to view/restore soft-deleted items
- [ ] Permanent delete after retention period
- [ ] Soft delete for categories
- [ ] Bulk soft delete operations
- [ ] Detailed audit log viewer
- [ ] Email notifications on deletion
- [ ] Restore functionality with modal confirmation

---

## 11. Rollback Instructions

If you need to revert to hard delete:

1. Remove `inventorySoftDelete.js` script include from `inventory.php`
2. Restore original `showAlert.js` function
3. Revert `inventoryModel.php` changes to use DELETE instead of UPDATE
4. Revert controller changes
5. Keep database columns (they won't harm anything) or run:
   ```sql
   ALTER TABLE inventory DROP COLUMN deleted_date, DROP COLUMN deleted_time, DROP COLUMN deleted_by;
   ALTER TABLE stock_history DROP COLUMN deleted_date, DROP COLUMN deleted_time, DROP COLUMN deleted_by;
   ```

---

## 12. Support Information

For issues or questions about the soft delete functionality:
- Check database columns exist
- Verify user is authenticated (_SESSION['user_id'])
- Inspect browser console for JavaScript errors
- Check server error logs for PHP errors
- Verify AJAX endpoint is receiving requests correctly

---

**Implementation Date:** April 7, 2026
**Status:** Complete
**Testing Status:** Ready for QA
