<?php

class AppointmentModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createAppointment($patientId, $appointmentDate, $appointmentTime, $reason) {
        $stmt = $this->db->prepare("INSERT INTO appointments (patient_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $patientId, $appointmentDate, $appointmentTime, $reason);
        return $stmt->execute();
    }
    public function getAllAppointmentsbyMethod($method) {
        $stmt = $this->db->prepare("SELECT * FROM appointment WHERE method = ?");
        $stmt->bind_param("s", $method);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Other appointment-related methods can be added here
}
?>