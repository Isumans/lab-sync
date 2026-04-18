<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';  // ✅ correct
}

require_once MODEL_PATH . '/administratorModel.php';
require_once APP_PATH . '/services/EmailService.php';
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
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&create_status=invalid_method');
                exit;
            }

            $sessionRole = (string)($_SESSION['user_role'] ?? '');
            if ($sessionRole !== 'admin') {
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&create_status=unauthorized');
                exit;
            }

            $username = trim((string)($_POST['username'] ?? ''));
            $newUserRole = trim((string)($_POST['role'] ?? ''));
            $contactNumber = trim((string)($_POST['contact_number'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $allowedRoles = ['admin', 'receptionist', 'technician'];

            if ($username === '' || $contactNumber === '' || $email === '' || !in_array($newUserRole, $allowedRoles, true)) {
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($newUserRole) . '&create_status=invalid_input');
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($newUserRole) . '&create_status=invalid_email');
                exit;
            }

            $temporaryPassword = $this->generateTemporaryPassword();
            $createResult = $this->adminModel->createUser($username, $temporaryPassword, $newUserRole, $contactNumber, $email);

            if (!$createResult['success']) {
                $errorCode = (string)($createResult['error_code'] ?? 'create_failed');
                header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($newUserRole) . '&create_status=' . urlencode($errorCode));
                exit;
            }

            $mailer = new EmailService();
            $mailResult = $mailer->sendTeamMemberWelcomeEmail($email, $username, [
                'username' => $username,
                'role' => $newUserRole,
                'temporary_password' => $temporaryPassword,
                'login_url' => '/lab_sync/index.php?controller=Auth&action=index',
            ]);

            $mailStatus = (string)($mailResult['status'] ?? 'error');
            if ($mailStatus === 'success') {
                $status = 'created_emailed';
            } elseif ($mailStatus === 'skipped') {
                $status = 'created_email_skipped';
            } else {
                $status = 'created_email_failed';
            }

            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($newUserRole) . '&create_status=' . urlencode($status));
            exit;

        // Additional methods for adding, editing, deleting users can be added here
    }

    public function resendInvite($role) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&create_status=invalid_method');
            exit;
        }

        $sessionRole = (string)($_SESSION['user_role'] ?? '');
        if ($sessionRole !== 'admin') {
            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&create_status=unauthorized');
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $targetRole = trim((string)($_POST['role'] ?? $role));
        if ($userId <= 0) {
            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($targetRole) . '&create_status=invalid_input');
            exit;
        }

        $user = $this->adminModel->getTeamUserById($userId);
        if (!$user) {
            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode($targetRole) . '&create_status=user_not_found');
            exit;
        }

        $currentStatus = strtolower(trim((string)($user['status'] ?? '')));
        if (!in_array($currentStatus, ['inactive', 'pending'], true)) {
            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode((string)$user['role']) . '&create_status=invite_not_eligible');
            exit;
        }

        $temporaryPassword = $this->generateTemporaryPassword();
        $resetResult = $this->adminModel->resetUserTemporaryPassword($userId, $temporaryPassword);
        if (!$resetResult['success']) {
            $errorCode = (string)($resetResult['error_code'] ?? 'password_reset_failed');
            header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode((string)$user['role']) . '&create_status=' . urlencode($errorCode));
            exit;
        }

        $mailer = new EmailService();
        $mailResult = $mailer->sendTeamMemberWelcomeEmail((string)$user['email'], (string)$user['username'], [
            'username' => (string)$user['username'],
            'role' => (string)$user['role'],
            'temporary_password' => $temporaryPassword,
            'login_url' => '/lab_sync/index.php?controller=Auth&action=index',
        ]);

        $mailStatus = (string)($mailResult['status'] ?? 'error');
        if ($mailStatus === 'success') {
            $status = 'resent_emailed';
        } elseif ($mailStatus === 'skipped') {
            $status = 'resent_email_skipped';
        } else {
            $status = 'resent_email_failed';
        }

        header('Location: /lab_sync/index.php?controller=administratorController&action=settings&role=' . urlencode((string)$user['role']) . '&create_status=' . urlencode($status));
        exit;
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

    private function generateTemporaryPassword($length = 12) {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
        $charactersLength = strlen($characters);
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $charactersLength - 1);
            $password .= $characters[$index];
        }

        return $password;
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