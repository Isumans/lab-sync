ALTER TABLE tests
    ADD COLUMN deleted_at DATETIME NULL,
    ADD COLUMN deleted_by INT(11) NULL,
    ADD INDEX idx_tests_deleted_at (deleted_at),
    ADD INDEX idx_tests_deleted_by (deleted_by);
