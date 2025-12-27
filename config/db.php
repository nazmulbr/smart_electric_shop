<?php
// Enable error reporting for database issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "smart_electric_shop";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    $error_details = "<div style='background:#ffebee;border:2px solid #f44336;padding:20px;margin:20px;font-family:Arial;'>";
    $error_details .= "<h3 style='color:#d32f2f;margin-top:0;'>❌ Database Connection Failed</h3>";
    $error_details .= "<p><strong>Error:</strong> " . htmlspecialchars($conn->connect_error) . "</p>";
    $error_details .= "<p><strong>Error Code:</strong> " . $conn->connect_errno . "</p>";
    $error_details .= "<hr>";
    $error_details .= "<h4>Troubleshooting Steps:</h4>";
    $error_details .= "<ol>";
    $error_details .= "<li>Check if MySQL is running in XAMPP Control Panel</li>";
    $error_details .= "<li>Verify database 'smart_electric_shop' exists in phpMyAdmin</li>";
    $error_details .= "<li>Import database_schema.sql file if database is empty</li>";
    $error_details .= "<li>Check database credentials in config/db.php</li>";
    $error_details .= "<li>Verify MySQL user 'root' has proper permissions</li>";
    $error_details .= "</ol>";
    $error_details .= "<p><strong>Connection Details:</strong><br>";
    $error_details .= "Host: $host<br>";
    $error_details .= "User: $user<br>";
    $error_details .= "Database: $db</p>";
    $error_details .= "</div>";
    die($error_details);
}

// Set charset to utf8
if (!$conn->set_charset("utf8")) {
    echo "<div class='alert alert-warning'>Warning: Error setting charset: " . $conn->error . "</div>";
}
?>
