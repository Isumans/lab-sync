(function () {
    var deleteButtons = document.querySelectorAll('.action-btn-delete');
    var deleteForm = document.getElementById('deleteSupplierForm');
    var deleteSupplierIdInput = document.getElementById('delete_supplier_id');
    var deleteModal = document.getElementById('deleteSupplierModal');
    var deleteModalCloseBtn = document.getElementById('closeDeleteSupplierModal');
    var deleteModalCancelBtn = document.getElementById('cancelDeleteSupplier');
    var deleteModalConfirmBtn = document.getElementById('confirmDeleteSupplier');
    var deleteSupplierIdText = document.getElementById('deleteSupplierIdText');
    var deleteSupplierNameText = document.getElementById('deleteSupplierNameText');

    if (!deleteButtons.length || !deleteForm || !deleteSupplierIdInput || !deleteModal) {
        return;
    }

    function openDeleteModal(supplierId, supplierName) {
        deleteSupplierIdInput.value = supplierId;

        if (deleteSupplierIdText) {
            deleteSupplierIdText.textContent = '#' + supplierId;
        }

        if (deleteSupplierNameText) {
            deleteSupplierNameText.textContent = supplierName || 'Unknown supplier';
        }

        deleteModal.classList.add('show');
        deleteModal.setAttribute('aria-hidden', 'false');
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('show');
        deleteModal.setAttribute('aria-hidden', 'true');
    }

    deleteButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var supplierName = btn.getAttribute('data-supplier-name') || 'this supplier';
            var supplierId = btn.getAttribute('data-supplier-id') || '';

            if (!supplierId) {
                return;
            }

            openDeleteModal(supplierId, supplierName);
        });
    });

    if (deleteModalConfirmBtn) {
        deleteModalConfirmBtn.addEventListener('click', function () {
            if (deleteSupplierIdInput.value === '') {
                return;
            }

            deleteForm.submit();
        });
    }

    if (deleteModalCloseBtn) {
        deleteModalCloseBtn.addEventListener('click', closeDeleteModal);
    }

    if (deleteModalCancelBtn) {
        deleteModalCancelBtn.addEventListener('click', closeDeleteModal);
    }

    deleteModal.addEventListener('click', function (event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && deleteModal.classList.contains('show')) {
            closeDeleteModal();
        }
    });
})();
