<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$name = $_SESSION['name'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light text-dark">
    <div class="container mt-4">
        <h3>Welcome, Staff <?=$name?></h3>
        <div class="mt-4">
            <a href="manage_products.php" class="btn btn-info">Manage Products</a>
            <a href="manage_orders.php" class="btn btn-success">View Orders</a>
            <a href="logout.php" class="btn btn-danger float-right">Logout</a>
        </div>
    </div>
</body>
</html>

