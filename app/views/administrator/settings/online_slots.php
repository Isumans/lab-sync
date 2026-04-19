<?php
// $slots passed from controller: ['mon_fri' => [...], 'sat' => [...], 'sun' => [...]]
$slots = $slots ?? ['mon_fri' => [], 'sat' => [], 'sun' => []];

function renderSlotRows(array $rows): void {
    if (empty($rows)) {
        echo '<tr class="no-slots-row"><td colspan="5" style="text-align:center;color:#888;padding:18px;">No slots defined yet.</td></tr>';
        return;
    }
    foreach ($rows as $row) {
        $id       = (int)$row['id'];
        $start    = htmlspecialchars(substr($row['start_time'], 0, 5));
        $end      = htmlspecialchars(substr($row['end_time'],   0, 5));
        $max      = (int)$row['max_patients'];
        $active   = (int)$row['is_active'];
        $badgeCls = $active ? 'badge-active' : 'badge-inactive';
        $badgeTxt = $active ? 'Active' : 'Inactive';
        echo <<<HTML
        <tr data-slot-id="{$id}">
            <td>{$start}</td>
            <td>{$end}</td>
            <td>{$max}</td>
            <td><span class="slot-badge {$badgeCls}">{$badgeTxt}</span></td>
            <td class="slot-actions">
                <button class="btn-slot-toggle" data-id="{$id}" title="Toggle active">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                </button>
                <button class="btn-slot-delete" data-id="{$id}" title="Delete slot">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
            </td>
        </tr>
        HTML;
    }
}
?>

<style>
.slots-tabs { display:flex; gap:8px; margin-bottom:18px; border-bottom:2px solid #e5e9f0; padding-bottom:0; }
.slots-tab-btn {
    padding:8px 20px; border:none; background:none; cursor:pointer;
    font-size:14px; font-weight:500; color:#6b7a99; border-bottom:2px solid transparent;
    margin-bottom:-2px; transition:color .15s, border-color .15s;
}
.slots-tab-btn.active { color:#2563eb; border-bottom-color:#2563eb; }
.slots-tab-panel { display:none; }
.slots-tab-panel.active { display:block; }

.slots-table { width:100%; border-collapse:collapse; font-size:14px; }
.slots-table th {
    text-align:left; padding:10px 12px; background:#f4f6fb;
    color:#5a6580; font-weight:600; border-bottom:1px solid #e2e6ef;
}
.slots-table td { padding:10px 12px; border-bottom:1px solid #f0f2f8; vertical-align:middle; }
.slots-table tr:last-child td { border-bottom:none; }

.slot-badge { display:inline-block; padding:2px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.badge-active   { background:#d1fae5; color:#065f46; }
.badge-inactive { background:#f3f4f6; color:#6b7280; }

.slot-actions { display:flex; gap:6px; }
.btn-slot-toggle, .btn-slot-delete {
    display:inline-flex; align-items:center; justify-content:center;
    padding:5px; border-radius:6px; border:1px solid #e2e6ef;
    background:#fff; cursor:pointer; color:#4a5568; transition:background .15s,color .15s;
}
.btn-slot-toggle:hover { background:#e0f2fe; color:#0284c7; }
.btn-slot-delete:hover  { background:#fee2e2; color:#dc2626; }

.add-slot-form {
    display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;
    margin-top:18px; padding:16px; background:#f7f9fc;
    border:1px solid #e2e6ef; border-radius:10px;
}
.add-slot-form .form-group { display:flex; flex-direction:column; gap:4px; }
.add-slot-form label { font-size:12px; font-weight:600; color:#6b7a99; }
.add-slot-form input[type="time"],
.add-slot-form input[type="number"] {
    padding:7px 10px; border:1px solid #d1d8e8; border-radius:7px;
    font-size:14px; outline:none; background:#fff;
    transition:border-color .15s;
}
.add-slot-form input:focus { border-color:#2563eb; }
.btn-add-slot {
    padding:8px 20px; background:#2563eb; color:#fff; border:none;
    border-radius:7px; font-size:14px; font-weight:600; cursor:pointer;
    transition:background .15s; align-self:flex-end;
}
.btn-add-slot:hover { background:#1d4ed8; }
.slots-msg { margin-bottom:12px; padding:10px 14px; border-radius:8px; font-size:14px; display:none; }
.slots-msg.success { background:#d1fae5; color:#065f46; }
.slots-msg.error   { background:#fee2e2; color:#dc2626; }
</style>

<div id="online-slots-msg" class="slots-msg"></div>

<div class="config-section">
    <h3>Online Booking Time Slots</h3>
    <p style="color:#6b7a99;font-size:14px;margin-bottom:16px;">
        Define the time slots available for online patient booking, per day group. When a slot reaches its patient limit for a given date, it will no longer be shown as available.
    </p>

    <div class="slots-tabs">
        <button class="slots-tab-btn active" data-tab="mon_fri">Mon – Fri</button>
        <button class="slots-tab-btn" data-tab="sat">Saturday</button>
        <button class="slots-tab-btn" data-tab="sun">Sunday</button>
    </div>

    <?php foreach (['mon_fri' => 'Mon – Fri', 'sat' => 'Saturday', 'sun' => 'Sunday'] as $key => $label): ?>
    <div class="slots-tab-panel <?= $key === 'mon_fri' ? 'active' : '' ?>" id="panel-<?= $key ?>" data-day-group="<?= $key ?>">
        <table class="slots-table">
            <thead>
                <tr>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Max Patients</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="slots-body-<?= $key ?>">
                <?php renderSlotRows($slots[$key]); ?>
            </tbody>
        </table>

        <form class="add-slot-form" onsubmit="addSlot(event, '<?= $key ?>')">
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" required>
            </div>
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" required>
            </div>
            <div class="form-group">
                <label>Max Patients</label>
                <input type="number" name="max_patients" min="1" max="100" value="4" required style="width:80px;">
            </div>
            <button type="submit" class="btn-add-slot">Add Slot</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

<script src="/lab_sync/public/js/onlineSlots.js?v=1"></script>
