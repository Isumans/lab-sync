// ── Patient Search with Debounce ──────────────────────────────────────────────
(function () {
    const searchInput = document.getElementById('patient-search');
    const suggestionsBox = document.getElementById('patient-suggestions');
    const searchBy = document.getElementById('patient-search-by');
    const patientIdInput = document.getElementById('patient_id');
    const createAppointmentForm = document.getElementById('createAppointmentForm');
// ── Patient Search with Debounce ──────────────────────────────────────────────
(function () {
    const searchInput = document.getElementById('patient-search');
    const suggestionsBox = document.getElementById('patient-suggestions');
    const searchBy = document.getElementById('patient-search-by');
    const patientIdInput = document.getElementById('patient_id');
    const createAppointmentForm = document.getElementById('createAppointmentForm');

    if (!searchInput || !suggestionsBox) return; // Guard: elements must exist

    // Validation error element (injected once, reused)
    let validationMsg = document.getElementById('patient-search-validation');
    if (!validationMsg) {
        validationMsg = document.createElement('p');
        validationMsg.id = 'patient-search-validation';
        validationMsg.className = 'patient-search-validation-msg';
        searchInput.parentNode.insertBefore(validationMsg, searchInput.nextSibling);
    }

    // Selected-patient card (shown after a patient is chosen)
    let selectedCard = document.getElementById('selected-patient-card');
    if (!selectedCard) {
        selectedCard = document.createElement('div');
        selectedCard.id = 'selected-patient-card';
        selectedCard.className = 'selected-patient-card';
        selectedCard.style.display = 'none';
        suggestionsBox.parentNode.insertBefore(selectedCard, suggestionsBox.nextSibling);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    function showValidationError(msg) {
        validationMsg.textContent = msg;
        validationMsg.style.display = 'block';
        searchInput.classList.add('input-error');
    }

    function clearValidationError() {
        validationMsg.style.display = 'none';
        validationMsg.textContent = '';
        searchInput.classList.remove('input-error');
        searchInput.classList.remove('input-success');
    }

    function clearSelection() {
        if (patientIdInput) patientIdInput.value = '';
        selectedCard.style.display = 'none';
        selectedCard.innerHTML = '';
        searchInput.classList.remove('input-success');
    }

    function selectPatient(id, name, email) {
        searchInput.value = name;
        if (patientIdInput) patientIdInput.value = id;
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'none';
        clearValidationError();

        // Render the selected-patient confirmation card
        selectedCard.innerHTML = `
            <div class="selected-patient-inner">
                <span class="selected-patient-icon">✓</span>
                <div class="selected-patient-info">
                    <span class="selected-patient-name">${escapeHtml(name)}</span>
                    <span class="selected-patient-email">${escapeHtml(email)}</span>
                </div>
                <button type="button" class="clear-patient-btn" title="Clear selection">✕</button>
            </div>`;
        selectedCard.style.display = 'block';
        searchInput.classList.add('input-success');

        selectedCard.querySelector('.clear-patient-btn').addEventListener('click', () => {
            clearSelection();
            searchInput.value = '';
            searchInput.focus();
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderSuggestions(patients) {
        if (patients.length === 0) {
            suggestionsBox.innerHTML = `
                <div class="patient-suggestion-no-result">
                    <span class="no-result-icon">🔍</span>
                    No patients found matching your search.
                </div>`;
        } else {
            suggestionsBox.innerHTML = patients.map(p => `
                <div class="patient-suggestion-item"
                     data-id="${escapeHtml(String(p.id))}"
                     data-name="${escapeHtml(p.name)}"
                     data-email="${escapeHtml(p.email)}">
                    <div class="suggestion-info">
                        <span class="suggestion-name">${escapeHtml(p.name)}</span>
                        <span class="suggestion-id">${escapeHtml(p.email)}</span>
                    </div>
                    <span class="suggestion-select-hint">Select →</span>
                </div>`).join('');
        }
        suggestionsBox.style.display = 'block';
    }

    // ── Debounce ───────────────────────────────────────────────────────────────
    let debounceTimer = null;

    searchInput.addEventListener('input', () => {
        clearDebounce();
        const query = searchInput.value.trim();

        // Clear selection when user types again
        clearSelection();
        clearValidationError();

        // Hide suggestions if below 3 chars
        if (query.length < 3) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
            if (query.length > 0) {
                showValidationError('Type at least 3 characters to search for a patient.');
            }
            return;
        }

        // Show loading state
        suggestionsBox.innerHTML = `<div class="patient-suggestion-loading">Searching…</div>`;
        suggestionsBox.style.display = 'block';

        debounceTimer = setTimeout(() => fetchPatients(query), 300);
    });

    function clearDebounce() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
    }

    async function fetchPatients(query) {
        const type = searchBy ? searchBy.value : 'patient_name';
        try {
            const url = `/lab_sync/index.php?controller=appointmentsController&action=searchPatients` +
                `&type=${encodeURIComponent(type)}&query=${encodeURIComponent(query)}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network error: ' + response.status);
            const patients = await response.json();

            // Sort alphabetically by name (LIKE result already filtered server-side)
            patients.sort((a, b) => a.name.localeCompare(b.name));
            renderSuggestions(patients);
        } catch (err) {
            console.error('Error fetching patient data:', err);
            suggestionsBox.innerHTML = `<div class="patient-suggestion-no-result">⚠ Could not load results. Please try again.</div>`;
        }
    }

    // ── Click: select a suggestion ─────────────────────────────────────────────
    suggestionsBox.addEventListener('click', (e) => {
        const item = e.target.closest('.patient-suggestion-item');
        if (item && item.dataset.id) {
            selectPatient(item.dataset.id, item.dataset.name, item.dataset.email);
        }
    });

    // ── Close suggestions on outside click ────────────────────────────────────
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    if (createAppointmentForm) {
        createAppointmentForm.addEventListener('submit', (event) => {
            if (!patientIdInput || !patientIdInput.value) {
                event.preventDefault();
                showValidationError('Please search for and select a patient before scheduling an appointment.');
                searchInput.focus();
            } else {
                clearValidationError();
            }
        });
    }
})();
