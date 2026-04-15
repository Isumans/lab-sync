document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteInventoryModal');
    var closeBtn = document.getElementById('deleteInventoryClose');
    var cancelBtn = document.getElementById('deleteInventoryCancel');
    var confirmBtn = document.getElementById('deleteInventoryConfirm');
    var inventoryNumberEl = document.getElementById('deleteInventoryNumber');
    var itemNameEl = document.getElementById('deleteInventoryItemName');
    var alertEl = document.getElementById('deleteInventoryAlert');

    if (!modal || !confirmBtn) {
        return;
    }

    var deleteEndpoint = '/lab_sync/index.php?controller=inventoryController&action=edit_item';

    var activeTrigger = null;
    var selectedInventoryId = null;

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

        selectedInventoryId = null;
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Delete Item';
        hideAlert();

        if (activeTrigger) {
            activeTrigger.focus();
        }
    }

    function setModalData(inventoryId, itemName) {
        selectedInventoryId = Number(inventoryId);
        inventoryNumberEl.textContent = 'INV-' + String(inventoryId || '').padStart(4, '0');
        itemNameEl.textContent = itemName && itemName.trim() ? itemName : 'Unknown Item';
    }

    function sendDeleteRequest() {
        if (!selectedInventoryId || Number.isNaN(selectedInventoryId)) {
            showAlert('Invalid inventory ID.');
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
                delete: true,
                inventory_id: selectedInventoryId
            })
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return null;
                }).then(function (payload) {
                    if (!response.ok || !payload || payload.status !== 'success') {
                        throw new Error((payload && payload.message) || 'Unable to delete inventory item.');
                    }

                    return payload;
                });
            })
            .then(function () {
                closeModal();
                window.location.reload();
            })
            .catch(function (error) {
                showAlert(error.message || 'Unable to delete inventory item.');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Delete Item';
            });
    }

    document.addEventListener('click', function (event) {
        var deleteBtn = event.target.closest('.js-delete-inventory-btn');
        if (deleteBtn) {
            event.preventDefault();
            activeTrigger = deleteBtn;

            var inventoryId = deleteBtn.getAttribute('data-inventory-id');
            var itemName = deleteBtn.getAttribute('data-item-name') || '';

            if (!inventoryId) {
                return;
            }

            setModalData(inventoryId, itemName);
            openModal();
            return;
        }

        if (event.target === modal) {
            closeModal();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    confirmBtn.addEventListener('click', sendDeleteRequest);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
