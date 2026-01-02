<?php
// Require admin-only access
$require_role = 'admin';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Ensure Product table has reward_points column
$check_column = $conn->query("SHOW COLUMNS FROM Product LIKE 'reward_points'");
if (!$check_column || $check_column->num_rows == 0) {
    $conn->query("ALTER TABLE Product ADD COLUMN reward_points INT DEFAULT 0 AFTER available_quantity");
}

// Get user reward points
$user_result = $conn->query('SELECT r.points_id, u.name as user_name, u.email, r.points FROM RewardPoints r JOIN User u ON r.user_id = u.user_id ORDER BY r.points DESC');
$user_rewards = $user_result ? $user_result->fetch_all(MYSQLI_ASSOC) : [];

// Get product reward points
$product_result = $conn->query('SELECT product_id, name, price, reward_points FROM Product WHERE reward_points > 0 OR reward_points IS NOT NULL ORDER BY reward_points DESC');
$product_rewards = $product_result ? $product_result->fetch_all(MYSQLI_ASSOC) : [];

// Get all products (including those with 0 points)
$all_products_result = $conn->query('SELECT product_id, name, price, COALESCE(reward_points, 0) as reward_points FROM Product ORDER BY name ASC');
$all_products = $all_products_result ? $all_products_result->fetch_all(MYSQLI_ASSOC) : [];

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Reward Points Management - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .nav-tabs .nav-link {
            color: #007bff;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Manage Reward Points</h4>
        <div class="mb-3">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $active_tab === 'users' ? 'active' : '' ?>" href="?tab=users">User Reward Points</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_tab === 'products' ? 'active' : '' ?>" href="?tab=products">Product Reward Points</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- User Reward Points Tab -->
            <div class="tab-pane <?= $active_tab === 'users' ? 'show active' : '' ?>">
                <div class="mb-3">
                    <a href="reward_form.php?type=user" class="btn btn-success">Add/Update User Reward Points</a>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>User Reward Points</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered bg-white">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($user_rewards)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No user reward points found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($user_rewards as $rw): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rw['points_id']) ?></td>
                                            <td><?= htmlspecialchars($rw['user_name']) ?></td>
                                            <td><?= htmlspecialchars($rw['email']) ?></td>
                                            <td><?= htmlspecialchars($rw['points']) ?></td>
                                            <td>
                                                <a href="reward_form.php?type=user&edit=<?= $rw['points_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="reward_form.php?type=user&adjust=<?= $rw['points_id'] ?>" class="btn btn-info btn-sm">Adjust</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Product Reward Points Tab -->
            <div class="tab-pane <?= $active_tab === 'products' ? 'show active' : '' ?>">
                <div class="mb-3">
                    <a href="reward_form.php?type=product" class="btn btn-success">Add/Update Product Reward Points</a>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5>Product Reward Points</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered bg-white">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Reward Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_products)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No products found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($all_products as $prod): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($prod['product_id']) ?></td>
                                            <td><?= htmlspecialchars($prod['name']) ?></td>
                                            <td><?= number_format($prod['price'], 2) ?> BDT</td>
                                            <td><?= htmlspecialchars($prod['reward_points']) ?></td>
                                            <td>
                                                <a href="reward_form.php?type=product&edit=<?= $prod['product_id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="reward_form.php?type=product&adjust=<?= $prod['product_id'] ?>" class="btn btn-info btn-sm">Adjust</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>