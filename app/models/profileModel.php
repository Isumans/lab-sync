<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

class ProfileModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getPatientById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Get result failed: " . $stmt->error);
            return null;
        }
        
        $patient = $result->fetch_assoc();
        $stmt->close();
        
        return $patient ?: null;  // Return null if no patient found
    }
    public function getPatientByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE email = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Get result failed: " . $stmt->error);
            return null;
        }
        
        $patient = $result->fetch_assoc();
        $stmt->close();
        
        return $patient ?: null;  // Return null if no patient found
    }

    public function updatePatient($id, $name, $email,$contact_number, $gender, $address) {
        $stmt = $this->db->prepare("UPDATE patients SET patient_name = ?, email = ?, contact_number = ?, gender = ?, address = ? WHERE patient_id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("sssssi", $name, $email, $contact_number, $gender, $address, $id);
        return $stmt->execute();
    }
    public function updateUser($id, $name, $email,$contact_number) {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, contact_number = ? WHERE user_id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("sssi", $name, $email, $contact_number, $id);
        return $stmt->execute();
    }
}