<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

class ProfileModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProfileByUserId($id) {
        $this->ensureAvatarColumn();
        $stmt = $this->db->prepare(
            "SELECT 
                u.user_id,
                u.username,
                u.email AS user_email,
                u.contact_number AS user_contact,
                u.role,
                p.patient_id,
                p.patient_name,
                p.email AS patient_email,
                p.contact_number,
                p.gender,
                p.address,
                COALESCE(p.avatar_path, '') AS avatar_path
             FROM users u
             LEFT JOIN patients p ON p.email = u.email
             WHERE u.user_id = ?
             LIMIT 1"
        );
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

    public function getUserAuthById($id) {
        $stmt = $this->db->prepare("SELECT user_id, password FROM users WHERE user_id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    public function updateUserPassword($id, $hashedPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $hashedPassword, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updatePatientAvatar($patientId, $avatarPath) {
        $stmt = $this->db->prepare("UPDATE patients SET avatar_path = ? WHERE patient_id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("si", $avatarPath, $patientId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    private function ensureAvatarColumn() {
        $result = $this->db->query("SHOW COLUMNS FROM patients LIKE 'avatar_path'");
        if (!$result || $result->num_rows === 0) {
            $this->db->query("ALTER TABLE patients ADD COLUMN avatar_path VARCHAR(255) NULL AFTER address");
        }
    }
}