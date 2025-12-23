<?php

class AppointmentModel {
    private $db;
    private $lastError = '';

    public function __construct($db) {
        $this->db = $db;
    }

    public function createAppointment($patientId, $appointmentDate, $appointmentTime, $reason) {
        // Try using the singular table name 'appointment' (other models use this)
        $sqlWithReason = "INSERT INTO appointment (patient_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sqlWithReason);
        if ($stmt !== false) {
            $stmt->bind_param("isss", $patientId, $appointmentDate, $appointmentTime, $reason);
            $result = $stmt->execute();
            if ($result === false) {
                $this->lastError = 'Execute failed in createAppointment (with reason): ' . $stmt->error;
                error_log($this->lastError);
            }
            return $result;
        }

        // If preparing with 'reason' failed (maybe column missing), try without reason
        $this->lastError = 'Prepare (with reason) failed in createAppointment: ' . $this->db->error;
        error_log($this->lastError);
        $sqlNoReason = "INSERT INTO appointment (patient_id, appointment_date, appointment_time) VALUES (?, ?, ?)";
        $stmt2 = $this->db->prepare($sqlNoReason);
        if ($stmt2 === false) {
            $this->lastError = 'Prepare failed in createAppointment (no reason): ' . $this->db->error;
            error_log($this->lastError);
            return false;
        }
        $stmt2->bind_param("iss", $patientId, $appointmentDate, $appointmentTime);
        $result2 = $stmt2->execute();
        if ($result2 === false) {
            $this->lastError = 'Execute failed in createAppointment (no reason): ' . $stmt2->error;
            error_log($this->lastError);
        }
        return $result2;
    }
    public function getAllAppointmentsbyMethod($method) {
        // other models use singular 'appointment'
        $stmt = $this->db->prepare("SELECT * FROM appointment WHERE method = ?");
        if ($stmt === false) {
            $this->lastError = 'Prepare failed in getAllAppointmentsbyMethod: ' . $this->db->error;
            error_log($this->lastError);
            return [];
        }
        $stmt->bind_param("s", $method);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getLastError() {
        return $this->lastError;
    }

    // Other appointment-related methods can be added here
}
?>