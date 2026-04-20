<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (($_SESSION['user_role'] ?? '') !== 'receptionist') {
    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
    exit();
}

$appointmentChangePrefix = $appointmentChangePct >= 0 ? '+' : '';

$densityRows = [];
if (isset($appointmentDensityRows) && is_array($appointmentDensityRows)) {
    foreach ($appointmentDensityRows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $densityRows[] = [
            'label' => (string)($row['label'] ?? ''),
            'count' => intval($row['count'] ?? 0),
            'max_patients' => intval($row['max_patients'] ?? 0),
        ];
    }
}

$densityMax = 1;
foreach ($densityRows as $row) {
    $densityMax = max($densityMax, intval($row['count'] ?? 0));
}

$statusLabels = ['Confirmed', 'Completed', 'In-Progress', 'Cancelled'];
$statusValues = [
    intval($statusSnapshot['confirmed'] ?? 0),
    intval($statusSnapshot['completed'] ?? 0),
    intval($statusSnapshot['in_progress'] ?? 0),
    intval($statusSnapshot['cancelled'] ?? 0),
];
$statusColors = ['#23C06B', '#3DBDEC', '#F4B400', '#E74C3C'];

$typeLabels = ['Physical', 'Online Scheduled', 'Online Home Visit'];
$typeValues = [
    intval($appointmentTypes['physical'] ?? 0),
    intval($appointmentTypes['online_scheduled'] ?? 0),
    intval($appointmentTypes['online_home_visit'] ?? 0),
];
$typeColors = ['#1F9CDA', '#6BC4F7', '#0E6AA4'];

