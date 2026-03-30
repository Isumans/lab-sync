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
            $ftype   = mime_content_type($_FILES['logo']['tmp_name']);
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
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                $logoPath = '/lab_sync/public/uploads/' . $filename;
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

        $ok = $this->adminModel->saveLabConfig($data);
        echo json_encode([
            'success'   => (bool)$ok,
            'message'   => $ok ? 'Lab configuration saved successfully.' : 'Failed to save. Please try again.',
            'logo_path' => $logoPath,
        ]);
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