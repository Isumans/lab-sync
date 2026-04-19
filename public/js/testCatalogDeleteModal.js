document.addEventListener('DOMContentLoaded', function () {
    var modal      = document.getElementById('testCatalogDeleteModal');
    var closeBtn   = document.getElementById('testCatalogDeleteClose');
    var cancelBtn  = document.getElementById('testCatalogDeleteCancel');
    var confirmBtn = document.getElementById('testCatalogDeleteConfirm');
    var testNameEl = document.getElementById('tcDeleteTestName');
    var alertEl    = document.getElementById('tcDeleteAlert');
    if (!modal || !confirmBtn) { return; }

    var appConfig = window.LAB_SYNC_CONFIG || {};
    var baseUrl   = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');
    var csrfToken = String(appConfig.csrfToken || '');
    var deleteEndpoint = baseUrl + '/index.php?controller=TestCatalog&action=deleteTestAjax';

    var activeTrigger  = null;
    var selectedTestId = null;

    function showAlert(message) {
        if (!alertEl) { return; }
        alertEl.textContent = message || 'Delete request failed.';
        alertEl.hidden = false;
    }

    function hideAlert() {
        if (!alertEl) { return; }
        alertEl.textContent = '';
        alertEl.hidden = true;
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        hideAlert();
        if (confirmBtn) { confirmBtn.focus(); }
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        selectedTestId = null;
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete Test';
        }
        hideAlert();
        if (activeTrigger) { activeTrigger.focus(); }
    }

    function setModalData(testId, testName) {
        selectedTestId = parseInt(testId, 10);
        if (testNameEl) {
            testNameEl.textContent = testName && testName.trim() ? testName : 'Unknown Test';
        }
    }

    function sendDelete() {
        if (!selectedTestId || isNaN(selectedTestId)) {
            showAlert('Invalid test ID.');
            return;
        }

        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting\u2026';
        hideAlert();

        fetch(deleteEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ test_id: selectedTestId })
        })
        .then(function (r) {
            return r.json().then(function (payload) {
                if (!r.ok || !payload || payload.status !== 'success') {
                    throw new Error((payload && payload.message) ? payload.message : 'Unable to delete test.');
                }
                return payload;
            });
        })
        .then(function () {
            closeModal();
            window.location.reload();
        })
        .catch(function (err) {
            showAlert(err.message || 'Unable to delete test.');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete Test';
        });
    }

    document.addEventListener('click', function (e) {
        var deleteBtn = e.target.closest('.js-delete-test-btn');
        if (deleteBtn) {
            e.preventDefault();
            var testId   = deleteBtn.getAttribute('data-test-id');
            var testName = deleteBtn.getAttribute('data-test-name') || '';
            if (!testId) { return; }
            activeTrigger = deleteBtn;
            setModalData(testId, testName);
            openModal();
            return;
        }

        if (e.target === modal) { closeModal(); }
    });

    if (closeBtn)  { closeBtn.addEventListener('click',  closeModal); }
    if (cancelBtn) { cancelBtn.addEventListener('click', closeModal); }
    confirmBtn.addEventListener('click', sendDelete);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) { closeModal(); }
    });
});
