(function () {
    var form = null;
    var nameInput = null;
    var emailInput = null;
    var statusText = null;
    var messageBox = null;

    function endpoint() {
        return '/lab_sync/index.php?controller=inventoryController&action=createSupplier';
    }

    function dashboardUrl() {
        return '/lab_sync/index.php?controller=supplierController&action=index';
    }

    function setMessage(text, isError) {
        if (!messageBox) {
            return;
        }

        messageBox.hidden = false;
        messageBox.textContent = text;
        messageBox.classList.remove('is-success', 'is-error');
        messageBox.classList.add(isError ? 'is-error' : 'is-success');
    }

    function clearMessage() {
        if (!messageBox) {
            return;
        }

        messageBox.hidden = true;
        messageBox.textContent = '';
        messageBox.classList.remove('is-success', 'is-error');
    }

    function updateRequiredStatus() {
        if (!statusText || !nameInput) {
            return;
        }

        var complete = String(nameInput.value || '').trim() !== '';
        statusText.textContent = complete ? 'Ready' : 'Incomplete';
        statusText.classList.toggle('is-complete', complete);
        statusText.classList.toggle('is-incomplete', !complete);
    }

    function validateClient() {
        var supplierName = String(nameInput.value || '').trim();
        if (supplierName === '') {
            setMessage('Supplier name is required.', true);
            nameInput.focus();
            return false;
        }

        var emailValue = String(emailInput.value || '').trim();
        if (emailValue !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
            setMessage('Please provide a valid email address.', true);
            emailInput.focus();
            return false;
        }

        return true;
    }

    function handleSubmit(event) {
        event.preventDefault();
        clearMessage();

        if (!validateClient()) {
            return;
        }

        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Registering...';
        }

        var payload = new FormData(form);

        fetch(endpoint(), {
            method: 'POST',
            body: payload,
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                return response.json().then(function (json) {
                    if (!response.ok || !json || json.status !== 'success') {
                        throw new Error((json && json.message) || 'Unable to register supplier.');
                    }
                    return json;
                });
            })
            .then(function () {
                setMessage('Supplier registered successfully. Redirecting to suppliers list...', false);
                window.setTimeout(function () {
                    window.location.href = dashboardUrl();
                }, 900);
            })
            .catch(function (error) {
                setMessage(error.message || 'Unable to register supplier.', true);
            })
            .finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Register Supplier';
                }
            });
    }

    function init() {
        form = document.getElementById('supplierCreateForm');
        nameInput = document.getElementById('supplier_name');
        emailInput = document.getElementById('email');
        statusText = document.getElementById('supplierFieldStatus');
        messageBox = document.getElementById('supplierCreateMessage');

        if (!form || !nameInput || !emailInput || !statusText || !messageBox) {
            return;
        }

        form.addEventListener('submit', handleSubmit);
        nameInput.addEventListener('input', function () {
            updateRequiredStatus();
            clearMessage();
        });
        emailInput.addEventListener('input', clearMessage);

        updateRequiredStatus();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
