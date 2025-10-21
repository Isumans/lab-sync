<?php
class HomeModel {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public function getData() {
        // Sample data retrieval logic
        return [
            'welcomeMessage' => 'Welcome to LabSync!',
            'features' => [
                'Book lab tests online',
                'Get results quickly',
                '24/7 customer support'
            ]
        ];
    }

    public function registerPatient($username, $email, $contact_number, $password, $role) {
        // Sample registration logic
        // In a real application, you would save this data to a database
        // Here, we just simulate a successful registration
        $stmt = $this->db->prepare("INSERT INTO users (username, password, role, contact_number, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, password_hash($password, PASSWORD_BCRYPT), $role, $contact_number, $email);
        return $stmt->execute();
        // return true;
    }
}

?>