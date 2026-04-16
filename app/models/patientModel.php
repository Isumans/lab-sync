<?php

class patientModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllPatients() {
        $result = $this->db->query("SELECT * FROM patients");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPatientById($patient_id) {
        $stmt = $this->db->prepare("SELECT patient_id, patient_name, email, contact_number FROM patients WHERE patient_id = ? LIMIT 1");
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
        $stmt->bind_param("sssss", $patient_name, $dob, $gender, $contact_no, $email);
        return $stmt->execute();
    }
    public function updatePatient($patient_id, $patient_name, $contact_number, $email) {
        if (!$this->isValidPatientUpdate($patient_name, $contact_number, $email)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE patients SET patient_name = ?, contact_number = ?, email = ? WHERE patient_id = ?");
        $stmt->bind_param("sssi", $patient_name, $contact_number, $email, $patient_id);
        return $stmt->execute();
    }
    public function deletePatient($patient_id) {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE patient_id = ?");
        $stmt->bind_param("i", $patient_id);
        return $stmt->execute();
    }
    public function searchPatients($type, $query) {
    // $conn = $this->connect();
        $query = "%" . $this->db->real_escape_string($query) . "%";

        if ($type === 'email') {
            $stmt = $this->db->prepare("SELECT patient_id AS id, patient_name AS name, email FROM patients WHERE email LIKE ? ORDER BY email ASC");
        } else {
            $stmt = $this->db->prepare("SELECT patient_id AS id, patient_name AS name, email FROM patients WHERE patient_name LIKE ? ORDER BY patient_name ASC");
        }

        $stmt->bind_param('s', $query);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $patients = [];
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }

        $stmt->close();
        // $conn->close();
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

        if (!filter_var(trim((string)$email), FILTER_VALIDATE_EMAIL) || strlen((string)$email) > 120) {
            return false;
        }

        return true;
    }

}