(function () {
    var itemSearchInput = document.getElementById('supplier-item-search');
    var resultsContainer = document.getElementById('supplier-item-results');
    var selectedContainer = document.getElementById('supplier-selected-items');
    var hiddenInputsContainer = document.getElementById('supplier-item-hidden-inputs');
    var emptyState = document.getElementById('supplier-item-empty');

    if (!itemSearchInput || !resultsContainer || !selectedContainer || !hiddenInputsContainer || !emptyState) {
        return;
    }

    var availableItems = [
        'Glucose Test Kit',
        'Blood Collection Tubes',
        'Urine Sample Bottles',
        'Microscope Slides',
        'COVID-19 Rapid Test Kit',
        'Latex Gloves',
        'Syringes',
        'Needles',
        'Alcohol Swabs',
        'Cotton Wool',
        'Pipettes',
        'Micropipettes',
        'Test Tube Racks',
        'Centrifuge Tubes',
        'Reagent Bottles',
        'Distilled Water',
        'Buffer Solution',
        'Petri Dishes',
        'Culture Media',
        'Specimen Containers',
        'Face Masks',
        'Lab Coats',
        'Disinfectant Solution',
        'Hand Sanitizer',
        'Sample Collection Bags'
    ];

    var selectedItems = [];

    function normalize(value) {
        return value.toLowerCase().trim();
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
            title.textContent = item;

            var addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'supplier-item-add';
            addBtn.textContent = selectedItems.indexOf(item) >= 0 ? 'Added' : 'Add';
            addBtn.disabled = selectedItems.indexOf(item) >= 0;

            addBtn.addEventListener('click', function () {
                addItem(item);
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
            return;
        }

        emptyState.style.display = 'none';

        selectedItems.forEach(function (item) {
            var selectedRow = document.createElement('div');
            selectedRow.className = 'supplier-register-item';

            var itemName = document.createElement('span');
            itemName.className = 'supplier-register-item-name';
            itemName.textContent = item;

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'supplier-item-remove';
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', function () {
                removeItem(item);
                filterItems(itemSearchInput.value);
            });

            selectedRow.appendChild(itemName);
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
            return normalize(item).indexOf(normalizedQuery) !== -1;
        });

        renderResults(matches);
    }

    itemSearchInput.addEventListener('input', function (event) {
        filterItems(event.target.value);
    });

    renderSelectedItems();
})();
