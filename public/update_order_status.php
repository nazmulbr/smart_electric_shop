<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$order_id = intval($_GET['order_id'] ?? 0);
$message = '';
$statusList = ['Pending', 'Processing', 'Completed', 'Cancelled', 'Paid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['payment_status'] ?? '';
    if ($status) {
        $stmt = $conn->prepare('UPDATE `Order` SET payment_status=? WHERE order_id=?');
        $stmt->bind_param('si', $status, $order_id);
        $stmt->execute();
        $message = 'Order status updated!';
        header('Location: manage_orders.php');
        exit;
    }
}
$stmt = $conn->prepare('SELECT payment_status FROM `Order` WHERE order_id=?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$stmt->bind_result($current);
$stmt->fetch();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Update Order Status - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_orders.php" class="btn btn-secondary mb-2">Back to Orders</a>
        <div class="card">
            <div class="card-header">
                <h5>Update Status for Order #<?= $order_id ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Payment/Order Status</label>
                        <select name="payment_status" class="form-control" required>
                            <?php foreach ($statusList as $stat): ?>
                                <option value="<?= $stat ?>" <?= $stat == $current ? 'selected' : '' ?>><?= $stat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>