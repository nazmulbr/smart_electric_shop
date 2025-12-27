<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if Product table exists
if (!checkTableExists('Product')) {
    die(showTableError('Product', 'Product Management'));
}

$msg = '';
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'] == 'added' ? 'Product added successfully!' : ($_GET['msg'] == 'updated' ? 'Product updated successfully!' : '');
}

// Handle deletion
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM Product WHERE product_id=?');
    if ($stmt) {
        $stmt->bind_param('i', $pid);
        if ($stmt->execute()) {
            header('Location: manage_products.php?msg=deleted');
            exit;
        } else {
            $msg = showDbError($conn, "Product Deletion");
            $msg .= "<strong>Delete Details:</strong><br>";
            $msg .= "Product ID: $pid<br>";
        }
        $stmt->close();
    } else {
        $msg = showDbError($conn, "Preparing DELETE statement");
    }
}

// Get products
$products = [];
$result = $conn->query('SELECT * FROM Product ORDER BY product_id DESC');
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $msg = showDbError($conn, "Loading Products");
    $msg .= "<strong>Query:</strong> SELECT * FROM Product ORDER BY product_id DESC<br>";
}
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
        <?php if ($msg): ?>
            <div class="alert alert-<?=strpos($msg, 'failed') !== false || strpos($msg, 'Error') !== false ? 'danger' : 'success'?>">
                <?php 
                // Check if it's already HTML formatted (from showDbError)
                if (strpos($msg, '<div') !== false || strpos($msg, '<strong') !== false) {
                    echo $msg;
                } else {
                    echo htmlspecialchars($msg);
                }
                ?>
            </div>
        <?php endif; ?>
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

