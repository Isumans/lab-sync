-- Staff profile and security tables (minimal full-stack)

CREATE TABLE IF NOT EXISTS user_profile_details (
  profile_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  full_name VARCHAR(120) NULL,
  date_of_birth DATE NULL,
  gender ENUM('Male', 'Female', 'Other') NULL,
  residential_address VARCHAR(255) NULL,
  avatar_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_profile_details_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_preferences (
  pref_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  email_notifications TINYINT(1) NOT NULL DEFAULT 1,
  sms_alerts TINYINT(1) NOT NULL DEFAULT 0,
  quiet_hours_start TIME NULL,
  quiet_hours_end TIME NULL,
  theme_mode ENUM('Light', 'Dark', 'System') NOT NULL DEFAULT 'System',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_preferences_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_2fa (
  twofa_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  is_enabled TINYINT(1) NOT NULL DEFAULT 0,
  method ENUM('TOTP', 'SMS', 'EMAIL') NOT NULL DEFAULT 'TOTP',
  secret_key VARCHAR(255) NULL,
  recovery_codes TEXT NULL,
  last_verified_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_2fa_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_sessions (
  user_session_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  php_session_id VARCHAR(128) NOT NULL,
  session_token CHAR(64) NOT NULL,
  device_label VARCHAR(120) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  logged_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_activity DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  logged_out_at DATETIME NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT uq_user_sessions_token UNIQUE (session_token),
  KEY idx_user_sessions_user_active (user_id, is_active),
  KEY idx_user_sessions_last_activity (last_activity),
  CONSTRAINT fk_user_sessions_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO user_profile_details (user_id, full_name)
SELECT u.user_id, u.username
FROM users u
LEFT JOIN user_profile_details p ON p.user_id = u.user_id
WHERE p.user_id IS NULL;

INSERT INTO user_preferences (user_id)
SELECT u.user_id
FROM users u
LEFT JOIN user_preferences p ON p.user_id = u.user_id
WHERE p.user_id IS NULL;

INSERT INTO user_2fa (user_id)
SELECT u.user_id
FROM users u
LEFT JOIN user_2fa t ON t.user_id = u.user_id
WHERE t.user_id IS NULL;
