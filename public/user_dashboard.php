<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';
$name = $_SESSION['name'];
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html>

<head>
    <title>User Dashboard - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h3>Welcome, <?= $name ?></h3>
        <div class="mt-4">
            <a href="index.php" class="btn btn-info">Browse Products</a>
            <a href="cart.php" class="btn btn-info">Shopping Cart</a>
            <a href="my_orders.php" class="btn btn-success">My Orders</a>
            <a href="service_request.php" class="btn btn-dark">Service Requests</a>
            <a href="energy_usage.php" class="btn btn-secondary">Energy Calculator</a>
            <a href="contact.php" class="btn btn-secondary">Contact Support</a>
            <a href="logout.php" class="btn btn-danger float-right">Logout</a>
        </div>
    </div>
</body>

</html>