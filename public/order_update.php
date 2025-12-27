<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
if (!isset($_GET['id'])) { header('Location: manage_orders.php'); exit; }
$id = intval($_GET['id']);
$message = '';

// Fetch current status
$stmt = $conn->prepare('SELECT * FROM `Order` WHERE order_id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_status'])) {
    $status = $_POST['payment_status'];
    $stmt = $conn->prepare('UPDATE `Order` SET payment_status=? WHERE order_id=?');
    $stmt->bind_param('si', $status, $id);
    if ($stmt->execute()) {
        $message = 'Order status updated!';
        $order['payment_status'] = $status;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Order #<?=$order['order_id']?> - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_orders.php" class="btn btn-secondary mb-2">Back to Orders</a>
        <h4>Update Payment Status - Order #<?=$order['order_id']?></h4>
        <div class="card mt-3">
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-success"><?=$message?></div><?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Payment Status</label>
                        <select name="payment_status" class="form-control" required>
                            <option value="Pending" <?= $order['payment_status']=='Pending'?'selected':'' ?>>Pending</option>
                            <option value="Paid" <?= $order['payment_status']=='Paid'?'selected':'' ?>>Paid</option>
                            <option value="Cancelled" <?= $order['payment_status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

