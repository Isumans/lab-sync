<?php

class AppointmentModel {
    private $db;
    private static $columnCache = [];
    private static $tableCache = [];
    private static $tableColumnCache = [];
    private $lastError = '';

    public function __construct($db) {
        $this->db = $db;
    }

    private function hasAppointmentColumn($columnName) {
        if (array_key_exists($columnName, self::$columnCache)) {
            return self::$columnCache[$columnName];
        }

        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
        $sql = "SHOW COLUMNS FROM appointment LIKE '" . $this->db->real_escape_string($safeColumn) . "'";
        $result = $this->db->query($sql);
        self::$columnCache[$columnName] = ($result && $result->num_rows > 0);
        return self::$columnCache[$columnName];
    }

    private function hasTable($tableName) {
        if (array_key_exists($tableName, self::$tableCache)) {
            return self::$tableCache[$tableName];
        }

        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        $sql = "SHOW TABLES LIKE '" . $this->db->real_escape_string($safeTable) . "'";
        $result = $this->db->query($sql);
        self::$tableCache[$tableName] = ($result && $result->num_rows > 0);
        return self::$tableCache[$tableName];
    }

    private function hasTableColumn($tableName, $columnName) {
        $cacheKey = $tableName . '.' . $columnName;
        if (array_key_exists($cacheKey, self::$tableColumnCache)) {
            return self::$tableColumnCache[$cacheKey];
        }

        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
        $sql = "SHOW COLUMNS FROM `" . $this->db->real_escape_string($safeTable) . "` LIKE '" . $this->db->real_escape_string($safeColumn) . "'";
        $result = $this->db->query($sql);
        self::$tableColumnCache[$cacheKey] = ($result && $result->num_rows > 0);
        return self::$tableColumnCache[$cacheKey];
    }

    private function ensurePrescriptionAuditColumns() {
        if (!$this->hasTable('prescription_requests')) {
            return false;
        }

        $alterStatements = [];

        if (!$this->hasTableColumn('prescription_requests', 'decision_action')) {
            $alterStatements[] = "ADD COLUMN decision_action VARCHAR(40) NULL AFTER status";
        }

        if (!$this->hasTableColumn('prescription_requests', 'decision_by_user_id')) {
            $alterStatements[] = "ADD COLUMN decision_by_user_id INT NULL AFTER decision_action";
        }

        if (!$this->hasTableColumn('prescription_requests', 'decision_at')) {
            $alterStatements[] = "ADD COLUMN decision_at DATETIME NULL AFTER decision_by_user_id";
        }

        if (!$this->hasTableColumn('prescription_requests', 'linked_appointment_id')) {
            $alterStatements[] = "ADD COLUMN linked_appointment_id INT NULL AFTER decision_at";
        }

        if (count($alterStatements) > 0) {
            $sql = "ALTER TABLE prescription_requests " . implode(', ', $alterStatements);
            if (!$this->db->query($sql)) {
                return false;
            }

            unset(self::$tableColumnCache['prescription_requests.decision_action']);
            unset(self::$tableColumnCache['prescription_requests.decision_by_user_id']);
            unset(self::$tableColumnCache['prescription_requests.decision_at']);
            unset(self::$tableColumnCache['prescription_requests.linked_appointment_id']);
        }

        return true;
    }

