<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
if (!$order_id) {
    header('Location: my_orders.php');
    exit;
}

// Fetch order and ensure it belongs to user
$stmt = $conn->prepare('SELECT * FROM `Order` WHERE order_id = ? AND user_id = ?');
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    $_SESSION['flash_message'] = 'Order not found.';
    header('Location: my_orders.php');
    exit;
}

// Allow payment only if not already paid
if ($order['payment_status'] === 'Paid') {
    $_SESSION['flash_message'] = 'Order already paid.';
    header('Location: order_details.php?order_id=' . $order_id);
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    // Simulate payment processing. You can integrate real gateway here.
    $simulate = $_POST['simulate'] ?? 'success';
    if ($simulate === 'fail') {
        $status = 'Failed';
    } else {
        $status = 'Paid';
    }

    $stmt = $conn->prepare('UPDATE `Order` SET payment_status = ? WHERE order_id = ? AND user_id = ?');
    $stmt->bind_param('sii', $status, $order_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = ($status === 'Paid') ? 'Payment successful. Thank you!' : 'Payment failed. Please try again.';
        header('Location: order_details.php?order_id=' . $order_id);
        exit;
    } else {
        $message = 'Payment processing failed: ' . $stmt->error;
    }
}

// Fetch order items for display
$stmt = $conn->prepare('SELECT oi.*, p.name FROM OrderItem oi JOIN Product p ON oi.product_id = p.product_id WHERE oi.order_id = ?');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Pay Order #<?= $order_id ?> - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h4>Pay for Order #<?= $order_id ?></h4>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Total:</strong> <?= number_format($order['total_amount'], 2) ?> BDT</p>
                <p><strong>Discount:</strong> <?= number_format($order['discount'], 2) ?> BDT</p>
                <p><strong>Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
            </div>
        </div>

        <h5>Items</h5>
        <table class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['name']) ?></td>
                        <td><?= htmlspecialchars($it['quantity']) ?></td>
                        <td><?= number_format($it['price'], 2) ?> BDT</td>
                        <td><?= number_format($it['price'] * $it['quantity'], 2) ?> BDT</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="card mt-3">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="Cash">Cash on Delivery</option>
                            <option value="Card">Card</option>
                            <option value="Mobile Banking">Mobile Banking</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Simulate Result</label>
                        <select name="simulate" class="form-control">
                            <option value="success">Simulate Success</option>
                            <option value="fail">Simulate Failure</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Pay Now</button>
                    <a href="order_details.php?order_id=<?= $order_id ?>" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>