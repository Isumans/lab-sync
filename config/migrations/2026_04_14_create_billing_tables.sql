CREATE TABLE IF NOT EXISTS bills (
    bill_id INT(11) NOT NULL AUTO_INCREMENT,
    bill_number VARCHAR(50) NOT NULL,
    appointment_id INT(11) NOT NULL,
    patient_id INT(11) NOT NULL,
    bill_date DATE NOT NULL,
    due_date DATE DEFAULT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance_due DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('DRAFT','PENDING','PARTIALLY_PAID','PAID','CANCELLED') NOT NULL DEFAULT 'DRAFT',
    status_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    created_by INT(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (bill_id),
    UNIQUE KEY uk_bills_bill_number (bill_number),
    UNIQUE KEY uk_bills_appointment_id (appointment_id),
    KEY idx_bills_patient_id (patient_id),
    KEY idx_bills_bill_date (bill_date),
    KEY idx_bills_status (status),
    CONSTRAINT fk_bills_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_bills_patient
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_bills_created_by
        FOREIGN KEY (created_by) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS bill_items (
    bill_item_id INT(11) NOT NULL AUTO_INCREMENT,
    bill_id INT(11) NOT NULL,
    test_id INT(11) DEFAULT NULL,
    test_name VARCHAR(150) NOT NULL,
    quantity INT(3) NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (bill_item_id),
    KEY idx_bill_items_bill_id (bill_id),
    KEY idx_bill_items_test_id (test_id),
    CONSTRAINT fk_bill_items_bill
        FOREIGN KEY (bill_id) REFERENCES bills(bill_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bill_items_test
        FOREIGN KEY (test_id) REFERENCES tests(test_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT(11) NOT NULL AUTO_INCREMENT,
    bill_id INT(11) NOT NULL,
    payment_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('CASH','CARD','TRANSFER') NOT NULL,
    reference_number VARCHAR(100) DEFAULT NULL,
    payment_date DATE NOT NULL,
    notes TEXT DEFAULT NULL,
    received_by INT(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (payment_id),
    KEY idx_payments_bill_id (bill_id),
    KEY idx_payments_payment_date (payment_date),
    KEY idx_payments_method (payment_method),
    CONSTRAINT fk_payments_bill
        FOREIGN KEY (bill_id) REFERENCES bills(bill_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_payments_received_by
        FOREIGN KEY (received_by) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
