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
        $result = $this->db->query("SELECT * FROM tests");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createAppointment($data) {
        $this->ensureHomeCollectionColumns();

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
            $data['test_id'],
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
        $patientId = isset($data['patient_id']) ? intval($data['patient_id']) : 0;
        $appointmentTime = $data['appointment_time'] ?? '';
        $appointmentDate = $data['appointment_date'] ?? '';
        $method = $data['method'] ?? 'Online';
        $reason = $data['reason'] ?? '';

        $testIds = [];
        if (isset($data['test_ids']) && is_array($data['test_ids'])) {
            $testIds = $data['test_ids'];
        } elseif (isset($data['test_id'])) {
            $testIds = [$data['test_id']];
        }

        $cleanTestIds = $this->normalizeTestIds($testIds);

        if ($patientId <= 0 || $appointmentTime === '' || $appointmentDate === '' || empty($cleanTestIds)) {
            error_log('Invalid createAppointment payload in HomeModel.');
            return false;
        }

        if ($this->appointmentTestsTableExists()) {
            $this->db->begin_transaction();

            try {
                $appointmentId = $this->insertAppointmentHeader($patientId, $appointmentTime, $appointmentDate, $method, $reason);
                if ($appointmentId <= 0) {
                    throw new Exception('Failed to resolve inserted appointment_id.');
                }

                $lineStmt = $this->db->prepare("INSERT INTO appointment_tests (appointment_id, test_id, status) VALUES (?, ?, 'PENDING')");
                if (!$lineStmt) {
                    throw new Exception('Prepare failed (appointment_tests): ' . $this->db->error);
                }

                foreach ($cleanTestIds as $testId) {
                    $lineStmt->bind_param('ii', $appointmentId, $testId);
                    if (!$lineStmt->execute()) {
                        throw new Exception('Execute failed (appointment_tests): ' . $lineStmt->error);
                    }
                }

                $this->db->commit();
                return $appointmentId;
            } catch (Throwable $e) {
                $this->db->rollback();
                error_log('createAppointment transaction failed: ' . $e->getMessage());
                return false;
            }
        }

        // Legacy fallback for schemas that still keep one test_id in appointment.
        $legacyStmt = $this->db->prepare(
            "INSERT INTO appointment (patient_id, test_id, appointment_time, appointment_date, method) VALUES (?, ?, ?, ?, ?)"
        );

        if (!$legacyStmt) {
            error_log('Prepare failed (legacy createAppointment): ' . $this->db->error);
            return false;
        }

        $firstTestId = intval($cleanTestIds[0]);
        $legacyStmt->bind_param('iisss', $patientId, $firstTestId, $appointmentTime, $appointmentDate, $method);

        if (!$legacyStmt->execute()) {
            error_log('Execute failed (legacy createAppointment): ' . $legacyStmt->error);
            return false;
        }

        $newId = $legacyStmt->insert_id;
        $legacyStmt->close();

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
        $priceStmt = $this->db->prepare("SELECT test_id, price FROM tests WHERE test_id IN ($placeholders)");
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
            "SELECT p.patient_id, p.patient_name, p.email FROM patients p JOIN users u ON p.email = u.email WHERE u.user_id = ? LIMIT 1"
        );
    }
    
    public function getAllAppointments($patientId) {
        if ($this->appointmentTestsTableExists()) {
            $sql = "
                SELECT
                    a.*,
                    GROUP_CONCAT(at.test_id ORDER BY at.test_id SEPARATOR ', ') AS test_id
                FROM appointment a
                LEFT JOIN appointment_tests at ON at.appointment_id = a.appointment_id
                WHERE a.patient_id = ?
                GROUP BY a.appointment_id
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
            ";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log('Prepare failed in getAllAppointments: ' . $this->db->error);
                return [];
            }

            $pid = intval($patientId);
            $stmt->bind_param('i', $pid);
            if (!$stmt->execute()) {
                error_log('Execute failed in getAllAppointments: ' . $stmt->error);
                return [];
            }

            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        $result = $this->db->query("SELECT * FROM appointment WHERE patient_id = " . intval($patientId));
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    public function updateAppointment($appointment_id, $appointment_time, $appointment_date) {
        $stmt = $this->db->prepare("
            UPDATE appointment 
            SET appointment_time = ?, appointment_date = ? 
            WHERE appointment_id = ?
        ");

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
        if (!$this->hasTable('prescription_requests')) {
            return [];
        }

        $this->ensureHomeCollectionColumns();

        $safeLimit = max(1, min(50, (int)$limit));
        $decisionActionSelect = $this->hasTableColumn('prescription_requests', 'decision_action')
            ? 'decision_action'
            : 'NULL AS decision_action';
        $linkedAppointmentSelect = $this->hasTableColumn('prescription_requests', 'linked_appointment_id')
            ? 'linked_appointment_id'
            : 'NULL AS linked_appointment_id';
        $decisionAtSelect = $this->hasTableColumn('prescription_requests', 'decision_at')
            ? 'decision_at'
            : 'NULL AS decision_at';
        $homeCollectionSelect = $this->hasTableColumn('prescription_requests', 'home_collection')
            ? 'home_collection'
            : '0 AS home_collection';
        $collectionAddressSelect = $this->hasTableColumn('prescription_requests', 'collection_address')
            ? 'collection_address'
            : 'NULL AS collection_address';

        $sql = "
            SELECT
                request_id,
                prescription_file_path,
                notes,
                preferred_date,
                preferred_time,
                " . $homeCollectionSelect . ",
                " . $collectionAddressSelect . ",
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

        $totalField = $this->hasTable('appointment_items')
            ? "COALESCE((SELECT SUM(ai.line_total) FROM appointment_items ai WHERE ai.appointment_id = a.appointment_id), t.price, 0)"
            : "COALESCE(t.price, 0)";

        $query = "
            SELECT
                a.*,
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

    public function updateAppointment($appointment_id, $appointment_time, $appointment_date) {
        $stmt = $this->db->prepare(
            "UPDATE appointment SET appointment_time = ?, appointment_date = ? WHERE appointment_id = ?"
        );

        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param("ssi", $appointment_time, $appointment_date, $appointment_id);

        $success = $stmt->execute();
        if (!$success) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    }

    public function deleteAppointment($appointment_id) {
        if ($this->appointmentTestsTableExists()) {
            $this->db->begin_transaction();

            try {
                $deleteLines = $this->db->prepare("DELETE FROM appointment_tests WHERE appointment_id = ?");
                if (!$deleteLines) {
                    throw new Exception('Prepare failed (delete appointment_tests): ' . $this->db->error);
                }

                $deleteLines->bind_param('i', $appointment_id);
                if (!$deleteLines->execute()) {
                    throw new Exception('Execute failed (delete appointment_tests): ' . $deleteLines->error);
                }

                $deleteHeader = $this->db->prepare("DELETE FROM appointment WHERE appointment_id = ?");
                if (!$deleteHeader) {
                    throw new Exception('Prepare failed (delete appointment): ' . $this->db->error);
                }

                $deleteHeader->bind_param('i', $appointment_id);
                if (!$deleteHeader->execute()) {
                    throw new Exception('Execute failed (delete appointment): ' . $deleteHeader->error);
                }

                $this->db->commit();
                return true;
            } catch (Throwable $e) {
                $this->db->rollback();
                error_log('deleteAppointment transaction failed: ' . $e->getMessage());
                return false;
            }
        }

        $stmt = $this->db->prepare("DELETE FROM appointment WHERE appointment_id = ?");

        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $appointment_id);

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

    public function createPrescriptionHelpRequest($patientId, $filePath, $notes = '', $preferredDate = '', $preferredTime = '', $homeCollection = 0, $collectionAddress = '') {
        if (!$this->ensurePrescriptionRequestsTable()) {
            return false;
        }

        if (!$this->ensurePrescriptionRequestEventsTable()) {
            return false;
        }

        $this->ensureHomeCollectionColumns();

        $dateValue = $preferredDate !== '' ? $preferredDate : null;
        $timeValue = $preferredTime !== '' ? $preferredTime : null;
        $homeCollectionValue = !empty($homeCollection) ? 1 : 0;
        $addressValue = trim((string)$collectionAddress);
        $addressValue = $addressValue !== '' ? $addressValue : null;

        $stmt = $this->db->prepare(
            "INSERT INTO prescription_requests (patient_id, prescription_file_path, notes, preferred_date, preferred_time, home_collection, collection_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );

        if (!$stmt) {
            $this->setLastError('Prepare failed: ' . $this->db->error);
            return false;
        }

        $stmt->bind_param("issssis", $patientId, $filePath, $notes, $dateValue, $timeValue, $homeCollectionValue, $addressValue);
        $success = $stmt->execute();
        if (!$success) {
            $this->setLastError('Execute failed: ' . $stmt->error);
            $stmt->close();
            return false;
        }

        $requestId = (int)$stmt->insert_id;
        $stmt->close();

        $eventStmt = $this->db->prepare(
            "INSERT INTO prescription_request_events (request_id, event_type, old_status, new_status, note, created_by_user_id)
             VALUES (?, 'submitted', NULL, 'Pending', ?, NULL)"
        );

        if ($eventStmt) {
            $eventNote = $notes !== '' ? $notes : null;
            $eventStmt->bind_param("is", $requestId, $eventNote);
            $eventStmt->execute();
            $eventStmt->close();
        }

        return true;
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
}

?>