(function () {
    'use strict';

    var PAGE_SIZE = 7;

    function compareStrings(left, right) {
        return String(left || '').localeCompare(String(right || ''), undefined, { sensitivity: 'base' });
    }

    function compareNumbers(left, right) {
        return (Number(left) || 0) - (Number(right) || 0);
    }

    window.initPartnerLabsFilter = function () {
        var root = document.getElementById('partner-labs');
        var searchEl = document.getElementById('plSearch');
        var statusEl = document.getElementById('plStatus');
        var clearBtn = document.getElementById('plClearBtn');
        var tbody = document.getElementById('plTableBody');
        var showingEl = document.getElementById('plShowingText');
        var pagEl = document.getElementById('plPagination');
        var sortableHeaders = Array.prototype.slice.call(document.querySelectorAll('#partner-labs .rd-sortable'));

        if (!root || !tbody || !showingEl || !pagEl) {
            return;
        }

        if (root.getAttribute('data-filter-init') === '1') {
            return;
        }
        root.setAttribute('data-filter-init', '1');

        var allRows = Array.from(tbody.querySelectorAll('tr')).filter(function (row) {
            return !row.querySelector('td[colspan]');
        });

        var state = {
            page: 1,
            search: '',
            status: 'all',
            sortBy: 'lab_name',
            sortDir: 'asc'
        };

        function updateSortUi() {
            sortableHeaders.forEach(function (header) {
                var sortKey = String(header.getAttribute('data-sort') || '');
                var isActive = sortKey === state.sortBy;
                header.classList.remove('is-active', 'is-asc', 'is-desc');
                if (isActive) {
                    header.classList.add('is-active');
                    header.classList.add(state.sortDir === 'asc' ? 'is-asc' : 'is-desc');
                }
            });
        }

        function getRowValue(row, key) {
            if (key === 'lab_name') return row.getAttribute('data-lab-name') || '';
            if (key === 'email') return row.getAttribute('data-email') || '';
            if (key === 'contact_person') return row.getAttribute('data-contact-person') || '';
            if (key === 'contact_number') return row.getAttribute('data-contact-number') || '';
            if (key === 'status') return row.getAttribute('data-status') || '';
            if (key === 'total_tests') return row.getAttribute('data-total-tests') || '0';
            return '';
        }

        function getVisibleRows() {
            var query = state.search;
            var status = state.status;

            return allRows.filter(function (row) {
                var searchable = [
                    getRowValue(row, 'lab_name'),
                    getRowValue(row, 'email'),
                    getRowValue(row, 'contact_person'),
                    getRowValue(row, 'contact_number')
                ].join(' ');

                var rowStatus = getRowValue(row, 'status');
                var matchesSearch = !query || searchable.indexOf(query) !== -1;
                var matchesStatus = status === 'all' || rowStatus === status;
                return matchesSearch && matchesStatus;
            }).sort(function (left, right) {
                var comparison;

                if (state.sortBy === 'total_tests') {
                    comparison = compareNumbers(getRowValue(left, state.sortBy), getRowValue(right, state.sortBy));
                } else {
                    comparison = compareStrings(getRowValue(left, state.sortBy), getRowValue(right, state.sortBy));
                }

                return state.sortDir === 'asc' ? comparison : -comparison;
            });
        }

        function buildPagination(totalPages) {
            pagEl.innerHTML = '';

            if (totalPages <= 1) {
                return;
            }

            function makeButton(label, targetPage, disabled, isActive) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'rd-page-btn' + (isActive ? ' is-active' : '');
                button.textContent = label;
                button.disabled = !!disabled;
                button.setAttribute('data-page', String(targetPage));
                return button;
            }

            pagEl.appendChild(makeButton('\u2039', state.page - 1, state.page === 1, false));

            for (var page = 1; page <= totalPages; page += 1) {
                pagEl.appendChild(makeButton(String(page), page, false, page === state.page));
            }

            pagEl.appendChild(makeButton('\u203a', state.page + 1, state.page === totalPages, false));
        }

        function render() {
            var visibleRows = getVisibleRows();
            var totalRows = visibleRows.length;
            var totalPages = Math.max(1, Math.ceil(totalRows / PAGE_SIZE));

            if (state.page > totalPages) {
                state.page = totalPages;
            }

            var start = (state.page - 1) * PAGE_SIZE;
            var end = start + PAGE_SIZE;

            allRows.forEach(function (row) {
                row.style.display = 'none';
            });

            visibleRows.forEach(function (row, index) {
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            var from = totalRows ? start + 1 : 0;
            var to = Math.min(end, totalRows);
            showingEl.textContent = 'Showing ' + from + '\u2013' + to + ' of ' + totalRows + ' partner labs';

            buildPagination(totalPages);
            updateSortUi();
        }

        function resetAndRender() {
            state.page = 1;
            render();
        }

        searchEl && searchEl.addEventListener('input', function () {
            state.search = String(searchEl.value || '').trim().toLowerCase();
            resetAndRender();
        });

        statusEl && statusEl.addEventListener('change', function () {
            state.status = String(statusEl.value || 'all').toLowerCase();
            resetAndRender();
        });

        clearBtn && clearBtn.addEventListener('click', function () {
            state.search = '';
            state.status = 'all';
            state.sortBy = 'lab_name';
            state.sortDir = 'asc';
            state.page = 1;

            if (searchEl) searchEl.value = '';
            if (statusEl) statusEl.value = 'all';

            render();
        });

        sortableHeaders.forEach(function (header) {
            function activateSort() {
                var nextSort = String(header.getAttribute('data-sort') || '');
                if (!nextSort) {
                    return;
                }

                if (state.sortBy === nextSort) {
                    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortBy = nextSort;
                    state.sortDir = String(header.getAttribute('data-direction') || 'asc');
                }

                resetAndRender();
            }

            header.addEventListener('click', activateSort);
            header.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    activateSort();
                }
            });
        });

        pagEl.addEventListener('click', function (event) {
            var button = event.target.closest('.rd-page-btn');
            if (!button || button.disabled) {
                return;
            }

            var nextPage = Number(button.getAttribute('data-page') || state.page);
            if (!Number.isFinite(nextPage) || nextPage < 1) {
                return;
            }

            state.page = nextPage;
            render();
        });

        render();
    };
})();
