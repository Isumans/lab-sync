(function () {
    var appConfig = window.LAB_SYNC_CONFIG || {};
    var baseUrl = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');

    var pageSize = 7;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var searchDebounceTimer = null;
    var sortBy = 'created_at';
    var sortDir = 'desc';

    var sectionNode = null;
    var searchInput = null;
    var typeInput = null;
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
        return baseUrl + '/index.php?controller=appointmentsController&action=filterPrescriptionRequests';
    }

    function queryNodes() {
        sectionNode = document.getElementById('prescriptionRequestsSection');
        searchInput = document.getElementById('prxSearch');
        typeInput = document.getElementById('prxType');
        sortByInput = document.getElementById('prxSortBy');
        sortDirInput = document.getElementById('prxSortDir');
        dateFromInput = document.getElementById('prxDateFrom');
        dateToInput = document.getElementById('prxDateTo');
        clearBtn = document.getElementById('prxClearBtn');

        tableBody = document.getElementById('prxTableBody');
        showingText = document.getElementById('prxShowingText');
        paginationNode = document.getElementById('prxPagination');
        sortableHeaders = Array.prototype.slice.call(document.querySelectorAll('#prescriptionRequestsSection .rd-prx-sortable'));
    }

    function safe(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getInitials(name) {
        var text = String(name || '').trim();
        if (!text) {
            return 'NA';
        }

        var parts = text.split(/\s+/).filter(Boolean);
        if (parts.length === 1) {
            return parts[0].slice(0, 2).toUpperCase();
        }

        return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
    }

    function formatPreferredDate(value) {
        if (!value) {
            return '-';
        }

        var datePart = String(value).split(' ')[0];
        var dateObj = new Date(datePart + 'T00:00:00');
        if (isNaN(dateObj.getTime())) {
            return safe(value);
        }

        return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatPreferredTime(value) {
        if (!value) {
            return '-';
        }

        var raw = String(value).trim();
        var match = raw.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
        if (!match) {
            return safe(raw);
        }

        var hour = Number(match[1]);
        var minute = match[2];
        var suffix = hour >= 12 ? 'PM' : 'AM';
        var twelveHour = hour % 12;
        if (twelveHour === 0) {
            twelveHour = 12;
        }

        return String(twelveHour) + ':' + minute + ' ' + suffix;
    }

    function formatRequestStatus(value) {
        var raw = String(value || '').trim();
        if (!raw) {
            return 'Pending';
        }

        return raw
            .replace(/_/g, ' ')
            .toLowerCase()
            .replace(/\b\w/g, function (char) { return char.toUpperCase(); });
    }

    function collectFilters() {
        function getValue(node, fallback) {
            return node ? String(node.value || fallback).trim() : fallback;
        }

        return {
            search: getValue(searchInput, ''),
            request_type: getValue(typeInput, 'all'),
            from_date: getValue(dateFromInput, ''),
            to_date: getValue(dateToInput, '')
        };
    }

    function setLoadingState() {
        if (!tableBody) {
            return;
        }
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="8">Loading prescription requests...</td></tr>';
    }

    function setErrorState(message) {
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }

        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="8">' + safe(message) + '</td></tr>';
        showingText.textContent = 'Showing 0-0 of 0 requests';
        paginationNode.innerHTML = '';
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

    function renderRows(rows) {
        if (!tableBody) {
            return;
        }

        if (!rows.length) {
            tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="8">No prescription requests found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map(function (item) {
            var requestId = Number(item.request_id || 0);
            var patientName = String(item.patient_name || ('Patient #' + String(item.patient_id || '')));
            var requestType = String(item.request_type_label || 'Onsite');
            var hasPrescription = Number(item.prescription_available || 0) === 1 || String(item.prescription_file_path || '').trim() !== '';
            var requestStatus = formatRequestStatus(item.status);

            return (
                '<tr>' +
                    '<td class="rd-appointment-id">#RX-' + String(requestId).padStart(5, '0') + '</td>' +
                    '<td>' +
                        '<div class="rd-patient-cell">' +
                            '<span class="rd-patient-initials">' + safe(getInitials(patientName)) + '</span>' +
                            '<span>' + safe(patientName) + '</span>' +
                        '</div>' +
                    '</td>' +
                    '<td><span class="rd-prescription-pill ' + (hasPrescription ? 'is-yes' : 'is-no') + '">' + (hasPrescription ? 'Yes' : 'No') + '</span></td>' +
                    '<td>' + safe(requestType) + '</td>' +
                    '<td>' + safe(requestStatus) + '</td>' +
                    '<td class="rd-th-right"><button type="button" class="rd-view-more-btn js-prx-view-more-btn" data-request-id="' + safe(requestId) + '">Manage</button></td>' +
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

    function fetchRequests() {
        if (!sectionNode || sectionNode.hidden) {
            return Promise.resolve();
        }

        var filters = collectFilters();
        var query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(pageSize),
            search: filters.search,
            request_type: filters.request_type,
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
                    throw new Error('Failed to load prescription requests.');
                }
                return response.json();
            })
            .then(function (payload) {
                var rows = [];
                var pagination = {};

                if (payload && payload.status === 'success' && Array.isArray(payload.data)) {
                    rows = payload.data;
                    pagination = payload.pagination && typeof payload.pagination === 'object' ? payload.pagination : {};
                } else {
                    throw new Error((payload && payload.message) || 'Unable to load prescription requests.');
                }

                totalItems = Number(pagination.total || rows.length || 0);
                totalPages = Math.max(1, Number(pagination.total_pages || 1));
                currentPage = Math.min(Math.max(1, Number(pagination.current_page || currentPage)), totalPages);

                renderRows(rows);
                renderPagination(totalPages);
                updateSortHeaderState();

                if (showingText) {
                    var start = totalItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
                    var end = totalItems === 0 ? 0 : Math.min(currentPage * pageSize, totalItems);
                    showingText.textContent = 'Showing ' + start + '-' + end + ' of ' + totalItems + ' requests';
                }
            })
            .catch(function (error) {
                setErrorState(error.message || 'Error loading prescription requests.');
            });
    }

    function scheduleSearchFetch() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
            currentPage = 1;
            fetchRequests();
        }, 350);
    }

    function wireEvents() {
        if (searchInput) {
            searchInput.addEventListener('input', scheduleSearchFetch);
        }

        [typeInput, dateFromInput, dateToInput, sortByInput, sortDirInput].forEach(function (node) {
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
                fetchRequests();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                }
                if (typeInput) {
                    typeInput.value = 'all';
                }
                if (sortByInput) {
                    sortByInput.value = 'created_at';
                }
                if (sortDirInput) {
                    sortDirInput.value = 'desc';
                }
                if (dateFromInput) {
                    dateFromInput.value = '';
                }
                if (dateToInput) {
                    dateToInput.value = '';
                }

                sortBy = 'created_at';
                sortDir = 'desc';
                currentPage = 1;
                fetchRequests();
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

                fetchRequests();
            });
        }

        if (tableBody) {
            tableBody.addEventListener('click', function (event) {
                var button = event.target.closest('.js-prx-view-more-btn');
                if (!button) {
                    return;
                }

                var requestId = Number(button.getAttribute('data-request-id') || 0);
                document.dispatchEvent(new CustomEvent('prescription:view-more', {
                    detail: {
                        request_id: requestId
                    }
                }));
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
                fetchRequests();
            });
        });

        document.addEventListener('appointments:tab-changed', function (event) {
            var tab = event && event.detail ? event.detail.tab : '';
            if (tab === 'prescription') {
                currentPage = 1;
                fetchRequests();
            }
        });

        document.addEventListener('prescription:refresh', function () {
            if (!sectionNode || sectionNode.hidden) {
                return;
            }
            fetchRequests();
        });
    }

    function init() {
        queryNodes();
        if (!tableBody || !showingText || !paginationNode || !sectionNode) {
            return;
        }

        wireEvents();
        updateSortHeaderState();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
