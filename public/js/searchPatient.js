// ── Patient Search with Debounce ──────────────────────────────────────────────
(function () {
    const searchInput = document.getElementById('patient-search');
    const suggestionsBox = document.getElementById('patient-suggestions');
    const searchBy = document.getElementById('patient-search-by');
    const patientIdInput = document.getElementById('patient_id');
    const createAppointmentForm = document.getElementById('createAppointmentForm');

    if (!searchInput || !suggestionsBox) {
        return;
    }

    let validationMsg = document.getElementById('patient-search-validation');
    if (!validationMsg) {
        validationMsg = document.createElement('p');
        validationMsg.id = 'patient-search-validation';
        validationMsg.className = 'patient-search-validation-msg';
        searchInput.parentNode.insertBefore(validationMsg, searchInput.nextSibling);
    }

    let selectedCard = document.getElementById('selected-patient-card');
    if (!selectedCard) {
        selectedCard = document.createElement('div');
        selectedCard.id = 'selected-patient-card';
        selectedCard.className = 'selected-patient-card';
        selectedCard.style.display = 'none';
        suggestionsBox.parentNode.insertBefore(selectedCard, suggestionsBox.nextSibling);
    }

    let debounceTimer = null;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showValidationError(message) {
        validationMsg.textContent = message;
        validationMsg.style.display = 'block';
        searchInput.classList.add('input-error');
    }

    function clearValidationError() {
        validationMsg.textContent = '';
        validationMsg.style.display = 'none';
        searchInput.classList.remove('input-error');
    }

    function clearSelection() {
        if (patientIdInput) {
            patientIdInput.value = '';
        }
        selectedCard.innerHTML = '';
        selectedCard.style.display = 'none';
        searchInput.classList.remove('input-success');
    }

    function selectPatient(id, name, email) {
        if (patientIdInput) {
            patientIdInput.value = id;
        }

        searchInput.value = name;
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'none';
        clearValidationError();
        searchInput.classList.add('input-success');

        selectedCard.innerHTML =
            '<div class="selected-patient-inner">' +
                '<span class="selected-patient-icon">OK</span>' +
                '<div class="selected-patient-info">' +
                    '<span class="selected-patient-name">' + escapeHtml(name) + '</span>' +
                    '<span class="selected-patient-email">' + escapeHtml(email) + '</span>' +
                '</div>' +
                '<button type="button" class="clear-patient-btn" title="Clear selection">x</button>' +
            '</div>';

        selectedCard.style.display = 'block';

        const clearBtn = selectedCard.querySelector('.clear-patient-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                clearSelection();
                searchInput.value = '';
                searchInput.focus();
            });
        }
    }

    function renderSuggestions(patients) {
        if (!Array.isArray(patients) || patients.length === 0) {
            suggestionsBox.innerHTML =
                '<div class="patient-suggestion-no-result">No patients found matching your search.</div>';
            suggestionsBox.style.display = 'block';
            return;
        }

        suggestionsBox.innerHTML = patients
            .map((p) => {
                const id = escapeHtml(String(p.id ?? ''));
                const name = escapeHtml(String(p.name ?? ''));
                const email = escapeHtml(String(p.email ?? ''));
                return (
                    '<div class="patient-suggestion-item" data-id="' + id + '" data-name="' + name + '" data-email="' + email + '">' +
                        '<div class="suggestion-info">' +
                            '<span class="suggestion-name">' + name + '</span>' +
                            '<span class="suggestion-id">' + email + '</span>' +
                        '</div>' +
                        '<span class="suggestion-select-hint">Select</span>' +
                    '</div>'
                );
            })
            .join('');

        suggestionsBox.style.display = 'block';
    }

    function clearDebounce() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
    }

    async function fetchPatients(query) {
        const type = searchBy ? searchBy.value : 'patient_name';

        try {
            const url = '/lab_sync/index.php?controller=appointmentsController&action=searchPatients' +
                '&type=' + encodeURIComponent(type) +
                '&query=' + encodeURIComponent(query);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Network error: ' + response.status);
            }

            const patients = await response.json();
            patients.sort((a, b) => String(a.name ?? '').localeCompare(String(b.name ?? '')));
            renderSuggestions(patients);
        } catch (error) {
            console.error('Error fetching patient data:', error);
            suggestionsBox.innerHTML =
                '<div class="patient-suggestion-no-result">Could not load results. Please try again.</div>';
            suggestionsBox.style.display = 'block';
        }
    }

    searchInput.addEventListener('input', () => {
        clearDebounce();
        clearSelection();
        clearValidationError();

        const query = searchInput.value.trim();
        if (query.length < 3) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.style.display = 'none';
            if (query.length > 0) {
                showValidationError('Type at least 3 characters to search for a patient.');
            }
            return;
        }

        suggestionsBox.innerHTML = '<div class="patient-suggestion-loading">Searching...</div>';
        suggestionsBox.style.display = 'block';
        debounceTimer = setTimeout(() => fetchPatients(query), 300);
    });

    suggestionsBox.addEventListener('click', (event) => {
        const item = event.target.closest('.patient-suggestion-item');
        if (!item || !item.dataset.id) {
            return;
        }

        selectPatient(
            item.dataset.id,
            item.dataset.name ?? '',
            item.dataset.email ?? ''
        );
    });

    document.addEventListener('click', (event) => {
        if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target)) {
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
