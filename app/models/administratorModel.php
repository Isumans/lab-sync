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

    // ─── Lab Configuration ───────────────────────────────────────────────────

    public function getLabConfig() {
        $result = $this->db->query("SELECT * FROM lab_configuration WHERE id = 1 LIMIT 1");
        return $result ? $result->fetch_assoc() : null;
    }

    public function saveLabConfig(array $data) {
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

}
?>