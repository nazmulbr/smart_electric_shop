<?php
session_start();
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Ensure Product table has images column
$check_column = $conn->query("SHOW COLUMNS FROM Product LIKE 'images'");
if (!$check_column || $check_column->num_rows == 0) {
    $conn->query("ALTER TABLE Product ADD COLUMN images TEXT NULL AFTER reward_points");
}

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

// Load bulk pricing tiers for this product
$bp = [];
$bp_stmt = $conn->prepare('SELECT min_quantity, discount_percentage FROM BulkPricing WHERE product_id = ? ORDER BY min_quantity ASC');
if ($bp_stmt) {
    $bp_stmt->bind_param('i', $product_id);
    $bp_stmt->execute();
    $bpres = $bp_stmt->get_result();
    if ($bpres) {
        while ($brow = $bpres->fetch_assoc()) {
            $bp[] = $brow;
        }
    }
    $bp_stmt->close();
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
            display: block;
        }

        .image-container {
            position: relative;
            overflow: hidden;
            cursor: zoom-in;
        }

        .zoom-lens {
            position: absolute;
            border: 2px solid #fff;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.2);
            pointer-events: none;
            display: none;
            z-index: 5;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .zoom-result {
            position: absolute;
            top: 0;
            right: -400px;
            width: 400px;
            height: 400px;
            border: 1px solid #ccc;
            background: #fff;
            background-size: 1000px 1000px;
            background-repeat: no-repeat;
            display: none;
            z-index: 20;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        .image-container:hover .zoom-result {
            display: block;
        }

        .image-container:hover .zoom-lens {
            display: block;
        }

        .nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 4px;
            z-index: 10;
            transition: background 0.3s;
        }

        .nav-btn:hover {
            background: rgba(0, 0, 0, 0.7);
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .nav-btn.prev {
            left: 10px;
        }

        .nav-btn.next {
            right: 10px;
        }
    </style>
    <script>
        const images = <?= json_encode($images) ?>;
        let currentImageIndex = 0;

        function switchImage(src) {
            document.getElementById('mainImage').src = src;
            // Update current index when clicking thumbnails
            const index = images.indexOf(src);
            if (index !== -1) {
                currentImageIndex = index;
                updateNavButtons();
            }
            // Update zoom background
            updateZoomBackground();
        }

        function showImage(index) {
            if (index < 0 || index >= images.length) return;
            currentImageIndex = index;
            document.getElementById('mainImage').src = images[currentImageIndex];
            updateNavButtons();
            // Update zoom background
            updateZoomBackground();
        }

        function updateZoomBackground() {
            const result = document.getElementById('zoomResult');
            const img = document.getElementById('mainImage');
            if (result && img) {
                // Wait for image to load, then update zoom
                img.onload = function() {
                    result.style.backgroundImage = "url('" + img.src + "')";
                    const imgWidth = img.offsetWidth || img.clientWidth;
                    const imgHeight = img.offsetHeight || img.clientHeight;
                    const zoomRatio = 2.5;
                    result.style.backgroundSize = (imgWidth * zoomRatio) + 'px ' + (imgHeight * zoomRatio) + 'px';
                };
                result.style.backgroundImage = "url('" + img.src + "')";
                // If image already loaded, update immediately
                if (img.complete) {
                    const imgWidth = img.offsetWidth || img.clientWidth;
                    const imgHeight = img.offsetHeight || img.clientHeight;
                    const zoomRatio = 2.5;
                    result.style.backgroundSize = (imgWidth * zoomRatio) + 'px ' + (imgHeight * zoomRatio) + 'px';
                }
            }
        }

        function prevImage() {
            if (currentImageIndex > 0) {
                showImage(currentImageIndex - 1);
            } else {
                showImage(images.length - 1); // Loop to last image
            }
        }

        function nextImage() {
            if (currentImageIndex < images.length - 1) {
                showImage(currentImageIndex + 1);
            } else {
                showImage(0); // Loop to first image
            }
        }

        function updateNavButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (prevBtn && nextBtn) {
                // Enable/disable buttons based on image count
                // For looping navigation, we can keep buttons enabled
                // Or disable them if you prefer non-looping behavior
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (images.length > 0) {
                updateNavButtons();
            }
            initImageZoom();
        });

        // Image zoom functionality
        function initImageZoom() {
            const img = document.getElementById('mainImage');
            const lens = document.getElementById('zoomLens');
            const result = document.getElementById('zoomResult');
            const container = document.getElementById('imageContainer');

            if (!img || !lens || !result || !container) return;

            // Wait for image to load
            function setupZoom() {
                // Set zoom result background image
                result.style.backgroundImage = "url('" + img.src + "')";

                // Calculate zoom ratio based on image dimensions
                const zoomRatio = 2.5;
                const imgWidth = img.offsetWidth || img.clientWidth;
                const imgHeight = img.offsetHeight || img.clientHeight;
                
                // Set background size for zoom result
                result.style.backgroundSize = (imgWidth * zoomRatio) + 'px ' + (imgHeight * zoomRatio) + 'px';

                // Mouse move event
                container.addEventListener('mousemove', function(e) {
                    const rect = container.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    // Calculate lens position
                    let lensX = x - lens.offsetWidth / 2;
                    let lensY = y - lens.offsetHeight / 2;

                    // Keep lens within image bounds
                    const maxX = rect.width - lens.offsetWidth;
                    const maxY = rect.height - lens.offsetHeight;

                    lensX = Math.max(0, Math.min(lensX, maxX));
                    lensY = Math.max(0, Math.min(lensY, maxY));

                    lens.style.left = lensX + 'px';
                    lens.style.top = lensY + 'px';

                    // Calculate background position for zoom result
                    const bgX = (lensX / maxX) * (imgWidth * zoomRatio - result.offsetWidth);
                    const bgY = (lensY / maxY) * (imgHeight * zoomRatio - result.offsetHeight);

                    result.style.backgroundPosition = '-' + bgX + 'px -' + bgY + 'px';
                });
            }

            if (img.complete) {
                setupZoom();
            } else {
                img.addEventListener('load', setupZoom);
            }
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
                        <div class="image-container" id="imageContainer">
                            <img id="mainImage" class="main-img" src="<?= !empty($images) ? htmlspecialchars($images[0]) : 'images/default-product.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="zoom-lens" id="zoomLens"></div>
                            <div class="zoom-result" id="zoomResult"></div>
                            <?php if (!empty($images) && count($images) > 1): ?>
                                <button class="nav-btn prev" id="prevBtn" onclick="prevImage()" title="Previous image">‹</button>
                                <button class="nav-btn next" id="nextBtn" onclick="nextImage()" title="Next image">›</button>
                            <?php endif; ?>
                        </div>
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
                        // Show reward points earned for this product (set by admin)
                        $points_for_product = intval($product['reward_points'] ?? 0);
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
                            <form method="POST" action="cart.php" class="d-inline" id="addCartForm">
                                <input type="hidden" name="prod_id" value="<?= $product['product_id'] ?>" />
                                <input type="number" name="qty" id="qtyInput" value="1" min="1" class="form-control" style="width:100px;display:inline-block;vertical-align:middle;margin-right:8px;" />
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </form>
                            <form method="POST" action="checkout.php" class="d-inline" id="buyNowForm">
                                <input type="hidden" name="prod_id" value="<?= $product['product_id'] ?>" />
                                <input type="hidden" name="qty" id="buyQty" value="1" />
                                <button type="submit" class="btn btn-success">Buy Now</button>
                            </form>
                            <div class="mt-2" id="bulkInfo">
                                <?php if (!empty($bp)): ?>
                                    <h6>Bulk Pricing</h6>
                                    <ul>
                                        <?php foreach ($bp as $tier): ?>
                                            <li><?= intval($tier['min_quantity']) ?>+ : <?= floatval($tier['discount_percentage']) ?>% off</li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <small class="text-muted">No bulk discounts for this product.</small>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <strong>Applied Discount:</strong> <span id="appliedDiscount">0%</span><br>
                                    <strong>Price per unit:</strong> ৳ <span id="appliedPrice"><?= number_format($product['price'], 2) ?></span><br>
                                    <strong>Total:</strong> ৳ <span id="appliedTotal"><?= number_format($product['price'], 2) ?></span>
                                </div>
                            </div>
                            <script>
                                const basePrice = <?= json_encode(floatval($product['price'])) ?>;
                                const bulkTiers = <?= json_encode($bp) ?>;

                                function computeBulk(qty) {
                                    qty = parseInt(qty) || 1;
                                    let discount = 0;
                                    for (let i = 0; i < bulkTiers.length; i++) {
                                        if (qty >= parseInt(bulkTiers[i].min_quantity)) discount = parseFloat(bulkTiers[i].discount_percentage);
                                    }
                                    const price = basePrice * (1 - discount / 100);
                                    return {
                                        discount,
                                        price,
                                        total: price * qty
                                    };
                                }
                                const qtyInput = document.getElementById('qtyInput');
                                const appliedDiscount = document.getElementById('appliedDiscount');
                                const appliedPrice = document.getElementById('appliedPrice');
                                const appliedTotal = document.getElementById('appliedTotal');
                                const buyQty = document.getElementById('buyQty');

                                function updateBulkUI() {
                                    const q = parseInt(qtyInput.value) || 1;
                                    const res = computeBulk(q);
                                    appliedDiscount.textContent = res.discount + '%';
                                    appliedPrice.textContent = res.price.toFixed(2);
                                    appliedTotal.textContent = res.total.toFixed(2);
                                    if (buyQty) buyQty.value = q;
                                }
                                qtyInput.addEventListener('input', updateBulkUI);
                                updateBulkUI();
                            </script>
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