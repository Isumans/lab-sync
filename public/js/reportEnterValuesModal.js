document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('rdEnterValuesModal');
  const closeBtn = document.getElementById('rdEnterValuesClose');
  const form = document.getElementById('rdEnterValuesForm');
  const fieldsWrap = document.getElementById('rdEnterValuesFields');
  const appointmentIdInput = document.getElementById('rdEnterValuesAppointmentId');
  const testIdInput = document.getElementById('rdEnterValuesTestId');
  const remarksInput = document.getElementById('rdEnterValuesRemarks');
  const title = document.getElementById('rdEnterValuesTitle');
  const patientLine = document.getElementById('rdEnterValuesPatientLine');
  const hintLine = document.getElementById('rdEnterValuesHint');
  const alertBox = document.getElementById('rdEnterValuesAlert');
  const saveDraftBtn = document.getElementById('rdSaveDraftBtn');
  const saveReadyBtn = document.getElementById('rdSaveReadyBtn');

  if (!modal || !form || !fieldsWrap) {
    return;
  }

  const contextEndpoint = '/lab_sync/index.php?controller=reportsController&action=getEnterValuesContext';
  const saveEndpoint = '/lab_sync/index.php?controller=reportsController&action=saveEnterValues';

  let activeTrigger = null;
  let activeUnits = [];

  const setModalOpen = (open) => {
    modal.hidden = !open;
    modal.classList.toggle('is-open', open);
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.style.overflow = open ? 'hidden' : '';

    if (!open && activeTrigger) {
      activeTrigger.focus();
    }
  };

  const setAlert = (message, isSuccess = false) => {
    if (!message) {
      alertBox.textContent = '';
      alertBox.className = 'rd-enter-modal__alert';
      alertBox.hidden = true;
      return;
    }

    alertBox.hidden = false;
    alertBox.textContent = message;
    alertBox.className = isSuccess ? 'rd-enter-modal__alert is-success' : 'rd-enter-modal__alert';
  };

  const setLoadingState = (loading) => {
    saveDraftBtn.disabled = loading;
    saveReadyBtn.disabled = loading;
    fieldsWrap.querySelectorAll('input').forEach((input) => {
      input.disabled = loading;
    });
    remarksInput.disabled = loading;
  };

  const normalizeNumeric = (value) => {
    const text = String(value ?? '').trim();
    if (text === '') {
      return null;
    }

    const parsed = Number(text);
    return Number.isFinite(parsed) ? parsed : null;
  };

  const getReferenceText = (unit) => {
    const hasMin = unit.ref_min !== null && unit.ref_min !== undefined;
    const hasMax = unit.ref_max !== null && unit.ref_max !== undefined;

    if (hasMin && hasMax) {
      return `Ref: ${unit.ref_min} - ${unit.ref_max}`;
    }

    if (hasMin) {
      return `Ref: >= ${unit.ref_min}`;
    }

    if (hasMax) {
      return `Ref: <= ${unit.ref_max}`;
    }

    return 'Ref: Not set';
  };

  const setOutOfRangeVisual = (input, unit, numericValue) => {
    const card = input.closest('.rd-enter-field');
    if (!card) {
      return;
    }

    if (numericValue === null) {
      card.classList.remove('is-out');
      return;
    }

    const hasMin = unit.ref_min !== null && unit.ref_min !== undefined;
    const hasMax = unit.ref_max !== null && unit.ref_max !== undefined;
    const isLow = hasMin && numericValue < Number(unit.ref_min);
    const isHigh = hasMax && numericValue > Number(unit.ref_max);

    card.classList.toggle('is-out', isLow || isHigh);
  };

  const renderFields = (units) => {
    fieldsWrap.innerHTML = '';
    activeUnits = Array.isArray(units) ? units : [];

    if (!activeUnits.length) {
      const empty = document.createElement('div');
      empty.className = 'rd-enter-modal__empty';
      empty.textContent = 'No configured analyte/unit definitions found for this test. Add units in test catalog first.';
      fieldsWrap.appendChild(empty);
      return;
    }

    activeUnits.forEach((unit, index) => {
      const card = document.createElement('div');
      card.className = 'rd-enter-field';

      const label = String(unit.value_name || 'Measured Value').toUpperCase();
      const unitName = String(unit.unit_name || '');
      const referenceText = getReferenceText(unit);
      const value = unit.measured_value ?? '';

      card.innerHTML = `
        <div class="rd-enter-field__top">
          <div class="rd-enter-field__label">${label}</div>
          <div class="rd-enter-field__reference">${referenceText}</div>
        </div>
        <div class="rd-enter-field__value-wrap">
          <input
            type="number"
            inputmode="decimal"
            step="any"
            data-unit-id="${unit.unit_id}"
            data-index="${index}"
            value="${value}"
            placeholder="0.0"
          >
          <span class="rd-enter-field__unit">${unitName}</span>
        </div>
      `;

      const input = card.querySelector('input');
      input.addEventListener('input', () => {
        const numeric = normalizeNumeric(input.value);
        setOutOfRangeVisual(input, unit, numeric);
      });

      setOutOfRangeVisual(input, unit, normalizeNumeric(value));
      fieldsWrap.appendChild(card);
    });
  };

  const collectResults = () => {
    const inputs = fieldsWrap.querySelectorAll('input[data-unit-id]');
    const rows = [];

    inputs.forEach((input) => {
      const unitId = Number(input.getAttribute('data-unit-id'));
      const raw = String(input.value || '').trim();
      rows.push({
        unit_id: unitId,
        measured_value: raw
      });
    });

    return rows;
  };

  const validateBeforeSubmit = (mode, rows) => {
    if (!rows.length) {
      return 'No measurable fields available for this test.';
    }

    const numericRows = rows.filter((row) => String(row.measured_value).trim() !== '');
    if (!numericRows.length) {
      return 'Please enter at least one measured value.';
    }

    const hasInvalid = numericRows.some((row) => Number.isNaN(Number(row.measured_value)));
    if (hasInvalid) {
      return 'All entered values must be numeric.';
    }

    if (mode === 'ready') {
      const hasEmpty = rows.some((row) => String(row.measured_value).trim() === '');
      if (hasEmpty) {
        return 'Enter all values before marking this test as ready.';
      }
    }

    return '';
  };

  const loadContext = async (appointmentId, testId) => {
    const response = await fetch(`${contextEndpoint}&appointment_id=${encodeURIComponent(appointmentId)}&test_id=${encodeURIComponent(testId)}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const payload = await response.json();
    if (!response.ok || payload.status !== 'success') {
      throw new Error(payload.message || 'Failed to load test entry context.');
    }

    return payload.data;
  };

  const saveValues = async (mode) => {
    const appointmentId = Number(appointmentIdInput.value || 0);
    const testId = Number(testIdInput.value || 0);
    const rows = collectResults();

    const validationError = validateBeforeSubmit(mode, rows);
    if (validationError) {
      setAlert(validationError, false);
      return;
    }

    setAlert('');
    setLoadingState(true);

    try {
      const response = await fetch(saveEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          appointment_id: appointmentId,
          test_id: testId,
          mode,
          remarks: remarksInput.value || '',
          results: rows
        })
      });

      const payload = await response.json();
      if (!response.ok || payload.status !== 'success') {
        throw new Error(payload.message || 'Failed to save values.');
      }

      setAlert(payload.message || 'Saved successfully.', true);

      if (mode === 'ready') {
        window.setTimeout(() => {
          setModalOpen(false);
          window.location.reload();
        }, 450);
      }
    } catch (error) {
      setAlert(error.message || 'Failed to save values.', false);
    } finally {
      setLoadingState(false);
    }
  };

  const openFromButton = async (button) => {
    activeTrigger = button;

    const appointmentId = Number(button.getAttribute('data-appointment-id') || 0);
    const testId = Number(button.getAttribute('data-test-id') || 0);
    const testName = button.getAttribute('data-test-name') || 'Test';
    const patientName = button.getAttribute('data-patient-name') || 'Unknown Patient';
    const patientPid = button.getAttribute('data-patient-pid') || '-';

    appointmentIdInput.value = String(appointmentId);
    testIdInput.value = String(testId);
    title.textContent = `Enter Test Results: ${testName}`;
    patientLine.textContent = `${patientName} | PID: ${patientPid}`;
    hintLine.textContent = 'Please input measured values and review reference ranges before submission.';
    remarksInput.value = '';
    renderFields([]);
    setAlert('');
    setModalOpen(true);

    try {
      setLoadingState(true);
      const context = await loadContext(appointmentId, testId);
      title.textContent = `Enter Test Results: ${context.test?.test_name || testName}`;
      patientLine.textContent = `${context.appointment?.patient_name || patientName} | PID: ${context.appointment?.pid || patientPid || '-'}`;
      remarksInput.value = context.remarks || '';
      renderFields(context.units || []);
    } catch (error) {
      setAlert(error.message || 'Failed to load modal data.', false);
      renderFields([]);
    } finally {
      setLoadingState(false);
    }
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('.js-enter-values-btn');
    if (trigger) {
      event.preventDefault();
      openFromButton(trigger);
      return;
    }

    if (event.target === modal) {
      setModalOpen(false);
    }
  });

  closeBtn?.addEventListener('click', () => {
    setModalOpen(false);
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      setModalOpen(false);
    }
  });

  saveDraftBtn?.addEventListener('click', () => {
    saveValues('draft');
  });

  saveReadyBtn?.addEventListener('click', () => {
    saveValues('ready');
  });
});
