<?php

class HomeModel {
    private $db;
    private $lastError = '';
    private static $appointmentColumnCache = [];
    private static $tableCache = [];
    private static $tableColumnCache = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function getLastError() {
        return $this->lastError;
    }

    private function setLastError($message) {
        $this->lastError = $message;
        error_log($message);
    }

    private function hasAppointmentColumn($columnName) {
        if (array_key_exists($columnName, self::$appointmentColumnCache)) {
            return self::$appointmentColumnCache[$columnName];
        }

        $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
        $sql = "SHOW COLUMNS FROM appointment LIKE '" . $this->db->real_escape_string($safeColumn) . "'";
        $result = $this->db->query($sql);
        self::$appointmentColumnCache[$columnName] = ($result && $result->num_rows > 0);
        return self::$appointmentColumnCache[$columnName];
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

    private function buildTestNotDeletedClause($alias = 'tests') {
        $parts = [];

        if ($this->hasTableColumn('tests', 'deleted_at')) {
            $parts[] = "{$alias}.deleted_at IS NULL";
        }

        if ($this->hasTableColumn('tests', 'deleted_by')) {
            $parts[] = "{$alias}.deleted_by IS NULL";
        }

        if (empty($parts)) {
            return '1 = 1';
        }

        return implode(' AND ', $parts);
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

        if ($this->hasTable('prescription_requests')) {
            if (!$this->hasTableColumn('prescription_requests', 'home_collection')) {
                $alterStatements[] = "ALTER TABLE prescription_requests ADD COLUMN home_collection TINYINT(1) NOT NULL DEFAULT 0 AFTER preferred_time";
            }

            if (!$this->hasTableColumn('prescription_requests', 'collection_address')) {
                $alterStatements[] = "ALTER TABLE prescription_requests ADD COLUMN collection_address VARCHAR(255) DEFAULT NULL AFTER home_collection";
            }
        }

        foreach ($alterStatements as $sql) {
            if (!$this->db->query($sql)) {
                return false;
            }
        }

        return true;
    }

    public function getData() {
        return [
            'welcomeMessage' => 'Welcome to LabSync!',
            'features' => [
                'Book lab tests online',
                'Get results quickly',
                '24/7 customer support'
            ]
        ];
    }

    public function registerPatient($name, $email, $contact_number, $password, $role) {
        $stmt = $this->db->prepare("INSERT INTO users (email, password, role, contact_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $password, $role, $contact_number);

        $stmt2 = $this->db->prepare("INSERT INTO patients (patient_name, contact_number, email) VALUES (?, ?, ?)");
        $stmt2->bind_param("sss", $name, $contact_number, $email);

        return $stmt->execute() && $stmt2->execute();
    }

    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests WHERE " . $this->buildTestNotDeletedClause('tests'));
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createAppointment($data) {
        $this->ensureHomeCollectionColumns();

        $testIds = [];
        if (isset($data['test_ids']) && is_array($data['test_ids'])) {
            $testIds = $data['test_ids'];
        } elseif (isset($data['test_id'])) {
            $testIds = [$data['test_id']];
        }

        $cleanTestIds = $this->normalizeTestIds($testIds);
        if (empty($cleanTestIds)) {
            $this->setLastError('No valid tests were provided.');
            return false;
        }

        $primaryTestId = (int)$cleanTestIds[0];

        $homeCollection = !empty($data['home_collection']) ? 1 : 0;
        $collectionAddress = trim((string)($data['collection_address'] ?? ''));
        $collectionAddress = $collectionAddress !== '' ? $collectionAddress : null;

        $stmt = $this->db->prepare(
            "INSERT INTO appointment (patient_id, test_id, appointment_time, appointment_date, method, status, booking_channel, home_collection, collection_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            $this->setLastError("Prepare failed: " . $this->db->error);
            return false;
        }

        $status = $data['status'] ?? 'Pending';
        $channel = $data['booking_channel'] ?? 'online_self';
        $stmt->bind_param(
            "iisssssis",
            $data['patient_id'],
            $primaryTestId,
            $data['appointment_time'],
            $data['appointment_date'],
            $data['method'],
            $status,
            $channel,
            $homeCollection,
            $collectionAddress
        );

        $success = $stmt->execute();
        if (!$success) {
            $this->setLastError("Execute failed: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $newId = $stmt->insert_id;
        $stmt->close();

        if ($this->appointmentTestsTableExists()) {
            $lineStmt = $this->db->prepare("INSERT INTO appointment_tests (appointment_id, test_id, status) VALUES (?, ?, 'PENDING')");
            if (!$lineStmt) {
                $this->setLastError('Prepare failed (appointment_tests): ' . $this->db->error);
                return false;
            }

            foreach ($cleanTestIds as $testId) {
                $lineStmt->bind_param('ii', $newId, $testId);
                if (!$lineStmt->execute()) {
                    $lineStmt->close();
                    $this->setLastError('Execute failed (appointment_tests): ' . $lineStmt->error);
                    return false;
                }
            }

            $lineStmt->close();
        }

        return $newId;
    }

    public function createOnlineAppointmentWithItems($data, $testIds) {
        $cleanIds = array_values(array_unique(array_map('intval', $testIds)));
        $cleanIds = array_filter($cleanIds, function ($id) { return $id > 0; });

        if (count($cleanIds) === 0) {
            $this->setLastError('No valid tests selected.');
            return false;
        }

        $hasItemsTable = $this->hasTable('appointment_items');
        if (!$hasItemsTable && count($cleanIds) > 1) {
            $this->setLastError('appointment_items table is missing. Run database/laboratory.sql before multi-test booking.');
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $types = str_repeat('i', count($cleanIds));
        $priceStmt = $this->db->prepare("SELECT test_id, price FROM tests WHERE test_id IN ($placeholders) AND " . $this->buildTestNotDeletedClause('tests'));
        if (!$priceStmt) {
            $this->setLastError('Failed to prepare price lookup: ' . $this->db->error);
            return false;
        }

        $priceStmt->bind_param($types, ...$cleanIds);
        if (!$priceStmt->execute()) {
            $this->setLastError('Failed to fetch selected test prices: ' . $priceStmt->error);
            $priceStmt->close();
            return false;
        }

        $priceResult = $priceStmt->get_result();
        $priceMap = [];
        while ($row = $priceResult->fetch_assoc()) {
            $priceMap[(int)$row['test_id']] = (float)$row['price'];
        }
        $priceStmt->close();

        if (count($priceMap) !== count($cleanIds)) {
            $this->setLastError('One or more selected tests are invalid.');
            return false;
        }

        $this->db->begin_transaction();
        try {
            if (!$this->isOnlineSlotCapacityAvailableForUpdate((string)$data['appointment_date'], (string)$data['appointment_time'])) {
                throw new Exception($this->getLastError() ?: 'Selected slot is no longer available.');
            }

            $data['test_id'] = (int)$cleanIds[0];
            $appointmentId = $this->createAppointment($data);
            if (!$appointmentId) {
                throw new Exception($this->getLastError() ?: 'Failed to create appointment.');
            }

            if ($hasItemsTable) {
                $itemStmt = $this->db->prepare(
                    "INSERT INTO appointment_items (appointment_id, test_id, unit_price, quantity, line_total) VALUES (?, ?, ?, 1, ?)"
                );
                if (!$itemStmt) {
                    throw new Exception('Failed to prepare appointment item insert: ' . $this->db->error);
                }

                foreach ($cleanIds as $testId) {
                    $unitPrice = $priceMap[$testId];
                    $lineTotal = $unitPrice;
                    $itemStmt->bind_param("iidd", $appointmentId, $testId, $unitPrice, $lineTotal);
                    if (!$itemStmt->execute()) {
                        $itemStmt->close();
                        throw new Exception('Failed to save appointment item: ' . $itemStmt->error);
                    }
                }
                $itemStmt->close();
            }

            $this->db->commit();
            return $appointmentId;
        } catch (Exception $e) {
            $this->db->rollback();
            $this->setLastError($e->getMessage());
            return false;
        }
    }

    public function isOnlineSlotCapacityAvailable(string $date, string $time): bool {
        if (!$this->hasTable('online_booking_slots')) {
            $this->setLastError('Online slot configuration is unavailable.');
            return false;
        }

        if (!$this->isFutureOnlineSlotTime($date, $time)) {
            return false;
        }

        $dayGroup = $this->resolveDayGroupFromDate($date);
        if ($dayGroup === null) {
            $this->setLastError('Invalid appointment date.');
            return false;
        }

        $slotStmt = $this->db->prepare(
            "SELECT id, max_patients, is_active
             FROM online_booking_slots
             WHERE day_group = ? AND start_time = ?
             LIMIT 1"
        );
        if (!$slotStmt) {
            $this->setLastError('Failed to validate slot availability.');
            return false;
        }

        $slotStmt->bind_param('ss', $dayGroup, $time);
        if (!$slotStmt->execute()) {
            $this->setLastError('Failed to validate slot availability.');
            $slotStmt->close();
            return false;
        }

        $slotResult = $slotStmt->get_result();
        $slotRow = $slotResult ? $slotResult->fetch_assoc() : null;
        $slotStmt->close();

        if (!$slotRow) {
            $this->setLastError('Selected slot is not available for this date.');
            return false;
        }

        if ((int)$slotRow['is_active'] !== 1) {
            $this->setLastError('Selected slot is currently inactive.');
            return false;
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS booked_count
             FROM appointment
             WHERE appointment_date = ?
               AND appointment_time = ?
               AND LOWER(COALESCE(method, '')) = 'online'
               AND LOWER(COALESCE(status, 'pending')) <> 'cancelled'"
        );
        if (!$countStmt) {
            $this->setLastError('Failed to verify slot capacity.');
            return false;
        }

        $countStmt->bind_param('ss', $date, $time);
        if (!$countStmt->execute()) {
            $this->setLastError('Failed to verify slot capacity.');
            $countStmt->close();
            return false;
        }

        $countResult = $countStmt->get_result();
        $countRow = $countResult ? $countResult->fetch_assoc() : ['booked_count' => 0];
        $countStmt->close();

        $bookedCount = (int)($countRow['booked_count'] ?? 0);
        $maxPatients = (int)$slotRow['max_patients'];

        if ($bookedCount >= $maxPatients) {
            $this->setLastError('This slot has reached its booking limit for the selected day.');
            return false;
        }

        return true;
    }

    private function isOnlineSlotCapacityAvailableForUpdate(string $date, string $time): bool {
        if (!$this->hasTable('online_booking_slots')) {
            $this->setLastError('Online slot configuration is unavailable.');
            return false;
        }

        if (!$this->isFutureOnlineSlotTime($date, $time)) {
            return false;
        }

        $dayGroup = $this->resolveDayGroupFromDate($date);
        if ($dayGroup === null) {
            $this->setLastError('Invalid appointment date.');
            return false;
        }

        $slotStmt = $this->db->prepare(
            "SELECT id, max_patients, is_active
             FROM online_booking_slots
             WHERE day_group = ? AND start_time = ?
             LIMIT 1
             FOR UPDATE"
        );
        if (!$slotStmt) {
            $this->setLastError('Failed to validate slot availability.');
            return false;
        }

        $slotStmt->bind_param('ss', $dayGroup, $time);
        if (!$slotStmt->execute()) {
            $this->setLastError('Failed to validate slot availability.');
            $slotStmt->close();
            return false;
        }

        $slotResult = $slotStmt->get_result();
        $slotRow = $slotResult ? $slotResult->fetch_assoc() : null;
        $slotStmt->close();

        if (!$slotRow) {
            $this->setLastError('Selected slot is not available for this date.');
            return false;
        }

        if ((int)$slotRow['is_active'] !== 1) {
            $this->setLastError('Selected slot is currently inactive.');
            return false;
        }

        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) AS booked_count
             FROM appointment
             WHERE appointment_date = ?
               AND appointment_time = ?
               AND LOWER(COALESCE(method, '')) = 'online'
               AND LOWER(COALESCE(status, 'pending')) <> 'cancelled'
             FOR UPDATE"
        );
        if (!$countStmt) {
            $this->setLastError('Failed to verify slot capacity.');
            return false;
        }

        $countStmt->bind_param('ss', $date, $time);
        if (!$countStmt->execute()) {
            $this->setLastError('Failed to verify slot capacity.');
            $countStmt->close();
            return false;
        }

        $countResult = $countStmt->get_result();
        $countRow = $countResult ? $countResult->fetch_assoc() : ['booked_count' => 0];
        $countStmt->close();

        $bookedCount = (int)($countRow['booked_count'] ?? 0);
        $maxPatients = (int)$slotRow['max_patients'];

        if ($bookedCount >= $maxPatients) {
            $this->setLastError('This slot has reached its booking limit for the selected day.');
            return false;
        }

        return true;
    }

    private function resolveDayGroupFromDate(string $date): ?string {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        $day = (int)date('N', $timestamp);
        if ($day >= 1 && $day <= 5) {
            return 'mon_fri';
        }

        if ($day === 6) {
            return 'sat';
        }

        return 'sun';
    }

    private function isFutureOnlineSlotTime(string $date, string $time): bool {
        $date = trim($date);
        $time = trim($time);

        $slotTimestamp = strtotime($date . ' ' . $time);
        if ($slotTimestamp === false) {
            $this->setLastError('Invalid appointment date or time.');
            return false;
        }

        $today = date('Y-m-d');
        if ($date !== $today) {
            return true;
        }

        if ($slotTimestamp <= time()) {
            $this->setLastError('Please select a future time slot for today.');
            return false;
        }

        return true;
    }

    public function hasTimeSlotConflict($appointmentDate, $appointmentTime) {
        $hasStatus = $this->hasAppointmentColumn('status');

        if ($hasStatus) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM appointment WHERE appointment_date = ? AND appointment_time = ? AND LOWER(COALESCE(status, 'pending')) <> 'cancelled'");
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM appointment WHERE appointment_date = ? AND appointment_time = ?");
        }

        if (!$stmt) {
            $this->setLastError('Failed to check slot conflict: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param("ss", $appointmentDate, $appointmentTime);
        if (!$stmt->execute()) {
            $this->setLastError('Failed to check slot conflict: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        $stmt->close();

        return ((int)($row['total'] ?? 0)) > 0;
    }

    public function getPatientIdByUserId($userId) {
        $stmt = $this->db->prepare(
            "SELECT p.patient_id FROM patients p JOIN users u ON p.email = u.email WHERE u.user_id = ? LIMIT 1"
        );

        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();
        $stmt->close();

        return $patient ? $patient['patient_id'] : false;
    }

    public function getPatientContactByUserId($userId) {
        $stmt = $this->db->prepare(
            "SELECT p.patient_id, p.patient_name, p.email, p.contact_number FROM patients p JOIN users u ON p.email = u.email WHERE u.user_id = ? LIMIT 1"
        );
        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return null;
        }

        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $contact = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $contact ?: null;
    }

    public function getPrescriptionRequestsByPatient($patientId, $limit = 10) {
        if (!$this->ensurePrescriptionRequestsTableReady()) {
            return [];
        }

        $safeLimit = max(1, min(50, (int)$limit));

        $hasVisitType = $this->hasTableColumn('prescription_requests', 'visit_type');
        $hasHomeCollection = $this->hasTableColumn('prescription_requests', 'home_collection');
        $hasCollectionAddress = $this->hasTableColumn('prescription_requests', 'collection_address');
        $hasDecisionAction = $this->hasTableColumn('prescription_requests', 'decision_action');
        $hasLinkedAppointment = $this->hasTableColumn('prescription_requests', 'linked_appointment_id');
        $hasDecisionAt = $this->hasTableColumn('prescription_requests', 'decision_at');

        $homeCollectionSelect = '0 AS home_collection';
        if ($hasVisitType) {
            $homeCollectionSelect = "CASE WHEN UPPER(COALESCE(visit_type, 'ONSITE')) = 'HOME_VISIT' THEN 1 ELSE 0 END AS home_collection";
        } elseif ($hasHomeCollection) {
            $homeCollectionSelect = 'COALESCE(home_collection, 0) AS home_collection';
        }

        $collectionAddressSelect = $hasCollectionAddress
            ? 'collection_address'
            : 'NULL AS collection_address';
        $decisionActionSelect = $hasDecisionAction
            ? 'decision_action'
            : 'NULL AS decision_action';
        $linkedAppointmentSelect = $hasLinkedAppointment
            ? 'linked_appointment_id'
            : 'NULL AS linked_appointment_id';
        $decisionAtSelect = $hasDecisionAt
            ? 'decision_at'
            : 'NULL AS decision_at';

        $requestTypeSelect = $this->hasTableColumn('prescription_requests', 'request_type')
            ? 'request_type' : "'' AS request_type";
        $visitTypeSelect = $this->hasTableColumn('prescription_requests', 'visit_type')
            ? 'visit_type' : "'' AS visit_type";

        $sql = "
            SELECT
                request_id,
                prescription_file_path,
                notes,
                preferred_date,
                preferred_time,
                " . $homeCollectionSelect . ",
                " . $collectionAddressSelect . ",
                " . $requestTypeSelect . ",
                " . $visitTypeSelect . ",
                status,
                " . $decisionActionSelect . ",
                " . $linkedAppointmentSelect . ",
                " . $decisionAtSelect . ",
                created_at,
                updated_at
            FROM prescription_requests
            WHERE patient_id = ?
            ORDER BY created_at DESC
            LIMIT $safeLimit
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $patientId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    public function getTestsForRequests(array $requestIds) {
        if (empty($requestIds) || !$this->hasTable('prescription_request_tests')) {
            return [];
        }

        $ids = array_map('intval', $requestIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $sql = "
            SELECT rt.request_id, rt.test_id,
                COALESCE(t.test_name, CONCAT('Test #', rt.test_id)) AS test_name,
                COALESCE(rt.unit_price, COALESCE(t.price, 0)) AS unit_price,
                COALESCE(rt.quantity, 1) AS quantity,
                COALESCE(rt.line_total, 0) AS line_total
            FROM prescription_request_tests rt
            LEFT JOIN tests t ON t.test_id = rt.test_id
            WHERE rt.request_id IN ({$placeholders})
            ORDER BY rt.request_id, t.test_name
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param($types, ...$ids);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int)$row['request_id']][] = $row;
        }

        return $grouped;
    }

    public function getTestIdsForRequest($requestId) {
        $requestId = intval($requestId);
        if ($requestId <= 0 || !$this->hasTable('prescription_request_tests')) {
            return [];
        }

        $sql = "SELECT test_id FROM prescription_request_tests WHERE request_id = ? ORDER BY test_id";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $requestId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $ids = [];
        while ($row = ($result ? $result->fetch_assoc() : null)) {
            $ids[] = (int)$row['test_id'];
        }
        $stmt->close();

        return $ids;
    }

    public function linkAppointmentToRequest($requestId, $appointmentId) {
        $requestId = intval($requestId);
        $appointmentId = intval($appointmentId);
        if ($requestId <= 0 || $appointmentId <= 0 || !$this->hasTable('prescription_requests')) {
            return false;
        }

        $sql = "UPDATE prescription_requests SET linked_appointment_id = ?, status = 'Booked', updated_at = NOW() WHERE request_id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $appointmentId, $requestId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
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

    private function ensurePrescriptionRequestsTableReady() {
        $this->createPrescriptionRequestsIfNeeded();

        if (!$this->hasTable('prescription_requests')) {
            $this->setLastError('Table prescription_requests does not exist.');
            return false;
        }

        $requiredColumns = [
            'request_id',
            'patient_id',
            'prescription_file_path',
            'notes',
            'preferred_date',
            'preferred_time',
            'status',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredColumns as $columnName) {
            if (!$this->hasTableColumn('prescription_requests', $columnName)) {
                $this->setLastError('Column prescription_requests.' . $columnName . ' is missing.');
                return false;
            }
        }

        return true;
    }

    public function getAppointmentEmailPayload($appointmentId) {
        $hasStatus = $this->hasAppointmentColumn('status');
        $hasChannel = $this->hasAppointmentColumn('booking_channel');
        $hasHomeCollection = $this->hasAppointmentColumn('home_collection');
        $hasCollectionAddress = $this->hasAppointmentColumn('collection_address');
        $hasItems = $this->hasTable('appointment_items');

        $statusField = $hasStatus ? "COALESCE(NULLIF(a.status, ''), 'Pending')" : "'Pending'";
        $channelField = $hasChannel ? "COALESCE(NULLIF(a.booking_channel, ''), 'online_self')" : "'online_self'";
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
        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return null;
        }

        $stmt->bind_param("i", $appointmentId);
        if (!$stmt->execute()) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $payload = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $payload ?: null;
    }

    public function getAllAppointments($patientId) {
        $this->ensureHomeCollectionColumns();

        $statusField = $this->hasAppointmentColumn('status')
            ? "COALESCE(NULLIF(a.status, ''), 'Pending')"
            : "'Pending'";

        $summaryField = $this->buildAppointmentTestsSummaryExpr('a');
        $totalField = $this->buildAppointmentTotalExpr('a');

        $query = "
            SELECT
                a.*,
                " . $summaryField . " AS tests_summary,
                t.test_name,
                t.price AS test_price,
                COALESCE(a.home_collection, 0) AS home_collection,
                COALESCE(a.collection_address, '') AS collection_address,
                " . $totalField . " AS total_price,
                " . $statusField . " AS appointment_status
            FROM appointment a
            LEFT JOIN tests t ON t.test_id = a.test_id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ";

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return [];
        }

        $stmt->bind_param("i", $patientId);
        if (!$stmt->execute()) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $appointments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $appointments;
    }

    public function getPatientAppointmentDetailsPayload($appointmentId, $patientId) {
        $appointmentId = intval($appointmentId);
        $patientId = intval($patientId);

        if ($appointmentId <= 0 || $patientId <= 0) {
            return null;
        }

        $statusField = $this->hasAppointmentColumn('status')
            ? "COALESCE(NULLIF(a.status, ''), 'Pending')"
            : "'Pending'";

        $summaryField = $this->buildAppointmentTestsSummaryExpr('a');
        $totalField = $this->buildAppointmentTotalExpr('a');
        $patientNameField = $this->hasTableColumn('patients', 'patient_name')
            ? "COALESCE(p.patient_name, '')"
            : "''";
        $patientContactField = $this->hasTableColumn('patients', 'contact_number')
            ? "COALESCE(p.contact_number, '')"
            : "''";
        $patientGenderField = $this->hasTableColumn('patients', 'gender')
            ? "COALESCE(p.gender, '')"
            : "''";
        $patientDobField = $this->hasTableColumn('patients', 'date_of_birth')
            ? "p.date_of_birth"
            : "NULL";

        $sql = "
            SELECT
                a.*,
                " . $statusField . " AS appointment_status,
                COALESCE(a.home_collection, 0) AS home_collection,
                COALESCE(a.collection_address, '') AS collection_address,
                " . $summaryField . " AS tests_summary,
                " . $totalField . " AS total_price,
                " . $patientNameField . " AS patient_name,
                " . $patientContactField . " AS contact_number,
                " . $patientGenderField . " AS gender,
                " . $patientDobField . " AS date_of_birth
            FROM appointment a
            LEFT JOIN patients p ON p.patient_id = a.patient_id
            LEFT JOIN tests t ON t.test_id = a.test_id
            WHERE a.appointment_id = ? AND a.patient_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return null;
        }

        $stmt->bind_param('ii', $appointmentId, $patientId);
        if (!$stmt->execute()) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $appointment = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$appointment) {
            return null;
        }

        return [
            'appointment' => $appointment,
            'tests' => $this->getAppointmentTestsWithStatus($appointmentId),
            'billing' => [
                'total_amount' => isset($appointment['total_price']) ? (float)$appointment['total_price'] : 0.0,
                'status' => 'PENDING',
            ],
        ];
    }

    private function buildAppointmentTestsSummaryExpr($appointmentAlias) {
        $hasTestsTable = $this->hasTable('tests');

        if ($hasTestsTable && $this->hasTable('appointment_tests')) {
            return "COALESCE((
                SELECT GROUP_CONCAT(DISTINCT COALESCE(t1.test_name, CONCAT('Test #', at1.test_id)) ORDER BY COALESCE(t1.test_name, '') SEPARATOR ', ')
                FROM appointment_tests at1
                LEFT JOIN tests t1 ON t1.test_id = at1.test_id
                WHERE at1.appointment_id = {$appointmentAlias}.appointment_id
            ), '')";
        }

