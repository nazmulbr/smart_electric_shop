<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$result = $conn->query('SELECT o.*, u.name as user_name FROM `Order` o JOIN User u ON o.user_id = u.user_id ORDER BY o.order_date DESC');
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Orders - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Order Management</h4>
        <div class="mb-2">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Discount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= htmlspecialchars($o['order_id']) ?></td>
                        <td><?= htmlspecialchars($o['user_name']) ?></td>
                        <td><?= htmlspecialchars($o['order_date']) ?></td>
                        <td>৳ <?= number_format($o['total_amount'], 2) ?></td>
                        <td>৳ <?= number_format($o['discount'], 2) ?></td>
                        <td><?= htmlspecialchars($o['payment_status']) ?></td>
                        <td>
                            <a href="order_items.php?order_id=<?= $o['order_id'] ?>" class="btn btn-primary btn-sm">View Items</a>
                            <a href="update_order_status.php?order_id=<?= $o['order_id'] ?>" class="btn btn-warning btn-sm">Update Status</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>