<?php

class AppointmentModel {
    private $db;
    private static $columnCache = [];
    private static $tableCache = [];
    private static $tableColumnCache = [];

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
                t.test_name,
                t.price AS test_price,
                COALESCE(a.home_collection, 0) AS home_collection,
                COALESCE(a.collection_address, '') AS collection_address,
                " . $statusField . " AS appointment_status,
                " . $testsSummaryField . " AS tests_summary,
                " . $totalPriceField . " AS total_price,
                " . $itemCountField . " AS item_count
            FROM appointment a
            LEFT JOIN tests t ON t.test_id = a.test_id
            WHERE LOWER(a.method) " . $operator . " LOWER(?)
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $method);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
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

        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
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
        $stmt->bind_param("i", $appointmentId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $payload = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $payload ?: null;
    }

    // Other appointment-related methods can be added here
}
?>