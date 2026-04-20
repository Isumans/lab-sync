document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('testCatalogEditModal');
    var form = document.getElementById('editTestCatalogForm');
    if (!modal || !form) { return; }

    var appConfig = window.LAB_SYNC_CONFIG || {};
    var baseUrl = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');
    var csrfToken = String(appConfig.csrfToken || '');

    var getEndpoint  = baseUrl + '/index.php?controller=TestCatalog&action=getTestEditData';
    var saveEndpoint = baseUrl + '/index.php?controller=TestCatalog&action=updateTestAjax';

    var idInput          = document.getElementById('editTestId');
    var testNameInput    = document.getElementById('editTcTestName');
    var departmentSelect = document.getElementById('editTcDepartment');
    var defaultUnitInput = document.getElementById('editTcDefaultUnit');
    var printNameInput   = document.getElementById('editTcPrintName');
    var descriptionInput = document.getElementById('editTcDescription');
    var costPriceInput   = document.getElementById('editTcCostPrice');
    var discountInput    = document.getElementById('editTcDiscount');
    var isActiveSelect   = document.getElementById('editTcIsActive');
    var reportCommentsInput = document.getElementById('editTcReportComments');
    var pricePreviewEl   = document.getElementById('editTcPricePreview');
    var alertBox         = document.getElementById('editTestCatalogAlert');
    var submitBtn        = document.getElementById('editTestCatalogSubmit');
    var titleEl          = document.getElementById('editTestCatalogTitle');
    var closeBtn         = document.getElementById('editTestCatalogClose');
    var cancelBtn        = document.getElementById('editTestCatalogCancel');

    var activeTrigger = null;

    function recalcPrice() {
        if (!pricePreviewEl) { return; }
        var cost = parseFloat(costPriceInput ? costPriceInput.value : '0') || 0;
        var disc = parseFloat(discountInput ? discountInput.value : '0') || 0;
        disc = Math.min(100, Math.max(0, disc));
        var price = Math.max(0, cost - (cost * disc / 100));
        pricePreviewEl.innerHTML = 'Calculated price: <strong>LKR ' + price.toFixed(2) + '</strong>';
    }

    if (costPriceInput) { costPriceInput.addEventListener('input', recalcPrice); }
    if (discountInput)  { discountInput.addEventListener('input', recalcPrice); }

    function showAlert(msg, type) {
        if (!alertBox) { return; }
        alertBox.textContent = msg || 'An error occurred.';
        alertBox.className = 'tc-edit-alert' + (type === 'success' ? ' success' : '');
        alertBox.hidden = false;
    }

    function hideAlert() {
        if (!alertBox) { return; }
        alertBox.textContent = '';
        alertBox.hidden = true;
    }

    function setModalOpen(open) {
        modal.classList.toggle('is-open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';
        if (!open && activeTrigger) { activeTrigger.focus(); }
    }

    function setLoading(isLoading) {
        if (!submitBtn) { return; }
        submitBtn.disabled = isLoading;
        submitBtn.textContent = isLoading ? 'Saving\u2026' : 'Save Changes';
    }

    function populateForm(data) {
        if (idInput)          { idInput.value          = data.test_id || ''; }
        if (titleEl)          { titleEl.textContent     = 'Edit Test: ' + (data.test_name || ''); }
        if (testNameInput)    { testNameInput.value    = data.test_name || ''; }
        if (departmentSelect) { departmentSelect.value = (data.department || '').toLowerCase(); }
        if (defaultUnitInput) { defaultUnitInput.value = data.default_unit || ''; }
        if (printNameInput)   { printNameInput.value   = data.print_name || ''; }
        if (descriptionInput) { descriptionInput.value = data.description || ''; }
        if (costPriceInput)   { costPriceInput.value   = data.cost_price || ''; }
        if (discountInput)    { discountInput.value    = data.discount || '0'; }
        if (isActiveSelect)   { isActiveSelect.value   = String(data.is_active !== undefined ? data.is_active : 1); }
        if (reportCommentsInput) { reportCommentsInput.value = data.report_comments || ''; }
        recalcPrice();
        hideAlert();
    }

    function fetchEditData(testId) {
        setModalOpen(true);
        hideAlert();

        fetch(getEndpoint + '&test_id=' + encodeURIComponent(testId), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (payload) {
            if (payload.status !== 'success' || !payload.data) {
                throw new Error(payload.message || 'Could not load test data.');
            }
            populateForm(payload.data);
            if (testNameInput) { testNameInput.focus(); }
        })
        .catch(function (err) {
            showAlert(err.message || 'Unable to load test data.');
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!idInput || !idInput.value) { showAlert('Missing test ID. Please reopen the modal.'); return; }
        if (!testNameInput || !testNameInput.value.trim()) {
            showAlert('Test name is required.');
            if (testNameInput) { testNameInput.focus(); }
            return;
        }

        setLoading(true);

        var payload = {
            test_id:         parseInt(idInput.value, 10),
            test_name:       testNameInput ? testNameInput.value.trim() : '',
            department:      departmentSelect ? departmentSelect.value : '',
            default_unit:    defaultUnitInput ? defaultUnitInput.value.trim() : '',
            print_name:      printNameInput ? printNameInput.value.trim() : '',
            description:     descriptionInput ? descriptionInput.value.trim() : '',
            cost_price:      parseFloat(costPriceInput ? costPriceInput.value : '0') || 0,
            discount:        parseFloat(discountInput ? discountInput.value : '0') || 0,
            is_active:       parseInt(isActiveSelect ? isActiveSelect.value : '1', 10),
            report_comments: reportCommentsInput ? reportCommentsInput.value.trim() : ''
        };

        fetch(saveEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json(); })
        .then(function (result) {
            if (result.status !== 'success') { throw new Error(result.message || 'Update failed.'); }
            showAlert(result.message || 'Test updated successfully.', 'success');
            setTimeout(function () { setModalOpen(false); window.location.reload(); }, 700);
        })
        .catch(function (err) {
            showAlert(err.message || 'Failed to save changes.');
        })
        .finally(function () {
            setLoading(false);
        });
    });

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.js-edit-test-btn');
        if (editBtn) {
            e.preventDefault();
            var testId = editBtn.getAttribute('data-test-id');
            if (!testId) { return; }
            activeTrigger = editBtn;
            fetchEditData(testId);
            return;
        }
        if (e.target === modal) { setModalOpen(false); }
    });

    if (closeBtn)  { closeBtn.addEventListener('click',  function () { setModalOpen(false); }); }
    if (cancelBtn) { cancelBtn.addEventListener('click', function () { setModalOpen(false); }); }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) { setModalOpen(false); }
    });
});
