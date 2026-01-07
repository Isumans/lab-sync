// appointmentPopup.js — minimal popup logic
document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openCreateAppointment');
  const modal = document.getElementById('createAppointmentModal');
  const form = document.getElementById('createAppointmentForm');
  const message = document.getElementById('modalMessage');

  function setVisible(visible) {
    if (!modal) return;
    modal.style.display = visible ? 'flex' : 'none';
    modal.setAttribute('aria-hidden', visible ? 'false' : 'true');

    // prevent background scrolling and compensate for scrollbar width to avoid layout shift
    if (visible) {
      const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
      if (scrollBarWidth > 0) {
        document.documentElement.style.paddingRight = `${scrollBarWidth}px`;
      }
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
      document.documentElement.style.paddingRight = '';
    }
  }

  openBtn?.addEventListener('click', e => {
    e.preventDefault();
    setVisible(true);
    modal.querySelector('input, select, textarea')?.focus();
  });

  modal?.addEventListener('click', e => { if (e.target === modal) setVisible(false); });
  modal?.querySelector('#closeModal')?.addEventListener('click', () => setVisible(false));
  modal?.querySelector('#cancelModal')?.addEventListener('click', () => setVisible(false));

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal && modal.style.display !== 'none') {
      setVisible(false);
      openBtn?.focus();
    }
  });

  if (!form) return;

  form.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const text = await res.text();
      if (message) { message.style.color = 'green'; message.textContent = text || 'Appointment created.'; }
      setTimeout(() => { setVisible(false); window.location.reload(); }, 900);
    } catch (err) {
      if (message) { message.style.color = 'red'; message.textContent = 'Error creating appointment.'; }
      console.error(err);
    }
  });
});
