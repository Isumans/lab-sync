<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
if (($_SESSION['user_role'] ?? '') !== 'technician') {
    header('Location: /lab_sync/index.php?controller=dashboard&action=index');
    exit();
}

$pendingDelta = intval($pendingEntryCompare['delta'] ?? 0);
$pendingDeltaPrefix = $pendingDelta >= 0 ? '+' : '';

$workflowRows = [
    [
        'label' => 'Data Entry',
        'value' => intval($workflowBreakdown['data_entry'] ?? 0),
        'color' => '#20c18a',
    ],
    [
        'label' => 'Review',
        'value' => intval($workflowBreakdown['review'] ?? 0),
        'color' => '#f4bc2a',
    ],
    [
        'label' => 'Authorized',
        'value' => intval($workflowBreakdown['authorized'] ?? 0),
        'color' => '#39b8e4',
    ],
];

$workflowTotalActive = intval($workflowBreakdown['total_active'] ?? 0);

if (empty($testVolumeCategories)) {
    $testVolumeCategories = [
        ['category_name' => 'Hematology', 'total_volume' => 0],
        ['category_name' => 'Biochemistry', 'total_volume' => 0],
        ['category_name' => 'Immunology', 'total_volume' => 0],
        ['category_name' => 'Microbiology', 'total_volume' => 0],
    ];
}

$volumeMax = 1;
foreach ($testVolumeCategories as $categoryRow) {
    $volumeMax = max($volumeMax, intval($categoryRow['total_volume'] ?? 0));
}

$targetProgress = $completedTarget > 0
    ? intval(round((intval($reportsCompletedToday ?? 0) / $completedTarget) * 100))
    : 0;
