// Appointment form test catalog interactivity.
let selectedTests = {};
let latestSearchResults = [];
let testIndexById = {};
let testSearchDebounceTimer = null;
let activeSearchRequestId = 0;

document.addEventListener('DOMContentLoaded', function () {
    const appointmentForm = document.getElementById('createAppointmentForm');
    if (!appointmentForm) return;

    const testCatalogSearch = document.getElementById('test-catalog-search');
    const testCatalogTableBody = document.getElementById('testCatalogTableBody');
    const selectedTestsTableBody = document.getElementById('selectedTestsTableBody');
    const timeSlotsContainer = document.querySelector('.time-slots-container');
    const appointmentTimeInput = document.getElementById('appointment-time');
    const appointmentDateInput = document.getElementById('appointment-date');
    const selectedAppointmentDate = document.getElementById('selected-appointment-date');
    const selectedAppointmentTime = document.getElementById('selected-appointment-time');
    const todayDateButton = document.getElementById('set-today-date');
    const cancelButton = document.getElementById('cancel');

    initializeDateDisplay(appointmentDateInput, selectedAppointmentDate);
    initializeTodayDateButton(todayDateButton, appointmentDateInput);
    initializeTimeSlots(timeSlotsContainer, appointmentTimeInput, appointmentDateInput, selectedAppointmentTime);
    initializeTestActions(testCatalogTableBody, selectedTestsTableBody);
    initializeSearch(testCatalogSearch);
    initializeFormSubmit(appointmentForm, appointmentTimeInput);
    initializeCancel(cancelButton);

    clearTestCatalogResults();
    setTestResultsMuted(true);
    setTestSearchHint('Type at least 3 characters to search for a test.');
    updateSelectedTestsCount();
    updateSelectedTestsHiddenInput();
});

function initializeDateDisplay(appointmentDateInput, selectedAppointmentDate) {
    if (!appointmentDateInput || !selectedAppointmentDate) return;

    const syncDisplay = function () {
        if (!appointmentDateInput.value) {
            selectedAppointmentDate.textContent = 'Not selected';
            return;
        }

        selectedAppointmentDate.textContent = formatForDisplay(appointmentDateInput.value);
    };

    appointmentDateInput.addEventListener('change', syncDisplay);
    appointmentDateInput.addEventListener('input', syncDisplay);
    syncDisplay();
}

function initializeTodayDateButton(todayDateButton, appointmentDateInput) {
    if (!todayDateButton || !appointmentDateInput) return;

    todayDateButton.addEventListener('click', function (e) {
        e.preventDefault();
        appointmentDateInput.value = getTodayDateString();
        appointmentDateInput.dispatchEvent(new Event('change', { bubbles: true }));
        appointmentDateInput.focus();
    });
}

function initializeTimeSlots(timeSlotsContainer, appointmentTimeInput, appointmentDateInput, selectedAppointmentTime) {
    if (!timeSlotsContainer || !appointmentTimeInput) return;

    const slotTimes = ['08:00 AM', '08:30 AM', '11:00 AM', '01:30 PM', '02:45 PM', '04:00 PM'];

    const renderSlots = function () {
        const selectedDate = appointmentDateInput ? appointmentDateInput.value : '';
        const isTodaySelected = selectedDate === getTodayDateString();
        const nowMinutes = getCurrentMinutes();

        const available = slotTimes.filter(label => {
            if (!isTodaySelected) {
                return true;
            }

            return convertTimeLabelToMinutes(label) > nowMinutes;
        });

        const slotsToRender = ['NOW'].concat(available);
        const leftColumnSlots = [];
        const rightColumnSlots = [];

        slotsToRender.forEach((slot, index) => {
            if (index % 2 === 0) {
                leftColumnSlots.push(slot);
            } else {
                rightColumnSlots.push(slot);
            }
        });

        const renderButton = function (slotValue) {
            const label = slotValue === 'NOW' ? 'NOW' : slotValue;
            return `<button type="button" class="time-slot" data-time-value="${label}">${label}</button>`;
        };

        let html = '<div class="time-slot-column">' + leftColumnSlots.map(renderButton).join('') + '</div>';
        html += '<div class="time-slot-column">';

        if (rightColumnSlots.length === 0 && available.length === 0) {
            html += '<div class="empty-state">No remaining time slots for today</div>';
        } else {
            html += rightColumnSlots.map(renderButton).join('');
        }

        html += '</div>';
        timeSlotsContainer.innerHTML = html;
    };

    const setSelectedTimeDisplay = function (timeValue24, displayValue) {
        appointmentTimeInput.value = timeValue24;
        if (selectedAppointmentTime) {
            selectedAppointmentTime.textContent = displayValue || 'Not selected';
        }
    };

    renderSlots();

    if (appointmentDateInput) {
        appointmentDateInput.addEventListener('change', function () {
            renderSlots();
            setSelectedTimeDisplay('', 'Not selected');
        });
    }

    timeSlotsContainer.addEventListener('click', function (e) {
        const clicked = e.target.closest('.time-slot');
        if (!clicked) return;

        e.preventDefault();

        timeSlotsContainer.querySelectorAll('.time-slot').forEach(slot => slot.classList.remove('active'));
        clicked.classList.add('active');

        if (clicked.dataset.timeValue === 'NOW') {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const time24 = hh + ':' + mm;
            const display = hh + ':' + mm;
            setSelectedTimeDisplay(time24, display);
            return;
        }

        const displayValue = clicked.dataset.timeValue;
        const time24 = convertTimeLabelTo24h(displayValue);
        setSelectedTimeDisplay(time24, displayValue);
    });
}

