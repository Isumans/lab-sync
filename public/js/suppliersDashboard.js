(function () {
    var pageSize = 7;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var searchDebounceTimer = null;
    var sortBy = 'created_at';
    var sortDir = 'desc';

    var searchInput = null;
    var statusInput = null;
    var sortByInput = null;
    var sortDirInput = null;
    var dateFromInput = null;
    var dateToInput = null;
    var clearBtn = null;

    var tableBody = null;
    var showingText = null;
    var paginationNode = null;
    var sortableHeaders = [];

    function endpoint() {
        return '/lab_sync/index.php?controller=inventoryController&action=listSuppliers';
    }

    function queryNodes() {
        searchInput = document.getElementById('suppSearch');
        statusInput = document.getElementById('suppStatus');
        sortByInput = document.getElementById('suppSortBy');
        sortDirInput = document.getElementById('suppSortDir');
        dateFromInput = document.getElementById('suppDateFrom');
        dateToInput = document.getElementById('suppDateTo');
        clearBtn = document.getElementById('suppClearBtn');

        tableBody = document.getElementById('suppTableBody');
        showingText = document.getElementById('suppShowingText');
        paginationNode = document.getElementById('suppPagination');
        sortableHeaders = Array.prototype.slice.call(document.querySelectorAll('.rd-sortable'));
    }

    function safe(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDateTime(value) {
        if (!value) {
            return '-';
        }

        var dateObj = new Date(String(value).replace(' ', 'T'));
        if (isNaN(dateObj.getTime())) {
            return safe(value);
        }

        return dateObj.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        }) + ' ' + dateObj.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function collectFilters() {
        function getValue(node, fallback) {
            return node ? String(node.value || fallback).trim() : fallback;
        }

        return {
            search: getValue(searchInput, ''),
            status: getValue(statusInput, 'all').toLowerCase(),
            from_date: getValue(dateFromInput, ''),
            to_date: getValue(dateToInput, '')
        };
    }

    function setLoadingState() {
        if (!tableBody) {
            return;
        }
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="6">Loading suppliers...</td></tr>';
    }

    function setErrorState(message) {
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }

        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="6">' + safe(message) + '</td></tr>';
        showingText.textContent = 'Showing 0-0 of 0 suppliers';
        paginationNode.innerHTML = '';
    }

    function renderRows(rows) {
        if (!tableBody) {
            return;
        }

        if (!rows.length) {
            tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="6">No suppliers found for the selected filters.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map(function (item) {
            var supplierId = Number(item.supplier_id || 0);
            var supplierName = String(item.supplier_name || '');
            var contactNo = item.contact_no == null || item.contact_no === '' ? '-' : String(item.contact_no);
            var location = item.location == null || item.location === '' ? '-' : String(item.location);
            var email = item.email == null || item.email === '' ? '-' : String(item.email);

            return (
                '<tr>' +
                    '<td><span class="rd-appointment-id">SUP-' + String(supplierId).padStart(4, '0') + '</span></td>' +
                    '<td>' + safe(supplierName) + '</td>' +
                    '<td>' + safe(contactNo) + '</td>' +
                    '<td>' + safe(location) + '</td>' +
                    '<td>' + safe(email) + '</td>' +
                    '<td>' + safe(formatDateTime(item.created_at || '')) + '</td>' +
                '</tr>'
            );
        }).join('');
    }

    function renderPagination(pageCount) {
        if (!paginationNode) {
            return;
        }

        var safePageCount = Math.max(1, pageCount);
        var buttons = [];

        buttons.push('<button type="button" class="rd-page-btn" data-page="prev" ' + (currentPage === 1 ? 'disabled' : '') + '>‹</button>');

        for (var i = 1; i <= safePageCount; i += 1) {
            buttons.push('<button type="button" class="rd-page-btn ' + (i === currentPage ? 'is-active' : '') + '" data-page="' + i + '">' + i + '</button>');
        }

        buttons.push('<button type="button" class="rd-page-btn" data-page="next" ' + (currentPage === safePageCount ? 'disabled' : '') + '>›</button>');
        paginationNode.innerHTML = buttons.join('');
    }

    function updateSortHeaderState() {
        sortableHeaders.forEach(function (header) {
            var headerKey = header.getAttribute('data-sort');
            var isActive = headerKey === sortBy;
            header.classList.toggle('is-active', isActive);
            header.classList.toggle('is-desc', isActive && sortDir === 'desc');
            header.classList.toggle('is-asc', isActive && sortDir === 'asc');
            header.setAttribute('aria-sort', isActive ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none');
        });

        if (sortByInput) {
            sortByInput.value = sortBy;
        }
        if (sortDirInput) {
            sortDirInput.value = sortDir;
        }
    }

    function fetchSuppliers() {
        var filters = collectFilters();
        var query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(pageSize),
            search: filters.search,
            status: filters.status,
            from_date: filters.from_date,
            to_date: filters.to_date,
            sort_by: sortBy,
            sort_dir: sortDir
        });

        setLoadingState();

        return fetch(endpoint() + '&' + query.toString(), {
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to load suppliers.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'success') {
                    throw new Error((payload && payload.message) || 'Unable to load suppliers.');
                }

                var rows = Array.isArray(payload.data) ? payload.data : [];
                var pagination = payload.pagination || {};

                totalItems = Number(pagination.total || 0);
                totalPages = Math.max(1, Number(pagination.total_pages || 1));
                currentPage = Math.min(Math.max(1, Number(pagination.current_page || currentPage)), totalPages);

                renderRows(rows);
                renderPagination(totalPages);
                updateSortHeaderState();

                var start = totalItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
                var end = totalItems === 0 ? 0 : Math.min(currentPage * pageSize, totalItems);
                showingText.textContent = 'Showing ' + start + '-' + end + ' of ' + totalItems + ' suppliers';
            })
            .catch(function (error) {
                setErrorState(error.message || 'Error loading suppliers.');
            });
    }

    function scheduleSearchFetch() {
        window.clearTimeout(searchDebounceTimer);
        searchDebounceTimer = window.setTimeout(function () {
            currentPage = 1;
            fetchSuppliers();
        }, 350);
    }

    function wireEvents() {
        if (searchInput) {
            searchInput.addEventListener('input', scheduleSearchFetch);
        }

        [statusInput, dateFromInput, dateToInput, sortByInput, sortDirInput].forEach(function (node) {
            if (!node) {
                return;
            }

            node.addEventListener('change', function () {
                if (node === sortByInput) {
                    sortBy = String(sortByInput.value || 'created_at');
                }
                if (node === sortDirInput) {
                    sortDir = String(sortDirInput.value || 'desc');
                }
                currentPage = 1;
                fetchSuppliers();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                }
                if (statusInput) {
                    statusInput.value = 'all';
                }
                if (dateFromInput) {
                    dateFromInput.value = '';
                }
                if (dateToInput) {
                    dateToInput.value = '';
                }
                if (sortByInput) {
                    sortByInput.value = 'created_at';
                }
                if (sortDirInput) {
                    sortDirInput.value = 'desc';
                }

                sortBy = 'created_at';
                sortDir = 'desc';
                currentPage = 1;
                fetchSuppliers();
            });
        }

        if (paginationNode) {
            paginationNode.addEventListener('click', function (event) {
                var button = event.target.closest('.rd-page-btn');
                if (!button || button.disabled) {
                    return;
                }

                var targetPage = button.getAttribute('data-page');
                if (targetPage === 'prev') {
                    currentPage = Math.max(1, currentPage - 1);
                } else if (targetPage === 'next') {
                    currentPage = Math.min(totalPages, currentPage + 1);
                } else {
                    currentPage = Number(targetPage || currentPage);
                }

                fetchSuppliers();
            });
        }

        sortableHeaders.forEach(function (header) {
            header.addEventListener('click', function () {
                var nextSortBy = header.getAttribute('data-sort') || 'created_at';
                if (sortBy === nextSortBy) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortBy = nextSortBy;
                    sortDir = header.getAttribute('data-direction') || 'asc';
                }

                currentPage = 1;
                fetchSuppliers();
            });
        });
    }

    function init() {
        queryNodes();
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }

        wireEvents();
        updateSortHeaderState();
        fetchSuppliers();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
