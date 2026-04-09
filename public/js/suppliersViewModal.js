(function () {
    var viewButtons = document.querySelectorAll('.action-btn-view');
    var viewModal = document.getElementById('viewSupplierModal');
    var closeTopBtn = document.getElementById('closeViewSupplierModal');
    var closeBottomBtn = document.getElementById('closeViewSupplierModalBtn');
    var supplierIdEl = document.getElementById('viewSupplierId');
    var supplierNameEl = document.getElementById('viewSupplierName');
    var itemsListEl = document.getElementById('viewSupplierItemsList');
    var itemsEmptyEl = document.getElementById('viewSupplierItemsEmpty');

    if (!viewButtons.length || !viewModal || !itemsListEl || !itemsEmptyEl) {
        return;
    }

    function splitItems(itemsRaw) {
        return String(itemsRaw || '')
            .split(',')
            .map(function (item) { return item.trim(); })
            .filter(function (item) { return item !== ''; });
    }

    function renderItems(items) {
        itemsListEl.innerHTML = '';

        if (!items.length) {
            itemsEmptyEl.style.display = 'block';
            return;
        }

        itemsEmptyEl.style.display = 'none';

        items.forEach(function (item) {
            var li = document.createElement('li');
            li.textContent = item;
            itemsListEl.appendChild(li);
        });
    }

    function openModal(supplierId, supplierName, itemsRaw) {
        if (supplierIdEl) {
            supplierIdEl.textContent = '#' + supplierId;
        }

        if (supplierNameEl) {
            supplierNameEl.textContent = supplierName || '-';
        }

        renderItems(splitItems(itemsRaw));

        viewModal.classList.add('show');
        viewModal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        viewModal.classList.remove('show');
        viewModal.setAttribute('aria-hidden', 'true');
    }

    viewButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var supplierId = btn.getAttribute('data-supplier-id') || '-';
            var supplierName = btn.getAttribute('data-supplier-name') || '-';
            var supplierItems = btn.getAttribute('data-supplier-items') || '';
            openModal(supplierId, supplierName, supplierItems);
        });
    });

    if (closeTopBtn) {
        closeTopBtn.addEventListener('click', closeModal);
    }

    if (closeBottomBtn) {
        closeBottomBtn.addEventListener('click', closeModal);
    }

    viewModal.addEventListener('click', function (event) {
        if (event.target === viewModal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && viewModal.classList.contains('show')) {
            closeModal();
        }
    });
})();
