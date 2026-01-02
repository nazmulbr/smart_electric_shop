<?php
// Require staff-only access
$require_role = 'staff';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';
$name = $current_admin_name;
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - Smart Electric Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-section {
            background: rgba(255,255,255,0.98);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.11);
            padding: 40px 30px;
            margin-top: 50px;
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .dashboard-header h2 {
            color: #28a745;
            font-weight: 700;
        }
        .staff-info-card {
            background: linear-gradient(125deg, #28a745, #20c997);
            color: #fff;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 4px 16px rgba(40, 167, 69, 0.11);
            text-align: center;
        }
        .staff-info-card .staff-name {
            font-size: 1.25rem;
            font-weight: 600;
        }
        .staff-info-card .staff-email {
            font-size: 0.98rem;
            opacity: 0.92;
        }
        .dashboard-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            justify-content: center;
        }
        .dashboard-card {
            flex: 0 1 260px;
            background: #f9f9fb;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            text-align: center;
            padding: 30px 10px 22px;
            transition: transform 0.18s, box-shadow 0.18s;
            color: #333;
            text-decoration: none !important;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .dashboard-card:hover {
            transform: translateY(-6px) scale(1.03);
            box-shadow: 0 4px 24px rgba(40,167,69,0.16);
            color: #1e7e34;
        }
        .dashboard-icon {
            font-size: 44px;
            color: #28a745;
            margin-bottom: 15px;
        }
        .dashboard-card-title {
            font-size: 1.14rem;
            font-weight: 600;
        }
        .dashboard-card-desc {
            color: #7c7c7c;
            font-size: 0.99rem;
            margin-bottom: 6px;
        }
        .logout-link {
            margin-top: 26px;
            font-size: 1rem;
        }
        .logout-link a {
            color: #dc3545;
            font-weight: 600;
            text-decoration: none;
        }
        .logout-link a:hover { color: #b31b2a; }
        @media (max-width: 900px) {
            .dashboard-grid { gap: 15px; }
        }
        @media (max-width: 600px) {
            .dashboard-section { padding: 22px 2vw; }
            .dashboard-header { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container dashboard-section">
        <div class="dashboard-header">
            <h2>Staff Dashboard</h2>
        </div>
        <div class="staff-info-card">
            <div class="staff-name"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($name) ?></div>
            <div class="staff-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></div>
        </div>
        <div class="dashboard-grid">
            <a class="dashboard-card" href="manage_products.php">
                <div class="dashboard-icon"><i class="fas fa-boxes"></i></div>
                <div class="dashboard-card-title">Manage Products</div>
                <div class="dashboard-card-desc">Add, edit products</div>
            </a>
            <a class="dashboard-card" href="manage_orders.php">
                <div class="dashboard-icon"><i class="fas fa-receipt"></i></div>
                <div class="dashboard-card-title">View Orders</div>
                <div class="dashboard-card-desc">Review and update orders</div>
            </a>
            <a class="dashboard-card" href="manage_services.php">
                <div class="dashboard-icon"><i class="fas fa-tools"></i></div>
                <div class="dashboard-card-title">Service Requests</div>
                <div class="dashboard-card-desc">Handle service issues</div>
            </a>
            <a class="dashboard-card" href="manage_rewards.php">
                <div class="dashboard-icon"><i class="fas fa-gift"></i></div>
                <div class="dashboard-card-title">Reward Points</div>
                <div class="dashboard-card-desc">Manage rewards</div>
            </a>
        </div>
        <div class="logout-link text-center">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</body>
</html>