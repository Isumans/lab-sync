<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
$csrfToken = (string)($csrfToken ?? ($_SESSION['csrf_token'] ?? ''));
unset($_SESSION['success'], $_SESSION['error']);
?>
<html>
<head>
    <title>Prescription Queue</title>
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
                    <h1>Prescription Queue</h1>
                    <button class="add-test-button"><a href="/lab_sync/index.php?controller=appointmentsController&action=index">Back to Appointments</a></button>
                </div>

                <div>
                    <p class="MC-p">Appointments -> Prescription Queue</p>
                </div>

                <div style="display:flex; gap:8px; margin: 8px 0 12px;">
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue&status=pending" style="padding:6px 10px; border-radius:6px; text-decoration:none; border:1px solid #d0d5dd; background:#fff; color:#344054;">Pending</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue&status=processed" style="padding:6px 10px; border-radius:6px; text-decoration:none; border:1px solid #d0d5dd; background:#fff; color:#344054;">Processed</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue&status=all" style="padding:6px 10px; border-radius:6px; text-decoration:none; border:1px solid #d0d5dd; background:#fff; color:#344054;">All</a>
                    <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionDecisionReport" style="padding:6px 10px; border-radius:6px; text-decoration:none; border:1px solid #174ea6; background:#174ea6; color:#fff;">Decisions Report</a>
                </div>

                <?php if (!empty($success)): ?>
                    <div style="margin: 10px 0; padding: 10px 12px; color: #0f7a3d; background: #e8f7ef; border: 1px solid #bbe8cc; border-radius: 8px;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div style="margin: 10px 0; padding: 10px 12px; color: #b32525; background: #fff0f0; border: 1px solid #f1c2c2; border-radius: 8px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="user-list">
                    <table class="test-catalog-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Patient</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Preferred Date</th>
                                <th>Preferred Time</th>
                                <th>Home Collection</th>
                                <th>Status</th>
                                <th>Decision</th>
                                <th>Linked Appointment</th>
                                <th>Prescription</th>
                                <th>Notes</th>
                                <th>Requested At</th>
                                <th>Decision At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requests)): ?>
                                <tr>
                                    <td colspan="15" style="text-align:center; color:#667085;">No prescription requests for this filter.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($requests as $request): ?>
                                    <?php $isPending = strtolower((string)($request['status'] ?? '')) === 'pending'; ?>
                                    <tr>
                                        <td><?php echo (int)$request['request_id']; ?></td>
                                        <td><?php echo htmlspecialchars($request['patient_name'] ?? ('Patient #' . (int)$request['patient_id'])); ?></td>
                                        <td><?php echo htmlspecialchars($request['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($request['contact_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($request['preferred_date'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($request['preferred_time'] ?: '-'); ?></td>
                                        <td><?php echo !empty($request['home_collection']) ? 'Yes' : 'No'; ?><?php if (!empty($request['collection_address'])): ?><div style="font-size:12px; color:#667085;"><?php echo htmlspecialchars($request['collection_address']); ?></div><?php endif; ?></td>
                                        <td><?php echo htmlspecialchars($request['status'] ?? 'Pending'); ?></td>
                                        <td><?php echo htmlspecialchars($request['decision_action'] ?: '-'); ?></td>
                                        <td>
                                            <?php if (!empty($request['linked_appointment_id'])): ?>
                                                #<?php echo (int)$request['linked_appointment_id']; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($request['prescription_file_path'])): ?>
                                                <a href="/lab_sync/<?php echo ltrim(htmlspecialchars($request['prescription_file_path']), '/'); ?>" target="_blank" rel="noopener">View File</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['notes'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($request['created_at'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($request['decision_at'] ?? '-'); ?></td>
                                        <td style="min-width: 240px;">
                                            <?php if ($isPending): ?>
                                                <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionRequestDetails&request_id=<?php echo (int)$request['request_id']; ?>" style="display:inline-block; margin:0 0 6px; padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054;">Details</a>
                                                <br>
                                                <form action="/lab_sync/index.php?controller=appointmentsController&action=processPrescriptionDecision" method="POST" style="display: inline-block; margin: 0 0 6px;">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                    <input type="hidden" name="decision" value="book_for_patient">
                                                    <button type="submit" style="padding:6px 10px; border:0; border-radius:6px; background:#174ea6; color:#fff; cursor:pointer;">Book for Patient</button>
                                                </form>

                                                <form action="/lab_sync/index.php?controller=appointmentsController&action=processPrescriptionDecision" method="POST" style="display: block; margin-top: 4px;">
                                                    <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                    <input type="hidden" name="decision" value="self_book">
                                                    <input type="text" name="decision_note" placeholder="Optional note" style="width:100%; max-width:220px; margin-bottom:6px; padding:6px; border:1px solid #d0d5dd; border-radius:6px;">
                                                    <button type="submit" style="padding:6px 10px; border:1px solid #344054; border-radius:6px; background:#fff; color:#344054; cursor:pointer;">Ask to Self-Book</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionRequestDetails&request_id=<?php echo (int)$request['request_id']; ?>" style="display:inline-block; margin:0 0 6px; padding:6px 10px; border:1px solid #d0d5dd; border-radius:6px; text-decoration:none; color:#344054;">Details</a>
                                                <br>
                                                <span style="color:#667085; font-size:13px;">Processed</span>
                                            <?php endif; ?>
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