$chartPayload = [
    'status' => [
        'labels' => $statusLabels,
        'values' => $statusValues,
        'colors' => $statusColors,
        'total' => intval($statusTotal ?? 0),
    ],
    'types' => [
        'labels' => $typeLabels,
        'values' => $typeValues,
        'colors' => $typeColors,
        'total' => intval($appointmentTypeTotal ?? 0),
    ],
];
?>
<html>
    <head>
        <title>Receptionist Dashboard</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
        <link rel="stylesheet" href="/lab_sync/public/receptionistDashboard.css?v=20260420">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <section class="reports-dashboard receptionist-terminal-dashboard" aria-label="Receptionist Dashboard">
                    <?php
                        $pageTitle = 'Receptionist Dashboard';
                        $pageBreadcrumbText = 'Receptionist-Dashboard->';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>


                    <div class="rt-stats-grid" aria-label="Key Daily Stats">
                        <div class="rt-stat-card">
                            <div class="rt-stat-top">
                                <span class="rt-stat-icon rt-icon-appointments">C</span>
                                <span class="rt-stat-badge rt-badge-soft"><?php echo $appointmentChangePrefix . intval($appointmentChangePct); ?>% vs yest.</span>
                            </div>
                            <p class="rt-stat-label">Today's Appointments</p>
                            <p class="rt-stat-value"><?php echo number_format($appointmentsToday ?? 0); ?></p>
                        </div>
                        <div class="rt-stat-card">
                            <div class="rt-stat-top">
                                <span class="rt-stat-icon rt-icon-requests">+</span>
                                <span class="rt-stat-badge rt-badge-warning"><?php echo htmlspecialchars($pendingBadgeLabel); ?></span>
                            </div>
                            <p class="rt-stat-label">Pending Requests</p>
                            <p class="rt-stat-value"><?php echo str_pad((string) intval($pendingPrescriptions ?? 0), 2, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        <div class="rt-stat-card">
                            <div class="rt-stat-top">
                                <span class="rt-stat-icon rt-icon-bills">B</span>
                                <span class="rt-stat-badge rt-badge-danger"><?php echo htmlspecialchars($unpaidBadgeLabel); ?></span>
                            </div>
                            <p class="rt-stat-label">Unpaid Bills</p>
                            <p class="rt-stat-value">LKR <?php echo number_format($unpaidBillsAmount ?? 0, 0); ?></p>
                        </div>
                        <div class="rt-stat-card">
                            <div class="rt-stat-top">
                                <span class="rt-stat-icon rt-icon-registered">U</span>
                                <span class="rt-stat-badge rt-badge-success"><?php echo htmlspecialchars($registeredBadgeLabel); ?></span>
                            </div>
                            <p class="rt-stat-label">Registered Today</p>
                            <p class="rt-stat-value"><?php echo number_format($patientsToday ?? 0); ?></p>
                        </div>
                    </div>

                    <div class="rt-main-grid">
                        <article class="rt-panel rt-density-panel">
                            <h3 class="rt-panel-title">Appointment Density (Online Slots Today)</h3>
                            <div class="rt-density-list">
                                <?php if (empty($densityRows)): ?>
                                    <p class="rt-empty">No online slots configured for today.</p>
                                <?php else: ?>
                                    <?php foreach ($densityRows as $row): ?>
                                        <?php
                                            $slotLabel = htmlspecialchars((string)($row['label'] ?? ''));
                                            $count = intval($row['count'] ?? 0);
                                            $width = $densityMax > 0 ? intval(round(($count / $densityMax) * 100)) : 0;
                                        ?>
                                        <div class="rt-density-row">
                                            <span class="rt-density-slot"><?php echo $slotLabel; ?></span>
                                            <div class="rt-density-track"><span class="rt-density-fill" style="width: <?php echo $width; ?>%;"></span></div>
                                            <span class="rt-density-value"><?php echo str_pad((string)$count, 2, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </article>

                        <article class="rt-panel rt-status-panel">
                            <h3 class="rt-panel-title">Status Snapshot</h3>
                            <div class="rt-donut-wrap">
                                <canvas id="rtStatusChart" aria-label="Status Snapshot Chart"></canvas>
                                <div class="rt-donut-center">
                                    <span class="rt-donut-caption">Total</span>
                                    <strong><?php echo number_format($statusTotal ?? 0); ?></strong>
                                </div>
                            </div>
                            <ul class="rt-legend-list">
                                <?php for ($i = 0; $i < count($statusLabels); $i++): ?>
                                <li>
                                    <span class="rt-legend-meta"><span class="rt-dot" style="background: <?php echo $statusColors[$i]; ?>;"></span><?php echo htmlspecialchars($statusLabels[$i]); ?></span>
                                    <span><?php echo number_format($statusValues[$i]); ?></span>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </article>

                        <aside class="rt-panel rt-actions-panel">
                            <h3 class="rt-panel-title">Quick Actions</h3>
                            <div class="rt-action-stack">
                                <a class="rt-action-btn rt-action-primary" href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=receptionist">Book Appointment</a>
                                <a class="rt-action-btn" href="/lab_sync/index.php?controller=appointmentsController&action=index&section=prescription&role=receptionist">Prescription Queue</a>
                                <a class="rt-action-btn" href="/lab_sync/index.php?controller=financesController&action=index&role=receptionist">Billing &amp; Payments</a>
                            </div>
                        </aside>
                    </div>

                    <div class="rt-bottom-grid">
                        <article class="rt-panel rt-types-panel">
                            <h3 class="rt-panel-title">Appointment Type</h3>
                            <div class="rt-donut-wrap rt-donut-wrap-small">
                                <canvas id="rtTypeChart" aria-label="Appointment Type Chart"></canvas>
                                <div class="rt-donut-center">
                                    <strong><?php echo number_format($appointmentTypeTotal ?? 0); ?></strong>
                                </div>
                            </div>
                            <ul class="rt-legend-list rt-legend-list-tight">
                                <?php for ($i = 0; $i < count($typeLabels); $i++): ?>
                                    <?php
                                        $count = intval($typeValues[$i]);
                                        $pct = ($appointmentTypeTotal ?? 0) > 0 ? round(($count / $appointmentTypeTotal) * 100) : 0;
                                    ?>
                                    <li>
                                        <span class="rt-legend-meta"><span class="rt-dot" style="background: <?php echo $typeColors[$i]; ?>;"></span><?php echo htmlspecialchars($typeLabels[$i]); ?></span>
                                        <span><?php echo $count; ?> (<?php echo $pct; ?>%)</span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </article>

                        <article class="rt-panel rt-tests-panel">
                            <h3 class="rt-panel-title">Top Ordered Tests</h3>
                            <?php if (empty($topOrderedTests)): ?>
                                <p class="rt-empty">No tests have been ordered today.</p>
                            <?php else: ?>
                                <ul class="rt-tests-list">
                                    <?php foreach ($topOrderedTests as $test): ?>
                                        <?php
                                            $orders = intval($test['total_orders'] ?? 0);
                                            $pct = ($topOrderedTestsTotal ?? 0) > 0 ? round(($orders / $topOrderedTestsTotal) * 100) : 0;
                                        ?>
                                        <li>
                                            <span class="rt-test-name"><?php echo htmlspecialchars($test['test_name'] ?? 'Unknown Test'); ?></span>
                                            <span class="rt-test-percent"><?php echo $pct; ?>%</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </article>
                    </div>

                </section>
            </main>
        </div>
        <script>
            window.RECEPTIONIST_DASHBOARD_DATA = <?php echo json_encode($chartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        </script>
        <script src="/lab_sync/public/js/receptionistDashboard.js?v=20260420"></script>
    </body>
</html>
