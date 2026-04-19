(function () {
    // Tab switching
    document.querySelectorAll('.slots-tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.slots-tab-btn').forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.slots-tab-panel').forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            var panel = document.getElementById('panel-' + btn.dataset.tab);
            if (panel) panel.classList.add('active');
        });
    });

    // Delegate toggle / delete clicks on the whole section
    document.addEventListener('click', function (e) {
        var toggleBtn = e.target.closest('.btn-slot-toggle');
        var deleteBtn = e.target.closest('.btn-slot-delete');
        if (toggleBtn) handleToggle(toggleBtn);
        if (deleteBtn) handleDelete(deleteBtn);
    });

    function showMsg(text, type) {
        var el = document.getElementById('online-slots-msg');
        if (!el) return;
        el.textContent = text;
        el.className = 'slots-msg ' + type;
        el.style.display = 'block';
        clearTimeout(el._t);
        el._t = setTimeout(function () { el.style.display = 'none'; }, 4000);
    }

    function buildSlotRow(slot) {
        var start = slot.start_time.substring(0, 5);
        var end   = slot.end_time.substring(0, 5);
        var badgeCls = slot.is_active == 1 ? 'badge-active' : 'badge-inactive';
        var badgeTxt = slot.is_active == 1 ? 'Active' : 'Inactive';
        var tr = document.createElement('tr');
        tr.dataset.slotId = slot.id;
        tr.innerHTML =
            '<td>' + start + '</td>' +
            '<td>' + end + '</td>' +
            '<td>' + slot.max_patients + '</td>' +
            '<td><span class="slot-badge ' + badgeCls + '">' + badgeTxt + '</span></td>' +
            '<td class="slot-actions">' +
                '<button class="btn-slot-toggle" data-id="' + slot.id + '" title="Toggle active">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>' +
                '</button>' +
                '<button class="btn-slot-delete" data-id="' + slot.id + '" title="Delete slot">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>' +
                '</button>' +
            '</td>';
        return tr;
    }

    window.addSlot = function (e, dayGroup) {
        e.preventDefault();
        var form = e.target;
        var fd = new FormData(form);
        fd.append('day_group', dayGroup);

        fetch('/lab_sync/index.php?controller=administratorController&action=saveOnlineSlot', {
            method: 'POST',
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                var tbody = document.getElementById('slots-body-' + dayGroup);
                // Remove "no slots" placeholder row if present
                var noRow = tbody.querySelector('.no-slots-row');
                if (noRow) noRow.remove();

                var slot = {
                    id: res.id,
                    start_time: form.querySelector('[name="start_time"]').value + ':00',
                    end_time:   form.querySelector('[name="end_time"]').value   + ':00',
                    max_patients: form.querySelector('[name="max_patients"]').value,
                    is_active: 1
                };
                tbody.appendChild(buildSlotRow(slot));
                form.reset();
                form.querySelector('[name="max_patients"]').value = 4;
                showMsg('Slot added successfully.', 'success');
            } else {
                showMsg(res.message || 'Failed to add slot.', 'error');
            }
        })
        .catch(function () { showMsg('Network error. Please try again.', 'error'); });
    };

    function handleToggle(btn) {
        var id = btn.dataset.id;
        var fd = new FormData();
        fd.append('id', id);

        fetch('/lab_sync/index.php?controller=administratorController&action=toggleOnlineSlot', {
            method: 'POST',
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                var tr = btn.closest('tr');
                var badge = tr.querySelector('.slot-badge');
                if (res.is_active == 1) {
                    badge.className = 'slot-badge badge-active';
                    badge.textContent = 'Active';
                } else {
                    badge.className = 'slot-badge badge-inactive';
                    badge.textContent = 'Inactive';
                }
            } else {
                showMsg(res.message || 'Failed to toggle slot.', 'error');
            }
        })
        .catch(function () { showMsg('Network error.', 'error'); });
    }

    function handleDelete(btn) {
        if (!confirm('Delete this time slot? This cannot be undone.')) return;
        var id = btn.dataset.id;
        var fd = new FormData();
        fd.append('id', id);

        fetch('/lab_sync/index.php?controller=administratorController&action=deleteOnlineSlot', {
            method: 'POST',
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                var tr = btn.closest('tr');
                var tbody = tr.parentElement;
                tr.remove();
                if (tbody.querySelectorAll('tr').length === 0) {
                    tbody.innerHTML = '<tr class="no-slots-row"><td colspan="5" style="text-align:center;color:#888;padding:18px;">No slots defined yet.</td></tr>';
                }
                showMsg('Slot deleted.', 'success');
            } else {
                showMsg(res.message || 'Failed to delete slot.', 'error');
            }
        })
        .catch(function () { showMsg('Network error.', 'error'); });
    }
})();
