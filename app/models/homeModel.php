<?php
class HomeModel {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getData() {
        // Sample data retrieval logic
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
        // Register patient - insert into users and patients tables
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
    public function getPatientIdByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT p.patient_id 
            FROM patients p 
            JOIN users u ON p.email = u.email 
            WHERE u.user_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $userId);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }

        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();
        $stmt->close();

        return $patient ? $patient['patient_id'] : false;
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
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("ssi", $appointment_time, $appointment_date, $appointment_id);

        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
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
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        $stmt->bind_param("i", $appointment_id);

        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
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
}

?>