<?php

class AppointmentModel {
    private $db;
    private $lastError = '';

    public function __construct($db) {
        $this->db = $db;
    }

    public function createAppointment($patientId, $appointmentDate, $appointmentTime, $reason = '', $method = 'online') {
        // Insert including method column. Try with reason first.
        $sqlWithReason = "INSERT INTO appointment (patient_id, appointment_date, appointment_time, reason, method) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sqlWithReason);
        if ($stmt !== false) {
            $stmt->bind_param("issss", $patientId, $appointmentDate, $appointmentTime, $reason, $method);
            $result = $stmt->execute();
            if ($result === false) {
                $this->lastError = 'Execute failed in createAppointment (with reason+method): ' . $stmt->error;
                error_log($this->lastError);
            }
            return $result;
        }

        // If prepare failed (maybe 'reason' column missing), try without reason but include method.
        $this->lastError = 'Prepare (with reason+method) failed in createAppointment: ' . $this->db->error;
        error_log($this->lastError);
        $sqlNoReason = "INSERT INTO appointment (patient_id, appointment_date, appointment_time, method) VALUES (?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sqlNoReason);
        if ($stmt2 === false) {
            $this->lastError = 'Prepare failed in createAppointment (no reason, with method): ' . $this->db->error;
            error_log($this->lastError);
            return false;
        }
        $stmt2->bind_param("isss", $patientId, $appointmentDate, $appointmentTime, $method);
        $result2 = $stmt2->execute();
        if ($result2 === false) {
            $this->lastError = 'Execute failed in createAppointment (no reason, with method): ' . $stmt2->error;
            error_log($this->lastError);
        }
        return $result2;
    }

