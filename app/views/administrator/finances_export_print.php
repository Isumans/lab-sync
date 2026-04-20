<?php
if (!isset($exportRows) || !is_array($exportRows)) {
    $exportRows = [];
}

if (!isset($summaryTotals) || !is_array($summaryTotals)) {
    $summaryTotals = [
        'total_amount' => 0.0,
        'paid_amount' => 0.0,
        'outstanding_amount' => 0.0,
    ];
}

$reportGeneratedAt = isset($reportGeneratedAt) ? (string)$reportGeneratedAt : date('Y-m-d H:i:s');
$filterSummary = isset($filterSummary) ? (string)$filterSummary : 'No filters applied';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finances Report</title>
    <style>
        :root {
            --ink: #1a2a3a;
            --muted: #617387;
            --border: #d9e2ec;
            --panel: #f8fbff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: var(--ink);
            background: #ffffff;
            padding: 24px;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 16px;
        }

        .toolbar button {
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 0.9rem;
            cursor: pointer;
            color: #ffffff;
            background: #0f4c81;
        }

        .toolbar button:hover {
            background: #2d7ab4;
        }

        .report-wrap {
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .report-head {
            background: var(--panel);
            border-bottom: 1px solid var(--border);
            padding: 18px 20px;
        }

        .report-head h1 {
            margin: 0;
            font-size: 1.5rem;
        }

        .meta {
            margin-top: 8px;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
        }

        .summary-item {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            background: #ffffff;
        }

        .summary-item span {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .summary-item strong {
            font-size: 1rem;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            padding: 0 20px 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 920px;
            margin-top: 14px;
        }

        thead th {
            text-align: left;
            font-size: 0.78rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #3d5369;
            background: #eef4fb;
            border-bottom: 1px solid var(--border);
            padding: 10px 12px;
        }

        tbody td {
            border-bottom: 1px solid #ecf1f6;
            padding: 10px 12px;
            font-size: 0.88rem;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: #fbfdff;
        }

        .empty {
            padding: 24px;
            color: var(--muted);
            text-align: center;
        }

        @media print {
            body {
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .report-wrap {
                border: none;
                border-radius: 0;
            }

            .table-wrap {
                overflow: visible;
            }

            table {
                min-width: 0;
            }

            @page {
                size: A4 landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="report-wrap">
        <header class="report-head">
            <h1>Finances Report</h1>
            <div class="meta">Generated at: <?php echo htmlspecialchars($reportGeneratedAt); ?></div>
            <div class="meta">Filters: <?php echo htmlspecialchars($filterSummary); ?></div>
        </header>

        <section class="summary">
            <div class="summary-item">
                <span>Total Billed</span>
                <strong>LKR <?php echo number_format((float)($summaryTotals['total_amount'] ?? 0), 2); ?></strong>
            </div>
            <div class="summary-item">
                <span>Total Paid</span>
                <strong>LKR <?php echo number_format((float)($summaryTotals['paid_amount'] ?? 0), 2); ?></strong>
            </div>
            <div class="summary-item">
                <span>Total Outstanding</span>
                <strong>LKR <?php echo number_format((float)($summaryTotals['outstanding_amount'] ?? 0), 2); ?></strong>
            </div>
        </section>

        <div class="table-wrap">
            <?php if (empty($exportRows)): ?>
                <div class="empty">No invoices found for the selected filters.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bill ID</th>
                            <th>Bill Date</th>
                            <th>Appointment ID</th>
                            <th>Patient Name</th>
                            <th>Total Amount</th>
                            <th>Amount Paid</th>
                            <th>Outstanding</th>
                            <th>Payment Method</th>
                            <th>Financial Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exportRows as $row): ?>
                            <tr>
                                <td><?php echo intval($row['bill_id'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['bill_date'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['appointment_id'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['patient_name'] ?? 'Unknown Patient')); ?></td>
                                <td>LKR <?php echo number_format((float)($row['total_amount'] ?? 0), 2); ?></td>
                                <td>LKR <?php echo number_format((float)($row['paid_amount'] ?? 0), 2); ?></td>
                                <td>LKR <?php echo number_format((float)($row['outstanding_amount'] ?? 0), 2); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['payment_method'] ?? '-')); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['financial_status'] ?? 'Unpaid')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
