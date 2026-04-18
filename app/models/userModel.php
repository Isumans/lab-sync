<?php

class userModel {
    private $db;
    private $tableExistsCache = [];
    private $columnExistsCache = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function getStaffProfileData($userId) {
        $userId = intval($userId);
        if ($userId <= 0) {
            return null;
        }

        $select = "SELECT u.user_id, u.username, u.email, u.contact_number, u.role, u.status";
        $joins = " FROM users u";

        $hasProfileDetails = $this->tableExists('user_profile_details');
        $hasPreferences = $this->tableExists('user_preferences');
        $hasTwoFactor = $this->tableExists('user_2fa');

        if ($hasProfileDetails) {
            $select .= ", upd.full_name, upd.date_of_birth, upd.gender, upd.residential_address, upd.avatar_path";
            $joins .= " LEFT JOIN user_profile_details upd ON upd.user_id = u.user_id";
        }

        if ($hasPreferences) {
            $select .= ", pref.email_notifications, pref.sms_alerts, pref.quiet_hours_start, pref.quiet_hours_end, pref.theme_mode";
            $joins .= " LEFT JOIN user_preferences pref ON pref.user_id = u.user_id";
        }

        if ($hasTwoFactor) {
            $select .= ", twofa.is_enabled AS twofa_enabled, twofa.method AS twofa_method";
            $joins .= " LEFT JOIN user_2fa twofa ON twofa.user_id = u.user_id";
        }

        $query = $select . $joins . " WHERE u.user_id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            return null;
        }