    public function createAppointmentWithTests($patientId, $appointmentDate, $appointmentTime, $reason = '', $method = 'online', $testIds = []) {
        $cleanTestIds = $this->normalizeTestIds($testIds);
        if (empty($cleanTestIds)) {
            $this->lastError = 'No valid tests were provided.';
            return false;
        }

        if (!$this->appointmentTestsTableExists()) {
            $this->lastError = 'Missing required table: appointment_tests. Please run the migration.';
            error_log($this->lastError);
            return false;
        }

        $this->db->begin_transaction();

        try {
            $appointmentId = $this->insertAppointmentHeader($patientId, $appointmentDate, $appointmentTime, $reason, $method);
            if ($appointmentId <= 0) {
                throw new Exception($this->lastError ?: 'Could not resolve appointment_id after insert.');
            }

            $lineSql = "INSERT INTO appointment_tests (appointment_id, test_id, status) VALUES (?, ?, 'PENDING')";
            $lineStmt = $this->db->prepare($lineSql);
            if ($lineStmt === false) {
                throw new Exception('Prepare failed in createAppointmentWithTests (appointment_tests): ' . $this->db->error);
            }

            foreach ($cleanTestIds as $testId) {
                $lineStmt->bind_param('ii', $appointmentId, $testId);
                if (!$lineStmt->execute()) {
                    throw new Exception('Execute failed in createAppointmentWithTests (appointment_tests): ' . $lineStmt->error);
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    private function insertAppointmentHeader($patientId, $appointmentDate, $appointmentTime, $reason, $method) {
        $sqlWithReason = "INSERT INTO appointment (patient_id, appointment_date, appointment_time, reason, method) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sqlWithReason);
        if ($stmt !== false) {
            $stmt->bind_param('issss', $patientId, $appointmentDate, $appointmentTime, $reason, $method);
            if ($stmt->execute()) {
                return intval($this->db->insert_id);
            }
        }

        $sqlNoReason = "INSERT INTO appointment (patient_id, appointment_date, appointment_time, method) VALUES (?, ?, ?, ?)";
        $stmt2 = $this->db->prepare($sqlNoReason);
        if ($stmt2 === false) {
            $this->lastError = 'Prepare failed in insertAppointmentHeader: ' . $this->db->error;
            return 0;
        }

        $stmt2->bind_param('isss', $patientId, $appointmentDate, $appointmentTime, $method);
        if (!$stmt2->execute()) {
            $this->lastError = 'Execute failed in insertAppointmentHeader: ' . $stmt2->error;
            return 0;
        }

        return intval($this->db->insert_id);
    }

    private function appointmentTestsTableExists() {
        $result = $this->db->query("SHOW TABLES LIKE 'appointment_tests'");
        return $result && $result->num_rows > 0;
    }

    private function normalizeTestIds($testIds) {
        if (!is_array($testIds)) {
            $testIds = [$testIds];
        }

        $clean = [];
        foreach ($testIds as $id) {
            if (is_string($id) && ctype_digit(trim($id))) {
                $clean[] = intval($id);
                continue;
            }

            if (is_int($id) && $id > 0) {
                $clean[] = $id;
            }
        }

        return array_values(array_unique($clean));
    }

    public function getAllAppointmentsbyMethod($method) {
        $patientProjection = $this->buildPatientProjectionSql('p');
        $notDeletedClause = $this->buildNotDeletedClause('a');
        $sql = "
            SELECT a.*, {$patientProjection}
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            WHERE a.method = ? AND {$notDeletedClause}
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getAllAppointmentsbyMethod: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }
        $stmt->bind_param("s", $method);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    

    public function getAppointmentDetailsPayload($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return null;
        }

        $patientProjection = $this->buildPatientProjectionSql('p');
        $notDeletedClause = $this->buildNotDeletedClause('a');

        $sql = "
            SELECT a.*, {$patientProjection}
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            WHERE a.appointment_id = ? AND {$notDeletedClause}
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getAppointmentDetailsPayload: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getAppointmentDetailsPayload: ' . $stmt->error;
            error_log($this->lastError);
            return null;
        }

        $result = $stmt->get_result();
        $appointment = $result ? $result->fetch_assoc() : null;
        if (!$appointment) {
            return null;
        }

        $tests = $this->getAppointmentTestsWithStatus($appointmentId);
        $billing = $this->getBillingSummary($appointment, $tests);

        return [
            'appointment' => $appointment,
            'tests' => $tests,
            'billing' => $billing,
        ];
    }

    public function getAppointmentEditPayload($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return null;
        }

        $reasonColumn = $this->resolveFirstExistingColumn('appointment', ['reason', 'clinical_notes', 'notes']);
        $reasonSelect = $reasonColumn !== null ? ", a.{$reasonColumn} AS reason" : ", '' AS reason";
        $patientProjection = $this->buildPatientProjectionSql('p');
        $notDeletedClause = $this->buildNotDeletedClause('a');

        $sql = "
            SELECT a.appointment_id, a.patient_id, a.appointment_date, a.appointment_time{$reasonSelect},
                   {$patientProjection}
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            WHERE a.appointment_id = ? AND {$notDeletedClause}
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getAppointmentEditPayload: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getAppointmentEditPayload: ' . $stmt->error;
            error_log($this->lastError);
            return null;
        }

        $result = $stmt->get_result();
        $appointment = $result ? $result->fetch_assoc() : null;
        if (!$appointment) {
            return null;
        }

        $tests = $this->getAppointmentTestsWithStatus($appointmentId);
        $canEditScheduleTests = $this->areAllTestsPending($tests);
        $nonPendingStatuses = $this->collectNonPendingStatuses($tests);

        return [
            'appointment' => $appointment,
            'tests' => $tests,
            'available_tests' => $this->searchTestsCatalog('', 20),
            'can_edit_schedule_tests' => $canEditScheduleTests,
            'non_pending_statuses' => $nonPendingStatuses,
        ];
    }

