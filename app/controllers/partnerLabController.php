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
            $lab_name = trim((string)($_POST['lab_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $contact_person = trim((string)($_POST['contact_person'] ?? ''));
            $phone = trim((string)($_POST['phone'] ?? ''));
            $website = trim((string)($_POST['website'] ?? ''));
            $address = trim((string)($_POST['address'] ?? ''));
            
            $services = isset($_POST['services']) ? $_POST['services'] : [];

            $validation = $this->validatePartnerLabPayload($lab_name, $email, $contact_person, $phone, $website, $address, $services);
            if (!$validation['ok']) {
                http_response_code(400);
                echo $validation['message'];
                return;
            }

            $website = $validation['website'];
            $services = $validation['services'];

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
                http_response_code(500);
                echo "Failed to create partner lab. Please try again.";
                return;
            }

       
        }
        public function getPartnerLabsSection() {

            // Fetch partner labs from the database
            $partnerLabs = $this->partnerModel->getAllPartnerLabs();            
            // Include the partial view. The view should now use $partnerLabs to render data.
            // We assume the view is located at app/views/administrator/settings/partner_labs.php
            // and it will output the HTML directly.
            $viewPath = VIEW_PATH . '/administrator/settings/partner_labs.php';
            if (!file_exists($viewPath)) {
                echo "ERROR: View file not found at $viewPath";
            } else {
                include $viewPath;
            }
        }

        private function validatePartnerLabPayload($labName, $email, $contactPerson, $phone, $website, $address, $services) {
            if ($labName === '' || strlen($labName) > 120) {
                return ['ok' => false, 'message' => 'Lab name is required and must be at most 120 characters.'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
                return ['ok' => false, 'message' => 'Email is invalid.'];
            }

            if ($contactPerson === '' || strlen($contactPerson) > 120) {
                return ['ok' => false, 'message' => 'Contact person is required and must be at most 120 characters.'];
            }

            if (!preg_match('/^[0-9+()\-\s]{7,25}$/', $phone)) {
                return ['ok' => false, 'message' => 'Phone number format is invalid.'];
            }

            if ($address === '' || strlen($address) > 255) {
                return ['ok' => false, 'message' => 'Address is required and must be at most 255 characters.'];
            }

            if ($website !== '' && (!filter_var($website, FILTER_VALIDATE_URL) || strlen($website) > 255)) {
                return ['ok' => false, 'message' => 'Website URL is invalid.'];
            }

            $serviceIds = [];
            if (!is_array($services)) {
                return ['ok' => false, 'message' => 'Selected services are invalid.'];
            }

            foreach ($services as $testId) {
                $id = intval($testId);
                if ($id > 0) {
                    $serviceIds[$id] = $id;
                }
            }

            if (empty($serviceIds)) {
                return ['ok' => false, 'message' => 'Select at least one testing service.'];
            }

            return [
                'ok' => true,
                'message' => '',
                'website' => $website,
                'services' => array_values($serviceIds),
            ];
        }
}

?>