function getTodayDateString() {
    const now = new Date();
    const timezoneAdjusted = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
    return timezoneAdjusted.toISOString().split('T')[0];
}

function formatForDisplay(inputDateValue) {
    const [year, month, day] = String(inputDateValue).split('-');
    if (!year || !month || !day) {
        return 'Not selected';
    }

    return month + '/' + day + '/' + year;
}

function convertTimeLabelTo24h(label) {
    const parts = String(label).trim().split(' ');
    if (parts.length !== 2) return '';

    const hm = parts[0].split(':');
    let hours = parseInt(hm[0], 10);
    const minutes = parseInt(hm[1], 10);
    const period = parts[1].toUpperCase();

    if (period === 'PM' && hours !== 12) hours += 12;
    if (period === 'AM' && hours === 12) hours = 0;

    return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
}

function convertTimeLabelToMinutes(label) {
    const value24 = convertTimeLabelTo24h(label);
    if (!value24) return -1;
    const [hh, mm] = value24.split(':');
    return Number(hh) * 60 + Number(mm);
}

function getCurrentMinutes() {
    const now = new Date();
    return now.getHours() * 60 + now.getMinutes();
}

function initializeTestActions(testCatalogTableBody, selectedTestsTableBody) {
    if (testCatalogTableBody) {
        testCatalogTableBody.addEventListener('click', function (e) {
            const addButton = e.target.closest('button[data-action="add-test"]');
            if (!addButton) return;

            const testId = addButton.dataset.testId;
            addTestToSelection(testId);
        });
    }

    if (selectedTestsTableBody) {
        selectedTestsTableBody.addEventListener('click', function (e) {
            const removeButton = e.target.closest('button[data-action="remove-test"]');
            if (!removeButton) return;

            const testId = removeButton.dataset.testId;
            removeTestFromSelection(testId);
        });
    }
}

function initializeSearch(testCatalogSearch) {
    if (!testCatalogSearch) return;

    const MIN_QUERY = 3;
    const DEBOUNCE_MS = 300;

    testCatalogSearch.addEventListener('input', function () {
        const query = this.value.trim();

        if (testSearchDebounceTimer) {
            clearTimeout(testSearchDebounceTimer);
            testSearchDebounceTimer = null;
        }

        if (query.length < MIN_QUERY) {
            activeSearchRequestId += 1;
            latestSearchResults = [];
            testIndexById = {};
            clearTestCatalogResults();
            setTestResultsMuted(true);
            setTestSearchHint('Type at least ' + MIN_QUERY + ' characters to search for a test.');
            return;
        }

        setTestResultsMuted(false);
        setTestSearchHint('');
        showSearchState();

        testSearchDebounceTimer = setTimeout(function () {
            searchTestsFromBackend(query);
        }, DEBOUNCE_MS);
    });
}

