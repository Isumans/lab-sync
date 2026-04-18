<?php
class administratorModel {
    private $db;
    private $columnExistsCache = [];

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
        $username = trim((string)$username);
        $role = trim((string)$role);
        $contact_number = trim((string)$contact_number);
        $email = trim((string)$email);
        $passwordHash = password_hash((string)$password, PASSWORD_BCRYPT);

        if ($username === '' || $passwordHash === false || $role === '' || $email === '') {
            return [
                'success' => false,
                'error_code' => 'invalid_input',
                'message' => 'Missing required account fields.',
            ];
        }

        $hasMustChangePassword = $this->columnExists('users', 'must_change_password');
        if ($hasMustChangePassword) {
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, password, role, contact_number, email, must_change_password) VALUES (?, ?, ?, ?, ?, 1)"
            );
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO users (username, password, role, contact_number, email) VALUES (?, ?, ?, ?, ?)"
            );
        }

        if (!$stmt) {
            return [
                'success' => false,
                'error_code' => 'prepare_failed',
                'message' => 'Failed to prepare user creation query.',
            ];
        }

        $stmt->bind_param("sssss", $username, $passwordHash, $role, $contact_number, $email);
        $ok = $stmt->execute();
        $errno = (int)$stmt->errno;
        $error = (string)$stmt->error;
        $insertId = (int)$stmt->insert_id;
        $stmt->close();

        if (!$ok) {
            if ($errno === 1062) {
                return [
                    'success' => false,
                    'error_code' => 'duplicate_email',
                    'message' => 'A user with this email already exists.',
                ];
            }

            return [
                'success' => false,
                'error_code' => 'insert_failed',
                'message' => 'Failed to create user: ' . $error,
            ];
        }

        return [
            'success' => true,
            'user_id' => $insertId,
            'error_code' => null,
            'message' => 'User created successfully.',
        ];
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

    public function getTeamUserById($userId) {
        $stmt = $this->db->prepare(
            "SELECT user_id, username, email, role, status FROM users WHERE user_id = ? AND role IN ('admin', 'receptionist', 'technician') LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $user ?: null;
    }

    public function resetUserTemporaryPassword($userId, $temporaryPassword) {
        $passwordHash = password_hash((string)$temporaryPassword, PASSWORD_BCRYPT);
        if ($passwordHash === false) {
            return [
                'success' => false,
                'error_code' => 'password_reset_failed',
                'message' => 'Failed to generate password hash.',
            ];
        }

        $hasMustChangePassword = $this->columnExists('users', 'must_change_password');
        if ($hasMustChangePassword) {
            $stmt = $this->db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE user_id = ?");
        } else {
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        }

        if (!$stmt) {
            return [
                'success' => false,
                'error_code' => 'prepare_failed',
                'message' => 'Failed to prepare password reset query.',
            ];
        }

        $stmt->bind_param("si", $passwordHash, $userId);
        $ok = $stmt->execute();
        $error = (string)$stmt->error;
        $affectedRows = (int)$stmt->affected_rows;
        $stmt->close();

        if (!$ok) {
            return [
                'success' => false,
                'error_code' => 'password_reset_failed',
                'message' => 'Failed to reset user password: ' . $error,
            ];
        }

        if ($affectedRows < 1) {
            return [
                'success' => false,
                'error_code' => 'user_not_found',
                'message' => 'No matching user found to reset password.',
            ];
        }

        return [
            'success' => true,
            'error_code' => null,
            'message' => 'Temporary password reset successfully.',
        ];
    }

    // ─── Lab Configuration ───────────────────────────────────────────────────

    public function getLabConfig() {
        $result = $this->db->query("SELECT * FROM lab_configuration WHERE id = 1 LIMIT 1");
        return $result ? $result->fetch_assoc() : null;
    }

    public function saveLabConfig(array $data) {
        if (!$this->isValidLabConfigData($data)) {
            return false;
        }

        $sql = "INSERT INTO lab_configuration
                    (id, lab_name, accreditation, address, phone, email, logo_path,
                     hours_mon_fri_open, hours_mon_fri_close, hours_mon_fri_enabled,
                     hours_sat_open, hours_sat_close, hours_sat_enabled,
                     hours_sun_open, hours_sun_close, hours_sun_enabled,
                     allow_walkins, auto_email_reports)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    lab_name            = VALUES(lab_name),
                    accreditation       = VALUES(accreditation),
                    address             = VALUES(address),
                    phone               = VALUES(phone),
                    email               = VALUES(email),
                    logo_path           = VALUES(logo_path),
                    hours_mon_fri_open  = VALUES(hours_mon_fri_open),
                    hours_mon_fri_close = VALUES(hours_mon_fri_close),
                    hours_mon_fri_enabled = VALUES(hours_mon_fri_enabled),
                    hours_sat_open      = VALUES(hours_sat_open),
                    hours_sat_close     = VALUES(hours_sat_close),
                    hours_sat_enabled   = VALUES(hours_sat_enabled),
                    hours_sun_open      = VALUES(hours_sun_open),
                    hours_sun_close     = VALUES(hours_sun_close),
                    hours_sun_enabled   = VALUES(hours_sun_enabled),
                    allow_walkins       = VALUES(allow_walkins),
                    auto_email_reports  = VALUES(auto_email_reports)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "ssssssssississiii",
            $data['lab_name'],
            $data['accreditation'],
            $data['address'],
            $data['phone'],
            $data['email'],
            $data['logo_path'],
            $data['hours_mon_fri_open'],
            $data['hours_mon_fri_close'],
            $data['hours_mon_fri_enabled'],
            $data['hours_sat_open'],
            $data['hours_sat_close'],
            $data['hours_sat_enabled'],
            $data['hours_sun_open'],
            $data['hours_sun_close'],
            $data['hours_sun_enabled'],
            $data['allow_walkins'],
            $data['auto_email_reports']
        );
        return $stmt->execute();
    }

    private function isValidLabConfigData(array $data) {
        if (empty($data['lab_name']) || strlen((string)$data['lab_name']) > 120) {
            return false;
        }
        if (empty($data['accreditation']) || strlen((string)$data['accreditation']) > 80) {
            return false;
        }
        if (empty($data['address']) || strlen((string)$data['address']) > 255) {
            return false;
        }
        if (!filter_var((string)($data['email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (!preg_match('/^[0-9+()\-\s]{7,25}$/', (string)($data['phone'] ?? ''))) {
            return false;
        }

        return $this->isValidTime((string)($data['hours_mon_fri_open'] ?? ''))
            && $this->isValidTime((string)($data['hours_mon_fri_close'] ?? ''))
            && $this->isValidTime((string)($data['hours_sat_open'] ?? ''))
            && $this->isValidTime((string)($data['hours_sat_close'] ?? ''))
            && $this->isValidTime((string)($data['hours_sun_open'] ?? ''))
            && $this->isValidTime((string)($data['hours_sun_close'] ?? ''));
    }

    private function isValidTime($value) {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1;
    }

    // ─── General Settings ────────────────────────────────────────────────────

    public function getGeneralSettings() {
        $result = $this->db->query("SELECT * FROM general_settings WHERE id = 1 LIMIT 1");
        return $result ? $result->fetch_assoc() : null;
    }

    public function saveGeneralSettings(array $data) {
        $sql = "INSERT INTO general_settings
                    (id, sms_alerts, email_reports, password_policy,
                     session_timeout, language, timezone, currency, date_format)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    sms_alerts       = VALUES(sms_alerts),
                    email_reports    = VALUES(email_reports),
                    password_policy  = VALUES(password_policy),
                    session_timeout  = VALUES(session_timeout),
                    language         = VALUES(language),
                    timezone         = VALUES(timezone),
                    currency         = VALUES(currency),
                    date_format      = VALUES(date_format)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param(
            "iisissss",
            $data['sms_alerts'],
            $data['email_reports'],
            $data['password_policy'],
            $data['session_timeout'],
            $data['language'],
            $data['timezone'],
            $data['currency'],
            $data['date_format']
        );
        return $stmt->execute();
    }

    private function columnExists($tableName, $columnName) {
        $cacheKey = $tableName . '.' . $columnName;
        if (isset($this->columnExistsCache[$cacheKey])) {
            return $this->columnExistsCache[$cacheKey];
        }

        $table = $this->db->real_escape_string($tableName);
        $column = $this->db->real_escape_string($columnName);
        $result = $this->db->query("SHOW COLUMNS FROM `" . $table . "` LIKE '" . $column . "'");
        $exists = $result && $result->num_rows > 0;
        $this->columnExistsCache[$cacheKey] = $exists;

        return $exists;
    }

}
?>