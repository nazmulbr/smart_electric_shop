<?php
session_start();
require_once '../config/db.php';
require_once '../config/error_handler.php';
$result = $conn->query('SELECT * FROM Product');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$added = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_id = intval($_POST['prod_id'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if ($prod_id && $qty > 0) {
        if (isset($_SESSION['cart'][$prod_id])) $_SESSION['cart'][$prod_id] += $qty;
        else $_SESSION['cart'][$prod_id] = $qty;
        $added = 'Added to cart!';
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Products - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>All Products</h4>
        <?php if ($added): ?><div class="alert alert-success"><?= $added ?></div><?php endif; ?>
        <a href="cart.php" class="btn btn-info float-right mb-2">Go to Cart</a>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Warranty</th>
                    <th>Available</th>
                    <th>Add</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['description']) ?></td>
                        <td><?= htmlspecialchars($p['price']) ?></td>
                        <td><?= htmlspecialchars($p['warranty_duration']) ?> months</td>
                        <td><?= htmlspecialchars($p['available_quantity']) ?></td>
                        <td>
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="prod_id" value="<?= $p['product_id'] ?>" />
                                <input type="number" name="qty" value="1" class="form-control mr-2" min="1" max="<?= $p['available_quantity'] ?>" style="width:70px;" />
                                <button class="btn btn-success btn-sm">Add</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>