$targetProgress = max(0, min(999, $targetProgress));
?>
<html>
    <head>
        <title>Technician Dashboard</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/reportsDashboard.css">
        <link rel="stylesheet" href="/lab_sync/public/technicianDashboard.css?v=20260420">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>
            <main class="main-content">
                <section class="reports-dashboard technician-workbench" aria-label="Technician Dashboard">
                    <?php
                        $pageTitle = 'Technician Dashboard';
                        $pageBreadcrumbText = 'Technician-Dashboard->';
                        $pageActionHtml = '';
                        require __DIR__ . '/../../../public/partials/page-header.php';
                    ?>

                    <div class="td-metrics-grid" aria-label="Top Key Metrics">
                        <article class="td-metric-card">
                            <div class="td-metric-head">
                                <p class="td-metric-label">Pending Entry</p>
                                <span class="td-metric-pill td-pill-danger"><?php echo $pendingDeltaPrefix . number_format($pendingDelta); ?> vs yesterday</span>
                            </div>
                            <p class="td-metric-value"><?php echo number_format($reportsPendingEntry ?? 0); ?></p>
                        </article>

                        <article class="td-metric-card">
                            <div class="td-metric-head">
                                <p class="td-metric-label">Awaiting Auth</p>
                                <span class="td-metric-icon" aria-hidden="true">&#8987;</span>
                            </div>
                            <p class="td-metric-value"><?php echo number_format($reportsAwaitingAuth ?? 0); ?></p>
                        </article>

                        <article class="td-metric-card">
                            <div class="td-metric-head">
                                <p class="td-metric-label">Completed Today</p>
                                <span class="td-metric-pill td-pill-success">Target: <?php echo number_format($completedTarget); ?></span>
                            </div>
                            <p class="td-metric-value"><?php echo number_format($reportsCompletedToday ?? 0); ?></p>
                            <p class="td-metric-sub">Progress <?php echo number_format($targetProgress); ?>%</p>
                        </article>

                        <article class="td-metric-card">
                            <div class="td-metric-head">
                                <p class="td-metric-label">Low-Stock Items</p>
                                <span class="td-metric-icon td-alert" aria-hidden="true">&#9888;</span>
                            </div>
                            <p class="td-metric-value"><?php echo str_pad((string) intval($lowStockCount ?? 0), 2, '0', STR_PAD_LEFT); ?></p>
                        </article>
                    </div>

                    <div class="td-main-grid" aria-label="Middle Charts and Details">
                        <article class="td-panel td-workflow-panel">
                            <h3 class="td-panel-title">Report Workflow Status</h3>
                            <div class="td-workflow-wrap">
                                <canvas id="tdWorkflowChart" aria-label="Report Workflow Status"></canvas>
                                <div class="td-workflow-center">
                                    <strong><?php echo number_format($workflowTotalActive); ?></strong>
                                    <span>Total Active</span>
                                </div>
                            </div>
                            <ul class="td-legend-list">
                                <?php foreach ($workflowRows as $workflowRow): ?>
                                    <li>
                                        <span class="td-legend-meta"><span class="td-dot" style="background: <?php echo htmlspecialchars($workflowRow['color']); ?>;"></span><?php echo htmlspecialchars($workflowRow['label']); ?></span>
                                        <span><?php echo number_format(intval($workflowRow['value'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </article>

                        <article class="td-panel td-volume-panel">
                            <h3 class="td-panel-title">Test Volume by Category</h3>
                            <div class="td-volume-list">
                                <?php foreach ($testVolumeCategories as $volumeRow): ?>
                                    <?php
                                        $volume = intval($volumeRow['total_volume'] ?? 0);
                                        $barWidth = intval(round(($volume / $volumeMax) * 100));
                                        $barWidth = max(2, min(100, $barWidth));
                                    ?>
                                    <div class="td-volume-row">
                                        <div class="td-volume-meta">
                                            <span><?php echo htmlspecialchars($volumeRow['category_name'] ?? 'Uncategorized'); ?></span>
                                            <strong><?php echo number_format($volume); ?></strong>
                                        </div>
                                        <div class="td-volume-track"><span class="td-volume-fill" style="width: <?php echo $barWidth; ?>%;"></span></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>

                        <aside class="td-panel td-inventory-panel">
                            <h3 class="td-panel-title">Critical Inventory Levels</h3>

                            <?php if (empty($criticalInventory)): ?>
                                <p class="td-empty-note">No inventory alerts right now.</p>
                            <?php else: ?>
                                <div class="td-critical-list">
                                    <?php foreach ($criticalInventory as $stockRow): ?>
                                        <?php
                                            $severityClass = 'is-healthy';
                                            if (($stockRow['severity'] ?? '') === 'critical') {
                                                $severityClass = 'is-critical';
                                            } elseif (($stockRow['severity'] ?? '') === 'warning') {
                                                $severityClass = 'is-warning';
                                            }
                                        ?>
                                        <div class="td-stock-row <?php echo $severityClass; ?>">
                                            <div class="td-stock-meta">
                                                <span><?php echo htmlspecialchars($stockRow['item_name'] ?? 'Inventory item'); ?></span>
                                                <strong><?php echo number_format(intval($stockRow['quantity'] ?? 0)); ?>/<?php echo number_format(intval($stockRow['reorder_level'] ?? 0)); ?></strong>
                                            </div>
                                            <div class="td-stock-track">
                                                <span class="td-stock-fill" style="width: <?php echo max(4, min(100, intval($stockRow['ratio_percent'] ?? 0))); ?>%;"></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <h4 class="td-shortcut-title">Navigation Shortcuts</h4>
                            <div class="td-shortcut-list">
                                <a href="/lab_sync/index.php?controller=reportsController&action=index&role=technician">Reports Module <span>&#8250;</span></a>
                                <a href="/lab_sync/index.php?controller=inventoryController&action=index">Inventory Manager <span>&#8250;</span></a>
                            </div>
                        </aside>
                    </div>

                    <article class="td-panel td-actions-panel" aria-label="Quick Actions">
                        <h3 class="td-panel-title">Quick Actions</h3>
                        <div class="td-action-grid">
                            <a class="td-action-btn" href="/lab_sync/index.php?controller=inventoryController&action=index">Add Inventory</a>
                            <a class="td-action-btn" href="/lab_sync/index.php?controller=TestCatalog&action=test_catalog&role=receptionist">Add New Test</a>
                        </div>
                    </article>

                </section>
            </main>
        </div>

        <script>
            window.TECHNICIAN_DASHBOARD_DATA = <?php echo json_encode($technicianChartPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        </script>
        <script src="/lab_sync/public/js/technicianDashboard.js?v=20260420"></script>
    </body>
</html>