        if ($hasTestsTable && $this->hasTable('appointment_items')) {
            return "COALESCE((
                SELECT GROUP_CONCAT(DISTINCT COALESCE(ti.test_name, CONCAT('Test #', ai.test_id)) ORDER BY COALESCE(ti.test_name, '') SEPARATOR ', ')
                FROM appointment_items ai
                LEFT JOIN tests ti ON ti.test_id = ai.test_id
                WHERE ai.appointment_id = {$appointmentAlias}.appointment_id
            ), '')";
        }

        if ($hasTestsTable) {
            return "COALESCE(t.test_name, '')";
        }

        return "''";
    }

    private function buildAppointmentTotalExpr($appointmentAlias) {
        $hasTestsTable = $this->hasTable('tests');

        if ($this->hasTable('appointment_items')) {
            return "COALESCE((SELECT SUM(ai.line_total) FROM appointment_items ai WHERE ai.appointment_id = {$appointmentAlias}.appointment_id), COALESCE(t.price, 0), 0)";
        }

        if ($hasTestsTable && $this->hasTable('appointment_tests')) {
            return "COALESCE((
                SELECT SUM(COALESCE(t2.price, 0))
                FROM appointment_tests at2
                LEFT JOIN tests t2 ON t2.test_id = at2.test_id
                WHERE at2.appointment_id = {$appointmentAlias}.appointment_id
            ), COALESCE(t.price, 0), 0)";
        }

        return "COALESCE(t.price, 0)";
    }

    private function getAppointmentTestsWithStatus($appointmentId) {
        $appointmentId = intval($appointmentId);
        if ($appointmentId <= 0) {
            return [];
        }

        $hasTestsTable = $this->hasTable('tests');

        if ($this->hasTable('appointment_tests')) {
            $categoryExpr = $hasTestsTable && $this->hasTableColumn('tests', 'category')
                ? 'COALESCE(t.category, \"\")'
                : ($hasTestsTable && $this->hasTableColumn('tests', 'department')
                    ? 'COALESCE(t.department, \"\")'
                    : '\"\"');

            $sql = "
                SELECT
                    at.test_id,
                    " . ($hasTestsTable ? "COALESCE(t.test_name, '')" : "''") . " AS test_name,
                    {$categoryExpr} AS category,
                    UPPER(COALESCE(at.status, 'PENDING')) AS status
                FROM appointment_tests at
                " . ($hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = at.test_id' : '') . "
                WHERE at.appointment_id = ?
                ORDER BY test_name ASC, at.test_id ASC
            ";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                $this->setLastError('Prepare failed: ' . $this->db->error);
            } else {
                $stmt->bind_param('i', $appointmentId);
                if (!$stmt->execute()) {
                    $this->setLastError('Execute failed: ' . $stmt->error);
                    $stmt->close();
                } else {
                    $result = $stmt->get_result();
                    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    $stmt->close();
                    if (!empty($rows)) {
                        return $rows;
                    }
                }
            }
        }

        if ($this->hasTable('appointment_items')) {
            $categoryExpr = $hasTestsTable && $this->hasTableColumn('tests', 'category')
                ? 'COALESCE(t.category, \"\")'
                : ($hasTestsTable && $this->hasTableColumn('tests', 'department')
                    ? 'COALESCE(t.department, \"\")'
                    : '\"\"');

            $sql = "
                SELECT
                    ai.test_id,
                    " . ($hasTestsTable ? "COALESCE(t.test_name, '')" : "''") . " AS test_name,
                    {$categoryExpr} AS category,
                    'PENDING' AS status
                FROM appointment_items ai
                " . ($hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = ai.test_id' : '') . "
                WHERE ai.appointment_id = ?
                ORDER BY test_name ASC, ai.test_id ASC
            ";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                $this->setLastError('Prepare failed: ' . $this->db->error);
            } else {
                $stmt->bind_param('i', $appointmentId);
                if (!$stmt->execute()) {
                    $this->setLastError('Execute failed: ' . $stmt->error);
                    $stmt->close();
                } else {
                    $result = $stmt->get_result();
                    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    $stmt->close();
                    if (!empty($rows)) {
                        return $rows;
                    }
                }
            }
        }

        if (
            $this->hasTable('prescription_requests') &&
            $this->hasTableColumn('prescription_requests', 'linked_appointment_id') &&
            $this->hasTable('prescription_request_tests')
        ) {
            $categoryExpr = $hasTestsTable && $this->hasTableColumn('tests', 'category')
                ? 'COALESCE(t.category, "")'
                : ($hasTestsTable && $this->hasTableColumn('tests', 'department')
                    ? 'COALESCE(t.department, "")'
                    : '""');

            $sql = "
                SELECT
                    rt.test_id,
                    " . ($hasTestsTable ? "COALESCE(t.test_name, CONCAT('Test #', rt.test_id))" : "CONCAT('Test #', rt.test_id)") . " AS test_name,
                    {$categoryExpr} AS category,
                    'PENDING' AS status
                FROM prescription_request_tests rt
                INNER JOIN prescription_requests pr ON pr.request_id = rt.request_id
                " . ($hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = rt.test_id' : '') . "
                WHERE pr.linked_appointment_id = ?
                ORDER BY test_name ASC, rt.test_id ASC
            ";

            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('i', $appointmentId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    $stmt->close();
                    if (!empty($rows)) {
                        return $rows;
                    }
                } else {
                    $stmt->close();
                }
            }
        }

        $sql = "
            SELECT
                a.test_id,
                " . ($hasTestsTable ? "COALESCE(t.test_name, '')" : "''") . " AS test_name,
                " . ($hasTestsTable && $this->hasTableColumn('tests', 'category')
                    ? 'COALESCE(t.category, \"\")'
                    : ($hasTestsTable && $this->hasTableColumn('tests', 'department')
                        ? 'COALESCE(t.department, \"\")'
                        : '\"\"')) . " AS category,
                'PENDING' AS status
            FROM appointment a
            " . ($hasTestsTable ? 'LEFT JOIN tests t ON t.test_id = a.test_id' : '') . "
            WHERE a.appointment_id = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return [];
        }

        $stmt->bind_param('i', $appointmentId);
        if (!$stmt->execute()) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row || intval($row['test_id'] ?? 0) <= 0) {
            return [];
        }

        return [$row];
    }

    public function updateAppointment($appointment_id, $appointment_time, $appointment_date, $patientId = null, $homeCollection = null, $collectionAddress = null) {
        $appointment_id = intval($appointment_id);
        if ($appointment_id <= 0) {
            $this->setLastError('Invalid appointment ID.');
            return false;
        }

        $appointmentDate = trim((string)$appointment_date);
        $appointmentTime = trim((string)$appointment_time);

        $methodQuery = "SELECT LOWER(COALESCE(method, '')) AS booking_method FROM appointment WHERE appointment_id = ?";
        $methodTypes = 'i';
        $methodParams = [$appointment_id];
        if ($patientId !== null) {
            $methodQuery .= ' AND patient_id = ?';
            $methodTypes .= 'i';
            $methodParams[] = intval($patientId);
        }
        $methodQuery .= ' LIMIT 1';

        $methodStmt = $this->db->prepare($methodQuery);
        if (!$methodStmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }
        $methodStmt->bind_param($methodTypes, ...$methodParams);
        if (!$methodStmt->execute()) {
            $this->setLastError('Execute failed: ' . $methodStmt->error);
            $methodStmt->close();
            return false;
        }

        $methodResult = $methodStmt->get_result();
        $methodRow = $methodResult ? $methodResult->fetch_assoc() : null;
        $methodStmt->close();

        if (!$methodRow) {
            $this->setLastError('Appointment not found.');
            return false;
        }

        if (($methodRow['booking_method'] ?? '') === 'online') {
            if (!$this->isOnlineSlotCapacityAvailableForUpdate($appointmentDate, $appointmentTime)) {
                return false;
            }
        }

        $query = "UPDATE appointment SET appointment_time = ?, appointment_date = ?";
        $types = "ss";
        $params = [$appointmentTime, $appointmentDate];

        if ($homeCollection !== null) {
            $query .= ", home_collection = ?";
            $types .= "i";
            $params[] = (int)$homeCollection;
        }

        if ($collectionAddress !== null) {
            $query .= ", collection_address = ?";
            $types .= "s";
            $params[] = $collectionAddress !== '' ? $collectionAddress : null;
        }

        $query .= " WHERE appointment_id = ?";
        $types .= "i";
        $params[] = $appointment_id;

        if ($patientId !== null) {
            $query .= " AND patient_id = ?";
            $types .= "i";
            $params[] = $patientId;
        }

        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param($types, ...$params);

        $success = $stmt->execute();
        if (!$success) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    }

    public function deleteAppointment($appointment_id, $patientId = null) {
        if (!$this->hasAppointmentColumn('status')) {
            $this->setLastError('Soft delete is unavailable because the appointment status column is missing.');
            return false;
        }

        $checkQuery = "SELECT appointment_id, COALESCE(status, 'Pending') AS appointment_status FROM appointment WHERE appointment_id = ?";
        if ($patientId !== null) {
            $checkQuery .= " AND patient_id = ?";
        }

        $checkStmt = $this->db->prepare($checkQuery);
        if (!$checkStmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        if ($patientId !== null) {
            $checkStmt->bind_param("ii", $appointment_id, $patientId);
        } else {
            $checkStmt->bind_param("i", $appointment_id);
        }

        if (!$checkStmt->execute()) {
            $this->setLastError('Execute failed: ' . $checkStmt->error);
            $checkStmt->close();
            return false;
        }

        $checkResult = $checkStmt->get_result();
        $appointment = $checkResult ? $checkResult->fetch_assoc() : null;
        $checkStmt->close();

        if (!$appointment) {
            $this->setLastError('Appointment not found.');
            return false;
        }

        if (strcasecmp((string)($appointment['appointment_status'] ?? ''), 'Cancelled') === 0) {
            $this->setLastError('This appointment has already been cancelled.');
            return false;
        }

        $query = "UPDATE appointment SET status = 'Cancelled' WHERE appointment_id = ?";
        if ($patientId !== null) {
            $query .= " AND patient_id = ?";
        }

        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        if ($patientId !== null) {
            $stmt->bind_param("ii", $appointment_id, $patientId);
        } else {
            $stmt->bind_param("i", $appointment_id);
        }

        $success = $stmt->execute();
        if (!$success) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    }

    private function ensurePrescriptionRequestsTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS prescription_requests (
                request_id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                prescription_file_path VARCHAR(255) NOT NULL,
                notes TEXT NULL,
                preferred_date DATE NULL,
                preferred_time TIME NULL,
                home_collection TINYINT(1) NOT NULL DEFAULT 0,
                collection_address VARCHAR(255) DEFAULT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'Pending',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_prescription_requests_patient
                    FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";

        if (!$this->db->query($sql)) {
            $this->setLastError('Failed to initialize prescription requests table: ' . $this->db->error);
            return false;
        }

        return true;
    }

    private function ensurePrescriptionRequestEventsTable() {
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
            $this->setLastError('Failed to initialize prescription request events table: ' . $this->db->error);
            return false;
        }

        return true;
    }

    public function createPrescriptionHelpRequest($patientId, $filePath, $notes = '', $preferredDate = '', $preferredTime = '', $homeCollection = 0, $collectionAddress = '', $requestType = 'PRESCRIPTION') {
        if (!$this->ensurePrescriptionRequestsTableReady()) {
            return false;
        }

        $normalizedRequestType = strtoupper(trim((string)$requestType));
        if (!in_array($normalizedRequestType, ['PRESCRIPTION', 'HOME_VISIT_NO_PRESCRIPTION'], true)) {
            $normalizedRequestType = 'PRESCRIPTION';
        }

        $dateValue = $preferredDate !== '' ? $preferredDate : null;
        $timeValue = $preferredTime !== '' ? $preferredTime : null;
        $visitType = !empty($homeCollection) ? 'HOME_VISIT' : 'ONSITE';
        if ($normalizedRequestType === 'HOME_VISIT_NO_PRESCRIPTION') {
            $visitType = 'HOME_VISIT';
        }

        $filePathValue = trim((string)$filePath);
        if ($normalizedRequestType === 'HOME_VISIT_NO_PRESCRIPTION') {
            $filePathValue = null;
        } elseif ($filePathValue === '') {
            $filePathValue = null;
        }

        $addressValue = trim((string)$collectionAddress);
        $addressValue = $addressValue !== '' ? $addressValue : null;

        $columns = ['patient_id'];
        $placeholders = ['?'];
        $types = 'i';
        $params = [$patientId];

        if ($this->hasTableColumn('prescription_requests', 'request_type')) {
            $columns[] = 'request_type';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = $normalizedRequestType;
        }

        if ($this->hasTableColumn('prescription_requests', 'visit_type')) {
            $columns[] = 'visit_type';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = $visitType;
        } elseif ($this->hasTableColumn('prescription_requests', 'home_collection')) {
            $columns[] = 'home_collection';
            $placeholders[] = '?';
            $types .= 'i';
            $params[] = ($visitType === 'HOME_VISIT') ? 1 : 0;
        }

        $columns[] = 'prescription_file_path';
        $placeholders[] = '?';
        $types .= 's';
        $params[] = $filePathValue;

        if ($this->hasTableColumn('prescription_requests', 'notes')) {
            $columns[] = 'notes';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = $notes;
        }

        if ($this->hasTableColumn('prescription_requests', 'preferred_date')) {
            $columns[] = 'preferred_date';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = $dateValue;
        }

        if ($this->hasTableColumn('prescription_requests', 'preferred_time')) {
            $columns[] = 'preferred_time';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = $timeValue;
        }

        if ($this->hasTableColumn('prescription_requests', 'collection_address')) {
            $columns[] = 'collection_address';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = $addressValue;
        }

        if ($this->hasTableColumn('prescription_requests', 'status')) {
            $columns[] = 'status';
            $placeholders[] = '?';
            $types .= 's';
            $params[] = 'Pending';
        }

        $sql = "INSERT INTO prescription_requests (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        if (!$success) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $stmt->close();

        return true;
    }

    private function insertAppointmentHeader($patientId, $appointmentTime, $appointmentDate, $method, $reason) {
        $sqlWithReason = "INSERT INTO appointment (patient_id, appointment_time, appointment_date, method, reason) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sqlWithReason);
        if ($stmt) {
            $stmt->bind_param('issss', $patientId, $appointmentTime, $appointmentDate, $method, $reason);
            if ($stmt->execute()) {
                return intval($this->db->insert_id);
            }
        }

        $sqlNoReason = "INSERT INTO appointment (patient_id, appointment_time, appointment_date, method) VALUES (?, ?, ?, ?)";
        $stmtNoReason = $this->db->prepare($sqlNoReason);
        if (!$stmtNoReason) {
            error_log('Prepare failed in insertAppointmentHeader: ' . $this->db->error);
            return 0;
        }

        $stmtNoReason->bind_param('isss', $patientId, $appointmentTime, $appointmentDate, $method);
        if (!$stmtNoReason->execute()) {
            error_log('Execute failed in insertAppointmentHeader: ' . $stmtNoReason->error);
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

        $cleanIds = [];
        foreach ($testIds as $id) {
            if (is_string($id)) {
                $id = trim($id);
                if ($id !== '' && ctype_digit($id)) {
                    $cleanIds[] = intval($id);
                }
                continue;
            }

            if (is_int($id) && $id > 0) {
                $cleanIds[] = $id;
            }
        }

        return array_values(array_unique($cleanIds));
    }

    public function updatePaymentStatus($appointmentId, $status, $reference) {
        $stmt = $this->db->prepare(
            "UPDATE appointment SET payment_status = ?, payment_reference = ? WHERE appointment_id = ?"
        );
        if (!$stmt) {
            $this->setLastError('Prepare failed (updatePaymentStatus): ' . $this->db->error);
            return false;
        }
        $stmt->bind_param('ssi', $status, $reference, $appointmentId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getTestsTotal(array $testIds) {
        $cleanIds = array_values(array_filter(array_map('intval', $testIds), function ($id) { return $id > 0; }));
        if (empty($cleanIds)) return 0.0;

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $stmt = $this->db->prepare("SELECT SUM(price) AS total FROM tests WHERE test_id IN ($placeholders) AND " . $this->buildTestNotDeletedClause('tests'));
        if (!$stmt) return 0.0;

        $stmt->bind_param(str_repeat('i', count($cleanIds)), ...$cleanIds);
        if (!$stmt->execute()) { $stmt->close(); return 0.0; }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (float)($row['total'] ?? 0.0);
    }

    // ── Online Booking Slots ──────────────────────────────────────────────────

    public function getAvailableSlotsForDate(string $date, string $dayGroup): array {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.start_time, s.end_time, s.max_patients,
                (SELECT COUNT(*) FROM appointment a
                 WHERE a.appointment_time = s.start_time
                   AND a.appointment_date = ?
                   AND a.method = 'online'
                   AND LOWER(COALESCE(a.status,'pending')) <> 'cancelled'
                ) AS booked_count
             FROM online_booking_slots s
             WHERE s.day_group = ? AND s.is_active = 1
             ORDER BY s.start_time"
        );
        if (!$stmt) return [];
        $stmt->bind_param('ss', $date, $dayGroup);
        $stmt->execute();
        $result = $stmt->get_result();
        $slots = [];
        $today = date('Y-m-d');
        $isToday = ($date === $today);
        $now = strtotime(date('H:i:s'));

        while ($row = $result->fetch_assoc()) {
            if ($isToday) {
                $slotTs = strtotime((string)$row['start_time']);
                if ($slotTs !== false && $slotTs <= $now) {
                    continue;
                }
            }

            $row['available'] = ((int)$row['booked_count'] < (int)$row['max_patients']);
            $slots[] = $row;
        }
        $stmt->close();
        return $slots;
    }
}

?>
