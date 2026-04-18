<?php

class ReportModel {
    private $db;
    private $lastError = '';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getReportsList($filters, $page = 1, $perPage = 7) {
        $this->lastError = '';
        $page = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $offset = ($page - 1) * $perPage;

        list($baseSql, $types, $params) = $this->buildBaseReportsSql($filters);
        $sql = "
            SELECT *
            FROM ({$baseSql}) report_rows
            ORDER BY appointment_date DESC, appointment_time DESC, appointment_id DESC
            LIMIT ? OFFSET ?
        ";

        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if ($stmt === null) {
            return [];
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getReportsList: ' . $stmt->error;
            error_log($this->lastError);
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function countReports($filters) {
        $this->lastError = '';
        list($baseSql, $types, $params) = $this->buildBaseReportsSql($filters);
        $sql = "SELECT COUNT(*) AS total_rows FROM ({$baseSql}) report_rows";

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if ($stmt === null) {
            return 0;
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in countReports: ' . $stmt->error;
            error_log($this->lastError);
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row && isset($row['total_rows']) ? intval($row['total_rows']) : 0;
    }

    public function getReportDetailsPayload($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return null;
        }

        $patientProjection = $this->buildPatientProjectionSql('p');
        $notDeletedClause = $this->buildNotDeletedClause('a');

        $headerSql = "
            SELECT
                a.appointment_id,
                a.patient_id,
                a.appointment_date,
                a.appointment_time,
                COALESCE(a.method, '') AS method,
                {$patientProjection}
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            WHERE a.appointment_id = ? AND {$notDeletedClause}
            LIMIT 1
        ";

        $headerStmt = $this->db->prepare($headerSql);
        if ($headerStmt === false) {
            $this->lastError = 'Prepare failed in getReportDetailsPayload: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        $headerStmt->bind_param('i', $appointmentId);
        if (!$headerStmt->execute()) {
            $this->lastError = 'Execute failed in getReportDetailsPayload: ' . $headerStmt->error;
            error_log($this->lastError);
            return null;
        }

        $headerResult = $headerStmt->get_result();
        $appointment = $headerResult ? $headerResult->fetch_assoc() : null;
        if (!$appointment) {
            return null;
        }

        $tests = $this->getReportTests($appointmentId);
        $summary = $this->buildProgressSummary($tests);
        $billing = $this->getBillingSummary($appointmentId);

        return [
            'appointment' => $appointment,
            'tests' => $tests,
            'billing' => $billing,
            'summary' => $summary,
        ];
    }

    public function getEnterValuesContext($appointmentId, $testId) {
        $this->lastError = '';
        $appointmentId = intval($appointmentId);
        $testId = intval($testId);

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment or test ID.';
            return null;
        }

        $payload = $this->getReportDetailsPayload($appointmentId);
        if ($payload === null) {
            $this->lastError = 'Report details not found.';
            return null;
        }

        $selectedTest = null;
        foreach ($payload['tests'] as $testRow) {
            if (intval($testRow['test_id'] ?? 0) === $testId) {
                $selectedTest = $testRow;
                break;
            }
        }

        if ($selectedTest === null) {
            $this->lastError = 'Selected test not found for this appointment.';
            return null;
        }

        $units = $this->getTestUnitsForEntry($testId);
        if (empty($units)) {
            return [
                'appointment' => $payload['appointment'],
                'test' => $selectedTest,
                'units' => [],
                'remarks' => ''
            ];
        }

        $existingValues = $this->getExistingResultValues($appointmentId, $testId);
        foreach ($units as &$unitRow) {
            $unitId = intval($unitRow['unit_id'] ?? 0);
            if ($unitId > 0 && isset($existingValues[$unitId])) {
                $unitRow['result_id'] = intval($existingValues[$unitId]['result_id']);
                $unitRow['measured_value'] = $existingValues[$unitId]['measured_value'];
                $unitRow['flag'] = (string) $existingValues[$unitId]['flag'];
            } else {
                $unitRow['result_id'] = 0;
                $unitRow['measured_value'] = null;
                $unitRow['flag'] = 'N';
            }
        }
        unset($unitRow);

        return [
            'appointment' => $payload['appointment'],
            'test' => $selectedTest,
            'units' => $units,
            'remarks' => $this->getExistingRemarks($appointmentId, $testId)
        ];
    }

    public function saveEnterValues($appointmentId, $testId, $results, $remarks, $markAsReady, $enteredBy = null) {
        $this->lastError = '';
        $appointmentId = intval($appointmentId);
        $testId = intval($testId);

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment or test ID.';
            return false;
        }

        if (!$this->tableExists('test_results')) {
            $this->lastError = 'Table test_results does not exist.';
            return false;
        }

        if (!$this->tableExists('appointment_tests')) {
            $this->lastError = 'Table appointment_tests does not exist.';
            return false;
        }

        $normalizedRows = [];
        foreach ($results as $row) {
            if (!is_array($row)) {
                continue;
            }

            $unitId = intval($row['unit_id'] ?? 0);
            $rawValue = isset($row['measured_value']) ? trim((string) $row['measured_value']) : '';
            if ($unitId <= 0 || $rawValue === '') {
                continue;
            }

            if (!is_numeric($rawValue)) {
                $this->lastError = 'Measured values must be numeric.';
                return false;
            }

            $normalizedRows[] = [
                'unit_id' => $unitId,
                'measured_value' => (float) $rawValue,
            ];
        }

        if (empty($normalizedRows)) {
            $this->lastError = 'No measurable values provided.';
            return false;
        }

        $remarks = trim((string) $remarks);
        $enteredByValue = $enteredBy !== null ? intval($enteredBy) : null;
        $savedCount = 0;
        $resultIds = [];

        try {
            $this->db->begin_transaction();

            foreach ($normalizedRows as $row) {
                $unitId = intval($row['unit_id']);
                $value = (float) $row['measured_value'];
                $flag = $this->resolveResultFlag($unitId, $value);

                $existingResultId = $this->findResultId($appointmentId, $testId, $unitId);
                if ($existingResultId > 0) {
                    $updateSql = '
                        UPDATE test_results
                        SET measured_value = ?, flag = ?, entered_by = ?, entered_at = CURRENT_TIMESTAMP
                        WHERE result_id = ?
                    ';
                    $updateStmt = $this->db->prepare($updateSql);
                    if ($updateStmt === false) {
                        throw new Exception('Prepare failed in saveEnterValues(update): ' . $this->db->error);
                    }

                    $updateStmt->bind_param('dsii', $value, $flag, $enteredByValue, $existingResultId);
                    if (!$updateStmt->execute()) {
                        throw new Exception('Execute failed in saveEnterValues(update): ' . $updateStmt->error);
                    }

                    $resultId = $existingResultId;
                } else {
                    $insertSql = '
                        INSERT INTO test_results (appointment_id, test_id, unit_id, measured_value, flag, entered_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ';
                    $insertStmt = $this->db->prepare($insertSql);
                    if ($insertStmt === false) {
                        throw new Exception('Prepare failed in saveEnterValues(insert): ' . $this->db->error);
                    }

                    $insertStmt->bind_param('iiidsi', $appointmentId, $testId, $unitId, $value, $flag, $enteredByValue);
                    if (!$insertStmt->execute()) {
                        throw new Exception('Execute failed in saveEnterValues(insert): ' . $insertStmt->error);
                    }

                    $resultId = intval($this->db->insert_id);
                }

                if ($resultId > 0) {
                    $resultIds[] = $resultId;
                }
                $savedCount += 1;
            }

            if ($this->tableExists('test_comments') && !empty($resultIds)) {
                foreach ($resultIds as $resultId) {
                    $deleteCommentsStmt = $this->db->prepare('DELETE FROM test_comments WHERE result_id = ?');
                    if ($deleteCommentsStmt === false) {
                        throw new Exception('Prepare failed in saveEnterValues(delete comments): ' . $this->db->error);
                    }

                    $deleteCommentsStmt->bind_param('i', $resultId);
                    if (!$deleteCommentsStmt->execute()) {
                        throw new Exception('Execute failed in saveEnterValues(delete comments): ' . $deleteCommentsStmt->error);
                    }

                    if ($remarks !== '') {
                        $insertCommentStmt = $this->db->prepare('INSERT INTO test_comments (result_id, comment_text, display_order) VALUES (?, ?, 0)');
                        if ($insertCommentStmt === false) {
                            throw new Exception('Prepare failed in saveEnterValues(insert comments): ' . $this->db->error);
                        }

                        $insertCommentStmt->bind_param('is', $resultId, $remarks);
                        if (!$insertCommentStmt->execute()) {
                            throw new Exception('Execute failed in saveEnterValues(insert comments): ' . $insertCommentStmt->error);
                        }
                    }
                }
            }

            $statusValue = $markAsReady ? 'COMPLETED' : 'IN_PROGRESS';
            $updateStatusSql = '
                UPDATE appointment_tests
                SET status = ?, completed_at = CASE WHEN ? = 1 THEN NOW() ELSE completed_at END
                WHERE appointment_id = ? AND test_id = ?
            ';
            $updateStatusStmt = $this->db->prepare($updateStatusSql);
            if ($updateStatusStmt === false) {
                throw new Exception('Prepare failed in saveEnterValues(update status): ' . $this->db->error);
            }

            $readyAsInt = $markAsReady ? 1 : 0;
            $updateStatusStmt->bind_param('siii', $statusValue, $readyAsInt, $appointmentId, $testId);
            if (!$updateStatusStmt->execute()) {
                throw new Exception('Execute failed in saveEnterValues(update status): ' . $updateStatusStmt->error);
            }

            $this->db->commit();

            return [
                'saved_count' => $savedCount,
                'status' => $statusValue,
            ];
        } catch (Exception $ex) {
            $this->db->rollback();
            $this->lastError = $ex->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function getAuthorizeContext($appointmentId, $testId) {
        $this->lastError = '';
        $appointmentId = intval($appointmentId);
        $testId = intval($testId);

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment or test ID.';
            return null;
        }

        $payload = $this->getReportDetailsPayload($appointmentId);
        if ($payload === null) {
            $this->lastError = 'Report details not found.';
            return null;
        }

        $selectedTest = null;
        foreach ($payload['tests'] as $testRow) {
            if (intval($testRow['test_id'] ?? 0) === $testId) {
                $selectedTest = $testRow;
                break;
            }
        }

        if ($selectedTest === null) {
            $this->lastError = 'Selected test not found for this appointment.';
            return null;
        }

        $testStatus = strtoupper(trim((string) ($selectedTest['status'] ?? '')));
        if ($testStatus !== 'COMPLETED' && $testStatus !== 'AUTHORIZED') {
            $this->lastError = 'Only completed reports can be opened for authorization.';
            return null;
        }

        $units = $this->getTestUnitsForEntry($testId);
        $existingValues = $this->getExistingResultValues($appointmentId, $testId);

        foreach ($units as &$unitRow) {
            $unitId = intval($unitRow['unit_id'] ?? 0);
            if ($unitId > 0 && isset($existingValues[$unitId])) {
                $unitRow['measured_value'] = $existingValues[$unitId]['measured_value'];
                $unitRow['flag'] = (string) $existingValues[$unitId]['flag'];
            } else {
                $unitRow['measured_value'] = null;
                $unitRow['flag'] = 'N';
            }
        }
        unset($unitRow);

        return [
            'appointment' => $payload['appointment'],
            'test' => $selectedTest,
            'units' => $units,
            'remarks' => $this->getExistingRemarks($appointmentId, $testId)
        ];
    }

    public function submitAuthorizationDecision($appointmentId, $testId, $decision, $actedBy, $note = '') {
        $this->lastError = '';
        $appointmentId = intval($appointmentId);
        $testId = intval($testId);
        $actedBy = intval($actedBy);
        $decision = strtolower(trim((string) $decision));
        $note = trim((string) $note);

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment or test ID.';
            return false;
        }

        if ($decision !== 'recheck' && $decision !== 'authorize') {
            $this->lastError = 'Invalid decision value.';
            return false;
        }

        if (!$this->tableExists('appointment_tests')) {
            $this->lastError = 'Table appointment_tests does not exist.';
            return false;
        }

        $statusCol = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'test_status']);
        if ($statusCol === null) {
            $this->lastError = 'Unable to resolve appointment test status column.';
            return false;
        }

        $completedAtCol = $this->resolveFirstExistingColumn('appointment_tests', ['completed_at']);
        $authorizedAtCol = $this->resolveFirstExistingColumn('appointment_tests', ['authorized_at']);
        $authorizedByCol = $this->resolveFirstExistingColumn('appointment_tests', ['authorized_by']);

        $currentRowSql = "
            SELECT {$statusCol} AS status
            FROM appointment_tests
            WHERE appointment_id = ? AND test_id = ?
            LIMIT 1
        ";
        $currentRowStmt = $this->db->prepare($currentRowSql);
        if ($currentRowStmt === false) {
            $this->lastError = 'Prepare failed in submitAuthorizationDecision(fetch current): ' . $this->db->error;
            error_log($this->lastError);
            return false;
        }

        $currentRowStmt->bind_param('ii', $appointmentId, $testId);
        if (!$currentRowStmt->execute()) {
            $this->lastError = 'Execute failed in submitAuthorizationDecision(fetch current): ' . $currentRowStmt->error;
            error_log($this->lastError);
            return false;
        }

        $currentRowResult = $currentRowStmt->get_result();
        $currentRow = $currentRowResult ? $currentRowResult->fetch_assoc() : null;
        if (!$currentRow) {
            $this->lastError = 'Appointment test record not found.';
            return false;
        }

        $currentStatus = strtoupper(trim((string) ($currentRow['status'] ?? 'PENDING')));
        if ($decision === 'authorize' && $currentStatus !== 'COMPLETED') {
            $this->lastError = 'Only completed tests can be authorized.';
            return false;
        }

        if ($decision === 'recheck' && $currentStatus !== 'COMPLETED' && $currentStatus !== 'AUTHORIZED') {
            $this->lastError = 'Only completed or authorized tests can be flagged for recheck.';
            return false;
        }

        try {
            $this->db->begin_transaction();

            if ($decision === 'authorize') {
                if ($actedBy <= 0) {
                    throw new Exception('Invalid authorizing user ID.');
                }

                $setParts = ["{$statusCol} = ?"];
                $types = 's';
                $bindValues = ['AUTHORIZED'];

                if ($authorizedByCol !== null) {
                    $setParts[] = "{$authorizedByCol} = ?";
                    $types .= 'i';
                    $bindValues[] = $actedBy;
                }

                if ($authorizedAtCol !== null) {
                    $setParts[] = "{$authorizedAtCol} = NOW()";
                }

                $updateSql = 'UPDATE appointment_tests SET ' . implode(', ', $setParts) . ' WHERE appointment_id = ? AND test_id = ?';
                $types .= 'ii';
                $bindValues[] = $appointmentId;
                $bindValues[] = $testId;

                $updateStmt = $this->db->prepare($updateSql);
                if ($updateStmt === false) {
                    throw new Exception('Prepare failed in submitAuthorizationDecision(authorize): ' . $this->db->error);
                }

                $bindParams = [$types];
                foreach ($bindValues as $idx => $value) {
                    $bindParams[] = &$bindValues[$idx];
                }
                call_user_func_array([$updateStmt, 'bind_param'], $bindParams);

                if (!$updateStmt->execute()) {
                    throw new Exception('Execute failed in submitAuthorizationDecision(authorize): ' . $updateStmt->error);
                }
            }

            if ($decision === 'recheck') {
                $setParts = ["{$statusCol} = ?"];
                $types = 's';
                $bindValues = ['IN_PROGRESS'];

                if ($completedAtCol !== null) {
                    $setParts[] = "{$completedAtCol} = NULL";
                }

                if ($authorizedByCol !== null) {
                    $setParts[] = "{$authorizedByCol} = NULL";
                }

                if ($authorizedAtCol !== null) {
                    $setParts[] = "{$authorizedAtCol} = NULL";
                }

                $updateSql = 'UPDATE appointment_tests SET ' . implode(', ', $setParts) . ' WHERE appointment_id = ? AND test_id = ?';
                $types .= 'ii';
                $bindValues[] = $appointmentId;
                $bindValues[] = $testId;

                $updateStmt = $this->db->prepare($updateSql);
                if ($updateStmt === false) {
                    throw new Exception('Prepare failed in submitAuthorizationDecision(recheck): ' . $this->db->error);
                }

                $bindParams = [$types];
                foreach ($bindValues as $idx => $value) {
                    $bindParams[] = &$bindValues[$idx];
                }
                call_user_func_array([$updateStmt, 'bind_param'], $bindParams);

                if (!$updateStmt->execute()) {
                    throw new Exception('Execute failed in submitAuthorizationDecision(recheck): ' . $updateStmt->error);
                }
            }

            if ($note !== '') {
                $this->appendDecisionNote($appointmentId, $testId, $note);
            }

            $this->db->commit();

            return [
                'decision' => $decision,
                'status' => $decision === 'authorize' ? 'AUTHORIZED' : 'IN_PROGRESS'
            ];
        } catch (Exception $ex) {
            $this->db->rollback();
            $this->lastError = $ex->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function buildBaseReportsSql($filters) {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';
        $status = isset($filters['status']) ? trim(strtolower((string) $filters['status'])) : 'all';
        $testType = isset($filters['test_type']) ? trim(strtolower((string) $filters['test_type'])) : 'all';
        $fromDate = isset($filters['from_date']) ? trim((string) $filters['from_date']) : '';
        $toDate = isset($filters['to_date']) ? trim((string) $filters['to_date']) : '';
        $hasAppointmentTests = $this->tableExists('appointment_tests');
        $hasTestsTable = $this->tableExists('tests');

        $patientNameExpr = $this->buildPatientNameExpr('p');

        $where = ['1 = 1', $this->buildNotDeletedClause('a')];
        $types = '';
        $params = [];

        if ($search !== '') {
            $where[] = "(CAST(a.appointment_id AS CHAR) LIKE ? OR LOWER({$patientNameExpr}) LIKE ?)";
            $like = '%' . strtolower($search) . '%';
            $types .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if ($fromDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
            $where[] = 'a.appointment_date >= ?';
            $types .= 's';
            $params[] = $fromDate;
        }

        if ($toDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
            $where[] = 'a.appointment_date <= ?';
            $types .= 's';
            $params[] = $toDate;
        }

        if (!$hasAppointmentTests) {
            if ($testType !== '' && $testType !== 'all') {
                $where[] = '1 = 0';
            }

            if ($status === 'complete' || $status === 'in progress') {
                $where[] = '1 = 0';
            }

            $sql = "
                SELECT
                    a.appointment_id,
                    a.appointment_date,
                    a.appointment_time,
                    {$patientNameExpr} AS patient_name,
                    '' AS test_types,
                    0 AS total_tests,
                    0 AS completed_tests,
                    0 AS overall_progress,
                    'pending' AS status_label
                FROM appointment a
                LEFT JOIN patients p ON p.patient_id = a.patient_id
                WHERE " . implode(' AND ', $where) . "
            ";

            return [$sql, $types, $params];
        }

        $appointmentTestPkCol = $this->resolveFirstExistingColumn('appointment_tests', ['appointment_test_id', 'id', 'appointment_testid']);
        $appointmentTestStatusCol = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'test_status']);

        $completedExpr = $appointmentTestStatusCol !== null
            ? "SUM(CASE WHEN at.{$appointmentTestStatusCol} IN ('AUTHORIZED', 'PRINTED') THEN 1 ELSE 0 END)"
            : '0';
        $totalExpr = $appointmentTestPkCol !== null
            ? "COUNT(at.{$appointmentTestPkCol})"
            : 'COUNT(at.test_id)';

        if ($testType !== '' && $testType !== 'all') {
            if ($hasTestsTable) {
                $where[] = "EXISTS (
                    SELECT 1
                    FROM appointment_tests atf
                    INNER JOIN tests tf ON tf.test_id = atf.test_id
                    WHERE atf.appointment_id = a.appointment_id
                      AND LOWER(COALESCE(tf.test_name, '')) = ?
                )";
                $types .= 's';
                $params[] = $testType;
            } else {
                $where[] = '1 = 0';
            }
        }

        $having = '';
        if ($status === 'complete') {
            $having = " HAVING {$totalExpr} > 0 AND {$completedExpr} = {$totalExpr}";
        } elseif ($status === 'pending') {
            $having = " HAVING {$totalExpr} = 0 OR {$completedExpr} = 0";
        } elseif ($status === 'in progress') {
            $having = " HAVING {$completedExpr} > 0 AND {$completedExpr} < {$totalExpr}";
        }

        $testTypesExpr = $hasTestsTable
            ? "COALESCE(GROUP_CONCAT(DISTINCT t.test_name ORDER BY t.test_name SEPARATOR ', '), '')"
            : "''";
        $testsJoinSql = $hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = at.test_id' : '';

        $sql = "
            SELECT
                a.appointment_id,
                MAX(a.appointment_date) AS appointment_date,
                MAX(a.appointment_time) AS appointment_time,
                MAX({$patientNameExpr}) AS patient_name,
                {$testTypesExpr} AS test_types,
                {$totalExpr} AS total_tests,
                {$completedExpr} AS completed_tests,
                CASE
                    WHEN {$totalExpr} = 0 THEN 0
                    ELSE ROUND(({$completedExpr} * 100) / {$totalExpr})
                END AS overall_progress,
                CASE
                    WHEN {$totalExpr} > 0 AND {$completedExpr} = {$totalExpr} THEN 'complete'
                    WHEN {$completedExpr} = 0 THEN 'pending'
                    ELSE 'in progress'
                END AS status_label
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            LEFT JOIN appointment_tests at ON at.appointment_id = a.appointment_id
            {$testsJoinSql}
            WHERE " . implode(' AND ', $where) . "
            GROUP BY a.appointment_id
        " . $having;

        return [$sql, $types, $params];
    }

