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
        $hasTestId = $this->hasAppointmentColumn('test_id');
        $hasHomeCollection = $this->hasAppointmentColumn('home_collection');
        $hasCollectionAddress = $this->hasAppointmentColumn('collection_address');
        $patientProjection = $this->buildPatientProjectionSql('p');
        $notDeletedClause = $this->buildNotDeletedClause('a');

        $statusField = $hasStatus
            ? "COALESCE(NULLIF(a.status, ''), 'Pending')"
            : "'Pending'";

        $homeCollectionField = $hasHomeCollection ? 'COALESCE(a.home_collection, 0)' : '0';
        $collectionAddressField = $hasCollectionAddress ? "COALESCE(a.collection_address, '')" : "''";
        $testsJoinSql = $hasTestId
            ? 'LEFT JOIN tests t ON t.test_id = a.test_id'
            : 'LEFT JOIN tests t ON 1 = 0';

        if ($hasItems) {
            $testsSummaryField = "COALESCE((SELECT GROUP_CONCAT(CONCAT(ti.test_name, ' (LKR ', FORMAT(ai.unit_price, 2), ')') ORDER BY ti.test_name SEPARATOR ', ') FROM appointment_items ai JOIN tests ti ON ti.test_id = ai.test_id WHERE ai.appointment_id = a.appointment_id), t.test_name)";
            $totalPriceField = "COALESCE((SELECT SUM(ai.line_total) FROM appointment_items ai WHERE ai.appointment_id = a.appointment_id), t.price, 0)";
            $itemCountField = "COALESCE((SELECT SUM(ai.quantity) FROM appointment_items ai WHERE ai.appointment_id = a.appointment_id), 1)";
        } else {
            $testsSummaryField = "t.test_name";
            $totalPriceField = "COALESCE(t.price, 0)";
            $itemCountField = "1";
        }

        $whereParts = [$notDeletedClause];
        if ($method !== '*') {
            $operator = $includeMethodMatch ? '=' : '<>';
            $whereParts[] = "LOWER(a.method) " . $operator . " LOWER(?)";
        }
        $whereClause = 'WHERE ' . implode(' AND ', $whereParts);

        $query = "
            SELECT
                a.*,
                {$patientProjection},
                t.test_name,
                t.price AS test_price,
                {$homeCollectionField} AS home_collection,
                {$collectionAddressField} AS collection_address,
                " . $statusField . " AS appointment_status,
                " . $testsSummaryField . " AS tests_summary,
                " . $totalPriceField . " AS total_price,
                " . $itemCountField . " AS item_count
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            {$testsJoinSql}
            {$whereClause}
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";

        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in fetchAppointmentsByMethodFilter: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }

        if ($method !== '*') {
            $stmt->bind_param('s', $method);
        }
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
        $methodFilter = $this->normalizeAppointmentMethod($filters['method'] ?? 'all');
        if (!in_array($methodFilter, ['all', 'online', 'physical', 'call'], true)) {
            $methodFilter = 'all';
        }
        $search = strtolower(trim((string)($filters['search'] ?? '')));
        $fromDate = trim((string)($filters['from_date'] ?? ''));
        $toDate = trim((string)($filters['to_date'] ?? ''));

        $rows = $this->getAllAppointmentsByMethod('*') ?: [];

        if (in_array($methodFilter, ['online', 'physical', 'call'], true)) {
            $rows = array_values(array_filter($rows, function ($row) use ($methodFilter) {
                $rowMethod = $this->normalizeAppointmentMethod($row['method'] ?? '');
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
                $methodValue = strtolower((string)$this->normalizeAppointmentMethod($row['method'] ?? ''));

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

            if ($sortKey === 'method') {
                $aValue = $this->normalizeAppointmentMethod($aValue);
                $bValue = $this->normalizeAppointmentMethod($bValue);
            }

            if (is_numeric($aValue) && is_numeric($bValue)) {
                $cmp = intval($aValue) <=> intval($bValue);
            } else {
                $cmp = strcmp(strtolower((string)$aValue), strtolower((string)$bValue));
            }

            return $cmp * $direction;
        });

        return $rows;
    }

    private function normalizeAppointmentMethod($method) {
        $normalized = strtolower(trim((string)$method));

        if ($normalized === '' || $normalized === 'all') {
            return $normalized === '' ? 'physical' : 'all';
        }

        if (in_array($normalized, ['online', 'virtual', 'video', 'telemedicine', 'telemed', 'remote'], true)) {
            return 'online';
        }

        if (in_array($normalized, ['call', 'phone', 'telephone', 'phone_call', 'phone-call'], true)) {
            return 'call';
        }

        if (in_array($normalized, ['physical', 'in_person', 'in-person', 'walkin', 'walk-in', 'offline', 'onsite', 'on-site'], true)) {
            return 'physical';
        }

        return $normalized;
    }

    public function getAllTests() {
        $result = $this->db->query("SELECT test_id, test_name, price FROM tests ORDER BY test_name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAppointmentDetailsPayload($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return null;
        }

        $patientProjection = $this->buildPatientProjectionSql('p');
        $notDeletedClause = $this->buildNotDeletedClause('a');
        $hasReason = $this->columnExists('appointment', 'reason');
        $reasonSelect = $hasReason ? 'COALESCE(a.reason, "") AS reason,' : '"" AS reason,';

        $sql = "
            SELECT
                a.*,
                {$reasonSelect}
                {$patientProjection}
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
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $appointment = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if (!$appointment) {
            return null;
        }

        $tests = $this->getAppointmentTestsWithStatus($appointmentId);
        $billing = $this->getBillingSummary($appointmentId);

        return [
            'appointment' => $appointment,
            'tests' => $tests,
            'billing' => $billing,
        ];
    }

    public function getAppointmentEditPayload($appointmentId) {
        $payload = $this->getAppointmentDetailsPayload($appointmentId);
        if ($payload === null) {
            return null;
        }

        $tests = $payload['tests'];
        $nonPendingStatuses = [];

        foreach ($tests as $test) {
            $status = strtoupper(trim((string) ($test['status'] ?? 'PENDING')));
            if ($status !== 'PENDING') {
                $nonPendingStatuses[$status] = $status;
            }
        }

        $appointment = $payload['appointment'];
        $appointment['patient_display_name'] = $appointment['patient_name'] ?? ('Patient #' . intval($appointment['patient_id'] ?? 0));
        $appointment['patient_display_pid'] = $appointment['pid'] ?? ('P-' . intval($appointment['patient_id'] ?? 0));

        return [
            'appointment' => $appointment,
            'tests' => $tests,
            'can_edit_schedule_tests' => empty($nonPendingStatuses),
            'non_pending_statuses' => array_values($nonPendingStatuses),
        ];
    }

    public function searchTestsCatalog($query = '', $limit = 20) {
        if (!$this->tableExists('tests')) {
            return [];
        }

        $safeLimit = max(1, min(50, intval($limit)));
        $categoryCol = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categoryExpr = $categoryCol !== null ? "COALESCE(t.{$categoryCol}, '')" : "''";

        $sql = "
            SELECT
                t.test_id,
                COALESCE(t.test_name, '') AS test_name,
                {$categoryExpr} AS category,
                COALESCE(t.price, 0) AS price
            FROM tests t
        ";

        $types = '';
        $params = [];
        $query = trim((string) $query);
        if ($query !== '') {
            $sql .= ' WHERE LOWER(COALESCE(t.test_name, \"\")) LIKE ? OR LOWER(' . $categoryExpr . ') LIKE ?';
            $needle = '%' . strtolower($query) . '%';
            $types = 'ss';
            $params[] = $needle;
            $params[] = $needle;
        }

        $sql .= ' ORDER BY t.test_name ASC LIMIT ' . $safeLimit;

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in searchTestsCatalog: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in searchTestsCatalog: ' . $stmt->error;
            error_log($this->lastError);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function updateAppointmentWithTests($appointmentId, $appointmentDate, $appointmentTime, $reason = '', $testIds = []) {
        $appointmentId = intval($appointmentId);
        $cleanTestIds = $this->normalizeTestIds($testIds);

        if ($appointmentId <= 0) {
            $this->lastError = 'Invalid appointment ID.';
            return false;
        }

        if (empty($cleanTestIds)) {
            $this->lastError = 'Please select at least one test.';
            return false;
        }

        $payload = $this->getAppointmentEditPayload($appointmentId);
        if ($payload === null) {
            $this->lastError = 'Appointment not found.';
            return false;
        }

        $existingTests = $payload['tests'] ?? [];
        $nonPending = !empty($payload['non_pending_statuses']);
        if ($nonPending) {
            $existingIds = [];
            foreach ($existingTests as $row) {
                $existingIds[] = intval($row['test_id'] ?? 0);
            }
            sort($existingIds);
            $incomingIds = $cleanTestIds;
            sort($incomingIds);

            $scheduleChanged = ((string) ($payload['appointment']['appointment_date'] ?? '') !== (string) $appointmentDate)
                || ((string) ($payload['appointment']['appointment_time'] ?? '') !== (string) $appointmentTime);

            if ($scheduleChanged || $existingIds !== $incomingIds) {
                $this->lastError = 'Schedule and tests can only be modified while all tests are in PENDING status.';
                return false;
            }
        }

        $this->db->begin_transaction();
        try {
            $hasReason = $this->columnExists('appointment', 'reason');
            if ($hasReason) {
                $headerSql = 'UPDATE appointment SET appointment_date = ?, appointment_time = ?, reason = ? WHERE appointment_id = ?';
                $headerStmt = $this->db->prepare($headerSql);
                if ($headerStmt === false) {
                    throw new Exception('Prepare failed in updateAppointmentWithTests: ' . $this->db->error);
                }
                $headerStmt->bind_param('sssi', $appointmentDate, $appointmentTime, $reason, $appointmentId);
            } else {
                $headerSql = 'UPDATE appointment SET appointment_date = ?, appointment_time = ? WHERE appointment_id = ?';
                $headerStmt = $this->db->prepare($headerSql);
                if ($headerStmt === false) {
                    throw new Exception('Prepare failed in updateAppointmentWithTests: ' . $this->db->error);
                }
                $headerStmt->bind_param('ssi', $appointmentDate, $appointmentTime, $appointmentId);
            }

            if (!$headerStmt->execute()) {
                throw new Exception('Execute failed in updateAppointmentWithTests: ' . $headerStmt->error);
            }

            if ($this->tableExists('appointment_tests') && !$nonPending) {
                $deleteSql = 'DELETE FROM appointment_tests WHERE appointment_id = ?';
                $deleteStmt = $this->db->prepare($deleteSql);
                if ($deleteStmt === false) {
                    throw new Exception('Prepare failed while resetting appointment tests: ' . $this->db->error);
                }
                $deleteStmt->bind_param('i', $appointmentId);
                if (!$deleteStmt->execute()) {
                    throw new Exception('Execute failed while resetting appointment tests: ' . $deleteStmt->error);
                }

                $insertSql = "INSERT INTO appointment_tests (appointment_id, test_id, status) VALUES (?, ?, 'PENDING')";
                $insertStmt = $this->db->prepare($insertSql);
                if ($insertStmt === false) {
                    throw new Exception('Prepare failed while saving appointment tests: ' . $this->db->error);
                }

                foreach ($cleanTestIds as $testId) {
                    $insertStmt->bind_param('ii', $appointmentId, $testId);
                    if (!$insertStmt->execute()) {
                        throw new Exception('Execute failed while saving appointment tests: ' . $insertStmt->error);
                    }
                }
            }

            if ($this->columnExists('appointment', 'test_id')) {
                $primaryTestId = intval($cleanTestIds[0]);
                $legacyStmt = $this->db->prepare('UPDATE appointment SET test_id = ? WHERE appointment_id = ?');
                if ($legacyStmt !== false) {
                    $legacyStmt->bind_param('ii', $primaryTestId, $appointmentId);
                    $legacyStmt->execute();
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

        if ($appointmentId <= 0 || $testId <= 0) {
            $this->lastError = 'Invalid appointment_id or test_id.';
            return false;
        }

        if (!$this->tableExists('appointment_tests')) {
            $this->lastError = 'Table appointment_tests does not exist.';
            return false;
        }

        $statusCol = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'test_status']);
        if ($statusCol === null) {
            $this->lastError = 'Unable to resolve appointment_tests status column.';
            return false;
        }

        $assignedCol = $this->resolveFirstExistingColumn('appointment_tests', ['assigned_to']);

        $lookupSql = "SELECT {$statusCol} AS status FROM appointment_tests WHERE appointment_id = ? AND test_id = ? LIMIT 1";
        $lookupStmt = $this->db->prepare($lookupSql);
        if ($lookupStmt === false) {
            $this->lastError = 'Prepare failed in startTestInProgress: ' . $this->db->error;
            return false;
        }

        $lookupStmt->bind_param('ii', $appointmentId, $testId);
        if (!$lookupStmt->execute()) {
            $this->lastError = 'Execute failed in startTestInProgress: ' . $lookupStmt->error;
            return false;
        }

        $rowResult = $lookupStmt->get_result();
        $row = $rowResult ? $rowResult->fetch_assoc() : null;
        if (!$row) {
            $this->lastError = 'Appointment test record not found.';
            return false;
        }

        $currentStatus = strtoupper(trim((string) ($row['status'] ?? 'PENDING')));
        if ($currentStatus !== 'PENDING') {
            $this->lastError = 'Only pending tests can be moved to IN_PROGRESS.';
            return false;
        }

        $setParts = ["{$statusCol} = 'IN_PROGRESS'"];
        $types = 'ii';
        $params = [$appointmentId, $testId];

        if ($assignedCol !== null && is_numeric($actorUserId) && intval($actorUserId) > 0) {
            $setParts[] = "{$assignedCol} = ?";
            $types = 'iii';
            $params = [intval($actorUserId), $appointmentId, $testId];
        }

        $updateSql = 'UPDATE appointment_tests SET ' . implode(', ', $setParts) . ' WHERE appointment_id = ? AND test_id = ?';
        $updateStmt = $this->db->prepare($updateSql);
        if ($updateStmt === false) {
            $this->lastError = 'Prepare failed in startTestInProgress(update): ' . $this->db->error;
            return false;
        }

        $updateStmt->bind_param($types, ...$params);
        if (!$updateStmt->execute()) {
            $this->lastError = 'Execute failed in startTestInProgress(update): ' . $updateStmt->error;
            return false;
        }

        return [
            'appointment_id' => $appointmentId,
            'test_id' => $testId,
            'status' => 'IN_PROGRESS'
        ];
    }

    public function getPrescriptionRequests($status = 'Pending') {
        if (!$this->hasTable('prescription_requests')) {
            return [];
        }

        $status = trim((string) $status);
        $whereSql = '';
        $types = '';
        $params = [];

        if ($status !== '' && strtolower($status) !== 'all') {
            $whereSql = 'WHERE LOWER(COALESCE(pr.status, "pending")) = LOWER(?)';
            $types = 's';
            $params[] = $status;
        }

        $patientNameExpr = $this->buildPatientNameExpr('p');
        $contactCol = $this->resolveFirstExistingColumn('patients', ['contact_number', 'phone_number', 'phone', 'mobile']);
        $contactExpr = $contactCol !== null ? "COALESCE(p.{$contactCol}, '')" : "''";

        $sql = "
            SELECT
                pr.request_id,
                pr.patient_id,
                pr.prescription_file_path,
                pr.preferred_date,
                pr.preferred_time,
                pr.home_collection,
                pr.collection_address,
                pr.notes,
                pr.symptoms,
                pr.status,
                pr.created_at,
                pr.updated_at,
                pr.decision_action,
                pr.decision_by_user_id,
                pr.decision_at,
                pr.linked_appointment_id,
                COALESCE(u.username, '') AS decision_by_username,
                {$patientNameExpr} AS patient_name,
                {$contactExpr} AS contact_number,
                COALESCE(p.email, '') AS email
            FROM prescription_requests pr
            LEFT JOIN patients p ON p.patient_id = pr.patient_id
            LEFT JOIN users u ON u.user_id = pr.decision_by_user_id
            {$whereSql}
            ORDER BY pr.created_at DESC, pr.request_id DESC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getPrescriptionRequests: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getPrescriptionRequests: ' . $stmt->error;
            error_log($this->lastError);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function tableExists($tableName) {
        return $this->hasTable($tableName);
    }

    public function columnExists($tableName, $columnName) {
        return $this->hasTableColumn($tableName, $columnName);
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
            $parts[] = "({$alias}.deleted_at IS NULL OR {$alias}.deleted_at = '0000-00-00 00:00:00' OR {$alias}.deleted_at = '0000-00-00')";
        }

        if ($this->columnExists('appointment', 'deleted_by')) {
            $parts[] = "({$alias}.deleted_by IS NULL OR {$alias}.deleted_by = 0)";
        }

        if (empty($parts)) {
            return '1 = 1';
        }

        return implode(' AND ', $parts);
    }

    private function getAppointmentTestsWithStatus($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return [];
        }

        if (!$this->tableExists('appointment_tests')) {
            return $this->getLegacyAppointmentTests($appointmentId);
        }

        $statusCol = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'test_status']);
        $pkCol = $this->resolveFirstExistingColumn('appointment_tests', ['appointment_test_id', 'id', 'appointment_testid']);
        $statusExpr = $statusCol !== null ? "at.{$statusCol}" : "'PENDING'";
        $pkExpr = $pkCol !== null ? "at.{$pkCol}" : '0';

        $hasTestsTable = $this->tableExists('tests');
        $testsJoinSql = $hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = at.test_id' : '';
        $testNameExpr = $hasTestsTable ? "COALESCE(t.test_name, '')" : "''";
        $categoryCol = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categoryExpr = ($hasTestsTable && $categoryCol !== null) ? "COALESCE(t.{$categoryCol}, '')" : "''";
        $priceExpr = $hasTestsTable ? 'COALESCE(t.price, 0)' : '0';

        $sql = "
            SELECT
                {$pkExpr} AS appointment_test_id,
                at.test_id,
                {$testNameExpr} AS test_name,
                {$categoryExpr} AS category,
                {$priceExpr} AS price,
                UPPER(COALESCE({$statusExpr}, 'PENDING')) AS status
            FROM appointment_tests at
            {$testsJoinSql}
            WHERE at.appointment_id = ?
            ORDER BY test_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return $this->getLegacyAppointmentTests($appointmentId);
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $stmt->close();
            return $this->getLegacyAppointmentTests($appointmentId);
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        if (!empty($rows)) {
            return $rows;
        }

        return $this->getLegacyAppointmentTests($appointmentId);
    }

    private function getLegacyAppointmentTests($appointmentId) {
        if (!$this->columnExists('appointment', 'test_id')) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT test_id, status FROM appointment WHERE appointment_id = ? LIMIT 1');
        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        if (!$row) {
            return [];
        }

        $testId = intval($row['test_id'] ?? 0);
        if ($testId <= 0) {
            return [];
        }

        $status = strtoupper(trim((string) ($row['status'] ?? 'PENDING')));
        if ($status === '') {
            $status = 'PENDING';
        }

        if (!$this->tableExists('tests')) {
            return [[
                'appointment_test_id' => 0,
                'test_id' => $testId,
                'test_name' => '',
                'category' => '',
                'price' => 0,
                'status' => $status,
            ]];
        }

        $categoryCol = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $categoryExpr = $categoryCol !== null ? "COALESCE(t.{$categoryCol}, '')" : "''";

        $testStmt = $this->db->prepare("SELECT t.test_id, COALESCE(t.test_name, '') AS test_name, {$categoryExpr} AS category, COALESCE(t.price, 0) AS price FROM tests t WHERE t.test_id = ? LIMIT 1");
        if ($testStmt === false) {
            return [];
        }

        $testStmt->bind_param('i', $testId);
        if (!$testStmt->execute()) {
            $testStmt->close();
            return [];
        }

        $testResult = $testStmt->get_result();
        $testRow = $testResult ? $testResult->fetch_assoc() : null;
        $testStmt->close();
        if (!$testRow) {
            return [];
        }

        $testRow['appointment_test_id'] = 0;
        $testRow['status'] = $status;
        return [$testRow];
    }

    private function getBillingSummary($appointmentId) {
        $summary = [
            'total_fee' => 0,
            'payment_status' => '',
            'reference' => '',
        ];

        if (!$this->tableExists('billing')) {
            $tests = $this->getAppointmentTestsWithStatus($appointmentId);
            $total = 0;
            foreach ($tests as $test) {
                $total += floatval($test['price'] ?? 0);
            }
            $summary['total_fee'] = $total;
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
            $stmt->close();
            return $summary;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();
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


}
?>