document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('appointmentDetailsModal');
  const modalBody = document.getElementById('appointmentDetailsBody');
  const closeBtn = document.getElementById('appointmentDetailsClose');

  if (!modal || !modalBody) {
    return;
  }

  const appConfig = window.LAB_SYNC_CONFIG || {};
  const baseUrl = String(appConfig.baseUrl || '/lab_sync').replace(/\/$/, '');
  const csrfToken = String(appConfig.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
  const endpoint = baseUrl + '/index.php?controller=appointmentsController&action=getAppointmentDetails';
  const updateStatusEndpoint = baseUrl + '/index.php?controller=appointmentsController&action=updateTestStatus';
  let activeTrigger = null;
  let currentAppointmentId = null;

  const loadingHtml = `
    <div class="appointment-details-loading">
      <div class="spinner" aria-hidden="true"></div>
      <p>Loading appointment details...</p>
    </div>
  `;

  const renderError = (message) => {
    modalBody.innerHTML = `
      <div class="appointment-details-error-state">
        <h3>Unable to load details</h3>
        <p>${message}</p>
      </div>
    `;
  };

  const lockBackgroundScroll = () => {
    document.body.style.overflow = 'hidden';
  };

  const unlockBackgroundScroll = () => {
    document.body.style.overflow = '';
  };

  const openModal = () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    lockBackgroundScroll();
    closeBtn?.focus();
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    modalBody.innerHTML = '';
    unlockBackgroundScroll();
    activeTrigger?.focus();
  };

  const fetchAppointmentDetails = async (appointmentId) => {
    currentAppointmentId = appointmentId;
    modalBody.innerHTML = loadingHtml;
    openModal();

    try {
      const response = await fetch(`${endpoint}&appointment_id=${encodeURIComponent(appointmentId)}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        const fallback = `Request failed with status ${response.status}.`;
        const text = await response.text();
        throw new Error(text || fallback);
      }

      const html = await response.text();
      if (!html.trim()) {
        throw new Error('The server returned an empty response.');
      }

      modalBody.innerHTML = html;
    } catch (error) {
      renderError(error.message || 'A network error occurred.');
      console.error('Error loading appointment details:', error);
    }
  };

  const updateProcToInProgress = async (appointmentId, testId, button) => {
    const confirmed = window.confirm('Do you want to update this test status to IN_PROGRESS?');
    if (!confirmed) {
      return;
    }

    button.disabled = true;
    button.classList.add('is-updating');

    const formBody = new URLSearchParams({
      appointment_id: String(appointmentId),
      test_id: String(testId)
    }).toString();

    try {
      const response = await fetch(updateStatusEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-CSRF-Token': csrfToken,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formBody
      });

      let payload = null;
      try {
        payload = await response.json();
      } catch (e) {
        payload = null;
      }

      if (!response.ok || !payload || payload.status !== 'success') {
        const message = payload && payload.message ? payload.message : 'Failed to update test status.';
        throw new Error(message);
      }

      await fetchAppointmentDetails(currentAppointmentId || appointmentId);
    } catch (error) {
      button.disabled = false;
      button.classList.remove('is-updating');
      window.alert(error.message || 'Unable to update test status.');
      console.error('Error updating test status:', error);
    }
  };

  document.addEventListener('click', (event) => {
    const viewButton = event.target.closest('.js-view-details-btn');
    if (viewButton) {
      event.preventDefault();
      const appointmentId = viewButton.getAttribute('data-appointment-id');
      if (!appointmentId) {
        return;
      }

      activeTrigger = viewButton;
      fetchAppointmentDetails(appointmentId);
      return;
    }

    const procStageButton = event.target.closest('.js-proc-stage');
    if (procStageButton) {
      event.preventDefault();

      const appointmentId = procStageButton.getAttribute('data-appointment-id');
      const testId = procStageButton.getAttribute('data-test-id');
      if (!appointmentId || !testId) {
        return;
      }

      updateProcToInProgress(appointmentId, testId, procStageButton);
      return;
    }

    if (event.target === modal || event.target.closest('#appointmentDetailsClose')) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
});
