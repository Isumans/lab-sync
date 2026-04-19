<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
    exit();
}

// Appointment status chart data
$statusMap = [
    'Completed' => ['label' => 'Completed', 'color' => '#3DBDEC'],
    'Pending'   => ['label' => 'Scheduled', 'color' => '#2ed573'],
    'Cancelled' => ['label' => 'Cancelled', 'color' => '#ff4757'],
    'No-show'   => ['label' => 'No-show',   'color' => '#ffa502'],
];
$apptData   = $appointmentStatus ?? [];
$totalAppts = array_sum($apptData);
$completedCount = $apptData['Completed'] ?? 0;
$efficiency = $totalAppts > 0 ? round($completedCount / $totalAppts * 100) : 0;

$apptLabels = [];
$apptValues = [];
$apptColors = [];
foreach ($statusMap as $key => $meta) {
    $apptLabels[] = $meta['label'];
    $apptValues[] = $apptData[$key] ?? 0;
    $apptColors[] = $meta['color'];
}

// Staffing progress bars
$staffRoles = [
    'technician'   => ['label' => 'Technicians',   'icon' => '🔬'],
    'admin'        => ['label' => 'Admin',          'icon' => '⚙️'],
    'receptionist' => ['label' => 'Receptionist',  'icon' => '📋'],
];
$staffCountsArr = $staffCounts ?? [];
$maxRoleCount = max(1, ...array_values($staffCountsArr) ?: [1]);
$totalActive   = $staffTotals['active']   ?? 0;
$totalInactive = $staffTotals['inactive'] ?? 0;

// Inventory color coding
function inventoryBarColor(int $qty, int $reorder): string {
    if ($reorder === 0) return '#2ed573';
    $pct = $qty / $reorder;
    if ($pct <= 0.1)  return '#ff4757';
    if ($pct <= 0.3)  return '#ff6b35';
    if ($pct <= 0.6)  return '#ffa502';
    return '#2ed573';
}

// Payment aging
$aging       = $paymentAging ?? ['d0_7' => 0, 'd8_30' => 0, 'd30plus' => 0, 'total_outstanding' => 0];
$agingTotal  = max(1, $aging['d0_7'] + $aging['d8_30'] + $aging['d30plus']);
$aging07Pct  = round($aging['d0_7']    / $agingTotal * 100);
$aging830Pct = round($aging['d8_30']   / $agingTotal * 100);
$aging30Pct  = round($aging['d30plus'] / $agingTotal * 100);

// Avatar color map by role
$avatarColors = ['admin' => '#9b59b6', 'technician' => '#3DBDEC', 'receptionist' => '#2ed573'];
$roleTitles   = ['admin' => 'Administrator', 'technician' => 'Lab Technician', 'receptionist' => 'Receptionist'];

// Page header action HTML
$pageActionHtml = '
<div class="dash-header-actions">
    <span class="date-range-badge">📅 Last 30 Days</span>
    <a href="/lab_sync/index.php?controller=appointmentsController&action=create" class="btn-new-entry">+ New Entry</a>
