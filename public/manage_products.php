<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

// Handle deletion
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM Product WHERE product_id=?');
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    header('Location: manage_products.php');
    exit;
}

// Get products
$result = $conn->query('SELECT * FROM Product');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h4>Product Management</h4>
        <div class="mb-2">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="product_form.php" class="btn btn-success">Add Product</a>
        </div>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Qty</th><th>Warranty (months)</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?=htmlspecialchars($p['product_id'])?></td>
                    <td><?=htmlspecialchars($p['name'])?></td>
                    <td><?=htmlspecialchars($p['description'])?></td>
                    <td><?=htmlspecialchars($p['price'])?></td>
                    <td><?=htmlspecialchars($p['available_quantity'])?></td>
                    <td><?=htmlspecialchars($p['warranty_duration'])?></td>
                    <td>
                        <a href="product_form.php?edit=<?=$p['product_id']?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="manage_products.php?delete=<?=$p['product_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

