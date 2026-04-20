<?php
class PartnerModel {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests WHERE " . $this->buildTestNotDeletedClause('tests'));
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function hasTestColumn($columnName) {
        $safeColumn = $this->db->real_escape_string($columnName);
        $result = $this->db->query("SHOW COLUMNS FROM tests LIKE '{$safeColumn}'");
        return $result && $result->num_rows > 0;
    }

    private function buildTestNotDeletedClause($alias = 'tests') {
        $parts = [];

        if ($this->hasTestColumn('deleted_at')) {
            $parts[] = "{$alias}.deleted_at IS NULL";
        }

        if ($this->hasTestColumn('deleted_by')) {
            $parts[] = "{$alias}.deleted_by IS NULL";
        }

        if (empty($parts)) {
            return '1 = 1';
        }

        return implode(' AND ', $parts);
    }

    public function createPartnerLab($lab_name, $email, $contact_person, $phone, $website, $address) {
        if (!$this->isValidPartnerLabData($lab_name, $email, $contact_person, $phone, $website, $address)) {
            return false;
        }

        $stmt = $this->db->prepare("INSERT INTO partner_labs (lab_name, email, contact_person_name, contact_person_phone, website, address) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            return false;
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



    public function deletePartnerLab($id) {
        $id = intval($id);
        if ($id <= 0) return false;
        // Remove associated test links first to avoid FK constraint issues
        $stmt = $this->db->prepare("DELETE FROM partner_lab_tests WHERE partner_lab_id = ?");
        if ($stmt) { $stmt->bind_param("i", $id); $stmt->execute(); $stmt->close(); }
        $stmt = $this->db->prepare("DELETE FROM partner_labs WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok && $this->db->affected_rows > 0;
    }

    public function getAllPartnerLabs() {
        $sql = "SELECT p.*, COUNT(plt.test_id) as total_tests 
                FROM partner_labs p 
                LEFT JOIN partner_lab_tests plt ON p.id = plt.partner_lab_id 
                GROUP BY p.id 
                ORDER BY p.created_at DESC";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function isValidPartnerLabData($labName, $email, $contactPerson, $phone, $website, $address) {
        if (trim((string)$labName) === '' || strlen((string)$labName) > 120) {
            return false;
        }

        if (!filter_var(trim((string)$email), FILTER_VALIDATE_EMAIL) || strlen((string)$email) > 120) {
            return false;
        }

        if (trim((string)$contactPerson) === '' || strlen((string)$contactPerson) > 120) {
            return false;
        }

        if (!preg_match('/^[0-9+()\-\s]{7,25}$/', trim((string)$phone))) {
            return false;
        }

        if (trim((string)$address) === '' || strlen((string)$address) > 255) {
            return false;
        }

        $website = trim((string)$website);
        if ($website !== '' && (!filter_var($website, FILTER_VALIDATE_URL) || strlen($website) > 255)) {
            return false;
        }

        return true;
    }
    

}