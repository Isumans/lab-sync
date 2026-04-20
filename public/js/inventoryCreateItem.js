(function () {
    var supplierSearchInput = null;
    var inventoryCreateForm = null;
    var supplierSuggestions = null;
    var supplierSelect = null;
    var supplierSourcesList = null;
    var supplierSourceTemplate = null;
    var addSupplierSourceRowBtn = null;
    var primarySourceIndexInput = null;
    var supplierOptionHtml = '';
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
    var supplierValidationMessage = null;

    var searchTimer = null;

    function queryNodes() {
        supplierSearchInput = document.getElementById('supplierSearch');
        inventoryCreateForm = document.getElementById('inventoryCreateForm');
        supplierSuggestions = document.getElementById('supplier-suggestions');
        supplierSelect = document.getElementById('supplier_id');
        supplierSourcesList = document.getElementById('supplierSourcesList');
        supplierSourceTemplate = document.getElementById('supplierSourceRowTemplate');
        addSupplierSourceRowBtn = document.getElementById('addSupplierSourceRow');
        primarySourceIndexInput = document.getElementById('primary_source_index');
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

        if (supplierSearchInput) {
            supplierValidationMessage = document.getElementById('supplier-search-validation');
            if (!supplierValidationMessage) {
                supplierValidationMessage = document.createElement('p');
                supplierValidationMessage.id = 'supplier-search-validation';
                supplierValidationMessage.className = 'supplier-search-validation-msg';
                supplierSearchInput.parentNode.appendChild(supplierValidationMessage);
            }
        }

        if (supplierSelect) {
            supplierOptionHtml = supplierSelect.innerHTML;
        }
    }

    function safeCurrency(value) {
        var parsed = Number(value);
        if (!isFinite(parsed) || parsed < 0) {
            parsed = 0;
        }

        return 'LKR ' + parsed.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
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

    function hideSupplierSuggestions() {
        if (!supplierSuggestions) {
            return;
        }

        supplierSuggestions.innerHTML = '';
        supplierSuggestions.style.display = 'none';
    }

    function showSupplierValidationError(message) {
        if (!supplierValidationMessage) {
            return;
        }

        supplierValidationMessage.textContent = message;
        supplierValidationMessage.style.display = 'block';
        if (supplierSearchInput) {
            supplierSearchInput.classList.add('input-error');
        }
    }

    function clearSupplierValidationError() {
        if (!supplierValidationMessage) {
            return;
        }

        supplierValidationMessage.textContent = '';
        supplierValidationMessage.style.display = 'none';
        if (supplierSearchInput) {
            supplierSearchInput.classList.remove('input-error');
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function getSupplierSourceRows() {
        if (!supplierSourcesList) {
            return [];
        }

        return Array.prototype.slice.call(supplierSourcesList.querySelectorAll('.inv-supplier-source-row'));
    }

    function syncPrimarySourceIndex() {
        if (!supplierSourcesList || !primarySourceIndexInput) {
            return;
        }

        var checked = supplierSourcesList.querySelector('.inv-source-primary-radio:checked');
        if (checked) {
            primarySourceIndexInput.value = String(checked.value || '0');
            return;
        }

        var firstRadio = supplierSourcesList.querySelector('.inv-source-primary-radio');
        if (firstRadio) {
            firstRadio.checked = true;
            primarySourceIndexInput.value = String(firstRadio.value || '0');
        } else {
            primarySourceIndexInput.value = '';
        }
    }

    function refreshSourceRowIndexes() {
        var rows = getSupplierSourceRows();
        rows.forEach(function (row, index) {
            row.setAttribute('data-row-index', String(index));
            var radio = row.querySelector('.inv-source-primary-radio');
            if (radio) {
                radio.value = String(index);
            }
        });
        syncPrimarySourceIndex();
    }

    function createSupplierSourceRow(selectedSupplierId, selectedUnitCost, makePrimary) {
        if (!supplierSourcesList || !supplierSourceTemplate) {
            return;
        }

        var rowIndex = getSupplierSourceRows().length;
        var html = supplierSourceTemplate.innerHTML.replace(/__INDEX__/g, String(rowIndex));
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html;

        var row = wrapper.firstElementChild;
        if (!row) {
            return;
        }

        var selectNode = row.querySelector('.inv-source-supplier-select');
        if (selectNode && supplierOptionHtml !== '') {
            selectNode.innerHTML = supplierOptionHtml;
            selectNode.value = selectedSupplierId ? String(selectedSupplierId) : '';
        }

        var unitCostNode = row.querySelector('.inv-source-unit-cost');
        if (unitCostNode && selectedUnitCost !== undefined && selectedUnitCost !== null && selectedUnitCost !== '') {
            unitCostNode.value = String(selectedUnitCost);
        }

        var primaryRadio = row.querySelector('.inv-source-primary-radio');
        if (primaryRadio && makePrimary) {
            primaryRadio.checked = true;
        }

        supplierSourcesList.appendChild(row);
        refreshSourceRowIndexes();
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
        supplierOptionHtml = supplierSelect.innerHTML;

        var sourceRows = getSupplierSourceRows();
        sourceRows.forEach(function (row) {
            var sourceSelect = row.querySelector('.inv-source-supplier-select');
            if (!sourceSelect) {
                return;
            }

            var currentValue = String(sourceSelect.value || '');
            sourceSelect.innerHTML = supplierOptionHtml;
            sourceSelect.value = currentValue;
        });
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

    function renderSupplierSuggestions(suppliers) {
        if (!supplierSuggestions) {
            return;
        }

        if (!suppliers || suppliers.length === 0) {
            supplierSuggestions.innerHTML = '<div class="supplier-suggestion-no-result">No suppliers found matching your search.</div>';
            supplierSuggestions.style.display = 'block';
            return;
        }

        supplierSuggestions.innerHTML = suppliers.map(function (supplier) {
            var id = String(supplier.supplier_id || '');
            var name = String(supplier.supplier_name || 'Supplier');
            var contact = String(supplier.contact_no || supplier.email || supplier.location || '');

            return '' +
                '<div class="supplier-suggestion-item" data-id="' + escapeHtml(id) + '" data-name="' + escapeHtml(name) + '">' +
                '<div class="suggestion-info">' +
                '<span class="suggestion-name">' + escapeHtml(name) + '</span>' +
                '<span class="suggestion-id">' + escapeHtml(contact) + '</span>' +
                '</div>' +
                '<span class="suggestion-select-hint">Select</span>' +
                '</div>';
        }).join('');

        supplierSuggestions.style.display = 'block';
    }

    function searchSupplierSuggestions(query) {
        var url = endpoint('searchSuppliers') + '&q=' + encodeURIComponent(query || '') + '&limit=25';

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

                renderSupplierSuggestions(payload.data || []);
            })
            .catch(function () {
                if (!supplierSuggestions) {
                    return;
                }

                supplierSuggestions.innerHTML = '<div class="supplier-suggestion-no-result">Could not load suppliers. Please try again.</div>';
                supplierSuggestions.style.display = 'block';
            });
    }

    function selectSupplierFromSuggestion(id, name) {
        var selectedId = String(id || '');
        if (supplierSelect) {
            supplierSelect.value = selectedId;
        }

        var rows = getSupplierSourceRows();
        var matchedRow = null;

        rows.forEach(function (row) {
            var sourceSelect = row.querySelector('.inv-source-supplier-select');
            if (!sourceSelect) {
                return;
            }

            if (String(sourceSelect.value || '') === selectedId) {
                matchedRow = row;
            }
        });

        if (!matchedRow) {
            rows.some(function (row) {
                var sourceSelect = row.querySelector('.inv-source-supplier-select');
                if (!sourceSelect) {
                    return false;
                }
                if (String(sourceSelect.value || '') === '') {
                    sourceSelect.value = selectedId;
                    matchedRow = row;
                    return true;
                }
                return false;
            });
        }

        if (!matchedRow) {
            createSupplierSourceRow(selectedId, '', rows.length === 0);
        }

        if (supplierSearchInput) {
            supplierSearchInput.value = String(name || '');
            supplierSearchInput.classList.remove('input-error');
        }

        clearSupplierValidationError();
        hideSupplierSuggestions();
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

                if (supplierSearchInput) {
                    supplierSearchInput.value = String(newSupplier.supplier_name || '');
                }

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

                var query = String(supplierSearchInput.value || '').trim();
                clearSupplierValidationError();

                if (query.length < 3) {
                    hideSupplierSuggestions();
                    if (query.length > 0) {
                        showSupplierValidationError('Type at least 3 characters to search for a supplier.');
                    }
                    return;
                }

                if (supplierSuggestions) {
                    supplierSuggestions.innerHTML = '<div class="supplier-suggestion-loading">Searching...</div>';
                    supplierSuggestions.style.display = 'block';
                }

                searchTimer = window.setTimeout(function () {
                    searchSupplierSuggestions(query);
                }, 300);
            });
        }

        if (supplierSuggestions) {
            supplierSuggestions.addEventListener('click', function (event) {
                var item = event.target.closest('.supplier-suggestion-item');
                if (!item || !item.dataset.id) {
                    return;
                }

                selectSupplierFromSuggestion(item.dataset.id, item.dataset.name || '');
            });
        }

        if (supplierSelect) {
            supplierSelect.addEventListener('change', function () {
                var selectedOption = supplierSelect.options[supplierSelect.selectedIndex];
                if (selectedOption && supplierSearchInput) {
                    supplierSearchInput.value = selectedOption.value ? selectedOption.text : '';
                }

                if (!supplierSourcesList || !selectedOption || !selectedOption.value) {
                    return;
                }

                var selectedValue = String(selectedOption.value || '');
                var rows = getSupplierSourceRows();
                var hasValue = rows.some(function (row) {
                    var selectNode = row.querySelector('.inv-source-supplier-select');
                    return !!selectNode && String(selectNode.value || '') === selectedValue;
                });

                if (!hasValue && rows.length > 0) {
                    var firstSelect = rows[0].querySelector('.inv-source-supplier-select');
                    if (firstSelect && String(firstSelect.value || '') === '') {
                        firstSelect.value = selectedValue;
                    }
                }
            });
        }

        if (addSupplierSourceRowBtn) {
            addSupplierSourceRowBtn.addEventListener('click', function () {
                createSupplierSourceRow('', '', false);
            });
        }

        if (supplierSourcesList) {
            supplierSourcesList.addEventListener('click', function (event) {
                var removeBtn = event.target.closest('.inv-source-remove-btn');
                if (!removeBtn) {
                    return;
                }

                var row = removeBtn.closest('.inv-supplier-source-row');
                if (!row) {
                    return;
                }

                row.remove();
                if (getSupplierSourceRows().length === 0) {
                    createSupplierSourceRow('', '', true);
                }
                refreshSourceRowIndexes();
            });

            supplierSourcesList.addEventListener('change', function (event) {
                if (event.target.classList.contains('inv-source-primary-radio')) {
                    syncPrimarySourceIndex();

                    var row = event.target.closest('.inv-supplier-source-row');
                    var sourceSelect = row ? row.querySelector('.inv-source-supplier-select') : null;
                    if (sourceSelect && supplierSelect && String(sourceSelect.value || '') !== '') {
                        supplierSelect.value = String(sourceSelect.value || '');
                    }
                }
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

            if (event.key === 'Escape') {
                hideSupplierSuggestions();
            }
        });

        document.addEventListener('click', function (event) {
            if (!supplierSearchInput || !supplierSuggestions) {
                return;
            }

            if (!supplierSearchInput.contains(event.target) && !supplierSuggestions.contains(event.target)) {
                hideSupplierSuggestions();
            }
        });

        if (createSupplierForm) {
            createSupplierForm.addEventListener('submit', handleSupplierCreate);
        }

        if (inventoryCreateForm) {
            inventoryCreateForm.addEventListener('submit', function () {
                syncPrimarySourceIndex();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        queryNodes();

        if (supplierSourcesList && supplierSourceTemplate && getSupplierSourceRows().length === 0) {
            createSupplierSourceRow('', '', true);
        }

        wireEvents();
        refreshSummary();
    });
})();
