<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';
$result = $conn->query('SELECT b.product_no, p.name as product_name, b.min_quantity, b.discount_percentage FROM BulkPricing b JOIN Product p ON b.product_id = p.product_id');
$rules = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Bulk Pricing Management - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Bulk Pricing Rules</h4>
        <div class="mb-2">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="bulk_pricing_form.php" class="btn btn-success">Add Bulk Pricing Rule</a>
        </div>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Min Quantity</th>
                    <th>Discount (%)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['product_no']) ?></td>
                        <td><?= htmlspecialchars($r['product_name']) ?></td>
                        <td><?= htmlspecialchars($r['min_quantity']) ?></td>
                        <td><?= htmlspecialchars($r['discount_percentage']) ?></td>
                        <td><a href="bulk_pricing_form.php?edit=<?= $r['product_no'] ?>" class="btn btn-primary btn-sm">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>