async function searchTestsFromBackend(query) {
    const requestId = ++activeSearchRequestId;

    try {
        const endpoint = '/lab_sync/index.php?controller=appointmentsController&action=searchTests&q=' + encodeURIComponent(query);
        const res = await fetch(endpoint, { headers: { Accept: 'application/json' } });

        if (requestId !== activeSearchRequestId) {
            return;
        }

        if (!res.ok) {
            throw new Error('Unable to search tests.');
        }

        const payload = await res.json();
        if (requestId !== activeSearchRequestId) {
            return;
        }

        latestSearchResults = normalizeTestsPayload(payload);
        rebuildTestIndex(latestSearchResults);
        setTestResultsMuted(false);

        if (latestSearchResults.length === 0) {
            showEmptyState('No tests found for your search.');
            return;
        }

        displayTestCatalog(latestSearchResults);
    } catch (error) {
        if (requestId !== activeSearchRequestId) {
            return;
        }

        latestSearchResults = [];
        testIndexById = {};
        setTestResultsMuted(true);
        showEmptyState('Could not load tests from server.');
        console.error('Failed to search tests for appointment form:', error);
    }
}

function initializeFormSubmit(appointmentForm, appointmentTimeInput) {
    appointmentForm.addEventListener('submit', function (e) {
        const patientIdField = document.getElementById('patient_id');
        const dateField = document.getElementById('appointment-date');

        if (!patientIdField || !patientIdField.value) {
            e.preventDefault();
            alert('Please select a patient');
            return false;
        }

        if (!dateField || !dateField.value) {
            e.preventDefault();
            alert('Please select an appointment date');
            return false;
        }

        if (!appointmentTimeInput || !appointmentTimeInput.value) {
            e.preventDefault();
            alert('Please select a time slot');
            return false;
        }

        if (Object.keys(selectedTests).length === 0) {
            e.preventDefault();
            alert('Please select at least one test');
            return false;
        }

        updateSelectedTestsHiddenInput();
        return true;
    });
}

function initializeCancel(cancelButton) {
    if (!cancelButton) return;

    cancelButton.addEventListener('click', function (e) {
        e.preventDefault();
        window.history.back();
    });
}

function normalizeTestsPayload(payload) {
    const records = payload && payload.status === 'success' && Array.isArray(payload.data)
        ? payload.data
        : (Array.isArray(payload) ? payload : []);

    return records
        .map(record => normalizeTestRecord(record))
        .filter(test => test.id !== '');
}

function normalizeTestRecord(record) {
    const rawId = record && (record.test_id ?? record.id ?? record.test_code ?? record.code);
    const id = rawId === undefined || rawId === null ? '' : String(rawId);
    const rawCode = record && (record.test_code ?? record.code ?? null);
    const category = (record && record.category ? String(record.category) : 'GENERAL').toUpperCase();

    return {
        id,
        code: getTestCodeLabel(rawCode !== null && rawCode !== undefined && String(rawCode).trim() !== '' ? rawCode : id),
        name: record && (record.test_name ?? record.name) ? String(record.test_name ?? record.name) : 'Unnamed Test',
        description: record && record.description ? String(record.description) : 'No description available',
        category,
        price: record && record.price !== undefined && record.price !== null ? Number(record.price) : null
    };
}

function displayTestCatalog(tests) {
    const tableBody = document.getElementById('testCatalogTableBody');
    if (!tableBody) return;

    if (tests.length === 0) {
        tableBody.innerHTML = '<li class="empty-state">No tests found</li>';
        return;
    }

    tableBody.innerHTML = tests.map(test => {
        const isSelected = Boolean(selectedTests[test.id]);
        const priceText = Number.isFinite(test.price) ? '$' + Number(test.price).toFixed(2) : '—';
        const categoryClass = escapeHtml(test.category.toLowerCase().replace(/\s+/g, '-'));
        return `
            <li class="test-result-row" data-test-id="${escapeHtml(test.id)}">
                <span class="test-code-badge">${escapeHtml(test.code)}</span>
                <div class="test-result-info">
                    <span class="test-result-name">${escapeHtml(test.name)}</span>
                    <span class="test-result-category ${categoryClass}">${escapeHtml(test.category)}</span>
                </div>
                <span class="test-result-price">${escapeHtml(priceText)}</span>
                <button
                    type="button"
                    class="test-add-circle-btn ${isSelected ? 'added' : ''}"
                    data-action="add-test"
                    data-test-id="${escapeHtml(test.id)}"
                    ${isSelected ? 'disabled' : ''}
                    aria-label="Add ${escapeHtml(test.name)}"
                >+</button>
            </li>
        `;
    }).join('');
}


