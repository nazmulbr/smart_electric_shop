-- Smart Electric Shop Management System - Initial Data
-- Run this after importing database_schema.sql to create default admin account

-- Create default Main Admin (if not exists)
INSERT INTO Main_Admin (main_id, name) 
VALUES (1, 'System Administrator')
ON DUPLICATE KEY UPDATE name = 'System Administrator';

-- Create default Admin account
-- Default credentials:
-- Email: admin@smartelectric.com
-- Password: admin123
-- ⚠️ CHANGE THIS PASSWORD AFTER FIRST LOGIN!
INSERT INTO Admin (main_id, name, email, password, phone_number) 
VALUES (
    1, 
    'Administrator', 
    'admin@smartelectric.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    '1234567890'
)
ON DUPLICATE KEY UPDATE 
    name = 'Administrator',
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Note: The password hash above is for 'admin123'
-- To generate a new password hash, use PHP:
-- <?php echo password_hash('your_password', PASSWORD_DEFAULT); ?>

