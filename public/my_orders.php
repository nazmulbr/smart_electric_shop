<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT * FROM `Order` WHERE user_id = ? ORDER BY order_date DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Orders - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h4>My Order History</h4>
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div class="alert alert-info"><?php echo htmlentities($_SESSION['flash_message']);
                                            unset($_SESSION['flash_message']); ?></div>
        <?php endif; ?>
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">You have no orders yet. <a href="index.php">Start Shopping</a></div>
        <?php else: ?>
            <table class="table table-bordered bg-white">
                <thead class="thead-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Discount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> BDT</td>
                            <td><?= number_format($order['discount'], 2) ?> BDT</td>
                            <td><?= htmlspecialchars($order['payment_status']) ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?= $order['order_id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>