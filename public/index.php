<?php
session_start();
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if database is set up
if (!checkTableExists('User')) {
    // Redirect to initialization page if tables don't exist
    header('Location: init_database.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } elseif ($_SESSION['role'] === 'staff') {
        header('Location: staff_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smart Electric Shop - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="jumbotron text-center">
            <h1 class="display-4">Smart Electric Shop</h1>
            <p class="lead">Your one-stop shop for all electrical products and services</p>
            <hr class="my-4">
            <p>Manage products, orders, warranties, and more with our comprehensive management system.</p>
            <a class="btn btn-primary btn-lg" href="login.php" role="button">Login</a>
            <a class="btn btn-success btn-lg" href="register.php" role="button">Register</a>
        </div>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Product Management</h5>
                        <p class="card-text">Browse and purchase electrical products with warranty and energy usage information.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Warranty Tracking</h5>
                        <p class="card-text">Track your product warranties and get notified before expiry.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Reward Points</h5>
                        <p class="card-text">Earn and redeem reward points with every purchase.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
