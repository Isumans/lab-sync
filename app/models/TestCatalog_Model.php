<?php
class TestCatalog {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function getAllTests() {
        $result = $this->db->query("SELECT * FROM tests");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function addTest($name, $category, $price, $code) {
        $stmt = $this->db->prepare("INSERT INTO tests (name, category, price, code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $category, $price, $code);
        return $stmt->execute();
    }
   
    

}