    public function searchTestsCatalog($query = '', $limit = 20) {
        $limit = max(1, min(60, intval($limit)));
        $query = trim((string) $query);
        $testCategoryColumn = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categorySelect = $testCategoryColumn !== null
            ? "COALESCE({$testCategoryColumn}, '') AS category"
            : "'' AS category";

        if ($query !== '') {
            $categoryWhere = $testCategoryColumn !== null
                ? " OR {$testCategoryColumn} LIKE CONCAT('%', ?, '%')"
                : '';

            $sql = "
                SELECT test_id, test_name, {$categorySelect}, price
                FROM tests
                WHERE test_name LIKE CONCAT('%', ?, '%')
                   {$categoryWhere}
                ORDER BY test_name ASC
                LIMIT {$limit}
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                return [];
            }

            if ($testCategoryColumn !== null) {
                $stmt->bind_param('ss', $query, $query);
            } else {
                $stmt->bind_param('s', $query);
            }
            if (!$stmt->execute()) {
                return [];
            }

            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        $sql = "
            SELECT test_id, test_name, {$categorySelect}, price
            FROM tests
            ORDER BY test_name ASC
            LIMIT {$limit}
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function updateAppointmentWithTests($appointmentId, $appointmentDate, $appointmentTime, $notes, $testIds) {
        $appointmentId = intval($appointmentId);
        $cleanTestIds = $this->normalizeTestIds($testIds);

        if ($appointmentId <= 0) {
            $this->lastError = 'Invalid appointment_id.';
            return false;
        }

        if ($appointmentDate === '' || $appointmentTime === '') {
            $this->lastError = 'Appointment date and time are required.';
            return false;
        }

        if (empty($cleanTestIds)) {
            $this->lastError = 'Please select at least one test.';
            return false;
        }

        if (!$this->appointmentExists($appointmentId)) {
            $this->lastError = 'Appointment not found.';
            return false;
        }

        $existingHeader = $this->getAppointmentHeaderSnapshot($appointmentId);
        if ($existingHeader === null) {
            $this->lastError = 'Unable to load current appointment details.';
            return false;
        }

        $existingTests = $this->getAppointmentTestsWithStatus($appointmentId);
        $canEditScheduleTests = $this->areAllTestsPending($existingTests);

        if (!$canEditScheduleTests) {
            $dateChanged = trim((string) ($existingHeader['appointment_date'] ?? '')) !== trim((string) $appointmentDate);
            $timeChanged = $this->normalizeTimeValue($existingHeader['appointment_time'] ?? '') !== $this->normalizeTimeValue($appointmentTime);
            $testsChanged = !$this->haveSameTestIds($existingTests, $cleanTestIds);

            if ($dateChanged || $timeChanged || $testsChanged) {
                $this->lastError = 'Schedule details and selected tests can only be modified when all test statuses are PENDING.';
                return false;
            }
        }

        $this->db->begin_transaction();
        try {
            $reasonColumn = $this->resolveFirstExistingColumn('appointment', ['reason', 'clinical_notes', 'notes']);

            if ($reasonColumn !== null) {
                $updateSql = "
                    UPDATE appointment
                    SET appointment_date = ?, appointment_time = ?, {$reasonColumn} = ?
                    WHERE appointment_id = ?
                ";
                $updateStmt = $this->db->prepare($updateSql);
                if ($updateStmt === false) {
                    throw new Exception('Prepare failed in updateAppointmentWithTests: ' . $this->db->error);
                }

                $updateStmt->bind_param('sssi', $appointmentDate, $appointmentTime, $notes, $appointmentId);
            } else {
                $updateSql = "
                    UPDATE appointment
                    SET appointment_date = ?, appointment_time = ?
                    WHERE appointment_id = ?
                ";
                $updateStmt = $this->db->prepare($updateSql);
                if ($updateStmt === false) {
                    throw new Exception('Prepare failed in updateAppointmentWithTests: ' . $this->db->error);
                }

                $updateStmt->bind_param('ssi', $appointmentDate, $appointmentTime, $appointmentId);
            }

            if (!$updateStmt->execute()) {
                throw new Exception('Execute failed in updateAppointmentWithTests (header): ' . $updateStmt->error);
            }

            if ($canEditScheduleTests && $this->tableExists('appointment_tests')) {
                $deleteStmt = $this->db->prepare('DELETE FROM appointment_tests WHERE appointment_id = ?');
                if ($deleteStmt === false) {
                    throw new Exception('Prepare failed while deleting appointment tests: ' . $this->db->error);
                }

                $deleteStmt->bind_param('i', $appointmentId);
                if (!$deleteStmt->execute()) {
                    throw new Exception('Execute failed while deleting appointment tests: ' . $deleteStmt->error);
                }

                $insertStmt = $this->db->prepare("INSERT INTO appointment_tests (appointment_id, test_id, status) VALUES (?, ?, 'PENDING')");
                if ($insertStmt === false) {
                    throw new Exception('Prepare failed while inserting appointment tests: ' . $this->db->error);
                }

                foreach ($cleanTestIds as $testId) {
                    $insertStmt->bind_param('ii', $appointmentId, $testId);
                    if (!$insertStmt->execute()) {
                        throw new Exception('Execute failed while inserting appointment tests: ' . $insertStmt->error);
                    }
                }
            } elseif ($canEditScheduleTests && $this->columnExists('appointment', 'test_id')) {
                $legacyCsv = implode(',', $cleanTestIds);
                $legacyStmt = $this->db->prepare('UPDATE appointment SET test_id = ? WHERE appointment_id = ?');
                if ($legacyStmt === false) {
                    throw new Exception('Prepare failed while updating legacy tests: ' . $this->db->error);
                }

                $legacyStmt->bind_param('si', $legacyCsv, $appointmentId);
                if (!$legacyStmt->execute()) {
                    throw new Exception('Execute failed while updating legacy tests: ' . $legacyStmt->error);
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function startTestInProgress($appointmentId, $testId, $actorUserId = null) {
        $appointmentId = intval($appointmentId);
        $testId = intval($testId);
        $actorUserId = is_numeric($actorUserId) ? intval($actorUserId) : null;

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment or test ID.';
            return false;
        }

        if (!$this->tableExists('appointment_tests')) {
            $this->lastError = 'Missing required table: appointment_tests.';
            return false;
        }

        $statusColumn = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'workflow_status', 'progress_status']);
        if ($statusColumn === null) {
            $this->lastError = 'Unable to resolve status column for appointment tests.';
            return false;
        }

        $selectSql = "SELECT {$statusColumn} AS status FROM appointment_tests WHERE appointment_id = ? AND test_id = ? LIMIT 1";
        $selectStmt = $this->db->prepare($selectSql);
        if ($selectStmt === false) {
            $this->lastError = 'Prepare failed while loading current test status: ' . $this->db->error;
            return false;
        }

        $selectStmt->bind_param('ii', $appointmentId, $testId);
        if (!$selectStmt->execute()) {
            $this->lastError = 'Execute failed while loading current test status: ' . $selectStmt->error;
            return false;
        }

        $result = $selectStmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row) {
            $this->lastError = 'Appointment test entry not found.';
            return false;
        }

        $currentRawStatus = (string) ($row['status'] ?? '');
        $currentStatus = $this->normalizeStatusValue($currentRawStatus);
        if ($currentStatus !== 'PENDING') {
            $this->lastError = 'Only tests with PENDING status can be updated to IN_PROGRESS.';
            return false;
        }

        $setParts = ["{$statusColumn} = ?"];
        $types = 's';
        $values = ['IN_PROGRESS'];

        if ($actorUserId !== null && $actorUserId > 0 && $this->columnExists('appointment_tests', 'assigned_to')) {
            $setParts[] = 'assigned_to = ?';
            $types .= 'i';
            $values[] = $actorUserId;
        }

        if ($this->columnExists('appointment_tests', 'updated_at')) {
            $setParts[] = 'updated_at = NOW()';
        }

        $updateSql = 'UPDATE appointment_tests SET ' . implode(', ', $setParts)
            . " WHERE appointment_id = ? AND test_id = ? AND {$statusColumn} = ?";

        $types .= 'iis';
        $values[] = $appointmentId;
        $values[] = $testId;
        $values[] = $currentRawStatus;

        $updateStmt = $this->db->prepare($updateSql);
        if ($updateStmt === false) {
            $this->lastError = 'Prepare failed while updating test status: ' . $this->db->error;
            return false;
        }

        $params = [$types];
        foreach ($values as $index => $value) {
            $params[] = &$values[$index];
        }
        call_user_func_array([$updateStmt, 'bind_param'], $params);

        if (!$updateStmt->execute()) {
            $this->lastError = 'Execute failed while updating test status: ' . $updateStmt->error;
            return false;
        }

        if ($updateStmt->affected_rows < 1) {
            $this->lastError = 'Status update was not applied. Please refresh and try again.';
            return false;
        }

        return [
            'appointment_id' => $appointmentId,
            'test_id' => $testId,
            'previous_status' => 'PENDING',
            'current_status' => 'IN_PROGRESS',
        ];
    }

    private function appointmentExists($appointmentId) {
        $stmt = $this->db->prepare('SELECT appointment_id FROM appointment WHERE appointment_id = ? LIMIT 1');
        if ($stmt === false) {
            return false;
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            return false;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return !empty($row);
    }

    private function getAppointmentTestsWithStatus($appointmentId) {
        $appointmentId = intval($appointmentId);
        $tests = [];
        $testCategoryColumn = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categorySelect = $testCategoryColumn !== null
            ? "COALESCE(t.{$testCategoryColumn}, '') AS category"
            : "'' AS category";

        if ($this->tableExists('appointment_tests')) {
            $statusColumn = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'workflow_status', 'progress_status']);
            $orderColumn = $this->resolveFirstExistingColumn('appointment_tests', ['appointment_test_id', 'id', 'created_at', 'test_id']);

            $statusSelect = $statusColumn !== null ? "at.{$statusColumn} AS status" : "'PENDING' AS status";
            $orderBy = $orderColumn !== null ? " ORDER BY at.{$orderColumn} ASC" : '';

            $sql = "
                SELECT
                    at.test_id,
                    t.test_name,
                    {$categorySelect},
                    t.price,
                    {$statusSelect}
                FROM appointment_tests at
                LEFT JOIN tests t ON t.test_id = at.test_id
                WHERE at.appointment_id = ?
                {$orderBy}
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt !== false) {
                $stmt->bind_param('i', $appointmentId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $tests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                } else {
                    $this->lastError = 'Execute failed in getAppointmentTestsWithStatus: ' . $stmt->error;
                    error_log($this->lastError);
                }
            } else {
                $this->lastError = 'Prepare failed in getAppointmentTestsWithStatus: ' . $this->db->error;
                error_log($this->lastError);
            }
        }

