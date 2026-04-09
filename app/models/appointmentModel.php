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

            if ($this->columnExists('appointment', 'test_id')) {
                $legacyCsv = implode(',', $cleanTestIds);
                $legacyStmt = $this->db->prepare('UPDATE appointment SET test_id = ? WHERE appointment_id = ?');
                if ($legacyStmt === false) {
                    throw new Exception('Prepare failed in createAppointmentWithTests (legacy test_id): ' . $this->db->error);
                }

                $legacyStmt->bind_param('si', $legacyCsv, $appointmentId);
                if (!$legacyStmt->execute()) {
                    throw new Exception('Execute failed in createAppointmentWithTests (legacy test_id): ' . $legacyStmt->error);
                }
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

    public function getAllTests() {
        $result = $this->db->query("SELECT test_id, test_name, price FROM tests ORDER BY test_name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPrescriptionRequests($status = 'Pending') {
        if (!$this->hasTable('prescription_requests')) {
            return [];
        }

        $this->ensurePrescriptionAuditColumns();
        $this->ensureHomeCollectionColumns();

        $decisionActionSelect = $this->hasTableColumn('prescription_requests', 'decision_action')
            ? "pr.decision_action"
            : "NULL AS decision_action";
        $decisionBySelect = $this->hasTableColumn('prescription_requests', 'decision_by_user_id')
            ? "pr.decision_by_user_id"
            : "NULL AS decision_by_user_id";
        $decisionAtSelect = $this->hasTableColumn('prescription_requests', 'decision_at')
            ? "pr.decision_at"
            : "NULL AS decision_at";
        $linkedAppointmentSelect = $this->hasTableColumn('prescription_requests', 'linked_appointment_id')
            ? "pr.linked_appointment_id"
            : "NULL AS linked_appointment_id";
        $homeCollectionSelect = $this->hasTableColumn('prescription_requests', 'home_collection')
            ? "pr.home_collection"
            : "0 AS home_collection";
        $collectionAddressSelect = $this->hasTableColumn('prescription_requests', 'collection_address')
            ? "pr.collection_address"
            : "NULL AS collection_address";

        $whereClause = "";
        $status = trim((string)$status);
        if ($status !== '' && strtolower($status) !== 'all') {
            $whereClause = "WHERE LOWER(pr.status) = LOWER(?)";
        }

        $sql = "
            SELECT
                pr.request_id,
                pr.patient_id,
                pr.prescription_file_path,
                pr.notes,
                pr.preferred_date,
                pr.preferred_time,
                " . $homeCollectionSelect . ",
                " . $collectionAddressSelect . ",
                pr.status,
                " . $decisionActionSelect . ",
                " . $decisionBySelect . ",
                " . $decisionAtSelect . ",
                " . $linkedAppointmentSelect . ",
                pr.created_at,
                p.patient_name,
                p.email,
                p.contact_number
            FROM prescription_requests pr
            LEFT JOIN patients p ON p.patient_id = pr.patient_id
            " . $whereClause . "
            ORDER BY pr.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($whereClause !== '') {
            $stmt->bind_param("s", $status);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    public function getPrescriptionRequestById($requestId) {
        if (!$this->hasTable('prescription_requests')) {
            return null;
        }

        $this->ensurePrescriptionAuditColumns();
        $this->ensureHomeCollectionColumns();

        $decisionActionSelect = $this->hasTableColumn('prescription_requests', 'decision_action')
            ? "pr.decision_action"
            : "NULL AS decision_action";
        $decisionBySelect = $this->hasTableColumn('prescription_requests', 'decision_by_user_id')
            ? "pr.decision_by_user_id"
            : "NULL AS decision_by_user_id";
        $decisionAtSelect = $this->hasTableColumn('prescription_requests', 'decision_at')
            ? "pr.decision_at"
            : "NULL AS decision_at";
        $linkedAppointmentSelect = $this->hasTableColumn('prescription_requests', 'linked_appointment_id')
            ? "pr.linked_appointment_id"
            : "NULL AS linked_appointment_id";
        $homeCollectionSelect = $this->hasTableColumn('prescription_requests', 'home_collection')
            ? "pr.home_collection"
            : "0 AS home_collection";
        $collectionAddressSelect = $this->hasTableColumn('prescription_requests', 'collection_address')
            ? "pr.collection_address"
            : "NULL AS collection_address";

        $sql = "
            SELECT
                pr.request_id,
                pr.patient_id,
                pr.prescription_file_path,
                pr.notes,
                pr.preferred_date,
                pr.preferred_time,
                " . $homeCollectionSelect . ",
                " . $collectionAddressSelect . ",
                pr.status,
                " . $decisionActionSelect . ",
                " . $decisionBySelect . ",
                " . $decisionAtSelect . ",
                " . $linkedAppointmentSelect . ",
                pr.created_at,
                p.patient_name,
                p.email,
                p.contact_number
            FROM prescription_requests pr
            LEFT JOIN patients p ON p.patient_id = pr.patient_id
            WHERE pr.request_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $requestId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
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
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return null;
        }

        $tests = $this->getAppointmentTestsWithStatus($appointmentId);
        $canEditScheduleTests = $this->areAllTestsPending($tests);
        $nonPendingStatuses = $this->collectNonPendingStatuses($tests);

        return [
            'appointment' => $row,
            'tests' => $tests,
            'available_tests' => $this->searchTestsCatalog('', 20),
            'can_edit_schedule_tests' => $canEditScheduleTests,
            'non_pending_statuses' => $nonPendingStatuses,
        ];
    }

    public function getPrescriptionDecisionReport($filters = []) {
        if (!$this->hasTable('prescription_requests')) {
            return [];
        }

        $this->ensurePrescriptionAuditColumns();
        $this->ensureHomeCollectionColumns();

        $where = ["LOWER(COALESCE(pr.status, 'pending')) <> 'pending'"];
        $types = '';
        $params = [];

        $status = trim((string)($filters['status'] ?? ''));
        if ($status !== '') {
            $where[] = "LOWER(pr.status) = LOWER(?)";
            $types .= 's';
            $params[] = $status;
        }

        $decisionAction = trim((string)($filters['decision_action'] ?? ''));
        if ($decisionAction !== '') {
            $where[] = "LOWER(COALESCE(pr.decision_action, '')) = LOWER(?)";
            $types .= 's';
            $params[] = $decisionAction;
        }

        $dateFrom = trim((string)($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $where[] = "DATE(COALESCE(pr.decision_at, pr.updated_at, pr.created_at)) >= ?";
            $types .= 's';
            $params[] = $dateFrom;
        }

        $dateTo = trim((string)($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $where[] = "DATE(COALESCE(pr.decision_at, pr.updated_at, pr.created_at)) <= ?";
            $types .= 's';
            $params[] = $dateTo;
        }

        $decisionByUserId = (int)($filters['decision_by_user_id'] ?? 0);
        if ($decisionByUserId > 0) {
            $where[] = "pr.decision_by_user_id = ?";
            $types .= 'i';
            $params[] = $decisionByUserId;
        }

        $sql = "
            SELECT
                pr.request_id,
                pr.patient_id,
                pr.status,
                pr.home_collection,
                pr.collection_address,
                pr.decision_action,
                pr.decision_by_user_id,
                pr.decision_at,
                pr.linked_appointment_id,
                pr.notes,
                pr.created_at,
                pr.updated_at,
                p.patient_name,
                p.email,
                p.contact_number,
                u.username AS decision_by_username,
                u.role AS decision_by_role
            FROM prescription_requests pr
            LEFT JOIN patients p ON p.patient_id = pr.patient_id
            LEFT JOIN users u ON u.user_id = pr.decision_by_user_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY COALESCE(pr.decision_at, pr.updated_at, pr.created_at) DESC, pr.request_id DESC
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    public function getPrescriptionDecisionSummary() {
        if (!$this->hasTable('prescription_requests')) {
            return [
                'total_requests' => 0,
                'pending' => 0,
                'processed' => 0,
                'booked_by_receptionist' => 0,
                'self_book_requested' => 0,
            ];
        }

        $sql = "
            SELECT
                COUNT(*) AS total_requests,
                SUM(CASE WHEN LOWER(COALESCE(status, 'pending')) = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN LOWER(COALESCE(status, 'pending')) <> 'pending' THEN 1 ELSE 0 END) AS processed,
                SUM(CASE WHEN LOWER(COALESCE(decision_action, '')) = 'book_for_patient' THEN 1 ELSE 0 END) AS booked_by_receptionist,
                SUM(CASE WHEN LOWER(COALESCE(decision_action, '')) = 'self_book' THEN 1 ELSE 0 END) AS self_book_requested
            FROM prescription_requests
        ";

        $result = $this->db->query($sql);
        if (!$result) {
            return [
                'total_requests' => 0,
                'pending' => 0,
                'processed' => 0,
                'booked_by_receptionist' => 0,
                'self_book_requested' => 0,
            ];
        }

        $row = $result->fetch_assoc() ?: [];
        return [
            'total_requests' => (int)($row['total_requests'] ?? 0),
            'pending' => (int)($row['pending'] ?? 0),
            'processed' => (int)($row['processed'] ?? 0),
            'booked_by_receptionist' => (int)($row['booked_by_receptionist'] ?? 0),
            'self_book_requested' => (int)($row['self_book_requested'] ?? 0),
        ];
    }

    private function appendDecisionNote($requestId, $decisionLine) {
        $decisionLine = trim($decisionLine);
        if ($decisionLine === '') {
            return true;
        }

        if (!$this->hasTableColumn('prescription_requests', 'notes')) {
            return true;
        }

        $entry = '[' . date('Y-m-d H:i:s') . '] ' . $decisionLine;
        $stmt = $this->db->prepare(
            "UPDATE prescription_requests
             SET notes = CASE
                    WHEN notes IS NULL OR notes = '' THEN ?
                    ELSE CONCAT(notes, '\n', ?)
                 END
             WHERE request_id = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssi", $entry, $entry, $requestId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function markPrescriptionRequestSelfBooking($requestId, $note = '', $decisionByUserId = 0) {
        if (!$this->hasTable('prescription_requests')) {
            return false;
        }

        if (!$this->ensurePrescriptionAuditColumns()) {
            return false;
        }

        $request = $this->getPrescriptionRequestById($requestId);
        $oldStatus = $request['status'] ?? null;

        $status = 'Self Booking Requested';
        $decisionAction = 'self_book';
        $now = date('Y-m-d H:i:s');
        $linkedAppointmentId = null;
        $stmt = $this->db->prepare("UPDATE prescription_requests SET status = ?, decision_action = ?, decision_by_user_id = ?, decision_at = ?, linked_appointment_id = ? WHERE request_id = ? AND LOWER(status) = 'pending'");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssissi", $status, $decisionAction, $decisionByUserId, $now, $linkedAppointmentId, $requestId);
        $updated = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if (!$updated || $affected <= 0) {
            return false;
        }

        $decision = 'Receptionist asked patient to self-book.';
        if (trim($note) !== '') {
            $decision .= ' Note: ' . trim($note);
        }

        $noteSaved = $this->appendDecisionNote($requestId, $decision);
        $eventSaved = $this->addPrescriptionRequestEvent(
            $requestId,
            'self_book_requested',
            $oldStatus,
            $status,
            $note,
            $decisionByUserId
        );

        return $noteSaved && $eventSaved;
    }

    public function markPrescriptionRequestBooked($requestId, $appointmentId, $decisionByUserId = 0) {
        if (!$this->hasTable('prescription_requests')) {
            return false;
        }

        if (!$this->ensurePrescriptionAuditColumns()) {
            return false;
        }

        $request = $this->getPrescriptionRequestById($requestId);
        $oldStatus = $request['status'] ?? null;

        $status = 'Booked by Receptionist';
        $decisionAction = 'book_for_patient';
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("UPDATE prescription_requests SET status = ?, decision_action = ?, decision_by_user_id = ?, decision_at = ?, linked_appointment_id = ? WHERE request_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssisii", $status, $decisionAction, $decisionByUserId, $now, $appointmentId, $requestId);
        $updated = $stmt->execute();
        $stmt->close();

        if (!$updated) {
            return false;
        }

        $noteText = 'Receptionist created appointment #' . (int)$appointmentId . ' from this request.';
        $noteSaved = $this->appendDecisionNote($requestId, $noteText);
        $eventSaved = $this->addPrescriptionRequestEvent(
            $requestId,
            'booked_by_receptionist',
            $oldStatus,
            $status,
            $noteText,
            $decisionByUserId
        );

        return $noteSaved && $eventSaved;
    }

    public function getAppointmentEmailPayload($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return null;
        }

        $hasStatus = $this->hasAppointmentColumn('status');
        $hasChannel = $this->hasAppointmentColumn('booking_channel');
        $hasHomeCollection = $this->hasAppointmentColumn('home_collection');
        $hasCollectionAddress = $this->hasAppointmentColumn('collection_address');
        $hasItems = $this->hasTable('appointment_items');

        $statusField = $hasStatus ? "COALESCE(NULLIF(a.status, ''), 'Pending')" : "'Pending'";
        $channelField = $hasChannel ? "COALESCE(NULLIF(a.booking_channel, ''), 'receptionist_walkin')" : "'receptionist_walkin'";
        $homeCollectionField = $hasHomeCollection ? "COALESCE(a.home_collection, 0)" : "0";
        $collectionAddressField = $hasCollectionAddress ? "COALESCE(a.collection_address, '')" : "''";

        if ($hasItems) {
            $summaryField = "COALESCE((SELECT GROUP_CONCAT(ti.test_name ORDER BY ti.test_name SEPARATOR ', ') FROM appointment_items ai JOIN tests ti ON ti.test_id = ai.test_id WHERE ai.appointment_id = a.appointment_id), t.test_name)";
            $totalField = "COALESCE((SELECT SUM(ai.line_total) FROM appointment_items ai WHERE ai.appointment_id = a.appointment_id), t.price, 0)";
        } else {
            $summaryField = "t.test_name";
            $totalField = "COALESCE(t.price, 0)";
        }

        $sql = "
            SELECT
                a.appointment_id,
                a.appointment_date,
                a.appointment_time,
                " . $statusField . " AS status,
                " . $channelField . " AS booking_channel,
                " . $homeCollectionField . " AS home_collection,
                " . $collectionAddressField . " AS collection_address,
                " . $summaryField . " AS tests_summary,
                " . $totalField . " AS total_price
            FROM appointment a
            LEFT JOIN tests t ON t.test_id = a.test_id
            WHERE a.appointment_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getAppointmentEmailPayload: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        $stmt->bind_param("i", $appointmentId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getAppointmentEmailPayload: ' . $stmt->error;
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

        if ($query !== '') {
            $sql = "
                SELECT test_id, test_name, category, price
                FROM tests
                WHERE test_name LIKE CONCAT('%', ?, '%')
                   OR category LIKE CONCAT('%', ?, '%')
                ORDER BY test_name ASC
                LIMIT {$limit}
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) {
                return [];
            }

            $stmt->bind_param('ss', $query, $query);
            if (!$stmt->execute()) {
                return [];
            }

            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        $sql = "
            SELECT test_id, test_name, category, price
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

                if ($this->columnExists('appointment', 'test_id')) {
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

        if ($this->tableExists('appointment_tests')) {
            $statusColumn = $this->resolveFirstExistingColumn('appointment_tests', ['status', 'workflow_status', 'progress_status']);
            $orderColumn = $this->resolveFirstExistingColumn('appointment_tests', ['appointment_test_id', 'id', 'created_at', 'test_id']);

            $statusSelect = $statusColumn !== null ? "at.{$statusColumn} AS status" : "'PENDING' AS status";
            $orderBy = $orderColumn !== null ? " ORDER BY at.{$orderColumn} ASC" : '';

            $sql = "
                SELECT
                    at.test_id,
                    t.test_name,
                    t.category,
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
                t.category,
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