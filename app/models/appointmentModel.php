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

    // Other appointment-related methods can be added here
}
?>