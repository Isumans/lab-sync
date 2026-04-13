document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('rdAuthorizeModal');
  const closeBtn = document.getElementById('rdAuthorizeClose');
  const title = document.getElementById('rdAuthorizeTitle');
  const patientLine = document.getElementById('rdAuthorizePatientLine');
  const hintLine = document.getElementById('rdAuthorizeHint');
  const alertBox = document.getElementById('rdAuthorizeAlert');
  const fieldsWrap = document.getElementById('rdAuthorizeFields');
  const remarksInput = document.getElementById('rdAuthorizeRemarks');
  const flagBtn = document.getElementById('rdFlagForRecheckBtn');
  const authorizeBtn = document.getElementById('rdAuthorizeSignBtn');

  if (!modal || !fieldsWrap || !flagBtn || !authorizeBtn) {
    return;
  }

  const contextEndpoint = '/lab_sync/index.php?controller=reportsController&action=getAuthorizeContext';
  const decisionEndpoint = '/lab_sync/index.php?controller=reportsController&action=submitAuthorizationDecision';
  const generatePdfEndpoint = '/lab_sync/index.php?controller=reportsController&action=generatePdf';

  let activeTrigger = null;
  let activeAppointmentId = 0;
  let activeTestId = 0;

  const getFlagLabel = (flag) => {
    const normalized = String(flag || '').toUpperCase();
    if (normalized === 'H') {
      return 'HIGH';
    }
    if (normalized === 'L') {
      return 'LOW';
    }
    return 'NORMAL';
  };

  const getFlagClass = (flag) => {
    const normalized = String(flag || '').toUpperCase();
    if (normalized === 'H') {
      return 'is-high';
    }
    if (normalized === 'L') {
      return 'is-low';
    }
    return 'is-normal';
  };

  const getRangeText = (unit) => {
    const hasMin = unit.ref_min !== null && unit.ref_min !== undefined;
    const hasMax = unit.ref_max !== null && unit.ref_max !== undefined;

    if (hasMin && hasMax) {
      return `Normal Range: ${unit.ref_min} - ${unit.ref_max}`;
    }

    if (hasMin) {
      return `Normal Range: >= ${unit.ref_min}`;
    }

    if (hasMax) {
      return `Normal Range: <= ${unit.ref_max}`;
    }

    return 'Normal Range: Not configured';
  };

  const setModalOpen = (open) => {
    modal.hidden = !open;
    modal.classList.toggle('is-open', open);
    modal.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.style.overflow = open ? 'hidden' : '';

    if (!open && activeTrigger) {
      activeTrigger.focus();
    }
  };

  const setLoadingState = (loading) => {
    flagBtn.disabled = loading;
    authorizeBtn.disabled = loading;
  };

  const setAlert = (message, isSuccess = false) => {
    if (!message) {
      alertBox.textContent = '';
      alertBox.className = 'rd-authorize-modal__alert';
      alertBox.hidden = true;
      return;
    }

    alertBox.hidden = false;
    alertBox.textContent = message;
    alertBox.className = isSuccess
      ? 'rd-authorize-modal__alert is-success'
      : 'rd-authorize-modal__alert';
  };

  const setAlertHtml = (html, isSuccess = false) => {
    if (!html) {
      setAlert('');
      return;
    }

    alertBox.hidden = false;
    alertBox.innerHTML = html;
    alertBox.className = isSuccess
      ? 'rd-authorize-modal__alert is-success'
      : 'rd-authorize-modal__alert';
  };

  const renderFields = (units) => {
    fieldsWrap.innerHTML = '';

    if (!Array.isArray(units) || !units.length) {
      const empty = document.createElement('div');
      empty.className = 'rd-authorize-modal__empty';
      empty.textContent = 'No measured result values are available for this test.';
      fieldsWrap.appendChild(empty);
      return;
    }

    units.forEach((unit) => {
      const label = String(unit.value_name || 'Measured Value').toUpperCase();
      const valueText = unit.measured_value !== null && unit.measured_value !== undefined
        ? String(unit.measured_value)
        : '-';
      const unitName = String(unit.unit_name || '');
      const flagClass = getFlagClass(unit.flag);
      const flagLabel = getFlagLabel(unit.flag);
      const rangeText = getRangeText(unit);

      const card = document.createElement('div');
      card.className = `rd-authorize-field ${flagClass}`;
      card.innerHTML = `
        <div class="rd-authorize-field__top">
          <div class="rd-authorize-field__label">${label}</div>
          <span class="rd-authorize-field__badge ${flagClass}">${flagLabel}</span>
        </div>
        <div class="rd-authorize-field__value-row">
          <div class="rd-authorize-field__value">${valueText}</div>
          <div class="rd-authorize-field__unit">${unitName}</div>
        </div>
        <div class="rd-authorize-field__range">${rangeText}</div>
      `;

      fieldsWrap.appendChild(card);
    });
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
      throw new Error(payload.message || 'Failed to load authorization context.');
    }

    return payload.data;
  };

  const generatePdf = async (appointmentId, testId) => {
    const response = await fetch(generatePdfEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        appointment_id: appointmentId,
        test_id: testId
      })
    });

    const payload = await response.json();
    if (!response.ok || payload.status !== 'success') {
      throw new Error(payload.message || 'Authorization saved, but PDF generation failed.');
    }

    return payload.data || {};
  };

  const updateRowAfterAuthorization = () => {
    if (!activeTrigger) {
      return;
    }

    const row = activeTrigger.closest('tr');
    if (!row) {
      return;
    }

    const cells = row.querySelectorAll('td');
    if (cells.length >= 5) {
      cells[2].textContent = 'AUTHORIZED';
      cells[4].textContent = new Date().toLocaleString();
    }

    const actionCell = cells.length >= 6 ? cells[5] : row.querySelector('td:last-child');
    if (!actionCell) {
      return;
    }

    actionCell.classList.add('rd-actions-cell');
    actionCell.innerHTML = '';

    const group = document.createElement('div');
    group.className = 'rd-actions-group rd-actions-group-stack';

    const viewBtn = document.createElement('button');
    viewBtn.type = 'button';
    viewBtn.className = 'rd-btn rd-btn-table rd-btn-action-primary js-view-pdf-btn';
    viewBtn.setAttribute('data-appointment-id', String(activeAppointmentId));
    viewBtn.setAttribute('data-test-id', String(activeTestId));
    viewBtn.textContent = 'View PDF';

    

    group.appendChild(viewBtn);
    actionCell.appendChild(group);
  };

  const submitDecision = async (decision) => {
    if (!activeAppointmentId || !activeTestId) {
      setAlert('Invalid test context. Please reopen the modal.');
      return;
    }

    setAlert('');
    setLoadingState(true);

    try {
      const response = await fetch(decisionEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          appointment_id: activeAppointmentId,
          test_id: activeTestId,
          decision,
          note: remarksInput.value || ''
        })
      });

      const payload = await response.json();
      if (!response.ok || payload.status !== 'success') {
        throw new Error(payload.message || 'Failed to update report status.');
      }

      if (decision === 'authorize') {
        setAlert('Authorization saved. Generating PDF...');
        await generatePdf(activeAppointmentId, activeTestId);

        const viewUrl = `/lab_sync/index.php?controller=reportsController&action=viewPdf&appointment_id=${encodeURIComponent(activeAppointmentId)}&test_id=${encodeURIComponent(activeTestId)}`;
        setAlertHtml(`Report authorized and PDF generated successfully. <a href="${viewUrl}" target="_blank" rel="noopener">View PDF</a>`, true);
        updateRowAfterAuthorization();
        return;
      }

      setAlert(payload.message || 'Status updated.', true);
      window.setTimeout(() => {
        setModalOpen(false);
        window.location.reload();
      }, 450);
    } catch (error) {
      setAlert(error.message || 'Failed to update report status.', false);
    } finally {
      setLoadingState(false);
    }
  };

  const openFromButton = async (button) => {
    activeTrigger = button;
    activeAppointmentId = Number(button.getAttribute('data-appointment-id') || 0);
    activeTestId = Number(button.getAttribute('data-test-id') || 0);
    const testName = button.getAttribute('data-test-name') || 'Test';
    const patientName = button.getAttribute('data-patient-name') || 'Unknown Patient';
    const patientPid = button.getAttribute('data-patient-pid') || '-';

    title.textContent = `Verify & Authorize Report: ${testName}`;
    patientLine.textContent = `${patientName} | PID: ${patientPid}`;
    hintLine.textContent = 'Please review the measured values and clinical status flags before final authorization.';
    remarksInput.value = '';
    renderFields([]);
    setAlert('');
    setModalOpen(true);

    try {
      setLoadingState(true);
      const context = await loadContext(activeAppointmentId, activeTestId);
      title.textContent = `Verify & Authorize Report: ${context.test?.test_name || testName}`;
      patientLine.textContent = `${context.appointment?.patient_name || patientName} | PID: ${context.appointment?.pid || patientPid || '-'}`;
      remarksInput.value = context.remarks || '';
      renderFields(context.units || []);
    } catch (error) {
      setAlert(error.message || 'Failed to load authorization data.');
      renderFields([]);
    } finally {
      setLoadingState(false);
    }
  };

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('.js-authorize-report-btn');
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

  flagBtn.addEventListener('click', () => {
    submitDecision('recheck');
  });

  authorizeBtn.addEventListener('click', () => {
    submitDecision('authorize');
  });
});
