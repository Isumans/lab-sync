<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/partnerModel.php';
require_once 'C:\xampp\htdocs\lab_sync\config\db.php';

class PartnerLabController {
    private $db;
    private $partnerModel;

        public function __construct() {
        $this->db = connect();
        if (!$this->db) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        $this->partnerModel = new partnerModel($this->db);
        
    }

        public function index($role) {
            $tests = $this->partnerModel->getAllTests();
            
            include VIEW_PATH . '/administrator/settings/regPartnerLab.php';
        }

        public function storeLab() {
            // Validate and sanitize input
                $role = $_GET['role'] ?? '';
            $lab_name = trim($_POST['lab_name']);
            $email = trim($_POST['email']);
            $contact_person = trim($_POST['contact_person']);
            $phone = trim($_POST['phone']);
            $website = trim($_POST['website']);
            $address = trim($_POST['address']);
            $services = isset($_POST['services']) ? $_POST['services'] : [];

            // Basic validation
            if (empty($lab_name) || empty($email) || empty($contact_person) || empty($phone)) {
                die("Please fill in all required fields.");
            }

            // Store partner lab information in the database
            $lab_id = $this->partnerModel->createPartnerLab($lab_name, $email, $contact_person, $phone, $website, $address);
    
            if ($lab_id) {
                // Associate selected services with the partner lab
                foreach ($services as $test_id) {
                    $this->partnerModel->addServiceToPartnerLab($lab_id, $test_id);
                }
                header("Location: /lab_sync/index.php?controller=administratorController&action=settings&role=" . urlencode($role) . "&section=partner-labs");
                exit();
            } else {
                die("Failed to create partner lab. Please try again.");
            }

       
        }
}

?>