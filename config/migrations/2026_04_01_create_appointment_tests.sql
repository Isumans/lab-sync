-- Creates a junction table for many-tests-per-appointment workflow.
CREATE TABLE IF NOT EXISTS appointment_tests (
    appointment_test_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    test_id INT NOT NULL,
    status ENUM('PENDING','IN_PROGRESS','COMPLETED','AUTHORIZED','PRINTED') NOT NULL DEFAULT 'PENDING',
    status_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    assigned_to INT(11) DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    authorized_by INT(11) DEFAULT NULL,
    authorized_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointment_tests_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_appointment_tests_test
        FOREIGN KEY (test_id) REFERENCES tests(test_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    UNIQUE KEY uk_appointment_test (appointment_id, test_id),
    KEY idx_appointment_id (appointment_id),
    KEY idx_test_id (test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
