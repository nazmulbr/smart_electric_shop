# Smart Electric Shop Management System - Feature to File Mapping

This document provides a comprehensive guide to all features, their corresponding files, and how to modify them.

## Table of Contents
1. [Database Setup](#database-setup)
2. [Authentication & User Management](#authentication--user-management)
3. [Product Management](#product-management)
4. [Shopping Cart & Checkout](#shopping-cart--checkout)
5. [Order Management](#order-management)
6. [Warranty Management](#warranty-management)
7. [Reward Points System](#reward-points-system)
8. [Service Requests](#service-requests)
9. [Bulk Pricing](#bulk-pricing)
10. [Energy Usage Tool](#energy-usage-tool)
11. [Contact & Support](#contact--support)
12. [Admin Dashboards](#admin-dashboards)
13. [User Dashboards](#user-dashboards)

---

## Database Setup

### File: `database_schema.sql`
**Purpose:** Complete MySQL database schema with all tables, relationships, and foreign keys.

**How to Use:**
1. Import this file into your MySQL database (phpMyAdmin or command line)
2. Database name should be `smart_electric_shop` (or update `config/db.php`)

**How to Modify:**
- Add new tables: Add CREATE TABLE statements
- Modify relationships: Update FOREIGN KEY constraints
- Add indexes: Add INDEX statements for performance

---

## Authentication & User Management

### Files:
- `public/login.php` - Login page for Admin, Staff, and Users
- `public/register.php` - User registration page
- `public/logout.php` - Logout handler
- `config/db.php` - Database connection configuration

### Features:
- Multi-role authentication (Admin, Staff, User)
- Secure password hashing (password_hash)
- Session management

### How to Modify:
- **Change login logic:** Edit `public/login.php` (lines 8-30)
- **Change password requirements:** Modify validation in `public/register.php`
- **Change session timeout:** Add session configuration in each PHP file
- **Database credentials:** Edit `config/db.php`

---

## Product Management

### Files:
- `public/manage_products.php` - Admin/Staff product listing and management
- `public/product_form.php` - Add/Edit product form
- `public/view_products.php` - User-facing product catalog

### Features:
- CRUD operations for products
- Product listing with details (name, description, price, warranty, stock)
- User product browsing

### How to Modify:
- **Add product fields:** 
  1. Update `database_schema.sql` Product table
  2. Modify `public/product_form.php` form fields
  3. Update `public/manage_products.php` display columns
- **Change product display:** Edit `public/view_products.php` (lines 20-40)
- **Add product images:** Add image upload logic in `product_form.php`

---

## Shopping Cart & Checkout

### Files:
- `public/view_products.php` - Product catalog with "Add to Cart"
- `public/cart.php` - Shopping cart management (view, update, remove items)
- `public/checkout.php` - Order placement and payment processing

### Features:
- Session-based shopping cart
- Bulk pricing discount application
- Reward points redemption
- Automatic warranty creation on purchase
- Stock quantity updates

### How to Modify:
- **Change cart storage:** Replace `$_SESSION['cart']` with database table
- **Modify bulk pricing logic:** Edit `public/cart.php` (lines 30-40) and `public/checkout.php` (lines 20-35)
- **Change reward points calculation:** Edit `public/checkout.php` (lines 60-70)
- **Add payment gateway:** Integrate payment API in `public/checkout.php` (line 50)

---

## Order Management

### Files:
- `public/manage_orders.php` - Admin/Staff order listing
- `public/order_items.php` - View order items
- `public/update_order_status.php` - Update payment/order status
- `public/my_orders.php` - User order history
- `public/order_details.php` - Detailed order view for users

### Features:
- View all orders (admin)
- Update order/payment status
- View order items
- User order history

### How to Modify:
- **Add order statuses:** Edit `public/update_order_status.php` (line 20) status array
- **Change order display:** Modify `public/manage_orders.php` table columns
- **Add order filters:** Add WHERE clauses in SQL queries

---

## Warranty Management

### Files:
- `public/manage_warranty.php` - Admin warranty management
- `public/warranty_form.php` - Add/Edit warranty form
- `public/my_warranty.php` - User warranty status view

### Features:
- Warranty CRUD (admin)
- Warranty expiry calculation
- Expiry notifications (30 days before)
- Automatic warranty creation on purchase

### How to Modify:
- **Change expiry notification period:** Edit `public/my_warranty.php` (line 37) - change `30` days
- **Modify warranty calculation:** Edit `public/my_warranty.php` (line 17) date calculation
- **Add warranty types:** Extend Warranty table schema and forms

---

## Reward Points System

### Files:
- `public/reward_points.php` - User reward points view
- `public/manage_rewards.php` - Admin reward points management
- `public/reward_form.php` - Add/Update reward points form

### Features:
- View reward points balance (users)
- Admin can assign/adjust points
- Points earned on purchase (1 point per 100 BDT)
- Points redemption at checkout

### How to Modify:
- **Change points earning rate:** Edit `public/checkout.php` (line 85) - change `/100` calculation
- **Change points redemption rate:** Edit `public/checkout.php` (line 65) - change `/10` conversion
- **Add points expiration:** Add expiry date logic in RewardPoints table and queries

---

## Service Requests

### Files:
- `public/service_request.php` - User service request submission and viewing
- `public/manage_services.php` - Admin service request management

### Features:
- Submit service requests (users)
- Link requests to warranties
- Update request status (admin)
- View request history (users)

### How to Modify:
- **Add request statuses:** Edit `public/manage_services.php` (line 30) status array
- **Add request categories:** Add category field to ServiceRequest table and forms
- **Add email notifications:** Add mail() function calls on status updates

---

## Bulk Pricing

### Files:
- `public/bulk_pricing.php` - Admin bulk pricing rules listing
- `public/bulk_pricing_form.php` - Add/Edit bulk pricing rules
- `public/cart.php` - Applies bulk pricing discounts (lines 30-40)
- `public/checkout.php` - Applies bulk pricing in checkout (lines 20-35)

### Features:
- Set minimum quantity for discounts
- Set discount percentage per product
- Automatic discount application in cart

### How to Modify:
- **Change discount calculation:** Edit discount logic in `public/cart.php` and `public/checkout.php`
- **Add tiered pricing:** Modify SQL query to support multiple tiers (currently uses highest applicable)
- **Add date-based pricing:** Add start/end date fields to BulkPricing table

---

## Energy Usage Tool

### File: `public/energy_usage.php`

### Features:
- Calculate energy consumption (kWh)
- Estimate cost based on usage hours
- Product-based wattage lookup

### How to Modify:
- **Change electricity rate:** Edit `public/energy_usage.php` (line 12) - `$rate = 12.5`
- **Modify calculation formula:** Edit `public/energy_usage.php` (line 14) energy calculation
- **Add cost breakdown:** Add monthly/yearly estimates
- **Link to EnergyUsage table:** Update to use stored product energy data

---

## Contact & Support

### File: `public/contact.php`

### Features:
- Display shop contact information
- Submit support messages (creates service request)

### How to Modify:
- **Update contact details:** Edit `public/contact.php` (lines 20-23)
- **Change message handling:** Modify to use dedicated Contact table instead of ServiceRequest
- **Add email sending:** Integrate mail() or SMTP library

---

## Admin Dashboards

### Files:
- `public/admin_dashboard.php` - Main admin dashboard
- `public/staff_dashboard.php` - Staff dashboard (limited access)

### Features:
- Quick access to all admin functions
- Role-based navigation

### How to Modify:
- **Add dashboard widgets:** Add statistics queries and display cards
- **Change navigation:** Edit button links in dashboard files
- **Add permissions:** Implement role-based access control checks

---

## User Dashboards

### File: `public/user_dashboard.php`

### Features:
- Quick access to all user functions
- Navigation to products, orders, warranty, etc.

### How to Modify:
- **Add user statistics:** Display order count, points balance, etc.
- **Add quick actions:** Add shortcuts for common tasks
- **Customize layout:** Modify Bootstrap grid and card layout

---

## Home/Landing Page

### File: `public/index.php`

### Features:
- Landing page with login/register options
- Auto-redirect if already logged in

### How to Modify:
- **Change landing content:** Edit HTML content in `public/index.php`
- **Add features showcase:** Add more cards or sections
- **Add product highlights:** Display featured products

---

## Database Configuration

### File: `config/db.php`

**How to Modify:**
- **Change database name:** Edit `$db` variable
- **Change credentials:** Update `$host`, `$user`, `$pass`
- **Add connection options:** Add mysqli options for SSL, charset, etc.

---

## Key Relationships (from EER Diagram)

### Implemented Relationships:
1. **Admin → Product** (Manages): `Product.admin_id` → `Admin.admin_id`
2. **User → Order** (Places): `Order.user_id` → `User.user_id`
3. **Order → OrderItem** (Contains): `OrderItem.order_id` → `Order.order_id`
4. **Product → OrderItem** (Included in): `OrderItem.product_id` → `Product.product_id`
5. **User → Warranty** (Can check): `User.warranty_id` → `Warranty.warranty_id`
6. **User → RewardPoints** (Owns): `RewardPoints.user_id` → `User.user_id`
7. **User → ServiceRequest** (Submits): `ServiceRequest.user_id` → `User.user_id`
8. **Product → BulkPricing** (Offers): `BulkPricing.product_id` → `Product.product_id`
9. **Product → EnergyUsage** (Uses): `EnergyUsage.product_id` → `Product.product_id`

---

## Common Customization Tasks

### 1. Add a New Field to Products
1. Update `database_schema.sql`: `ALTER TABLE Product ADD COLUMN new_field VARCHAR(255);`
2. Update `public/product_form.php`: Add form input
3. Update `public/manage_products.php`: Add table column
4. Update `public/view_products.php`: Display new field

### 2. Change Currency
- Search and replace "BDT" with your currency in all display files
- Update number formatting if needed

### 3. Add Email Notifications
- Create `config/mail.php` with SMTP settings
- Add `require_once '../config/mail.php';` to relevant files
- Call mail function on order creation, status updates, etc.

### 4. Add Product Images
1. Create `uploads/products/` directory
2. Add image upload in `public/product_form.php`
3. Store image path in Product table
4. Display images in `public/view_products.php` and `public/cart.php`

### 5. Implement Real Payment Gateway
1. Choose payment provider (Stripe, PayPal, etc.)
2. Add API keys to `config/payment.php`
3. Replace mock payment in `public/checkout.php` with API calls
4. Handle webhooks for payment confirmation

---

## File Structure Summary

```
smart_electric_shop/
├── config/
│   └── db.php                    # Database connection
├── database_schema.sql           # Complete database schema
├── public/
│   ├── index.php                 # Landing page
│   ├── login.php                 # Login page
│   ├── register.php              # User registration
│   ├── logout.php                # Logout handler
│   ├── admin_dashboard.php       # Admin main dashboard
│   ├── staff_dashboard.php       # Staff dashboard
│   ├── user_dashboard.php        # User dashboard
│   ├── manage_products.php       # Product listing (admin)
│   ├── product_form.php          # Add/Edit product
│   ├── view_products.php         # Product catalog (user)
│   ├── cart.php                  # Shopping cart
│   ├── checkout.php              # Checkout and order creation
│   ├── manage_orders.php         # Order management (admin)
│   ├── order_items.php           # View order items
│   ├── update_order_status.php   # Update order status
│   ├── my_orders.php             # User order history
│   ├── order_details.php         # Order details (user)
│   ├── manage_warranty.php       # Warranty management (admin)
│   ├── warranty_form.php         # Add/Edit warranty
│   ├── my_warranty.php           # User warranty view
│   ├── reward_points.php         # User reward points
│   ├── manage_rewards.php        # Reward points management (admin)
│   ├── reward_form.php           # Add/Update reward points
│   ├── service_request.php       # Service requests (user)
│   ├── manage_services.php      # Service request management (admin)
│   ├── bulk_pricing.php          # Bulk pricing rules (admin)
│   ├── bulk_pricing_form.php     # Add/Edit bulk pricing
│   ├── energy_usage.php          # Energy calculator
│   ├── contact.php               # Contact support
│   ├── manage_users.php          # User management (admin)
│   └── user_form.php             # Add/Edit user
└── FEATURE_FILE_MAPPING.md       # This documentation file
```

---

## Testing Checklist

- [ ] Database schema imported successfully
- [ ] Admin can login
- [ ] User can register and login
- [ ] Admin can add/edit/delete products
- [ ] User can browse products
- [ ] User can add products to cart
- [ ] Cart shows bulk pricing discounts
- [ ] User can checkout and place order
- [ ] Order appears in admin order management
- [ ] Warranty is created on purchase
- [ ] User can view warranty status
- [ ] Reward points are earned on purchase
- [ ] User can view reward points
- [ ] Admin can manage reward points
- [ ] User can submit service request
- [ ] Admin can manage service requests
- [ ] Bulk pricing rules work correctly
- [ ] Energy calculator works
- [ ] Contact form works

---

## Support & Maintenance

For questions or issues:
1. Check this documentation first
2. Review the database schema for relationships
3. Check PHP error logs in XAMPP
4. Verify database connection in `config/db.php`
5. Ensure all session_start() calls are present

---

**Last Updated:** 2024
**Version:** 1.0

