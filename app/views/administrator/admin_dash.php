<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
    exit();
}

$statusRows = [
    [
        'label' => 'Pending',
        'value' => intval($testStatus['pending'] ?? 0),
        'color' => '#f4b400',
    ],
    [
        'label' => 'In Progress',
        'value' => intval($testStatus['in_progress'] ?? 0),
        'color' => '#3DBDEC',
    ],
    [
        'label' => 'Completed',
        'value' => intval($testStatus['completed'] ?? 0),
        'color' => '#2ed573',
    ],
    [
        'label' => 'Authenticated',
        'value' => intval($testStatus['authenticated'] ?? 0),
        'color' => '#8b5cf6',
    ],
];

$totalTests = array_sum(array_map(function ($row) {
    return intval($row['value']);
}, $statusRows));
$processingCount = intval($testStatus['pending'] ?? 0) + intval($testStatus['in_progress'] ?? 0);
$processingPct = $totalTests > 0 ? intval(round(($processingCount / $totalTests) * 100)) : 0;

$staffRoleRows = [
    [
        'label' => 'Technicians',
        'count' => intval($staffCounts['technician'] ?? 0),
        'icon' => 'T',
    ],
    [
        'label' => 'Admin',
        'count' => intval($staffCounts['admin'] ?? 0),
        'icon' => 'A',
    ],
    [
        'label' => 'Receptionist',
        'count' => intval($staffCounts['receptionist'] ?? 0),
        'icon' => 'R',
    ],
];

$maxStaffCount = 1;
foreach ($staffRoleRows as $staffRow) {
    $maxStaffCount = max($maxStaffCount, intval($staffRow['count']));
}

$pendingPaymentsBadge = intval($unpaidBills ?? 0) > 0 ? 'Urgent' : 'Clear';