        $result = $stmt->get_result();
        $data = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$data) {
            return null;
        }

        // Defaults let UI render even before migration is applied.
        $data['full_name'] = $data['full_name'] ?? $data['username'];
        $data['date_of_birth'] = $data['date_of_birth'] ?? null;
        $data['gender'] = $data['gender'] ?? '';
        $data['residential_address'] = $data['residential_address'] ?? '';
        $data['avatar_path'] = $data['avatar_path'] ?? '';
        $data['email_notifications'] = isset($data['email_notifications']) ? intval($data['email_notifications']) : 1;
        $data['sms_alerts'] = isset($data['sms_alerts']) ? intval($data['sms_alerts']) : 0;
        $data['quiet_hours_start'] = $data['quiet_hours_start'] ?? '22:00:00';
        $data['quiet_hours_end'] = $data['quiet_hours_end'] ?? '07:00:00';
        $data['theme_mode'] = $data['theme_mode'] ?? 'System';
        $data['twofa_enabled'] = isset($data['twofa_enabled']) ? intval($data['twofa_enabled']) : 0;
        $data['twofa_method'] = $data['twofa_method'] ?? 'TOTP';

        return $data;
    }

    public function ensureSupportRows($userId, $fallbackName) {
        $userId = intval($userId);
        if ($userId <= 0) {
            return;
        }

        if ($this->tableExists('user_profile_details')) {
            $stmt = $this->db->prepare(
                "INSERT INTO user_profile_details (user_id, full_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE full_name = COALESCE(full_name, VALUES(full_name))"
            );
            if ($stmt) {
                $stmt->bind_param('is', $userId, $fallbackName);
                $stmt->execute();
                $stmt->close();
            }
        }

        if ($this->tableExists('user_preferences')) {
            $stmt = $this->db->prepare(
                "INSERT INTO user_preferences (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id = user_id"
            );
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->close();
            }
        }

        if ($this->tableExists('user_2fa')) {
            $stmt = $this->db->prepare(
                "INSERT INTO user_2fa (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id = user_id"
            );
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    public function updateStaffProfile($userId, array $payload) {
        $userId = intval($userId);
        if ($userId <= 0) {
            return false;
        }

        $username = trim((string)($payload['full_name'] ?? ''));
        $email = trim((string)($payload['email'] ?? ''));
        $contact = trim((string)($payload['contact_number'] ?? ''));
        $dob = trim((string)($payload['date_of_birth'] ?? ''));
        $gender = trim((string)($payload['gender'] ?? ''));
        $address = trim((string)($payload['residential_address'] ?? ''));

        if ($username === '' || $email === '') {
            return false;
        }

        $userStmt = $this->db->prepare(
            "UPDATE users SET username = ?, email = ?, contact_number = ? WHERE user_id = ?"
        );
        if (!$userStmt) {
            return false;
        }

        $userStmt->bind_param('sssi', $username, $email, $contact, $userId);
        $ok = $userStmt->execute();
        $userStmt->close();

        if (!$ok) {
            return false;
        }

        if ($this->tableExists('user_profile_details')) {
            $profileStmt = $this->db->prepare(
                "INSERT INTO user_profile_details (user_id, full_name, date_of_birth, gender, residential_address)
                 VALUES (?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''))
                 ON DUPLICATE KEY UPDATE
                    full_name = VALUES(full_name),
                    date_of_birth = VALUES(date_of_birth),
                    gender = VALUES(gender),
                    residential_address = VALUES(residential_address)"
            );
            if ($profileStmt) {
                $profileStmt->bind_param('issss', $userId, $username, $dob, $gender, $address);
                $profileStmt->execute();
                $profileStmt->close();
            }
        }

        return true;
    }

    public function updateUserPreferences($userId, array $payload) {
        if (!$this->tableExists('user_preferences')) {
            return false;
        }

        $userId = intval($userId);
        $emailNotifications = !empty($payload['email_notifications']) ? 1 : 0;
        $smsAlerts = !empty($payload['sms_alerts']) ? 1 : 0;
        $quietStart = trim((string)($payload['quiet_hours_start'] ?? ''));
        $quietEnd = trim((string)($payload['quiet_hours_end'] ?? ''));
        $themeMode = trim((string)($payload['theme_mode'] ?? 'System'));

        $allowedThemes = ['Light', 'Dark', 'System'];
        if (!in_array($themeMode, $allowedThemes, true)) {
            $themeMode = 'System';
        }

        $stmt = $this->db->prepare(
            "INSERT INTO user_preferences (user_id, email_notifications, sms_alerts, quiet_hours_start, quiet_hours_end, theme_mode)
             VALUES (?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?)
             ON DUPLICATE KEY UPDATE
                email_notifications = VALUES(email_notifications),
                sms_alerts = VALUES(sms_alerts),
                quiet_hours_start = VALUES(quiet_hours_start),
                quiet_hours_end = VALUES(quiet_hours_end),
                theme_mode = VALUES(theme_mode)"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iiisss', $userId, $emailNotifications, $smsAlerts, $quietStart, $quietEnd, $themeMode);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updateTwoFactor($userId, $enabled) {
        if (!$this->tableExists('user_2fa')) {
            return false;
        }

        $userId = intval($userId);
        $enabledFlag = $enabled ? 1 : 0;

        $stmt = $this->db->prepare(
            "INSERT INTO user_2fa (user_id, is_enabled, method)
             VALUES (?, ?, 'TOTP')
             ON DUPLICATE KEY UPDATE
                is_enabled = VALUES(is_enabled)"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $userId, $enabledFlag);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function listActiveSessions($userId) {
        if (!$this->tableExists('user_sessions')) {
            return [];
        }

        $userId = intval($userId);
        $stmt = $this->db->prepare(
            "SELECT user_session_id, session_token, device_label, ip_address, user_agent, logged_in_at, last_activity
             FROM user_sessions
             WHERE user_id = ? AND is_active = 1
             ORDER BY last_activity DESC"
        );
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return is_array($rows) ? $rows : [];
    }

    public function revokeSession($userId, $sessionId, $currentToken) {
        if (!$this->tableExists('user_sessions')) {
            return false;
        }

        $userId = intval($userId);
        $sessionId = intval($sessionId);
        $currentToken = (string)$currentToken;

        $stmt = $this->db->prepare(
            "UPDATE user_sessions
             SET is_active = 0, logged_out_at = NOW()
             WHERE user_session_id = ? AND user_id = ? AND session_token <> ?"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('iis', $sessionId, $userId, $currentToken);
        $ok = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $ok && $affected > 0;
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        $userId = intval($userId);
        $stmt = $this->db->prepare("SELECT password FROM users WHERE user_id = ? LIMIT 1");
        if (!$stmt) {
            return ['ok' => false, 'message' => 'Password update failed.'];
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            return ['ok' => false, 'message' => 'Password update failed.'];
        }

        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$user || !isset($user['password']) || !password_verify($currentPassword, $user['password'])) {
            return ['ok' => false, 'message' => 'Current password is incorrect.'];
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $this->db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        if (!$updateStmt) {
            return ['ok' => false, 'message' => 'Password update failed.'];
        }

        $updateStmt->bind_param('si', $hash, $userId);
        $ok = $updateStmt->execute();
        $updateStmt->close();

        if (!$ok) {
            return ['ok' => false, 'message' => 'Password update failed.'];
        }

        if ($this->columnExists('users', 'must_change_password')) {
            $flagStmt = $this->db->prepare("UPDATE users SET must_change_password = 0 WHERE user_id = ?");
            if ($flagStmt) {
                $flagStmt->bind_param('i', $userId);
                $flagStmt->execute();
                $flagStmt->close();
            }
        }

        return ['ok' => true, 'message' => 'Password updated successfully.'];
    }

    public function touchSession($token) {
        if (!$this->tableExists('user_sessions')) {
            return;
        }

        $token = trim((string)$token);
        if ($token === '') {
            return;
        }

        $stmt = $this->db->prepare(
            "UPDATE user_sessions SET last_activity = NOW() WHERE session_token = ? AND is_active = 1"
        );
        if ($stmt) {
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $stmt->close();
        }
    }

    private function tableExists($tableName) {
        if (isset($this->tableExistsCache[$tableName])) {
            return $this->tableExistsCache[$tableName];
        }

        $escaped = $this->db->real_escape_string($tableName);
        $result = $this->db->query("SHOW TABLES LIKE '" . $escaped . "'");
        $exists = $result && $result->num_rows > 0;
        $this->tableExistsCache[$tableName] = $exists;

        return $exists;
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