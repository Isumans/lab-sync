<div id="supplier-new-item-modal" class="supplier-new-item-modal" aria-hidden="true">
    <div class="supplier-new-item-dialog" role="dialog" aria-modal="true" aria-labelledby="supplier-new-item-title">
        <div class="supplier-new-item-header">
            <div class="supplier-new-item-heading-wrap">
                <div class="supplier-new-item-heading-text">
                    <h3 id="supplier-new-item-title">Add New Item to Inventory</h3>
                    <p>Register a new product in the central laboratory database.</p>
                </div>
            </div>
            <button type="button" class="supplier-new-item-close" id="supplier-new-item-close" aria-label="Close">&times;</button>
        </div>

        <form id="supplier-new-item-form" class="supplier-new-item-form">
            <section class="supplier-modal-section">
                <div class="supplier-modal-section-label">Basic Information</div>
                <div class="supplier-new-item-grid">
                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-name">Item Name</label>
                        <input type="text" id="supplier-new-item-name" placeholder="Blood Collection Tube" required>
                    </div>

                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-category">Category</label>
                        <select id="supplier-new-item-category" required>
                            <option value="Consumables">Consumables</option>
                            <option value="Chemicals">Chemicals</option>
                            <option value="Diagnostics">Diagnostics</option>
                            <option value="Safety">Safety</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Containers">Containers</option>
                            <option value="Glassware">Glassware</option>
                            <option value="General">General</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="supplier-modal-section">
                <div class="supplier-modal-section-label">Stock Information</div>
                <div class="supplier-new-item-grid">
                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-cost">Unit Cost(Rs.)</label>
                        <input type="number" id="supplier-new-item-cost" placeholder="0.00" min="0" step="0.01">
                    </div>

                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-initial">Initial Quantity</label>
                        <input type="number" id="supplier-new-item-initial" placeholder="0" min="0" required>
                    </div>

                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-unit">Unit of Measure</label>
                        <select id="supplier-new-item-unit" required>
                            <option value="Units">Units</option>
                            <option value="Boxes">Boxes</option>
                            <option value="Bottles">Bottles</option>
                            <option value="Packs">Packs</option>
                            <option value="Kg">Kg</option>
                            <option value="L">L</option>
                        </select>
                    </div>

                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-reorder">Reorder Level</label>
                        <input type="number" id="supplier-new-item-reorder" placeholder="50" min="0" required>
                    </div>

                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-purchase-date">Purchase Date</label>
                        <input type="date" id="supplier-new-item-purchase-date">
                    </div>

                    <div class="supplier-new-item-field">
                        <label for="supplier-new-item-expiry-date">Expiry Date</label>
                        <input type="date" id="supplier-new-item-expiry-date">
                    </div>
                </div>
            </section>

            <div class="supplier-new-item-actions">
                <button type="button" id="supplier-new-item-cancel" class="supplier-new-item-cancel">Cancel</button>
                <button type="submit" class="supplier-new-item-submit">Add Item</button>
            </div>
        </form>
    </div>
</div>
