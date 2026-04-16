<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>
    <title>Prescription Decisions Report</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <div class="Tmain-content">
                <div class="test-catalog-header">
                    <h1>Prescription Decisions</h1>
                    <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=index">Back to Appointments</a></button>
                </div>

                <div>
                    <p class="MC-p">Appointments -> Prescription Decisions Report</p>
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap; margin: 8px 0 12px;">
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=index" style="padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054; background:#fff;">Back to Appointments</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue" style="padding:6px 10px; border:1px solid #174ea6; border-radius:6px; text-decoration:none; color:#fff; background:#174ea6;">Prescription Queue</a>
                </div>

                <?php
                    $today = date('Y-m-d');
                    $last7 = date('Y-m-d', strtotime('-6 days'));
                    $monthStart = date('Y-m-01');

                    $baseFilter =
                        '&status=' . urlencode((string)($filters['status'] ?? '')) .
                        '&decision_action=' . urlencode((string)($filters['decision_action'] ?? '')) .
                        '&decision_by_user_id=' . (int)($filters['decision_by_user_id'] ?? 0);
                ?>

                <div style="display:flex; gap:8px; flex-wrap:wrap; margin: 8px 0 12px;">
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&date_from=<?php echo $today; ?>&date_to=<?php echo $today; ?><?php echo $baseFilter; ?>" style="padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054; background:#fff;">Today</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&date_from=<?php echo $last7; ?>&date_to=<?php echo $today; ?><?php echo $baseFilter; ?>" style="padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054; background:#fff;">Last 7 Days</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&date_from=<?php echo $monthStart; ?>&date_to=<?php echo $today; ?><?php echo $baseFilter; ?>" style="padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054; background:#fff;">This Month</a>
                </div>

                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:8px; margin: 10px 0 12px;">
                    <div style="border:1px solid #e4e7ec; border-radius:8px; padding:10px; background:#fff;"><div style="font-size:12px; color:#667085;">Total</div><div style="font-size:20px; font-weight:700; color:#101828;"><?php echo (int)($summary['total_requests'] ?? 0); ?></div></div>
                    <div style="border:1px solid #e4e7ec; border-radius:8px; padding:10px; background:#fff;"><div style="font-size:12px; color:#667085;">Pending</div><div style="font-size:20px; font-weight:700; color:#b54708;"><?php echo (int)($summary['pending'] ?? 0); ?></div></div>
                    <div style="border:1px solid #e4e7ec; border-radius:8px; padding:10px; background:#fff;"><div style="font-size:12px; color:#667085;">Processed</div><div style="font-size:20px; font-weight:700; color:#175cd3;"><?php echo (int)($summary['processed'] ?? 0); ?></div></div>
                    <div style="border:1px solid #e4e7ec; border-radius:8px; padding:10px; background:#fff;"><div style="font-size:12px; color:#667085;">Booked by Receptionist</div><div style="font-size:20px; font-weight:700; color:#067647;"><?php echo (int)($summary['booked_by_receptionist'] ?? 0); ?></div></div>
                    <div style="border:1px solid #e4e7ec; border-radius:8px; padding:10px; background:#fff;"><div style="font-size:12px; color:#667085;">Self Book Requested</div><div style="font-size:20px; font-weight:700; color:#344054;"><?php echo (int)($summary['self_book_requested'] ?? 0); ?></div></div>
                </div>

                <form method="GET" action="/lab_sync/index.php" style="margin: 10px 0 14px; display:flex; gap:8px; flex-wrap:wrap; align-items:flex-end;">
                    <input type="hidden" name="controller" value="appointmentsController">
                    <input type="hidden" name="action" value="prescriptionDecisionReport">

                    <div>
                        <label for="status" style="display:block; font-size:12px; color:#475467;">Status</label>
                        <input type="text" id="status" name="status" value="<?php echo htmlspecialchars((string)($filters['status'] ?? '')); ?>" placeholder="Booked by Receptionist" style="padding:6px; border:1px solid #d0d5dd; border-radius:6px;">
                    </div>

                    <div>
                        <label for="decision_action" style="display:block; font-size:12px; color:#475467;">Decision Action</label>
                        <select id="decision_action" name="decision_action" style="padding:6px; border:1px solid #d0d5dd; border-radius:6px;">
                            <option value="">All</option>
                            <option value="self_book" <?php echo (($filters['decision_action'] ?? '') === 'self_book') ? 'selected' : ''; ?>>self_book</option>
                            <option value="book_for_patient" <?php echo (($filters['decision_action'] ?? '') === 'book_for_patient') ? 'selected' : ''; ?>>book_for_patient</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_from" style="display:block; font-size:12px; color:#475467;">Decision Date From</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars((string)($filters['date_from'] ?? '')); ?>" style="padding:6px; border:1px solid #d0d5dd; border-radius:6px;">
                    </div>

                    <div>
                        <label for="date_to" style="display:block; font-size:12px; color:#475467;">Decision Date To</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars((string)($filters['date_to'] ?? '')); ?>" style="padding:6px; border:1px solid #d0d5dd; border-radius:6px;">
                    </div>

                    <div>
                        <label for="decision_by_user_id" style="display:block; font-size:12px; color:#475467;">Decision By User ID</label>
                        <input type="number" id="decision_by_user_id" name="decision_by_user_id" min="0" value="<?php echo (int)($filters['decision_by_user_id'] ?? 0); ?>" style="padding:6px; border:1px solid #d0d5dd; border-radius:6px; width:140px;">
                    </div>

                    <button type="submit" style="padding:6px 10px; border:0; border-radius:6px; background:#174ea6; color:#fff; cursor:pointer;">Apply</button>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport" style="padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054;">Reset</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport&status=<?php echo urlencode((string)($filters['status'] ?? '')); ?>&decision_action=<?php echo urlencode((string)($filters['decision_action'] ?? '')); ?>&date_from=<?php echo urlencode((string)($filters['date_from'] ?? '')); ?>&date_to=<?php echo urlencode((string)($filters['date_to'] ?? '')); ?>&decision_by_user_id=<?php echo (int)($filters['decision_by_user_id'] ?? 0); ?>&format=csv" style="padding:6px 10px; border:1px solid #12b76a; border-radius:6px; text-decoration:none; color:#027a48;">Export CSV</a>
                </form>

                <div class="user-list">
                    <table class="test-catalog-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Patient</th>
                                <th>Status</th>
                                <th>Decision</th>
                                <th>By User</th>
                                <th>Linked Appointment</th>
                                <th>Decision At</th>
                                <th>Requested At</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportRows)): ?>
                                <tr>
                                    <td colspan="10" style="text-align:center; color:#667085;">No decision records found for current filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportRows as $row): ?>
                                    <tr>
                                        <td><?php echo (int)$row['request_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name'] ?? ('Patient #' . (int)$row['patient_id'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['status'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['decision_action'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $uid = (int)($row['decision_by_user_id'] ?? 0);
                                                $uname = trim((string)($row['decision_by_username'] ?? ''));
                                                echo $uid > 0 ? ('#' . $uid . ($uname !== '' ? (' (' . htmlspecialchars($uname) . ')') : '')) : '-';
                                            ?>
                                        </td>
                                        <td><?php echo !empty($row['linked_appointment_id']) ? ('#' . (int)$row['linked_appointment_id']) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($row['decision_at'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['created_at'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['notes'] ?? '-'); ?></td>
                                        <td>
                                            <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionRequestDetails&request_id=<?php echo (int)$row['request_id']; ?>" style="padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054;">View Timeline</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
