<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
if (!isset($_GET['id'])) { header('Location: manage_orders.php'); exit; }
$id = intval($_GET['id']);

// Fetch order
$stmt = $conn->prepare('SELECT o.*, u.name AS user_name FROM `Order` o LEFT JOIN User u ON o.user_id=u.user_id WHERE o.order_id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Fetch order items
$stmt = $conn->prepare('SELECT oi.*, p.name FROM OrderItem oi LEFT JOIN Product p ON oi.product_id=p.product_id WHERE oi.order_id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order #<?=$order['order_id']?> - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_orders.php" class="btn btn-secondary mb-2">Back to Orders</a>
        <h4>Order #<?=$order['order_id']?> - <?=$order['user_name']?></h4>
        <div class="card mt-3">
            <div class="card-body">
                <strong>Date:</strong> <?=$order['order_date']?><br>
                <strong>Status:</strong> <?=$order['payment_status']?><br>
                <strong>Total Amount:</strong> <?=$order['total_amount']?><br>
                <strong>Discount:</strong> <?=$order['discount']?><br>
            </div>
        </div>
        <h5 class="mt-4">Items</h5>
        <table class="table table-bordered bg-white">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th></tr></thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><?=htmlspecialchars($item['name'])?></td>
                    <td><?=htmlspecialchars($item['quantity'])?></td>
                    <td><?=htmlspecialchars($item['price'])?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

