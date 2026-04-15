document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteSupplierModal');
    var closeBtn = document.getElementById('deleteSupplierClose');
    var cancelBtn = document.getElementById('deleteSupplierCancel');
    var confirmBtn = document.getElementById('deleteSupplierConfirm');
    var supplierNumberEl = document.getElementById('deleteSupplierNumber');
    var supplierNameEl = document.getElementById('deleteSupplierName');
    var alertEl = document.getElementById('deleteSupplierAlert');

    if (!modal || !confirmBtn) {
        return;
    }

    var deleteEndpoint = '/lab_sync/index.php?controller=inventoryController&action=deleteSupplier';

    var activeTrigger = null;
    var selectedSupplierId = 0;

    function showAlert(message) {
        if (!alertEl) {
            return;
        }

        alertEl.textContent = message || 'Delete request failed.';
        alertEl.hidden = false;
    }

    function hideAlert() {
        if (!alertEl) {
            return;
        }

        alertEl.textContent = '';
        alertEl.hidden = true;
    }

    function formatSupplierNumber(id) {
        var numeric = Number(id || 0);
        return 'SUP-' + String(numeric).padStart(4, '0');
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        hideAlert();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        selectedSupplierId = 0;
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Delete Supplier';
        hideAlert();

        if (activeTrigger) {
            activeTrigger.focus();
        }
    }

    function setModalData(supplierId, supplierName) {
        selectedSupplierId = Number(supplierId || 0);
        supplierNumberEl.textContent = formatSupplierNumber(selectedSupplierId);
        supplierNameEl.textContent = supplierName && String(supplierName).trim() ? String(supplierName).trim() : 'Unknown Supplier';
    }

    function refreshSupplierTable() {
        if (window.suppliersDashboard && typeof window.suppliersDashboard.refresh === 'function') {
            window.suppliersDashboard.refresh(false);
        }
    }

    function sendDeleteRequest() {
        if (!selectedSupplierId || Number.isNaN(selectedSupplierId)) {
            showAlert('Invalid supplier ID.');
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting...';
        hideAlert();

        fetch(deleteEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                supplier_id: selectedSupplierId
            })
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return null;
                }).then(function (payload) {
                    if (!response.ok || !payload || payload.status !== 'success') {
                        throw new Error((payload && payload.message) || 'Unable to delete supplier.');
                    }
                    return payload;
                });
            })
            .then(function () {
                closeModal();
                refreshSupplierTable();
            })
            .catch(function (error) {
                showAlert(error.message || 'Unable to delete supplier.');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Delete Supplier';
            });
    }

    document.addEventListener('click', function (event) {
        var deleteBtn = event.target.closest('.js-delete-supplier-btn');
        if (deleteBtn) {
            event.preventDefault();
            activeTrigger = deleteBtn;
            setModalData(deleteBtn.getAttribute('data-supplier-id'), deleteBtn.getAttribute('data-supplier-name') || '');
            openModal();
            return;
        }

        if (event.target === modal) {
            closeModal();
        }
    });

    closeBtn && closeBtn.addEventListener('click', closeModal);
    cancelBtn && cancelBtn.addEventListener('click', closeModal);
    confirmBtn.addEventListener('click', sendDeleteRequest);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
