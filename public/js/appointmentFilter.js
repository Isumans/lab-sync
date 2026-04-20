(function () {
    var appConfig = window.LAB_SYNC_CONFIG || {};
    var baseUrl = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');

    var pageSize = 7;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var searchDebounceTimer = null;
    var sortBy = 'appointment_date';
    var sortDir = 'desc';

    var searchInput = null;
    var methodInput = null;
    var sortByInput = null;
    var sortDirInput = null;
    var dateFromInput = null;
    var dateToInput = null;
    var clearBtn = null;
    var tableBody = null;
    var showingText = null;
    var paginationNode = null;
    var sortableHeaders = [];
    var tabButtons = [];
    var scheduledSection = null;
    var prescriptionSection = null;
    var activeTab = 'scheduled';

    function endpoint() {
        return baseUrl + '/index.php?controller=appointmentsController&action=filterAppointments';
    }

    function queryNodes() {
        searchInput = document.getElementById('aptSearch');
        methodInput = document.getElementById('aptMethod');
        sortByInput = document.getElementById('aptSortBy');
        sortDirInput = document.getElementById('aptSortDir');
        dateFromInput = document.getElementById('aptDateFrom');
        dateToInput = document.getElementById('aptDateTo');
        clearBtn = document.getElementById('aptClearBtn');

        tableBody = document.getElementById('aptTableBody');
        showingText = document.getElementById('aptShowingText');
        paginationNode = document.getElementById('aptPagination');
        sortableHeaders = Array.prototype.slice.call(document.querySelectorAll('#scheduledAppointmentsSection .rd-sortable'));
    }

    function queryTabNodes() {
        tabButtons = Array.prototype.slice.call(document.querySelectorAll('[data-appointments-tab]'));
        scheduledSection = document.getElementById('scheduledAppointmentsSection');
        prescriptionSection = document.getElementById('prescriptionRequestsSection');
    }

    function applyTabState(nextTab, shouldFetch) {
        if (!scheduledSection || !prescriptionSection || !tabButtons.length) {
            return;
        }

        activeTab = nextTab === 'prescription' ? 'prescription' : 'scheduled';
        var scheduledActive = activeTab === 'scheduled';

        scheduledSection.classList.toggle('is-active', scheduledActive);
        scheduledSection.hidden = !scheduledActive;

        prescriptionSection.classList.toggle('is-active', !scheduledActive);
        prescriptionSection.hidden = scheduledActive;

        tabButtons.forEach(function (button) {
            var tabName = button.getAttribute('data-appointments-tab') || '';
            var isActive = tabName === activeTab;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (scheduledActive && shouldFetch) {
            fetchAppointments();
        }

        document.dispatchEvent(new CustomEvent('appointments:tab-changed', {
            detail: {
                tab: activeTab
            }
        }));
    }

    function safe(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
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

    function formatTime(value) {
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

    function resolvePatientName(item) {
        if (item && item.patient_name) {
            return item.patient_name;
        }
        if (item && item.patient_display_name) {
            return item.patient_display_name;
        }
        if (item && item.patient_id != null) {
            return String(item.patient_id);
        }
        return 'Unknown Patient';
    }

    function collectFilters() {
        function getValue(node, fallback) {
            return node ? String(node.value || fallback).trim() : fallback;
        }

        return {
            search: getValue(searchInput, ''),
            method: getValue(methodInput, 'all'),
            from_date: getValue(dateFromInput, ''),
            to_date: getValue(dateToInput, '')
        };
    }

    function billingActionHtml(item) {
        var appointmentId = Number(item.appointment_id || 0);
        var billExists = Number(item.bill_id || 0) > 0;
        var label = billExists ? 'View Bill' : 'Create Bill';
        var href = '/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=' + encodeURIComponent(String(appointmentId));

        return '<button type="button" class="billing-action-btn" onclick="window.location.href=\'' + safe(href) + '\'" title="' + safe(label) + '">' + safe(label) + '</button>';
    }

    function renderRows(rows) {
        if (!tableBody) {
            return;
        }

        if (!rows.length) {
            tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">No appointments found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map(function (item) {
            var appointmentId = Number(item.appointment_id || 0);
            var patientName = resolvePatientName(item);
            var method = String(item.method || 'physical').toLowerCase();
            var isHomeVisit = Number(item.home_collection || 0) === 1;
            var methodClass = isHomeVisit ? 'status-home' : (method === 'online' ? 'status-active' : 'status-inactive');
            var methodLabel = isHomeVisit ? 'Home Visit' : (method === 'online' ? 'Online' : 'Physical/Call');

            return (
                '<tr>' +
                    '<td class="rd-appointment-id">APP-' + String(appointmentId).padStart(4, '0') + '</td>' +
                    '<td>' + safe(patientName) + '</td>' +
                    '<td>' + safe(formatDate(item.appointment_date || '')) + '</td>' +
                    '<td>' + safe(formatTime(item.appointment_time || '')) + '</td>' +
                    '<td><span class="status-badge ' + methodClass + '">' + safe(methodLabel) + '</span></td>' +
                    '<td class="rd-th-right">' + billingActionHtml(item) + '</td>' +
                    '<td class="rd-th-right user-actions" style="justify-content:flex-end;">' +
                        '<button type="button" class="action-btn-view view-btn js-view-details-btn" data-appointment-id="' + safe(appointmentId) + '" title="View Details">' +
                            '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                                '<path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                                '<path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                            '</svg>' +
                        '</button>' +
                        '<button type="button" class="action-btn-edit edit-btn js-edit-appointment-btn" data-appointment-id="' + safe(appointmentId) + '" title="Edit">' +
                            '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                                '<path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
                            '</svg>' +
                        '</button>' +
                        '<button type="button" class="action-btn-delete delete-btn js-delete-appointment-btn" data-appointment-id="' + safe(appointmentId) + '" data-patient-name="' + safe(patientName) + '" title="Delete">' +
                            '<svg width="16" height="16" viewBox="0 0 16 16" fill="none">' +
                                '<path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>' +
                            '</svg>' +
                        '</button>' +
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

    function setLoadingState() {
        if (!tableBody) {
            return;
        }
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">Loading appointments...</td></tr>';
    }

    function setErrorState(message) {
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }

        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">' + safe(message) + '</td></tr>';
        showingText.textContent = 'Showing 0-0 of 0 appointments';
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

    function fetchAppointments() {
        var filters = collectFilters();
        var query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(pageSize),
            search: filters.search,
            method: filters.method,
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
                    throw new Error('Failed to load appointments.');
                }
                return response.json();
            })
            .then(function (payload) {
                var rows = [];
                var pagination = {};

                if (Array.isArray(payload)) {
                    rows = payload;
                } else if (payload && typeof payload === 'object') {
                    if (Array.isArray(payload.data)) {
                        rows = payload.data;
                    }
                    if (payload.pagination && typeof payload.pagination === 'object') {
                        pagination = payload.pagination;
                    }

                    if (payload.status === 'error' && !Array.isArray(payload.data)) {
                        throw new Error(payload.message || 'Unable to load appointments.');
                    }
                } else {
                    throw new Error('Unable to load appointments.');
                }

                if (Object.keys(pagination).length > 0) {
                    totalItems = Number(pagination.total || rows.length || 0);
                    totalPages = Math.max(1, Number(pagination.total_pages || 1));
                    currentPage = Math.min(Math.max(1, Number(pagination.current_page || currentPage)), totalPages);
                } else {
                    totalItems = rows.length;
                    totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
                    currentPage = Math.min(Math.max(1, currentPage), totalPages);

                    var startIndex = (currentPage - 1) * pageSize;
                    rows = rows.slice(startIndex, startIndex + pageSize);
                }

                renderRows(rows);
                renderPagination(totalPages);
                updateSortHeaderState();

                if (showingText) {
                    var start = totalItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
                    var end = totalItems === 0 ? 0 : Math.min(currentPage * pageSize, totalItems);
                    showingText.textContent = 'Showing ' + start + '-' + end + ' of ' + totalItems + ' appointments';
                }
            })
            .catch(function (error) {
                setErrorState(error.message || 'Error loading appointments.');
            });
    }

    function scheduleSearchFetch() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
            currentPage = 1;
            fetchAppointments();
        }, 350);
    }

    function wireEvents() {
        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var nextTab = button.getAttribute('data-appointments-tab') || 'scheduled';
                if (nextTab === activeTab) {
                    return;
                }

                applyTabState(nextTab, nextTab === 'scheduled');
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', scheduleSearchFetch);
        }

        [methodInput, dateFromInput, dateToInput, sortByInput, sortDirInput].forEach(function (node) {
            if (!node) {
                return;
            }

            node.addEventListener('change', function () {
                if (node === sortByInput) {
                    sortBy = String(sortByInput.value || 'appointment_date');
                }
                if (node === sortDirInput) {
                    sortDir = String(sortDirInput.value || 'desc');
                }
                currentPage = 1;
                fetchAppointments();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                }
                if (methodInput) {
                    methodInput.value = 'all';
                }
                if (sortByInput) {
                    sortByInput.value = 'appointment_date';
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

                sortBy = 'appointment_date';
                sortDir = 'desc';
                currentPage = 1;
                fetchAppointments();
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

                fetchAppointments();
            });
        }

        sortableHeaders.forEach(function (header) {
            header.addEventListener('click', function () {
                var nextSortBy = header.getAttribute('data-sort') || 'appointment_date';
                if (sortBy === nextSortBy) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortBy = nextSortBy;
                    sortDir = header.getAttribute('data-direction') || 'asc';
                }

                currentPage = 1;
                fetchAppointments();
            });
        });
    }

    function init() {
        queryTabNodes();
        queryNodes();
        if (!tableBody || !showingText || !paginationNode || !tabButtons.length) {
            return;
        }

        applyTabState('scheduled', false);
        wireEvents();
        updateSortHeaderState();
        fetchAppointments();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
