<?php
class administratorModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllUsers() {
        $result = $this->db->query("SELECT * FROM users WHERE role='admin' OR role='receptionist' OR role='technician'");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    public function getUserByRole($role) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    public function createUser($username, $password, $role, $contact_number, $email) {
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role, contact_number, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, password_hash($password, PASSWORD_BCRYPT), $role, $contact_number, $email);
        return $stmt->execute();
    }
    public function deleteUser($userId) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function updateUser($userId, $username, $email, $role, $status) {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $username, $email, $role, $status, $userId);
        return $stmt->execute();
    }


    // Additional methods for adding, editing, deleting users can be added here
}
?>