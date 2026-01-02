<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$message = '';
// Handle update/remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add to cart (from product listings)
    if (isset($_POST['prod_id']) && !isset($_POST['update']) && !isset($_POST['remove'])) {
        $prod_id = intval($_POST['prod_id']);
        $qty = intval($_POST['qty'] ?? 1);
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$prod_id])) $_SESSION['cart'][$prod_id] += $qty;
        else $_SESSION['cart'][$prod_id] = $qty;

        // Persist to DB: create/find a 'cart' order for this user
        $user_id = $_SESSION['user_id'];
        $order_id = null;
        $stmt = $conn->prepare('SELECT order_id FROM `Order` WHERE user_id=? AND payment_status = ? LIMIT 1');
        if ($stmt) {
            $status = 'cart';
            $stmt->bind_param('is', $user_id, $status);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $order_id = $row['order_id'];
            }
            $stmt->close();
        }

        if (!$order_id) {
            $ins = $conn->prepare('INSERT INTO `Order` (user_id, order_date, payment_status, total_amount, discount) VALUES (?, NOW(), ?, 0, 0)');
            if ($ins) {
                $status = 'cart';
                $ins->bind_param('is', $user_id, $status);
                $ins->execute();
                $order_id = $ins->insert_id;
                $ins->close();
            }
        }

        if ($order_id) {
            // get product price
            $pstmt = $conn->prepare('SELECT price FROM Product WHERE product_id=?');
            if ($pstmt) {
                $pstmt->bind_param('i', $prod_id);
                $pstmt->execute();
                $pres = $pstmt->get_result();
                if ($prow = $pres->fetch_assoc()) {
                    $unit_price = $prow['price'];

                    // check existing order item
                    $ipstmt = $conn->prepare('SELECT item_id, quantity FROM OrderItem WHERE order_id=? AND product_id=? LIMIT 1');
                    if ($ipstmt) {
                        $ipstmt->bind_param('ii', $order_id, $prod_id);
                        $ipstmt->execute();
                        $ires = $ipstmt->get_result();
                        if ($irow = $ires->fetch_assoc()) {
                            // update quantity
                            $newQty = $irow['quantity'] + $qty;
                            $up = $conn->prepare('UPDATE OrderItem SET quantity=? , price=? WHERE item_id=?');
                            if ($up) {
                                $up->bind_param('idi', $newQty, $unit_price, $irow['item_id']);
                                $up->execute();
                                $up->close();
                            }
                        } else {
                            // insert order item
                            $insItem = $conn->prepare('INSERT INTO OrderItem (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
                            if ($insItem) {
                                $insItem->bind_param('iiid', $order_id, $prod_id, $qty, $unit_price);
                                $insItem->execute();
                                $insItem->close();
                            }
                        }
                        $ipstmt->close();
                    }

                    // update order total
                    $sumStmt = $conn->prepare('SELECT SUM(quantity * price) AS total FROM OrderItem WHERE order_id=?');
                    if ($sumStmt) {
                        $sumStmt->bind_param('i', $order_id);
                        $sumStmt->execute();
                        $sres = $sumStmt->get_result();
                        if ($srow = $sres->fetch_assoc()) {
                            $totalAmt = $srow['total'] ?? 0;
                            $updOrder = $conn->prepare('UPDATE `Order` SET total_amount=? WHERE order_id=?');
                            if ($updOrder) {
                                $updOrder->bind_param('di', $totalAmt, $order_id);
                                $updOrder->execute();
                                $updOrder->close();
                            }
                        }
                        $sumStmt->close();
                    }
                }
                $pstmt->close();
            }
        }
    } elseif (isset($_POST['update'])) {
        $prod_id = intval($_POST['prod_id']);
        $qty = intval($_POST['qty']);
        if ($qty > 0) {
            $_SESSION['cart'][$prod_id] = $qty;
            // update DB if cart order exists
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare('SELECT order_id FROM `Order` WHERE user_id=? AND payment_status = ? LIMIT 1');
            if ($stmt) {
                $status = 'cart';
                $stmt->bind_param('is', $user_id, $status);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $order_id = $row['order_id'];
                    if ($qty > 0) {
                        $up = $conn->prepare('UPDATE OrderItem oi JOIN `Order` o ON o.order_id = oi.order_id SET oi.quantity = ? , oi.price = (SELECT price FROM Product WHERE product_id = ?) WHERE oi.order_id = ? AND oi.product_id = ?');
                        if ($up) {
                            $up->bind_param('iiii', $qty, $prod_id, $order_id, $prod_id);
                            $up->execute();
                            $up->close();
                        }
                    }
                    // recalc order total
                    $sumStmt = $conn->prepare('SELECT SUM(quantity * price) AS total FROM OrderItem WHERE order_id=?');
                    if ($sumStmt) {
                        $sumStmt->bind_param('i', $order_id);
                        $sumStmt->execute();
                        $sres = $sumStmt->get_result();
                        if ($srow = $sres->fetch_assoc()) {
                            $totalAmt = $srow['total'] ?? 0;
                            $updOrder = $conn->prepare('UPDATE `Order` SET total_amount=? WHERE order_id=?');
                            if ($updOrder) {
                                $updOrder->bind_param('di', $totalAmt, $order_id);
                                $updOrder->execute();
                                $updOrder->close();
                            }
                        }
                        $sumStmt->close();
                    }
                }
                $stmt->close();
            }
        } else {
            unset($_SESSION['cart'][$prod_id]);
            // remove from DB if present
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare('SELECT order_id FROM `Order` WHERE user_id=? AND payment_status = ? LIMIT 1');
            if ($stmt) {
                $status = 'cart';
                $stmt->bind_param('is', $user_id, $status);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $order_id = $row['order_id'];
                    $del = $conn->prepare('DELETE FROM OrderItem WHERE order_id=? AND product_id=?');
                    if ($del) {
                        $del->bind_param('ii', $order_id, $prod_id);
                        $del->execute();
                        $del->close();
                    }
                    // update total
                    $sumStmt = $conn->prepare('SELECT SUM(quantity * price) AS total FROM OrderItem WHERE order_id=?');
                    if ($sumStmt) {
                        $sumStmt->bind_param('i', $order_id);
                        $sumStmt->execute();
                        $sres = $sumStmt->get_result();
                        if ($srow = $sres->fetch_assoc()) {
                            $totalAmt = $srow['total'] ?? 0;
                            $updOrder = $conn->prepare('UPDATE `Order` SET total_amount=? WHERE order_id=?');
                            if ($updOrder) {
                                $updOrder->bind_param('di', $totalAmt, $order_id);
                                $updOrder->execute();
                                $updOrder->close();
                            }
                        }
                        $sumStmt->close();
                    }
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['remove'])) {
        $prod_id = intval($_POST['prod_id']);
        unset($_SESSION['cart'][$prod_id]);
        // remove from DB
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare('SELECT order_id FROM `Order` WHERE user_id=? AND payment_status = ? LIMIT 1');
        if ($stmt) {
            $status = 'cart';
            $stmt->bind_param('is', $user_id, $status);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $order_id = $row['order_id'];
                $del = $conn->prepare('DELETE FROM OrderItem WHERE order_id=? AND product_id=?');
                if ($del) {
                    $del->bind_param('ii', $order_id, $prod_id);
                    $del->execute();
                    $del->close();
                }
                // update total
                $sumStmt = $conn->prepare('SELECT SUM(quantity * price) AS total FROM OrderItem WHERE order_id=?');
                if ($sumStmt) {
                    $sumStmt->bind_param('i', $order_id);
                    $sumStmt->execute();
                    $sres = $sumStmt->get_result();
                    if ($srow = $sres->fetch_assoc()) {
                        $totalAmt = $srow['total'] ?? 0;
                        $updOrder = $conn->prepare('UPDATE `Order` SET total_amount=? WHERE order_id=?');
                        if ($updOrder) {
                            $updOrder->bind_param('di', $totalAmt, $order_id);
                            $updOrder->execute();
                            $updOrder->close();
                        }
                    }
                    $sumStmt->close();
                }
            }
            $stmt->close();
        }
    }
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Try to populate cart from persistent 'cart' order in DB
    $cart_items = [];
    $total = 0;
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT o.order_id FROM `Order` o WHERE o.user_id = ? AND o.payment_status = ? LIMIT 1');
    if ($stmt) {
        $status = 'cart';
        $stmt->bind_param('is', $user_id, $status);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $order_id = $row['order_id'];
            // load items
            $it = $conn->prepare('SELECT oi.product_id, oi.quantity, oi.price, p.name FROM OrderItem oi JOIN Product p ON p.product_id = oi.product_id WHERE oi.order_id = ?');
            if ($it) {
                $it->bind_param('i', $order_id);
                $it->execute();
                $r = $it->get_result();
                $_SESSION['cart'] = [];
                while ($orow = $r->fetch_assoc()) {
                    $_SESSION['cart'][$orow['product_id']] = $orow['quantity'];
                }
                $it->close();
            }
        }
        $stmt->close();
    }
    // if session cart now populated, the following code will use it to build $cart_items
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
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h4>Shopping Cart</h4>
        <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-warning">Your cart is empty.</div>
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
                            <td>৳ <?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['discount'] > 0 ? $item['discount'] . '%' : 'None' ?></td>
                            <td>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="prod_id" value="<?= $item['product_id'] ?>" />
                                    <input type="number" name="qty" value="<?= $item['quantity'] ?>" class="form-control" min="1" style="width:70px;" />
                                    <input type="submit" name="update" value="Update" class="btn btn-sm btn-primary ml-1" />
                                </form>
                            </td>
                            <td>৳ <?= number_format($item['item_total'], 2) ?></td>
                            <td>
                                <?php $orig = $item['price'] * $item['quantity'];
                                $saved = $orig - $item['item_total']; ?>
                                <?php if ($saved > 0): ?>
                                    <span class="text-success">Saved ৳ <?= number_format($saved, 2) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
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
                        <th>৳ <?= number_format($total, 2) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary mt-2">Continue Shopping</a>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>