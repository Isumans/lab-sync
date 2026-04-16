<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>
    <title>Prescription Request Details</title>
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
                    <h1>Request #<?php echo (int)$request['request_id']; ?> Details</h1>
                    <div style="display:flex; gap:8px;">
                        <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue">Back to Queue</a></button>
                        <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport">Decisions Report</a></button>
                    </div>
                </div>

                <div>
                    <p class="MC-p">Appointments -> Prescription Request Details</p>
                </div>

                <div class="user-list" style="margin-bottom: 12px;">
                    <table class="test-catalog-table">
                        <tbody>
                            <tr><th style="width:220px;">Patient</th><td><?php echo htmlspecialchars($request['patient_name'] ?? ('Patient #' . (int)$request['patient_id'])); ?></td></tr>
                            <tr><th>Email</th><td><?php echo htmlspecialchars($request['email'] ?? '-'); ?></td></tr>
                            <tr><th>Contact</th><td><?php echo htmlspecialchars($request['contact_number'] ?? '-'); ?></td></tr>
                            <tr><th>Status</th><td><?php echo htmlspecialchars($request['status'] ?? '-'); ?></td></tr>
                            <tr><th>Decision</th><td><?php echo htmlspecialchars($request['decision_action'] ?? '-'); ?></td></tr>
                            <tr><th>Linked Appointment</th><td><?php echo !empty($request['linked_appointment_id']) ? ('#' . (int)$request['linked_appointment_id']) : '-'; ?></td></tr>
                            <tr><th>Preferred Date/Time</th><td><?php echo htmlspecialchars(($request['preferred_date'] ?? '-') . ' ' . ($request['preferred_time'] ?? '')); ?></td></tr>
                            <tr><th>Home Collection</th><td><?php echo !empty($request['home_collection']) ? 'Yes' : 'No'; ?><?php if (!empty($request['collection_address'])): ?><div style="color:#667085; margin-top:4px;"><?php echo htmlspecialchars($request['collection_address']); ?></div><?php endif; ?></td></tr>
                            <tr><th>Submitted At</th><td><?php echo htmlspecialchars($request['created_at'] ?? '-'); ?></td></tr>
                            <tr><th>Decision At</th><td><?php echo htmlspecialchars($request['decision_at'] ?? '-'); ?></td></tr>
                            <tr><th>Prescription</th><td>
                                <?php if (!empty($request['prescription_file_path'])): ?>
                                    <a href="/lab_sync/<?php echo ltrim(htmlspecialchars($request['prescription_file_path']), '/'); ?>" target="_blank" rel="noopener">View File</a>
                                <?php else: ?>-
                                <?php endif; ?>
                            </td></tr>
                            <tr><th>Notes</th><td><?php echo nl2br(htmlspecialchars($request['notes'] ?? '-')); ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <h2 class="heading3" style="margin-top:10px;">Lifecycle Timeline</h2>
                <div class="user-list">
                    <table class="test-catalog-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>From</th>
                                <th>To</th>
                                <th>By</th>
                                <th>At</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#667085;">No timeline events found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($event['event_type'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($event['old_status'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($event['new_status'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $uid = (int)($event['created_by_user_id'] ?? 0);
                                                $uname = trim((string)($event['created_by_username'] ?? ''));
                                                echo $uid > 0 ? ('#' . $uid . ($uname !== '' ? (' (' . htmlspecialchars($uname) . ')') : '')) : 'System';
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['created_at'] ?? '-'); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($event['note'] ?? '-')); ?></td>
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
