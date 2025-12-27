# Admin Account Setup Guide

## Problem Fixed

**Issue:** No admin accounts found in the database after importing schema.

**Root Cause:** The original `database_schema.sql` only created table structures but didn't include initial data (admin accounts).

## Solution Implemented

### 1. Updated Database Schema

The `database_schema.sql` now includes:
- ✅ Initial Main_Admin record
- ✅ Default Admin account with credentials
- ✅ Fixed foreign key constraint (ON DELETE SET NULL)

### 2. Created Admin Setup Tools

**Files Created:**
- `public/create_admin.php` - Easy admin account creation page
- `database_initial_data.sql` - Separate file with initial data only

**Files Updated:**
- `database_schema.sql` - Now includes initial admin data
- `public/test_db.php` - Shows link to create admin if none exists
- `public/init_database.php` - Checks for admin accounts and provides setup link

## Default Admin Credentials

If you imported the updated schema, you can use:

**Email:** `admin@smartelectric.com`  
**Password:** `admin123`

⚠️ **IMPORTANT:** Change this password immediately after first login!

## How to Create Admin Account

### Method 1: Using Web Interface (Recommended)

1. **Access the creation page:**
   ```
   http://localhost/smart_electric_shop/public/create_admin.php
   ```

2. **Fill in the form:**
   - Name: Your full name
   - Email: Your email address
   - Password: Choose a strong password (min 6 characters)
   - Phone: Optional

3. **Click "Create Admin Account"**

4. **Login with your new credentials**

### Method 2: Using phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `smart_electric_shop`
3. Go to SQL tab
4. Run this SQL:

```sql
-- Create Main Admin first
INSERT INTO Main_Admin (main_id, name) VALUES (1, 'System Administrator');

-- Create Admin (password: admin123)
INSERT INTO Admin (main_id, name, email, password, phone_number) 
VALUES (
    1, 
    'Administrator', 
    'admin@smartelectric.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    '1234567890'
);
```

### Method 3: Import Initial Data File

1. Open phpMyAdmin
2. Select database: `smart_electric_shop`
3. Go to Import tab
4. Choose file: `database_initial_data.sql`
5. Click Go

## Password Hash Generation

To create a custom password hash, use PHP:

```php
<?php
echo password_hash('your_password_here', PASSWORD_DEFAULT);
?>
```

Or use online tool: https://www.php.net/manual/en/function.password-hash.php

## Verification

After creating admin account:

1. **Check in test_db.php:**
   ```
   http://localhost/smart_electric_shop/public/test_db.php
   ```
   Should show: "✅ Admin accounts exist"

2. **Try to login:**
   ```
   http://localhost/smart_electric_shop/public/login.php
   ```
   Use your admin credentials

3. **Access admin dashboard:**
   After login, you should be redirected to admin dashboard

## Troubleshooting

### Issue: "Email already exists"
**Solution:** Use a different email address or delete the existing admin first

### Issue: "Foreign key constraint fails"
**Solution:** Make sure Main_Admin record exists first (it's created automatically in the updated schema)

### Issue: Can't login after creating admin
**Solution:**
1. Verify password was hashed correctly
2. Check email matches exactly (case-sensitive)
3. Try creating a new admin with different email

### Issue: No Main_Admin record
**Solution:** The create_admin.php page automatically creates Main_Admin if it doesn't exist

## Files Reference

- **Schema with data:** `database_schema.sql` (includes initial admin)
- **Data only:** `database_initial_data.sql` (just initial data)
- **Create admin page:** `public/create_admin.php`
- **Test page:** `public/test_db.php`

## Security Notes

1. **Change default password** immediately after first login
2. **Use strong passwords** (min 8 characters, mix of letters, numbers, symbols)
3. **Don't share admin credentials**
4. **Create separate admin accounts** for different administrators
5. **Regularly audit admin accounts** and remove unused ones

---

**Admin setup is now easy and automated!**

