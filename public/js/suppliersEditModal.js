(function () {
    var modal = document.getElementById('editSupplierModal');
    var closeBtn = document.getElementById('closeSupplierModal');
    var cancelBtn = document.getElementById('cancelSupplierEdit');
    var editForm = document.getElementById('editSupplierForm');
    var editButtons = document.querySelectorAll('.edit-supplier-btn');

    if (!modal || !editForm || !editButtons.length) {
        return;
    }

    var idInput = document.getElementById('edit_supplier_id');
    var nameInput = document.getElementById('edit_supplier_name');
    var emailInput = document.getElementById('edit_supplier_email');
    var contactInput = document.getElementById('edit_supplier_contact');
    var itemsInput = document.getElementById('edit_supplier_items');
    var itemSearchInput = document.getElementById('edit_supplier_item_search');
    var itemResults = document.getElementById('edit_supplier_item_results');

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

    function normalize(value) {
        return String(value || '').toLowerCase().trim();
    }

    function getCurrentItems() {
        return (itemsInput.value || '')
            .split(',')
            .map(function (item) { return item.trim(); })
            .filter(function (item) { return item !== ''; });
    }

    function setCurrentItems(items) {
        itemsInput.value = items.join(', ');
    }

    function renderItemResults(query) {
        if (!itemResults || !itemSearchInput) {
            return;
        }

        itemResults.innerHTML = '';

        var keyword = normalize(query);
        if (!keyword) {
            return;
        }

        var selectedSet = new Set(getCurrentItems().map(normalize));
        var matches = availableItems.filter(function (item) {
            var normalizedItem = normalize(item);
            return normalizedItem.indexOf(keyword) !== -1 && !selectedSet.has(normalizedItem);
        });

        matches.forEach(function (item) {
            var row = document.createElement('div');
            row.className = 'supplier-item-result';

            var title = document.createElement('span');
            title.textContent = item;

            var addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'supplier-item-add';
            addBtn.textContent = 'Add';

            addBtn.addEventListener('click', function () {
                var items = getCurrentItems();
                var exists = items.some(function (existing) {
                    return normalize(existing) === normalize(item);
                });

                if (!exists) {
                    items.push(item);
                    setCurrentItems(items);
                }

                renderItemResults(itemSearchInput.value);
            });

            row.appendChild(title);
            row.appendChild(addBtn);
            itemResults.appendChild(row);
        });
    }

    function openModal() {
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }

    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            idInput.value = btn.getAttribute('data-supplier-id') || '';
            nameInput.value = btn.getAttribute('data-supplier-name') || '';
            emailInput.value = btn.getAttribute('data-supplier-email') || '';
            contactInput.value = btn.getAttribute('data-supplier-contact') || '';
            itemsInput.value = btn.getAttribute('data-supplier-items') || '';

            if (itemSearchInput) {
                itemSearchInput.value = '';
            }
            if (itemResults) {
                itemResults.innerHTML = '';
            }

            openModal();
        });
    });

    if (itemSearchInput) {
        itemSearchInput.addEventListener('input', function () {
            renderItemResults(itemSearchInput.value);
        });
    }

    if (itemsInput) {
        itemsInput.addEventListener('input', function () {
            if (itemSearchInput && itemSearchInput.value.trim() !== '') {
                renderItemResults(itemSearchInput.value);
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
})();
