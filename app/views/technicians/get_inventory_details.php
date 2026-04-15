<?php
if (!function_exists('inventoryDetailsEscape')) {
    function inventoryDetailsEscape($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('inventoryDetailsFormatDateTime')) {
    function inventoryDetailsFormatDateTime($value) {
        if ($value === null || trim((string) $value) === '') {
            return 'N/A';
        }

        $timestamp = strtotime((string) $value);
        if ($timestamp === false) {
            return inventoryDetailsEscape($value);
        }

        return date('Y-m-d h:i A', $timestamp);
    }
}

if (!function_exists('inventoryDetailsFormatDate')) {
    function inventoryDetailsFormatDate($value) {
        if ($value === null || trim((string) $value) === '') {
            return 'N/A';
        }

        $timestamp = strtotime((string) $value);
        if ($timestamp === false) {
            return inventoryDetailsEscape($value);
        }

        return date('Y-m-d', $timestamp);
    }
}

if (!function_exists('inventoryDetailsFormatMoney')) {
    function inventoryDetailsFormatMoney($value) {
        if ($value === null || $value === '') {
            return 'N/A';
        }

        if (!is_numeric($value)) {
            return inventoryDetailsEscape($value);
        }

        return '$' . number_format((float) $value, 2);
    }
}

$inventoryId = isset($inventory['inventory_id']) ? intval($inventory['inventory_id']) : 0;
$itemName = $inventory['item_name'] ?? 'Unknown Item';
?>
<div class="inventory-details-shell">
    <div class="inventory-details-header">
        <div>
            <h2>Inventory Details: #<?php echo inventoryDetailsEscape('INV-' . str_pad((string) $inventoryId, 4, '0', STR_PAD_LEFT)); ?></h2>
            <p class="inventory-details-sub">Item: <?php echo inventoryDetailsEscape($itemName); ?></p>
        </div>
    </div>

    <div class="inventory-details-grid">
        <section class="inventory-card inventory-item-profile">
            <h3>Item Profile</h3>
            <div class="inventory-card-body">
                <div class="profile-name"><?php echo inventoryDetailsEscape($itemName); ?></div>
                <div class="profile-id">INV: <?php echo inventoryDetailsEscape(str_pad((string) $inventoryId, 4, '0', STR_PAD_LEFT)); ?></div>

                <div class="profile-meta-grid">
                    <div>
                        <span class="label">Status</span>
                        <strong><?php echo inventoryDetailsEscape($inventory['status'] ?? 'N/A'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Category</span>
                        <strong><?php echo inventoryDetailsEscape($inventory['category_name'] ?? 'Uncategorized'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Quantity</span>
                        <strong><?php echo inventoryDetailsEscape($inventory['quantity'] ?? '0'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Reorder Level</span>
                        <strong><?php echo inventoryDetailsEscape($inventory['reorder_level'] ?? '0'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Unit</span>
                        <strong><?php echo inventoryDetailsEscape($inventory['unit_of_measure'] ?? 'N/A'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Primary Supplier</span>
                        <strong><?php echo inventoryDetailsEscape($inventory['primary_supplier_name'] ?? 'N/A'); ?></strong>
                    </div>
                    <div>
                        <span class="label">Item Unit Cost</span>
                        <strong><?php echo inventoryDetailsEscape(inventoryDetailsFormatMoney($inventory['unit_cost'] ?? null)); ?></strong>
                    </div>
                    <div>
                        <span class="label">Expiry Date</span>
                        <strong><?php echo inventoryDetailsEscape(inventoryDetailsFormatDate($inventory['expiry_date'] ?? null)); ?></strong>
                    </div>
                    <div>
                        <span class="label">Last Updated</span>
                        <strong><?php echo inventoryDetailsEscape(inventoryDetailsFormatDateTime($inventory['last_updated'] ?? null)); ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="inventory-card inventory-supplier-sources">
            <h3>Supplier Price Sources</h3>
            <div class="inventory-card-body">
                <?php if (!empty($suppliers)): ?>
                    <div class="inventory-supplier-table-wrap">
                        <table class="inventory-supplier-table">
                            <thead>
                                <tr>
                                    <th>Supplier Name</th>
                                    <th>Unit Cost</th>
                                    <th>Primary</th>
                                    <th>Lead Time / Min Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td><?php echo inventoryDetailsEscape($supplier['supplier_name'] ?? 'Unknown Supplier'); ?></td>
                                        <td><?php echo inventoryDetailsEscape(inventoryDetailsFormatMoney($supplier['unit_cost'] ?? null)); ?></td>
                                        <td>
                                            <?php if (!empty($supplier['is_primary'])): ?>
                                                <span class="pill paid">Primary</span>
                                            <?php else: ?>
                                                <span class="pill pending">Secondary</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $leadTime = isset($supplier['lead_time_days']) && $supplier['lead_time_days'] !== null && $supplier['lead_time_days'] !== ''
                                                    ? intval($supplier['lead_time_days']) . ' days'
                                                    : 'N/A';
                                                $minOrder = isset($supplier['min_order_qty']) && $supplier['min_order_qty'] !== null && $supplier['min_order_qty'] !== ''
                                                    ? intval($supplier['min_order_qty'])
                                                    : 'N/A';
                                            ?>
                                            <?php echo inventoryDetailsEscape($leadTime . ' / ' . $minOrder); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="details-empty">No supplier sources are configured for this item.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
