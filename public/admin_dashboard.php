<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    <title>Admin Dashboard - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <h3>Welcome, Admin <?=$name?></h3>
        <div class="mt-4">
            <a href="manage_products.php" class="btn btn-info">Manage Products</a>
            <a href="manage_users.php" class="btn btn-secondary">Manage Users</a>
            <a href="manage_orders.php" class="btn btn-success">View Orders</a>
            <a href="manage_services.php" class="btn btn-warning">Service Requests</a>
            <a href="manage_rewards.php" class="btn btn-primary">Reward Points</a>
            <a href="manage_warranty.php" class="btn btn-info">Manage Warranties</a>
            <a href="bulk_pricing.php" class="btn btn-secondary">Bulk Pricing</a>
            <a href="logout.php" class="btn btn-danger float-right">Logout</a>
        </div>
    </div>
</body>
</html>

