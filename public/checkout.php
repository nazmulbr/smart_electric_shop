<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$user_id = $_SESSION['user_id'];
$message = '';
$order_created = false;

// Calculate cart total (same logic as cart.php)
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

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
            'item_total' => $item_total,
            'warranty_duration' => $product['warranty_duration']
        ];
    }
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $use_rewards = isset($_POST['use_rewards']) ? true : false;

    // Get reward points and compute discount (1 point = 1 BDT)
    $points_used = 0;
    $points_discount = 0;
    if ($use_rewards) {
        $stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE user_id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->bind_result($user_points);
            $stmt->fetch();
            $stmt->close();
            $user_points = intval($user_points ?: 0);
            if ($user_points > 0) {
                // Use up to available points but not more than the order total
                $points_used = min($user_points, floor($total));
                $points_discount = $points_used; // 1 point = 1 BDT
            }
        }
    }

    $final_total = max(0, $total - $points_discount);
    $discount_amount = $points_discount;

    // Create order
    $stmt = $conn->prepare('INSERT INTO `Order` (user_id, order_date, payment_status, total_amount, discount) VALUES (?, NOW(), ?, ?, ?)');
    $payment_status = 'Pending';
    $stmt->bind_param('isdd', $user_id, $payment_status, $final_total, $discount_amount);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Create order items and update stock
    foreach ($cart_items as $item) {
        $stmt = $conn->prepare('INSERT INTO OrderItem (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $item['discounted_price']);
        $stmt->execute();

        // Update product quantity
        $stmt2 = $conn->prepare('UPDATE Product SET available_quantity = available_quantity - ? WHERE product_id = ?');
        $stmt2->bind_param('ii', $item['quantity'], $item['product_id']);
        $stmt2->execute();

        // Create warranty if product has warranty
        if ($item['warranty_duration'] > 0) {
            $stmt3 = $conn->prepare('INSERT INTO Warranty (warranty_duration, purchase_date) VALUES (?, CURDATE())');
            $stmt3->bind_param('i', $item['warranty_duration']);
            $stmt3->execute();
            $warranty_id = $conn->insert_id;

            // Link warranty to user
            $stmt4 = $conn->prepare('UPDATE User SET warranty_id = ? WHERE user_id = ?');
            $stmt4->bind_param('ii', $warranty_id, $user_id);
            $stmt4->execute();
        }
    }

    // Update reward points (deduct used, add earned)
    $points_earned = floor($final_total / 100); // 1 point per 100 BDT

    // Read existing points
    $current_points = 0;
    $stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE user_id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($current_points);
        $stmt->fetch();
        $stmt->close();
    }
    $current_points = intval($current_points ?: 0);

    $new_points = $current_points - $points_used + $points_earned;
    if ($new_points < 0) $new_points = 0;

    // Upsert new points
    $check = $conn->prepare('SELECT points_id FROM RewardPoints WHERE user_id = ?');
    if ($check) {
        $check->bind_param('i', $user_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $check->close();
            $up = $conn->prepare('UPDATE RewardPoints SET points = ? WHERE user_id = ?');
            if ($up) {
                $up->bind_param('ii', $new_points, $user_id);
                $up->execute();
                $up->close();
            }
        } else {
            $check->close();
            $ins = $conn->prepare('INSERT INTO RewardPoints (user_id, points) VALUES (?, ?)');
            if ($ins) {
                $ins->bind_param('ii', $user_id, $new_points);
                $ins->execute();
                $ins->close();
            }
        }
    }

    // Link order to admin (for management)
    $admin_result = $conn->query('SELECT admin_id FROM Admin LIMIT 1');
    if ($admin_row = $admin_result->fetch_assoc()) {
        $admin_id = $admin_row['admin_id'];
        $stmt = $conn->prepare('INSERT INTO CanCheckOrder (order_id, admin_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $order_id, $admin_id);
        $stmt->execute();
    }

    // Clear cart
    $_SESSION['cart'] = [];

    $message = "Order #$order_id placed successfully! Total: " . number_format($final_total, 2) . " BDT";
    $order_created = true;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Checkout - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h4>Checkout</h4>
        <?php if ($message): ?>
            <div class="alert alert-<?= $order_created ? 'success' : 'info' ?>"><?= $message ?></div>
            <?php if ($order_created): ?>
                <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
                <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            <?php endif; ?>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <h5>Order Summary</h5>
                    <table class="table table-bordered bg-white">
                        <thead class="thead-dark">
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>৳ <?= number_format($item['discounted_price'], 2) ?></td>
                                    <td>৳ <?= number_format($item['item_total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Subtotal</th>
                                <th>৳ <?= number_format($total, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Payment</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="Cash">Cash</option>
                                        <option value="Card">Card</option>
                                        <option value="Mobile Banking">Mobile Banking</option>
                                    </select>
                                </div>
                                <?php
                                $stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE user_id = ?');
                                $stmt->bind_param('i', $user_id);
                                $stmt->execute();
                                $stmt->bind_result($user_points);
                                $has_points = $stmt->fetch();
                                $stmt->close();
                                if ($has_points && $user_points > 0): ?>
                                    <div class="form-check">
                                        <input type="checkbox" name="use_rewards" class="form-check-input" id="use_rewards">
                                        <label class="form-check-label" for="use_rewards">Use Reward Points (<?= $user_points ?> available)</label>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <strong>Total: ৳ <?= number_format($total, 2) ?></strong>
                                </div>
                                <button type="submit" name="place_order" class="btn btn-success btn-block mt-3">Place Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <a href="cart.php" class="btn btn-secondary mt-2">Back to Cart</a>
        <?php endif; ?>
    </div>
</body>

</html>