document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('prescriptionRequestManageModal');
    if (!modal) {
        return;
    }

    var closeBtn = document.getElementById('prxManageClose');
    var cancelBtn = document.getElementById('prxManageCancel');
    var saveBtn = document.getElementById('prxManageSave');
    var alertBox = document.getElementById('prxManageAlert');

    var typeBadge = document.getElementById('prxManageTypeBadge');
    var titleEl = document.getElementById('prxManageTitle');
    var subtitleEl = document.getElementById('prxManageSubtitle');

    var patientMetaEl = document.getElementById('prxPatientMeta');
    var prescriptionPanelEl = document.getElementById('prxPrescriptionPanel');
    var statusInfoEl = document.getElementById('prxStatusInfo');
    var totalsEl = document.getElementById('prxTotals');

    var testSearchInput = document.getElementById('prxTestSearch');
    var testSearchResults = document.getElementById('prxTestSearchResults');
    var selectedTestsEl = document.getElementById('prxSelectedTests');
    var addCustomBtn = document.getElementById('prxAddCustomTest');

    var appConfig = window.LAB_SYNC_CONFIG || {};
    var baseUrl = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');
    var detailsEndpoint = baseUrl + '/index.php?controller=appointmentsController&action=getPrescriptionRequestManageData';
    var saveEndpoint = baseUrl + '/index.php?controller=appointmentsController&action=savePrescriptionRequestManagement';
    var testSearchEndpoint = baseUrl + '/index.php?controller=appointmentsController&action=searchTests';

    var activeRequest = null;
    var selectedTests = [];
    var searchTimer = null;
    var isSaving = false;

    function getCsrfToken() {
        if (appConfig && typeof appConfig.csrfToken === 'string' && appConfig.csrfToken !== '') {
            return appConfig.csrfToken;
        }

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        return csrfMeta ? String(csrfMeta.getAttribute('content') || '') : '';
    }

    function safe(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatLkr(value) {
        var amount = Number(value || 0);
        return 'LKR ' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function setModalOpen(open) {
        modal.classList.toggle('is-open', open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';

        if (!open) {
            hideAlert();
            clearSearchResults();
        }
    }

    function showAlert(message, type) {
        alertBox.className = 'prx-manage-alert is-' + (type || 'error');
        alertBox.textContent = message;
        alertBox.hidden = false;
    }

    function hideAlert() {
        alertBox.hidden = true;
        alertBox.textContent = '';
        alertBox.className = 'prx-manage-alert';
    }

    function clearSearchResults() {
        testSearchResults.hidden = true;
        testSearchResults.innerHTML = '';
    }

    function renderStatusInfo(request) {
        var statusLabel = safe(request.status || 'Pending');
        var typeLabel = safe(request.request_type_label || 'Onsite');
        var preferredDate = safe(request.preferred_date || '-');
        var preferredTime = safe(request.preferred_time || '-');
        var collectionAddress = request.home_collection ? safe(request.collection_address || '-') : 'N/A';

        statusInfoEl.innerHTML = [
            '<div><span class="prx-label">Status</span><strong>' + statusLabel + '</strong></div>',
            '<div><span class="prx-label">Type</span><strong>' + typeLabel + '</strong></div>',
            '<div><span class="prx-label">Preferred Date</span><strong>' + preferredDate + '</strong></div>',
            '<div><span class="prx-label">Preferred Time</span><strong>' + preferredTime + '</strong></div>',
            '<div><span class="prx-label">Collection Address</span><strong>' + collectionAddress + '</strong></div>'
        ].join('');
    }

    function renderPatientMeta(request) {
        var addressValue = request.home_collection ? (request.collection_address || request.patient_address || '-') : (request.patient_address || '-');

        patientMetaEl.innerHTML = [
            '<div><span class="prx-label">Full Name</span><strong>' + safe(request.patient_name || '-') + '</strong></div>',
            '<div><span class="prx-label">Phone</span><strong>' + safe(request.contact_number || '-') + '</strong></div>',
            '<div><span class="prx-label">Email</span><strong>' + safe(request.email || '-') + '</strong></div>',
            '<div><span class="prx-label">Address</span><strong>' + safe(addressValue) + '</strong></div>'
        ].join('');
    }

    function renderPrescriptionPanel(request) {
        var hasPrescription = Number(request.prescription_available || 0) === 1;
        var imagePath = String(request.prescription_file_path || '').trim();
        var notes = safe(request.notes || '');
        var symptoms = safe(request.symptoms || '');

        if (hasPrescription && imagePath !== '') {
            var normalizedImagePath = String(imagePath).replace(/^\/?lab_sync\//, '').replace(/^\/+/, '');
            var imageUrl = baseUrl + '/' + normalizedImagePath;
            prescriptionPanelEl.innerHTML = [
                '<div class="prx-prescription-frame">',
                '<img src="' + safe(imageUrl) + '" alt="Prescription image">',
                '</div>',
                '<div class="prx-prescription-copy">',
                notes ? ('<p><span class="prx-label">Notes</span>' + notes + '</p>') : '',
                symptoms ? ('<p><span class="prx-label">Symptoms</span>' + symptoms + '</p>') : '',
                '</div>'
            ].join('');
            return;
        }

        prescriptionPanelEl.innerHTML = [
            '<div class="prx-prescription-empty">',
            '<p>No prescription image uploaded for this request.</p>',
            '<p>This request follows the no-prescription workflow.</p>',
            '</div>',
            '<div class="prx-prescription-copy">',
            notes ? ('<p><span class="prx-label">Notes</span>' + notes + '</p>') : '',
            symptoms ? ('<p><span class="prx-label">Symptoms</span>' + symptoms + '</p>') : '',
            '</div>'
        ].join('');
    }

    function calcTotals() {
        var total = 0;
        selectedTests.forEach(function (test) {
            total += Number(test.line_total || test.unit_price || 0);
        });
        return total;
    }

    function renderTotals() {
        totalsEl.innerHTML = '<strong>Total Tests: ' + selectedTests.length + '</strong> <span>' + formatLkr(calcTotals()) + '</span>';
    }

    function renderSelectedTests() {
        if (selectedTests.length === 0) {
            selectedTestsEl.innerHTML = '<div class="prx-empty-row">No tests selected yet.</div>';
            renderTotals();
            return;
        }

        selectedTestsEl.innerHTML = selectedTests.map(function (test) {
            return [
                '<div class="prx-test-row" data-test-id="' + Number(test.test_id || 0) + '">',
                '<div>',
                '<strong>' + safe(test.test_name || 'Unknown test') + '</strong>',
                '</div>',
                '<div class="prx-test-price">' + formatLkr(test.line_total || test.unit_price || 0) + '</div>',
                '<button type="button" class="prx-remove-test" aria-label="Remove test">×</button>',
                '</div>'
            ].join('');
        }).join('');

        renderTotals();
    }

    function addOrUpdateTest(test) {
        var targetId = Number(test.test_id || 0);
        if (!targetId) {
            return;
        }

        var existing = selectedTests.some(function (item) {
            return Number(item.test_id) === targetId;
        });

        if (existing) {
            return;
        }

        var unitPrice = Number(test.unit_price != null ? test.unit_price : test.price || 0);
        selectedTests.push({
            test_id: targetId,
            test_name: String(test.test_name || 'Unknown test'),
            unit_price: unitPrice,
            line_total: unitPrice,
            quantity: 1
        });
        renderSelectedTests();
    }

    function removeTest(testId) {
        var idValue = Number(testId || 0);
        selectedTests = selectedTests.filter(function (item) {
            return Number(item.test_id) !== idValue;
        });
        renderSelectedTests();
    }

    function renderSearchResults(items) {
        if (!Array.isArray(items) || items.length === 0) {
            testSearchResults.hidden = false;
            testSearchResults.innerHTML = '<div class="prx-search-empty">No matching tests found.</div>';
            return;
        }

        testSearchResults.hidden = false;
        testSearchResults.innerHTML = items.map(function (item) {
            return [
                '<button type="button" class="prx-search-item" data-test-id="' + Number(item.test_id || 0) + '"',
                ' data-test-name="' + safe(item.test_name || '') + '" data-test-price="' + Number(item.price || 0) + '">',
                '<span>' + safe(item.test_name || 'Unknown test') + '</span>',
                '<span>' + formatLkr(item.price || 0) + '</span>',
                '</button>'
            ].join('');
        }).join('');
    }

    function fetchSearchResults(query) {
        clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function () {
            fetch(testSearchEndpoint + '&q=' + encodeURIComponent(query || ''), {
                headers: { Accept: 'application/json' }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Unable to search tests.');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    if (!payload || payload.status !== 'success' || !Array.isArray(payload.data)) {
                        throw new Error('Invalid test response.');
                    }
                    renderSearchResults(payload.data);
                })
                .catch(function () {
                    renderSearchResults([]);
                });
        }, 250);
    }

    function setSavingState(saving) {
        isSaving = saving;
        saveBtn.disabled = saving;
        saveBtn.textContent = saving ? 'Saving...' : 'Save & Send to Patient';
    }

    function buildSubtitle(request) {
        var bits = [];
        bits.push('Patient: ' + (request.patient_name || 'Unknown'));
        bits.push(request.request_type_label || 'Onsite');
        if (request.prescription_available) {
            bits.push('Prescription Available');
        } else {
            bits.push('No Prescription');
        }
        return bits.join(' · ');
    }

    function renderPayload(payload) {
        if (!payload || typeof payload !== 'object' || !payload.request || typeof payload.request !== 'object') {
            throw new Error('Invalid response payload. Missing request details.');
        }

        var request = payload.request || {};
        activeRequest = request;

        typeBadge.textContent = (request.request_type_label || 'Appointment Request').toUpperCase();
        titleEl.textContent = 'Request Details - #RX-' + String(request.request_id || '').padStart(5, '0');
        subtitleEl.textContent = buildSubtitle(request);

        selectedTests = Array.isArray(payload.tests) ? payload.tests.map(function (item) {
            return {
                test_id: Number(item.test_id || 0),
                test_name: String(item.test_name || 'Unknown test'),
                unit_price: Number(item.unit_price || 0),
                line_total: Number(item.line_total || 0),
                quantity: Number(item.quantity || 1)
            };
        }) : [];

        renderPatientMeta(request);
        renderPrescriptionPanel(request);
        renderStatusInfo(request);
        renderSelectedTests();
        hideAlert();
    }

    function openLoadingState() {
        typeBadge.textContent = 'Loading';
        titleEl.textContent = 'Request Details';
        subtitleEl.textContent = '';
        patientMetaEl.innerHTML = '<div class="prx-empty-row">Loading request...</div>';
        prescriptionPanelEl.innerHTML = '<div class="prx-empty-row">Loading prescription...</div>';
        statusInfoEl.innerHTML = '<div class="prx-empty-row">Loading status...</div>';
        selectedTestsEl.innerHTML = '<div class="prx-empty-row">Loading tests...</div>';
        totalsEl.innerHTML = '';
        setModalOpen(true);
    }

    function loadRequest(requestId) {
        openLoadingState();

        fetch(detailsEndpoint + '&request_id=' + encodeURIComponent(requestId), {
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Unable to load request details.');
                }
                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.status !== 'success' || !payload.data) {
                    throw new Error(payload && payload.message ? payload.message : 'Invalid response payload.');
                }
                renderPayload(payload.data);
            })
            .catch(function (error) {
                showAlert(error.message || 'Failed to load request.', 'error');
            });
    }

    function saveRequestManagement() {
        if (isSaving || !activeRequest) {
            return;
        }

        if (selectedTests.length === 0) {
            showAlert('Please select at least one test before saving.', 'error');
            return;
        }

        setSavingState(true);

        var payload = {
            request_id: Number(activeRequest.request_id || 0),
            preferred_date: activeRequest.preferred_date || '',
            preferred_time: activeRequest.preferred_time || '',
            collection_address: activeRequest.collection_address || '',
            tests: selectedTests.map(function (item) { return Number(item.test_id); }),
            note: 'Sent to patient from receptionist manage modal.'
        };

        fetch(saveEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken(),
                Accept: 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json().then(function (json) {
                    return {
                        ok: response.ok,
                        json: json
                    };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.json || result.json.status !== 'success') {
                    throw new Error(result.json && result.json.message ? result.json.message : 'Save failed.');
                }

                setModalOpen(false);
                document.dispatchEvent(new CustomEvent('prescription:refresh'));
            })
            .catch(function (error) {
                showAlert(error.message || 'Unable to save request.', 'error');
            })
            .finally(function () {
                setSavingState(false);
            });
    }

    function wireEvents() {
        document.addEventListener('prescription:view-more', function (event) {
            var requestId = event && event.detail ? Number(event.detail.request_id || 0) : 0;
            if (!requestId) {
                return;
            }
            loadRequest(requestId);
        });

        closeBtn.addEventListener('click', function () {
            setModalOpen(false);
        });

        cancelBtn.addEventListener('click', function () {
            setModalOpen(false);
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                setModalOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                setModalOpen(false);
            }
        });

        saveBtn.addEventListener('click', saveRequestManagement);

        selectedTestsEl.addEventListener('click', function (event) {
            var removeButton = event.target.closest('.prx-remove-test');
            if (!removeButton) {
                return;
            }
            var row = removeButton.closest('.prx-test-row');
            if (!row) {
                return;
            }
            removeTest(row.getAttribute('data-test-id'));
        });

        testSearchInput.addEventListener('input', function () {
            fetchSearchResults(testSearchInput.value || '');
        });

        testSearchResults.addEventListener('click', function (event) {
            var button = event.target.closest('.prx-search-item');
            if (!button) {
                return;
            }

            addOrUpdateTest({
                test_id: Number(button.getAttribute('data-test-id') || 0),
                test_name: button.getAttribute('data-test-name') || '',
                price: Number(button.getAttribute('data-test-price') || 0)
            });
            clearSearchResults();
            testSearchInput.value = '';
        });

        addCustomBtn.addEventListener('click', function () {
            testSearchInput.focus();
            fetchSearchResults(testSearchInput.value || '');
        });
    }

    wireEvents();
});
