<?php
// CRUD Debug Helper - Use this to test database operations
// Access: http://localhost/smart_electric_shop/public/debug_crud.php

require_once '../config/db.php';

echo "<h2>CRUD Operations Debug Test</h2>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#4CAF50;color:white;}</style>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
if ($conn->connect_error) {
    echo "<p class='error'>❌ Connection Failed: " . $conn->connect_error . "</p>";
    exit;
} else {
    echo "<p class='success'>✅ Database connection successful!</p>";
}

// Test 2: Check if User table exists and structure
echo "<h3>2. User Table Structure</h3>";
$result = $conn->query("DESCRIBE User");
if ($result) {
    echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Error: " . $conn->error . "</p>";
}

// Test 3: Test INSERT operation
echo "<h3>3. Test INSERT Operation</h3>";
$test_name = "Test User " . time();
$test_email = "test" . time() . "@example.com";
$test_password = password_hash("test123", PASSWORD_DEFAULT);
$test_phone = "1234567890";

$stmt = $conn->prepare("INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param('ssss', $test_name, $test_email, $test_password, $test_phone);
    if ($stmt->execute()) {
        $inserted_id = $conn->insert_id;
        echo "<p class='success'>✅ INSERT successful! New user ID: " . $inserted_id . "</p>";
        
        // Test 4: Test SELECT operation
        echo "<h3>4. Test SELECT Operation</h3>";
        $select_stmt = $conn->prepare("SELECT * FROM User WHERE user_id = ?");
        $select_stmt->bind_param('i', $inserted_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo "<p class='success'>✅ SELECT successful!</p>";
            echo "<table><tr><th>Field</th><th>Value</th></tr>";
            foreach ($row as $key => $value) {
                if ($key != 'password') {
                    echo "<tr><td>" . $key . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
                } else {
                    echo "<tr><td>" . $key . "</td><td>***HIDDEN***</td></tr>";
                }
            }
            echo "</table>";
        }
        $select_stmt->close();
        
        // Test 5: Test UPDATE operation
        echo "<h3>5. Test UPDATE Operation</h3>";
        $new_name = "Updated Test User";
        $update_stmt = $conn->prepare("UPDATE User SET name = ? WHERE user_id = ?");
        $update_stmt->bind_param('si', $new_name, $inserted_id);
        if ($update_stmt->execute()) {
            echo "<p class='success'>✅ UPDATE successful!</p>";
        } else {
            echo "<p class='error'>❌ UPDATE failed: " . $conn->error . "</p>";
        }
        $update_stmt->close();
        
        // Test 6: Test DELETE operation
        echo "<h3>6. Test DELETE Operation</h3>";
        $delete_stmt = $conn->prepare("DELETE FROM User WHERE user_id = ?");
        $delete_stmt->bind_param('i', $inserted_id);
        if ($delete_stmt->execute()) {
            echo "<p class='success'>✅ DELETE successful!</p>";
        } else {
            echo "<p class='error'>❌ DELETE failed: " . $conn->error . "</p>";
        }
        $delete_stmt->close();
        
    } else {
        echo "<p class='error'>❌ INSERT failed: " . $conn->error . "</p>";
    }
    $stmt->close();
} else {
    echo "<p class='error'>❌ Prepare failed: " . $conn->error . "</p>";
}

// Test 7: Check current user count
echo "<h3>7. Current Database Status</h3>";
$tables = ['User', 'Admin', 'Product', 'Order', 'Warranty', 'RewardPoints', 'ServiceRequest'];
echo "<table><tr><th>Table</th><th>Record Count</th></tr>";
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<tr><td>$table</td><td>" . $row['count'] . "</td></tr>";
    } else {
        echo "<tr><td>$table</td><td class='error'>Error: " . $conn->error . "</td></tr>";
    }
}
echo "</table>";

echo "<br><a href='index.php'>Back to Home</a> | <a href='test_db.php'>Database Test</a>";
?>

