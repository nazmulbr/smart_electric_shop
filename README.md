# Smart Electric Shop Management System

A comprehensive e-commerce management system for an electric shop with features including product management, order processing, warranty tracking, reward points, service requests, bulk pricing, and energy consumption calculator.

## Features

### Admin Features
- ✅ Secure admin login
- ✅ Product management (CRUD)
- ✅ User management
- ✅ Order management and status updates
- ✅ Service request management
- ✅ Reward points management
- ✅ Warranty management
- ✅ Bulk pricing configuration

### User Features
- ✅ User registration and login
- ✅ Product browsing
- ✅ Shopping cart management
- ✅ Checkout and order placement
- ✅ Order history and details
- ✅ Warranty status tracking
- ✅ Reward points viewing
- ✅ Service request submission
- ✅ Energy consumption calculator
- ✅ Contact support

### System Features
- ✅ Dynamic bulk pricing discounts
- ✅ Automatic warranty creation on purchase
- ✅ Reward points earning and redemption
- ✅ Warranty expiry notifications
- ✅ Multi-role authentication (Admin, Staff, User)
- ✅ Responsive Bootstrap UI

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)
- Web browser

## Installation

### Step 1: Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `smart_electric_shop`
3. Import the `database_schema.sql` file:
   - Click on the `smart_electric_shop` database
   - Go to "Import" tab
   - Choose `database_schema.sql` file
   - Click "Go"

### Step 2: Configure Database Connection

1. Open `config/db.php`
2. Update database credentials if needed:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";  // Your MySQL password
   $db   = "smart_electric_shop";
   ```

### Step 3: Access the Application

1. Start XAMPP (Apache and MySQL)
2. Navigate to: `http://localhost/smart_electric_shop/public/`
3. You'll see the landing page

### Step 4: Create Admin Account

You need to manually create an admin account in the database:

1. Open phpMyAdmin
2. Select `smart_electric_shop` database
3. Go to `Admin` table
4. Click "Insert" and add:
   - `name`: Your admin name
   - `email`: admin@example.com
   - `password`: Use this PHP code to generate hash:
     ```php
     <?php echo password_hash('your_password', PASSWORD_DEFAULT); ?>
     ```
   - `phone_number`: Your phone
   - `main_id`: 1 (or create Main_Admin first)

Or run this SQL:
```sql
INSERT INTO Main_Admin (main_id, name) VALUES (1, 'Main Admin');
INSERT INTO Admin (main_id, name, email, password, phone_number) 
VALUES (1, 'Admin', 'admin@example.com', '$2y$10$YourHashedPasswordHere', '1234567890');
```

## Default Access

After setup, you can:
- **Register as User:** Click "Register" on the landing page
- **Login as Admin:** Use the admin credentials you created

## Project Structure

```
smart_electric_shop/
├── config/
│   └── db.php                    # Database configuration
├── database_schema.sql           # Complete database schema
├── public/                       # All PHP pages
│   ├── index.php                 # Landing page
│   ├── login.php                 # Login
│   ├── register.php              # Registration
│   ├── admin_dashboard.php       # Admin dashboard
│   ├── user_dashboard.php        # User dashboard
│   ├── manage_products.php       # Product management
│   ├── cart.php                  # Shopping cart
│   ├── checkout.php              # Checkout
│   └── ... (see FEATURE_FILE_MAPPING.md for complete list)
├── FEATURE_FILE_MAPPING.md       # Detailed feature documentation
└── README.md                     # This file
```

## Key Features Implementation

### 1. Shopping Cart
- Session-based cart storage
- Automatic bulk pricing application
- Quantity updates and item removal

### 2. Checkout Process
- Order creation with OrderItems
- Automatic warranty creation
- Reward points earning (1 point per 100 BDT)
- Reward points redemption option
- Stock quantity updates

### 3. Bulk Pricing
- Admin sets minimum quantity and discount percentage
- Automatically applied in cart and checkout
- Highest applicable discount is used

### 4. Warranty Management
- Automatic creation on purchase
- Expiry date calculation
- 30-day expiry warning
- User can view warranty status

### 5. Reward Points
- Earned: 1 point per 100 BDT spent
- Redeemable: 100 points = 10 BDT discount (max 10% of order)
- Admin can manually adjust points

### 6. Service Requests
- Users submit requests linked to warranties
- Admin can update status (Open, In Progress, Resolved, Rejected)
- Request history visible to users

## Customization Guide

For detailed information on how to modify features, see **FEATURE_FILE_MAPPING.md**

### Quick Customization Examples:

**Change Currency:**
- Search and replace "BDT" in all PHP files

**Modify Reward Points Rate:**
- Edit `public/checkout.php` line 85: Change `/100` to your desired rate

**Change Electricity Rate:**
- Edit `public/energy_usage.php` line 12: Change `$rate = 12.5`

**Add Product Images:**
- Create `uploads/products/` directory
- Add file upload in `public/product_form.php`
- Display images in product listings

## Troubleshooting

### Database Connection Error
- Check MySQL is running in XAMPP
- Verify credentials in `config/db.php`
- Ensure database `smart_electric_shop` exists

### Session Issues
- Ensure `session_start()` is called before any output
- Check PHP session configuration in php.ini

### Page Not Found
- Verify you're accessing `http://localhost/smart_electric_shop/public/`
- Check Apache is running in XAMPP

### Login Not Working
- Verify admin account exists in database
- Check password hash is correct
- Ensure email matches exactly

## Security Notes

- Passwords are hashed using `password_hash()`
- SQL injection protection via prepared statements
- Session-based authentication
- Role-based access control

**For Production:**
- Change default passwords
- Use HTTPS
- Implement CSRF protection
- Add input validation
- Use environment variables for database credentials

## Support

For detailed feature documentation and modification guides, refer to **FEATURE_FILE_MAPPING.md**

## License

This project is developed for educational purposes.

## Version

**Version:** 1.0  
**Last Updated:** 2024

---

**Developed by:** Group 8 (Section 17)  
**Project:** Smart Electric Shop Management System