</div>';
?>
<html>
    <head>
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/dashboardCards.css">
        <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            /* ── Header Actions ── */
            .dash-header-actions {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .date-range-badge {
                padding: 7px 14px;
                border: 1px solid #d1d9e6;
                border-radius: 8px;
                font-size: 0.85rem;
                color: #7c8ba3;
                background: #fff;
                white-space: nowrap;
            }
            .btn-new-entry {
                padding: 8px 18px;
                background: var(--primary-color);
                color: #fff;
                border-radius: 8px;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                white-space: nowrap;
                transition: opacity 0.2s;
            }
            .btn-new-entry:hover { opacity: 0.85; }

            /* ── 5-col metrics grid ── */
            .metrics-grid-5 {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 18px;
                margin-bottom: 24px;
            }
            .metric-card-v2 {
                background: #fff;
                border-radius: 14px;
                padding: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.07);
                display: flex;
                flex-direction: column;
                gap: 8px;
                transition: box-shadow 0.2s, transform 0.2s;
            }
            .metric-card-v2:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.12); transform: translateY(-2px); }
            .metric-card-top {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }
            .metric-icon-v2 {
                width: 42px; height: 42px;
                border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: 20px;
            }
            .metric-icon-v2.blue   { background: rgba(61,189,236,0.12); }
            .metric-icon-v2.green  { background: rgba(46,213,115,0.12); }
            .metric-icon-v2.purple { background: rgba(155,89,182,0.12); }
            .metric-icon-v2.red    { background: rgba(255,71,87,0.12); }
            .metric-icon-v2.gray   { background: rgba(124,139,163,0.12); }

            .metric-badge {
                font-size: 0.7rem;
                font-weight: 700;
                padding: 3px 8px;
                border-radius: 20px;
                letter-spacing: 0.3px;
            }
            .badge-cyan   { background: rgba(61,189,236,0.15); color: #3DBDEC; }
            .badge-today  { background: rgba(61,189,236,0.1);  color: #3DBDEC; border: 1px solid #3DBDEC; }
            .badge-lkr    { background: rgba(61,189,236,0.1);  color: #3DBDEC; }
            .badge-urgent { background: rgba(255,71,87,0.12);  color: #ff4757; }
            .badge-queue  { background: #f1f3f7;               color: #7c8ba3; }

            .metric-value-v2 {
                font-size: 1.8rem;
                font-weight: 700;
                color: var(--font-color);
                line-height: 1.1;
                margin: 2px 0;
            }
            .metric-sub {
                font-size: 0.78rem;
                color: #7c8ba3;
            }
            .metric-progress {
                height: 3px;
                background: #eef0f5;
                border-radius: 2px;
                overflow: hidden;
                margin-top: 4px;
            }
            .metric-progress-fill {
                height: 100%;
                background: var(--primary-color);
                border-radius: 2px;
                width: 60%;
            }
            .metric-change-badge {
                font-size: 0.75rem;
                font-weight: 600;
                color: #2ed573;
            }

            /* ── Row 2: Charts ── */
            .charts-section {
                display: grid;
                grid-template-columns: 3fr 2fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            .card {
                background: #fff;
                border-radius: 14px;
                padding: 22px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            }
            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 16px;
            }
            .card-header h3 {
                font-size: 0.95rem;
                font-weight: 600;
                margin: 0;
                color: var(--font-color);
            }
            .legend-dots {
                display: flex;
                gap: 14px;
                font-size: 0.78rem;
                color: #7c8ba3;
            }
            .legend-dot {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .dot {
                width: 9px; height: 9px;
                border-radius: 50%;
                display: inline-block;
            }
            .chart-wrap { position: relative; height: 220px; }

            /* Donut center label */
            .donut-wrap {
                position: relative;
                width: 180px;
                height: 180px;
                margin: 0 auto 16px;
            }
            .donut-center {
                position: absolute;
                top: 50%; left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                line-height: 1.2;
            }
            .donut-pct {
                font-size: 1.6rem;
                font-weight: 700;
                color: var(--font-color);
            }
            .donut-label {
                font-size: 0.65rem;
                color: #7c8ba3;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .appt-legend {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .appt-legend-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 0.82rem;
            }
            .appt-legend-left {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #7c8ba3;
            }
            .appt-legend-right { font-weight: 600; color: var(--font-color); font-size: 0.82rem; }

            /* ── Row 3: Three columns ── */
            .three-col-section {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-bottom: 20px;
            }

            /* Staffing */
            .staff-role-row {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 0;
                border-bottom: 1px solid #f1f3f7;
            }
            .staff-role-row:last-of-type { border-bottom: none; }
            .staff-role-icon {
                width: 36px; height: 36px;
                border-radius: 8px;
                background: rgba(61,189,236,0.1);
                display: flex; align-items: center; justify-content: center;
                font-size: 16px;
                flex-shrink: 0;
            }
            .staff-role-info { flex: 1; min-width: 0; }
            .staff-role-name {
                font-size: 0.82rem;
                color: var(--font-color);
                font-weight: 500;
                white-space: nowrap;
            }
            .staff-role-sub { font-size: 0.72rem; color: #7c8ba3; }
            .staff-bar-wrap {
                flex: 1;
                height: 5px;
                background: #eef0f5;
                border-radius: 3px;
                overflow: hidden;
            }
            .staff-bar-fill {
                height: 100%;
                background: var(--primary-color);
                border-radius: 3px;
            }
            .staff-totals-row {
                display: flex;
                gap: 0;
                margin-top: 16px;
                border-top: 1px solid #f1f3f7;
                padding-top: 14px;
            }
            .staff-total-item {
                flex: 1;
                text-align: center;
            }
            .staff-total-item + .staff-total-item {
                border-left: 1px solid #f1f3f7;
            }
            .staff-total-num {
                font-size: 1.3rem;
                font-weight: 700;
                color: var(--font-color);
            }
            .staff-total-lbl {
                font-size: 0.68rem;
                color: #7c8ba3;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* Inventory */
            .inventory-alert-header {
                font-size: 0.72rem;
                font-weight: 700;
                color: #ff4757;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 14px;
            }
            .inv-item-row {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 7px 0;
                border-bottom: 1px solid #f8f9fb;
            }
            .inv-item-row:last-of-type { border-bottom: none; }
            .inv-item-name { font-size: 0.82rem; color: var(--font-color); flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .inv-bar-wrap { width: 80px; height: 5px; background: #eef0f5; border-radius: 3px; overflow: hidden; flex-shrink: 0; }
            .inv-bar-fill { height: 100%; border-radius: 3px; }
            .inv-qty-lbl { font-size: 0.75rem; font-weight: 600; flex-shrink: 0; min-width: 70px; text-align: right; }
            .inv-full-btn {
                display: block;
                margin-top: 14px;
                padding: 9px;
                text-align: center;
                border: 1.5px solid var(--primary-color);
                border-radius: 8px;
                color: var(--primary-color);
                font-size: 0.82rem;
                font-weight: 600;
                text-decoration: none;
                transition: background 0.2s;
            }
            .inv-full-btn:hover { background: rgba(61,189,236,0.06); }

            /* Payment Aging */
            .aging-cols {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 6px;
                margin-bottom: 14px;
            }
            .aging-col-head {
                text-align: center;
                font-size: 0.7rem;
                color: #7c8ba3;
                margin-bottom: 6px;
            }
            .aging-bar-track {
                height: 6px;
                background: #eef0f5;
                border-radius: 3px;
                overflow: hidden;
                margin-bottom: 6px;
            }
            .aging-bar-fill { height: 100%; border-radius: 3px; }
            .aging-bar-fill.blue   { background: #3DBDEC; }
            .aging-bar-fill.orange { background: #ffa502; }
            .aging-bar-fill.red    { background: #ff4757; }
            .aging-outstanding {
                margin-top: 12px;
                border-top: 1px solid #f1f3f7;
                padding-top: 12px;
            }
            .aging-total-lbl { font-size: 0.72rem; color: #7c8ba3; margin-bottom: 4px; }
            .aging-total-val { font-size: 1.3rem; font-weight: 700; color: var(--font-color); }
            .aging-total-sub { font-size: 0.72rem; color: #7c8ba3; }

            /* ── Row 4: Active Staff + Quick Actions ── */
            .bottom-section {
                display: grid;
                grid-template-columns: 3fr 2fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            .staff-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .profile-card {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px;
                background: #f8f9fb;
                border-radius: 10px;
                position: relative;
            }
            .avatar-circle {
                width: 40px; height: 40px;
                border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                font-size: 0.9rem;
                font-weight: 700;
                color: #fff;
                flex-shrink: 0;
            }
            .profile-info { flex: 1; min-width: 0; }
            .profile-name { font-size: 0.82rem; font-weight: 600; color: var(--font-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .profile-role { font-size: 0.72rem; color: #7c8ba3; }
            .status-badge {
                font-size: 0.65rem;
                font-weight: 700;
                padding: 2px 7px;
                border-radius: 10px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                white-space: nowrap;
            }
            .status-active   { background: rgba(46,213,115,0.15); color: #2ed573; }
            .status-inactive { background: #eef0f5; color: #7c8ba3; }
            .profile-menu {
                position: absolute; top: 10px; right: 10px;
                font-size: 1rem; color: #b0b8c9; cursor: pointer;
            }

            /* Quick Actions */
            .quick-actions-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .quick-action-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 18px 12px;
                background: #f8f9fb;
                border-radius: 10px;
                text-decoration: none;
                transition: background 0.2s, transform 0.2s;
                cursor: pointer;
            }
            .quick-action-card:hover { background: rgba(61,189,236,0.08); transform: translateY(-2px); }
            .qa-icon {
                width: 40px; height: 40px;
                background: #fff;
                border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: 20px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            }
            .qa-label { font-size: 0.78rem; font-weight: 600; color: var(--font-color); text-align: center; }

            /* ── Responsive ── */
            @media (max-width: 1400px) {
                .metrics-grid-5 { grid-template-columns: repeat(3, 1fr); }
            }
            @media (max-width: 1100px) {
                .charts-section { grid-template-columns: 1fr; }
                .three-col-section { grid-template-columns: 1fr 1fr; }
                .bottom-section { grid-template-columns: 1fr; }
            }
            @media (max-width: 768px) {
                .metrics-grid-5 { grid-template-columns: repeat(2, 1fr); }
                .three-col-section { grid-template-columns: 1fr; }
                .staff-grid { grid-template-columns: 1fr; }
                .quick-actions-grid { grid-template-columns: 1fr 1fr; }
            }
        </style>
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <section class="reports-dashboard" aria-label="Admin Dashboard">
                    <?php
                        $pageTitle         = 'Dashboard';
                        $pageBreadcrumbText = 'Welcome back. Here is the operational summary for today.';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>

                    <!-- ── Row 1: 5 Metric Cards ── -->
                    <div class="metrics-grid-5">

                        <!-- Total Patients -->
                        <div class="metric-card-v2">
                            <div class="metric-card-top">
                                <div class="metric-icon-v2 blue">👥</div>
                                <span class="metric-badge badge-cyan metric-change-badge">+12%↑</span>
                            </div>
                            <p class="metric-value-v2"><?php echo number_format($patientsThisWeek ?? 0); ?></p>
                            <div>
                                <div style="font-size:0.78rem;font-weight:600;color:#7c8ba3;text-transform:uppercase;letter-spacing:0.4px;">Total Patients</div>
                                <div class="metric-sub">This week's volume</div>
                            </div>
                        </div>

                        <!-- Appointments Today -->
                        <div class="metric-card-v2">
                            <div class="metric-card-top">
                                <div class="metric-icon-v2 blue">📅</div>
                                <span class="metric-badge badge-today">Today</span>
                            </div>
                            <p class="metric-value-v2"><?php echo number_format($appointmentsToday ?? 0); ?></p>
                            <div style="font-size:0.78rem;font-weight:600;color:#7c8ba3;text-transform:uppercase;letter-spacing:0.4px;">Appointments</div>
                            <div class="metric-progress"><div class="metric-progress-fill"></div></div>
                        </div>

                        <!-- Revenue -->
                        <div class="metric-card-v2">
                            <div class="metric-card-top">
                                <div class="metric-icon-v2 green">💰</div>
                                <span class="metric-badge badge-lkr">LKR</span>
                            </div>
                            <p class="metric-value-v2"><?php
                                $rev = $revenueThisMonth ?? 0;
                                echo $rev >= 1000000 ? number_format($rev/1000000, 1).'M' : number_format($rev, 0);
                            ?></p>
                            <div>
                                <div style="font-size:0.78rem;font-weight:600;color:#7c8ba3;text-transform:uppercase;letter-spacing:0.4px;">Revenue</div>
                                <div class="metric-sub">Monthly projected</div>
                            </div>
                        </div>

                        <!-- Pending Payments -->
                        <div class="metric-card-v2">
                            <div class="metric-card-top">
                                <div class="metric-icon-v2 red">💳</div>
                                <span class="metric-badge badge-urgent">URGENT</span>
                            </div>
                            <p class="metric-value-v2"><?php echo number_format($unpaidBills ?? 0); ?></p>
                            <div>
                                <div style="font-size:0.78rem;font-weight:600;color:#7c8ba3;text-transform:uppercase;letter-spacing:0.4px;">Pending Payments</div>
                                <div class="metric-sub">Requires attention</div>
                            </div>
                        </div>

                        <!-- Pending Reports -->
                        <div class="metric-card-v2">
                            <div class="metric-card-top">
                                <div class="metric-icon-v2 gray">📄</div>
                                <span class="metric-badge badge-queue">Queue</span>
                            </div>
                            <p class="metric-value-v2"><?php echo number_format($pendingReports ?? 0); ?></p>
                            <div>
                                <div style="font-size:0.78rem;font-weight:600;color:#7c8ba3;text-transform:uppercase;letter-spacing:0.4px;">Pending Reports</div>
                                <div class="metric-sub">6x Processing</div>
                            </div>
                        </div>

                    </div>

                    <!-- ── Row 2: Revenue Trend + Appointment Status ── -->
                    <div class="charts-section">

                        <!-- Monthly Revenue Trend -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Monthly Revenue Trend</h3>
                                <div class="legend-dots">
                                    <span class="legend-dot"><span class="dot" style="background:#3DBDEC;"></span> Service Revenue</span>
                                    <span class="legend-dot"><span class="dot" style="background:#e0e0e0;border:1px dashed #aaa;"></span> Retail Revenue</span>
                                </div>
                            </div>
                            <div class="chart-wrap">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>

                        <!-- Appointment Status -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Appointment Status</h3>
                            </div>
                            <div class="donut-wrap">
                                <canvas id="apptChart"></canvas>
                                <div class="donut-center">
                                    <div class="donut-pct"><?php echo $efficiency; ?>%</div>
                                    <div class="donut-label">Efficiency</div>
                                </div>
                            </div>
                            <div class="appt-legend">
                                <?php foreach ($statusMap as $key => $meta):
                                    $cnt = $apptData[$key] ?? 0;
                                    $pct = $totalAppts > 0 ? round($cnt / $totalAppts * 100) : 0;
                                ?>
                                <div class="appt-legend-row">
                                    <span class="appt-legend-left">
                                        <span class="dot" style="background:<?php echo $meta['color']; ?>;"></span>
                                        <?php echo htmlspecialchars($meta['label']); ?>
                                    </span>
                                    <span class="appt-legend-right"><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                    <!-- ── Row 3: Staffing | Inventory | Payment Aging ── -->
                    <div class="three-col-section">

                        <!-- Staffing -->
                        <div class="card">
                            <div class="card-header"><h3>Staffing</h3></div>
                            <?php foreach ($staffRoles as $roleKey => $roleMeta):
                                $roleCount = $staffCountsArr[$roleKey] ?? 0;
                                $barPct    = round($roleCount / $maxRoleCount * 100);
                            ?>
                            <div class="staff-role-row">
                                <div class="staff-role-icon"><?php echo $roleMeta['icon']; ?></div>
                                <div class="staff-role-info">
                                    <div class="staff-role-name"><?php echo htmlspecialchars($roleMeta['label']); ?></div>
                                    <div class="staff-role-sub"><?php echo $roleCount; ?> Active</div>
                                </div>
                                <div class="staff-bar-wrap">
                                    <div class="staff-bar-fill" style="width:<?php echo $barPct; ?>%;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div class="staff-totals-row">
                                <div class="staff-total-item">
                                    <div class="staff-total-num"><?php echo $totalActive; ?></div>
                                    <div class="staff-total-lbl">Active</div>
                                </div>
                                <div class="staff-total-item">
                                    <div class="staff-total-num">0</div>
                                    <div class="staff-total-lbl">Pending</div>
                                </div>
                                <div class="staff-total-item">
                                    <div class="staff-total-num"><?php echo $totalInactive; ?></div>
                                    <div class="staff-total-lbl">Off</div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Status -->
                        <div class="card">
                            <div class="card-header"><h3>Inventory Status</h3></div>
                            <?php if (!empty($lowStockItems)): ?>
                            <div class="inventory-alert-header">Critical Stock Levels</div>
                            <?php foreach ($lowStockItems as $item):
                                $qty     = intval($item['quantity']);
                                $reorder = intval($item['reorder_level']);
                                $barColor = inventoryBarColor($qty, $reorder);
                                $barWidth = $reorder > 0 ? min(100, round($qty / $reorder * 100)) : 50;
                            ?>
                            <div class="inv-item-row">
                                <span class="inv-item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <div class="inv-bar-wrap">
                                    <div class="inv-bar-fill" style="width:<?php echo $barWidth; ?>%;background:<?php echo $barColor; ?>;"></div>
                                </div>
                                <span class="inv-qty-lbl" style="color:<?php echo $barColor; ?>;"><?php echo $qty; ?> Units Left</span>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <p style="font-size:0.82rem;color:#7c8ba3;margin:0;">All items are well-stocked.</p>
                            <?php endif; ?>
                            <a href="/lab_sync/index.php?controller=inventoryController&action=index" class="inv-full-btn">View Full Inventory</a>
                        </div>

                        <!-- Payment Aging -->
                        <div class="card">
                            <div class="card-header"><h3>Payment Aging</h3></div>
                            <div class="aging-cols">
                                <?php
                                $agingBuckets = [
                                    ['label'=>'0-7 Days',  'value'=>$aging['d0_7'],    'pct'=>$aging07Pct,  'class'=>'blue'],
                                    ['label'=>'8-30 Days', 'value'=>$aging['d8_30'],   'pct'=>$aging830Pct, 'class'=>'orange'],
                                    ['label'=>'30+ Days',  'value'=>$aging['d30plus'], 'pct'=>$aging30Pct,  'class'=>'red'],
                                ];
                                foreach ($agingBuckets as $bucket):
                                ?>
                                <div>
                                    <div class="aging-col-head"><?php echo $bucket['label']; ?></div>
                                    <div class="aging-bar-track">
                                        <div class="aging-bar-fill <?php echo $bucket['class']; ?>" style="width:<?php echo $bucket['pct']; ?>%;"></div>
                                    </div>
                                    <div style="font-size:0.72rem;color:var(--font-color);font-weight:600;text-align:center;">
                                        LKR <?php echo number_format($bucket['value'], 0); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="aging-outstanding">
                                <div class="aging-total-lbl">Total Outstanding Balance</div>
                                <div class="aging-total-val">LKR <?php echo number_format($aging['total_outstanding'], 0); ?></div>
                                <div class="aging-total-sub">Across all overdue accounts</div>
                            </div>
                        </div>

                    </div>

                    <!-- ── Row 4: Active Staff + Quick Actions ── -->
                    <div class="bottom-section">

                        <!-- Active Laboratory Staff -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Active Laboratory Staff</h3>
                            </div>
                            <div class="staff-grid">
                                <?php if (!empty($activeStaff)): ?>
                                <?php foreach ($activeStaff as $member):
                                    $initials   = strtoupper(substr($member['username'], 0, 2));
                                    $avatarBg   = $avatarColors[$member['role']] ?? '#3DBDEC';
                                    $roleTitle  = $roleTitles[$member['role']] ?? ucfirst($member['role']);
                                    $isActive   = ($member['status'] === 'active');
                                ?>
                                <div class="profile-card">
                                    <div class="avatar-circle" style="background:<?php echo $avatarBg; ?>;">
                                        <?php echo htmlspecialchars($initials); ?>
                                    </div>
                                    <div class="profile-info">
                                        <div class="profile-name"><?php echo htmlspecialchars($member['username']); ?></div>
                                        <div class="profile-role"><?php echo htmlspecialchars($roleTitle); ?></div>
                                    </div>
                                    <span class="status-badge <?php echo $isActive ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $isActive ? 'ON DUTY' : 'OFF DUTY'; ?>
                                    </span>
                                    <span class="profile-menu">⋯</span>
                                </div>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <p style="font-size:0.82rem;color:#7c8ba3;grid-column:span 2;">No staff records found.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header"><h3>Quick Actions</h3></div>
                            <div class="quick-actions-grid">
                                <a class="quick-action-card" href="/lab_sync/index.php?controller=administratorController&action=settings">
                                    <div class="qa-icon">👤</div>
                                    <span class="qa-label">Manage Users</span>
                                </a>
                                <a class="quick-action-card" href="/lab_sync/index.php?controller=administratorController&action=settings">
                                    <div class="qa-icon">⚙️</div>
                                    <span class="qa-label">Settings</span>
                                </a>
                                <a class="quick-action-card" href="/lab_sync/index.php?controller=financesController&action=index">
                                    <div class="qa-icon">🏦</div>
                                    <span class="qa-label">Finances</span>
                                </a>
                                <a class="quick-action-card" href="/lab_sync/index.php?controller=partnerLabController&action=index">
                                    <div class="qa-icon">🏥</div>
                                    <span class="qa-label">Partner Labs</span>
                                </a>
                            </div>
                        </div>

                    </div>

                </section>
            </main>
        </div>
    </body>
    <script>
    (function() {
        // Revenue Trend Chart
        var revCtx = document.getElementById('revenueChart');
        if (revCtx) {
            var labels = <?php echo json_encode($monthlyRevenue['labels'] ?? []); ?>;
            var values = <?php echo json_encode($monthlyRevenue['values'] ?? []); ?>;
            if (labels.length === 0) { labels = ['No data']; values = [0]; }
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Service Revenue',
                            data: values,
                            borderColor: '#3DBDEC',
                            backgroundColor: 'rgba(61,189,236,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3DBDEC',
                            pointRadius: 4,
                            borderWidth: 2
                        },
                        {
                            label: 'Retail Revenue',
                            data: values.map(function() { return 0; }),
                            borderColor: '#dde3ed',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.4,
                            borderDash: [4, 4],
                            pointRadius: 0,
                            borderWidth: 1.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#3a3d4a',
                            bodyColor: '#3DBDEC',
                            borderColor: '#e0e6f0',
                            borderWidth: 1,
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.datasetIndex === 1) return null;
                                    return 'LKR ' + ctx.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { font: { size: 11 }, color: '#7c8ba3', callback: function(v) { return v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v; } },
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false }
                        },
                        x: {
                            ticks: { font: { size: 11 }, color: '#7c8ba3' },
                            grid: { display: false, drawBorder: false }
                        }
                    }
                }
            });
        }

        // Appointment Status Donut
        var apptCtx = document.getElementById('apptChart');
        if (apptCtx) {
            var apptLabels = <?php echo json_encode($apptLabels); ?>;
            var apptValues = <?php echo json_encode($apptValues); ?>;
            var apptColors = <?php echo json_encode($apptColors); ?>;
            var total = apptValues.reduce(function(a,b){return a+b;}, 0);
            if (total === 0) { apptValues = [1]; apptLabels = ['No data']; apptColors = ['#eef0f5']; }
            new Chart(apptCtx, {
                type: 'doughnut',
                data: {
                    labels: apptLabels,
                    datasets: [{
                        data: apptValues,
                        backgroundColor: apptColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#3a3d4a',
                            bodyColor: '#3a3d4a',
                            borderColor: '#e0e6f0',
                            borderWidth: 1,
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    var sum = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                                    var pct = sum > 0 ? Math.round(ctx.parsed / sum * 100) : 0;
                                    return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    })();
    </script>
</html>
