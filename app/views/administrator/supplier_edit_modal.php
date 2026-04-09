<div id="editSupplierModal" class="supplier-modal-overlay" aria-hidden="true">
    <div class="supplier-modal" role="dialog" aria-modal="true" aria-labelledby="editSupplierTitle">
        <div class="supplier-modal-header">
            <div class="supplier-modal-heading">
                <h3 id="editSupplierTitle">Edit Supplier Details</h3>
                <p>Update supplier profile and supply items.</p>
            </div>
            <button type="button" id="closeSupplierModal" class="supplier-modal-close" aria-label="Close">&times;</button>
        </div>

        <form id="editSupplierForm" class="supplier-modal-form" method="POST" action="/lab_sync/index.php?controller=supplierController&action=update<?php echo $roleParam; ?>">
            <section class="supplier-modal-section">
                <p class="supplier-modal-section-title">Basic Information</p>
                <div class="supplier-modal-grid">
                    <div class="supplier-modal-field">
                        <label for="edit_supplier_id">Supplier ID</label>
                        <input id="edit_supplier_id" name="edit_supplier_id" type="text" readonly>
                    </div>

                    <div class="supplier-modal-field">
                        <label for="edit_supplier_name">Supplier Name</label>
                        <input id="edit_supplier_name" name="edit_supplier_name" type="text" required>
                    </div>

                    <div class="supplier-modal-field">
                        <label for="edit_supplier_email">Email</label>
                        <input id="edit_supplier_email" name="edit_supplier_email" type="email" required>
                    </div>

                    <div class="supplier-modal-field">
                        <label for="edit_supplier_contact">Contact Number</label>
                        <input id="edit_supplier_contact" name="edit_supplier_contact" type="text" required>
                    </div>
                </div>
            </section>

            <section class="supplier-modal-section">
                <p class="supplier-modal-section-title">Supplying Items</p>
                <div class="supplier-modal-grid">
                    <div class="supplier-modal-field supplier-modal-field-full">
                        <label for="edit_supplier_items">Current Items</label>
                        <textarea id="edit_supplier_items" name="edit_supplier_items" rows="3" required></textarea>
                    </div>

                    <div class="supplier-modal-field supplier-modal-field-full">
                        <label for="edit_supplier_item_search">Add New Item</label>
                        <input
                            id="edit_supplier_item_search"
                            type="text"
                            placeholder="Search and add item..."
                            autocomplete="off"
                        >
                        <div id="edit_supplier_item_results" class="supplier-item-results" role="listbox" aria-label="Edit supplier item results"></div>
                    </div>
                </div>
            </section>

            <div class="supplier-modal-actions">
                <button type="button" id="cancelSupplierEdit" class="supplier-modal-cancel">Cancel</button>
                <button type="submit" class="supplier-modal-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>
