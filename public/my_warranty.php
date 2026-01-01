<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
$user_id = $_SESSION['user_id'];
// Find user's warranty (if linked by user.warranty_id or via last purchase)
$stmt = $conn->prepare('SELECT w.* FROM Warranty w JOIN User u ON w.warranty_id=u.warranty_id WHERE u.user_id=?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$warranty = $result ? $result->fetch_assoc() : null;
// Calculate expiry if available
$expiry = '';
if ($warranty) {
    $expiry = date('Y-m-d', strtotime($warranty['purchase_date'] . ' +' . $warranty['warranty_duration'] . ' months'));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Warranty - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h4>My Warranty Status</h4>
        <?php if ($warranty): ?>
            <?php
            // Fetch product(s) purchased on the warranty purchase date to show short details (no thumbnails)
            $products_for_warranty = [];
            $pstmt = $conn->prepare("SELECT p.product_id, p.name, p.description FROM `Order` o
                JOIN OrderItem oi ON oi.order_id = o.order_id
                JOIN Product p ON p.product_id = oi.product_id
                WHERE o.user_id = ? AND DATE(o.order_date) = ?");
            if ($pstmt) {
                $pdate = $warranty['purchase_date'];
                $pstmt->bind_param('is', $user_id, $pdate);
                $pstmt->execute();
                $pres = $pstmt->get_result();
                if ($pres) {
                    while ($prow = $pres->fetch_assoc()) {
                        $products_for_warranty[] = $prow;
                    }
                }
                $pstmt->close();
            }
            ?>
            <table class="table table-bordered bg-white col-md-6">
                <?php if (!empty($products_for_warranty)): ?>
                    <tr>
                        <th>Product(s)</th>
                        <td>
                            <?php foreach ($products_for_warranty as $pp): ?>
                                <div style="margin-bottom:8px;">
                                    <a href="product_details.php?product_id=<?= $pp['product_id'] ?>" style="font-weight:600;color:#333;text-decoration:none;"><?= htmlspecialchars($pp['name']) ?></a><br>
                                    <small class="text-muted"><?= htmlspecialchars(substr($pp['description'], 0, 120)) ?><?= strlen($pp['description']) > 120 ? '...' : '' ?></small>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Duration (months)</th>
                    <td><?= htmlspecialchars($warranty['warranty_duration']) ?></td>
                </tr>
                <tr>
                    <th>Purchase Date</th>
                    <td><?= htmlspecialchars($warranty['purchase_date']) ?></td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td><?= htmlspecialchars($expiry) ?></td>
                </tr>
            </table>
            <?php if (strtotime($expiry) < time()): ?>
                <div class="alert alert-danger">Your warranty has expired!</div>
            <?php elseif ((strtotime($expiry) - time()) / (60 * 60 * 24) <= 30): ?>
                <div class="alert alert-warning">Your warranty is expiring soon!</div>
            <?php else: ?>
                <div class="alert alert-success">Your warranty is active.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">No warranty associated with your account.</div>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>