<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$order_id = intval($_GET['order_id'] ?? 0);
$stmt = $conn->prepare('SELECT oi.*, p.name FROM OrderItem oi JOIN Product p ON oi.product_id=p.product_id WHERE oi.order_id=?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Order Items - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_orders.php" class="btn btn-secondary mb-2">Back to Orders</a>
        <h4>Items for Order #<?= $order_id ?></h4>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>Item ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['item_id']) ?></td>
                        <td><?= htmlspecialchars($it['name']) ?></td>
                        <td><?= htmlspecialchars($it['quantity']) ?></td>
                        <td><?= htmlspecialchars($it['price']) ?></td>
                        <td><?= number_format($it['price'] * $it['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>