<?php
$slots = $slots ?? ['mon_fri' => [], 'sat' => [], 'sun' => []];
$dayLabels = ['mon_fri' => 'Mon - Fri', 'sat' => 'Saturday', 'sun' => 'Sunday'];

function formatSlotTime(string $time): string {
    $timestamp = strtotime($time);
    if ($timestamp === false) {
        return htmlspecialchars(substr($time, 0, 5));
    }
    return htmlspecialchars(date('h:i A', $timestamp));
}

function renderSlotRows(array $rows): void {
    if (empty($rows)) {
        echo '<tr class="slots-empty-row"><td colspan="5">No slots configured for this day yet.</td></tr>';
        return;
    }

    foreach ($rows as $row) {
        $id = (int)$row['id'];
        $start = formatSlotTime((string)$row['start_time']);
        $end = formatSlotTime((string)$row['end_time']);
        $maxPatients = (int)$row['max_patients'];
        $isActive = (int)$row['is_active'] === 1;
        $statusClass = $isActive ? 'is-active' : 'is-inactive';
        $statusText = $isActive ? 'Active' : 'Inactive';

        echo <<<HTML
        <tr data-slot-id="{$id}">
            <td class="slots-time">{$start}</td>
            <td class="slots-time">{$end}</td>
            <td>
                <div class="slots-capacity-wrap">
                    <div class="slots-capacity-bar"><span style="width: 100%;"></span></div>
                    <div class="slots-capacity-text">0 / {$maxPatients}</div>
                </div>
            </td>
            <td><span class="slots-status-badge {$statusClass}">{$statusText}</span></td>
            <td class="slots-actions-cell">
                <button type="button" class="slots-action-btn btn-slot-toggle" data-id="{$id}" title="Toggle slot status">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v6m0 0l-3-3m3 3l3-3"/><path d="M5 19h14"/></svg>
                </button>
                <button type="button" class="slots-action-btn btn-slot-delete" data-id="{$id}" title="Delete slot">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M9 7V5h6v2"/><path d="M8 7l1 12h6l1-12"/></svg>
                </button>
            </td>
        </tr>
        HTML;
    }
}
?>

<div id="online-slots-root" class="slots-config-shell" data-active-day="mon_fri">
    <div class="slots-config-head">
        <div>
            <h3>Time Slot Configuration</h3>
            <p>Manage active patient appointment windows for online scheduling.</p>
        </div>
        <div class="slots-day-tabs" role="tablist" aria-label="Day groups">
            <?php foreach ($dayLabels as $key => $label): ?>
                <button type="button" class="slots-day-tab <?php echo $key === 'mon_fri' ? 'is-active' : ''; ?>" data-tab="<?php echo htmlspecialchars($key); ?>" role="tab" aria-selected="<?php echo $key === 'mon_fri' ? 'true' : 'false'; ?>">
                    <?php echo htmlspecialchars($label); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="online-slots-msg" class="slots-msg" aria-live="polite"></div>

    <div class="slots-layout-grid">
        <section class="slots-panel slots-grid-panel">
            <div class="slots-panel-header">
                <h4>Active Scheduling Grid</h4>
                <span class="slots-pill">Slots Enabled</span>
            </div>

            <?php foreach ($dayLabels as $key => $label): ?>
                <div class="slots-day-panel <?php echo $key === 'mon_fri' ? 'is-active' : ''; ?>" data-panel="<?php echo htmlspecialchars($key); ?>">
                    <table class="slots-grid-table">
                        <thead>
                            <tr>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Max Patients</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="slots-body-<?php echo htmlspecialchars($key); ?>">
                            <?php renderSlotRows($slots[$key] ?? []); ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </section>

        <aside class="slots-panel slots-form-panel">
            <div class="slots-panel-header">
                <h4>Create New Slot</h4>
            </div>

            <p class="slots-form-meta">Adding for: <strong id="slots-current-day">Mon - Fri</strong></p>

            <form id="online-slot-create-form" class="slots-create-form">
                <input type="hidden" name="day_group" id="slots-day-group" value="mon_fri">

                <label class="slots-field">
                    <span>Start Time</span>
                    <input type="time" name="start_time" required>
                </label>

                <label class="slots-field">
                    <span>End Time</span>
                    <input type="time" name="end_time" required>
                </label>

                <label class="slots-field">
                    <span>Patient Limit</span>
                    <input type="number" name="max_patients" min="1" max="100" value="20" required>
                </label>

                <button type="submit" class="slots-submit-btn">Confirm Slot Addition</button>
            </form>
        </aside>
    </div>

    
</div>
