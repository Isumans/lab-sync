<?php
class AuthModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getUserByUsername($username) {
        $result = $this->db->query("SELECT * FROM users WHERE username = '$username'");
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();

        }else{
            return false;
        }
    }
    // public function getAllTests() {
    //     $result = $this->db->query("SELECT * FROM tests");
    //     return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    // }

    // public function addTest($name, $category, $price) {
    //     $stmt = $this->db->prepare("INSERT INTO tests (test_name, category, price) VALUES (?, ?, ?)");
    //     $stmt->bind_param("ssd", $name, $category, $price);
    //     return $stmt->execute();
    // }
   
    public function verifyUser($username, $password) {
    // Step 1: Check database connection
    if (!$this->db) {
        die("Database connection not established properly.");
    }

    // Step 2: The SQL query
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $this->db->prepare($query);

    // Step 3: Check if prepare() failed
    if (!$stmt) {
        die("Prepare failed: " . $this->db->error . " | Query: " . $query);
    }

    // Step 4: Proceed only if prepare() worked
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (($password === $user['password'])) {
        return $user;
    }else {
        echo "Password mismatch for user: $username"; // Debug line
    }

    // return false;
}

}
?>