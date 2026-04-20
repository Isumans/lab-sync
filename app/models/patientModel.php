<?php

class patientModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllPatients() {
        $result = $this->db->query("SELECT * FROM patients WHERE " . $this->buildPatientNotDeletedClause() . " ORDER BY patient_id DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPatientById($patient_id) {
        $stmt = $this->db->prepare("SELECT patient_id, patient_name, email, contact_number FROM patients WHERE patient_id = ? AND " . $this->buildPatientNotDeletedClause() . " LIMIT 1");
        $stmt->bind_param("i", $patient_id);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $patient = $result ? $result->fetch_assoc() : null;
        $stmt->close();
        return $patient ?: null;
    }

    public function registerPatient($patient_name, $dob, $gender, $contact_no, $email) {
        if (!$this->isValidPatientRegistration($patient_name, $dob, $gender, $contact_no, $email)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO patients (patient_name, date_of_birth, gender, contact_number, email) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            return false;
        }

        $emailValue = trim((string)$email) !== '' ? $email : null;
        $stmt->bind_param("sssss", $patient_name, $dob, $gender, $contact_no, $emailValue);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function createPatientAndReturn($patient_name, $dob, $gender, $contact_no, $email) {
        if (!$this->isValidPatientRegistration($patient_name, $dob, $gender, $contact_no, $email)) {
            return null;
        }

        $stmt = $this->db->prepare("INSERT INTO patients (patient_name, date_of_birth, gender, contact_number, email) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            return null;
        }

        $emailValue = trim((string)$email) !== '' ? $email : null;
        $stmt->bind_param("sssss", $patient_name, $dob, $gender, $contact_no, $emailValue);
        $ok = $stmt->execute();
        $newPatientId = $ok ? intval($this->db->insert_id) : 0;
        $stmt->close();

        if (!$ok || $newPatientId <= 0) {
            return null;
        }

        return $this->getPatientById($newPatientId);
    }
    public function updatePatient($patient_id, $patient_name, $contact_number, $email) {
        if (!$this->isValidPatientUpdate($patient_name, $contact_number, $email)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE patients SET patient_name = ?, contact_number = ?, email = ? WHERE patient_id = ? AND " . $this->buildPatientNotDeletedClause());
        $stmt->bind_param("sssi", $patient_name, $contact_number, $email, $patient_id);
        return $stmt->execute();
    }
    public function deletePatient($patient_id, $actorUserId = null) {
        $patient_id = intval($patient_id);
        $actorUserId = is_numeric($actorUserId) ? intval($actorUserId) : null;

        if ($patient_id <= 0) {
            return false;
        }

        $hasDeletedAt = $this->columnExists('patients', 'deleted_at');
        $hasDeletedBy = $this->columnExists('patients', 'deleted_by');
        if (!$hasDeletedAt && !$hasDeletedBy) {
            return false;
        }

        $setParts = [];
        $types = '';
        $params = [];

        if ($hasDeletedAt) {
            $setParts[] = 'deleted_at = NOW()';
        }

        if ($hasDeletedBy) {
            $setParts[] = 'deleted_by = ?';
            $types .= 'i';
            $params[] = ($actorUserId !== null && $actorUserId > 0) ? $actorUserId : null;
        }

        $sql = "UPDATE patients SET " . implode(', ', $setParts) . " WHERE patient_id = ? AND " . $this->buildPatientNotDeletedClause();
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $types .= 'i';
        $params[] = $patient_id;
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }
    public function searchPatients($type, $query) {
        $query = '%' . trim((string)$query) . '%';

        if ($type === 'email') {
            $stmt = $this->db->prepare("SELECT patient_id AS id, patient_name AS name, email FROM patients WHERE email LIKE ? AND " . $this->buildPatientNotDeletedClause() . " ORDER BY email ASC LIMIT 10");
        } else {
            $stmt = $this->db->prepare("SELECT patient_id AS id, patient_name AS name, email FROM patients WHERE patient_name LIKE ? AND " . $this->buildPatientNotDeletedClause() . " ORDER BY patient_name ASC LIMIT 10");
        }

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('s', $query);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $patients = [];

        while ($result && ($row = $result->fetch_assoc())) {
            $patients[] = $row;
        }

        $stmt->close();
        return $patients;
    }

    private function isValidPatientRegistration($patientName, $dob, $gender, $contactNo, $email) {
        if (!$this->isValidPatientUpdate($patientName, $contactNo, $email)) {
            return false;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$dob)) {
            return false;
        }

        if (!in_array(strtolower((string)$gender), ['male', 'female', 'other'], true)) {
            return false;
        }

        return true;
    }

    private function isValidPatientUpdate($patientName, $contactNo, $email) {
        if (trim((string)$patientName) === '' || strlen((string)$patientName) > 120) {
            return false;
        }

        if (!preg_match('/^[0-9+()\-\s]{7,25}$/', trim((string)$contactNo))) {
            return false;
        }

        $emailTrimmed = trim((string)$email);
        if ($emailTrimmed !== '' && (!filter_var($emailTrimmed, FILTER_VALIDATE_EMAIL) || strlen($emailTrimmed) > 120)) {
            return false;
        }

        return true;
    }

    private function buildPatientNotDeletedClause() {
        $parts = [];

        if ($this->columnExists('patients', 'deleted_at')) {
            $parts[] = 'deleted_at IS NULL';
        }

        if ($this->columnExists('patients', 'deleted_by')) {
            $parts[] = 'deleted_by IS NULL';
        }

        if (empty($parts)) {
            return '1 = 1';
        }

        return implode(' AND ', $parts);
    }

    private function columnExists($tableName, $columnName) {
        $tableName = $this->db->real_escape_string($tableName);
        $columnName = $this->db->real_escape_string($columnName);
        $sql = "SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'";
        $result = $this->db->query($sql);
        return $result && $result->num_rows > 0;
    }

}
