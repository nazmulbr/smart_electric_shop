-- database_admin_reset.sql
-- Upsert a single primary admin account (admin_id = 1)
-- Default credentials set by this script:
-- Email: admin@smartelectric.com
-- Password: admin123
-- IMPORTANT: Change the password immediately after logging in.

-- Ensure the Main_Admin row exists (foreign key target)
INSERT INTO Main_Admin (main_id, name)
VALUES (1, 'System Administrator')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO Admin (admin_id, main_id, name, email, password, phone_number)
VALUES (
  1,
  1,
  'Administrator',
  'admin@smartelectric.com',
  'admin123',
  '1234567890'
)
ON DUPLICATE KEY UPDATE
  main_id = VALUES(main_id),
  name = VALUES(name),
  email = VALUES(email),
  password = VALUES(password),
  phone_number = VALUES(phone_number);

SELECT admin_id, main_id, name, email FROM Admin WHERE admin_id = 1;
