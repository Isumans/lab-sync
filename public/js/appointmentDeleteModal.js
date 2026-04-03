document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('deleteAppointmentModal');
  const closeBtn = document.getElementById('deleteAppointmentClose');
  const cancelBtn = document.getElementById('deleteAppointmentCancel');
  const confirmBtn = document.getElementById('deleteAppointmentConfirm');
  const appointmentNumberEl = document.getElementById('deleteAppointmentNumber');
  const patientNameEl = document.getElementById('deleteAppointmentPatient');
  const alertEl = document.getElementById('deleteAppointmentAlert');

  if (!modal || !confirmBtn) {
    return;
  }

  const deleteEndpoint = '/lab_sync/index.php?controller=appointmentsController&action=deleteAppointment';

  let activeTrigger = null;
  let selectedAppointmentId = null;

  const showAlert = (message) => {
    if (!alertEl) {
      return;
    }
    alertEl.textContent = message || 'Delete request failed.';
    alertEl.hidden = false;
  };

  const hideAlert = () => {
    if (!alertEl) {
      return;
    }
    alertEl.textContent = '';
    alertEl.hidden = true;
  };

  const openModal = () => {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    hideAlert();
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    selectedAppointmentId = null;
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Delete Appointment';
    hideAlert();
    if (activeTrigger) {
      activeTrigger.focus();
    }
  };

  const setModalData = (appointmentId, patientName) => {
    selectedAppointmentId = Number(appointmentId);
    const normalizedId = String(appointmentId || '').trim();
    appointmentNumberEl.textContent = normalizedId.startsWith('#') ? normalizedId : '#APP-' + normalizedId;
    patientNameEl.textContent = patientName && patientName.trim() ? patientName : 'Unknown Patient';
  };

  const sendDeleteRequest = async () => {
    if (!selectedAppointmentId || Number.isNaN(selectedAppointmentId)) {
      showAlert('Invalid appointment ID.');
      return;
    }

    const confirmed = window.confirm('Are you sure you want to delete this appointment?');
    if (!confirmed) {
      return;
    }

    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Deleting...';
    hideAlert();

    try {
      const response = await fetch(deleteEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ appointment_id: selectedAppointmentId })
      });

      let payload = null;
      try {
        payload = await response.json();
      } catch (e) {
        payload = null;
      }

      if (!response.ok || !payload || payload.status !== 'success') {
        const message = payload && payload.message ? payload.message : 'Unable to delete appointment.';
        throw new Error(message);
      }

      closeModal();
      window.location.reload();
    } catch (error) {
      showAlert(error.message || 'Unable to delete appointment.');
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Delete Appointment';
    }
  };

  document.addEventListener('click', (event) => {
    const deleteBtn = event.target.closest('.js-delete-appointment-btn');
    if (deleteBtn) {
      event.preventDefault();
      activeTrigger = deleteBtn;
      const appointmentId = deleteBtn.getAttribute('data-appointment-id');
      const patientName = deleteBtn.getAttribute('data-patient-name') || '';
      if (!appointmentId) {
        return;
      }
      setModalData(appointmentId, patientName);
      openModal();
      return;
    }

    if (event.target === modal) {
      closeModal();
    }
  });

  closeBtn && closeBtn.addEventListener('click', closeModal);
  cancelBtn && cancelBtn.addEventListener('click', closeModal);
  confirmBtn.addEventListener('click', sendDeleteRequest);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
});