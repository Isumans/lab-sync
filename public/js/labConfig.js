/**
 * Lab Configuration - AJAX form handler & logo preview
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Logo preview on file select ─────────────────────────────────────────
    document.addEventListener('change', function (e) {
        const input = e.target;
        if (input && input.id === 'logo-input' && input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                const wrap = document.getElementById('logo-preview-wrap');
                if (!wrap) return;
                // Replace whatever is in the preview (svg or img) with a fresh <img>
                wrap.innerHTML = `<img id="logo-preview-img" src="${ev.target.result}"
                    alt="Lab Logo" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // ── Operational-hours toggle labels ────────────────────────────────────
    document.addEventListener('change', function (e) {
        const cb = e.target;
        if (cb && cb.closest && cb.closest('.hours-row')) {
            const row = cb.closest('.hours-row');
            const span = row.querySelector('span[class^="status-"]');
            if (!span) return;
            if (cb.checked) {
                span.className = 'status-open';
                span.textContent = 'Open';
            } else {
                span.className = 'status-closed';
                span.textContent = 'Closed';
            }
        }
    });

    // ── Form submit ─────────────────────────────────────────────────────────
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.id !== 'lab-config-form') return;
        e.preventDefault();

        const msg = document.getElementById('lab-config-msg');
        const btn = form.querySelector('button[type="submit"]');

        const validationError = validateLabConfigForm(form);
        if (validationError) {
            showMsg(msg, validationError, 'error');
            return;
        }

        showMsg(msg, 'Saving…', 'info');
        if (btn) btn.disabled = true;

        const formData = new FormData(form);

        fetch('/lab_sync/index.php?controller=administratorController&action=saveLabConfiguration', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                showMsg(msg, data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            })
            .catch(() => showMsg(msg, 'Network error. Please try again.', 'error'))
            .finally(() => { if (btn) btn.disabled = false; });
    });
});

function validateLabConfigForm(form) {
    if (!form.checkValidity()) {
        form.reportValidity();
        return 'Please correct the highlighted fields.';
    }

    const rows = [
        {
            enabled: form.querySelector('input[name="hours_mon_fri_enabled"]'),
            open: form.querySelector('input[name="hours_mon_fri_open"]'),
            close: form.querySelector('input[name="hours_mon_fri_close"]'),
            label: 'Monday - Friday'
        },
        {
            enabled: form.querySelector('input[name="hours_sat_enabled"]'),
            open: form.querySelector('input[name="hours_sat_open"]'),
            close: form.querySelector('input[name="hours_sat_close"]'),
            label: 'Saturday'
        },
        {
            enabled: form.querySelector('input[name="hours_sun_enabled"]'),
            open: form.querySelector('input[name="hours_sun_open"]'),
            close: form.querySelector('input[name="hours_sun_close"]'),
            label: 'Sunday'
        }
    ];

    for (const row of rows) {
        const isEnabled = !!(row.enabled && row.enabled.checked);
        const open = row.open ? row.open.value : '';
        const close = row.close ? row.close.value : '';

        if (isEnabled && open !== '' && close !== '' && open >= close) {
            return `${row.label} close time must be later than open time.`;
        }
    }

    return '';
}

function showMsg(el, text, type) {
    if (!el) return;
    el.textContent = text;
    el.className = 'settings-msg settings-msg--' + type;
    el.style.display = 'block';
    if (type === 'success') {
        setTimeout(() => { el.style.display = 'none'; }, 4000);
    }
}
