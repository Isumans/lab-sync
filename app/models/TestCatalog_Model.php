<?php
class TestCatalog {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function addTest($name, $category, $price, $description) {
        $stmt = $this->db->prepare("INSERT INTO tests (test_name, category, price, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $category, $price, $description);
        return $stmt->execute();
    }
    public function getTestById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tests WHERE test_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }
    public function updateTest($id, $name, $category, $price) {
        $stmt = $this->db->prepare("UPDATE tests SET test_name = ?, category = ?, price = ? WHERE test_id = ?");
        $stmt->bind_param("ssdi", $name, $category, $price, $id);
        return $stmt->execute();
    }

    public function deleteTest($id) {
        $stmt = $this->db->prepare("DELETE FROM tests WHERE test_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }


}
?>