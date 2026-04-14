(function () {
    var pageSize = 7;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var searchDebounceTimer = null;
    var sortBy = 'last_updated';
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
        return '/lab_sync/index.php?controller=inventoryController&action=listInventory';
    }

    function queryNodes() {
        searchInput = document.getElementById('invSearch');
        statusInput = document.getElementById('invStatus');
        sortByInput = document.getElementById('invSortBy');
        sortDirInput = document.getElementById('invSortDir');
        dateFromInput = document.getElementById('invDateFrom');
        dateToInput = document.getElementById('invDateTo');
        clearBtn = document.getElementById('invClearBtn');

        tableBody = document.getElementById('invTableBody');
        showingText = document.getElementById('invShowingText');
        paginationNode = document.getElementById('invPagination');
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

    function toDisplayDateTime(value) {
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

    function normalizeStatusLabel(status) {
        var raw = String(status || 'In Stock').trim();
        if (!raw) {
            raw = 'In Stock';
        }
        return raw
            .split(/\s+/)
            .map(function (part) {
                return part.charAt(0).toUpperCase() + part.slice(1).toLowerCase();
            })
            .join(' ');
    }

    function statusClass(status) {
        var normalized = String(status || '').trim().toLowerCase();
        if (normalized === 'out of stock') {
            return 'status-inactive';
        }
        if (normalized === 'low stock') {
            return 'role-receptionist';
        }
        return 'status-active';
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
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="8">Loading inventory items...</td></tr>';
    }

    function setErrorState(message) {
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }

        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="8">' + safe(message) + '</td></tr>';
        showingText.textContent = 'Showing 0-0 of 0 items';
        paginationNode.innerHTML = '';
    }

    function renderRows(rows) {
        if (!tableBody) {
            return;
        }

        if (!rows.length) {
            tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="8">No inventory items found for the selected filters.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map(function (item) {
            var inventoryId = Number(item.inventory_id || 0);
            var itemName = String(item.item_name || '');
            var supplierId = item.supplier_id == null ? 'N/A' : String(item.supplier_id);
            var quantity = item.quantity == null ? '0' : String(item.quantity);
            var reorderLevel = item.reorder_level == null ? '0' : String(item.reorder_level);
            var statusLabel = normalizeStatusLabel(item.status);
            var updatedAt = toDisplayDateTime(item.last_updated || '');

            return (
                '<tr>' +
                    '<td><span class="rd-appointment-id">INV-' + String(inventoryId).padStart(4, '0') + '</span></td>' +
                    '<td>' + safe(itemName) + '</td>' +
                    '<td>' + safe(supplierId) + '</td>' +
                    '<td>' + safe(quantity) + '</td>' +
                    '<td>' + safe(reorderLevel) + '</td>' +
                    '<td><span class="status-badge ' + statusClass(item.status) + '">' + safe(statusLabel) + '</span></td>' +
                    '<td>' + safe(updatedAt) + '</td>' +
                    '<td class="rd-th-right user-actions" style="justify-content:flex-end;">' +
                        '<form method="post" action="/lab_sync/index.php?controller=inventoryController&action=edit_item" class="user-action-form">' +
                            '<input type="hidden" name="inventory_id" value="' + safe(inventoryId) + '">' +
                            '<input type="hidden" name="item_name" value="' + safe(itemName) + '">' +
                            '<input type="hidden" name="supplier_id" value="' + safe(item.supplier_id == null ? '' : item.supplier_id) + '">' +
                            '<input type="hidden" name="quantity" value="' + safe(item.quantity == null ? '' : item.quantity) + '">' +
                            '<input type="hidden" name="reorder_level" value="' + safe(item.reorder_level == null ? '' : item.reorder_level) + '">' +
                            '<button type="submit" name="edit" class="action-btn-edit" title="Edit" onclick="showAlertAndSubmit(event)">' +
                                '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                                    '<path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
                                '</svg>' +
                            '</button>' +
                            '<button type="submit" name="delete" class="action-btn-delete" title="Delete" onclick="showAlertAndSubmit(event)">' +
                                '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                                    '<path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
                                '</svg>' +
                            '</button>' +
                        '</form>' +
                    '</td>' +
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

    function fetchInventory() {
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
                    throw new Error('Failed to load inventory items.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'success') {
                    throw new Error((payload && payload.message) || 'Unable to load inventory items.');
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
                showingText.textContent = 'Showing ' + start + '-' + end + ' of ' + totalItems + ' items';
            })
            .catch(function (error) {
                setErrorState(error.message || 'Error loading inventory items.');
            });
    }

    function scheduleSearchFetch() {
        window.clearTimeout(searchDebounceTimer);
        searchDebounceTimer = window.setTimeout(function () {
            currentPage = 1;
            fetchInventory();
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
                    sortBy = String(sortByInput.value || 'last_updated');
                }
                if (node === sortDirInput) {
                    sortDir = String(sortDirInput.value || 'desc');
                }
                currentPage = 1;
                fetchInventory();
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
                    sortByInput.value = 'last_updated';
                }
                if (sortDirInput) {
                    sortDirInput.value = 'desc';
                }

                sortBy = 'last_updated';
                sortDir = 'desc';
                currentPage = 1;
                fetchInventory();
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

                fetchInventory();
            });
        }

        sortableHeaders.forEach(function (header) {
            header.addEventListener('click', function () {
                var nextSortBy = header.getAttribute('data-sort') || 'last_updated';
                if (sortBy === nextSortBy) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortBy = nextSortBy;
                    sortDir = header.getAttribute('data-direction') || 'asc';
                }

                currentPage = 1;
                fetchInventory();
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
        fetchInventory();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
