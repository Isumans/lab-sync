ALTER TABLE appointment
  ADD COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  ADD COLUMN payment_reference VARCHAR(100) DEFAULT NULL;
