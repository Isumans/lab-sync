<div id="deleteSupplierModal" class="supplier-delete-modal-overlay" aria-hidden="true">
    <div class="supplier-delete-modal" role="dialog" aria-modal="true" aria-labelledby="deleteSupplierTitle">
        <div class="supplier-delete-header">
            <div class="supplier-delete-title-wrap">
                <span class="supplier-delete-icon" aria-hidden="true">!</span>
                <h3 id="deleteSupplierTitle">Delete Supplier</h3>
            </div>
            <button type="button" id="closeDeleteSupplierModal" class="supplier-delete-close" aria-label="Close">&times;</button>
        </div>

        <p class="supplier-delete-message">Are you sure you want to delete this supplier? This action cannot be undone.</p>

        <div class="supplier-delete-details">
            <p class="supplier-delete-label">Supplier ID</p>
            <p id="deleteSupplierIdText" class="supplier-delete-value">-</p>
            <p class="supplier-delete-label">Supplier Name</p>
            <p id="deleteSupplierNameText" class="supplier-delete-value">-</p>
        </div>

        <div class="supplier-delete-actions">
            <button type="button" id="confirmDeleteSupplier" class="supplier-delete-confirm">Delete Supplier</button>
            <button type="button" id="cancelDeleteSupplier" class="supplier-delete-cancel">Cancel</button>
        </div>

        <p class="supplier-delete-footnote">SYSTEM: AUTHORIZATION REQUIRED</p>
    </div>
</div>

<form id="deleteSupplierForm" method="POST" action="/lab_sync/index.php?controller=supplierController&action=delete<?php echo $roleParam; ?>" style="display:none;">
    <input type="hidden" id="delete_supplier_id" name="supplier_id" value="">
</form>
