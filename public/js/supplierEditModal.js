document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('editSupplierModal');
    var form = document.getElementById('editSupplierForm');
    if (!modal || !form) {
        return;
    }

    var closeBtn = document.getElementById('editSupplierClose');
    var cancelBtn = document.getElementById('editSupplierCancel');
    var submitBtn = document.getElementById('editSupplierSubmit');
    var alertBox = document.getElementById('editSupplierAlert');

    var idInput = document.getElementById('editSupplierId');
    var nameInput = document.getElementById('editSupplierName');
    var contactInput = document.getElementById('editSupplierContact');
    var locationInput = document.getElementById('editSupplierLocation');
    var emailInput = document.getElementById('editSupplierEmail');

    var getEndpoint = '/lab_sync/index.php?controller=inventoryController&action=getSupplierEditData';
    var updateEndpoint = '/lab_sync/index.php?controller=inventoryController&action=updateSupplier';

    var activeTrigger = null;

    function showAlert(message, type) {
        if (!alertBox) {
            return;
        }
        alertBox.textContent = message;
        alertBox.className = 'supplier-edit-alert' + (type === 'success' ? ' success' : '');
        alertBox.hidden = false;
    }

    function hideAlert() {
        if (!alertBox) {
            return;
        }
        alertBox.hidden = true;
        alertBox.textContent = '';
        alertBox.className = 'supplier-edit-alert';
    }

    function setModalOpen(open) {
        modal.classList.toggle('is-open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';

        if (!open && activeTrigger) {
            activeTrigger.focus();
        }
    }

    function setSubmitLoading(isLoading) {
        submitBtn.disabled = isLoading;
        submitBtn.textContent = isLoading ? 'Updating...' : 'Update Supplier';
    }

    function populateModal(data) {
        idInput.value = data.supplier_id || '';
        nameInput.value = data.supplier_name || '';
        contactInput.value = data.contact_no || '';
        locationInput.value = data.location || '';
        emailInput.value = data.email || '';
    }

    function requestSupplierData(supplierId) {
        return fetch(getEndpoint + '&supplier_id=' + encodeURIComponent(supplierId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to load supplier data.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'success' || !payload.data) {
                    throw new Error((payload && payload.message) || 'Unable to load supplier data.');
                }
                return payload.data;
            });
    }

    function refreshSupplierTable() {
        if (window.suppliersDashboard && typeof window.suppliersDashboard.refresh === 'function') {
            window.suppliersDashboard.refresh(false);
        }
    }

    function openForSupplier(trigger) {
        activeTrigger = trigger;
        hideAlert();
        setModalOpen(true);

        var supplierId = trigger.getAttribute('data-supplier-id') || '';
        populateModal({
            supplier_id: supplierId,
            supplier_name: trigger.getAttribute('data-supplier-name') || '',
            contact_no: trigger.getAttribute('data-contact-no') || '',
            location: trigger.getAttribute('data-location') || '',
            email: trigger.getAttribute('data-email') || ''
        });

        if (!supplierId) {
            showAlert('Invalid supplier selected.', 'error');
            return;
        }

        requestSupplierData(supplierId)
            .then(function (data) {
                populateModal(data);
                nameInput.focus();
            })
            .catch(function (error) {
                showAlert(error.message || 'Unable to load supplier data.', 'error');
            });
    }

    function closeModal() {
        setModalOpen(false);
        hideAlert();
        form.reset();
        idInput.value = '';
        setSubmitLoading(false);
    }

    function handleSubmit(event) {
        event.preventDefault();
        hideAlert();

        var supplierId = Number(idInput.value || 0);
        var supplierName = String(nameInput.value || '').trim();
        var contactNo = String(contactInput.value || '').trim();
        var location = String(locationInput.value || '').trim();
        var email = String(emailInput.value || '').trim();

        if (!supplierId) {
            showAlert('Invalid supplier selected.', 'error');
            return;
        }

        if (!supplierName) {
            showAlert('Supplier name is required.', 'error');
            return;
        }

        setSubmitLoading(true);

        fetch(updateEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                supplier_id: supplierId,
                supplier_name: supplierName,
                contact_no: contactNo,
                location: location,
                email: email
            })
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return null;
                }).then(function (payload) {
                    if (!response.ok || !payload || payload.status !== 'success') {
                        throw new Error((payload && payload.message) || 'Unable to update supplier.');
                    }
                    return payload;
                });
            })
            .then(function (payload) {
                showAlert(payload.message || 'Supplier updated successfully.', 'success');
                refreshSupplierTable();
                window.setTimeout(closeModal, 500);
            })
            .catch(function (error) {
                showAlert(error.message || 'Unable to update supplier.', 'error');
            })
            .finally(function () {
                setSubmitLoading(false);
            });
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.js-edit-supplier-btn');
        if (trigger) {
            event.preventDefault();
            openForSupplier(trigger);
            return;
        }

        if (event.target === modal) {
            closeModal();
        }
    });

    closeBtn && closeBtn.addEventListener('click', closeModal);
    cancelBtn && cancelBtn.addEventListener('click', closeModal);
    form.addEventListener('submit', handleSubmit);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
