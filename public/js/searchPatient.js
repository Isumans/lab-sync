(function () {
    'use strict';

    var MIN_QUERY_LENGTH = 3;
    var DEBOUNCE_MS = 300;

    var searchInput = document.getElementById('patient-search');
    var suggestionsBox = document.getElementById('patient-suggestions');
    var searchBy = document.getElementById('patient-search-by');
    var patientIdInput = document.getElementById('patient_id');
    var createAppointmentForm = document.getElementById('createAppointmentForm');
    var createPatientTrigger = document.getElementById('create-new-patient-trigger');
    var createPatientModal = document.getElementById('createPatientModal');
    var createPatientForm = document.getElementById('createPatientModalForm');
    var createPatientAlert = document.getElementById('createPatientAlert');
    var createPatientClose = document.getElementById('createPatientClose');
    var createPatientCancel = document.getElementById('createPatientCancel');
    var createPatientSubmit = document.getElementById('createPatientSubmit');
    var createPatientCsrf = document.getElementById('createPatientCsrf');
    var createPatientEndpoint = '/lab_sync/index.php?controller=patientController&action=createPatient';
    var isCreatingPatient = false;

    if (!searchInput || !suggestionsBox || !createAppointmentForm) {
        return;
    }

    var debounceTimer = null;
    var activeRequestId = 0;

    var validationMsg = document.getElementById('patient-search-validation');
    if (!validationMsg) {
        validationMsg = document.createElement('p');
        validationMsg.id = 'patient-search-validation';
        validationMsg.className = 'patient-search-validation-msg';
        searchInput.insertAdjacentElement('afterend', validationMsg);
    }

    var selectedCard = document.getElementById('selected-patient-card');
    if (!selectedCard) {
        selectedCard = document.createElement('div');
        selectedCard.id = 'selected-patient-card';
        selectedCard.className = 'selected-patient-card';
        selectedCard.style.display = 'none';
        suggestionsBox.insertAdjacentElement('afterend', selectedCard);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showValidationError(message) {
        validationMsg.textContent = message;
        validationMsg.style.display = 'block';
        searchInput.classList.add('input-error');
        searchInput.classList.remove('input-success');
    }

    function clearValidationError() {
        validationMsg.textContent = '';
        validationMsg.style.display = 'none';
        searchInput.classList.remove('input-error');
    }

    function hideSuggestions() {
        suggestionsBox.innerHTML = '';
        suggestionsBox.style.display = 'none';
    }

    function clearCreatePatientAlert() {
        if (!createPatientAlert) {
            return;
        }

        createPatientAlert.hidden = true;
        createPatientAlert.textContent = '';
        createPatientAlert.className = 'appointment-edit-alert';
    }

    function showCreatePatientAlert(message, type) {
        if (!createPatientAlert) {
            return;
        }

        createPatientAlert.hidden = false;
        createPatientAlert.textContent = message;
        createPatientAlert.className = 'appointment-edit-alert ' + (type || 'error');
    }

    function setCreatePatientFieldError(fieldName, message) {
        if (!createPatientForm) {
            return;
        }

        var errorEl = createPatientForm.querySelector('[data-field-error="' + fieldName + '"]');
        if (!errorEl) {
            return;
        }

        var fieldWrap = errorEl.closest('.create-patient-field');
        if (fieldWrap) {
            fieldWrap.classList.add('is-error');
        }
        errorEl.textContent = message || '';
    }

    function clearCreatePatientFieldErrors() {
        if (!createPatientForm) {
            return;
        }

        var errorEls = createPatientForm.querySelectorAll('.create-patient-field-error');
        errorEls.forEach(function (errorEl) {
            errorEl.textContent = '';
        });

        var fieldWraps = createPatientForm.querySelectorAll('.create-patient-field');
        fieldWraps.forEach(function (fieldWrap) {
            fieldWrap.classList.remove('is-error');
        });
    }

    function setCreatePatientLoading(isLoading) {
        if (!createPatientSubmit || !createPatientForm) {
            return;
        }

        createPatientSubmit.disabled = isLoading;
        if (createPatientCancel) {
            createPatientCancel.disabled = isLoading;
        }
        if (createPatientClose) {
            createPatientClose.disabled = isLoading;
        }

        var fields = createPatientForm.querySelectorAll('input, select, button');
        fields.forEach(function (field) {
            if (field === createPatientSubmit || field === createPatientCancel || field === createPatientClose) {
                return;
            }
            field.disabled = isLoading;
        });

        createPatientSubmit.innerHTML = isLoading
            ? '<span aria-hidden="true">⏳</span> Creating...'
            : '<span aria-hidden="true">💾</span> Create Patient';
    }

    function setCreatePatientModalOpen(open) {
        if (!createPatientModal) {
            return;
        }

        createPatientModal.classList.toggle('is-open', open);
        createPatientModal.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.style.overflow = open ? 'hidden' : '';

        if (open) {
            if (createPatientCsrf && createAppointmentForm) {
                var csrfInput = createAppointmentForm.querySelector('input[name="csrf_token"]');
                if (csrfInput && csrfInput.value) {
                    createPatientCsrf.value = csrfInput.value;
                }
            }

            var nameInput = document.getElementById('createPatientName');
            if (nameInput && String(searchInput.value || '').trim() !== '') {
                nameInput.value = String(searchInput.value || '').trim();
            }
            if (nameInput) {
                nameInput.focus();
            }
        }
    }

    function resetCreatePatientForm() {
        if (!createPatientForm) {
            return;
        }

        createPatientForm.reset();
        clearCreatePatientFieldErrors();
        clearCreatePatientAlert();
        setCreatePatientLoading(false);
        isCreatingPatient = false;
    }

    function isValidDate(value) {
        return /^\d{4}-\d{2}-\d{2}$/.test(value);
    }

    function isValidPhone(value) {
        return /^[0-9+()\-\s]{7,25}$/.test(value);
    }

    function validateCreatePatientPayload(payload) {
        var errors = {};

        if (payload.patient_name === '' || payload.patient_name.length > 120) {
            errors.patient_name = 'Patient name is required and must be at most 120 characters.';
        }

        if (!isValidDate(payload.date_of_birth)) {
            errors.date_of_birth = 'Date of birth format is invalid.';
        } else {
            var selectedDate = new Date(payload.date_of_birth + 'T00:00:00');
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate.getTime() > today.getTime()) {
                errors.date_of_birth = 'Date of birth cannot be in the future.';
            }
        }

        if (['male', 'female', 'other'].indexOf(payload.gender) === -1) {
            errors.gender = 'Gender value is invalid.';
        }

        if (!isValidPhone(payload.contact_no)) {
            errors.contact_no = 'Contact number format is invalid.';
        }

        var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(payload.email);
        if (!emailOk || payload.email.length > 120) {
            errors.email = 'Email is invalid.';
        }

        return errors;
    }

    function clearSelection() {
        if (patientIdInput) {
            patientIdInput.value = '';
        }
        selectedCard.innerHTML = '';
        selectedCard.style.display = 'none';
        searchInput.classList.remove('input-success');
    }

    function renderSelectedPatient(patient) {
        selectedCard.innerHTML = [
            '<div class="selected-patient-inner">',
            '    <span class="selected-patient-icon">&#10003;</span>',
            '    <div class="selected-patient-info">',
            '        <span class="selected-patient-name">' + escapeHtml(patient.name) + '</span>',
            '        <span class="selected-patient-email">' + escapeHtml(patient.email || 'No email available') + '</span>',
            '    </div>',
            '    <button type="button" class="clear-patient-btn" title="Clear selection">&#10005;</button>',
            '</div>'
        ].join('');
        selectedCard.style.display = 'block';

        var clearButton = selectedCard.querySelector('.clear-patient-btn');
        if (clearButton) {
            clearButton.addEventListener('click', function () {
                clearSelection();
                searchInput.value = '';
                searchInput.focus();
            });
        }
    }

    function selectPatient(patient) {
        searchInput.value = patient.name || '';
        if (patientIdInput) {
            patientIdInput.value = patient.id || '';
        }
        clearValidationError();
        hideSuggestions();
        renderSelectedPatient(patient);
        searchInput.classList.add('input-success');
    }

    function renderSuggestions(patients) {
        if (!patients.length) {
            suggestionsBox.innerHTML = '' +
                '<div class="patient-suggestion-no-result">' +
                '    <span class="no-result-icon">&#128269;</span>' +
                '    No patients found matching your search.' +
                '</div>';
            suggestionsBox.style.display = 'block';
            return;
        }

        suggestionsBox.innerHTML = patients.map(function (patient) {
            return '' +
                '<button type="button" class="patient-suggestion-item"' +
                ' data-id="' + escapeHtml(patient.id) + '"' +
                ' data-name="' + escapeHtml(patient.name) + '"' +
                ' data-email="' + escapeHtml(patient.email || '') + '">' +
                '    <div class="suggestion-info">' +
                '        <span class="suggestion-name">' + escapeHtml(patient.name) + '</span>' +
                '        <span class="suggestion-id">' + escapeHtml(patient.email || 'No email available') + '</span>' +
                '    </div>' +
                '    <span class="suggestion-select-hint">Select &rarr;</span>' +
                '</button>';
        }).join('');

        suggestionsBox.style.display = 'block';
    }

    function fetchPatients(query, requestId) {
        var type = searchBy ? searchBy.value : 'patient_name';
        var url = '/lab_sync/index.php?controller=appointmentsController&action=searchPatients'
            + '&type=' + encodeURIComponent(type)
            + '&query=' + encodeURIComponent(query);

        suggestionsBox.innerHTML = '<div class="patient-suggestion-loading">Searching...</div>';
        suggestionsBox.style.display = 'block';

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed with status ' + response.status);
                }
                return response.json();
            })
            .then(function (patients) {
                if (requestId !== activeRequestId) {
                    return;
                }

                var normalizedPatients = Array.isArray(patients) ? patients : [];
                normalizedPatients.sort(function (left, right) {
                    return String(left.name || '').localeCompare(String(right.name || ''), undefined, { sensitivity: 'base' });
                });
                renderSuggestions(normalizedPatients);
            })
            .catch(function () {
                if (requestId !== activeRequestId) {
                    return;
                }
                suggestionsBox.innerHTML = '<div class="patient-suggestion-no-result">&#9888; Could not load results. Please try again.</div>';
                suggestionsBox.style.display = 'block';
            });
    }

    function scheduleSearch() {
        var query = String(searchInput.value || '').trim();

        clearSelection();
        clearValidationError();

        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }

        if (query.length === 0) {
            hideSuggestions();
            return;
        }

        if (query.length < MIN_QUERY_LENGTH) {
            hideSuggestions();
            showValidationError('Type at least ' + MIN_QUERY_LENGTH + ' characters to search for a patient.');
            return;
        }

        activeRequestId += 1;
        var requestId = activeRequestId;
        debounceTimer = setTimeout(function () {
            fetchPatients(query, requestId);
        }, DEBOUNCE_MS);
    }

    searchInput.addEventListener('input', scheduleSearch);

    if (searchBy) {
        searchBy.addEventListener('change', function () {
            clearSelection();
            clearValidationError();
            if (searchInput.value.trim().length >= MIN_QUERY_LENGTH) {
                scheduleSearch();
            } else {
                hideSuggestions();
            }
        });
    }

    suggestionsBox.addEventListener('click', function (event) {
        var item = event.target.closest('.patient-suggestion-item');
        if (!item) {
            return;
        }

        selectPatient({
            id: item.getAttribute('data-id') || '',
            name: item.getAttribute('data-name') || '',
            email: item.getAttribute('data-email') || ''
        });
    });

    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target) && !suggestionsBox.contains(event.target) && !selectedCard.contains(event.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    createAppointmentForm.addEventListener('submit', function (event) {
        if (!patientIdInput || !patientIdInput.value) {
            event.preventDefault();
            showValidationError('Please search for and select a patient before scheduling an appointment.');
            searchInput.focus();
        } else {
            clearValidationError();
        }
    });

    if (createPatientTrigger && createPatientModal && createPatientForm) {
        createPatientTrigger.addEventListener('click', function () {
            clearCreatePatientFieldErrors();
            clearCreatePatientAlert();
            setCreatePatientModalOpen(true);
        });

        if (createPatientClose) {
            createPatientClose.addEventListener('click', function () {
                if (isCreatingPatient) {
                    return;
                }
                setCreatePatientModalOpen(false);
                resetCreatePatientForm();
            });
        }

        if (createPatientCancel) {
            createPatientCancel.addEventListener('click', function () {
                if (isCreatingPatient) {
                    return;
                }
                setCreatePatientModalOpen(false);
                resetCreatePatientForm();
            });
        }

        createPatientModal.addEventListener('click', function (event) {
            if (event.target !== createPatientModal || isCreatingPatient) {
                return;
            }
            setCreatePatientModalOpen(false);
            resetCreatePatientForm();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (createPatientModal.classList.contains('is-open') && !isCreatingPatient) {
                setCreatePatientModalOpen(false);
                resetCreatePatientForm();
            }
        });

        createPatientForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (isCreatingPatient) {
                return;
            }

            clearCreatePatientFieldErrors();
            clearCreatePatientAlert();

            var payload = {
                patient_name: String(createPatientForm.patient_name ? createPatientForm.patient_name.value : '').trim(),
                date_of_birth: String(createPatientForm.date_of_birth ? createPatientForm.date_of_birth.value : '').trim(),
                gender: String(createPatientForm.gender ? createPatientForm.gender.value : '').trim().toLowerCase(),
                contact_no: String(createPatientForm.contact_no ? createPatientForm.contact_no.value : '').trim(),
                email: String(createPatientForm.email ? createPatientForm.email.value : '').trim(),
                csrf_token: String(createPatientForm.csrf_token ? createPatientForm.csrf_token.value : '').trim(),
            };

            var clientErrors = validateCreatePatientPayload(payload);
            var clientErrorKeys = Object.keys(clientErrors);
            if (clientErrorKeys.length > 0) {
                clientErrorKeys.forEach(function (key) {
                    setCreatePatientFieldError(key, clientErrors[key]);
                });
                showCreatePatientAlert(clientErrors[clientErrorKeys[0]], 'error');
                return;
            }

            isCreatingPatient = true;
            setCreatePatientLoading(true);

            var requestBody = new URLSearchParams();
            requestBody.set('patient_name', payload.patient_name);
            requestBody.set('date_of_birth', payload.date_of_birth);
            requestBody.set('gender', payload.gender);
            requestBody.set('contact_no', payload.contact_no);
            requestBody.set('email', payload.email);
            requestBody.set('csrf_token', payload.csrf_token);

            fetch(createPatientEndpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-Token': payload.csrf_token,
                },
                body: requestBody.toString(),
            })
                .then(function (response) {
                    return response.json().catch(function () {
                        return {
                            status: 'error',
                            message: 'Invalid server response.',
                        };
                    }).then(function (json) {
                        return {
                            ok: response.ok,
                            statusCode: response.status,
                            payload: json,
                        };
                    });
                })
                .then(function (result) {
                    var payloadData = result.payload || {};
                    if (!result.ok || payloadData.status !== 'success') {
                        var fieldErrors = payloadData.errors || {};
                        Object.keys(fieldErrors).forEach(function (field) {
                            setCreatePatientFieldError(field, String(fieldErrors[field] || ''));
                        });

                        showCreatePatientAlert(
                            payloadData.message || 'Unable to create patient. Please review the form and try again.',
                            'error'
                        );
                        return;
                    }

                    var created = payloadData.data || {};
                    selectPatient({
                        id: String(created.patient_id || ''),
                        name: String(created.patient_name || ''),
                        email: String(created.email || ''),
                    });

                    hideSuggestions();
                    clearValidationError();
                    setCreatePatientModalOpen(false);
                    resetCreatePatientForm();
                })
                .catch(function () {
                    showCreatePatientAlert('Unable to create patient right now. Please try again.', 'error');
                })
                .finally(function () {
                    isCreatingPatient = false;
                    setCreatePatientLoading(false);
                });
        });
    }
})();
