-- Enforce unique test names in tests table.
-- This migration is idempotent and skips index creation when duplicates exist.

SET @duplicate_count := (
    SELECT COUNT(*)
    FROM (
        SELECT LOWER(TRIM(test_name)) AS normalized_name, COUNT(*) AS c
        FROM tests
        GROUP BY LOWER(TRIM(test_name))
        HAVING COUNT(*) > 1
    ) dup
);

SET @index_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'tests'
      AND index_name = 'uk_tests_test_name'
);

SET @ddl := IF(
    @index_exists > 0 OR @duplicate_count > 0,
    'SELECT 1',
    'ALTER TABLE tests ADD UNIQUE KEY uk_tests_test_name (test_name)'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT
    @duplicate_count AS duplicate_name_groups,
    @index_exists AS index_already_exists,
    IF(@index_exists > 0, 'Index already exists.', IF(@duplicate_count > 0, 'Skipped: clean duplicate test names before rerunning migration.', 'Unique index created.')) AS migration_status;
