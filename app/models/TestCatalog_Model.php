<?php
class TestCatalog {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function addTest($name, $category, $price) {
        $stmt = $this->db->prepare("INSERT INTO tests (test_name, category, price) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $name, $category, $price);
        return $stmt->execute();
    }
   
    

}
?>