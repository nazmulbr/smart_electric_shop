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
            echo "<p style='color:orange;'>⚠️ No admin accounts found. You need to create one manually.</p>";
        }
    }
}

echo "<br><a href='index.php'>Back to Home</a>";
?>

