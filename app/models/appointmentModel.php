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

    private function createPrescriptionRequestsIfNeeded() {
        if (!$this->hasTable('prescription_requests')) {
            $sql = "
                CREATE TABLE IF NOT EXISTS `prescription_requests` (
                    `request_id` int(11) NOT NULL AUTO_INCREMENT,
                    `patient_id` int(11) NOT NULL,
                    `request_type` varchar(40) NOT NULL DEFAULT 'PRESCRIPTION',
                    `visit_type` varchar(40) NOT NULL DEFAULT 'ONSITE',
                    `prescription_file_path` varchar(255) DEFAULT NULL,
                    `notes` text DEFAULT NULL,
                    `symptoms` text DEFAULT NULL,
                    `preferred_date` date DEFAULT NULL,
                    `preferred_time` time DEFAULT NULL,
                    `home_collection` tinyint(1) NOT NULL DEFAULT 0,
                    `collection_address` varchar(255) DEFAULT NULL,
                    `status` varchar(40) NOT NULL DEFAULT 'Pending',
                    `decision_action` varchar(40) DEFAULT NULL,
                    `decision_by_user_id` int(11) DEFAULT NULL,
                    `decision_at` datetime DEFAULT NULL,
                    `linked_appointment_id` int(11) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`request_id`),
                    KEY `idx_pr_patient` (`patient_id`),
                    CONSTRAINT `fk_pr_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ";

            if ($this->db->query($sql)) {
                unset(self::$tableCache['prescription_requests']);
            } else {
                error_log('Failed to create prescription_requests table: ' . $this->db->error);
            }
            return;
        }

        // Fix UNSIGNED on request_id if created with wrong type — FK to prescription_request_tests requires signed INT
        $colResult = $this->db->query("SHOW COLUMNS FROM `prescription_requests` LIKE 'request_id'");
        $colRow = $colResult ? $colResult->fetch_assoc() : null;
        if ($colRow && stripos((string)($colRow['Type'] ?? ''), 'unsigned') !== false) {
            $this->db->query("ALTER TABLE `prescription_requests` MODIFY COLUMN `request_id` int(11) NOT NULL AUTO_INCREMENT");
        }
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

    public function getPrescriptionRequestsList($filters = [], $page = 1, $perPage = 7, $sortBy = 'created_at', $sortDir = 'desc') {
        $this->lastError = '';

        $page = max(1, intval($page));
        $perPage = max(1, min(50, intval($perPage)));
        $rows = $this->getFilteredPrescriptionRequestsDataset($filters, $sortBy, $sortDir);

        $offset = ($page - 1) * $perPage;
        return array_slice($rows, $offset, $perPage);
    }

    public function countPrescriptionRequests($filters = []) {
        $this->lastError = '';
        $rows = $this->getFilteredPrescriptionRequestsDataset($filters);
        return count($rows);
    }

    private function ensurePrescriptionRequestTestsTable() {
        if (!$this->hasTable('prescription_requests') || !$this->hasTable('tests')) {
            return false;
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS prescription_request_tests (
                request_test_id INT(11) NOT NULL AUTO_INCREMENT,
                request_id INT(11) NOT NULL,
                test_id INT(11) NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                quantity INT(11) NOT NULL DEFAULT 1,
                line_total DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (request_test_id),
                UNIQUE KEY uk_request_test (request_id, test_id),
                KEY idx_request_tests_request (request_id),
                KEY idx_request_tests_test (test_id),
                CONSTRAINT fk_request_tests_request FOREIGN KEY (request_id) REFERENCES prescription_requests(request_id) ON DELETE CASCADE,
                CONSTRAINT fk_request_tests_test FOREIGN KEY (test_id) REFERENCES tests(test_id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";

        if (!$this->db->query($sql)) {
            $this->lastError = 'Failed to ensure prescription_request_tests table: ' . $this->db->error;
            error_log($this->lastError);
            return false;
        }

        self::$tableCache['prescription_request_tests'] = true;
        return true;
    }

    public function getPrescriptionRequestManagePayload($requestId) {
        $requestId = intval($requestId);
        if ($requestId <= 0) {
            return null;
        }

        $this->createPrescriptionRequestsIfNeeded();
        $this->backfillPrescriptionRequestsFromLegacyServiceRequests();

        if (!$this->hasTable('prescription_requests')) {
            return $this->getLegacyServiceRequestManagePayload($requestId);
        }

        $requestTypeExpr = $this->hasTableColumn('prescription_requests', 'request_type')
            ? "COALESCE(sr.request_type, '')"
            : "''";
        $visitTypeExpr = $this->hasTableColumn('prescription_requests', 'visit_type')
            ? "COALESCE(sr.visit_type, '')"
            : "''";
        $symptomsExpr = $this->hasTableColumn('prescription_requests', 'symptoms')
            ? "COALESCE(sr.symptoms, '')"
            : "''";
        $notesExpr = $this->hasTableColumn('prescription_requests', 'notes')
            ? "COALESCE(sr.notes, '')"
            : "''";
        $preferredDateExpr = $this->hasTableColumn('prescription_requests', 'preferred_date')
            ? 'sr.preferred_date'
            : 'NULL';
        $preferredTimeExpr = $this->hasTableColumn('prescription_requests', 'preferred_time')
            ? 'sr.preferred_time'
            : 'NULL';
        $homeCollectionExpr = $this->hasTableColumn('prescription_requests', 'home_collection')
            ? 'COALESCE(sr.home_collection, 0)'
            : '0';
        $collectionAddressExpr = $this->hasTableColumn('prescription_requests', 'collection_address')
            ? "COALESCE(sr.collection_address, '')"
            : "''";

        $patientNameExpr = $this->buildPatientNameExpr('p');
        $contactCol = $this->resolveFirstExistingColumn('patients', ['contact_number', 'phone_number', 'phone', 'mobile']);
        $contactExpr = $contactCol !== null ? "COALESCE(p.{$contactCol}, '')" : "''";
        $addressCol = $this->resolveFirstExistingColumn('patients', ['address', 'residential_address']);
        $addressExpr = $addressCol !== null ? "COALESCE(p.{$addressCol}, '')" : "''";

        $sql = "
            SELECT
                sr.request_id,
                sr.patient_id,
                {$requestTypeExpr} AS request_type,
                {$visitTypeExpr} AS visit_type,
                COALESCE(sr.prescription_file_path, '') AS prescription_file_path,
                {$preferredDateExpr} AS preferred_date,
                {$preferredTimeExpr} AS preferred_time,
                {$homeCollectionExpr} AS home_collection,
                {$collectionAddressExpr} AS collection_address,
                {$notesExpr} AS notes,
                {$symptomsExpr} AS symptoms,
                COALESCE(sr.status, 'Pending') AS status,
                COALESCE(sr.decision_action, '') AS decision_action,
                sr.decision_by_user_id,
                sr.decision_at,
                sr.linked_appointment_id,
                sr.created_at,
                sr.updated_at,
                {$patientNameExpr} AS patient_name,
                {$contactExpr} AS contact_number,
                {$addressExpr} AS patient_address,
                COALESCE(p.email, '') AS email
            FROM prescription_requests sr
            LEFT JOIN patients p ON p.patient_id = sr.patient_id
            WHERE sr.request_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getPrescriptionRequestManagePayload: ' . $this->db->error;
            error_log($this->lastError);
            return null;
        }

        $stmt->bind_param('i', $requestId);
        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getPrescriptionRequestManagePayload: ' . $stmt->error;
            error_log($this->lastError);
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row) {
            return $this->getLegacyServiceRequestManagePayload($requestId);
        }

        $requestTypeLabel = $this->normalizePrescriptionRequestType($row['visit_type'] ?? null, $row['home_collection'] ?? null);
        $requestType = strtolower(str_replace(' ', '_', $requestTypeLabel));
        $hasPrescription = trim((string)($row['prescription_file_path'] ?? '')) !== '';

        if ($hasPrescription && $requestType === 'onsite') {
            $modalVariant = 'onsite_with_prescription';
        } elseif ($hasPrescription && $requestType === 'home_visit') {
            $modalVariant = 'home_visit_with_prescription';
        } else {
            $modalVariant = 'without_prescription';
        }

        $tests = $this->getPrescriptionRequestTests($requestId);
        $estimatedTotal = 0.0;
        foreach ($tests as $item) {
            $estimatedTotal += floatval($item['line_total'] ?? 0);
        }

        return [
            'request' => [
                'request_id' => intval($row['request_id'] ?? 0),
                'patient_id' => intval($row['patient_id'] ?? 0),
                'patient_name' => (string)($row['patient_name'] ?? ''),
                'contact_number' => (string)($row['contact_number'] ?? ''),
                'email' => (string)($row['email'] ?? ''),
                'patient_address' => (string)($row['patient_address'] ?? ''),
                'request_type' => $requestType,
                'request_type_label' => $requestTypeLabel,
                'status' => (string)($row['status'] ?? 'Pending'),
                'prescription_file_path' => (string)($row['prescription_file_path'] ?? ''),
                'prescription_available' => $hasPrescription ? 1 : 0,
                'home_collection' => intval($row['home_collection'] ?? 0),
                'collection_address' => (string)($row['collection_address'] ?? ''),
                'preferred_date' => (string)($row['preferred_date'] ?? ''),
                'preferred_time' => (string)($row['preferred_time'] ?? ''),
                'notes' => (string)($row['notes'] ?? ''),
                'symptoms' => (string)($row['symptoms'] ?? ''),
                'modal_variant' => $modalVariant,
            ],
            'tests' => $tests,
            'estimated_total' => round($estimatedTotal, 2),
        ];
    }

    private function getLegacyServiceRequestManagePayload($requestId) {
        $requestId = intval($requestId);
        if ($requestId <= 0 || !$this->hasTable('service_requests')) {
            return null;
        }

        $legacyIdCol = $this->resolveFirstExistingColumn('service_requests', ['request_id', 'service_request_id', 'id']);
        if ($legacyIdCol === null) {
            return null;
        }

        $requestTypeExpr = $this->hasTableColumn('service_requests', 'request_type')
            ? "COALESCE(sr.request_type, '')" : "''";
        $visitTypeExpr = $this->hasTableColumn('service_requests', 'visit_type')
            ? "COALESCE(sr.visit_type, '')" : "''";
        $symptomsExpr = $this->hasTableColumn('service_requests', 'symptoms')
            ? "COALESCE(sr.symptoms, '')" : "''";
        $notesExpr = $this->hasTableColumn('service_requests', 'notes')
            ? "COALESCE(sr.notes, '')" : "''";
        $preferredDateExpr = $this->hasTableColumn('service_requests', 'preferred_date')
            ? 'sr.preferred_date' : 'NULL';
        $preferredTimeExpr = $this->hasTableColumn('service_requests', 'preferred_time')
            ? 'sr.preferred_time' : 'NULL';
        $homeCollectionExpr = $this->hasTableColumn('service_requests', 'home_collection')
            ? 'COALESCE(sr.home_collection, 0)' : '0';
        $collectionAddressExpr = $this->hasTableColumn('service_requests', 'collection_address')
            ? "COALESCE(sr.collection_address, '')" : "''";

        $patientNameExpr = $this->buildPatientNameExpr('p');
        $contactCol = $this->resolveFirstExistingColumn('patients', ['contact_number', 'phone_number', 'phone', 'mobile']);
        $contactExpr = $contactCol !== null ? "COALESCE(p.{$contactCol}, '')" : "''";
        $addressCol = $this->resolveFirstExistingColumn('patients', ['address', 'residential_address']);
        $addressExpr = $addressCol !== null ? "COALESCE(p.{$addressCol}, '')" : "''";

        $sql = "
            SELECT
                sr.{$legacyIdCol} AS request_id,
                sr.patient_id,
                {$requestTypeExpr} AS request_type,
                {$visitTypeExpr} AS visit_type,
                COALESCE(sr.prescription_file_path, '') AS prescription_file_path,
                {$preferredDateExpr} AS preferred_date,
                {$preferredTimeExpr} AS preferred_time,
                {$homeCollectionExpr} AS home_collection,
                {$collectionAddressExpr} AS collection_address,
                {$notesExpr} AS notes,
                {$symptomsExpr} AS symptoms,
                COALESCE(sr.status, 'Pending') AS status,
                {$patientNameExpr} AS patient_name,
                {$contactExpr} AS contact_number,
                {$addressExpr} AS patient_address,
                COALESCE(p.email, '') AS email
            FROM service_requests sr
            LEFT JOIN patients p ON p.patient_id = sr.patient_id
            WHERE sr.{$legacyIdCol} = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
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

        if (!$row) {
            return null;
        }

        $requestTypeLabel = $this->normalizePrescriptionRequestType($row['visit_type'] ?? null, $row['home_collection'] ?? null);
        $requestType = strtolower(str_replace(' ', '_', $requestTypeLabel));
        $hasPrescription = trim((string)($row['prescription_file_path'] ?? '')) !== '';

        if ($hasPrescription && $requestType === 'onsite') {
            $modalVariant = 'onsite_with_prescription';
        } elseif ($hasPrescription && $requestType === 'home_visit') {
            $modalVariant = 'home_visit_with_prescription';
        } else {
            $modalVariant = 'without_prescription';
        }

        return [
            'request' => [
                'request_id' => intval($row['request_id'] ?? 0),
                'patient_id' => intval($row['patient_id'] ?? 0),
                'patient_name' => (string)($row['patient_name'] ?? ''),
                'contact_number' => (string)($row['contact_number'] ?? ''),
                'email' => (string)($row['email'] ?? ''),
                'patient_address' => (string)($row['patient_address'] ?? ''),
                'request_type' => $requestType,
                'request_type_label' => $requestTypeLabel,
                'status' => (string)($row['status'] ?? 'Pending'),
                'prescription_file_path' => (string)($row['prescription_file_path'] ?? ''),
                'prescription_available' => $hasPrescription ? 1 : 0,
                'home_collection' => intval($row['home_collection'] ?? 0),
                'collection_address' => (string)($row['collection_address'] ?? ''),
                'preferred_date' => (string)($row['preferred_date'] ?? ''),
                'preferred_time' => (string)($row['preferred_time'] ?? ''),
                'notes' => (string)($row['notes'] ?? ''),
                'symptoms' => (string)($row['symptoms'] ?? ''),
                'modal_variant' => $modalVariant,
            ],
            'tests' => [],
            'estimated_total' => 0.0,
        ];
    }

    private function getPrescriptionRequestTests($requestId) {
        $requestId = intval($requestId);
        if ($requestId <= 0 || !$this->hasTable('prescription_request_tests')) {
            return [];
        }

        $sql = "
            SELECT
                rt.test_id,
                COALESCE(t.test_name, CONCAT('Test #', rt.test_id)) AS test_name,
                COALESCE(rt.unit_price, 0) AS unit_price,
                COALESCE(rt.quantity, 1) AS quantity,
                COALESCE(rt.line_total, 0) AS line_total
            FROM prescription_request_tests rt
            LEFT JOIN tests t ON t.test_id = rt.test_id
            WHERE rt.request_id = ?
            ORDER BY t.test_name ASC, rt.test_id ASC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('i', $requestId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public function savePrescriptionRequestManagement($requestId, $payload = [], $actorUserId = 0) {
        $requestId = intval($requestId);
        $actorUserId = intval($actorUserId);

        if ($requestId <= 0) {
            $this->lastError = 'Invalid request ID.';
            return false;
        }

        $this->createPrescriptionRequestsIfNeeded();
        $this->backfillPrescriptionRequestsFromLegacyServiceRequests();

        if (!$this->hasTable('prescription_requests')) {
            $this->lastError = 'Table prescription_requests not found.';
            return false;
        }

        $basePayload = $this->getPrescriptionRequestManagePayload($requestId);
        if ($basePayload === null) {
            $this->lastError = 'Prescription request not found.';
            return false;
        }

        if (!$this->ensurePrescriptionRequestTestsTable()) {
            return false;
        }

        $testIds = $this->normalizeTestIds($payload['tests'] ?? []);
        if (empty($testIds)) {
            $this->lastError = 'At least one diagnostic test is required.';
            return false;
        }

        $requestData = $basePayload['request'];
        $requestType = strtolower((string)($requestData['request_type'] ?? 'onsite'));
        $inputAddress = trim((string)($payload['collection_address'] ?? ''));
        $effectiveAddress = $inputAddress !== '' ? $inputAddress : trim((string)($requestData['collection_address'] ?? ''));
        if ($requestType === 'home_visit' && $effectiveAddress === '') {
            $this->lastError = 'Collection address is required for home visit requests.';
            return false;
        }

        $preferredDate = trim((string)($payload['preferred_date'] ?? ''));
        $preferredTime = trim((string)($payload['preferred_time'] ?? ''));
        $eventNote = trim((string)($payload['note'] ?? ''));

        $placeholders = implode(',', array_fill(0, count($testIds), '?'));
        $testSql = "SELECT test_id, COALESCE(price, 0) AS price FROM tests WHERE test_id IN ({$placeholders})";
        $testStmt = $this->db->prepare($testSql);
        if ($testStmt === false) {
            $this->lastError = 'Prepare failed while loading tests: ' . $this->db->error;
            return false;
        }

        $types = str_repeat('i', count($testIds));
        $testStmt->bind_param($types, ...$testIds);
        if (!$testStmt->execute()) {
            $this->lastError = 'Execute failed while loading tests: ' . $testStmt->error;
            $testStmt->close();
            return false;
        }

        $result = $testStmt->get_result();
        $priceMap = [];
        while ($row = $result ? $result->fetch_assoc() : null) {
            $priceMap[intval($row['test_id'])] = floatval($row['price'] ?? 0);
        }
        $testStmt->close();

        if (count($priceMap) !== count($testIds)) {
            $this->lastError = 'One or more selected tests are invalid.';
            return false;
        }

        $this->db->begin_transaction();
        try {
            $deleteStmt = $this->db->prepare('DELETE FROM prescription_request_tests WHERE request_id = ?');
            if ($deleteStmt === false) {
                throw new Exception('Prepare failed while clearing request tests: ' . $this->db->error);
            }
            $deleteStmt->bind_param('i', $requestId);
            if (!$deleteStmt->execute()) {
                throw new Exception('Execute failed while clearing request tests: ' . $deleteStmt->error);
            }

            $insertStmt = $this->db->prepare(
                'INSERT INTO prescription_request_tests (request_id, test_id, unit_price, quantity, line_total) VALUES (?, ?, ?, 1, ?)'
            );
            if ($insertStmt === false) {
                throw new Exception('Prepare failed while inserting request tests: ' . $this->db->error);
            }

            foreach ($testIds as $testId) {
                $price = floatval($priceMap[intval($testId)] ?? 0);
                $lineTotal = $price;
                $insertStmt->bind_param('iidd', $requestId, $testId, $price, $lineTotal);
                if (!$insertStmt->execute()) {
                    throw new Exception('Execute failed while inserting request tests: ' . $insertStmt->error);
                }
            }

            $setParts = [
                "status = 'Communicated'",
                "decision_action = 'sent_to_patient'",
                'decision_at = NOW()',
                'updated_at = NOW()'
            ];
            $updateTypes = '';
            $updateParams = [];

            if ($this->hasTableColumn('prescription_requests', 'preferred_date') && $preferredDate !== '') {
                $setParts[] = 'preferred_date = ?';
                $updateTypes .= 's';
                $updateParams[] = $preferredDate;
            }
            if ($this->hasTableColumn('prescription_requests', 'preferred_time') && $preferredTime !== '') {
                $setParts[] = 'preferred_time = ?';
                $updateTypes .= 's';
                $updateParams[] = $preferredTime;
            }
            if ($this->hasTableColumn('prescription_requests', 'collection_address')) {
                $setParts[] = 'collection_address = ?';
                $updateTypes .= 's';
                $updateParams[] = $effectiveAddress;
            }

            if ($this->hasTableColumn('prescription_requests', 'decision_by_user_id')) {
                if ($actorUserId > 0) {
                    $setParts[] = 'decision_by_user_id = ?';
                    $updateTypes .= 'i';
                    $updateParams[] = $actorUserId;
                } else {
                    $setParts[] = 'decision_by_user_id = NULL';
                }
            }

            $updateSql = 'UPDATE prescription_requests SET ' . implode(', ', $setParts) . ' WHERE request_id = ?';
            $updateTypes .= 'i';
            $updateParams[] = $requestId;

            $updateStmt = $this->db->prepare($updateSql);
            if ($updateStmt === false) {
                throw new Exception('Prepare failed while updating request: ' . $this->db->error);
            }
            $updateStmt->bind_param($updateTypes, ...$updateParams);
            if (!$updateStmt->execute()) {
                throw new Exception('Execute failed while updating request: ' . $updateStmt->error);
            }

            $oldStatus = (string)($requestData['status'] ?? 'Pending');
            $this->addPrescriptionRequestEvent($requestId, 'communicated', $oldStatus, 'Communicated', $eventNote, $actorUserId);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = $e->getMessage();
            error_log($this->lastError);
            return false;
        }
    }

    private function getFilteredPrescriptionRequestsDataset($filters = [], $sortBy = 'created_at', $sortDir = 'desc') {
        $requestType = strtolower(trim((string)($filters['request_type'] ?? 'all')));
        if (!in_array($requestType, ['all', 'home_visit', 'onsite'], true)) {
            $requestType = 'all';
        }

        $search = strtolower(trim((string)($filters['search'] ?? '')));
        $fromDate = trim((string)($filters['from_date'] ?? ''));
        $toDate = trim((string)($filters['to_date'] ?? ''));

        $rows = $this->getPrescriptionRequests('all');

        foreach ($rows as &$row) {
            $requestTypeLabel = $this->normalizePrescriptionRequestType(
                $row['visit_type'] ?? null,
                $row['home_collection'] ?? null
            );
            $row['request_type_label'] = $requestTypeLabel;
            $row['request_type'] = strtolower(str_replace(' ', '_', $requestTypeLabel));
            $row['prescription_available'] = trim((string)($row['prescription_file_path'] ?? '')) !== '' ? 1 : 0;
            $row['filter_date'] = $this->resolvePrescriptionRequestDate($row);
        }
        unset($row);

        if ($requestType !== 'all') {
            $rows = array_values(array_filter($rows, function ($row) use ($requestType) {
                return (string)($row['request_type'] ?? 'onsite') === $requestType;
            }));
        }

        if ($search !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($search) {
                $requestId = strtolower((string)($row['request_id'] ?? ''));
                $patientName = strtolower((string)($row['patient_name'] ?? ''));
                $requestTypeLabel = strtolower((string)($row['request_type_label'] ?? ''));

                return strpos($requestId, $search) !== false
                    || strpos($patientName, $search) !== false
                    || strpos($requestTypeLabel, $search) !== false;
            }));
        }

        if ($fromDate !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($fromDate) {
                $rowDate = (string)($row['filter_date'] ?? '');
                return $rowDate !== '' && $rowDate >= $fromDate;
            }));
        }

        if ($toDate !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($toDate) {
                $rowDate = (string)($row['filter_date'] ?? '');
                return $rowDate !== '' && $rowDate <= $toDate;
            }));
        }

        $sortAllowlist = [
            'request_id' => 'request_id',
            'patient_name' => 'patient_name',
            'preferred_date' => 'preferred_date',
            'created_at' => 'created_at',
            'request_type' => 'request_type_label',
            'prescription_available' => 'prescription_available',
        ];

        $sortKey = $sortAllowlist[strtolower(trim((string)$sortBy))] ?? 'created_at';
        $direction = strtolower(trim((string)$sortDir)) === 'asc' ? 1 : -1;

        usort($rows, function ($a, $b) use ($sortKey, $direction) {
            $aValue = $a[$sortKey] ?? '';
            $bValue = $b[$sortKey] ?? '';

            if ($sortKey === 'request_id' || $sortKey === 'prescription_available') {
                $cmp = intval($aValue) <=> intval($bValue);
            } else {
                $cmp = strcmp(strtolower((string)$aValue), strtolower((string)$bValue));
            }

            return $cmp * $direction;
        });

        return $rows;
    }

    private function normalizePrescriptionRequestType($visitTypeValue, $homeCollectionValue = null) {
        $visitType = strtoupper(trim((string)$visitTypeValue));
        if ($visitType === 'HOME_VISIT') {
            return 'Home Visit';
        }

        if ($visitType === 'ONSITE') {
            return 'Onsite';
        }

        return $this->isTruthyFlag($homeCollectionValue) ? 'Home Visit' : 'Onsite';
    }

    private function resolvePrescriptionRequestDate($row) {
        $preferredDate = substr(trim((string)($row['preferred_date'] ?? '')), 0, 10);
        if ($preferredDate !== '') {
            return $preferredDate;
        }

        $createdDate = substr(trim((string)($row['created_at'] ?? '')), 0, 10);
        return $createdDate;
    }

    private function isTruthyFlag($value) {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return intval($value) !== 0;
        }

        $normalized = strtolower(trim((string)$value));
        return in_array($normalized, ['1', 'true', 'yes', 'on', 'y'], true);
    }

    private function getFilteredAppointmentsDataset($filters = [], $sortBy = 'appointment_date', $sortDir = 'desc') {
        $methodFilter = $this->normalizeAppointmentMethod($filters['method'] ?? 'all');
        if (!in_array($methodFilter, ['all', 'online', 'physical', 'call', 'home_visit'], true)) {
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
        } elseif ($methodFilter === 'home_visit') {
            $rows = array_values(array_filter($rows, function ($row) {
                return !empty($row['home_collection']) && (int)$row['home_collection'] === 1;
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

        if ($normalized === 'home_visit') {
            return 'home_visit';
        }

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
        $nameCol = $this->resolveFirstExistingColumn('tests', ['test_name', 'name']);
        $categoryCol = $this->resolveFirstExistingColumn('tests', ['category', 'department']);
        $codeCol = $this->resolveFirstExistingColumn('tests', ['test_code', 'code', 'testid']);
        $priceCol = $this->resolveFirstExistingColumn('tests', ['price', 'test_price', 'amount']);

        $nameExpr = $nameCol !== null ? "COALESCE(t.{$nameCol}, '')" : "''";
        $categoryExpr = $categoryCol !== null ? "COALESCE(t.{$categoryCol}, '')" : "''";
        $codeExpr = $codeCol !== null ? "COALESCE(t.{$codeCol}, '')" : "''";
        $priceExpr = $priceCol !== null ? "COALESCE(t.{$priceCol}, 0)" : '0';

        $sql = "
            SELECT
                t.test_id,
                {$nameExpr} AS test_name,
                {$categoryExpr} AS category,
                {$codeExpr} AS test_code,
                {$priceExpr} AS price
            FROM tests t
        ";

        $types = '';
        $params = [];
        $query = trim((string) $query);
        if ($query !== '') {
            $conditions = [
                'LOWER(' . $nameExpr . ') LIKE ?',
                'LOWER(' . $categoryExpr . ') LIKE ?',
                'CAST(t.test_id AS CHAR) LIKE ?'
            ];

            if ($codeCol !== null) {
                $conditions[] = 'LOWER(' . $codeExpr . ') LIKE ?';
            }

            $sql .= ' WHERE ' . implode(' OR ', $conditions);
            $needle = '%' . strtolower($query) . '%';
            $types = 'sss';
            $params[] = $needle;
            $params[] = $needle;
            $params[] = '%' . $query . '%';

            if ($codeCol !== null) {
                $types .= 's';
                $params[] = $needle;
            }
        }

        $sql .= ' ORDER BY ' . $nameExpr . ' ASC LIMIT ' . $safeLimit;

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
        $this->createPrescriptionRequestsIfNeeded();
        $this->backfillPrescriptionRequestsFromLegacyServiceRequests();

        if (!$this->hasTable('prescription_requests')) {
            return $this->getLegacyServiceRequests($status);
        }

        $status = trim((string) $status);
        $whereSql = '';
        $types = '';
        $params = [];

        if ($status !== '' && strtolower($status) !== 'all') {
            $whereSql = 'WHERE LOWER(COALESCE(sr.status, "pending")) = LOWER(?)';
            $types = 's';
            $params[] = $status;
        }

        $patientNameExpr = $this->buildPatientNameExpr('p');
        $contactCol = $this->resolveFirstExistingColumn('patients', ['contact_number', 'phone_number', 'phone', 'mobile']);
        $contactExpr = $contactCol !== null ? "COALESCE(p.{$contactCol}, '')" : "''";

        $requestTypeExpr = $this->hasTableColumn('prescription_requests', 'request_type')
            ? "COALESCE(sr.request_type, '')"
            : "''";
        $visitTypeExpr = $this->hasTableColumn('prescription_requests', 'visit_type')
            ? "COALESCE(sr.visit_type, '')"
            : "''";
        $notesExpr = $this->hasTableColumn('prescription_requests', 'notes')
            ? "COALESCE(sr.notes, '')"
            : "''";
        $symptomsExpr = $this->hasTableColumn('prescription_requests', 'symptoms')
            ? "COALESCE(sr.symptoms, '')"
            : "''";
        $preferredDateExpr = $this->hasTableColumn('prescription_requests', 'preferred_date')
            ? 'sr.preferred_date'
            : 'NULL';
        $preferredTimeExpr = $this->hasTableColumn('prescription_requests', 'preferred_time')
            ? 'sr.preferred_time'
            : 'NULL';
        $homeCollectionExpr = $this->hasTableColumn('prescription_requests', 'home_collection')
            ? 'COALESCE(sr.home_collection, 0)'
            : '0';
        $collectionAddressExpr = $this->hasTableColumn('prescription_requests', 'collection_address')
            ? "COALESCE(sr.collection_address, '')"
            : "''";
        $decisionActionExpr = $this->hasTableColumn('prescription_requests', 'decision_action')
            ? "COALESCE(sr.decision_action, '')"
            : "''";
        $decisionByExpr = $this->hasTableColumn('prescription_requests', 'decision_by_user_id')
            ? 'sr.decision_by_user_id'
            : 'NULL';
        $decisionAtExpr = $this->hasTableColumn('prescription_requests', 'decision_at')
            ? 'sr.decision_at'
            : 'NULL';
        $linkedAppointmentExpr = $this->hasTableColumn('prescription_requests', 'linked_appointment_id')
            ? 'sr.linked_appointment_id'
            : 'NULL';

        $sql = "
            SELECT
                sr.request_id,
                sr.patient_id,
                {$requestTypeExpr} AS request_type,
                {$visitTypeExpr} AS visit_type,
                sr.prescription_file_path,
                {$preferredDateExpr} AS preferred_date,
                {$preferredTimeExpr} AS preferred_time,
                {$homeCollectionExpr} AS home_collection,
                {$collectionAddressExpr} AS collection_address,
                {$notesExpr} AS notes,
                {$symptomsExpr} AS symptoms,
                sr.status,
                sr.created_at,
                sr.updated_at,
                {$decisionActionExpr} AS decision_action,
                {$decisionByExpr} AS decision_by_user_id,
                {$decisionAtExpr} AS decision_at,
                {$linkedAppointmentExpr} AS linked_appointment_id,
                COALESCE(u.username, '') AS decision_by_username,
                {$patientNameExpr} AS patient_name,
                {$contactExpr} AS contact_number,
                COALESCE(p.email, '') AS email
            FROM prescription_requests sr
            LEFT JOIN patients p ON p.patient_id = sr.patient_id
            LEFT JOIN users u ON u.user_id = sr.decision_by_user_id
            {$whereSql}
            ORDER BY sr.created_at DESC, sr.request_id DESC
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

    private function getLegacyServiceRequests($status = 'Pending') {
        if (!$this->hasTable('service_requests')) {
            return [];
        }

        $status = trim((string) $status);
        $whereSql = '';
        $types = '';
        $params = [];

        if ($status !== '' && strtolower($status) !== 'all' && $this->hasTableColumn('service_requests', 'status')) {
            $whereSql = 'WHERE LOWER(COALESCE(sr.status, "pending")) = LOWER(?)';
            $types = 's';
            $params[] = $status;
        }

        $patientNameExpr = $this->buildPatientNameExpr('p');
        $contactCol = $this->resolveFirstExistingColumn('patients', ['contact_number', 'phone_number', 'phone', 'mobile']);
        $contactExpr = $contactCol !== null ? "COALESCE(p.{$contactCol}, '')" : "''";

        $legacyIdCol = $this->resolveFirstExistingColumn('service_requests', ['request_id', 'service_request_id', 'id']);
        if ($legacyIdCol === null) {
            return [];
        }

        $requestTypeExpr = $this->hasTableColumn('service_requests', 'request_type')
            ? "COALESCE(sr.request_type, '')"
            : "''";
        $visitTypeExpr = $this->hasTableColumn('service_requests', 'visit_type')
            ? "COALESCE(sr.visit_type, '')"
            : "''";
        $prescriptionPathExpr = $this->hasTableColumn('service_requests', 'prescription_file_path')
            ? 'COALESCE(sr.prescription_file_path, "")'
            : "''";
        $notesExpr = $this->hasTableColumn('service_requests', 'notes')
            ? "COALESCE(sr.notes, '')"
            : "''";
        $symptomsExpr = $this->hasTableColumn('service_requests', 'symptoms')
            ? "COALESCE(sr.symptoms, '')"
            : "''";
        $preferredDateExpr = $this->hasTableColumn('service_requests', 'preferred_date')
            ? 'sr.preferred_date'
            : 'NULL';
        $preferredTimeExpr = $this->hasTableColumn('service_requests', 'preferred_time')
            ? 'sr.preferred_time'
            : 'NULL';
        $homeCollectionExpr = $this->hasTableColumn('service_requests', 'home_collection')
            ? 'COALESCE(sr.home_collection, 0)'
            : '0';
        $collectionAddressExpr = $this->hasTableColumn('service_requests', 'collection_address')
            ? "COALESCE(sr.collection_address, '')"
            : "''";
        $statusExpr = $this->hasTableColumn('service_requests', 'status')
            ? "COALESCE(sr.status, 'Pending')"
            : "'Pending'";
        $createdAtExpr = $this->hasTableColumn('service_requests', 'created_at')
            ? 'sr.created_at'
            : 'NOW()';
        $updatedAtExpr = $this->hasTableColumn('service_requests', 'updated_at')
            ? 'sr.updated_at'
            : 'NOW()';
        $decisionActionExpr = $this->hasTableColumn('service_requests', 'decision_action')
            ? "COALESCE(sr.decision_action, '')"
            : "''";
        $decisionByExpr = $this->hasTableColumn('service_requests', 'decision_by_user_id')
            ? 'sr.decision_by_user_id'
            : 'NULL';
        $decisionAtExpr = $this->hasTableColumn('service_requests', 'decision_at')
            ? 'sr.decision_at'
            : 'NULL';
        $linkedAppointmentExpr = $this->hasTableColumn('service_requests', 'linked_appointment_id')
            ? 'sr.linked_appointment_id'
            : 'NULL';

        $sql = "
            SELECT
                sr.{$legacyIdCol} AS request_id,
                sr.patient_id,
                {$requestTypeExpr} AS request_type,
                {$visitTypeExpr} AS visit_type,
                {$prescriptionPathExpr} AS prescription_file_path,
                {$preferredDateExpr} AS preferred_date,
                {$preferredTimeExpr} AS preferred_time,
                {$homeCollectionExpr} AS home_collection,
                {$collectionAddressExpr} AS collection_address,
                {$notesExpr} AS notes,
                {$symptomsExpr} AS symptoms,
                {$statusExpr} AS status,
                {$createdAtExpr} AS created_at,
                {$updatedAtExpr} AS updated_at,
                {$decisionActionExpr} AS decision_action,
                {$decisionByExpr} AS decision_by_user_id,
                {$decisionAtExpr} AS decision_at,
                {$linkedAppointmentExpr} AS linked_appointment_id,
                COALESCE(u.username, '') AS decision_by_username,
                {$patientNameExpr} AS patient_name,
                {$contactExpr} AS contact_number,
                COALESCE(p.email, '') AS email
            FROM service_requests sr
            LEFT JOIN patients p ON p.patient_id = sr.patient_id
            LEFT JOIN users u ON u.user_id = {$decisionByExpr}
            {$whereSql}
            ORDER BY {$createdAtExpr} DESC, sr.{$legacyIdCol} DESC
        ";

        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getLegacyServiceRequests: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $this->lastError = 'Execute failed in getLegacyServiceRequests: ' . $stmt->error;
            error_log($this->lastError);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    private function backfillPrescriptionRequestsFromLegacyServiceRequests() {
        if (!$this->hasTable('prescription_requests') || !$this->hasTable('service_requests')) {
            return;
        }

        if (!$this->hasTableColumn('service_requests', 'patient_id')) {
            return;
        }

        $insertCols = ['patient_id'];
        $selectExprs = ['sr.patient_id'];

        $hasServiceRequestType = $this->hasTableColumn('service_requests', 'request_type');
        $hasServiceVisitType = $this->hasTableColumn('service_requests', 'visit_type');
        $hasServicePrescriptionPath = $this->hasTableColumn('service_requests', 'prescription_file_path');
        $hasServiceNotes = $this->hasTableColumn('service_requests', 'notes');
        $hasServicePreferredDate = $this->hasTableColumn('service_requests', 'preferred_date');
        $hasServicePreferredTime = $this->hasTableColumn('service_requests', 'preferred_time');
        $hasServiceHomeCollection = $this->hasTableColumn('service_requests', 'home_collection');
        $hasServiceCollectionAddress = $this->hasTableColumn('service_requests', 'collection_address');
        $hasServiceStatus = $this->hasTableColumn('service_requests', 'status');
        $hasServiceDecisionAction = $this->hasTableColumn('service_requests', 'decision_action');
        $hasServiceDecisionBy = $this->hasTableColumn('service_requests', 'decision_by_user_id');
        $hasServiceDecisionAt = $this->hasTableColumn('service_requests', 'decision_at');
        $hasServiceLinkedAppointment = $this->hasTableColumn('service_requests', 'linked_appointment_id');
        $hasServiceCreatedAt = $this->hasTableColumn('service_requests', 'created_at');
        $hasServiceUpdatedAt = $this->hasTableColumn('service_requests', 'updated_at');

        if ($this->hasTableColumn('prescription_requests', 'request_type')) {
            $insertCols[] = 'request_type';
            $selectExprs[] = $hasServiceRequestType
                ? "COALESCE(NULLIF(sr.request_type, ''), 'prescription')"
                : "'prescription'";
        }

        if ($this->hasTableColumn('prescription_requests', 'visit_type')) {
            $insertCols[] = 'visit_type';
            $visitTypeExpr = "CASE
                WHEN " . ($hasServiceVisitType ? "UPPER(COALESCE(sr.visit_type, '')) IN ('HOME_VISIT', 'ONSITE')" : '0 = 1') . " THEN UPPER(sr.visit_type)
                WHEN " . ($hasServiceHomeCollection ? 'COALESCE(sr.home_collection, 0) = 1' : '0 = 1') . " THEN 'HOME_VISIT'
                ELSE 'ONSITE'
            END";
            $selectExprs[] = $visitTypeExpr;
        }

        if ($this->hasTableColumn('prescription_requests', 'prescription_file_path')) {
            $insertCols[] = 'prescription_file_path';
            $selectExprs[] = $hasServicePrescriptionPath ? 'sr.prescription_file_path' : "''";
        }

        if ($this->hasTableColumn('prescription_requests', 'notes')) {
            $insertCols[] = 'notes';
            $selectExprs[] = $hasServiceNotes ? 'sr.notes' : "''";
        }

        if ($this->hasTableColumn('prescription_requests', 'preferred_date')) {
            $insertCols[] = 'preferred_date';
            $selectExprs[] = $hasServicePreferredDate ? 'sr.preferred_date' : 'NULL';
        }

        if ($this->hasTableColumn('prescription_requests', 'preferred_time')) {
            $insertCols[] = 'preferred_time';
            $selectExprs[] = $hasServicePreferredTime ? 'sr.preferred_time' : 'NULL';
        }

        if ($this->hasTableColumn('prescription_requests', 'home_collection')) {
            $insertCols[] = 'home_collection';
            $selectExprs[] = $hasServiceHomeCollection ? 'COALESCE(sr.home_collection, 0)' : '0';
        }

        if ($this->hasTableColumn('prescription_requests', 'collection_address')) {
            $insertCols[] = 'collection_address';
            $selectExprs[] = $hasServiceCollectionAddress ? 'sr.collection_address' : "''";
        }

        if ($this->hasTableColumn('prescription_requests', 'status')) {
            $insertCols[] = 'status';
            $selectExprs[] = $hasServiceStatus ? "COALESCE(NULLIF(sr.status, ''), 'Pending')" : "'Pending'";
        }

        if ($this->hasTableColumn('prescription_requests', 'decision_action')) {
            $insertCols[] = 'decision_action';
            $selectExprs[] = $hasServiceDecisionAction ? 'sr.decision_action' : 'NULL';
        }

        if ($this->hasTableColumn('prescription_requests', 'decision_by_user_id')) {
            $insertCols[] = 'decision_by_user_id';
            $selectExprs[] = $hasServiceDecisionBy ? 'sr.decision_by_user_id' : 'NULL';
        }

        if ($this->hasTableColumn('prescription_requests', 'decision_at')) {
            $insertCols[] = 'decision_at';
            $selectExprs[] = $hasServiceDecisionAt ? 'sr.decision_at' : 'NULL';
        }

        if ($this->hasTableColumn('prescription_requests', 'linked_appointment_id')) {
            $insertCols[] = 'linked_appointment_id';
            $selectExprs[] = $hasServiceLinkedAppointment ? 'sr.linked_appointment_id' : 'NULL';
        }

        if ($this->hasTableColumn('prescription_requests', 'created_at')) {
            $insertCols[] = 'created_at';
            $selectExprs[] = $hasServiceCreatedAt ? 'COALESCE(sr.created_at, NOW())' : 'NOW()';
        }

        if ($this->hasTableColumn('prescription_requests', 'updated_at')) {
            $insertCols[] = 'updated_at';
            $selectExprs[] = $hasServiceUpdatedAt ? 'COALESCE(sr.updated_at, NOW())' : 'NOW()';
        }

        if (count($insertCols) < 2) {
            return;
        }

        $joinConditions = ['pr.patient_id = sr.patient_id'];

        if ($this->hasTableColumn('prescription_requests', 'created_at') && $hasServiceCreatedAt) {
            $joinConditions[] = "COALESCE(pr.created_at, '1970-01-01 00:00:00') = COALESCE(sr.created_at, '1970-01-01 00:00:00')";
        }
        if ($this->hasTableColumn('prescription_requests', 'prescription_file_path') && $hasServicePrescriptionPath) {
            $joinConditions[] = "COALESCE(pr.prescription_file_path, '') = COALESCE(sr.prescription_file_path, '')";
        }
        if ($this->hasTableColumn('prescription_requests', 'notes') && $hasServiceNotes) {
            $joinConditions[] = "COALESCE(pr.notes, '') = COALESCE(sr.notes, '')";
        }
        if ($this->hasTableColumn('prescription_requests', 'preferred_date') && $hasServicePreferredDate) {
            $joinConditions[] = "COALESCE(pr.preferred_date, '1970-01-01') = COALESCE(sr.preferred_date, '1970-01-01')";
        }
        if ($this->hasTableColumn('prescription_requests', 'preferred_time') && $hasServicePreferredTime) {
            $joinConditions[] = "COALESCE(pr.preferred_time, '00:00:00') = COALESCE(sr.preferred_time, '00:00:00')";
        }

        $sql = "
            INSERT INTO prescription_requests (" . implode(', ', $insertCols) . ")
            SELECT " . implode(', ', $selectExprs) . "
            FROM service_requests sr
            LEFT JOIN prescription_requests pr
              ON " . implode(' AND ', $joinConditions) . "
            WHERE pr.request_id IS NULL
        ";

        $this->db->query($sql);
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
            'bill_number'     => '',
            'bill_date'       => '',
            'subtotal'        => 0,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'total_amount'    => 0,
            'paid_amount'     => 0,
            'balance_due'     => 0,
            'status'          => '',
        ];

        if ($this->tableExists('bills') && $this->columnExists('bills', 'appointment_id')) {
            $sql = "
                SELECT
                    bill_number, bill_date,
                    subtotal, discount_amount, tax_amount,
                    total_amount, paid_amount, balance_due,
                    status
                FROM bills
                WHERE appointment_id = ?
                ORDER BY bill_id DESC
                LIMIT 1
            ";
            $stmt = $this->db->prepare($sql);
            if ($stmt !== false) {
                $stmt->bind_param('i', $appointmentId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $row = $result ? $result->fetch_assoc() : null;
                    if ($row) {
                        foreach (array_keys($summary) as $key) {
                            if (isset($row[$key])) {
                                $summary[$key] = $row[$key];
                            }
                        }
                        $stmt->close();
                        return $summary;
                    }
                }
                $stmt->close();
            }
        }

        // Fallback: sum test prices when no bill record exists
        $tests = $this->getAppointmentTestsWithStatus($appointmentId);
        $total = 0;
        foreach ($tests as $test) {
            $total += floatval($test['price'] ?? 0);
        }
        $summary['total_amount'] = $total;
        return $summary;
    }


}
?>