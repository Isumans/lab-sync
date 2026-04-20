<?php
if (!function_exists('tcDetailsEscape')) {
    function tcDetailsEscape($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

// Resolve discount column value from whichever key exists in $test
$tcDiscount = '0';
if (isset($test['discount_percent'])) {
    $tcDiscount = $test['discount_percent'];
} elseif (isset($test['discount'])) {
    $tcDiscount = $test['discount'];
}

$tcDepartment = $test['department'] ?? $test['category'] ?? 'N/A';
$tcIsActive   = (int)($test['is_active'] ?? 1);
?>
<div class="test-details-shell">

    <div class="test-details-header">
        <div>
            <h2 class="test-details-title"><?= tcDetailsEscape($test['test_name'] ?? 'Unknown Test') ?></h2>
            <p class="test-details-sub">Test Code: <?= tcDetailsEscape($test['test_code'] ?? 'N/A') ?></p>
        </div>
        <span class="test-active-badge <?= $tcIsActive ? 'is-active' : 'is-inactive' ?>">
            <?= $tcIsActive ? 'ACTIVE' : 'INACTIVE' ?>
        </span>
    </div>

    <div class="test-details-grid">

        <section class="test-detail-card">
            <h3 class="test-detail-card-title">Basic Information</h3>
            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">Test ID</span>
                    <strong><?= tcDetailsEscape($test['test_id'] ?? 'N/A') ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Department</span>
                    <strong><?= tcDetailsEscape($tcDepartment) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Default Unit</span>
                    <strong><?= tcDetailsEscape($test['default_unit'] ?? 'N/A') ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Print Name</span>
                    <strong><?= tcDetailsEscape($test['print_name'] ?? 'N/A') ?></strong>
                </div>
            </div>
        </section>

        <section class="test-detail-card">
            <h3 class="test-detail-card-title">Pricing</h3>
            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">Price</span>
                    <strong class="price-highlight">LKR <?= number_format((float)($test['price'] ?? 0), 2) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Cost Price</span>
                    <strong>LKR <?= number_format((float)($test['cost_price'] ?? 0), 2) ?></strong>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Discount</span>
                    <strong><?= tcDetailsEscape($tcDiscount) ?>%</strong>
                </div>
            </div>
        </section>

        <?php if (!empty($test['description'])): ?>
        <section class="test-detail-card test-detail-card--full">
            <h3 class="test-detail-card-title">Description</h3>
            <p class="detail-text"><?= tcDetailsEscape($test['description']) ?></p>
        </section>
        <?php endif; ?>

        <?php if (!empty($test['report_comments'])): ?>
        <section class="test-detail-card test-detail-card--full">
            <h3 class="test-detail-card-title">Report Comments</h3>
            <p class="detail-text"><?= tcDetailsEscape($test['report_comments']) ?></p>
        </section>
        <?php endif; ?>

    </div>

    <?php if (!empty($units)): ?>
    <div class="test-units-section">
        <h3 class="test-units-heading">Units &amp; Reference Ranges</h3>

        <?php foreach ($units as $unit): ?>
        <div class="test-unit-block">
            <div class="unit-header">
                <span class="unit-value-badge"><?= tcDetailsEscape($unit['value_name'] ?? '') ?></span>
                <span class="unit-name-label"><?= tcDetailsEscape($unit['unit_name'] ?? '') ?></span>
                <?php if (!empty($unit['is_default'])): ?>
                <span class="unit-default-tag">Default</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($unit['ranges'])): ?>
            <div class="ranges-table-wrap">
                <table class="ranges-table">
                    <thead>
                        <tr>
                            <th>Gender</th>
                            <th>Age Min</th>
                            <th>Age Max</th>
                            <th>Ref Min</th>
                            <th>Ref Max</th>
                            <th>Label</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unit['ranges'] as $range): ?>
                        <?php
                            $refMin = $range['range_min'] ?? $range['ref_min'] ?? $range['min'] ?? '-';
                            $refMax = $range['range_max'] ?? $range['ref_max'] ?? $range['max'] ?? '-';
                            $label  = $range['range_label'] ?? $range['label'] ?? '-';
                        ?>
                        <tr>
                            <td><?= tcDetailsEscape($range['gender'] ?? 'ALL') ?></td>
                            <td><?= tcDetailsEscape($range['age_min'] ?? '-') ?></td>
                            <td><?= tcDetailsEscape($range['age_max'] ?? '-') ?></td>
                            <td><?= tcDetailsEscape($refMin) ?></td>
                            <td><?= tcDetailsEscape($refMax) ?></td>
                            <td><?= tcDetailsEscape($label) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="no-ranges-note">No reference ranges defined for this unit.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="test-units-section">
        <p class="no-units-note">No units defined for this test.</p>
    </div>
    <?php endif; ?>

</div>
