<div id="viewSupplierModal" class="supplier-view-modal-overlay" aria-hidden="true">
    <div class="supplier-view-modal" role="dialog" aria-modal="true" aria-labelledby="viewSupplierTitle">
        <div class="supplier-view-header">
            <div class="supplier-view-heading">
                <h3 id="viewSupplierTitle">Supplier Items</h3>
                <p>View all supplying items for the selected supplier.</p>
            </div>
            <button type="button" id="closeViewSupplierModal" class="supplier-view-close" aria-label="Close">&times;</button>
        </div>

        <div class="supplier-view-meta">
            <p class="supplier-view-label">Supplier ID</p>
            <p id="viewSupplierId" class="supplier-view-value">-</p>
            <p class="supplier-view-label">Supplier Name</p>
            <p id="viewSupplierName" class="supplier-view-value">-</p>
        </div>

        <div class="supplier-view-items-wrap">
            <p class="supplier-view-items-label">Supplying Items</p>
            <ul id="viewSupplierItemsList" class="supplier-view-items-list"></ul>
            <p id="viewSupplierItemsEmpty" class="supplier-view-empty">No supplying items listed.</p>
        </div>

        <div class="supplier-view-actions">
            <button type="button" id="closeViewSupplierModalBtn" class="supplier-view-ok">Close</button>
        </div>
    </div>
</div>
