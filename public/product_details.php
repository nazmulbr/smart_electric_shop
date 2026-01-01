<?php
session_start();
require_once '../config/db.php';
require_once '../config/error_handler.php';

if (!isset($_GET['product_id'])) {
    header('Location: index.php');
    exit;
}

$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare('SELECT * FROM Product WHERE product_id = ? LIMIT 1');
if (!$stmt) {
    die('Database error');
}
$stmt->bind_param('i', $product_id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$product) {
    header('Location: index.php');
    exit;
}

$images = [];
if (!empty($product['images'])) {
    $images = json_decode($product['images'], true);
}

?>
<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($product['name']) ?> - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .thumb {
            width: 100px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            margin-right: 8px;
        }

        .main-img {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            background: #fff;
            border-radius: 6px;
        }
    </style>
    <script>
        function switchImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <a href="index.php" class="btn btn-secondary mb-3">Back to Home</a>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="mainImage" class="main-img" src="<?= !empty($images) ? htmlspecialchars($images[0]) : 'images/default-product.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="mt-3 d-flex">
                            <?php if (!empty($images)): ?>
                                <?php foreach ($images as $img): ?>
                                    <img src="<?= htmlspecialchars($img) ?>" class="thumb" onclick="switchImage('<?= htmlspecialchars($img) ?>')" />
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2><?= htmlspecialchars($product['name']) ?></h2>
                        <h4 class="text-success">৳ <?= number_format($product['price'], 2) ?></h4>
                        <?php
                        // Show reward points earned for this product (1 point per 100 BDT)
                        $points_for_product = floor($product['price'] / 100);
                        ?>
                        <p class="text-muted">Earn <strong><?= $points_for_product ?></strong> point(s) on purchase. (1 point = ৳1)</p>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        <ul>
                            <?php if (!empty($product['category'])): ?><li><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></li><?php endif; ?>
                            <?php if (!empty($product['product_type'])): ?><li><strong>Type:</strong> <?= htmlspecialchars($product['product_type']) ?></li><?php endif; ?>
                            <?php if (!empty($product['door_type'])): ?><li><strong>Door:</strong> <?= htmlspecialchars($product['door_type']) ?></li><?php endif; ?>
                            <?php if (!empty($product['gross_volume'])): ?><li><strong>Gross Volume:</strong> <?= htmlspecialchars($product['gross_volume']) ?></li><?php endif; ?>
                            <?php if (!empty($product['net_volume'])): ?><li><strong>Net Volume:</strong> <?= htmlspecialchars($product['net_volume']) ?></li><?php endif; ?>
                            <?php if (!empty($product['special_technology'])): ?><li><strong>Special Technology:</strong> <?= htmlspecialchars($product['special_technology']) ?></li><?php endif; ?>
                            <?php if (!empty($product['refrigerant'])): ?><li><strong>Refrigerant:</strong> <?= htmlspecialchars($product['refrigerant']) ?></li><?php endif; ?>
                            <?php if (!empty($product['compressor_guarantee'])): ?><li><strong>Compressor Guarantee:</strong> <?= htmlspecialchars($product['compressor_guarantee']) ?></li><?php endif; ?>
                        </ul>

                        <p><strong>Warranty:</strong> <?= htmlspecialchars($product['warranty_duration']) ?> months</p>
                        <p><strong>Available:</strong> <?= htmlspecialchars($product['available_quantity']) ?> units</p>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" action="cart.php" class="d-inline">
                                <input type="hidden" name="prod_id" value="<?= $product['product_id'] ?>" />
                                <input type="number" name="qty" value="1" min="1" class="form-control" style="width:100px;display:inline-block;vertical-align:middle;margin-right:8px;" />
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>
                            <form method="POST" action="checkout.php" class="d-inline">
                                <input type="hidden" name="prod_id" value="<?= $product['product_id'] ?>" />
                                <input type="hidden" name="qty" value="1" />
                                <button type="submit" class="btn btn-success">Buy Now</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Login to buy</a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>