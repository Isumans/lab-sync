<?php
class PartnerModel {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function createPartnerLab($lab_name, $email, $contact_person, $phone, $website, $address) {
        $stmt = $this->db->prepare("INSERT INTO partner_labs (lab_name, email, contact_person_name, contact_person_phone, website, address) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $this->db->error);
        }
        $stmt->bind_param("ssssss", $lab_name, $email, $contact_person, $phone, $website, $address);
        if ($stmt->execute()) {
            return $stmt->insert_id; // Return the ID of the newly created lab
        } else {
            return false;
        }
    }
    public function addServiceToPartnerLab($lab_id, $test_id) {
        $stmt = $this->db->prepare("INSERT INTO partner_lab_tests (partner_lab_id, test_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $lab_id, $test_id);
        return $stmt->execute();
    }



}
?>