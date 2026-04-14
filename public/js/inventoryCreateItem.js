(function () {
    var supplierSearchInput = null;
    var supplierSelect = null;
    var quantityInput = null;
    var unitCostInput = null;
    var itemNameInput = null;
    var categoryInput = null;
    var unitInput = null;
    var reorderInput = null;

    var summaryIntakeValue = null;
    var summaryCompliance = null;

    var supplierModal = null;
    var openSupplierModalBtn = null;
    var closeSupplierModalBtn = null;
    var cancelSupplierModalBtn = null;
    var createSupplierForm = null;
    var supplierMessage = null;

    var searchTimer = null;

    function queryNodes() {
        supplierSearchInput = document.getElementById('supplierSearch');
        supplierSelect = document.getElementById('supplier_id');
        quantityInput = document.getElementById('quantity');
        unitCostInput = document.getElementById('unit_cost');
        itemNameInput = document.getElementById('item_name');
        categoryInput = document.getElementById('category_id');
        unitInput = document.getElementById('unit_of_measure');
        reorderInput = document.getElementById('reorder_level');

        summaryIntakeValue = document.getElementById('summaryIntakeValue');
        summaryCompliance = document.getElementById('summaryCompliance');

        supplierModal = document.getElementById('createSupplierModal');
        openSupplierModalBtn = document.getElementById('openSupplierModal');
        closeSupplierModalBtn = document.getElementById('closeSupplierModal');
        cancelSupplierModalBtn = document.getElementById('cancelSupplierModal');
        createSupplierForm = document.getElementById('createSupplierForm');
        supplierMessage = document.getElementById('supplierFormMessage');
    }

    function safeCurrency(value) {
        var parsed = Number(value);
        if (!isFinite(parsed) || parsed < 0) {
            parsed = 0;
        }

        return parsed.toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD'
        });
    }

    function setComplianceState() {
        if (!summaryCompliance) {
            return;
        }

        var hasItemName = itemNameInput && String(itemNameInput.value || '').trim() !== '';
        var hasCategory = categoryInput && String(categoryInput.value || '').trim() !== '';
        var hasUnit = unitInput && String(unitInput.value || '').trim() !== '';
        var hasQuantity = quantityInput && Number(quantityInput.value || 0) >= 0;
        var hasReorder = reorderInput && Number(reorderInput.value || 0) >= 0;
        var hasUnitCost = unitCostInput && String(unitCostInput.value || '').trim() !== '';

        var complete = !!(hasItemName && hasCategory && hasUnit && hasQuantity && hasReorder && hasUnitCost);

        summaryCompliance.textContent = complete ? 'Ready' : 'Incomplete';
        summaryCompliance.classList.toggle('is-complete', complete);
        summaryCompliance.classList.toggle('is-incomplete', !complete);
    }

    function refreshSummary() {
        if (summaryIntakeValue && quantityInput && unitCostInput) {
            var quantity = Number(quantityInput.value || 0);
            var unitCost = Number(unitCostInput.value || 0);
            summaryIntakeValue.textContent = safeCurrency(quantity * unitCost);
        }

        setComplianceState();
    }

    function endpoint(action) {
        return '/lab_sync/index.php?controller=inventoryController&action=' + encodeURIComponent(action);
    }

    function setSupplierMessage(message, isError) {
        if (!supplierMessage) {
            return;
        }

        supplierMessage.hidden = false;
        supplierMessage.textContent = message;
        supplierMessage.classList.remove('is-success', 'is-error');
        supplierMessage.classList.add(isError ? 'is-error' : 'is-success');
    }

    function clearSupplierMessage() {
        if (!supplierMessage) {
            return;
        }

        supplierMessage.hidden = true;
        supplierMessage.textContent = '';
        supplierMessage.classList.remove('is-success', 'is-error');
    }

    function renderSupplierOptions(suppliers, selectedId) {
        if (!supplierSelect) {
            return;
        }

        var currentSelected = selectedId || supplierSelect.value;
        var options = ['<option value="">Select supplier (optional)</option>'];

        (suppliers || []).forEach(function (supplier) {
            var id = String(supplier.supplier_id || '');
            var selected = id !== '' && String(currentSelected) === id ? ' selected' : '';
            var label = String(supplier.supplier_name || 'Supplier');
            options.push('<option value="' + id + '"' + selected + '>' + label + '</option>');
        });

        supplierSelect.innerHTML = options.join('');
    }

    function fetchSuppliers(query, preferredId) {
        var url = endpoint('searchSuppliers') + '&q=' + encodeURIComponent(query || '') + '&limit=50';

        return fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Could not load suppliers.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'success') {
                    throw new Error((payload && payload.message) || 'Could not load suppliers.');
                }

                renderSupplierOptions(payload.data || [], preferredId || '');
            })
            .catch(function () {
                // Keep the existing list when lookup fails.
            });
    }

    function openSupplierModal() {
        if (!supplierModal) {
            return;
        }

        supplierModal.classList.add('is-open');
        supplierModal.setAttribute('aria-hidden', 'false');
        clearSupplierMessage();

        var firstInput = document.getElementById('supplier_name');
        if (firstInput) {
            firstInput.focus();
        }
    }

    function closeSupplierModal() {
        if (!supplierModal) {
            return;
        }

        supplierModal.classList.remove('is-open');
        supplierModal.setAttribute('aria-hidden', 'true');
        clearSupplierMessage();

        if (createSupplierForm) {
            createSupplierForm.reset();
        }
    }

    function handleSupplierCreate(event) {
        event.preventDefault();
        if (!createSupplierForm) {
            return;
        }

        clearSupplierMessage();

        var formData = new FormData(createSupplierForm);
        var supplierName = String(formData.get('supplier_name') || '').trim();

        if (supplierName === '') {
            setSupplierMessage('Supplier name is required.', true);
            return;
        }

        fetch(endpoint('createSupplier'), {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok || !payload || payload.status !== 'success') {
                        throw new Error((payload && payload.message) || 'Unable to create supplier.');
                    }
                    return payload;
                });
            })
            .then(function (payload) {
                var newSupplier = payload.data || {};
                setSupplierMessage('Supplier created and selected.', false);

                fetchSuppliers('', String(newSupplier.supplier_id || ''));

                window.setTimeout(function () {
                    closeSupplierModal();
                }, 600);
            })
            .catch(function (error) {
                setSupplierMessage(error.message || 'Unable to create supplier.', true);
            });
    }

    function wireEvents() {
        [quantityInput, unitCostInput, itemNameInput, categoryInput, unitInput, reorderInput].forEach(function (node) {
            if (!node) {
                return;
            }
            node.addEventListener('input', refreshSummary);
            node.addEventListener('change', refreshSummary);
        });

        if (supplierSearchInput) {
            supplierSearchInput.addEventListener('input', function () {
                window.clearTimeout(searchTimer);
                searchTimer = window.setTimeout(function () {
                    fetchSuppliers(String(supplierSearchInput.value || '').trim());
                }, 250);
            });
        }

        if (openSupplierModalBtn) {
            openSupplierModalBtn.addEventListener('click', openSupplierModal);
        }

        [closeSupplierModalBtn, cancelSupplierModalBtn].forEach(function (node) {
            if (node) {
                node.addEventListener('click', closeSupplierModal);
            }
        });

        if (supplierModal) {
            supplierModal.addEventListener('click', function (event) {
                if (event.target === supplierModal) {
                    closeSupplierModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && supplierModal && supplierModal.classList.contains('is-open')) {
                closeSupplierModal();
            }
        });

        if (createSupplierForm) {
            createSupplierForm.addEventListener('submit', handleSupplierCreate);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        queryNodes();
        wireEvents();
        refreshSummary();
    });
})();