    private function ensurePrescriptionEventsTable() {
        if (!$this->hasTable('prescription_requests')) {
            return false;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS prescription_request_events (
                event_id INT AUTO_INCREMENT PRIMARY KEY,
                request_id INT NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                old_status VARCHAR(30) NULL,
                new_status VARCHAR(30) NULL,
                note TEXT NULL,
                created_by_user_id INT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_pre_request_id (request_id),
                KEY idx_pre_created_by (created_by_user_id),
                CONSTRAINT fk_pre_request FOREIGN KEY (request_id) REFERENCES prescription_requests(request_id) ON DELETE CASCADE,
                CONSTRAINT fk_pre_created_by FOREIGN KEY (created_by_user_id) REFERENCES users(user_id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";

        if (!$this->db->query($sql)) {
            return false;
        }

        self::$tableCache['prescription_request_events'] = true;
        return true;
    }

    private function ensureHomeCollectionColumns() {
        $alterStatements = [];

        if ($this->hasTable('appointment')) {
            if (!$this->hasAppointmentColumn('home_collection')) {
                $alterStatements[] = "ALTER TABLE appointment ADD COLUMN home_collection TINYINT(1) NOT NULL DEFAULT 0 AFTER booking_channel";
            }

            if (!$this->hasAppointmentColumn('collection_address')) {
                $alterStatements[] = "ALTER TABLE appointment ADD COLUMN collection_address VARCHAR(255) DEFAULT NULL AFTER home_collection";
            }
        }

        if ($this->hasTable('prescription_requests') && !$this->hasTableColumn('prescription_requests', 'home_collection')) {
            $alterStatements[] = "ALTER TABLE prescription_requests ADD COLUMN home_collection TINYINT(1) NOT NULL DEFAULT 0 AFTER preferred_time";
        }

        if ($this->hasTable('prescription_requests') && !$this->hasTableColumn('prescription_requests', 'collection_address')) {
            $alterStatements[] = "ALTER TABLE prescription_requests ADD COLUMN collection_address VARCHAR(255) DEFAULT NULL AFTER home_collection";
        }

        foreach ($alterStatements as $sql) {
            if (!$this->db->query($sql)) {
                return false;
            }
        }

        return true;
    }

    public function addPrescriptionRequestEvent($requestId, $eventType, $oldStatus = null, $newStatus = null, $note = '', $createdByUserId = 0) {
        if (!$this->ensurePrescriptionEventsTable()) {
            return false;
        }

        $uid = $createdByUserId > 0 ? (int)$createdByUserId : null;
        $cleanNote = trim((string)$note);
        $noteValue = $cleanNote !== '' ? $cleanNote : null;

        $stmt = $this->db->prepare(
            "INSERT INTO prescription_request_events (request_id, event_type, old_status, new_status, note, created_by_user_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("issssi", $requestId, $eventType, $oldStatus, $newStatus, $noteValue, $uid);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getPrescriptionRequestEvents($requestId) {
        if (!$this->hasTable('prescription_request_events')) {
            return [];
        }

        $sql = "
            SELECT
                e.event_id,
                e.request_id,
                e.event_type,
                e.old_status,
                e.new_status,
                e.note,
                e.created_by_user_id,
                e.created_at,
                u.username AS created_by_username,
                u.role AS created_by_role
            FROM prescription_request_events e
            LEFT JOIN users u ON u.user_id = e.created_by_user_id
            WHERE e.request_id = ?
            ORDER BY e.created_at DESC, e.event_id DESC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function hasTimeSlotConflict($appointmentDate, $appointmentTime) {
        $hasStatus = $this->hasAppointmentColumn('status');

        if ($hasStatus) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM appointment WHERE appointment_date = ? AND appointment_time = ? AND LOWER(COALESCE(status, 'pending')) <> 'cancelled'");
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM appointment WHERE appointment_date = ? AND appointment_time = ?");
        }

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ss", $appointmentDate, $appointmentTime);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        $stmt->close();

        return ((int)($row['total'] ?? 0)) > 0;
    }

    private function fetchAppointmentsByMethodFilter($includeMethodMatch, $method) {
        $this->ensureHomeCollectionColumns();

        $hasStatus = $this->hasAppointmentColumn('status');
        $hasItems = $this->hasTable('appointment_items');
        $patientProjection = $this->buildPatientProjectionSql('p');

        $statusField = $hasStatus
            ? "COALESCE(NULLIF(a.status, ''), 'Pending')"
            : "'Pending'";

        if ($hasItems) {
            $testsSummaryField = "COALESCE((SELECT GROUP_CONCAT(CONCAT(ti.test_name, ' (LKR ', FORMAT(ai.unit_price, 2), ')') ORDER BY ti.test_name SEPARATOR ', ') FROM appointment_items ai JOIN tests ti ON ti.test_id = ai.test_id WHERE ai.appointment_id = a.appointment_id), t.test_name)";
            $totalPriceField = "COALESCE((SELECT SUM(ai.line_total) FROM appointment_items ai WHERE ai.appointment_id = a.appointment_id), t.price, 0)";
            $itemCountField = "COALESCE((SELECT SUM(ai.quantity) FROM appointment_items ai WHERE ai.appointment_id = a.appointment_id), 1)";
        } else {
            $testsSummaryField = "t.test_name";
            $totalPriceField = "COALESCE(t.price, 0)";
            $itemCountField = "1";
        }

        $operator = $includeMethodMatch ? '=' : '<>';
        $query = "
            SELECT
                a.*,
                {$patientProjection},
                t.test_name,
                t.price AS test_price,
                COALESCE(a.home_collection, 0) AS home_collection,
                COALESCE(a.collection_address, '') AS collection_address,
                " . $statusField . " AS appointment_status,
                " . $testsSummaryField . " AS tests_summary,
                " . $totalPriceField . " AS total_price,
                " . $itemCountField . " AS item_count
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            LEFT JOIN tests t ON t.test_id = a.test_id
            WHERE LOWER(a.method) " . $operator . " LOWER(?)
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";

        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in fetchAppointmentsByMethodFilter: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }

        $stmt->bind_param('s', $method);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in fetchAppointmentsByMethodFilter: ' . $stmt->error;
            error_log($this->lastError);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
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

    public function createReceptionistAppointment($patientId, $testId, $appointmentDate, $appointmentTime, $method = 'Physical', $status = 'Pending', $homeCollection = 0, $collectionAddress = '') {
        $this->ensureHomeCollectionColumns();

        $channel = strtolower($method) === 'call' ? 'receptionist_phone' : 'receptionist_walkin';
        $homeCollectionValue = !empty($homeCollection) ? 1 : 0;
        $collectionAddressValue = trim((string)$collectionAddress);
        $collectionAddressValue = $collectionAddressValue !== '' ? $collectionAddressValue : null;

        $stmt = $this->db->prepare("INSERT INTO appointment (patient_id, test_id, appointment_time, appointment_date, method, status, booking_channel, home_collection, collection_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisssssis", $patientId, $testId, $appointmentTime, $appointmentDate, $method, $status, $channel, $homeCollectionValue, $collectionAddressValue);

        return $stmt->execute();
    }

    public function getAllAppointmentsByMethod($method) {
        return $this->fetchAppointmentsByMethodFilter(true, $method);
    }

    public function getAllAppointmentsExceptMethod($method) {
        return $this->fetchAppointmentsByMethodFilter(false, $method);
    }

    public function getAppointmentsList($filters = [], $page = 1, $perPage = 7, $sortBy = 'appointment_date', $sortDir = 'desc') {
        $this->lastError = '';

        $page = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $rows = $this->getFilteredAppointmentsDataset($filters, $sortBy, $sortDir);

        $offset = ($page - 1) * $perPage;
        return array_slice($rows, $offset, $perPage);
    }

    public function countAppointments($filters = []) {
        $this->lastError = '';
        $rows = $this->getFilteredAppointmentsDataset($filters);
        return count($rows);
    }

    private function getFilteredAppointmentsDataset($filters = [], $sortBy = 'appointment_date', $sortDir = 'desc') {
        $methodFilter = strtolower(trim((string)($filters['method'] ?? 'all')));
        $search = strtolower(trim((string)($filters['search'] ?? '')));
        $fromDate = trim((string)($filters['from_date'] ?? ''));
        $toDate = trim((string)($filters['to_date'] ?? ''));

        $rows = array_merge(
            $this->getAllAppointmentsByMethod('online') ?: [],
            $this->getAllAppointmentsByMethod('physical') ?: [],
            $this->getAllAppointmentsByMethod('call') ?: []
        );

        if (in_array($methodFilter, ['online', 'physical', 'call'], true)) {
            $rows = array_values(array_filter($rows, function ($row) use ($methodFilter) {
                $rowMethod = strtolower(trim((string)($row['method'] ?? '')));
                if ($methodFilter === 'physical') {
                    return in_array($rowMethod, ['physical', 'call'], true);
                }
                return $rowMethod === $methodFilter;
            }));
        }

        if ($search !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($search) {
                $patientName = strtolower((string)($row['patient_name'] ?? ($row['patient_display_name'] ?? '')));
                $appointmentId = strtolower((string)($row['appointment_id'] ?? ''));
                $methodValue = strtolower((string)($row['method'] ?? ''));

                return strpos($patientName, $search) !== false
                    || strpos($appointmentId, $search) !== false
                    || strpos($methodValue, $search) !== false;
            }));
        }

        if ($fromDate !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($fromDate) {
                $rowDate = (string)($row['appointment_date'] ?? '');
                return $rowDate !== '' && $rowDate >= $fromDate;
            }));
        }

        if ($toDate !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($toDate) {
                $rowDate = (string)($row['appointment_date'] ?? '');
                return $rowDate !== '' && $rowDate <= $toDate;
            }));
        }

        $sortAllowlist = [
            'appointment_id' => 'appointment_id',
            'patient_name' => 'patient_name',
            'appointment_date' => 'appointment_date',
            'appointment_time' => 'appointment_time',
            'method' => 'method',
        ];
        $sortKey = $sortAllowlist[strtolower(trim((string)$sortBy))] ?? 'appointment_date';
        $direction = strtolower(trim((string)$sortDir)) === 'asc' ? 1 : -1;

        usort($rows, function ($a, $b) use ($sortKey, $direction) {
            $aValue = $a[$sortKey] ?? '';
            $bValue = $b[$sortKey] ?? '';

            if (is_numeric($aValue) && is_numeric($bValue)) {
                $cmp = intval($aValue) <=> intval($bValue);
            } else {
                $cmp = strcmp(strtolower((string)$aValue), strtolower((string)$bValue));
            }

            return $cmp * $direction;
        });

        return $rows;
    }

    public function getAllTests() {
        $result = $this->db->query("SELECT test_id, test_name, price FROM tests ORDER BY test_name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPrescriptionRequests($status = 'Pending') {
        if (!$this->hasTable('prescription_requests')) {
            return [];
        }
        $stmt->bind_param("s", $method);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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