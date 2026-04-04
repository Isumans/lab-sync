# Inventory Management System - Implementation Guide

## Overview
This implementation makes the 'All Items', 'Stock History', and 'Categories' sections fully functional using the MVC architecture.

## What's Been Changed

### 1. Model Layer (`app/models/inventoryModel.php`)
**New Methods Added:**

- `getAllItems()` - Updated to include category information
- `addItem()` - Enhanced with category support and automatic stock history logging
- `updateItem()` - Now tracks quantity changes in stock history
- `deleteItem()` - Remains unchanged
- `getStockHistory()` - Retrieves all stock transaction history
- `addStockHistory()` - Logs inventory changes for audit trail
- `getAllCategories()` - Fetches all inventory categories
- `addCategory()` - Creates new category
- `updateCategory()` - Modifies existing category
- `deleteCategory()` - Removes category
- `getCategoryCount()` - Returns total number of categories

### 2. Controller Layer (`app/controllers/inventoryController.php`)
**Updated Methods:**

- `index()` - Now passes `$stockHistory`, `$categories`, and `$categoryCount` to view
- `store()` - Supports category assignment for new items
- `edit_item()` - Updated to handle category_id
- `add_category()` - NEW: Handles category creation
- `edit_category()` - NEW: Handles category updates and deletions

### 3. View Layer (`app/views/technicians/inventory.php`)
**Complete Redesign:**

**All Items Section:**
- Displays inventory table with columns: ID, Name, Category, Supplier, Quantity, Reorder Level, Status, Actions
- Edit and delete functionality for each item
- Dynamic status badges (In Stock, Low Stock, Out of Stock)

**Stock History Section:**
- Shows historical log of all inventory transactions
- Columns: History ID, Item Name, Quantity, Action, Notes, Date & Time
- Color-coded action indicators (Added/Removed)
- Ordered by most recent first

**Categories Section:**
- Complete CRUD (Create, Read, Update, Delete) for inventory categories
- Modal popup for adding new categories
- Edit and delete functionality for existing categories

**Dashboard Cards:**
- Total Items count
- Low Stock Items count (calculated dynamically)
- Out of Stock Items count
- Total Categories count

## Setup Instructions

### Step 1: Run Database Setup Script
Execute the SQL queries in `config/inventory_setup.sql` in your MySQL database:

```sql
-- Copy and paste the contents of config/inventory_setup.sql into MySQL
-- Or use command line:
mysql -u root laboratory < config/inventory_setup.sql
```

This will:
- Create `inventory_categories` table
- Create `stock_history` table
- Add `category_id` column to `inventory` table
- Insert default categories

### Step 2: Verify Database Tables
Check that the following tables exist:
```
- inventory
- inventory_categories
- stock_history
```

### Step 3: Test the Functionality

1. **Navigate to Inventory:** `/lab_sync/index.php?controller=inventoryController&action=index`

2. **All Items Tab:**
   - View all inventory items with their categories
   - Edit any field (except ID)
   - Delete items
   - Add new items: Click "+Create New Item" button

3. **Stock History Tab:**
   - View complete transaction history
   - Each item addition/update creates a history entry
   - Automatically tracks quantity changes

4. **Categories Tab:**
   - View existing categories
   - Edit category name and description
   - Delete categories
   - Add new categories: Click "+Add Category" button

## Database Schema

### inventory_categories Table
```sql
CREATE TABLE inventory_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### stock_history Table
```sql
CREATE TABLE stock_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventory(inventory_id) ON DELETE CASCADE,
    INDEX idx_inventory_id (inventory_id),
    INDEX idx_created_at (created_at)
);
```

### inventory Table Update
```sql
ALTER TABLE inventory ADD COLUMN category_id INT;
ALTER TABLE inventory ADD CONSTRAINT fk_category 
FOREIGN KEY (category_id) REFERENCES inventory_categories(category_id) ON DELETE SET NULL;
```

## Features Implemented

### 1. Category Management
- ✅ Add categories via modal popup
- ✅ Edit category name and description
- ✅ Delete categories
- ✅ Assign categories to items
- ✅ Category count on dashboard

### 2. Stock History Tracking
- ✅ Automatic logging of item additions
- ✅ Track quantity changes
- ✅ Record user actions with timestamps
- ✅ Add notes to transactions
- ✅ Historical audit trail

### 3. Inventory Item Management
- ✅ Full CRUD operations
- ✅ Dynamic status badges
- ✅ Low stock alerts
- ✅ Out of stock tracking
- ✅ Category assignment per item

### 4. Dashboard Analytics
- ✅ Total items count
- ✅ Low stock items count
- ✅ Out of stock items count
- ✅ Total categories count

## File Locations
- Model: `app/models/inventoryModel.php`
- Controller: `app/controllers/inventoryController.php`
- View: `app/views/technicians/inventory.php`
- Database Setup: `config/inventory_setup.sql`

## API Endpoints (URL Format)
- View Inventory: `/lab_sync/index.php?controller=inventoryController&action=index`
- Add Item: `/lab_sync/index.php?controller=inventoryController&action=add_inventory`
- Add Category: `/lab_sync/index.php?controller=inventoryController&action=add_category`
- Edit Item: `/lab_sync/index.php?controller=inventoryController&action=edit_item` (POST)
- Edit Category: `/lab_sync/index.php?controller=inventoryController&action=edit_category` (POST)

## MVC Architecture Compliance
✅ Model: Handles all database operations and business logic
✅ View: Displays data and user interface
✅ Controller: Orchestrates model-view interaction and request handling
✅ Separation of Concerns: Each layer has distinct responsibilities
✅ Reusability: Methods can be used by other controllers/views

## Notes
- All inventory changes are automatically logged in stock_history
- Status calculations are dynamic based on quantity vs. reorder_level
- The inventory table must have: inventory_id, item_name, quantity, reorder_level, supplier_id
- Default categories are inserted: Medical Supplies, Laboratory Equipment, Chemicals, Consumables, Equipment

## Troubleshooting

**Issue:** "Unknown column 'category_id' in field list"
- Solution: Run the database setup script to add the column

**Issue:** "Table 'stock_history' doesn't exist"
- Solution: Run the database setup script to create the table

**Issue:** "No items showing in All Items tab"
- Solution: Make sure inventory table has data. Use "Create New Item" to add items

**Issue:** Categories not showing
- Solution: Run the database setup script to create the default categories

## Next Steps (Optional Enhancements)
- Add search/filter functionality for inventory items
- Add export to CSV feature
- Add stock level notifications/alerts
- Add supplier management integration
- Add barcode scanning support
- Add inventory forecasting
- Add multi-location support
