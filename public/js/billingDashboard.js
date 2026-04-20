(function () {
    var pageSize = 10;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var currentRows = [];
    var searchDebounceTimer = null;

    var searchInput = null;
    var statusInput = null;
    var paymentMethodInput = null;
    var dateFromInput = null;
    var dateToInput = null;
    var clearBtn = null;

    var tableBody = null;
    var showingText = null;
    var paginationNode = null;

    // var exportBtn = document.getElementById("rdExportBtn");
    // var generateBtn = document.getElementById("rdGenerateBtn");

    var urlParams = new URLSearchParams(window.location.search);
    var currentRole = urlParams.get("role") || "";

    function endpoint(actionName) {
        var endpointUrl = "/lab_sync/index.php?controller=financesController&action=" + encodeURIComponent(actionName);
        if (currentRole) {
            endpointUrl += "&role=" + encodeURIComponent(currentRole);
        }
        return endpointUrl;
    }

    function queryNodes() {
        searchInput = document.getElementById("rdSearch");
        statusInput = document.getElementById("rdStatus");
        paymentMethodInput = document.getElementById("rdPaymentMethod");
        dateFromInput = document.getElementById("rdDateFrom");
        dateToInput = document.getElementById("rdDateTo");
        clearBtn = document.getElementById("rdClearBtn");

        tableBody = document.getElementById("rdTableBody") || document.getElementById("billingTableBody");
        showingText = document.getElementById("rdShowingText");
        paginationNode = document.getElementById("rdPagination");
    }

    function initials(name) {
        var safeName = typeof name === "string" ? name.trim() : "";
        if (!safeName) {
            return "--";
        }

        var parts = safeName.split(/\s+/);
        if (parts.length === 1) {
            return parts[0].slice(0, 2).toUpperCase();
        }
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatCurrency(amount) {
        var numeric = Number(amount);
        if (!Number.isFinite(numeric)) {
            numeric = 0;
        }

        return "LKR " + numeric.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function collectFilters() {
        function getValue(node, fallback) {
            return node ? node.value || fallback : fallback;
        }

        return {
            search: (getValue(searchInput, "") || "").trim(),
            status: (getValue(statusInput, "all") || "all").toLowerCase(),
            payment_method: (getValue(paymentMethodInput, "all") || "all").toLowerCase(),
            from_date: getValue(dateFromInput, ""),
            to_date: getValue(dateToInput, "")
        };
    }

    function isIsoDate(value) {
        return /^\d{4}-\d{2}-\d{2}$/.test(String(value || ""));
    }

    function getDateRangeError(filters) {
        if (filters.from_date !== "" && !isIsoDate(filters.from_date)) {
            return "Invalid start date format.";
        }

        if (filters.to_date !== "" && !isIsoDate(filters.to_date)) {
            return "Invalid end date format.";
        }

        if (filters.from_date !== "" && filters.to_date !== "" && filters.from_date > filters.to_date) {
            return "Start date cannot be later than end date.";
        }

        return "";
    }

    function applyDateRangeLimits() {
        if (!dateFromInput || !dateToInput) {
            return;
        }

        dateFromInput.max = dateToInput.value || "9999-12-31";
        dateToInput.min = dateFromInput.value || "";
    }

    function setLoadingState() {
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">Loading invoices...</td></tr>';
    }

    function setErrorState(message) {
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="7">' + message + "</td></tr>";
        showingText.textContent = "Showing 0-0 of 0 invoices";
        paginationNode.innerHTML = "";
    }

    function postAction(actionName, payload) {
        return fetch(endpoint(actionName), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json"
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json().then(function (body) {
                    return {
                        ok: response.ok,
                        body: body
                    };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.body || result.body.status !== "success") {
                    throw new Error((result.body && result.body.message) || "Request failed.");
                }
                return result.body;
            });
    }

    function fetchBills() {
        var filters = collectFilters();
        var dateRangeError = getDateRangeError(filters);
        if (dateRangeError) {
            setErrorState(dateRangeError);
            return Promise.resolve();
        }

        var query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(pageSize),
            search: filters.search,
            status: filters.status,
            payment_method: filters.payment_method,
            from_date: filters.from_date,
            to_date: filters.to_date
        });

        setLoadingState();

        return fetch(endpoint("listBills") + "&" + query.toString(), {
            headers: {
                Accept: "application/json"
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed to load billing data.");
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== "success") {
                    throw new Error((payload && payload.message) || "Unable to load invoices.");
                }

                currentRows = Array.isArray(payload.data) ? payload.data : [];
                var pagination = payload.pagination || {};
                totalItems = Number(pagination.total || 0);
                totalPages = Math.max(1, Number(pagination.total_pages || 1));
                currentPage = Math.min(Math.max(1, Number(pagination.current_page || currentPage)), totalPages);

                renderRows(currentRows);
                renderPagination(totalPages);

                var showingStart = totalItems === 0 ? 0 : (currentPage - 1) * pageSize + 1;
                var showingEnd = totalItems === 0 ? 0 : Math.min(currentPage * pageSize, totalItems);
                showingText.textContent = "Showing " + showingStart + "-" + showingEnd + " of " + totalItems + " invoices";
            })
            .catch(function (error) {
                setErrorState(error.message || "Error loading invoices.");
            });
    }

    function renderRows(pageData) {
        if (pageData.length === 0) {
            tableBody.innerHTML =
                '<tr class="rd-empty-row"><td colspan="7">No invoices found for the selected filters.</td></tr>';
            return;
        }

        tableBody.innerHTML = pageData
            .map(function (item) {
                return (
                    '<tr data-appointment-id="' + item.appointmentNumericId + '" data-bill-id="' + item.billId + '">' +
                    '<td><span class="rd-appointment-id">' + item.appointmentId + "</span></td>" +
                    '<td><div class="rd-patient-cell"><span class="rd-patient-initials">' +
                    initials(item.patientName) +
                    "</span>" +
                    escapeHtml(item.patientName) +
                    "</div></td>" +
                    '<td class="rd-money-cell rd-money-total">' + formatCurrency(item.totalAmount) + "</td>" +
                    '<td class="rd-money-cell rd-money-paid">' + formatCurrency(item.amountPaid) + "</td>" +
                    '<td class="rd-payment-method">' + escapeHtml(item.paymentMethod) + "</td>" +
                    '<td><span class="rd-financial-status is-' + escapeHtml(item.statusKey) + '">' + escapeHtml(item.financialStatus) + "</span></td>" +
                    '<td class="rd-detail-cell"><div class="rd-finance-actions">' +
                    '<button type="button" class="rd-icon-btn rd-bell-btn" data-action="notify" title="Send Reminder" aria-label="Send Reminder">' +
                    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 .2 1 1H6l1-1V11a5 5 0 1 1 10 0v5.2Z"></path></svg>' +
                    '</button>' +
                    '<button type="button" class="rd-icon-btn rd-delete-btn" data-action="delete" title="Delete" aria-label="Delete Bill">' +
                    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 3h6l1 2h4v2H4V5h4l1-2Zm1 6h2v9h-2V9Zm4 0h2v9h-2V9ZM7 9h2v9H7V9Z"></path></svg>' +
                    '</button>' +
                    '<button type="button" class="rd-view-btn rd-manage-btn" data-action="manage">Manage</button>' +
                    '</div></td>' +
                    "</tr>"
                );
            })
            .join("");
    }

    function renderPagination(pageCount) {
        var safePageCount = Math.max(1, pageCount);
        var buttons = [];
        buttons.push(
            '<button type="button" class="rd-page-btn" data-page="prev" ' +
                (currentPage === 1 ? "disabled" : "") +
                '>\u2039</button>'
        );

        for (var i = 1; i <= safePageCount; i += 1) {
            buttons.push(
                '<button type="button" class="rd-page-btn ' +
                    (i === currentPage ? "is-active" : "") +
                    '" data-page="' +
                    i +
                    '">' +
                    i +
                    "</button>"
            );
        }

        buttons.push(
            '<button type="button" class="rd-page-btn" data-page="next" ' +
                (currentPage === safePageCount ? "disabled" : "") +
                '>\u203A</button>'
        );

        paginationNode.innerHTML = buttons.join("");
    }

    function resetFilters() {
        if (searchInput) {
            searchInput.value = "";
        }
        if (statusInput) {
            statusInput.value = "all";
        }
        if (paymentMethodInput) {
            paymentMethodInput.value = "all";
        }
        if (dateFromInput) {
            dateFromInput.value = "";
        }
        if (dateToInput) {
            dateToInput.value = "";
        }
        currentPage = 1;
        fetchBills();
    }

    function scheduleSearchFetch() {
        if (searchDebounceTimer) {
            window.clearTimeout(searchDebounceTimer);
        }

        searchDebounceTimer = window.setTimeout(function () {
            currentPage = 1;
            fetchBills();
        }, 350);
    }

    function attachEvents() {
        if (searchInput) {
            searchInput.addEventListener("input", function () {
                scheduleSearchFetch();
            });
        }

        [statusInput, paymentMethodInput, dateFromInput, dateToInput].forEach(function (node) {
            if (node) {
                node.addEventListener("change", function () {
                    applyDateRangeLimits();
                    currentPage = 1;
                    fetchBills();
                });
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener("click", resetFilters);
        }

        paginationNode.addEventListener("click", function (event) {
            var target = event.target;

            if (!(target instanceof HTMLElement) || !target.classList.contains("rd-page-btn") || target.disabled) {
                return;
            }

            var action = target.getAttribute("data-page");

            if (action === "prev") {
                currentPage = Math.max(1, currentPage - 1);
            } else if (action === "next") {
                currentPage = Math.min(totalPages, currentPage + 1);
            } else {
                currentPage = Number(action) || 1;
            }

            fetchBills();
        });

        tableBody.addEventListener("click", function (event) {
            var target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            var button = target.closest("button");
            if (!(button instanceof HTMLElement)) {
                return;
            }

            var row = button.closest("tr");
            if (!row) {
                return;
            }

            var action = button.getAttribute("data-action");
            var billId = Number(row.getAttribute("data-bill-id") || "0");
            var appointmentNumericId = Number(row.getAttribute("data-appointment-id") || "0");

            if (action === "notify") {
                if (billId <= 0) {
                    window.alert("Unable to identify bill for reminder.");
                    return;
                }

                postAction("sendReminder", { bill_id: billId })
                    .then(function (payload) {
                        window.alert((payload && payload.message) || "Reminder sent successfully.");
                        fetchBills();
                    })
                    .catch(function (error) {
                        window.alert(error.message || "Failed to send reminder.");
                    });
                return;
            }

            if (action === "delete") {
                if (billId <= 0) {
                    window.alert("Unable to identify bill for cancellation.");
                    return;
                }

                if (!window.confirm("Cancel this bill? This will mark it as Claim Submitted.")) {
                    return;
                }

                postAction("deleteBill", { bill_id: billId })
                    .then(function (payload) {
                        window.alert((payload && payload.message) || "Bill cancelled successfully.");
                        fetchBills();
                    })
                    .catch(function (error) {
                        window.alert(error.message || "Failed to cancel bill.");
                    });
                return;
            }

            if (action !== "manage") {
                return;
            }

            if (appointmentNumericId <= 0) {
                return;
            }

            var detailsUrl = "/lab_sync/index.php?controller=billingController&action=Register_billing&appointment_id=" +
                encodeURIComponent(appointmentNumericId);

            if (currentRole) {
                detailsUrl += "&role=" + encodeURIComponent(currentRole);
            }

            window.location.href = detailsUrl;
        });


    }

    function initFinancesDashboard() {
        queryNodes();

        if (!tableBody || !showingText || !paginationNode) {
            return false;
        }

        attachEvents();
        applyDateRangeLimits();
        fetchBills();
        return true;
    }

    if (!initFinancesDashboard()) {
        document.addEventListener("DOMContentLoaded", function onReady() {
            document.removeEventListener("DOMContentLoaded", onReady);
            if (!initFinancesDashboard()) {
                console.error("Finances dashboard failed to initialize: required table nodes are missing.");
            }
        });
    }
})();