function adminInventorySeverity(int $qty, int $reorder): string {
    if ($reorder <= 0) {
        return 'ok';
    }

    $ratio = $qty / $reorder;
    if ($ratio <= 0.3) {
        return 'critical';
    }
    if ($ratio <= 0.8) {
        return 'warning';
    }
    return 'ok';
}
?>
<html>
    <head>
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
        <link rel="stylesheet" href="/lab_sync/public/adminDashboard.css?v=20260420">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <section class="reports-dashboard admin-ops-dashboard" aria-label="Admin Dashboard">
                    <?php
                        $pageTitle = 'Administrator Dashboard';
                        $pageBreadcrumbText = 'Admin-Dashboard->';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>



                    <div class="ad-stats-grid" aria-label="Top Key Metrics">
                        <article class="ad-stat-card">
                            <div class="ad-stat-top">
                                <span class="ad-stat-icon ad-icon-patients">A</span>
                                <span class="ad-stat-badge ad-badge-soft"><?php echo ($patientGrowthPct >= 0 ? '+' : '') . intval($patientGrowthPct); ?>%</span>
                            </div>
                            <p class="ad-stat-label">Total Patients</p>
                            <p class="ad-stat-value"><?php echo number_format($patientsThisWeek ?? 0); ?></p>
                            <p class="ad-stat-sub">This week's volume</p>
                        </article>

                        <article class="ad-stat-card">
                            <div class="ad-stat-top">
                                <span class="ad-stat-icon ad-icon-appointments">B</span>
                                <span class="ad-stat-badge ad-badge-neutral">Today</span>
                            </div>
                            <p class="ad-stat-label">Appointments</p>
                            <p class="ad-stat-value"><?php echo number_format($appointmentsToday ?? 0); ?></p>
                            <p class="ad-stat-sub">Scheduled today</p>
                        </article>

                        <article class="ad-stat-card">
                            <div class="ad-stat-top">
                                <span class="ad-stat-icon ad-icon-payments">C</span>
                                <span class="ad-stat-badge ad-badge-danger"><?php echo htmlspecialchars($pendingPaymentsBadge); ?></span>
                            </div>
                            <p class="ad-stat-label">Pending Payments</p>
                            <p class="ad-stat-value"><?php echo number_format($unpaidBills ?? 0); ?></p>
                            <p class="ad-stat-sub">Needs attention</p>
                        </article>

                        <article class="ad-stat-card">
                            <div class="ad-stat-top">
                                <span class="ad-stat-icon ad-icon-reports">D</span>
                                <span class="ad-stat-badge ad-badge-neutral">Queue</span>
                            </div>
                            <p class="ad-stat-label">Pending Reports</p>
                            <p class="ad-stat-value"><?php echo number_format($pendingReports ?? 0); ?></p>
                            <p class="ad-stat-sub">In processing</p>
                        </article>
                    </div>

                    <div class="ad-main-grid">
                        <article class="ad-panel ad-revenue-panel">
                            <div class="ad-panel-header">
                                <h3 class="ad-panel-title">Monthly Revenue Trend</h3>
                                <div class="ad-legend-inline">
                                    <span><i style="background:#3DBDEC;"></i>Service Revenue</span>
                                </div>
                            </div>
                            <div class="ad-chart-wrap">
                                <canvas id="adRevenueChart" aria-label="Monthly Revenue Trend"></canvas>
                            </div>
                        </article>

                        <article class="ad-panel ad-status-panel">
                            <h3 class="ad-panel-title">Test Status</h3>
                            <div class="ad-donut-wrap">
                                <canvas id="adStatusChart" aria-label="Test Status"></canvas>
                                <div class="ad-donut-center">
                                    <strong><?php echo intval($processingPct); ?>%</strong>
                                    <span>Processing</span>
                                </div>
                            </div>
                            <ul class="ad-legend-list">
                                <?php foreach ($statusRows as $statusRow): ?>
                                    <?php
                                        $statusValue = intval($statusRow['value']);
                                        $statusPct = $totalTests > 0 ? intval(round(($statusValue / $totalTests) * 100)) : 0;
                                    ?>
                                    <li>
                                        <span class="ad-legend-meta"><span class="ad-dot" style="background: <?php echo htmlspecialchars($statusRow['color']); ?>;"></span><?php echo htmlspecialchars($statusRow['label']); ?></span>
                                        <span><?php echo $statusValue; ?> (<?php echo $statusPct; ?>%)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    </div>

                    <div class="ad-bottom-grid">
                        <article class="ad-panel ad-staff-panel">
                            <h3 class="ad-panel-title">Staffing</h3>
                            <div class="ad-staff-list">
                                <?php foreach ($staffRoleRows as $staffRow): ?>
                                    <?php $staffPct = intval(round((intval($staffRow['count']) / $maxStaffCount) * 100)); ?>
                                    <div class="ad-staff-row">
                                        <span class="ad-staff-icon"><?php echo htmlspecialchars($staffRow['icon']); ?></span>
                                        <div class="ad-staff-main">
                                            <span class="ad-staff-name"><?php echo htmlspecialchars($staffRow['label']); ?></span>
                                            <span class="ad-staff-meta"><?php echo intval($staffRow['count']); ?> Active</span>
                                        </div>
                                        <div class="ad-staff-track"><span style="width: <?php echo max(4, min(100, $staffPct)); ?>%;"></span></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="ad-staff-totals">
                                <div><strong><?php echo intval($staffActiveTotal); ?></strong><span>Active</span></div>
                                <!-- <div><strong><?php echo intval($staffPendingTotal); ?></strong><span>Pending</span></div> -->
                                <!-- <div><strong><?php echo intval($staffOffTotal); ?></strong><span>Off</span></div> -->
                            </div>
                        </article>

                        <article class="ad-panel ad-inventory-panel">
                            <h3 class="ad-panel-title">Inventory Status</h3>
                            <p class="ad-alert-title">Critical Stock Levels</p>

                            <?php if (empty($lowStockItems)): ?>
                                <p class="ad-empty">All items are currently stocked.</p>
                            <?php else: ?>
                                <div class="ad-stock-list">
                                    <?php foreach ($lowStockItems as $item): ?>
                                        <?php
                                            $qty = intval($item['quantity'] ?? 0);
                                            $reorder = intval($item['reorder_level'] ?? 0);
                                            $severity = adminInventorySeverity($qty, $reorder);
                                        ?>
                                        <div class="ad-stock-row is-<?php echo $severity; ?>">
                                            <span class="ad-stock-name"><?php echo htmlspecialchars($item['item_name'] ?? 'Inventory Item'); ?></span>
                                            <span class="ad-stock-qty"><?php echo $qty; ?> Units Left</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <a class="ad-outline-btn" href="/lab_sync/index.php?controller=inventoryController&action=index">View Full Inventory</a>
                        </article>

                        <article class="ad-panel ad-actions-panel">
                            <h3 class="ad-panel-title">Quick Actions</h3>
                            <div class="ad-action-grid">
                                <a class="ad-action-btn" href="/lab_sync/index.php?controller=inventoryController&action=add_inventory">Add Inventory</a>
                                <a class="ad-action-btn" href="/lab_sync/index.php?controller=administratorController&action=add_user&role=admin">Add System User</a>
                                <a class="ad-action-btn" href="/lab_sync/index.php?controller=patientController&action=register_patient&role=admin">Add Patient</a>
                                <a class="ad-action-btn" href="/lab_sync/index.php?controller=appointmentsController&action=createAppointment&role=admin">Schedule Appointment</a>
                            </div>
                        </article>
                    </div>

                </section>
            </main>
        </div>

        <script>
            window.ADMIN_DASHBOARD_DATA = <?php echo json_encode($adminChartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        </script>
        <script src="/lab_sync/public/js/adminDashboard.js?v=20260420"></script>
    </body>
</html>
