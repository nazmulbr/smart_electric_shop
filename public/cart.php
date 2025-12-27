<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$message = '';
// Handle update/remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $prod_id = intval($_POST['prod_id']);
        $qty = intval($_POST['qty']);
        if ($qty > 0) {
            $_SESSION['cart'][$prod_id] = $qty;
        } else {
            unset($_SESSION['cart'][$prod_id]);
        }
    } elseif (isset($_POST['remove'])) {
        $prod_id = intval($_POST['prod_id']);
        unset($_SESSION['cart'][$prod_id]);
    }
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $cart_items = [];
    $total = 0;
} else {
    $cart_items = [];
    $total = 0;
    foreach ($_SESSION['cart'] as $prod_id => $qty) {
        $stmt = $conn->prepare('SELECT * FROM Product WHERE product_id = ?');
        $stmt->bind_param('i', $prod_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $unit_price = $product['price'];
            // Check bulk pricing
            $stmt2 = $conn->prepare('SELECT discount_percentage FROM BulkPricing WHERE product_id = ? AND min_quantity <= ? ORDER BY min_quantity DESC LIMIT 1');
            $stmt2->bind_param('ii', $prod_id, $qty);
            $stmt2->execute();
            $discount_result = $stmt2->get_result();
            $discount = 0;
            if ($discount_row = $discount_result->fetch_assoc()) {
                $discount = $discount_row['discount_percentage'];
            }
            $discounted_price = $unit_price * (1 - $discount / 100);
            $item_total = $discounted_price * $qty;
            $total += $item_total;
            $cart_items[] = [
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'price' => $unit_price,
                'discounted_price' => $discounted_price,
                'discount' => $discount,
                'quantity' => $qty,
                'item_total' => $item_total
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Shopping Cart - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Shopping Cart</h4>
        <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-warning">Your cart is empty. <a href="view_products.php">Browse Products</a></div>
        <?php else: ?>
            <table class="table table-bordered bg-white">
                <thead class="thead-dark">
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['discount'] > 0 ? $item['discount'] . '%' : 'None' ?></td>
                            <td>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="prod_id" value="<?= $item['product_id'] ?>" />
                                    <input type="number" name="qty" value="<?= $item['quantity'] ?>" class="form-control" min="1" style="width:70px;" />
                                    <input type="submit" name="update" value="Update" class="btn btn-sm btn-primary ml-1" />
                                </form>
                            </td>
                            <td><?= number_format($item['item_total'], 2) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="prod_id" value="<?= $item['product_id'] ?>" />
                                    <input type="submit" name="remove" value="Remove" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')" />
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <th colspan="4">Grand Total</th>
                        <th><?= number_format($total, 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
        <?php endif; ?>
        <a href="view_products.php" class="btn btn-secondary mt-2">Continue Shopping</a>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>