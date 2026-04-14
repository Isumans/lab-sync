(function () {
    var pageSize = 7;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var searchDebounceTimer = null;

    var searchInput = document.getElementById('rrSearch');
    var clearBtn = document.getElementById('rrClearBtn');
    var tableBody = document.getElementById('rrTableBody');
    var showingText = document.getElementById('rrShowingText');
    var paginationNode = document.getElementById('rrPagination');

    var sendModal = document.getElementById('rrSendModal');
    var sendDetails = document.getElementById('rrSendDetails');
    var sendCloseBtn = document.getElementById('rrSendCloseBtn');
    var sendOkBtn = document.getElementById('rrSendOkBtn');

    function endpoint(actionName) {
        return '/lab_sync/index.php?controller=reportsController&action=' + encodeURIComponent(actionName);
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
            return value;
        }
        return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function collectFilters() {
        return {
            search: searchInput ? String(searchInput.value || '').trim() : ''
        };
    }

    function setLoadingState() {
        if (!tableBody) {
            return;
        }
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">Loading authorized reports...</td></tr>';
    }

    function setErrorState(message) {
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">' + safe(message) + '</td></tr>';
        showingText.textContent = 'Showing 0-0 of 0 reports';
        paginationNode.innerHTML = '';
    }

    function statusBadge(statusValue) {
        var status = String(statusValue || 'AUTHORIZED').toUpperCase();
        var className = status === 'PRINTED' ? 'rr-status-printed' : 'rr-status-authorized';
        return '<span class="rr-status-badge ' + className + '">' + safe(status) + '</span>';
    }

    function renderRows(rows) {
        if (!tableBody) {
            return;
        }

        if (!rows.length) {
            tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">No authorized reports found.</td></tr>';
            return;
        }

        tableBody.innerHTML = rows.map(function (item) {
            return (
                '<tr>' +
                    '<td>' + safe(item.referenceNo || '-') + '</td>' +
                    '<td>' + safe(item.patientName || 'Unknown') + '</td>' +
                    '<td>' + safe(item.uhid || '-') + '</td>' +
                    '<td>' + safe(item.testName || '-') + '</td>' +
                    '<td>' + safe(formatDate(item.date)) + '</td>' +
                    '<td>' + statusBadge(item.status) + '</td>' +
                    '<td class="rd-th-right">' +
                        '<div class="rr-actions">' +
                            '<button type="button" class="rr-action-btn rr-action-view js-rr-view" data-url="' + safe(item.viewUrl || '#') + '">View</button>' +
                            '<button type="button" class="rr-action-btn rr-action-download js-rr-download" data-url="' + safe(item.downloadUrl || '#') + '">Download</button>' +
                            '<button type="button" class="rr-action-btn rr-action-print js-rr-print" data-url="' + safe(item.viewUrl || '#') + '">Print</button>' +
                            '<button type="button" class="rr-action-btn rr-action-send js-rr-send" data-patient="' + safe(item.patientName || 'Unknown') + '" data-ref="' + safe(item.referenceNo || '-') + '">Send</button>' +
                        '</div>' +
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

    function fetchReports() {
        var filters = collectFilters();
        var query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(pageSize),
            search: filters.search
        });

        setLoadingState();

        return fetch(endpoint('listAuthorizedReports') + '&' + query.toString(), {
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Failed to load authorized reports.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'success') {
                    throw new Error((payload && payload.message) || 'Unable to load authorized reports.');
                }

                var rows = Array.isArray(payload.data) ? payload.data : [];
                var pagination = payload.pagination || {};
                totalItems = Number(pagination.total || 0);
                totalPages = Math.max(1, Number(pagination.total_pages || 1));
                currentPage = Math.min(Math.max(1, Number(pagination.current_page || currentPage)), totalPages);

                renderRows(rows);
                renderPagination(totalPages);

                if (showingText) {
                    var start = totalItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
                    var end = totalItems === 0 ? 0 : Math.min(currentPage * pageSize, totalItems);
                    showingText.textContent = 'Showing ' + start + '-' + end + ' of ' + totalItems + ' reports';
                }
            })
            .catch(function (error) {
                setErrorState(error.message || 'Error loading authorized reports.');
            });
    }

    function openSendModal(patientName, referenceNo) {
        if (!sendModal || !sendDetails) {
            return;
        }
        sendDetails.textContent = 'Patient: ' + patientName + ' | Reference: ' + referenceNo;
        sendModal.hidden = false;
    }

    function closeSendModal() {
        if (!sendModal) {
            return;
        }
        sendModal.hidden = true;
    }

    function scheduleSearchFetch() {
        if (searchDebounceTimer) {
            window.clearTimeout(searchDebounceTimer);
        }

        searchDebounceTimer = window.setTimeout(function () {
            currentPage = 1;
            fetchReports();
        }, 300);
    }

    function attachEvents() {
        if (searchInput) {
            searchInput.addEventListener('input', scheduleSearchFetch);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (searchInput) {
                    searchInput.value = '';
                }
                currentPage = 1;
                fetchReports();
            });
        }

        if (paginationNode) {
            paginationNode.addEventListener('click', function (event) {
                var target = event.target;
                if (!(target instanceof HTMLElement) || !target.classList.contains('rd-page-btn') || target.disabled) {
                    return;
                }

                var action = target.getAttribute('data-page');
                if (action === 'prev') {
                    currentPage = Math.max(1, currentPage - 1);
                } else if (action === 'next') {
                    currentPage = Math.min(totalPages, currentPage + 1);
                } else {
                    currentPage = Number(action) || 1;
                }

                fetchReports();
            });
        }

        if (tableBody) {
            tableBody.addEventListener('click', function (event) {
                var target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }

                var viewBtn = target.closest('.js-rr-view');
                if (viewBtn) {
                    var viewUrl = viewBtn.getAttribute('data-url');
                    if (viewUrl && viewUrl !== '#') {
                        window.open(viewUrl, '_blank', 'noopener');
                    }
                    return;
                }

                var downloadBtn = target.closest('.js-rr-download');
                if (downloadBtn) {
                    var downloadUrl = downloadBtn.getAttribute('data-url');
                    if (downloadUrl && downloadUrl !== '#') {
                        window.location.href = downloadUrl;
                    }
                    return;
                }

                var printBtn = target.closest('.js-rr-print');
                if (printBtn) {
                    var printUrl = printBtn.getAttribute('data-url');
                    if (printUrl && printUrl !== '#') {
                        var printWindow = window.open(printUrl, '_blank', 'noopener');
                        if (printWindow) {
                            printWindow.addEventListener('load', function () {
                                printWindow.print();
                            });
                        }
                    }
                    return;
                }

                var sendBtn = target.closest('.js-rr-send');
                if (sendBtn) {
                    var patient = sendBtn.getAttribute('data-patient') || 'Unknown';
                    var reference = sendBtn.getAttribute('data-ref') || '-';
                    openSendModal(patient, reference);
                }
            });
        }

        if (sendCloseBtn) {
            sendCloseBtn.addEventListener('click', closeSendModal);
        }
        if (sendOkBtn) {
            sendOkBtn.addEventListener('click', closeSendModal);
        }
        if (sendModal) {
            sendModal.addEventListener('click', function (event) {
                if (event.target === sendModal) {
                    closeSendModal();
                }
            });
        }
    }

    function init() {
        if (!tableBody || !showingText || !paginationNode) {
            return;
        }
        attachEvents();
        fetchReports();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
