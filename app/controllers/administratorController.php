<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/administratorModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class administratorController {
    private $db;
    private $adminModel;

        public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        $this->adminModel = new administratorModel($this->db);
    }

        public function settings() {
            $users = $this->adminModel->getAllUsers();
            include VIEW_PATH . '/administrator/settings.php';
        }
        public function createUser() {
            // Logic to create a new user in the database
            $username = $_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $contact_number = $_POST['contact_number'];
            $email = $_POST['email'];
            $user=$this->adminModel->createUser($username, $password, $role, $contact_number, $email);
            if($user){
                // Redirect back to settings or user list after creation
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings');
            } else{
                echo "Error creating user.";
            }
                

        // Additional methods for adding, editing, deleting users can be added here
    }
    public function manageUser() {
        if (isset($_POST['delete'])) {
            $userId = $_POST['user_id'];
            // Logic to delete the user from the database
            $success=$this->adminModel->deleteUser($userId);
            // Redirect back to settings or user list after deletion
            if($success){
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings');
            } else {
                echo "Error deleting user.";
            }
            
        }elseif(isset($_POST['edit'])) {
            $userId = $_POST['user_id'];
            $username = $_POST['username'];
            $role = $_POST['role'];
            $email = $_POST['email'];
            $status = $_POST['status'];
            // Logic to update the user details in the database
            $success=$this->adminModel->updateUser($userId, $username, $email, $role, $status);
            // Redirect back to settings or user list after update
            if($success){
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings');
            } else {
                echo "Error updating user.";
            }
        }
    }}

?>