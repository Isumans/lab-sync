document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('editInventoryModal');
    var form = document.getElementById('editInventoryForm');
    if (!modal || !form) {
        return;
    }

    var closeBtn = document.getElementById('editInventoryClose');
    var cancelBtn = document.getElementById('editInventoryCancel');
    var submitBtn = document.getElementById('editInventorySubmit');
    var alertBox = document.getElementById('editInventoryAlert');

    var idInput = document.getElementById('editInventoryId');
    var titleEl = document.getElementById('editInventoryTitle');
    var itemNameInput = document.getElementById('editItemName');
    var itemStatusInput = document.getElementById('editItemStatus');
    var itemQuantityInput = document.getElementById('editItemQuantity');
    var itemReorderInput = document.getElementById('editItemReorder');
    var itemUnitInput = document.getElementById('editItemUnit');
    var itemCategoryInput = document.getElementById('editItemCategory');
    var itemSupplierInput = document.getElementById('editItemSupplier');
    var itemUnitCostInput = document.getElementById('editItemUnitCost');
    var itemExpiryInput = document.getElementById('editItemExpiry');

    var sourcesList = document.getElementById('editSupplierSourcesList');
    var addSourceBtn = document.getElementById('addEditSourceRow');
    var sourceRowTemplate = document.getElementById('editSupplierSourceRowTemplate');

    var activeTrigger = null;
    var supplierOptions = [];
    var categoryOptions = [];

    var getEndpoint = '/lab_sync/index.php?controller=inventoryController&action=getInventoryEditData';
    var saveEndpoint = '/lab_sync/index.php?controller=inventoryController&action=edit_item';

    function showAlert(message, type) {
        if (!alertBox) {
            return;
        }
        alertBox.textContent = String(message || 'Unable to process request.');
        alertBox.className = 'inventory-edit-alert' + (type === 'success' ? ' success' : '') + (type === 'info' ? ' info' : '');
        alertBox.hidden = false;
    }

    function hideAlert() {
        if (!alertBox) {
            return;
        }
        alertBox.textContent = '';
        alertBox.hidden = true;
        alertBox.className = 'inventory-edit-alert';
    }

    function setModalOpen(open) {
        modal.classList.toggle('is-open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';

        if (!open) {
            if (activeTrigger) {
                activeTrigger.focus();
            }
        }
    }

    function setLoadingState(isLoading) {
        submitBtn.disabled = isLoading;
        submitBtn.textContent = isLoading ? 'Updating...' : 'Update Item';
    }

    function setSelectOptions(selectNode, options, valueKey, labelKey, placeholder) {
        if (!selectNode) {
            return;
        }

        var html = ['<option value="">' + placeholder + '</option>'];
        (options || []).forEach(function (option) {
            var value = option[valueKey] == null ? '' : String(option[valueKey]);
            var label = option[labelKey] == null ? '' : String(option[labelKey]);
            html.push('<option value="' + value.replace(/"/g, '&quot;') + '">' + label.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</option>');
        });
        selectNode.innerHTML = html.join('');
    }

    function getSourceRows() {
        if (!sourcesList) {
            return [];
        }
        return Array.prototype.slice.call(sourcesList.querySelectorAll('.inventory-edit-source-row'));
    }

    function syncPrimarySourceRadio() {
        var rows = getSourceRows();
        if (!rows.length) {
            return;
        }

        var selected = sourcesList.querySelector('.inventory-edit-source-primary:checked');
        if (!selected) {
            var first = rows[0].querySelector('.inventory-edit-source-primary');
            if (first) {
                first.checked = true;
                selected = first;
            }
        }

        if (!selected) {
            return;
        }

        var selectedRow = selected.closest('.inventory-edit-source-row');
        var supplierNode = selectedRow ? selectedRow.querySelector('.inventory-edit-source-supplier') : null;
        var unitCostNode = selectedRow ? selectedRow.querySelector('.inventory-edit-source-unit-cost') : null;

        if (supplierNode && itemSupplierInput && String(supplierNode.value || '') !== '') {
            itemSupplierInput.value = String(supplierNode.value || '');
        }
        if (unitCostNode && itemUnitCostInput) {
            itemUnitCostInput.value = String(unitCostNode.value || '');
        }
    }

    function createSourceRow(source) {
        if (!sourcesList || !sourceRowTemplate) {
            return;
        }

        var rowIndex = getSourceRows().length;
        var html = sourceRowTemplate.innerHTML.replace(/__INDEX__/g, String(rowIndex));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        var row = wrapper.firstElementChild;
        if (!row) {
            return;
        }

        var supplierNode = row.querySelector('.inventory-edit-source-supplier');
        var unitCostNode = row.querySelector('.inventory-edit-source-unit-cost');
        var minOrderNode = row.querySelector('.inventory-edit-source-min-order');
        var leadTimeNode = row.querySelector('.inventory-edit-source-lead-time');
        var primaryNode = row.querySelector('.inventory-edit-source-primary');

        setSelectOptions(supplierNode, supplierOptions, 'supplier_id', 'supplier_name', 'Select supplier');

        if (source) {
            if (supplierNode && source.supplier_id != null) {
                supplierNode.value = String(source.supplier_id);
            }
            if (unitCostNode && source.unit_cost != null && source.unit_cost !== '') {
                unitCostNode.value = String(source.unit_cost);
            }
            if (minOrderNode && source.min_order_qty != null && source.min_order_qty !== '') {
                minOrderNode.value = String(source.min_order_qty);
            }
            if (leadTimeNode && source.lead_time_days != null && source.lead_time_days !== '') {
                leadTimeNode.value = String(source.lead_time_days);
            }
            if (primaryNode && Number(source.is_primary || 0) === 1) {
                primaryNode.checked = true;
            }
        }

        sourcesList.appendChild(row);
        syncPrimarySourceRadio();
    }

    function clearSourceRows() {
        if (!sourcesList) {
            return;
        }
        sourcesList.innerHTML = '';
    }

    function populateForm(data) {
        var inventory = data.inventory || {};
        var suppliers = Array.isArray(data.suppliers) ? data.suppliers : [];

        supplierOptions = Array.isArray(data.all_suppliers) ? data.all_suppliers : [];
        categoryOptions = Array.isArray(data.categories) ? data.categories : [];

        idInput.value = inventory.inventory_id || '';
        titleEl.textContent = 'Edit Inventory Item: #INV-' + String(inventory.inventory_id || '').padStart(4, '0');

        itemNameInput.value = inventory.item_name || '';
        itemStatusInput.value = inventory.status || 'In Stock';
        itemQuantityInput.value = inventory.quantity == null ? '' : inventory.quantity;
        itemReorderInput.value = inventory.reorder_level == null ? '' : inventory.reorder_level;
        itemUnitInput.value = inventory.unit_of_measure || 'Units';
        itemUnitCostInput.value = inventory.unit_cost == null ? '' : inventory.unit_cost;
        itemExpiryInput.value = inventory.expiry_date || '';

        setSelectOptions(itemCategoryInput, categoryOptions, 'category_id', 'category_name', 'Select category');
        itemCategoryInput.value = inventory.category_id == null ? '' : String(inventory.category_id);

        setSelectOptions(itemSupplierInput, supplierOptions, 'supplier_id', 'supplier_name', 'Select supplier');
        itemSupplierInput.value = inventory.supplier_id == null ? '' : String(inventory.supplier_id);

        clearSourceRows();
        if (suppliers.length === 0) {
            createSourceRow({
                supplier_id: inventory.supplier_id || '',
                unit_cost: inventory.unit_cost || '',
                min_order_qty: '',
                lead_time_days: '',
                is_primary: 1
            });
        } else {
            suppliers.forEach(function (source) {
                createSourceRow(source);
            });
        }

        if (sourcesList && !sourcesList.querySelector('.inventory-edit-source-primary:checked')) {
            var firstPrimary = sourcesList.querySelector('.inventory-edit-source-primary');
            if (firstPrimary) {
                firstPrimary.checked = true;
            }
        }

        syncPrimarySourceRadio();
        hideAlert();
    }

    function fetchEditData(inventoryId) {
        setModalOpen(true);
        hideAlert();
        showAlert('Loading inventory data...', 'info');

        fetch(getEndpoint + '&inventory_id=' + encodeURIComponent(inventoryId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return null;
                }).then(function (payload) {
                    if (!response.ok || !payload || payload.status !== 'success' || !payload.data) {
                        throw new Error((payload && payload.message) || 'Unable to load inventory data.');
                    }
                    return payload.data;
                });
            })
            .then(function (data) {
                populateForm(data);
                itemNameInput.focus();
            })
            .catch(function (error) {
                showAlert(error.message || 'Unable to load inventory data.');
            });
    }

    function collectSourcePayload() {
        var rows = getSourceRows();
        var seenSupplierIds = {};
        var payload = [];
        var hasPrimary = false;

        for (var i = 0; i < rows.length; i += 1) {
            var row = rows[i];
            var supplierNode = row.querySelector('.inventory-edit-source-supplier');
            var unitCostNode = row.querySelector('.inventory-edit-source-unit-cost');
            var minOrderNode = row.querySelector('.inventory-edit-source-min-order');
            var leadTimeNode = row.querySelector('.inventory-edit-source-lead-time');
            var primaryNode = row.querySelector('.inventory-edit-source-primary');

            var supplierId = String((supplierNode && supplierNode.value) || '').trim();
            var unitCost = String((unitCostNode && unitCostNode.value) || '').trim();
            var minOrder = String((minOrderNode && minOrderNode.value) || '').trim();
            var leadTime = String((leadTimeNode && leadTimeNode.value) || '').trim();
            var isPrimary = !!(primaryNode && primaryNode.checked);

            if (supplierId === '' && unitCost === '' && minOrder === '' && leadTime === '') {
                continue;
            }

            if (supplierId === '') {
                throw new Error('Each supplier source row must include a supplier.');
            }

            if (seenSupplierIds[supplierId]) {
                throw new Error('Duplicate supplier source detected. Each supplier can only appear once.');
            }
            seenSupplierIds[supplierId] = true;

            if (unitCost !== '' && isNaN(Number(unitCost))) {
                throw new Error('Supplier source unit cost must be a valid number.');
            }
            if (minOrder !== '' && (isNaN(Number(minOrder)) || Number(minOrder) < 0)) {
                throw new Error('Min order quantity must be zero or greater.');
            }
            if (leadTime !== '' && (isNaN(Number(leadTime)) || Number(leadTime) < 0)) {
                throw new Error('Lead time must be zero or greater.');
            }

            if (isPrimary) {
                hasPrimary = true;
            }

            payload.push({
                supplier_id: Number(supplierId),
                unit_cost: unitCost === '' ? null : Number(unitCost),
                min_order_qty: minOrder === '' ? null : Number(minOrder),
                lead_time_days: leadTime === '' ? null : Number(leadTime),
                is_primary: isPrimary ? 1 : 0
            });
        }

        if (payload.length > 0 && !hasPrimary) {
            payload[0].is_primary = 1;
        }

        return payload;
    }

    function validateForm() {
        if (!idInput.value) {
            throw new Error('Invalid inventory item selected.');
        }
        if (!String(itemNameInput.value || '').trim()) {
            throw new Error('Item name is required.');
        }
        if (String(itemQuantityInput.value || '').trim() === '' || Number(itemQuantityInput.value) < 0) {
            throw new Error('Quantity must be zero or greater.');
        }
        if (String(itemReorderInput.value || '').trim() === '' || Number(itemReorderInput.value) < 0) {
            throw new Error('Reorder level must be zero or greater.');
        }
        if (String(itemUnitInput.value || '').trim() === '') {
            throw new Error('Unit of measure is required.');
        }

        var expiry = String(itemExpiryInput.value || '').trim();
        if (expiry !== '' && !/^\d{4}-\d{2}-\d{2}$/.test(expiry)) {
            throw new Error('Expiry date must be in YYYY-MM-DD format.');
        }
    }

    document.addEventListener('click', function (event) {
        var editButton = event.target.closest('.js-edit-inventory-btn');
        if (editButton) {
            event.preventDefault();
            var inventoryId = editButton.getAttribute('data-inventory-id');
            if (!inventoryId) {
                return;
            }
            activeTrigger = editButton;
            fetchEditData(inventoryId);
            return;
        }

        if (event.target === modal) {
            setModalOpen(false);
        }
    });

    closeBtn && closeBtn.addEventListener('click', function () {
        setModalOpen(false);
    });

    cancelBtn && cancelBtn.addEventListener('click', function () {
        setModalOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            setModalOpen(false);
        }
    });

    addSourceBtn && addSourceBtn.addEventListener('click', function () {
        createSourceRow({ is_primary: 0 });
    });

    sourcesList && sourcesList.addEventListener('click', function (event) {
        var removeBtn = event.target.closest('.inventory-edit-remove-source-btn');
        if (!removeBtn) {
            return;
        }

        var row = removeBtn.closest('.inventory-edit-source-row');
        if (!row) {
            return;
        }

        row.remove();
        if (getSourceRows().length === 0) {
            createSourceRow({ is_primary: 1 });
        }
        syncPrimarySourceRadio();
    });

    sourcesList && sourcesList.addEventListener('change', function (event) {
        if (event.target.classList.contains('inventory-edit-source-primary')) {
            syncPrimarySourceRadio();
            return;
        }

        if (event.target.classList.contains('inventory-edit-source-supplier')) {
            var row = event.target.closest('.inventory-edit-source-row');
            var primaryNode = row ? row.querySelector('.inventory-edit-source-primary') : null;
            if (primaryNode && primaryNode.checked && itemSupplierInput) {
                itemSupplierInput.value = String(event.target.value || '');
            }
            return;
        }

        if (event.target.classList.contains('inventory-edit-source-unit-cost')) {
            var parentRow = event.target.closest('.inventory-edit-source-row');
            var parentPrimary = parentRow ? parentRow.querySelector('.inventory-edit-source-primary') : null;
            if (parentPrimary && parentPrimary.checked && itemUnitCostInput) {
                itemUnitCostInput.value = String(event.target.value || '');
            }
        }
    });

    itemSupplierInput && itemSupplierInput.addEventListener('change', function () {
        var selectedSupplierId = String(itemSupplierInput.value || '');
        if (selectedSupplierId === '') {
            return;
        }

        var rows = getSourceRows();
        var matched = null;
        rows.forEach(function (row) {
            var supplierNode = row.querySelector('.inventory-edit-source-supplier');
            if (supplierNode && String(supplierNode.value || '') === selectedSupplierId) {
                matched = row;
            }
        });

        if (!matched) {
            createSourceRow({ supplier_id: Number(selectedSupplierId), unit_cost: itemUnitCostInput.value || '', is_primary: 0 });
            rows = getSourceRows();
            rows.forEach(function (row) {
                var supplierNode = row.querySelector('.inventory-edit-source-supplier');
                if (supplierNode && String(supplierNode.value || '') === selectedSupplierId) {
                    matched = row;
                }
            });
        }

        if (matched) {
            var primaryNode = matched.querySelector('.inventory-edit-source-primary');
            if (primaryNode) {
                primaryNode.checked = true;
            }
            syncPrimarySourceRadio();
        }
    });

    itemUnitCostInput && itemUnitCostInput.addEventListener('change', function () {
        var rows = getSourceRows();
        rows.forEach(function (row) {
            var primaryNode = row.querySelector('.inventory-edit-source-primary');
            if (primaryNode && primaryNode.checked) {
                var unitCostNode = row.querySelector('.inventory-edit-source-unit-cost');
                if (unitCostNode) {
                    unitCostNode.value = String(itemUnitCostInput.value || '');
                }
            }
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        hideAlert();

        try {
            validateForm();
            var supplierSources = collectSourcePayload();

            var payload = {
                inventory_id: Number(idInput.value),
                item_name: String(itemNameInput.value || '').trim(),
                quantity: Number(itemQuantityInput.value || 0),
                reorder_level: Number(itemReorderInput.value || 0),
                status: String(itemStatusInput.value || 'In Stock').trim(),
                unit_of_measure: String(itemUnitInput.value || 'Units').trim(),
                category_id: String(itemCategoryInput.value || '').trim(),
                supplier_id: String(itemSupplierInput.value || '').trim(),
                unit_cost: String(itemUnitCostInput.value || '').trim(),
                expiry_date: String(itemExpiryInput.value || '').trim(),
                supplier_sources: supplierSources
            };

            setLoadingState(true);

            fetch(saveEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        return null;
                    }).then(function (result) {
                        if (!response.ok || !result || result.status !== 'success') {
                            throw new Error((result && result.message) || 'Failed to update inventory item.');
                        }
                        return result;
                    });
                })
                .then(function (result) {
                    showAlert(result.message || 'Inventory item updated successfully.', 'success');
                    window.setTimeout(function () {
                        setModalOpen(false);
                        window.location.reload();
                    }, 700);
                })
                .catch(function (error) {
                    showAlert(error.message || 'Failed to update inventory item.');
                })
                .finally(function () {
                    setLoadingState(false);
                });
        } catch (error) {
            showAlert(error.message || 'Please check your inputs.');
        }
    });
});
