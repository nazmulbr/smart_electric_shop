<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php'); exit;
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id'] ?? 0);

// Get order
$stmt = $conn->prepare('SELECT * FROM `Order` WHERE order_id = ? AND user_id = ?');
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: my_orders.php'); exit;
}

// Get order items
$stmt = $conn->prepare('SELECT oi.*, p.name FROM OrderItem oi JOIN Product p ON oi.product_id = p.product_id WHERE oi.order_id = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result() ? $stmt->get_result()->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Details - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h4>Order #<?=$order_id?> Details</h4>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered bg-white">
                    <tr><th>Order Date</th><td><?=htmlspecialchars($order['order_date'])?></td></tr>
                    <tr><th>Total Amount</th><td><?=number_format($order['total_amount'], 2)?> BDT</td></tr>
                    <tr><th>Discount</th><td><?=number_format($order['discount'], 2)?> BDT</td></tr>
                    <tr><th>Payment Status</th><td><?=htmlspecialchars($order['payment_status'])?></td></tr>
                </table>
            </div>
        </div>
        <h5 class="mt-3">Order Items</h5>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr><th>Product</th><th>Quantity</th><th>Unit Price</th><th>Total</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?=htmlspecialchars($item['name'])?></td>
                    <td><?=htmlspecialchars($item['quantity'])?></td>
                    <td><?=number_format($item['price'], 2)?> BDT</td>
                    <td><?=number_format($item['price'] * $item['quantity'], 2)?> BDT</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="my_orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>
</body>
</html>

