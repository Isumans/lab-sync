-- Adds first-login password-change enforcement flag for staff onboarding.
ALTER TABLE users
ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password;
