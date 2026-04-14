(function () {
    var pageSize = 7;
    var currentPage = 1;
    var totalPages = 1;
    var totalItems = 0;
    var currentRows = [];
    var searchDebounceTimer = null;

    var searchInput = null;
    var statusInput = null;
    var testTypeInput = null;
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
        var endpointUrl = "/lab_sync/index.php?controller=reportsController&action=" + encodeURIComponent(actionName);
        if (currentRole) {
            endpointUrl += "&role=" + encodeURIComponent(currentRole);
        }
        return endpointUrl;
    }

    function queryNodes() {
        searchInput = document.getElementById("rdSearch");
        statusInput = document.getElementById("rdStatus");
        testTypeInput = document.getElementById("rdTestType");
        dateFromInput = document.getElementById("rdDateFrom");
        dateToInput = document.getElementById("rdDateTo");
        clearBtn = document.getElementById("rdClearBtn");

        tableBody = document.getElementById("rdTableBody");
        showingText = document.getElementById("rdShowingText");
        paginationNode = document.getElementById("rdPagination");
    }

    function toDisplayDate(isoDate) {
        var dateObj = new Date(isoDate + "T00:00:00");
        return dateObj.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
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

    function progressClass(progress) {
        if (progress >= 95) {
            return "rd-progress-complete";
        }
        if (progress >= 50) {
            return "rd-progress-mid";
        }
        return "rd-progress-low";
    }

    function collectFilters() {
        function getValue(node, fallback) {
            return node ? node.value || fallback : fallback;
        }

        return {
            search: (getValue(searchInput, "") || "").trim(),
            status: (getValue(statusInput, "all") || "all").toLowerCase(),
            test_type: (getValue(testTypeInput, "all") || "all").toLowerCase(),
            from_date: getValue(dateFromInput, ""),
            to_date: getValue(dateToInput, "")
        };
    }

    function setLoadingState() {
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="5">Loading reports...</td></tr>';
    }

    function setErrorState(message) {
        tableBody.innerHTML = '<tr class="rd-empty-row"><td colspan="5">' + message + "</td></tr>";
        showingText.textContent = "Showing 0-0 of 0 reports";
        paginationNode.innerHTML = "";
    }

    function fetchReports() {
        var filters = collectFilters();
        var query = new URLSearchParams({
            page: String(currentPage),
            per_page: String(pageSize),
            search: filters.search,
            status: filters.status,
            test_type: filters.test_type,
            from_date: filters.from_date,
            to_date: filters.to_date
        });

        setLoadingState();

        return fetch(endpoint("listReports") + "&" + query.toString(), {
            headers: {
                Accept: "application/json"
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed to load reports data.");
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== "success") {
                    throw new Error((payload && payload.message) || "Unable to load reports.");
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
                showingText.textContent = "Showing " + showingStart + "-" + showingEnd + " of " + totalItems + " reports";
            })
            .catch(function (error) {
                setErrorState(error.message || "Error loading reports.");
            });
    }

    function renderRows(pageData) {
        if (pageData.length === 0) {
            tableBody.innerHTML =
                '<tr class="rd-empty-row"><td colspan="5">No reports found for the selected filters.</td></tr>';
            return;
        }

        tableBody.innerHTML = pageData
            .map(function (item) {
                return (
                    '<tr data-appointment-id="' + item.appointmentNumericId + '">' +
                    '<td><span class="rd-appointment-id">' + item.appointmentId + "</span></td>" +
                    '<td><div class="rd-patient-cell"><span class="rd-patient-initials">' +
                    initials(item.patientName) +
                    "</span>" +
                    item.patientName +
                    "</div></td>" +
                    "<td>" +
                    toDisplayDate(item.date) +
                    "</td>" +
                    '<td class="rd-progress-cell"><div class="rd-progress-top"><span class="rd-progress-label">' +
                    item.progress +
                    "% Complete</span><span class=\"rd-progress-count\">" +
                    item.completed +
                    "/" +
                    item.total +
                    "</span></div><div class=\"rd-progress-track\"><div class=\"rd-progress-bar " +
                    progressClass(item.progress) +
                    "\" style=\"width: " +
                    item.progress +
                    "%\"></div></div></td>" +
                    '<td class="rd-detail-cell"><a href="/lab_sync/index.php?controller=reportsController&action=details&appointment_id=' + item.appointmentNumericId + '"><button type="button" class="rd-view-btn">View Details</button></a></td>' +
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
        if (testTypeInput) {
            testTypeInput.value = "all";
        }
        if (dateFromInput) {
            dateFromInput.value = "";
        }
        if (dateToInput) {
            dateToInput.value = "";
        }
        currentPage = 1;
        fetchReports();
    }

    function scheduleSearchFetch() {
        if (searchDebounceTimer) {
            window.clearTimeout(searchDebounceTimer);
        }

        searchDebounceTimer = window.setTimeout(function () {
            currentPage = 1;
            fetchReports();
        }, 350);
    }

    function attachEvents() {
        if (searchInput) {
            searchInput.addEventListener("input", function () {
                scheduleSearchFetch();
            });
        }

        [statusInput, testTypeInput, dateFromInput, dateToInput].forEach(function (node) {
            if (node) {
                node.addEventListener("change", function () {
                    currentPage = 1;
                    fetchReports();
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

            fetchReports();
        });

        tableBody.addEventListener("click", function (event) {
            var target = event.target;
            if (!(target instanceof HTMLElement) || !target.classList.contains("rd-view-btn")) {
                return;
            }

            var row = target.closest("tr");
            if (!row) {
                return;
            }

            var appointmentNumericId = row.getAttribute("data-appointment-id");
            if (!appointmentNumericId) {
                return;
            }

            var detailsUrl = "/lab_sync/index.php?controller=reportsController&action=details&appointment_id=" +
                encodeURIComponent(appointmentNumericId);

            if (currentRole) {
                detailsUrl += "&role=" + encodeURIComponent(currentRole);
            }

            window.location.href = detailsUrl;
        });


    }

    function initReportsDashboard() {
        queryNodes();

        if (!tableBody || !showingText || !paginationNode) {
            return false;
        }

        attachEvents();
        fetchReports();
        return true;
    }

    if (!initReportsDashboard()) {
        document.addEventListener("DOMContentLoaded", function onReady() {
            document.removeEventListener("DOMContentLoaded", onReady);
            if (!initReportsDashboard()) {
                console.error("Reports dashboard failed to initialize: required table nodes are missing.");
            }
        });
    }
})();
