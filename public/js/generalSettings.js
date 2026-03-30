/**
 * General Settings - AJAX form handler
 */
document.addEventListener('DOMContentLoaded', () => {

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.id !== 'general-settings-form') return;
        e.preventDefault();

        const msg = document.getElementById('general-settings-msg');
        const btn = form.querySelector('button[type="submit"]');

        showGeneralMsg(msg, 'Saving…', 'info');
        if (btn) btn.disabled = true;

        const formData = new FormData(form);

        fetch('/lab_sync/index.php?controller=administratorController&action=saveGeneralSettings', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                showGeneralMsg(msg, data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            })
            .catch(() => showGeneralMsg(msg, 'Network error. Please try again.', 'error'))
            .finally(() => { if (btn) btn.disabled = false; });
    });
});

function showGeneralMsg(el, text, type) {
    if (!el) return;
    el.textContent = text;
    el.className = 'settings-msg settings-msg--' + type;
    el.style.display = 'block';
    if (type === 'success') {
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    }
}
