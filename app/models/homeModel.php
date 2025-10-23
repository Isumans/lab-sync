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

    public function registerPatient($username, $email, $contact_number, $password, $role) {
        // Sample registration logic
        // In a real application, you would save this data to a database
        // Here, we just simulate a successful registration
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role, contact_number, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username,$password, $role, $contact_number, $email);
        $stmt2= $this-> db->prepare("INSERT INTO patients (patient_name, contact_number, email) VALUES (?, ?, ?)");
        $stmt2-> bind_param("sss", $username, $contact_number, $email);
        
        return $stmt->execute()&& $stmt2->execute();
        // return true;
    }
    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    public function createAppointment($data) {
        // Prepare SQL with all fields including method
        $stmt = $this->db->prepare("
            INSERT INTO appointment 
            (patient_id, test_id, appointment_time, appointment_date, method) 
            VALUES (?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return false;
        }

        // Bind parameters with their types
        $stmt->bind_param(
            "iisss", 
            $data['patient_id'],
            $data['test_id'],
            $data['appointment_time'],
            $data['appointment_date'],
            $data['method']
        );

        $success = $stmt->execute();
        
        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
            return false;
        }

        // Get the auto-incremented ID
        $newId = $stmt->insert_id;
        $stmt->close();

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
}

?>