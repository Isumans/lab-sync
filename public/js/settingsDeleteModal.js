/**
 * Settings Delete Modal
 * Handles styled delete confirmations for Team Management (user) and Partner Labs (lab).
 *
 * Delete button requirements:
 *   Team user:  class="js-settings-delete-btn" data-delete-type="user" data-entity-name="John"
 *               Must be inside a <form> that submits the delete action.
 *   Partner lab: class="js-settings-delete-btn" data-delete-type="lab" data-entity-id="5" data-entity-name="City Lab"
 */

document.addEventListener('DOMContentLoaded', () => {
    const modal       = document.getElementById('settingsDeleteModal');
    const closeBtn    = document.getElementById('settingsDeleteClose');
    const cancelBtn   = document.getElementById('settingsDeleteCancel');
    const confirmBtn  = document.getElementById('settingsDeleteConfirm');
    const nameEl      = document.getElementById('settingsDeleteName');
    const titleEl     = document.getElementById('settingsDeleteTitle');
    const alertEl     = document.getElementById('settingsDeleteAlert');

    if (!modal || !confirmBtn) return;

    let activeTrigger  = null;
    let pendingForm    = null;  // for user delete (form submit)
    let pendingLabId   = null;  // for lab delete (AJAX)

    const baseUrl = (window.LAB_SYNC_CONFIG && window.LAB_SYNC_CONFIG.baseUrl)
        ? String(window.LAB_SYNC_CONFIG.baseUrl).replace(/\/$/, '')
        : '/lab_sync';

    /* ── helpers ─────────────────────────────────────────────────── */

    function showAlert(msg) {
        if (!alertEl) return;
        alertEl.textContent = msg || 'Delete failed.';
        alertEl.hidden = false;
    }

    function hideAlert() {
        if (!alertEl) return;
        alertEl.textContent = '';
        alertEl.hidden = true;
    }

    function openModal(entityName, deleteType, triggerEl) {
        activeTrigger = triggerEl || null;
        hideAlert();
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Delete';

        if (titleEl) titleEl.textContent = deleteType === 'lab' ? 'Delete Partner Lab' : 'Delete User';
        if (nameEl)  nameEl.textContent  = entityName || '—';

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        confirmBtn.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingForm  = null;
        pendingLabId = null;
        hideAlert();
        if (activeTrigger) activeTrigger.focus();
    }

    /* ── delete actions ───────────────────────────────────────────── */

    function submitUserDelete() {
        if (!pendingForm) { closeModal(); return; }
        // Inject the flag the server expects (was previously the button name="delete")
        const flag = document.createElement('input');
        flag.type  = 'hidden';
        flag.name  = 'delete';
        flag.value = '1';
        pendingForm.appendChild(flag);
        pendingForm.submit();
    }

    async function submitLabDelete() {
        if (!pendingLabId) { showAlert('Invalid lab ID.'); return; }

        confirmBtn.disabled    = true;
        confirmBtn.textContent = 'Deleting…';
        hideAlert();

        try {
            const endpoint = baseUrl + '/index.php?controller=partnerLabController&action=deleteLab';
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ lab_id: pendingLabId })
            });

            let payload = null;
            try { payload = await response.json(); } catch (_) { /* ignore */ }

            if (!response.ok || !payload || payload.status !== 'success') {
                throw new Error((payload && payload.message) || 'Unable to delete partner lab.');
            }

            closeModal();
            // Reload the partner-labs section content
            const section = document.getElementById('partner-labs');
            if (section) section.innerHTML = '';  // force re-fetch on next tab click
            window.location.reload();
        } catch (err) {
            showAlert(err.message || 'Unable to delete partner lab.');
            confirmBtn.disabled    = false;
            confirmBtn.textContent = 'Delete';
        }
    }

    /* ── event listeners ─────────────────────────────────────────── */

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-settings-delete-btn');
        if (btn) {
            e.preventDefault();
            const deleteType  = btn.getAttribute('data-delete-type') || 'user';
            const entityName  = btn.getAttribute('data-entity-name') || '';

            if (deleteType === 'lab') {
                pendingLabId = btn.getAttribute('data-entity-id') || null;
                pendingForm  = null;
            } else {
                pendingForm  = btn.closest('form') || null;
                pendingLabId = null;
            }

            openModal(entityName, deleteType, btn);
            return;
        }

        // Close on backdrop click
        if (e.target === modal) closeModal();
    });

    confirmBtn.addEventListener('click', () => {
        if (pendingLabId) {
            submitLabDelete();
        } else {
            submitUserDelete();
        }
    });

    closeBtn  && closeBtn.addEventListener('click', closeModal);
    cancelBtn && cancelBtn.addEventListener('click', closeModal);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });
});
