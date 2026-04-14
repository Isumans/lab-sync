<?php
$billItems = isset($bill['items']) && is_array($bill['items']) ? $bill['items'] : [];
$patientName = $appointment['patient_name'] ?? ('Patient #' . intval($bill['patient_id'] ?? 0));
$appointmentDate = $appointment['appointment_date'] ?? ($bill['bill_date'] ?? '');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo htmlspecialchars((string) ($bill['bill_number'] ?? '')); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: 700; margin: 0; }
        .meta { font-size: 13px; color: #4b5563; }
        .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 13px; }
        th { background: #f3f4f6; font-weight: 700; }
        .right { text-align: right; }
        .totals { width: 320px; margin-left: auto; }
        .totals td { border: none; padding: 5px 0; }
        .grand td { font-weight: 700; font-size: 18px; color: #0f172a; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <h1 class="title">LabSync Invoice</h1>
            <p class="meta">Bill No: <?php echo htmlspecialchars((string) ($bill['bill_number'] ?? '')); ?></p>
            <p class="meta">Bill Date: <?php echo htmlspecialchars((string) ($bill['bill_date'] ?? '')); ?></p>
        </div>
        <button class="no-print" onclick="window.print()">Print Invoice</button>
    </div>

    <div class="box">
        <p><strong>Patient:</strong> <?php echo htmlspecialchars((string) $patientName); ?></p>
        <p><strong>Appointment Date:</strong> <?php echo htmlspecialchars((string) $appointmentDate); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars((string) ($bill['status'] ?? '')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Test Name</th>
                <th class="right">Unit Price (LKR)</th>
                <th class="right">Qty</th>
                <th class="right">Total (LKR)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($billItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($item['test_name'] ?? '')); ?></td>
                    <td class="right"><?php echo number_format(floatval($item['unit_price'] ?? 0), 2); ?></td>
                    <td class="right"><?php echo intval($item['quantity'] ?? 0); ?></td>
                    <td class="right"><?php echo number_format(floatval($item['line_total'] ?? 0), 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right"><?php echo number_format(floatval($bill['subtotal'] ?? 0), 2); ?></td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="right"><?php echo number_format(floatval($bill['discount_amount'] ?? 0), 2); ?></td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="right"><?php echo number_format(floatval($bill['tax_amount'] ?? 0), 2); ?></td>
        </tr>
        <tr class="grand">
            <td>Grand Total</td>
            <td class="right"><?php echo number_format(floatval($bill['total_amount'] ?? 0), 2); ?></td>
        </tr>
        <tr>
            <td>Paid</td>
            <td class="right"><?php echo number_format(floatval($bill['paid_amount'] ?? 0), 2); ?></td>
        </tr>
        <tr>
            <td>Balance</td>
            <td class="right"><?php echo number_format(floatval($bill['balance_due'] ?? 0), 2); ?></td>
        </tr>
    </table>
</body>
</html>