        if (!empty($tests)) {
            return $tests;
        }

        return $this->getLegacyAppointmentTests($appointmentId);
    }

    private function getLegacyAppointmentTests($appointmentId) {
        if (!$this->columnExists('appointment', 'test_id')) {
            return [];
        }

        $testCategoryColumn = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categorySelect = $testCategoryColumn !== null
            ? "COALESCE(t.{$testCategoryColumn}, '') AS category"
            : "'' AS category";

        $legacyIdSql = 'SELECT test_id FROM appointment WHERE appointment_id = ? LIMIT 1';
        $legacyIdStmt = $this->db->prepare($legacyIdSql);
        if ($legacyIdStmt === false) {
            return [];
        }

        $legacyIdStmt->bind_param('i', $appointmentId);
        if (!$legacyIdStmt->execute()) {
            return [];
        }

        $legacyIdResult = $legacyIdStmt->get_result();
        $legacyRow = $legacyIdResult ? $legacyIdResult->fetch_assoc() : null;
        if (!$legacyRow || !isset($legacyRow['test_id'])) {
            return [];
        }

        $rawTestIds = trim((string) $legacyRow['test_id']);
        if ($rawTestIds === '') {
            return [];
        }

        $testIds = $this->normalizeTestIds(explode(',', $rawTestIds));
        if (empty($testIds)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($testIds), '?'));
        $legacySql = "
            SELECT
                t.test_id,
                t.test_name,
                {$categorySelect},
                t.price,
                'PENDING' AS status
            FROM tests t
            WHERE t.test_id IN ({$placeholders})
            ORDER BY FIELD(t.test_id, {$placeholders})
        ";

        $legacyStmt = $this->db->prepare($legacySql);
        if ($legacyStmt === false) {
            return [];
        }

        $types = str_repeat('i', count($testIds) * 2);
        $bindValues = array_merge($testIds, $testIds);
        $bindParams = [$types];
        foreach ($bindValues as $index => $value) {
            $bindParams[] = &$bindValues[$index];
        }

        call_user_func_array([$legacyStmt, 'bind_param'], $bindParams);
        if (!$legacyStmt->execute()) {
            return [];
        }

        $legacyResult = $legacyStmt->get_result();
        return $legacyResult ? $legacyResult->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function getBillingSummary($appointment, $tests) {
        $totalFee = 0.0;
        foreach ($tests as $test) {
            if (isset($test['price']) && is_numeric($test['price'])) {
                $totalFee += floatval($test['price']);
            }
        }

        $summary = [
            'total_fee' => $totalFee,
            'payment_status' => !empty($appointment['bill_id']) ? 'PAID' : 'PENDING',
            'reference' => !empty($appointment['bill_id']) ? ('BILL-' . $appointment['bill_id']) : 'N/A',
        ];

        if (!$this->tableExists('billing')) {
            return $summary;
        }

        $amountColumn = $this->resolveFirstExistingColumn('billing', ['total_fee', 'total_amount', 'amount']);
        $statusColumn = $this->resolveFirstExistingColumn('billing', ['payment_status', 'status']);
        $referenceColumn = $this->resolveFirstExistingColumn('billing', ['reference_no', 'ref_no', 'billing_id', 'bill_id']);

        $selectParts = [];
        if ($amountColumn !== null) {
            $selectParts[] = "{$amountColumn} AS total_fee";
        }
        if ($statusColumn !== null) {
            $selectParts[] = "{$statusColumn} AS payment_status";
        }
        if ($referenceColumn !== null) {
            $selectParts[] = "{$referenceColumn} AS reference";
        }

        if (empty($selectParts)) {
            return $summary;
        }

        $whereSql = '';
        $bindType = '';
        $bindValue = null;

        if ($this->columnExists('billing', 'appointment_id')) {
            $whereSql = 'appointment_id = ?';
            $bindType = 'i';
            $bindValue = intval($appointment['appointment_id']);
        } elseif (!empty($appointment['bill_id']) && $this->columnExists('billing', 'bill_id')) {
            $whereSql = 'bill_id = ?';
            $bindType = 'i';
            $bindValue = intval($appointment['bill_id']);
        }

        if ($whereSql === '') {
            return $summary;
        }

        $sql = 'SELECT ' . implode(', ', $selectParts) . ' FROM billing WHERE ' . $whereSql . ' LIMIT 1';
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return $summary;
        }

        $stmt->bind_param($bindType, $bindValue);
        if (!$stmt->execute()) {
            return $summary;
        }

        $result = $stmt->get_result();
        $billingRow = $result ? $result->fetch_assoc() : null;
        if (!$billingRow) {
            return $summary;
        }

        if (isset($billingRow['total_fee']) && is_numeric($billingRow['total_fee'])) {
            $summary['total_fee'] = floatval($billingRow['total_fee']);
        }

        if (!empty($billingRow['payment_status'])) {
            $summary['payment_status'] = strtoupper((string) $billingRow['payment_status']);
        }

        if (isset($billingRow['reference']) && $billingRow['reference'] !== null && $billingRow['reference'] !== '') {
            $summary['reference'] = (string) $billingRow['reference'];
        }

        return $summary;
    }

    private function buildPatientProjectionSql($alias) {
        $nameExpr = "''";
        $firstNameCol = $this->columnExists('patients', 'first_name') ? 'first_name' : null;
        $lastNameCol = $this->columnExists('patients', 'last_name') ? 'last_name' : null;

        if ($firstNameCol !== null && $lastNameCol !== null) {
            $nameExpr = "NULLIF(TRIM(CONCAT(COALESCE({$alias}.{$firstNameCol}, ''), ' ', COALESCE({$alias}.{$lastNameCol}, ''))), '')";
        } else {
            $nameCol = $this->resolveFirstExistingColumn('patients', ['patient_name', 'full_name', 'name', 'first_name']);
            if ($nameCol !== null) {
                $nameExpr = "COALESCE({$alias}.{$nameCol}, '')";
            }
        }

        $pidCol = $this->resolveFirstExistingColumn('patients', ['pid', 'patient_code', 'patient_no', 'patient_number', 'patient_id']);
        $genderCol = $this->resolveFirstExistingColumn('patients', ['gender', 'sex']);
        $dobCol = $this->resolveFirstExistingColumn('patients', ['date_of_birth', 'dob', 'birth_date']);
        $contactCol = $this->resolveFirstExistingColumn('patients', ['contact_number', 'phone_number', 'phone', 'mobile']);

        $pidExpr = $pidCol !== null ? "COALESCE({$alias}.{$pidCol}, '')" : "''";
        $genderExpr = $genderCol !== null ? "COALESCE({$alias}.{$genderCol}, '')" : "''";
        $dobExpr = $dobCol !== null ? "{$alias}.{$dobCol}" : 'NULL';
        $contactExpr = $contactCol !== null ? "COALESCE({$alias}.{$contactCol}, '')" : "''";

        return "
            {$nameExpr} AS patient_name,
            {$pidExpr} AS pid,
            {$genderExpr} AS gender,
            {$dobExpr} AS date_of_birth,
            {$contactExpr} AS contact_number,
            {$nameExpr} AS patient_display_name,
            {$pidExpr} AS patient_display_pid
        ";
    }

    private function normalizeStatusValue($value) {
        $raw = strtoupper(trim((string) $value));
        if ($raw === '') {
            return 'PENDING';
        }

        if (in_array($raw, ['NEW', 'PROCESSING', 'PROC', 'IN PROGRESS'], true)) {
            return $raw === 'NEW' ? 'PENDING' : 'IN_PROGRESS';
        }

        if (in_array($raw, ['DONE', 'COMPLETE'], true)) {
            return 'COMPLETED';
        }

        if (in_array($raw, ['APPROVED', 'AUTHORISED'], true)) {
            return 'AUTHORIZED';
        }

        if ($raw === 'PRINT') {
            return 'PRINTED';
        }

        return str_replace(' ', '_', $raw);
    }

    private function areAllTestsPending($tests) {
        foreach ($tests as $test) {
            $normalized = $this->normalizeStatusValue($test['status'] ?? '');
            if ($normalized !== 'PENDING') {
                return false;
            }
        }

        return true;
    }

    private function collectNonPendingStatuses($tests) {
        $statuses = [];
        foreach ($tests as $test) {
            $normalized = $this->normalizeStatusValue($test['status'] ?? '');
            if ($normalized !== 'PENDING') {
                $statuses[] = $normalized;
            }
        }

        return array_values(array_unique($statuses));
    }

    private function getAppointmentHeaderSnapshot($appointmentId) {
        $stmt = $this->db->prepare('SELECT appointment_date, appointment_time FROM appointment WHERE appointment_id = ? LIMIT 1');
        if ($stmt === false) {
            return null;
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            return null;
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    private function normalizeTimeValue($value) {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $raw)) {
            return $raw;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $raw)) {
            return $raw . ':00';
        }

        return strtoupper($raw);
    }

    private function haveSameTestIds($existingTests, $newTestIds) {
        $existingIds = [];
        foreach ($existingTests as $test) {
            if (isset($test['test_id']) && is_numeric($test['test_id'])) {
                $existingIds[] = intval($test['test_id']);
            }
        }

        $existingIds = array_values(array_unique($existingIds));
        sort($existingIds);

        $normalizedNew = $this->normalizeTestIds($newTestIds);
        sort($normalizedNew);

        return $existingIds === $normalizedNew;
    }

    private function tableExists($tableName) {
        $tableName = $this->db->real_escape_string($tableName);
        $result = $this->db->query("SHOW TABLES LIKE '{$tableName}'");
        return $result && $result->num_rows > 0;
    }

    private function columnExists($tableName, $columnName) {
        $tableName = $this->db->real_escape_string($tableName);
        $columnName = $this->db->real_escape_string($columnName);
        $sql = "SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'";
        $result = $this->db->query($sql);
        return $result && $result->num_rows > 0;
    }

    private function resolveFirstExistingColumn($tableName, $candidates) {
        foreach ($candidates as $candidate) {
            if ($this->columnExists($tableName, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function deleteAppointment($appointmentId, $actorUserId = null) {
        $appointmentId = intval($appointmentId);
        $actorUserId = is_numeric($actorUserId) ? intval($actorUserId) : null;

        if ($appointmentId <= 0) {
            $this->lastError = 'Invalid appointment ID.';
            return false;
        }

        if (!$this->tableExists('appointment')) {
            $this->lastError = 'Appointment table not found.';
            return false;
        }

        $hasDeletedBy = $this->columnExists('appointment', 'deleted_by');
        $hasDeletedAt = $this->columnExists('appointment', 'deleted_at');

        if (!$hasDeletedBy && !$hasDeletedAt) {
            $this->lastError = 'Soft delete columns are missing. Add deleted_by or deleted_at to appointment table.';
            return false;
        }

        if ($hasDeletedBy && ($actorUserId === null || $actorUserId <= 0)) {
            $this->lastError = 'Unable to identify authenticated user for deleted_by.';
            return false;
        }

        $this->db->begin_transaction();
        try {
            $selectParts = ['appointment_id'];
            if ($hasDeletedBy) {
                $selectParts[] = 'deleted_by';
            }
            if ($hasDeletedAt) {
                $selectParts[] = 'deleted_at';
            }

            $checkSql = 'SELECT ' . implode(', ', $selectParts) . ' FROM appointment WHERE appointment_id = ? LIMIT 1';
            $checkStmt = $this->db->prepare($checkSql);
            if ($checkStmt === false) {
                throw new Exception('Prepare failed while verifying appointment: ' . $this->db->error);
            }

            $checkStmt->bind_param('i', $appointmentId);
            if (!$checkStmt->execute()) {
                throw new Exception('Execute failed while verifying appointment: ' . $checkStmt->error);
            }

            $checkResult = $checkStmt->get_result();
            $existing = $checkResult ? $checkResult->fetch_assoc() : null;
            if (!$existing) {
                throw new Exception('Appointment not found.');
            }

            $alreadyDeleted = false;
            if ($hasDeletedBy && !empty($existing['deleted_by'])) {
                $alreadyDeleted = true;
            }
            if ($hasDeletedAt && !empty($existing['deleted_at'])) {
                $alreadyDeleted = true;
            }
            if ($alreadyDeleted) {
                throw new Exception('Appointment is already deleted.');
            }

            $setParts = [];
            $types = '';
            $bindValues = [];

            if ($hasDeletedBy) {
                $setParts[] = 'deleted_by = ?';
                $types .= 'i';
                $bindValues[] = $actorUserId;
            }
            if ($hasDeletedAt) {
                $setParts[] = 'deleted_at = NOW()';
            }

            $deleteSql = 'UPDATE appointment SET ' . implode(', ', $setParts) . ' WHERE appointment_id = ?';
            $types .= 'i';
            $bindValues[] = $appointmentId;

            $deleteAppointmentStmt = $this->db->prepare($deleteSql);
            if ($deleteAppointmentStmt === false) {
                throw new Exception('Prepare failed while deleting appointment: ' . $this->db->error);
            }

            $bindParams = [$types];
            foreach ($bindValues as $index => $value) {
                $bindParams[] = &$bindValues[$index];
            }

            call_user_func_array([$deleteAppointmentStmt, 'bind_param'], $bindParams);
            if (!$deleteAppointmentStmt->execute()) {
                throw new Exception('Execute failed while deleting appointment: ' . $deleteAppointmentStmt->error);
            }

            if ($deleteAppointmentStmt->affected_rows < 1) {
                throw new Exception('No appointment record was deleted. Please check the ID and try again.');
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    private function buildNotDeletedClause($alias = 'a') {
        $parts = [];

        if ($this->columnExists('appointment', 'deleted_at')) {
            $parts[] = "{$alias}.deleted_at IS NULL";
        }

        if ($this->columnExists('appointment', 'deleted_by')) {
            $parts[] = "{$alias}.deleted_by IS NULL";
        }

        if (empty($parts)) {
            return '1 = 1';
        }

        return implode(' AND ', $parts);
    }


}
?>