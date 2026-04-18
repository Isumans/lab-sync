<?php
class AuthModel {
    private $db;
    private $tableExistsCache = [];
    private $columnExistsCache = [];
    public function __construct($db) { $this->db = $db; }

    public function getUserByUsername($username) {
        $result = $this->db->query("SELECT * FROM users WHERE username = '$username'");
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();

        }else{
            return false;
        }
    }
    // public function getAllTests() {
    //     $result = $this->db->query("SELECT * FROM tests");
    //     return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    // }

    // public function addTest($name, $category, $price) {
    //     $stmt = $this->db->prepare("INSERT INTO tests (test_name, category, price) VALUES (?, ?, ?)");
    //     $stmt->bind_param("ssd", $name, $category, $price);
    //     return $stmt->execute();
    // }
   
    public function verifyUser($email, $password) {
    // Step 1: Check database connection
    if (!$this->db) {
        die("Database connection not established properly.");
    }

    // Step 2: The SQL query - authenticate using email
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $this->db->prepare($query);

    // Step 3: Check if prepare() failed
    if (!$stmt) {
        die("Prepare failed: " . $this->db->error . " | Query: " . $query);
    }

    // Step 4: Proceed only if prepare() worked
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (isset(($user['password'])) && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

    public function startTrackedSession($userId, $phpSessionId, $sessionToken, $deviceLabel, $ipAddress, $userAgent) {
        if (!$this->tableExists('user_sessions')) {
            return true;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO user_sessions
                (user_id, php_session_id, session_token, device_label, ip_address, user_agent, logged_in_at, last_activity, is_active)
             VALUES
                (?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NOW(), NOW(), 1)"
        );
        if (!$stmt) {
            return false;
        }

        $userId = intval($userId);
        $stmt->bind_param('isssss', $userId, $phpSessionId, $sessionToken, $deviceLabel, $ipAddress, $userAgent);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function closeTrackedSession($sessionToken) {
        if (!$this->tableExists('user_sessions')) {
            return true;
        }

        $stmt = $this->db->prepare(
            "UPDATE user_sessions
             SET is_active = 0, logged_out_at = NOW(), last_activity = NOW()
             WHERE session_token = ? AND is_active = 1"
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $sessionToken);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function isPasswordChangeRequired($userId) {
        $userId = intval($userId);
        if ($userId <= 0) {
            return false;
        }

        if (!$this->columnExists('users', 'must_change_password')) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT must_change_password FROM users WHERE user_id = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return isset($row['must_change_password']) && intval($row['must_change_password']) === 1;
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