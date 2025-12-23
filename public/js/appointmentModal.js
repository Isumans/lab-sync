document.addEventListener('DOMContentLoaded', function () {
  const openBtn = document.getElementById('openCreateAppointment');
  const modal = document.getElementById('createAppointmentModal');
  const closeBtn = document.getElementById('closeModal');
  const cancelBtn = document.getElementById('cancelModal');
  const form = document.getElementById('createAppointmentForm');
  const message = document.getElementById('modalMessage');

  function openModal() {
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
  }
  function closeModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    message.textContent = '';
  }

  openBtn && openBtn.addEventListener('click', function (e) {
    e.preventDefault();
    openModal();
  });
  closeBtn && closeBtn.addEventListener('click', closeModal);
  cancelBtn && cancelBtn.addEventListener('click', closeModal);

  // Close when clicking outside modal content
  window.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  // AJAX form submit
  form && form.addEventListener('submit', function (e) {
    e.preventDefault();
    const url = form.action;
    const fd = new FormData(form);

    fetch(url, {
      method: 'POST',
      body: fd,
    })
      .then(res => res.text())
      .then(text => {
        // show server response inside modal
        message.style.color = 'green';
        message.textContent = text || 'Appointment created.';
        setTimeout(() => {
          closeModal();
          // optional: reload to refresh appointment list
          window.location.reload();
        }, 900);
      })
      .catch(err => {
        message.style.color = 'red';
        message.textContent = 'Error creating appointment.';
        console.error(err);
      });
  });
});
