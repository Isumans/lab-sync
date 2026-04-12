<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>
    <title>Report Details</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="reports-dashboard" aria-label="Report Details">

                <div class="main-content-header">
                    <h1>Reports Details</h1>
                    <p class="MC-p"><a href="javascript:history.back()" style="color: var(--primary-color); text-decoration: none;">Reports-></a>Report Details</p><br>
                </div>
                <!-- <div class="rd-header-row">
                    <h1 class="rd-title">Report Details</h1>
                    

                    <div class="rd-header-actions">
                        <a class="rd-btn rd-btn-muted" href="/lab_sync/index.php?controller=reportsController&action=index&role=<?php echo urlencode($role ?? ''); ?>" style="text-decoration: none; display: inline-flex; align-items: center;">Back to Reports</a>
                    </div>
                </div> -->
                

                <section class="rd-filter-card" aria-label="Appointment Summary">
                    <div class="rd-filter-grid" style="grid-template-columns: repeat(4, minmax(140px, 1fr));">
                        <div class="rd-filter-field">
                            <label>Appointment ID</label>
                            <input type="text" value="APP-<?php echo str_pad((string) intval($appointment['appointment_id'] ?? 0), 4, '0', STR_PAD_LEFT); ?>" readonly>
                        </div>
                        <div class="rd-filter-field">
                            <label>Patient Name</label>
                            <input type="text" value="<?php echo htmlspecialchars((string) ($appointment['patient_name'] ?? 'Unknown Patient')); ?>" readonly>
                        </div>
                        <div class="rd-filter-field">
                            <label>Date</label>
                            <input type="text" value="<?php echo htmlspecialchars((string) ($appointment['appointment_date'] ?? '')); ?>" readonly>
                        </div>
                        <div class="rd-filter-field">
                            <label>Progress</label>
                            <input type="text" value="<?php echo intval($summary['completed_tests'] ?? 0); ?>/<?php echo intval($summary['total_tests'] ?? 0); ?> (<?php echo intval($summary['overall_progress'] ?? 0); ?>%)" readonly>
                        </div>
                    </div>
                </section>

                <section class="rd-table-card" aria-label="Test Details">
                    <div class="rd-table-wrap">
                        <table class="rd-table">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Completed At</th>
                                    <th>Authorized At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tests)): ?>
                                    <?php foreach ($tests as $test): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string) ($test['test_name'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($test['category'] ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($test['status'] ?? 'PENDING')); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($test['completed_at'] ?? '-')); ?></td>
                                            <td><?php echo htmlspecialchars((string) ($test['authorized_at'] ?? '-')); ?></td>
                                            <td>
                                                <?php if (($test['status'] ?? '') === 'IN_PROGRESS'): ?>
                                                    <button
                                                        type="button"
                                                        class="rd-btn rd-btn-primary js-enter-values-btn"
                                                        data-appointment-id="<?php echo intval($appointment['appointment_id'] ?? 0); ?>"
                                                        data-test-id="<?php echo intval($test['test_id'] ?? 0); ?>"
                                                        data-test-name="<?php echo htmlspecialchars((string) ($test['test_name'] ?? ''), ENT_QUOTES); ?>"
                                                        data-patient-name="<?php echo htmlspecialchars((string) ($appointment['patient_name'] ?? 'Unknown Patient'), ENT_QUOTES); ?>"
                                                        data-patient-pid="<?php echo htmlspecialchars((string) ($appointment['pid'] ?? ''), ENT_QUOTES); ?>"
                                                    >
                                                        Enter Values
                                                    </button>
                                                <?php elseif (($test['status'] ?? '') === 'COMPLETED'): ?>
                                                    <a href="/lab_sync/index.php?controller=reportsController&action=viewTestDetails&test_id=<?php echo urlencode((string) ($test['test_id'] ?? '')); ?>" class="rd-btn rd-btn-secondary" style="text-decoration: none;">View Report</a>
                                                    <a href="/lab_sync/index.php?controller=reportsController&action=viewTestDetails&test_id=<?php echo urlencode((string) ($test['test_id'] ?? '')); ?>&download=1" class="rd-btn rd-btn-muted" style="text-decoration: none;">Authorize</a>
                                                <?php else: ?>
                                                    <span style="color: var(--text-muted);">No actions available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="rd-empty-row">
                                        <td colspan="6">No tests found for this appointment.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="rd-table-footer">
                        <p>Billing: <?php echo number_format((float) ($billing['total_fee'] ?? 0), 2); ?> | Status: <?php echo htmlspecialchars((string) ($billing['payment_status'] ?? '')); ?> | Ref: <?php echo htmlspecialchars((string) ($billing['reference'] ?? '')); ?></p>
                    </div>
                </section>

                <div id="rdEnterValuesModal" class="rd-enter-modal" aria-hidden="true">
                    <div class="rd-enter-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rdEnterValuesTitle">
                        <div class="rd-enter-modal__header">
                            <div>
                                <h2 id="rdEnterValuesTitle">Enter Test Results</h2>
                                <p id="rdEnterValuesPatientLine" class="rd-enter-modal__meta">Patient details</p>
                            </div>
                            <button type="button" class="rd-enter-modal__close" id="rdEnterValuesClose" aria-label="Close modal">&times;</button>
                        </div>

                        <p id="rdEnterValuesHint" class="rd-enter-modal__hint">Provide measured values and verify references before saving.</p>

                        <form id="rdEnterValuesForm" class="rd-enter-modal__form">
                            <input type="hidden" id="rdEnterValuesAppointmentId" name="appointment_id" value="">
                            <input type="hidden" id="rdEnterValuesTestId" name="test_id" value="">

                            <div id="rdEnterValuesAlert" class="rd-enter-modal__alert" hidden></div>
                            <div id="rdEnterValuesFields" class="rd-enter-modal__fields"></div>

                            <label for="rdEnterValuesRemarks" class="rd-enter-modal__remarks-label">General Remarks</label>
                            <textarea id="rdEnterValuesRemarks" name="remarks" rows="4" placeholder="Add technician observations or quality notes..."></textarea>

                            <div class="rd-enter-modal__actions">
                                <button type="button" class="rd-btn rd-btn-muted" id="rdSaveDraftBtn">Save Draft</button>
                                <button type="button" class="rd-btn rd-btn-primary" id="rdSaveReadyBtn">Save &amp; Mark as Ready</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="/lab_sync/public/js/reportEnterValuesModal.js"></script>
</body>
</html>
