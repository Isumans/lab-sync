// Appointment form test catalog interactivity.
let selectedTests = {};
let allTests = [];
let latestTests = [];

document.addEventListener('DOMContentLoaded', function () {
    const appointmentForm = document.getElementById('createAppointmentForm');
    if (!appointmentForm) return;

    const testCatalogSearch = document.getElementById('test-catalog-search');
    const testCatalogTableBody = document.getElementById('testCatalogTableBody');
    const prominentTestsCards = document.getElementById('prominentTestsCards');
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
    initializeTestActions(testCatalogTableBody, prominentTestsCards, selectedTestsTableBody);
    initializeSearch(testCatalogSearch);
    initializeFormSubmit(appointmentForm, appointmentTimeInput);
    initializeCancel(cancelButton);

    loadTestsFromBackend();
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

function initializeTestActions(testCatalogTableBody, prominentTestsCards, selectedTestsTableBody) {
    if (testCatalogTableBody) {
        testCatalogTableBody.addEventListener('click', function (e) {
            const addButton = e.target.closest('button[data-action="add-test"]');
            if (!addButton) return;

            const testId = addButton.dataset.testId;
            addTestToSelection(testId);
        });
    }

    if (prominentTestsCards) {
        prominentTestsCards.addEventListener('click', function (e) {
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

    testCatalogSearch.addEventListener('input', function () {
        const searchTerm = this.value.trim().toLowerCase();
        const filteredTests = allTests.filter(test =>
            test.name.toLowerCase().includes(searchTerm) ||
            test.id.toLowerCase().includes(searchTerm) ||
            test.description.toLowerCase().includes(searchTerm) ||
            test.category.toLowerCase().includes(searchTerm)
        );

        displayTestCatalog(filteredTests);
    });
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

async function loadTestsFromBackend() {
    const tableBody = document.getElementById('testCatalogTableBody');
    const prominentTestsCards = document.getElementById('prominentTestsCards');

    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">Loading test catalog...</td></tr>';
    }

    if (prominentTestsCards) {
        prominentTestsCards.innerHTML = '<div class="empty-state">Loading latest tests...</div>';
    }

    try {
        const [allTestsResponse, topTestsResponse] = await Promise.all([
            fetch('/lab_sync/index.php?controller=TestCatalog&action=getTestsForAppointment', {
                headers: { 'Accept': 'application/json' }
            }),
            fetch('/lab_sync/index.php?controller=TestCatalog&action=getLatestTestsForAppointment', {
                headers: { 'Accept': 'application/json' }
            })
        ]);

        if (!allTestsResponse.ok || !topTestsResponse.ok) {
            throw new Error('Unable to fetch test catalog data.');
        }

        const allTestsPayload = await allTestsResponse.json();
        const topTestsPayload = await topTestsResponse.json();

        allTests = normalizeTestsPayload(allTestsPayload);
        latestTests = normalizeTestsPayload(topTestsPayload);

        displayTestCatalog(allTests);
        renderProminentTests(latestTests);
    } catch (error) {
        console.error('Failed to load tests for appointment form:', error);

        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">Could not load tests from server.</td></tr>';
        }

        if (prominentTestsCards) {
            prominentTestsCards.innerHTML = '<div class="empty-state">Could not load latest tests.</div>';
        }
    }
}

function normalizeTestsPayload(payload) {
    const records = Array.isArray(payload) ? payload : (payload && Array.isArray(payload.data) ? payload.data : []);

    return records
        .map(record => normalizeTestRecord(record))
        .filter(test => test.id !== '');
}

function normalizeTestRecord(record) {
    const rawId = record && (record.test_id ?? record.id);
    const id = rawId === undefined || rawId === null ? '' : String(rawId);
    const category = (record && record.category ? String(record.category) : 'GENERAL').toUpperCase();

    return {
        id,
        code: 'T-' + id.padStart(4, '0'),
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
        tableBody.innerHTML = '<tr><td colspan="4" class="empty-state">No tests found</td></tr>';
        return;
    }

    tableBody.innerHTML = tests.map(test => {
        const isSelected = Boolean(selectedTests[test.id]);
        return `
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
                    <button
                        type="button"
                        class="test-action-btn ${isSelected ? 'added' : ''}"
                        data-action="add-test"
                        data-test-id="${escapeHtml(test.id)}"
                        ${isSelected ? 'disabled' : ''}
                    >
                        ${isSelected ? 'Added' : '+ Add'}
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderProminentTests(tests) {
    const cardsContainer = document.getElementById('prominentTestsCards');
    if (!cardsContainer) return;

    if (tests.length === 0) {
        cardsContainer.innerHTML = '<div class="empty-state">No prominent tests available.</div>';
        return;
    }

    cardsContainer.innerHTML = tests.map(test => {
        const isSelected = Boolean(selectedTests[test.id]);
        const priceText = Number.isFinite(test.price) ? 'UGX ' + test.price.toLocaleString() : 'Price not set';
        return `
            <article class="prominent-test-card" data-test-id="${escapeHtml(test.id)}">
                <div class="prominent-test-top">
                    <span class="prominent-test-id">${escapeHtml(test.code)}</span>
                    <span class="test-category ${escapeHtml(test.category.toLowerCase())}">${escapeHtml(test.category)}</span>
                </div>
                <h5 class="prominent-test-name">${escapeHtml(test.name)}</h5>
                <p class="prominent-test-desc">${escapeHtml(test.description)}</p>
                <div class="prominent-test-footer">
                    <span class="prominent-test-price">${escapeHtml(priceText)}</span>
                    <button
                        type="button"
                        class="test-action-btn ${isSelected ? 'added' : ''}"
                        data-action="add-test"
                        data-test-id="${escapeHtml(test.id)}"
                        ${isSelected ? 'disabled' : ''}
                    >
                        ${isSelected ? 'Added' : '+ Add'}
                    </button>
                </div>
            </article>
        `;
    }).join('');
}

function addTestToSelection(testId) {
    const test = allTests.find(item => item.id === String(testId));
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

    const searchTerm = (document.getElementById('test-catalog-search')?.value || '').trim().toLowerCase();
    const filteredTests = allTests.filter(test =>
        test.name.toLowerCase().includes(searchTerm) ||
        test.id.toLowerCase().includes(searchTerm) ||
        test.description.toLowerCase().includes(searchTerm) ||
        test.category.toLowerCase().includes(searchTerm)
    );
    displayTestCatalog(filteredTests);
    renderProminentTests(latestTests);
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

