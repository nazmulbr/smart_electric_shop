<?php
// Database Connection Test Script
// Access this file to test your database connection: http://localhost/smart_electric_shop/public/test_db.php

require_once '../config/db.php';

echo "<h2>Database Connection Test</h2>";

if ($conn->connect_error) {
    echo "<p style='color:red;'>❌ Connection Failed: " . $conn->connect_error . "</p>";
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL is running in XAMPP</li>";
    echo "<li>Check if database 'smart_electric_shop' exists</li>";
    echo "<li>Import database_schema.sql file in phpMyAdmin</li>";
    echo "<li>Verify credentials in config/db.php</li>";
    echo "</ol>";
} else {
    echo "<p style='color:green;'>✅ Database connection successful!</p>";

    // Test if tables exist
    echo "<h3>Checking Tables:</h3>";
    $tables = ['User', 'Admin', 'Product', 'Order', 'Warranty', 'RewardPoints', 'ServiceRequest', 'BulkPricing'];
    echo "<ul>";
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<li style='color:green;'>✅ Table '$table' exists</li>";
        } else {
            echo "<li style='color:red;'>❌ Table '$table' NOT found - Please import database_schema.sql</li>";
        }
    }
    echo "</ul>";

    // Check User table structure
    echo "<h3>User Table Structure:</h3>";
    $result = $conn->query("DESCRIBE User");
    if ($result) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Check if any users exist
    echo "<h3>User Count:</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM User");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total users in database: " . $row['count'] . "</p>";
    }

    // Check if any admins exist
    echo "<h3>Admin Count:</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM Admin");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total admins in database: " . $row['count'] . "</p>";
        if ($row['count'] == 0) {
            echo "<div style='background:#fff3cd;border:1px solid #ffc107;padding:15px;margin:10px 0;border-radius:5px;'>";
            echo "<h4 style='color:#856404;margin-top:0;'>⚠️ No Admin Accounts Found</h4>";
            echo "<p style='color:#856404;'>You need to create an admin account to access admin features.</p>";
            echo "<p><strong>Quick Fix:</strong></p>";
            echo "<ol style='color:#856404;'>";
            echo "<li>Click the button below to create a default admin account with full access</li>";
            echo "<li>Or create a custom admin account</li>";
            echo "</ol>";
            echo "<a href='create_default_admin.php' class='btn btn-success' style='margin-top:10px;'>✅ Create Default Admin (admin@smartelectric.com)</a> ";
            echo "<a href='create_admin.php' class='btn btn-warning' style='margin-top:10px;'>🔐 Create Custom Admin Account</a>";
            echo "</div>";
        } else {
            echo "<p style='color:green;'>✅ Admin accounts exist. You can login or create more.</p>";
            echo "<a href='create_admin.php' class='btn btn-info' style='margin-top:10px;'>Create Another Admin</a>";
        }
    }

    // Check Main_Admin
    echo "<h3>Main Admin Count:</h3>";
    $result = $conn->query("SELECT COUNT(*) as count FROM Main_Admin");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Total main admins: " . $row['count'] . "</p>";
        if ($row['count'] == 0) {
            echo "<p style='color:orange;'>⚠️ No Main Admin found. This will be created automatically when you create your first admin.</p>";
        }
    }
}

echo "<br><div style='margin-top:20px;'>";
echo "<a href='index.php' class='btn btn-secondary'>Back to Home</a> ";
echo "<a href='create_default_admin.php' class='btn btn-success'>Create Default Admin</a> ";
echo "<a href='create_admin.php' class='btn btn-primary'>Create Custom Admin</a> ";
echo "<a href='init_database.php' class='btn btn-info'>Database Setup</a>";
echo "</div>";
