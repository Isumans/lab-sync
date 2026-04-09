(function () {
    var itemSearchInput = document.getElementById('supplier-item-search');
    var resultsContainer = document.getElementById('supplier-item-results');
    var selectedContainer = document.getElementById('supplier-selected-items');
    var hiddenInputsContainer = document.getElementById('supplier-item-hidden-inputs');
    var emptyState = document.getElementById('supplier-item-empty');
    var newItemBtn = document.getElementById('supplier-new-item-btn');
    var newItemModal = document.getElementById('supplier-new-item-modal');
    var newItemForm = document.getElementById('supplier-new-item-form');
    var newItemCloseBtn = document.getElementById('supplier-new-item-close');
    var newItemCancelBtn = document.getElementById('supplier-new-item-cancel');
    var newItemNameInput = document.getElementById('supplier-new-item-name');
    var newItemCategoryInput = document.getElementById('supplier-new-item-category');

    if (!itemSearchInput || !resultsContainer || !selectedContainer || !hiddenInputsContainer || !emptyState) {
        return;
    }

    var availableItems = [
        { name: 'Precision Pipette Tips 200uL', category: 'Consumables' },
        { name: 'Reagent Grade Ethanol 99%', category: 'Chemicals' },
        { name: 'Glucose Test Kit', category: 'Diagnostics' },
        { name: 'Blood Collection Tubes', category: 'Consumables' },
        { name: 'Urine Sample Bottles', category: 'Containers' },
        { name: 'Microscope Slides', category: 'Glassware' },
        { name: 'COVID-19 Rapid Test Kit', category: 'Diagnostics' },
        { name: 'Latex Gloves', category: 'Safety' },
        { name: 'Syringes', category: 'Consumables' },
        { name: 'Needles', category: 'Consumables' },
        { name: 'Alcohol Swabs', category: 'Consumables' },
        { name: 'Cotton Wool', category: 'Consumables' },
        { name: 'Pipettes', category: 'Equipment' },
        { name: 'Micropipettes', category: 'Equipment' },
        { name: 'Test Tube Racks', category: 'Equipment' },
        { name: 'Centrifuge Tubes', category: 'Consumables' },
        { name: 'Reagent Bottles', category: 'Chemicals' },
        { name: 'Distilled Water', category: 'Chemicals' },
        { name: 'Buffer Solution', category: 'Chemicals' },
        { name: 'Petri Dishes', category: 'Glassware' },
        { name: 'Culture Media', category: 'Chemicals' },
        { name: 'Specimen Containers', category: 'Containers' },
        { name: 'Face Masks', category: 'Safety' },
        { name: 'Lab Coats', category: 'Safety' },
        { name: 'Disinfectant Solution', category: 'Chemicals' },
        { name: 'Hand Sanitizer', category: 'Safety' },
        { name: 'Sample Collection Bags', category: 'Containers' }
    ].map(function (item, index) {
        return {
            id: 'ITM' + String(index + 1).padStart(4, '0'),
            name: item.name,
            category: item.category
        };
    });

    var nextItemId = availableItems.length + 1;

    var selectedItems = [];

    function normalize(value) {
        return value.toLowerCase().trim();
    }

    function openNewItemModal() {
        if (!newItemModal) {
            return;
        }
        newItemModal.classList.add('show');
        newItemModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (newItemNameInput) {
            newItemNameInput.focus();
        }
    }

    function closeNewItemModal() {
        if (!newItemModal) {
            return;
        }
        newItemModal.classList.remove('show');
        newItemModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (newItemForm) {
            newItemForm.reset();
        }
    }

    function findItemByName(name) {
        return availableItems.find(function (item) {
            return item.name === name;
        }) || null;
    }

    function renderResults(items) {
        resultsContainer.innerHTML = '';

        if (!items.length) {
            return;
        }

        items.forEach(function (item) {
            var row = document.createElement('div');
            row.className = 'supplier-item-result';

            var title = document.createElement('span');
            title.textContent = item.name;

            var addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'supplier-item-add';
            addBtn.textContent = selectedItems.indexOf(item.name) >= 0 ? 'Added' : 'Add';
            addBtn.disabled = selectedItems.indexOf(item.name) >= 0;

            addBtn.addEventListener('click', function () {
                addItem(item.name);
                var query = itemSearchInput.value;
                filterItems(query);
            });

            row.appendChild(title);
            row.appendChild(addBtn);
            resultsContainer.appendChild(row);
        });
    }

    function renderSelectedItems() {
        selectedContainer.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';

        if (!selectedItems.length) {
            emptyState.style.display = 'block';
            emptyState.textContent = 'Start typing to search for more items.';
            return;
        }

        emptyState.style.display = 'block';
        emptyState.textContent = 'Start typing to search for more items.';

        selectedItems.forEach(function (item) {
            var itemInfo = findItemByName(item) || { id: '-', name: item, category: 'General' };
            var selectedRow = document.createElement('div');
            selectedRow.className = 'supplier-register-item';

            var itemId = document.createElement('span');
            itemId.className = 'supplier-register-item-id';
            itemId.textContent = itemInfo.id;

            var itemName = document.createElement('span');
            itemName.className = 'supplier-register-item-name';
            itemName.textContent = itemInfo.name;

            var itemCategory = document.createElement('span');
            itemCategory.className = 'supplier-register-item-category';
            itemCategory.textContent = itemInfo.category;

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'supplier-item-remove';
            removeBtn.textContent = 'Delete';
            removeBtn.addEventListener('click', function () {
                removeItem(item);
                filterItems(itemSearchInput.value);
            });

            selectedRow.appendChild(itemId);
            selectedRow.appendChild(itemName);
            selectedRow.appendChild(itemCategory);
            selectedRow.appendChild(removeBtn);
            selectedContainer.appendChild(selectedRow);

            var hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'supplier_items[]';
            hiddenInput.value = item;
            hiddenInputsContainer.appendChild(hiddenInput);
        });
    }

    function addItem(item) {
        if (selectedItems.indexOf(item) >= 0) {
            return;
        }
        selectedItems.push(item);
        renderSelectedItems();
    }

    function removeItem(item) {
        selectedItems = selectedItems.filter(function (selected) {
            return selected !== item;
        });
        renderSelectedItems();
    }

    function filterItems(query) {
        var normalizedQuery = normalize(query);

        if (!normalizedQuery) {
            resultsContainer.innerHTML = '';
            return;
        }

        var matches = availableItems.filter(function (item) {
            return normalize(item.name).indexOf(normalizedQuery) !== -1;
        });

        renderResults(matches);
    }

    itemSearchInput.addEventListener('input', function (event) {
        filterItems(event.target.value);
    });

    if (newItemBtn) {
        newItemBtn.addEventListener('click', function () {
            openNewItemModal();
        });
    }

    if (newItemCloseBtn) {
        newItemCloseBtn.addEventListener('click', function () {
            closeNewItemModal();
        });
    }

    if (newItemCancelBtn) {
        newItemCancelBtn.addEventListener('click', function () {
            closeNewItemModal();
        });
    }

    if (newItemModal) {
        newItemModal.addEventListener('click', function (event) {
            if (event.target === newItemModal) {
                closeNewItemModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && newItemModal && newItemModal.classList.contains('show')) {
            closeNewItemModal();
        }
    });

    if (newItemForm) {
        newItemForm.addEventListener('submit', function (event) {
            event.preventDefault();

            var itemName = newItemNameInput ? newItemNameInput.value.trim() : '';
            var category = newItemCategoryInput ? newItemCategoryInput.value : 'General';

            if (!itemName) {
                return;
            }

            var existingItem = findItemByName(itemName);
            if (!existingItem) {
                availableItems.unshift({
                    id: 'ITM' + String(nextItemId).padStart(4, '0'),
                    name: itemName,
                    category: category || 'General'
                });
                nextItemId += 1;
            }

            addItem(itemName);
            itemSearchInput.value = itemName;
            filterItems(itemName);
            closeNewItemModal();
        });
    }

    renderSelectedItems();
})();
