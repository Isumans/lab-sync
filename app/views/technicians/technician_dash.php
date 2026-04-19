<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (($_SESSION['user_role'] ?? '') !== 'technician') {
    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
    exit();
}
?>
<html>
    <head>
        <title>Technician Dashboard</title>
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
            .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 24px; }
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
            .status-PENDING     { background: rgba(230,126,34,0.12); color: #e67e22; }
            .status-IN_PROGRESS { background: rgba(61,189,236,0.12);  color: #3DBDEC; }
            .status-COMPLETED   { background: rgba(46,213,115,0.12);  color: #2ed573; }
            .stock-bar-wrap {
                width: 100%;
                background: rgba(0,0,0,0.06);
                border-radius: 4px;
                height: 6px;
                margin-top: 4px;
            }
            .stock-bar {
                height: 6px;
                border-radius: 4px;
                background: #ff4757;
                transition: width 0.3s;
            }
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
            @media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }
        </style>
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <section class="reports-dashboard" aria-label="Technician Dashboard">
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
                            <div class="metric-icon pending-icon">📝</div>
                            <div class="metric-content">
                                <h3>Reports Pending Entry</h3>
                                <p class="metric-value"><?php echo number_format($reportsPendingEntry ?? 0); ?></p>
                                <span class="metric-change <?php echo ($reportsPendingEntry > 0) ? 'down' : ''; ?>">
                                    <?php echo ($reportsPendingEntry > 0) ? 'Values needed' : 'All up to date'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon order-icon">🔍</div>
                            <div class="metric-content">
                                <h3>Awaiting Authorization</h3>
                                <p class="metric-value"><?php echo number_format($reportsAwaitingAuth ?? 0); ?></p>
                                <span class="metric-change <?php echo ($reportsAwaitingAuth > 0) ? 'down' : ''; ?>">
                                    <?php echo ($reportsAwaitingAuth > 0) ? 'Pending review' : 'None pending'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon sales-icon">✅</div>
                            <div class="metric-content">
                                <h3>Completed Today</h3>
                                <p class="metric-value"><?php echo number_format($reportsCompletedToday ?? 0); ?></p>
                                <span class="metric-change up">Reports authorized today</span>
                            </div>
                        </div>
                        <div class="metric-card">
                            <div class="metric-icon user-icon">📦</div>
                            <div class="metric-content">
                                <h3>Low Stock Items</h3>
                                <p class="metric-value"><?php echo number_format($lowStockCount ?? 0); ?></p>
                                <span class="metric-change <?php echo ($lowStockCount > 0) ? 'down' : ''; ?>">
                                    <?php echo ($lowStockCount > 0) ? 'Need restocking' : 'Stock levels OK'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="two-col">
                        <!-- Pending Reports List -->
                        <div class="dash-table-section" style="margin-top:0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                                <h3 style="margin:0;">Pending Reports</h3>
                                <a href="/lab_sync/index.php?controller=reportsController&action=index&role=technician"
                                   style="font-size:0.85rem;color:var(--primary-color);text-decoration:none;">View all →</a>
                            </div>
                            <?php if (empty($pendingReportsList)): ?>
                            <p class="empty-state">No pending reports.</p>
                            <?php else: ?>
                            <table class="dash-table">
                                <thead>
                                    <tr>
                                        <th>#ID</th>
                                        <th>Patient</th>
                                        <th>Test</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReportsList as $row): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($row['appointment_id'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_name'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($row['test_name'] ?? '—'); ?></td>
                                        <td>
                                            <?php
                                                $st = htmlspecialchars($row['report_status'] ?? $row['status'] ?? 'PENDING');
                                                $cls = 'status-' . $st;
                                            ?>
                                            <span class="status-badge <?php echo $cls; ?>"><?php echo str_replace('_', ' ', $st); ?></span>
                                        </td>
                                        <td>
                                            <a href="/lab_sync/index.php?controller=reportsController&action=index&role=technician"
                                               style="font-size:0.8rem;color:var(--primary-color);text-decoration:none;">Open</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>

                        <!-- Low Stock Items -->
                        <div class="dash-table-section" style="margin-top:0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                                <h3 style="margin:0;">Low Stock Alert</h3>
                                <a href="/lab_sync/index.php?controller=inventoryController&action=index"
                                   style="font-size:0.85rem;color:var(--primary-color);text-decoration:none;">View all →</a>
                            </div>
                            <?php if (empty($lowStockItems)): ?>
                            <p class="empty-state">All stock levels are adequate.</p>
                            <?php else: ?>
                            <table class="dash-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Reorder At</th>
                                        <th>Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockItems as $item): ?>
                                    <?php
                                        $qty     = intval($item['quantity']);
                                        $reorder = intval($item['reorder_level']);
                                        $pct     = $reorder > 0 ? min(100, round(($qty / $reorder) * 100)) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td style="color:#ff4757;font-weight:600;"><?php echo $qty; ?></td>
                                        <td><?php echo $reorder; ?></td>
                                        <td style="min-width:70px;">
                                            <div class="stock-bar-wrap">
                                                <div class="stock-bar" style="width:<?php echo $pct; ?>%;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="quick-links">
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=reportsController&action=index&role=technician">📊 Reports</a>
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=inventoryController&action=index">📦 Inventory</a>
                        <a class="quick-link-btn" href="/lab_sync/index.php?controller=inventoryController&action=listSuppliers">🏢 Suppliers</a>
                    </div>

                </section>
            </main>
        </div>
    </body>
</html>
