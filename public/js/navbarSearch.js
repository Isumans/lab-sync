(function () {
    'use strict';

    var MIN_QUERY_LENGTH = 3;
    var DEBOUNCE_MS = 300;

    var searchInput = document.getElementById('navbar-search-input');
    var resultsBox = document.getElementById('navbar-search-results');

    if (!searchInput || !resultsBox) {
        return;
    }

    var debounceTimer = null;
    var activeRequestId = 0;

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function hideResults() {
        resultsBox.hidden = true;
        resultsBox.innerHTML = '';
    }

    var TYPE_LABELS = {
        patient:   'Patient',
        test:      'Test',
        inventory: 'Inventory',
        supplier:  'Supplier',
    };

    var TYPE_COLORS = {
        patient:   '#3DBDEC',
        test:      '#10b981',
        inventory: '#f59e0b',
        supplier:  '#8b5cf6',
    };

    function renderResults(items) {
        if (!items.length) {
            resultsBox.innerHTML = '<div class="nsr-empty">No results found.</div>';
            resultsBox.hidden = false;
            return;
        }

        resultsBox.innerHTML = items.map(function (item) {
            var color = TYPE_COLORS[item.type] || '#9ca3af';
            var badge = TYPE_LABELS[item.type] || item.type;
            return '<a class="nsr-item" href="' + escapeHtml(item.url) + '">' +
                '<span class="nsr-badge" style="background:' + color + '">' + escapeHtml(badge) + '</span>' +
                '<span class="nsr-text">' +
                    '<span class="nsr-label">' + escapeHtml(item.label) + '</span>' +
                    (item.subtitle ? '<span class="nsr-subtitle">' + escapeHtml(item.subtitle) + '</span>' : '') +
                '</span>' +
            '</a>';
        }).join('');
        resultsBox.hidden = false;
    }

    function fetchResults(query, requestId) {
        resultsBox.innerHTML = '<div class="nsr-loading">Searching\u2026</div>';
        resultsBox.hidden = false;

        var url = '/lab_sync/index.php?controller=navbarSearch&action=search&q=' + encodeURIComponent(query);

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json();
            })
            .then(function (items) {
                if (requestId !== activeRequestId) {
                    return;
                }
                renderResults(Array.isArray(items) ? items : []);
            })
            .catch(function () {
                if (requestId !== activeRequestId) {
                    return;
                }
                resultsBox.innerHTML = '<div class="nsr-empty">\u26a0 Could not load results. Please try again.</div>';
                resultsBox.hidden = false;
            });
    }

    function scheduleSearch() {
        var query = String(searchInput.value || '').trim();

        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }

        if (query.length === 0) {
            hideResults();
            return;
        }

        if (query.length < MIN_QUERY_LENGTH) {
            hideResults();
            return;
        }

        activeRequestId += 1;
        var requestId = activeRequestId;
        debounceTimer = setTimeout(function () {
            fetchResults(query, requestId);
        }, DEBOUNCE_MS);
    }

    searchInput.addEventListener('input', scheduleSearch);

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideResults();
            searchInput.blur();
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target) && !resultsBox.contains(event.target)) {
            hideResults();
        }
    });

    resultsBox.addEventListener('click', function () {
        hideResults();
        searchInput.value = '';
    });
})();
