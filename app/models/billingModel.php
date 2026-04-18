<?php

class BillingModel {
    private $db;
    private $lastError = '';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getBillByAppointmentId($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return null;
        }

        if (!$this->tableExists('bills')) {
            return null;
        }

        $sql = "
            SELECT b.*
            FROM bills b
            WHERE b.appointment_id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getBillByAppointmentId: ' . $this->db->error;
            return null;
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getBillByAppointmentId: ' . $stmt->error;
            return null;
        }

        $result = $stmt->get_result();
        $bill = $result ? $result->fetch_assoc() : null;
        if (!$bill) {
            return null;
        }

        $billId = intval($bill['bill_id']);
        $bill['items'] = $this->getBillItems($billId);
        $bill['payments'] = $this->getBillPayments($billId);

        return $bill;
    }

    public function getBillById($billId) {
        $billId = intval($billId);
        if ($billId <= 0 || !$this->tableExists('bills')) {
            return null;
        }

        $sql = "
            SELECT b.*
            FROM bills b
            WHERE b.bill_id = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getBillById: ' . $this->db->error;
            return null;
        }

        $stmt->bind_param('i', $billId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getBillById: ' . $stmt->error;
            return null;
        }

        $result = $stmt->get_result();
        $bill = $result ? $result->fetch_assoc() : null;
        if (!$bill) {
            return null;
        }

        $bill['items'] = $this->getBillItems($billId);
        $bill['payments'] = $this->getBillPayments($billId);

        return $bill;
    }

    public function saveBill($input, $finalize, $userId) {
        if (!$this->tableExists('bills') || !$this->tableExists('bill_items') || !$this->tableExists('payments')) {
            $this->lastError = 'Billing tables are missing. Run the billing migration first.';
            return null;
        }

        $appointmentId = isset($input['appointment_id']) ? intval($input['appointment_id']) : 0;
        $patientId = isset($input['patient_id']) ? intval($input['patient_id']) : 0;
        $discountAmount = $this->toMoney($input['discount_amount'] ?? 0);
        $taxPercent = max(0, floatval($input['tax_percent'] ?? 0));
        $amountTendered = $this->toMoney($input['amount_tendered'] ?? 0);
        $referenceNo = trim((string) ($input['reference_no'] ?? ''));
        $paymentMethod = strtoupper(trim((string) ($input['payment_method'] ?? 'CASH')));

        if ($appointmentId <= 0 || $patientId <= 0) {
            $this->lastError = 'Invalid appointment or patient reference.';
            return null;
        }

        if ($taxPercent > 100) {
            $this->lastError = 'Tax percent cannot be greater than 100.';
            return null;
        }

        if (!$this->isValidReferenceNo($referenceNo)) {
            $this->lastError = 'Reference number is invalid.';
            return null;
        }

        if (!in_array($paymentMethod, ['CASH', 'CARD', 'TRANSFER'], true)) {
            $paymentMethod = 'CASH';
        }

        $existing = $this->getBillByAppointmentId($appointmentId);
        $existingStatus = strtoupper((string) ($existing['status'] ?? ''));

        if ($existing && $existingStatus === 'PAID') {
            $this->lastError = 'This bill is fully paid and cannot be edited.';
            return null;
        }

        $itemsInput = isset($input['items']) && is_array($input['items']) ? $input['items'] : [];

        // PARTIALLY_PAID bills are settlement-only: no item, subtotal, tax, or discount changes.
        if ($existing && $existingStatus === 'PARTIALLY_PAID') {
            if (!$finalize) {
                $this->lastError = 'Draft save is not allowed for partially paid bills.';
                return null;
            }

            $billId = intval($existing['bill_id']);
            $totalAmount = $this->toMoney($existing['total_amount'] ?? 0);
            $alreadyPaid = $this->toMoney($existing['paid_amount'] ?? 0);
            $remaining = max(0, round($totalAmount - $alreadyPaid, 2));

            $paymentToApply = min($amountTendered, $remaining);
            if ($paymentToApply <= 0) {
                $this->lastError = 'Enter a payment amount greater than zero.';
                return null;
            }

            $newPaidAmount = min($totalAmount, round($alreadyPaid + $paymentToApply, 2));
            $newBalance = max(0, round($totalAmount - $newPaidAmount, 2));
            $newStatus = $newBalance > 0 ? 'PARTIALLY_PAID' : 'PAID';

            $userId = intval($userId);
            if ($userId <= 0) {
                $userId = 0;
            }

            $this->db->begin_transaction();
            try {
                $updateSql = '
                    UPDATE bills
                    SET paid_amount = ?,
                        balance_due = ?,
                        status = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE bill_id = ?
                ';
                $updateStmt = $this->db->prepare($updateSql);
                if ($updateStmt === false) {
                    throw new Exception('Prepare failed updating partial payment bill: ' . $this->db->error);
                }

                $updateStmt->bind_param('ddsi', $newPaidAmount, $newBalance, $newStatus, $billId);
                if (!$updateStmt->execute()) {
                    throw new Exception('Execute failed updating partial payment bill: ' . $updateStmt->error);
                }

                $paymentSql = '
                    INSERT INTO payments (
                        bill_id,
                        payment_amount,
                        payment_method,
                        reference_number,
                        payment_date,
                        received_by
                    ) VALUES (?, ?, ?, ?, CURDATE(), ?)
                ';
                $paymentStmt = $this->db->prepare($paymentSql);
                if ($paymentStmt === false) {
                    throw new Exception('Prepare failed inserting partial payment: ' . $this->db->error);
                }

                $paymentStmt->bind_param('idssi', $billId, $paymentToApply, $paymentMethod, $referenceNo, $userId);
                if (!$paymentStmt->execute()) {
                    throw new Exception('Execute failed inserting partial payment: ' . $paymentStmt->error);
                }

                $this->db->commit();
                return $this->getBillById($billId);
            } catch (Throwable $e) {
                $this->db->rollback();
                $this->lastError = $e->getMessage();
                return null;
            }
        }

        $items = $this->normalizeItems($itemsInput);
        if (empty($items)) {
            $this->lastError = 'At least one selected bill item is required.';
            return null;
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += $item['line_total'];
        }
        $discountAmount = min($discountAmount, $subtotal);
        $taxable = max(0, $subtotal - $discountAmount);
        $taxAmount = round(($taxable * $taxPercent) / 100, 2);
        $totalAmount = round($taxable + $taxAmount, 2);

        $paymentToApply = $finalize ? min($amountTendered, $totalAmount) : 0.0;
        $paidAmount = round($paymentToApply, 2);
        $balanceDue = max(0, round($totalAmount - $paidAmount, 2));

        $status = 'DRAFT';
        if ($finalize) {
            if ($paidAmount <= 0) {
                $status = 'PENDING';
            } elseif ($balanceDue > 0) {
                $status = 'PARTIALLY_PAID';
            } else {
                $status = 'PAID';
            }
        }

        $userId = intval($userId);
        if ($userId <= 0) {
            $userId = 0;
        }

        $this->db->begin_transaction();

        try {
            $billId = $existing ? intval($existing['bill_id']) : 0;

            if ($billId > 0) {
                $updateSql = "
                    UPDATE bills
                    SET patient_id = ?,
                        bill_date = CURDATE(),
                        subtotal = ?,
                        discount_amount = ?,
                        tax_amount = ?,
                        total_amount = ?,
                        paid_amount = ?,
                        balance_due = ?,
                        status = ?,
                        notes = NULL,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE bill_id = ?
                ";
                $stmt = $this->db->prepare($updateSql);
                if ($stmt === false) {
                    throw new Exception('Prepare failed in saveBill update: ' . $this->db->error);
                }

                $stmt->bind_param(
                    'idddddssi',
                    $patientId,
                    $subtotal,
                    $discountAmount,
                    $taxAmount,
                    $totalAmount,
                    $paidAmount,
                    $balanceDue,
                    $status,
                    $billId
                );

                if (!$stmt->execute()) {
                    throw new Exception('Execute failed in saveBill update: ' . $stmt->error);
                }
            } else {
                $tempBillNo = 'BILL-TEMP-' . uniqid();
                $insertSql = "
                    INSERT INTO bills (
                        bill_number,
                        appointment_id,
                        patient_id,
                        bill_date,
                        subtotal,
                        discount_amount,
                        tax_amount,
                        total_amount,
                        paid_amount,
                        balance_due,
                        status,
                        created_by
                    ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?)
                ";
                $stmt = $this->db->prepare($insertSql);
                if ($stmt === false) {
                    throw new Exception('Prepare failed in saveBill insert: ' . $this->db->error);
                }

                $stmt->bind_param(
                    'siiddddddsi',
                    $tempBillNo,
                    $appointmentId,
                    $patientId,
                    $subtotal,
                    $discountAmount,
                    $taxAmount,
                    $totalAmount,
                    $paidAmount,
                    $balanceDue,
                    $status,
                    $userId
                );

                if (!$stmt->execute()) {
                    throw new Exception('Execute failed in saveBill insert: ' . $stmt->error);
                }

                $billId = intval($this->db->insert_id);
                $billNo = 'BILL-' . str_pad((string) $billId, 6, '0', STR_PAD_LEFT);
                $updateNoStmt = $this->db->prepare('UPDATE bills SET bill_number = ? WHERE bill_id = ?');
                if ($updateNoStmt === false) {
                    throw new Exception('Prepare failed updating bill number: ' . $this->db->error);
                }

                $updateNoStmt->bind_param('si', $billNo, $billId);
                if (!$updateNoStmt->execute()) {
                    throw new Exception('Execute failed updating bill number: ' . $updateNoStmt->error);
                }
            }

            $deleteItemsStmt = $this->db->prepare('DELETE FROM bill_items WHERE bill_id = ?');
            if ($deleteItemsStmt === false) {
                throw new Exception('Prepare failed deleting bill items: ' . $this->db->error);
            }
            $deleteItemsStmt->bind_param('i', $billId);
            if (!$deleteItemsStmt->execute()) {
                throw new Exception('Execute failed deleting bill items: ' . $deleteItemsStmt->error);
            }

            $itemSql = '
                INSERT INTO bill_items (
                    bill_id,
                    test_id,
                    test_name,
                    quantity,
                    unit_price,
                    discount_amount,
                    line_total,
                    notes
                ) VALUES (?, ?, ?, ?, ?, 0.00, ?, ?)
            ';
            $itemStmt = $this->db->prepare($itemSql);
            if ($itemStmt === false) {
                throw new Exception('Prepare failed inserting bill item: ' . $this->db->error);
            }

            foreach ($items as $item) {
                $testId = $item['test_id'];
                $testName = $item['test_name'];
                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $lineTotal = $item['line_total'];
                $notes = $item['is_custom'] ? 'CUSTOM_ITEM' : null;

                $itemStmt->bind_param('iisidds', $billId, $testId, $testName, $quantity, $unitPrice, $lineTotal, $notes);
                if (!$itemStmt->execute()) {
                    throw new Exception('Execute failed inserting bill item: ' . $itemStmt->error);
                }
            }

            if ($finalize && $paymentToApply > 0) {
                $paymentSql = '
                    INSERT INTO payments (
                        bill_id,
                        payment_amount,
                        payment_method,
                        reference_number,
                        payment_date,
                        received_by
                    ) VALUES (?, ?, ?, ?, CURDATE(), ?)
                ';
                $paymentStmt = $this->db->prepare($paymentSql);
                if ($paymentStmt === false) {
                    throw new Exception('Prepare failed inserting payment: ' . $this->db->error);
                }

                $paymentStmt->bind_param('idssi', $billId, $paymentToApply, $paymentMethod, $referenceNo, $userId);
                if (!$paymentStmt->execute()) {
                    throw new Exception('Execute failed inserting payment: ' . $paymentStmt->error);
                }
            }

            $this->db->commit();
            return $this->getBillById($billId);
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    public function getBillsList($filters, $page = 1, $perPage = 10) {
        $this->lastError = '';

        if (!$this->tableExists('bills')) {
            return [];
        }

        $page = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $offset = ($page - 1) * $perPage;

        list($whereSql, $types, $params) = $this->buildBillsWhereClause($filters);
        $sql = "
            SELECT
                b.bill_id,
                b.appointment_id,
                b.patient_id,
                b.bill_date,
                b.total_amount,
                b.paid_amount,
                b.status,
                COALESCE(p.patient_name, CONCAT('Patient #', b.patient_id)) AS patient_name,
                (
                    SELECT pay.payment_method
                    FROM payments pay
                    WHERE pay.bill_id = b.bill_id
                    ORDER BY pay.payment_date DESC, pay.payment_id DESC
                    LIMIT 1
                ) AS latest_payment_method
            FROM bills b
            LEFT JOIN patients p ON p.patient_id = b.patient_id
            {$whereSql}
            ORDER BY b.bill_date DESC, b.bill_id DESC
            LIMIT ? OFFSET ?
        ";

        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->prepareAndBind($sql, $types, $params, 'getBillsList');
        if ($stmt === null) {
            return [];
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getBillsList: ' . $stmt->error;
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countBills($filters) {
        $this->lastError = '';

        if (!$this->tableExists('bills')) {
            return 0;
        }

        list($whereSql, $types, $params) = $this->buildBillsWhereClause($filters);
        $sql = "
            SELECT COUNT(*) AS total_rows
            FROM bills b
            LEFT JOIN patients p ON p.patient_id = b.patient_id
            {$whereSql}
        ";

        $stmt = $this->prepareAndBind($sql, $types, $params, 'countBills');
        if ($stmt === null) {
            return 0;
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in countBills: ' . $stmt->error;
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row && isset($row['total_rows']) ? intval($row['total_rows']) : 0;
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function buildBillsWhereClause($filters) {
        $whereParts = [];
        $types = '';
        $params = [];

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $whereParts[] = '(p.patient_name LIKE ? OR CAST(b.appointment_id AS CHAR) LIKE ? OR b.bill_number LIKE ?)';
            $likeSearch = '%' . $search . '%';
            $types .= 'sss';
            $params[] = $likeSearch;
            $params[] = $likeSearch;
            $params[] = $likeSearch;
        }

        $status = strtolower(trim((string) ($filters['status'] ?? 'all')));
        if ($status === 'paid_in_full') {
            $whereParts[] = "b.status = 'PAID'";
        } elseif ($status === 'partially_paid') {
            $whereParts[] = "b.status = 'PARTIALLY_PAID'";
        } elseif ($status === 'unpaid') {
            $whereParts[] = "b.status IN ('DRAFT', 'PENDING')";
        } elseif ($status === 'claim_submitted') {
            $whereParts[] = "b.status = 'CANCELLED'";
        }

        $paymentMethod = strtolower(trim((string) ($filters['payment_method'] ?? 'all')));
        if (in_array($paymentMethod, ['cash', 'card', 'transfer'], true)) {
            $whereParts[] = "EXISTS (
                SELECT 1
                FROM payments pay_filter
                WHERE pay_filter.bill_id = b.bill_id
                  AND UPPER(pay_filter.payment_method) = ?
            )";
            $types .= 's';
            $params[] = strtoupper($paymentMethod);
        }

        $fromDate = trim((string) ($filters['from_date'] ?? ''));
        if ($fromDate !== '') {
            $whereParts[] = 'b.bill_date >= ?';
            $types .= 's';
            $params[] = $fromDate;
        }

        $toDate = trim((string) ($filters['to_date'] ?? ''));
        if ($toDate !== '') {
            $whereParts[] = 'b.bill_date <= ?';
            $types .= 's';
            $params[] = $toDate;
        }

        if (empty($whereParts)) {
            return ['WHERE 1=1', $types, $params];
        }

        return ['WHERE ' . implode(' AND ', $whereParts), $types, $params];
    }

    private function prepareAndBind($sql, $types, $params, $context = 'query') {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in ' . $context . ': ' . $this->db->error;
            return null;
        }

        if ($types !== '' && !empty($params)) {
            $bindOk = $stmt->bind_param($types, ...$params);
            if (!$bindOk) {
                $this->lastError = 'Bind failed in ' . $context . ': ' . $stmt->error;
                return null;
            }
        }

        return $stmt;
    }

    private function getBillItems($billId) {
        if (!$this->tableExists('bill_items')) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM bill_items WHERE bill_id = ? ORDER BY bill_item_id ASC');
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param('i', $billId);
        if (!$stmt->execute()) {
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function getBillPayments($billId) {
        if (!$this->tableExists('payments')) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT * FROM payments WHERE bill_id = ? ORDER BY payment_id DESC');
        if ($stmt === false) {
            return [];
        }
        $stmt->bind_param('i', $billId);
        if (!$stmt->execute()) {
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function normalizeItems($itemsInput) {
        $normalized = [];

        foreach ($itemsInput as $item) {
            $selected = !empty($item['selected']);
            if (!$selected) {
                continue;
            }

            $name = trim((string) ($item['test_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $quantity = max(1, intval($item['quantity'] ?? 1));
            if ($quantity > 1000) {
                continue;
            }

            $unitPrice = $this->toMoney($item['unit_price'] ?? 0);
            if ($unitPrice < 0) {
                continue;
            }
            $lineTotal = round($quantity * $unitPrice, 2);

            $normalized[] = [
                'test_id' => max(0, intval($item['test_id'] ?? 0)),
                'test_name' => substr($name, 0, 150),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'is_custom' => !empty($item['is_custom']),
            ];
        }

        return $normalized;
    }

    private function toMoney($value) {
        if (!is_numeric($value)) {
            return 0.0;
        }

        return round(floatval($value), 2);
    }

    private function tableExists($tableName) {
        $tableName = $this->db->real_escape_string($tableName);
        $result = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
        return $result && $result->num_rows > 0;
    }

    public function createBillFromOnlinePayment($appointmentId, $orderId) {
        $appointmentId = intval($appointmentId);
        $orderId       = substr(trim((string) $orderId), 0, 100);

        if ($appointmentId <= 0) {
            $this->lastError = 'Invalid appointment ID.';
            return null;
        }

        if (!$this->tableExists('bills') || !$this->tableExists('bill_items') || !$this->tableExists('payments')) {
            $this->lastError = 'Billing tables are missing.';
            return null;
        }

        $existing = $this->getBillByAppointmentId($appointmentId);
        if ($existing) {
            return $existing;
        }

        $apptStmt = $this->db->prepare('SELECT patient_id FROM appointment WHERE appointment_id = ? LIMIT 1');
        if (!$apptStmt) {
            $this->lastError = 'Failed to look up appointment: ' . $this->db->error;
            return null;
        }
        $apptStmt->bind_param('i', $appointmentId);
        $apptStmt->execute();
        $apptRow = $apptStmt->get_result()->fetch_assoc();
        if (!$apptRow) {
            $this->lastError = 'Appointment not found.';
            return null;
        }
        $patientId = intval($apptRow['patient_id']);

        $items = [];
        if ($this->tableExists('appointment_items')) {
            $itemsStmt = $this->db->prepare('
                SELECT ai.test_id, t.test_name, ai.unit_price, ai.quantity, ai.line_total
                FROM appointment_items ai
                JOIN tests t ON t.test_id = ai.test_id
                WHERE ai.appointment_id = ?
            ');
            if ($itemsStmt) {
                $itemsStmt->bind_param('i', $appointmentId);
                $itemsStmt->execute();
                $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
        }

        if (empty($items)) {
            $fallbackStmt = $this->db->prepare('
                SELECT a.test_id, t.test_name,
                       COALESCE(t.price, 0) AS unit_price,
                       1 AS quantity,
                       COALESCE(t.price, 0) AS line_total
                FROM appointment a
                JOIN tests t ON t.test_id = a.test_id
                WHERE a.appointment_id = ?
                LIMIT 1
            ');
            if ($fallbackStmt) {
                $fallbackStmt->bind_param('i', $appointmentId);
                $fallbackStmt->execute();
                $row = $fallbackStmt->get_result()->fetch_assoc();
                if ($row) {
                    $items[] = $row;
                }
            }
        }

        if (empty($items)) {
            $this->lastError = 'No test items found for this appointment.';
            return null;
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += floatval($item['line_total']);
        }
        $subtotal     = round($subtotal, 2);
        $totalAmount  = $subtotal;
        $paidAmount   = $totalAmount;
        $balanceDue   = 0.0;

        $this->db->begin_transaction();
        try {
            $tempBillNo = 'BILL-TEMP-' . uniqid();
            $insertSql  = "
                INSERT INTO bills (
                    bill_number, appointment_id, patient_id, bill_date,
                    subtotal, discount_amount, tax_amount,
                    total_amount, paid_amount, balance_due,
                    status, created_by
                ) VALUES (?, ?, ?, CURDATE(), ?, 0.00, 0.00, ?, ?, 0.00, 'PAID', NULL)
            ";
            $stmt = $this->db->prepare($insertSql);
            if ($stmt === false) {
                throw new Exception('Prepare failed inserting online bill: ' . $this->db->error);
            }
            $stmt->bind_param('siiddd', $tempBillNo, $appointmentId, $patientId, $subtotal, $totalAmount, $paidAmount);
            if (!$stmt->execute()) {
                throw new Exception('Execute failed inserting online bill: ' . $stmt->error);
            }

            $billId = intval($this->db->insert_id);
            $billNo = 'BILL-' . str_pad((string) $billId, 6, '0', STR_PAD_LEFT);

            $updateNoStmt = $this->db->prepare('UPDATE bills SET bill_number = ? WHERE bill_id = ?');
            if ($updateNoStmt === false) {
                throw new Exception('Prepare failed updating bill number: ' . $this->db->error);
            }
            $updateNoStmt->bind_param('si', $billNo, $billId);
            if (!$updateNoStmt->execute()) {
                throw new Exception('Execute failed updating bill number: ' . $updateNoStmt->error);
            }

            $itemSql  = 'INSERT INTO bill_items (bill_id, test_id, test_name, quantity, unit_price, discount_amount, line_total, notes) VALUES (?, ?, ?, ?, ?, 0.00, ?, NULL)';
            $itemStmt = $this->db->prepare($itemSql);
            if ($itemStmt === false) {
                throw new Exception('Prepare failed inserting bill items: ' . $this->db->error);
            }
            foreach ($items as $item) {
                $testId    = intval($item['test_id']);
                $testName  = substr(trim((string) $item['test_name']), 0, 150);
                $quantity  = max(1, intval($item['quantity']));
                $unitPrice = round(floatval($item['unit_price']), 2);
                $lineTotal = round(floatval($item['line_total']), 2);
                $itemStmt->bind_param('iisidd', $billId, $testId, $testName, $quantity, $unitPrice, $lineTotal);
                if (!$itemStmt->execute()) {
                    throw new Exception('Execute failed inserting bill item: ' . $itemStmt->error);
                }
            }

            $payMethod   = 'CARD';
            $paymentSql  = 'INSERT INTO payments (bill_id, payment_amount, payment_method, reference_number, payment_date, received_by) VALUES (?, ?, ?, ?, CURDATE(), NULL)';
            $paymentStmt = $this->db->prepare($paymentSql);
            if ($paymentStmt === false) {
                throw new Exception('Prepare failed inserting online payment: ' . $this->db->error);
            }
            $paymentStmt->bind_param('idss', $billId, $paidAmount, $payMethod, $orderId);
            if (!$paymentStmt->execute()) {
                throw new Exception('Execute failed inserting online payment: ' . $paymentStmt->error);
            }

            $this->db->commit();
            return $this->getBillById($billId);
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    public function getBillsByPatientId($patientId) {
        $patientId = intval($patientId);
        if ($patientId <= 0 || !$this->tableExists('bills')) {
            return [];
        }

        $sql = "
            SELECT b.bill_id, b.bill_number, b.bill_date, b.subtotal,
                   b.discount_amount, b.tax_amount, b.total_amount,
                   b.paid_amount, b.balance_due, b.status, b.appointment_id,
                   COALESCE(a.appointment_date, b.bill_date) AS appointment_date
            FROM bills b
            LEFT JOIN appointment a ON a.appointment_id = b.appointment_id
            WHERE b.patient_id = ?
            ORDER BY b.bill_date DESC, b.bill_id DESC
        ";
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getBillsByPatientId: ' . $this->db->error;
            return [];
        }
        $stmt->bind_param('i', $patientId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getBillsByPatientId: ' . $stmt->error;
            return [];
        }
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function isValidReferenceNo($value) {
        if ($value === '') {
            return true;
        }

        if (strlen($value) > 64) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9_\-\/ ]+$/', $value) === 1;
    }
}
