(function () {
    function formatTimeLabel(timeValue) {
        var parts = String(timeValue || '').split(':');
        if (parts.length < 2) {
            return String(timeValue || '');
        }

        var hour = parseInt(parts[0], 10);
        var min = parts[1] || '00';
        if (isNaN(hour)) {
            return String(timeValue || '');
        }

        var suffix = hour >= 12 ? 'PM' : 'AM';
        var displayHour = hour % 12;
        if (displayHour === 0) {
            displayHour = 12;
        }

        var hh = displayHour < 10 ? '0' + displayHour : String(displayHour);
        return hh + ':' + min + ' ' + suffix;
    }

    function showMsg(text, type) {
        var el = document.getElementById('online-slots-msg');
        if (!el) {
            return;
        }

        el.textContent = text;
        el.className = 'slots-msg ' + type;

        if (el._msgTimer) {
            clearTimeout(el._msgTimer);
        }

        el._msgTimer = setTimeout(function () {
            el.className = 'slots-msg';
            el.textContent = '';
        }, 4200);
    }

    function updateDayView(root, dayKey) {
        root.setAttribute('data-active-day', dayKey);

        root.querySelectorAll('.slots-day-tab').forEach(function (tab) {
            var isActive = tab.getAttribute('data-tab') === dayKey;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        root.querySelectorAll('.slots-day-panel').forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-panel') === dayKey);
        });

        var selectedTab = root.querySelector('.slots-day-tab.is-active');
        var dayText = selectedTab ? selectedTab.textContent.trim() : 'Mon - Fri';

        var currentDay = root.querySelector('#slots-current-day');
        if (currentDay) {
            currentDay.textContent = dayText;
        }

        var dayField = root.querySelector('#slots-day-group');
        if (dayField) {
            dayField.value = dayKey;
        }
    }

    function buildSlotRow(slot) {
        var tr = document.createElement('tr');
        tr.setAttribute('data-slot-id', String(slot.id));

        var statusClass = Number(slot.is_active) === 1 ? 'is-active' : 'is-inactive';
        var statusText = Number(slot.is_active) === 1 ? 'Active' : 'Inactive';

        tr.innerHTML = [
            '<td class="slots-time">' + formatTimeLabel(slot.start_time) + '</td>',
            '<td class="slots-time">' + formatTimeLabel(slot.end_time) + '</td>',
            '<td>',
            '   <div class="slots-capacity-wrap">',
            '       <div class="slots-capacity-bar"><span style="width:100%;"></span></div>',
            '       <div class="slots-capacity-text">0 / ' + Number(slot.max_patients) + '</div>',
            '   </div>',
            '</td>',
            '<td><span class="slots-status-badge ' + statusClass + '">' + statusText + '</span></td>',
            '<td class="slots-actions-cell">',
            '   <button type="button" class="slots-action-btn btn-slot-toggle" data-id="' + Number(slot.id) + '" title="Toggle slot status">',
            '       <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v6m0 0l-3-3m3 3l3-3"/><path d="M5 19h14"/></svg>',
            '   </button>',
            '   <button type="button" class="slots-action-btn btn-slot-delete" data-id="' + Number(slot.id) + '" title="Delete slot">',
            '       <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V5h6v2"/><path d="M8 7l1 12h6l1-12"/></svg>',
            '   </button>',
            '</td>'
        ].join('');

        return tr;
    }

    function ensureEmptyRow(tbody) {
        if (tbody.querySelectorAll('tr').length === 0) {
            tbody.innerHTML = '<tr class="slots-empty-row"><td colspan="5">No slots configured for this day yet.</td></tr>';
        }
    }

    function removeEmptyRow(tbody) {
        var row = tbody.querySelector('.slots-empty-row');
        if (row) {
            row.remove();
        }
    }

    function onAddSlotSubmit(root, form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var dayGroupField = form.querySelector('#slots-day-group');
            var dayGroup = dayGroupField ? dayGroupField.value : 'mon_fri';
            var payload = new FormData(form);

            fetch('/lab_sync/index.php?controller=administratorController&action=saveOnlineSlot', {
                method: 'POST',
                body: payload
            })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (!res || !res.success) {
                        showMsg((res && res.message) || 'Failed to add slot.', 'error');
                        return;
                    }

                    var tbody = root.querySelector('#slots-body-' + dayGroup);
                    if (!tbody) {
                        showMsg('Slot added but table could not refresh.', 'error');
                        return;
                    }

                    removeEmptyRow(tbody);

                    var slot = {
                        id: res.id,
                        start_time: (form.querySelector('[name="start_time"]').value || '') + ':00',
                        end_time: (form.querySelector('[name="end_time"]').value || '') + ':00',
                        max_patients: form.querySelector('[name="max_patients"]').value || '20',
                        is_active: 1
                    };

                    tbody.appendChild(buildSlotRow(slot));
                    form.reset();

                    var dayGroupReset = form.querySelector('#slots-day-group');
                    if (dayGroupReset) {
                        dayGroupReset.value = dayGroup;
                    }

                    var maxInput = form.querySelector('[name="max_patients"]');
                    if (maxInput) {
                        maxInput.value = '20';
                    }

                    showMsg('Slot added successfully.', 'success');
                })
                .catch(function () {
                    showMsg('Network error. Please try again.', 'error');
                });
        });
    }

    function handleToggle(root, btn) {
        var id = btn.getAttribute('data-id');
        if (!id) {
            return;
        }

        var fd = new FormData();
        fd.append('id', id);

        fetch('/lab_sync/index.php?controller=administratorController&action=toggleOnlineSlot', {
            method: 'POST',
            body: fd
        })
            .then(function (res) { return res.json(); })
            .then(function (res) {
                if (!res || !res.success) {
                    showMsg((res && res.message) || 'Failed to toggle slot.', 'error');
                    return;
                }

                var tr = btn.closest('tr');
                if (!tr) {
                    return;
                }

                var badge = tr.querySelector('.slots-status-badge');
                if (!badge) {
                    return;
                }

                if (Number(res.is_active) === 1) {
                    badge.className = 'slots-status-badge is-active';
                    badge.textContent = 'Active';
                } else {
                    badge.className = 'slots-status-badge is-inactive';
                    badge.textContent = 'Inactive';
                }

                showMsg('Slot status updated.', 'success');
            })
            .catch(function () {
                showMsg('Network error. Please try again.', 'error');
            });
    }

    function handleDelete(root, btn) {
        var id = btn.getAttribute('data-id');
        if (!id) {
            return;
        }

        if (!window.confirm('Delete this slot? This action cannot be undone.')) {
            return;
        }

        var fd = new FormData();
        fd.append('id', id);

        fetch('/lab_sync/index.php?controller=administratorController&action=deleteOnlineSlot', {
            method: 'POST',
            body: fd
        })
            .then(function (res) { return res.json(); })
            .then(function (res) {
                if (!res || !res.success) {
                    showMsg((res && res.message) || 'Failed to delete slot.', 'error');
                    return;
                }

                var row = btn.closest('tr');
                if (!row) {
                    return;
                }

                var tbody = row.parentElement;
                row.remove();
                ensureEmptyRow(tbody);
                showMsg('Slot deleted.', 'success');
            })
            .catch(function () {
                showMsg('Network error. Please try again.', 'error');
            });
    }

    function bindRowActions(root) {
        root.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.btn-slot-toggle');
            if (toggleBtn && root.contains(toggleBtn)) {
                handleToggle(root, toggleBtn);
                return;
            }

            var deleteBtn = e.target.closest('.btn-slot-delete');
            if (deleteBtn && root.contains(deleteBtn)) {
                handleDelete(root, deleteBtn);
            }
        });
    }

    function bindTabs(root) {
        root.querySelectorAll('.slots-day-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var dayKey = tab.getAttribute('data-tab') || 'mon_fri';
                updateDayView(root, dayKey);
            });
        });
    }

    function initOnlineSlots() {
        var root = document.getElementById('online-slots-root');
        if (!root || root.dataset.initialized === '1') {
            return;
        }

        root.dataset.initialized = '1';

        var form = root.querySelector('#online-slot-create-form');
        if (form) {
            onAddSlotSubmit(root, form);
        }

        bindTabs(root);
        bindRowActions(root);

        var firstTab = root.querySelector('.slots-day-tab.is-active');
        var activeDay = firstTab ? firstTab.getAttribute('data-tab') : 'mon_fri';
        updateDayView(root, activeDay || 'mon_fri');
    }

    window.initOnlineSlots = initOnlineSlots;

    document.addEventListener('DOMContentLoaded', function () {
        initOnlineSlots();
    });
})();
