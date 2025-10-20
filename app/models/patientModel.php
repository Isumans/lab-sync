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

    public function registerPatient($patient_name, $dob, $gender, $contact_no, $email) {
        $stmt = $this->db->prepare("INSERT INTO patients (patient_name, date_of_birth, gender, contact_number, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $patient_name, $dob, $gender, $contact_no, $email);
        return $stmt->execute();
    }
    public function updatePatient($patient_id, $patient_name, $contact_number, $email) {
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

}