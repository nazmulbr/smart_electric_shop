<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "smart_electric_shop";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error . "<br>Please ensure:<br>1. MySQL is running in XAMPP<br>2. Database 'smart_electric_shop' exists<br>3. Import database_schema.sql file");
}

// Set charset to utf8
$conn->set_charset("utf8");
?>
