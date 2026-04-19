<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (($_SESSION['user_role'] ?? '') !== 'receptionist') {
    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
    exit();
}
?>
<html>
    <head>
        <title>Receptionist Dashboard</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/dashboardCards.css">
        <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
        <style>
            .dash-table-section {
                background: var(--secondary-color);
                border-radius: 15px;
                padding: 24px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                margin-top: 24px;
            }
            .dash-table-section h3 {
                font-size: 1rem;
                font-weight: 600;
                margin: 0 0 16px 0;
                color: var(--font-color);
            }
            .dash-table {
                width: 100%;
                border-collapse: collapse;
            }
            .dash-table th {
                text-align: left;
                padding: 10px 12px;
                font-size: 0.8rem;
                color: #7c8ba3;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                border-bottom: 1px solid rgba(0,0,0,0.08);
            }
            .dash-table td {
                padding: 10px 12px;
                font-size: 0.875rem;
                border-bottom: 1px solid rgba(0,0,0,0.05);
                color: var(--font-color);
            }
            .dash-table tr:last-child td { border-bottom: none; }
            .dash-table tr:hover td { background: rgba(61,189,236,0.04); }
            .status-badge {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 0.75rem;
                font-weight: 600;
            }
            .status-Pending   { background: rgba(230,126,34,0.12); color: #e67e22; }
            .status-Confirmed { background: rgba(46,213,115,0.12); color: #2ed573; }
            .status-Completed { background: rgba(61,189,236,0.12); color: #3DBDEC; }
            .status-Cancelled { background: rgba(255,71,87,0.12);  color: #ff4757; }
            .quick-links { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; }
            .quick-link-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 10px 18px;
                border-radius: 8px;
                background: var(--primary-color);
                color: #fff;
                font-size: 0.875rem;
                font-weight: 500;
                text-decoration: none;
                transition: opacity 0.2s;
            }
            .quick-link-btn:hover { opacity: 0.85; }
            .empty-state {
                text-align: center;
                color: #7c8ba3;
                padding: 32px 0;
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <section class="reports-dashboard" aria-label="Receptionist Dashboard">
                    <?php
                        $pageTitle = 'Dashboard';
                        $pageBreadcrumbText = 'Dashboard';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>

                    <h3>Your Overview</h3>

                    <!-- Metric Cards -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-icon order-icon">📅</div>
                            <div class="metric-content">
                                <h3>Appointments Today</h3>
                                <p class="metric-value"><?php echo number_format($appointmentsToday ?? 0); ?></p>
                                <span class="metric-change">Scheduled for today</span>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon pending-icon">📋</div>
                            <div class="metric-content">
                                <h3>Prescription Requests</h3>
                                <p class="metric-value"><?php echo number_format($pendingPrescriptions ?? 0); ?></p>
                                <span class="metric-change <?php echo ($pendingPrescriptions > 0) ? 'down' : ''; ?>">
                                    <?php echo ($pendingPrescriptions > 0) ? 'Awaiting review' : 'All reviewed'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon sales-icon">💳</div>
                            <div class="metric-content">
                                <h3>Unpaid Bills</h3>
                                <p class="metric-value"><?php echo number_format($unpaidBills ?? 0); ?></p>
                                <span class="metric-change <?php echo ($unpaidBills > 0) ? 'down' : ''; ?>">
                                    <?php echo ($unpaidBills > 0) ? 'Pending payment' : 'All settled'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon user-icon">👤</div>
                            <div class="metric-content">
                                <h3>New Patients Today</h3>
                                <p class="metric-value"><?php echo number_format($patientsToday ?? 0); ?></p>
                                <span class="metric-change">Registered today</span>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Appointments Table -->
                    <div class="dash-table-section">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h3 style="margin:0;">Today's Appointments</h3>
                            <a href="/lab_sync/index.php?controller=appointmentsController&action=index&role=receptionist"
                               style="font-size:0.85rem;color:var(--primary-color);text-decoration:none;">View all →</a>
                        </div>
                        <?php if (empty($todaysAppointments)): ?>
                        <p class="empty-state">No appointments scheduled for today.</p>
                        <?php else: ?>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Test</th>
                                    <th>Time</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todaysAppointments as $appt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appt['patient_name'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($appt['test_name'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($appt['appointment_time'] ?? '', 0, 5)); ?></td>
                                    <td style="text-transform:capitalize;"><?php echo htmlspecialchars($appt['method'] ?? '—'); ?></td>
                                    <td>
                                        <?php
                                            $status = htmlspecialchars($appt['status'] ?? 'Pending');
                                            $cls = 'status-' . str_replace(' ', '', $status);
                                        ?>
                                        <span class="status-badge <?php echo $cls; ?>"><?php echo $status; ?></span>
                                    </td>
                                    <td>
                                        <a href="/lab_sync/index.php?controller=appointmentsController&action=index&role=receptionist"
                                           style="font-size:0.8rem;color:var(--primary-color);text-decoration:none;">Manage</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Links -->
                    <div class="quick-links">
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=receptionist">📅 Book Appointment</a>
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=appointmentsController&action=prescriptionQueue">📋 Prescription Queue</a>
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=TestCatalog&action=test_catalog&role=receptionist">🧪 Test Catalog</a>
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=financesController&action=index&role=receptionist">💳 Billing</a>
                    </div>

                </section>
            </main>
        </div>
    </body>
</html>
