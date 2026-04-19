document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('testCatalogViewModal');
    var modalBody = document.getElementById('testCatalogViewBody');
    var closeBtn = document.getElementById('testCatalogViewClose');
    if (!modal || !modalBody) { return; }

    var appConfig = window.LAB_SYNC_CONFIG || {};
    var baseUrl = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');
    var endpoint = baseUrl + '/index.php?controller=TestCatalog&action=getTestDetails';

    var activeTrigger = null;

    var loadingHtml = '<div class="test-catalog-view-loading"><div class="spinner"></div><p>Loading test details\u2026</p></div>';

    function renderError(message) {
        modalBody.innerHTML = '<div class="test-catalog-view-error"><h3>Unable to load details</h3><p>' + String(message) + '</p></div>';
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (closeBtn) { closeBtn.focus(); }
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modalBody.innerHTML = '';
        document.body.style.overflow = '';
        if (activeTrigger) { activeTrigger.focus(); }
    }

    function fetchTestDetails(testId) {
        modalBody.innerHTML = loadingHtml;
        openModal();

        fetch(endpoint + '&test_id=' + encodeURIComponent(testId), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) {
            if (!r.ok) {
                return r.text().then(function (t) { throw new Error(t || 'Status ' + r.status); });
            }
            return r.text();
        })
        .then(function (html) {
            if (!html.trim()) { throw new Error('Empty response from server.'); }
            modalBody.innerHTML = html;
        })
        .catch(function (err) {
            renderError(err.message || 'A network error occurred.');
        });
    }

    document.addEventListener('click', function (e) {
        var viewBtn = e.target.closest('.js-view-test-btn');
        if (viewBtn) {
            e.preventDefault();
            var testId = viewBtn.getAttribute('data-test-id');
            if (!testId) { return; }
            activeTrigger = viewBtn;
            fetchTestDetails(testId);
            return;
        }

        if (e.target === modal) { closeModal(); return; }

        if (closeBtn && (e.target === closeBtn || closeBtn.contains(e.target))) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
