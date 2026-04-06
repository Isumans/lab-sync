document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('editAppointmentModal');
  const form = document.getElementById('editAppointmentForm');
  if (!modal || !form) {
    return;
  }

  const closeBtn = document.getElementById('editAppointmentClose');
  const cancelBtn = document.getElementById('editAppointmentCancel');
  const submitBtn = document.getElementById('editAppointmentSubmit');
  const alertBox = document.getElementById('editAppointmentAlert');
  const toast = document.getElementById('appointmentEditToast');

  const idInput = document.getElementById('editAppointmentId');
  const dateInput = document.getElementById('editAppointmentDate');
  const timeInput = document.getElementById('editAppointmentTime');
  const reasonInput = document.getElementById('editAppointmentReason');
  const titleEl = document.getElementById('editAppointmentTitle');

  const patientNameEl = document.getElementById('editPatientName');
  const patientPidEl = document.getElementById('editPatientPid');

  const searchInput = document.getElementById('editTestSearch');
  const searchResultsEl = document.getElementById('editTestSearchResults');
  const tagsEl = document.getElementById('editSelectedTests');
  const addTestBtn = document.getElementById('editAddTestBtn');
  const timeSlotsWrap = document.getElementById('editTimeSlots');

  const editDataEndpoint = '/lab_sync/index.php?controller=appointmentsController&action=getAppointmentEditData';
  const updateEndpoint = '/lab_sync/update_appointment.php';
  const searchTestsEndpoint = '/lab_sync/index.php?controller=appointmentsController&action=searchTests';

  let activeTrigger = null;
  let selectedTests = [];
  let lastSearchToken = 0;
  let canEditScheduleTests = true;

  const formatAppointmentNumber = (id) => {
    const raw = String(id || '').trim();
    return raw.startsWith('APP-') ? raw : `APP-${raw}`;
  };

  const normalizeTime = (value) => {
    const input = String(value || '').trim().toUpperCase();
    if (!input) {
      return '';
    }

    if (input === 'NOW') {
      const now = new Date();
      const hh = String(now.getHours()).padStart(2, '0');
      const mm = String(now.getMinutes()).padStart(2, '0');
      return `${hh}:${mm}:00`;
    }

    if (/^\d{2}:\d{2}:\d{2}$/.test(input)) {
      return input;
    }

    if (/^\d{2}:\d{2}$/.test(input)) {
      return `${input}:00`;
    }

    const match = input.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/);
    if (!match) {
      return '';
    }

    let hour = Number(match[1]);
    const minute = match[2];
    const meridiem = match[3];

    if (meridiem === 'PM' && hour < 12) {
      hour += 12;
    }
    if (meridiem === 'AM' && hour === 12) {
      hour = 0;
    }

    return `${String(hour).padStart(2, '0')}:${minute}:00`;
  };

  const closeSearchResults = () => {
    searchResultsEl.innerHTML = '';
    searchResultsEl.hidden = true;
  };

  const showToast = (message, type = 'success') => {
    if (!toast) {
      return;
    }

    toast.textContent = message;
    toast.className = `appointment-edit-toast ${type}`;
    toast.hidden = false;

    window.setTimeout(() => {
      toast.hidden = true;
    }, 2800);
  };

  const showAlert = (message, type = 'error') => {
    if (!alertBox) {
      return;
    }

    alertBox.textContent = message;
    alertBox.className = `appointment-edit-alert ${type}`;
    alertBox.hidden = false;
  };

  const setScheduleAndTestsEditable = (editable, statuses = []) => {
    canEditScheduleTests = editable;

    dateInput.disabled = !editable;
    searchInput.disabled = !editable;
    addTestBtn.disabled = !editable;
    addTestBtn.classList.toggle('is-disabled', !editable);

    const slotButtons = timeSlotsWrap.querySelectorAll('.time-slot');
    slotButtons.forEach((slot) => {
      slot.disabled = !editable;
      slot.classList.toggle('is-disabled', !editable);
    });

    renderSelectedTests();

    if (!editable) {
      const label = statuses.length ? statuses.join(', ') : 'non-pending';
      showAlert(`Schedule and selected tests are locked because current test status is ${label}. Only PENDING tests can change schedule/tests.`, 'info');
    }
  };

  const hideAlert = () => {
    if (!alertBox) {
      return;
    }

    alertBox.hidden = true;
    alertBox.textContent = '';
    alertBox.className = 'appointment-edit-alert';
  };

  const setModalOpen = (open) => {
    modal.classList.toggle('is-open', open);
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.style.overflow = open ? 'hidden' : '';

    if (!open) {
      closeSearchResults();
      activeTrigger?.focus();
    }
  };

  const setLoadingState = (isLoading) => {
    submitBtn.disabled = isLoading;
    submitBtn.innerHTML = isLoading ? '<span aria-hidden="true">⏳</span> Updating...' : '<span aria-hidden="true">💾</span> Update Appointment';
  };

  const renderSelectedTests = () => {
    tagsEl.innerHTML = '';

    if (selectedTests.length === 0) {
      const empty = document.createElement('span');
      empty.className = 'details-empty';
      empty.textContent = 'No tests selected.';
      tagsEl.appendChild(empty);
      return;
    }

    selectedTests.forEach((test) => {
      const tag = document.createElement('span');
      tag.className = 'test-tag';
      const disabledAttr = canEditScheduleTests ? '' : 'disabled';
      tag.innerHTML = `${test.test_name} <button type="button" class="remove-test-tag" data-test-id="${test.test_id}" aria-label="Remove test" ${disabledAttr}>×</button>`;
      tagsEl.appendChild(tag);
    });
  };

  const setSelectedTime = (rawTime) => {
    const normalized = normalizeTime(rawTime);
    timeInput.value = normalized;

    const slots = timeSlotsWrap.querySelectorAll('.time-slot');
    slots.forEach((slot) => {
      const slotTime = normalizeTime(slot.getAttribute('data-time'));
      slot.classList.toggle('is-selected', normalized !== '' && slotTime === normalized);
    });
  };

  const buildResultItem = (test) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'test-result-item';
    button.dataset.testId = test.test_id;
    button.innerHTML = `
      <div>
        <strong>${test.test_name}</strong>
        <div><span>${test.category || 'General'}</span></div>
      </div>
      <span>$${Number(test.price || 0).toFixed(2)}</span>
    `;
    return button;
  };

  const renderSearchResults = (tests) => {
    searchResultsEl.innerHTML = '';

    if (!tests.length) {
      const empty = document.createElement('div');
      empty.className = 'test-result-item';
      empty.textContent = 'No matching tests found.';
      searchResultsEl.appendChild(empty);
      searchResultsEl.hidden = false;
      return;
    }

    tests.forEach((test) => {
      searchResultsEl.appendChild(buildResultItem(test));
    });

    searchResultsEl.hidden = false;
  };

  const addTestByObject = (test) => {
    const existing = selectedTests.some((item) => String(item.test_id) === String(test.test_id));
    if (existing) {
      return;
    }

    selectedTests.push({
      test_id: Number(test.test_id),
      test_name: String(test.test_name || 'Unknown Test')
    });
    renderSelectedTests();
  };

  const fetchTests = async (query = '') => {
    const token = ++lastSearchToken;

    try {
      const response = await fetch(`${searchTestsEndpoint}&q=${encodeURIComponent(query)}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`Search request failed with status ${response.status}.`);
      }

      const payload = await response.json();
      if (token !== lastSearchToken) {
        return;
      }

      if (payload.status !== 'success' || !Array.isArray(payload.data)) {
        throw new Error('Invalid search response.');
      }

      renderSearchResults(payload.data);
    } catch (error) {
      console.error('Failed to search tests:', error);
      renderSearchResults([]);
    }
  };

  const populateModal = (payload) => {
    const appointment = payload.appointment || {};
    const tests = Array.isArray(payload.tests) ? payload.tests : [];
    const editable = payload.can_edit_schedule_tests !== false;
    const nonPendingStatuses = Array.isArray(payload.non_pending_statuses) ? payload.non_pending_statuses : [];

    idInput.value = appointment.appointment_id || '';
    titleEl.textContent = `Edit Appointment: #${formatAppointmentNumber(appointment.appointment_id || '')}`;

    const displayName = appointment.patient_display_name || appointment.patient_name || 'Unknown Patient';
    const displayPid = appointment.patient_display_pid || appointment.pid || `P-${appointment.patient_id || 'N/A'}`;

    patientNameEl.textContent = displayName;
    patientPidEl.textContent = `PID: ${displayPid}`;

    dateInput.value = appointment.appointment_date || '';
    setSelectedTime(appointment.appointment_time || '');
    reasonInput.value = appointment.reason || '';

    selectedTests = tests.map((test) => ({
      test_id: Number(test.test_id),
      test_name: String(test.test_name || 'Unknown Test')
    }));
    renderSelectedTests();

    closeSearchResults();
    hideAlert();
    setScheduleAndTestsEditable(editable, nonPendingStatuses);
  };

  const openWithLoading = () => {
    hideAlert();
    setModalOpen(true);
    tagsEl.innerHTML = `
      <div class="edit-loading">
        <div class="spinner" aria-hidden="true"></div>
        <p>Loading appointment data...</p>
      </div>
    `;
  };

  const fetchEditData = async (appointmentId) => {
    openWithLoading();

    try {
      const response = await fetch(`${editDataEndpoint}&appointment_id=${encodeURIComponent(appointmentId)}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}.`);
      }

      const payload = await response.json();
      if (payload.status !== 'success' || !payload.data) {
        throw new Error(payload.message || 'Unable to load appointment data.');
      }

      populateModal(payload.data);
      dateInput.focus();
    } catch (error) {
      console.error('Failed to load appointment edit data:', error);
      selectedTests = [];
      renderSelectedTests();
      showAlert(error.message || 'Unable to load appointment data.');
    }
  };

  const validateForm = () => {
    if (!idInput.value) {
      showAlert('Missing appointment ID. Please reopen the modal.');
      return false;
    }

    if (!dateInput.value) {
      showAlert('Please select an appointment date.');
      dateInput.focus();
      return false;
    }

    if (!timeInput.value) {
      showAlert('Please select an appointment time slot.');
      timeSlotsWrap.focus();
      return false;
    }

    if (!selectedTests.length) {
      showAlert('Please keep at least one test in the selection.');
      searchInput.focus();
      return false;
    }

    hideAlert();
    return true;
  };

  document.addEventListener('click', (event) => {
    const editButton = event.target.closest('.js-edit-appointment-btn');
    if (editButton) {
      event.preventDefault();
      const appointmentId = editButton.getAttribute('data-appointment-id');
      if (!appointmentId) {
        return;
      }

      activeTrigger = editButton;
      fetchEditData(appointmentId);
      return;
    }

    if (event.target === modal) {
      setModalOpen(false);
      return;
    }

    if (!event.target.closest('#editTestSearchResults') && !event.target.closest('#editTestSearch') && !event.target.closest('#editAddTestBtn')) {
      closeSearchResults();
    }
  });

  closeBtn?.addEventListener('click', () => setModalOpen(false));
  cancelBtn?.addEventListener('click', () => setModalOpen(false));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      setModalOpen(false);
    }
  });

  timeSlotsWrap?.addEventListener('click', (event) => {
    if (!canEditScheduleTests) {
      return;
    }

    const slot = event.target.closest('.time-slot');
    if (!slot) {
      return;
    }

    const slotValue = slot.getAttribute('data-time') || '';
    setSelectedTime(slotValue);
  });

  tagsEl?.addEventListener('click', (event) => {
    if (!canEditScheduleTests) {
      return;
    }

    const removeButton = event.target.closest('.remove-test-tag');
    if (!removeButton) {
      return;
    }

    const testId = removeButton.getAttribute('data-test-id');
    selectedTests = selectedTests.filter((test) => String(test.test_id) !== String(testId));
    renderSelectedTests();
  });

  addTestBtn?.addEventListener('click', () => {
    if (!canEditScheduleTests) {
      return;
    }

    fetchTests(searchInput.value.trim());
    searchInput.focus();
  });

  let searchDebounceTimer = null;
  searchInput?.addEventListener('input', () => {
    if (!canEditScheduleTests) {
      return;
    }

    window.clearTimeout(searchDebounceTimer);
    searchDebounceTimer = window.setTimeout(() => {
      fetchTests(searchInput.value.trim());
    }, 260);
  });

  searchResultsEl?.addEventListener('click', (event) => {
    if (!canEditScheduleTests) {
      return;
    }

    const item = event.target.closest('.test-result-item');
    if (!item || !item.dataset.testId) {
      return;
    }

    const testName = item.querySelector('strong')?.textContent?.trim() || 'Unknown Test';
    addTestByObject({
      test_id: Number(item.dataset.testId),
      test_name: testName
    });

    searchInput.value = '';
    closeSearchResults();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!validateForm()) {
      return;
    }

    setLoadingState(true);

    const payload = {
      appointment_id: Number(idInput.value),
      appointment_date: dateInput.value,
      appointment_time: timeInput.value,
      reason: reasonInput.value.trim(),
      tests: selectedTests.map((test) => Number(test.test_id))
    };

    try {
      const response = await fetch(updateEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();
      if (!response.ok || result.status !== 'success') {
        throw new Error(result.message || 'Update failed.');
      }

      showAlert(result.message || 'Appointment updated successfully.', 'success');
      showToast(result.message || 'Appointment updated successfully.', 'success');

      window.setTimeout(() => {
        setModalOpen(false);
        window.location.reload();
      }, 700);
    } catch (error) {
      console.error('Appointment update failed:', error);
      showAlert(error.message || 'Failed to update appointment.');
      showToast(error.message || 'Failed to update appointment.', 'error');
    } finally {
      setLoadingState(false);
    }
  });
});