function addTestToSelection(testId) {
    const test = testIndexById[String(testId)];
    if (!test) return;

    if (selectedTests[test.id]) {
        return;
    }

    selectedTests[test.id] = test;
    updateSelectedTestsDisplay();
    refreshSelectableButtons();
}

function removeTestFromSelection(testId) {
    const normalizedId = String(testId);
    if (!selectedTests[normalizedId]) {
        return;
    }

    delete selectedTests[normalizedId];
    updateSelectedTestsDisplay();
    refreshSelectableButtons();
}

function refreshSelectableButtons() {
    updateSelectedTestsCount();
    updateSelectedTestsHiddenInput();

    const searchTerm = (document.getElementById('test-catalog-search')?.value || '').trim();
    if (searchTerm.length >= 3) {
        setTestResultsMuted(false);
        displayTestCatalog(latestSearchResults);
        return;
    }

    clearTestCatalogResults();
    setTestResultsMuted(true);
    setTestSearchHint('Type at least 3 characters to search for a test.');
}

function updateSelectedTestsDisplay() {
    const selectedList = document.getElementById('selectedTestsList');
    const tableBody = document.getElementById('selectedTestsTableBody');
    const selectedValues = Object.values(selectedTests);

    if (selectedValues.length === 0) {
        if (selectedList) selectedList.style.display = 'none';
        if (tableBody) tableBody.innerHTML = '';
        return;
    }

    if (selectedList) selectedList.style.display = 'block';

    if (tableBody) {
        tableBody.innerHTML = selectedValues.map(test => `
            <tr data-test-id="${escapeHtml(test.id)}">
                <td class="test-id">${escapeHtml(test.code)}</td>
                <td class="test-name-cell">
                    <div class="test-name-main">${escapeHtml(test.name)}</div>
                    <div class="test-name-desc">${escapeHtml(test.description)}</div>
                </td>
                <td>
                    <span class="test-category ${escapeHtml(test.category.toLowerCase())}">${escapeHtml(test.category)}</span>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="test-action-btn remove" data-action="remove-test" data-test-id="${escapeHtml(test.id)}">
                        Remove
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

function updateSelectedTestsCount() {
    const countBadge = document.getElementById('selectedTestsCount');
    if (countBadge) {
        countBadge.textContent = Object.keys(selectedTests).length;
    }
}

function updateSelectedTestsHiddenInput() {
    const hiddenInput = document.getElementById('selected_test_ids');
    if (hiddenInput) {
        hiddenInput.value = Object.keys(selectedTests).join(',');
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function rebuildTestIndex(tests) {
    testIndexById = {};
    tests.forEach(function (test) {
        testIndexById[String(test.id)] = test;
    });
}

function setTestSearchHint(message) {
    const hintEl = document.getElementById('test-search-hint');
    if (!hintEl) return;

    if (!message) {
        hintEl.textContent = '';
        hintEl.style.display = 'none';
        return;
    }

    hintEl.textContent = message;
    hintEl.style.display = 'block';
}

function setTestResultsMuted(isMuted) {
    const resultsEl = document.getElementById('testCatalogResults');
    if (!resultsEl) return;
    resultsEl.classList.toggle('is-muted', Boolean(isMuted));
}

function clearTestCatalogResults() {
    const tableBody = document.getElementById('testCatalogTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '';
}

function showSearchState() {
    const tableBody = document.getElementById('testCatalogTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '<li class="empty-state">Searching tests...</li>';
}

function showEmptyState(message) {
    const tableBody = document.getElementById('testCatalogTableBody');
    if (!tableBody) return;
    tableBody.innerHTML = '<li class="empty-state">' + escapeHtml(message || 'No tests found') + '</li>';
}

function getTestCodeLabel(rawId) {
    const value = String(rawId || '').trim();
    if (/^[0-9]+$/.test(value)) {
        return 'T-' + value.padStart(4, '0');
    }

    return value.toUpperCase();
}