    private function getReportTests($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return [];
        }

        if (!$this->tableExists('appointment_tests')) {
            return $this->getLegacyReportTests($appointmentId);
        }

        $appointmentTestPkCol = $this->resolveFirstExistingColumn('appointment_tests', ['appointment_test_id', 'id', 'appointment_testid']);
        $appointmentTestStatusCol = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'test_status']);

        $completedAtCol = $this->resolveFirstExistingColumn('appointment_tests', ['completed_at']);
        $authorizedAtCol = $this->resolveFirstExistingColumn('appointment_tests', ['authorized_at']);
        $authorizedByCol = $this->resolveFirstExistingColumn('appointment_tests', ['authorized_by']);
        $createdAtCol = $this->resolveFirstExistingColumn('appointment_tests', ['created_at', 'status_updated_at']);

        $appointmentTestPkExpr = $appointmentTestPkCol !== null ? "at.{$appointmentTestPkCol}" : '0';
        $appointmentTestStatusExpr = $appointmentTestStatusCol !== null ? "at.{$appointmentTestStatusCol}" : "'PENDING'";
        $completedAtExpr = $completedAtCol !== null ? "at.{$completedAtCol}" : 'NULL';
        $authorizedAtExpr = $authorizedAtCol !== null ? "at.{$authorizedAtCol}" : 'NULL';
        $authorizedByExpr = $authorizedByCol !== null ? "at.{$authorizedByCol}" : 'NULL';
        $createdAtExpr = $createdAtCol !== null ? "at.{$createdAtCol}" : 'NULL';

        $hasTestsTable = $this->tableExists('tests');
        $testsJoinSql = $hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = at.test_id' : '';
        $testNameExpr = $hasTestsTable ? "COALESCE(t.test_name, '')" : "''";
        $testsCategoryCol = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categoryExpr = ($hasTestsTable && $testsCategoryCol !== null) ? "COALESCE(t.{$testsCategoryCol}, '')" : "''";

        $testsSql = "
            SELECT
                {$appointmentTestPkExpr} AS appointment_test_id,
                at.test_id,
                {$testNameExpr} AS test_name,
                {$categoryExpr} AS category,
                UPPER(COALESCE({$appointmentTestStatusExpr}, 'PENDING')) AS status,
                {$completedAtExpr} AS completed_at,
                {$authorizedAtExpr} AS authorized_at,
                {$authorizedByExpr} AS authorized_by,
                {$createdAtExpr} AS created_at
            FROM appointment_tests at
            {$testsJoinSql}
            WHERE at.appointment_id = ?
            ORDER BY test_name ASC, appointment_test_id ASC
        ";

        $stmt = $this->db->prepare($testsSql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getReportTests: ' . $this->db->error;
            error_log($this->lastError);
            return $this->getLegacyReportTests($appointmentId);
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getReportTests: ' . $stmt->error;
            error_log($this->lastError);
            return $this->getLegacyReportTests($appointmentId);
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

        if (!empty($rows)) {
            return $rows;
        }

        return $this->getLegacyReportTests($appointmentId);
    }

    private function getLegacyReportTests($appointmentId) {
        if (!$this->columnExists('appointment', 'test_id')) {
            return [];
        }

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

        if (!$this->tableExists('tests')) {
            $fallbackRows = [];
            foreach ($testIds as $testId) {
                $fallbackRows[] = [
                    'appointment_test_id' => 0,
                    'test_id' => intval($testId),
                    'test_name' => '',
                    'category' => '',
                    'status' => 'PENDING',
                    'completed_at' => null,
                    'authorized_at' => null,
                    'authorized_by' => null,
                    'created_at' => null,
                ];
            }
            return $fallbackRows;
        }

        $placeholders = implode(', ', array_fill(0, count($testIds), '?'));
        $legacyCategoryCol = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $legacyCategoryExpr = $legacyCategoryCol !== null ? "COALESCE(t.{$legacyCategoryCol}, '')" : "''";

        $legacySql = "
            SELECT
                0 AS appointment_test_id,
                t.test_id,
                COALESCE(t.test_name, '') AS test_name,
            {$legacyCategoryExpr} AS category,
                'PENDING' AS status,
                NULL AS completed_at,
                NULL AS authorized_at,
                NULL AS authorized_by,
                NULL AS created_at
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

    private function buildProgressSummary($tests) {
        $total = count($tests);
        $completed = 0;

        foreach ($tests as $test) {
            $status = strtoupper(trim((string) ($test['status'] ?? '')));
            if ($status === 'AUTHORIZED' || $status === 'PRINTED') {
                $completed += 1;
            }
        }

        $progress = $total > 0 ? intval(round(($completed * 100) / $total)) : 0;

        return [
            'completed_tests' => $completed,
            'total_tests' => $total,
            'overall_progress' => $progress,
            'status_label' => $this->statusFromCounts($completed, $total),
        ];
    }

    public function createReport($testId, $reportContent) {
        $this->lastError = 'createReport is not implemented for the current schema.';
        return false;
    }

    private function statusFromCounts($completed, $total) {
        if ($total > 0 && $completed >= $total) {
            return 'complete';
        }

        if ($completed === 0) {
            return 'pending';
        }

        return 'in progress';
    }

    private function getBillingSummary($appointmentId) {
        $summary = [
            'total_fee' => 0,
            'payment_status' => '',
            'reference' => '',
        ];

        if (!$this->tableExists('billing')) {
            return $summary;
        }

        $appointmentFk = $this->resolveFirstExistingColumn('billing', ['appointment_id', 'bill_id']);
        if ($appointmentFk === null) {
            return $summary;
        }

        $totalCol = $this->resolveFirstExistingColumn('billing', ['total_fee', 'total_amount', 'amount']);
        $statusCol = $this->resolveFirstExistingColumn('billing', ['payment_status', 'status']);
        $refCol = $this->resolveFirstExistingColumn('billing', ['reference_no', 'reference', 'bill_reference']);

        $totalExpr = $totalCol !== null ? $totalCol : '0';
        $statusExpr = $statusCol !== null ? $statusCol : "''";
        $refExpr = $refCol !== null ? $refCol : "''";

        $sql = "
            SELECT
                {$totalExpr} AS total_fee,
                {$statusExpr} AS payment_status,
                {$refExpr} AS reference
            FROM billing
            WHERE {$appointmentFk} = ?
            ORDER BY 1 DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return $summary;
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            return $summary;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row) {
            return $summary;
        }

        if (isset($row['total_fee']) && is_numeric($row['total_fee'])) {
            $summary['total_fee'] = floatval($row['total_fee']);
        }

        if (!empty($row['payment_status'])) {
            $summary['payment_status'] = strtoupper((string) $row['payment_status']);
        }

        if (!empty($row['reference'])) {
            $summary['reference'] = (string) $row['reference'];
        }

        return $summary;
    }

    private function prepareAndBind($sql, $types, $params) {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        if ($types !== '' && !empty($params)) {
            $bindArgs = [$types];
            foreach ($params as $index => $value) {
                $bindArgs[] = &$params[$index];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindArgs);
        }

        return $stmt;
    }

    private function buildPatientNameExpr($alias) {
        $firstNameCol = $this->columnExists('patients', 'first_name') ? 'first_name' : null;
        $lastNameCol = $this->columnExists('patients', 'last_name') ? 'last_name' : null;

        if ($firstNameCol !== null && $lastNameCol !== null) {
            return "COALESCE(NULLIF(TRIM(CONCAT(COALESCE({$alias}.{$firstNameCol}, ''), ' ', COALESCE({$alias}.{$lastNameCol}, ''))), ''), '')";
        }

        $nameCol = $this->resolveFirstExistingColumn('patients', ['patient_name', 'full_name', 'name', 'first_name']);
        if ($nameCol !== null) {
            return "COALESCE({$alias}.{$nameCol}, '')";
        }

        return "''";
    }

    private function buildPatientProjectionSql($alias) {
        $nameExpr = $this->buildPatientNameExpr($alias);
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
            {$contactExpr} AS contact_number
        ";
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

    private function normalizeTestIds($testIds) {
        $normalized = [];

        if (!is_array($testIds)) {
            return $normalized;
        }

        foreach ($testIds as $testId) {
            if ($testId === null || $testId === '') {
                continue;
            }

            $value = intval($testId);
            if ($value > 0) {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    private function getTestUnitsForEntry($testId) {
        if (!$this->tableExists('test_units')) {
            return [];
        }

        $hasRanges = $this->tableExists('test_reference_ranges');
        $isPrimaryCol = $hasRanges ? $this->resolveFirstExistingColumn('test_reference_ranges', ['is_primary']) : null;

        $joinSql = $hasRanges ? 'LEFT JOIN test_reference_ranges trr ON trr.unit_id = tu.unit_id' : '';
        $selectRangeCols = $hasRanges
            ? 'trr.ref_min, trr.ref_max, COALESCE(trr.range_label, \'\') AS range_label, trr.range_index'
            : 'NULL AS ref_min, NULL AS ref_max, \'\' AS range_label, 0 AS range_index';
        $orderPrimaryExpr = $isPrimaryCol !== null ? "trr.{$isPrimaryCol} DESC," : '';

        $sql = "
            SELECT
                tu.unit_id,
                tu.unit_index,
                COALESCE(tu.value_name, '') AS value_name,
                COALESCE(tu.unit_name, '') AS unit_name,
                {$selectRangeCols}
            FROM test_units tu
            {$joinSql}
            WHERE tu.test_id = ?
            ORDER BY tu.unit_index ASC, {$orderPrimaryExpr} trr.range_index ASC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getTestUnitsForEntry: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }

        $stmt->bind_param('i', $testId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getTestUnitsForEntry: ' . $stmt->error;
            error_log($this->lastError);
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        if (empty($rows)) {
            return [];
        }

        $unitsById = [];
        foreach ($rows as $row) {
            $unitId = intval($row['unit_id'] ?? 0);
            if ($unitId <= 0) {
                continue;
            }

            if (!isset($unitsById[$unitId])) {
                $unitsById[$unitId] = [
                    'unit_id' => $unitId,
                    'unit_index' => intval($row['unit_index'] ?? 0),
                    'value_name' => (string) ($row['value_name'] ?? ''),
                    'unit_name' => (string) ($row['unit_name'] ?? ''),
                    'ref_min' => $row['ref_min'] !== null ? (float) $row['ref_min'] : null,
                    'ref_max' => $row['ref_max'] !== null ? (float) $row['ref_max'] : null,
                    'range_label' => (string) ($row['range_label'] ?? ''),
                ];
            }
        }

        usort($unitsById, function ($a, $b) {
            return intval($a['unit_index']) <=> intval($b['unit_index']);
        });

        return array_values($unitsById);
    }

    private function getExistingResultValues($appointmentId, $testId) {
        if (!$this->tableExists('test_results')) {
            return [];
        }

        $sql = '
            SELECT result_id, unit_id, measured_value, COALESCE(flag, \'N\') AS flag
            FROM test_results
            WHERE appointment_id = ? AND test_id = ?
        ';

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('ii', $appointmentId, $testId);
        if (!$stmt->execute()) {
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        if (empty($rows)) {
            return [];
        }

        $indexed = [];
        foreach ($rows as $row) {
            $unitId = intval($row['unit_id'] ?? 0);
            if ($unitId <= 0) {
                continue;
            }

            $indexed[$unitId] = [
                'result_id' => intval($row['result_id'] ?? 0),
                'measured_value' => $row['measured_value'] !== null ? (float) $row['measured_value'] : null,
                'flag' => (string) ($row['flag'] ?? 'N'),
            ];
        }

        return $indexed;
    }

    private function getExistingRemarks($appointmentId, $testId) {
        if (!$this->tableExists('test_results') || !$this->tableExists('test_comments')) {
            return '';
        }

        $sql = '
            SELECT tc.comment_text
            FROM test_comments tc
            INNER JOIN test_results tr ON tr.result_id = tc.result_id
            WHERE tr.appointment_id = ? AND tr.test_id = ?
            ORDER BY tc.display_order ASC, tc.comment_id ASC
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return '';
        }

        $stmt->bind_param('ii', $appointmentId, $testId);
        if (!$stmt->execute()) {
            return '';
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row || !isset($row['comment_text'])) {
            return '';
        }

        return trim((string) $row['comment_text']);
    }

    private function appendDecisionNote($appointmentId, $testId, $note) {
        if (!$this->tableExists('test_results') || !$this->tableExists('test_comments')) {
            return;
        }

        $resultId = $this->findFirstResultId($appointmentId, $testId);
        if ($resultId <= 0) {
            return;
        }

        $insertSql = 'INSERT INTO test_comments (result_id, comment_text, display_order) VALUES (?, ?, 999)';
        $insertStmt = $this->db->prepare($insertSql);
        if ($insertStmt === false) {
            return;
        }

        $insertStmt->bind_param('is', $resultId, $note);
        $insertStmt->execute();
    }

    private function findFirstResultId($appointmentId, $testId) {
        if (!$this->tableExists('test_results')) {
            return 0;
        }

        $sql = '
            SELECT result_id
            FROM test_results
            WHERE appointment_id = ? AND test_id = ?
            ORDER BY result_id ASC
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return 0;
        }

        $stmt->bind_param('ii', $appointmentId, $testId);
        if (!$stmt->execute()) {
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row && isset($row['result_id']) ? intval($row['result_id']) : 0;
    }

    private function findResultId($appointmentId, $testId, $unitId) {
        $sql = '
            SELECT result_id
            FROM test_results
            WHERE appointment_id = ? AND test_id = ? AND unit_id = ?
            LIMIT 1
        ';

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return 0;
        }

        $stmt->bind_param('iii', $appointmentId, $testId, $unitId);
        if (!$stmt->execute()) {
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row && isset($row['result_id']) ? intval($row['result_id']) : 0;
    }

    private function resolveResultFlag($unitId, $measuredValue) {
        if (!$this->tableExists('test_reference_ranges')) {
            return 'N';
        }

        $isPrimaryCol = $this->resolveFirstExistingColumn('test_reference_ranges', ['is_primary']);
        $orderExpr = $isPrimaryCol !== null ? "{$isPrimaryCol} DESC, " : '';

        $sql = "
            SELECT ref_min, ref_max
            FROM test_reference_ranges
            WHERE unit_id = ?
            ORDER BY {$orderExpr} range_index ASC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return 'N';
        }

        $stmt->bind_param('i', $unitId);
        if (!$stmt->execute()) {
            return 'N';
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row) {
            return 'N';
        }

        $min = isset($row['ref_min']) && $row['ref_min'] !== null ? floatval($row['ref_min']) : null;
        $max = isset($row['ref_max']) && $row['ref_max'] !== null ? floatval($row['ref_max']) : null;

        if ($min !== null && $measuredValue < $min) {
            return 'L';
        }

        if ($max !== null && $measuredValue > $max) {
            return 'H';
        }

        return 'N';
    }

    /* ====================================================================
     * PDF REPORT MANAGEMENT METHODS
     * ==================================================================== */

    /**
     * Find the report row for an appointment + test combination.
     */
    public function getReportByAppointmentTest($appointmentId, $testId)
    {
        $sql = "
            SELECT report_id, appointment_id, test_id, reference_number,
                   pdf_relative_path, pdf_original_name, pdf_generated_at,
                   pdf_generated_by, status
            FROM reports
            WHERE appointment_id = ? AND test_id = ?
            ORDER BY report_id DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('ii', $appointmentId, $testId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function getReportById($reportId)
    {
        $sql = "SELECT report_id, appointment_id, test_id FROM reports WHERE report_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Return the absolute file path for a report's PDF.
     */
    public function getReportPdfPath($reportId)
    {
        $sql = "SELECT pdf_relative_path FROM reports WHERE report_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        if (!$row || empty($row['pdf_relative_path'])) return null;

        $basePath = realpath(__DIR__ . '/../../public/reports/pdfs');
        if (!$basePath) return null;
        $full = $basePath . '/' . $row['pdf_relative_path'];
        return file_exists($full) ? $full : null;
    }

    /**
     * Paginated list of authorized/printed reports (for receptionist dashboard).
     */
    public function getAuthorizedReportsList($filters, $page = 1, $perPage = 7)
    {
        $this->lastError = '';
        $page    = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $offset  = ($page - 1) * $perPage;

        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';

        $where = ["r.status IN ('AUTHORIZED','PRINTED')"];
        $types = '';
        $params = [];

        if ($search !== '') {
            $where[] = "(
                LOWER(COALESCE(p.patient_name, '')) LIKE ?
                OR LOWER(COALESCE(p.uhid, '')) LIKE ?
                OR LOWER(r.reference_number) LIKE ?
                OR CAST(r.appointment_id AS CHAR) LIKE ?
            )";
            $like = '%' . strtolower($search) . '%';
            $types .= 'ssss';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        $whereSql = implode(' AND ', $where);

        $sql = "
            SELECT
                r.report_id,
                r.appointment_id,
                r.test_id,
                r.reference_number,
                r.pdf_relative_path,
                r.pdf_generated_at,
                r.status,
                COALESCE(p.patient_name, 'Unknown') AS patient_name,
                COALESCE(p.uhid, '') AS uhid,
                COALESCE(t.test_name, '') AS test_name,
                COALESCE(t.print_name, '') AS print_name,
                a.appointment_date
            FROM reports r
            LEFT JOIN appointment a ON a.appointment_id = r.appointment_id
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            LEFT JOIN tests t ON t.test_id = r.test_id
            WHERE {$whereSql}
            ORDER BY r.pdf_generated_at DESC, r.report_id DESC
            LIMIT ? OFFSET ?
        ";

        $types .= 'ii';
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if (!$stmt) return [];
        if (!$stmt->execute()) {
            $this->lastError = 'getAuthorizedReportsList execute: ' . $stmt->error;
            return [];
        }
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Count of authorized reports matching filters.
     */
    public function countAuthorizedReports($filters)
    {
        $this->lastError = '';
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';

        $where = ["r.status IN ('AUTHORIZED','PRINTED')"];
        $types = '';
        $params = [];

        if ($search !== '') {
            $where[] = "(
                LOWER(COALESCE(p.patient_name, '')) LIKE ?
                OR LOWER(COALESCE(p.uhid, '')) LIKE ?
                OR LOWER(r.reference_number) LIKE ?
                OR CAST(r.appointment_id AS CHAR) LIKE ?
            )";
            $like = '%' . strtolower($search) . '%';
            $types .= 'ssss';
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        $whereSql = implode(' AND ', $where);

        $sql = "
            SELECT COUNT(*) AS total
            FROM reports r
            LEFT JOIN appointment a ON a.appointment_id = r.appointment_id
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            LEFT JOIN tests t ON t.test_id = r.test_id
            WHERE {$whereSql}
        ";

        $stmt = $this->prepareAndBind($sql, $types, $params);
        if (!$stmt) return 0;
        if (!$stmt->execute()) return 0;
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ? intval($row['total']) : 0;
    }

    public function getAuthorizedReportsByPatient($patientId)
    {
        $patientId = intval($patientId);
        if ($patientId <= 0) return [];

        $sql = "
            SELECT
                r.report_id,
                r.appointment_id,
                r.test_id,
                r.reference_number,
                r.pdf_generated_at,
                r.status,
                COALESCE(t.print_name, t.test_name, '') AS test_name,
                a.appointment_date
            FROM reports r
            JOIN appointment a ON a.appointment_id = r.appointment_id
            JOIN tests t ON t.test_id = r.test_id
            WHERE a.patient_id = ?
              AND r.status IN ('AUTHORIZED', 'PRINTED')
            ORDER BY r.pdf_generated_at DESC, r.report_id DESC
        ";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('i', $patientId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
