(function () {
    'use strict';

    var PAGE_SIZE = 7;

    function compareStrings(left, right) {
        return String(left || '').localeCompare(String(right || ''), undefined, { sensitivity: 'base' });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var searchEl = document.getElementById('tmSearch');
        var roleEl = document.getElementById('tmRoleFilter');
        var clearBtn = document.getElementById('tmClearBtn');
        var tbody = document.getElementById('usersTableBody');
        var showingEl = document.getElementById('tmShowingText');
        var pagEl = document.getElementById('tmPagination');
        var sortableHeaders = Array.prototype.slice.call(document.querySelectorAll('#team .rd-sortable'));

        if (!tbody || !showingEl || !pagEl) {
            return;
        }

        var allRows = Array.from(tbody.querySelectorAll('.user-row'));
        var state = {
            page: 1,
            search: '',
            role: 'all',
            sortBy: 'name',
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
            if (key === 'name') return row.getAttribute('data-name') || '';
            if (key === 'role') return row.getAttribute('data-role') || '';
            if (key === 'status') return row.getAttribute('data-status') || '';
            if (key === 'email') return row.getAttribute('data-email') || '';
            return '';
        }

        function getVisibleRows() {
            var query = state.search;
            var role = state.role;

            return allRows.filter(function (row) {
                var name = getRowValue(row, 'name');
                var email = getRowValue(row, 'email');
                var rowRole = getRowValue(row, 'role');

                var matchesSearch = !query || name.indexOf(query) !== -1 || email.indexOf(query) !== -1;
                var matchesRole = role === 'all' || rowRole === role;
                return matchesSearch && matchesRole;
            }).sort(function (left, right) {
                var leftValue = getRowValue(left, state.sortBy);
                var rightValue = getRowValue(right, state.sortBy);
                var comparison = compareStrings(leftValue, rightValue);
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
            showingEl.textContent = 'Showing ' + from + '\u2013' + to + ' of ' + totalRows + ' users';

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

        roleEl && roleEl.addEventListener('change', function () {
            state.role = String(roleEl.value || 'all').toLowerCase();
            resetAndRender();
        });

        clearBtn && clearBtn.addEventListener('click', function () {
            state.search = '';
            state.role = 'all';
            state.sortBy = 'name';
            state.sortDir = 'asc';
            state.page = 1;

            if (searchEl) searchEl.value = '';
            if (roleEl) roleEl.value = 'all';

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
    });
})();
