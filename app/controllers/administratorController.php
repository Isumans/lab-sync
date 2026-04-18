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

        public function settings($role) {
            $users = $this->adminModel->getAllUsers();
            include VIEW_PATH . '/administrator/settings.php';
        }

        public function usersByRole($role) {
            $users = $this->adminModel->getUserByRole($role);
            include VIEW_PATH . '/administrator/settings.php';
        }
        public function createUser($role) {
            // Logic to create a new user in the database
            $role=$_GET['role'] ?? '';
            $username = $_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $contact_number = $_POST['contact_number'];
            $email = $_POST['email'];
            $user=$this->adminModel->createUser($username, $password, $role, $contact_number, $email);
            if($user){
                // Redirect back to settings or user list after creation
                $role = $_POST['role'];
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($role));
            } else{
                echo "Error creating user.";
            }
                

        // Additional methods for adding, editing, deleting users can be added here
    }
    public function manageUser($role) {
        if (isset($_POST['delete'])) {
            $userId = $_POST['user_id'];
            $role = $_POST['role'];
            // Logic to delete the user from the database
            $success=$this->adminModel->deleteUser($userId);
            // Redirect back to settings or user list after deletion
            if($success){
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($role));
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
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($role));
            } else {
                echo "Error updating user.";
            }
        }
        }

    public function getLabConfigurationSection() {
        $config = $this->adminModel->getLabConfig();
        include VIEW_PATH . '/administrator/settings/lab_configuration.php';
    }

    public function saveLabConfiguration() {
        header('Content-Type: application/json');

        // Handle logo upload
        $logoPath = $_POST['existing_logo_path'] ?? '';
        if (!empty($_FILES['logo']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $tmpName = $_FILES['logo']['tmp_name'] ?? '';
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                echo json_encode(['success' => false, 'message' => 'Invalid logo upload.']);
                return;
            }

            $ftype   = mime_content_type($tmpName);
            $fsize   = $_FILES['logo']['size'];

            if (!in_array($ftype, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF, WEBP allowed.']);
                exit;
            }
            if ($fsize > 2 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File too large (max 2MB).']);
                exit;
            }

            $ext      = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'lab_logo_' . time() . '.' . $ext;
            $dest     = ROOT_PATH . '/public/uploads/' . $filename;
            if (move_uploaded_file($tmpName, $dest)) {
                $logoPath = '/lab_sync/public/uploads/' . $filename;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload logo.']);
                return;
            }
        }

        $data = [
            'lab_name'               => trim($_POST['lab_name']         ?? ''),
            'accreditation'          => trim($_POST['accreditation']    ?? ''),
            'address'                => trim($_POST['address']          ?? ''),
            'phone'                  => trim($_POST['phone']            ?? ''),
            'email'                  => trim($_POST['email']            ?? ''),
            'logo_path'              => $logoPath,
            'hours_mon_fri_open'     => $_POST['hours_mon_fri_open']    ?? '08:00',
            'hours_mon_fri_close'    => $_POST['hours_mon_fri_close']   ?? '17:00',
            'hours_mon_fri_enabled'  => isset($_POST['hours_mon_fri_enabled'])  ? 1 : 0,
            'hours_sat_open'         => $_POST['hours_sat_open']        ?? '09:00',
            'hours_sat_close'        => $_POST['hours_sat_close']       ?? '14:00',
            'hours_sat_enabled'      => isset($_POST['hours_sat_enabled'])       ? 1 : 0,
            'hours_sun_open'         => $_POST['hours_sun_open']        ?? '12:00',
            'hours_sun_close'        => $_POST['hours_sun_close']       ?? '12:00',
            'hours_sun_enabled'      => isset($_POST['hours_sun_enabled'])       ? 1 : 0,
            'allow_walkins'          => isset($_POST['allow_walkins'])           ? 1 : 0,
            'auto_email_reports'     => isset($_POST['auto_email_reports'])      ? 1 : 0,
        ];

        $validation = $this->validateLabConfigPayload($data);
        if (!$validation['ok']) {
            echo json_encode([
                'success' => false,
                'message' => $validation['message'],
            ]);
            return;
        }

        $data = $validation['data'];

        $ok = $this->adminModel->saveLabConfig($data);
        echo json_encode([
            'success'   => (bool)$ok,
            'message'   => $ok ? 'Lab configuration saved successfully.' : 'Failed to save. Please try again.',
            'logo_path' => $logoPath,
        ]);
    }

    private function validateLabConfigPayload(array $data) {
        if ($data['lab_name'] === '' || strlen($data['lab_name']) > 120) {
            return ['ok' => false, 'message' => 'Lab name is required and must be at most 120 characters.'];
        }

        if ($data['accreditation'] === '' || strlen($data['accreditation']) > 80) {
            return ['ok' => false, 'message' => 'Accreditation number is required and must be at most 80 characters.'];
        }

        if ($data['address'] === '' || strlen($data['address']) > 255) {
            return ['ok' => false, 'message' => 'Address is required and must be at most 255 characters.'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 120) {
            return ['ok' => false, 'message' => 'Official email is invalid.'];
        }

        if (!preg_match('/^[0-9+()\-\s]{7,25}$/', $data['phone'])) {
            return ['ok' => false, 'message' => 'Primary phone format is invalid.'];
        }

        $hoursConfig = [
            ['enabled' => intval($data['hours_mon_fri_enabled']), 'open' => (string)$data['hours_mon_fri_open'], 'close' => (string)$data['hours_mon_fri_close'], 'label' => 'Monday-Friday'],
            ['enabled' => intval($data['hours_sat_enabled']), 'open' => (string)$data['hours_sat_open'], 'close' => (string)$data['hours_sat_close'], 'label' => 'Saturday'],
            ['enabled' => intval($data['hours_sun_enabled']), 'open' => (string)$data['hours_sun_open'], 'close' => (string)$data['hours_sun_close'], 'label' => 'Sunday'],
        ];

        foreach ($hoursConfig as $cfg) {
            if (!$this->isValidTimeString($cfg['open']) || !$this->isValidTimeString($cfg['close'])) {
                return ['ok' => false, 'message' => $cfg['label'] . ' time format is invalid.'];
            }

            if ($cfg['enabled'] === 1 && strcmp($cfg['open'], $cfg['close']) >= 0) {
                return ['ok' => false, 'message' => $cfg['label'] . ' close time must be later than open time.'];
            }
        }

        $data['lab_name'] = substr($data['lab_name'], 0, 120);
        $data['accreditation'] = substr($data['accreditation'], 0, 80);
        $data['address'] = substr($data['address'], 0, 255);
        $data['phone'] = substr($data['phone'], 0, 25);
        $data['email'] = substr($data['email'], 0, 120);
        $data['hours_mon_fri_enabled'] = intval($data['hours_mon_fri_enabled']) ? 1 : 0;
        $data['hours_sat_enabled'] = intval($data['hours_sat_enabled']) ? 1 : 0;
        $data['hours_sun_enabled'] = intval($data['hours_sun_enabled']) ? 1 : 0;
        $data['allow_walkins'] = intval($data['allow_walkins']) ? 1 : 0;
        $data['auto_email_reports'] = intval($data['auto_email_reports']) ? 1 : 0;

        return ['ok' => true, 'message' => '', 'data' => $data];
    }

    private function isValidTimeString($value) {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string)$value) === 1;
    }

    public function getGeneralSettingsSection() {
        $settings = $this->adminModel->getGeneralSettings();
        include VIEW_PATH . '/administrator/settings/general_settings.php';
    }

    public function saveGeneralSettings() {
        header('Content-Type: application/json');

        $data = [
            'sms_alerts'      => isset($_POST['sms_alerts'])     ? 1 : 0,
            'email_reports'   => isset($_POST['email_reports'])  ? 1 : 0,
            'password_policy' => trim($_POST['password_policy']  ?? '60'),
            'session_timeout' => (int)($_POST['session_timeout'] ?? 15),
            'language'        => trim($_POST['language']         ?? 'en_US'),
            'timezone'        => trim($_POST['timezone']         ?? 'America/New_York'),
            'currency'        => trim($_POST['currency']         ?? 'USD'),
            'date_format'     => trim($_POST['date_format']      ?? 'dd/mm/yyyy'),
        ];

        $ok = $this->adminModel->saveGeneralSettings($data);
        echo json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'General settings saved successfully.' : 'Failed to save. Please try again.',
        ]);
    }
}

?>