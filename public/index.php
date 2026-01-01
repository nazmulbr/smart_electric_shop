<?php
session_start();
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if database is set up
if (!checkTableExists('User')) {
    // Redirect to initialization page if tables don't exist
    header('Location: init_database.php');
    exit;
}

// Redirect only admin and staff to their dashboards
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'staff') {
        header('Location: staff_dashboard.php');
        exit;
    }
    // Regular users continue to see the homepage
}

// Fetch all products to display
$products = [];
if (checkTableExists('Product')) {
    $result = $conn->query('SELECT * FROM Product');
    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Smart Electric Shop - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .my-account-btn {
            position: static;
            width: auto;
            height: auto;
            border-radius: 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 999;
            transition: all 0.3s ease;
            text-decoration: none;
            padding: 8px 20px;
            margin-left: auto;
            gap: 8px;
        }

        .my-account-btn:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            color: white;
            text-decoration: none;
        }

        .my-account-dropdown {
            position: relative;
            display: inline-block;
        }

        .my-account-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 250px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
            animation: slideDown 0.3s ease;
        }

        .my-account-dropdown:hover .my-account-menu {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .my-account-menu-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: 1px solid #ddd;
        }

        .my-account-menu-header p {
            margin: 5px 0;
            font-size: 0.9rem;
        }

        .my-account-menu-header .user-name {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .my-account-menu-header .user-email {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .my-account-menu-item {
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            display: block;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .my-account-menu-item:hover {
            background-color: #f8f9fa;
            padding-left: 25px;
            color: var(--primary-color);
        }

        .my-account-menu-item i {
            margin-right: 8px;
            width: 18px;
        }

        .my-account-menu-item.logout {
            color: var(--danger-color);
            border-bottom: none;
        }

        .my-account-menu-item.logout:hover {
            background-color: #ffe6e6;
        }

        .my-account-btn.hidden {
            display: none;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .container-main {
            margin-top: 30px;
        }

        .jumbotron {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            padding: 60px 30px;
        }

        .jumbotron h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .jumbotron .lead {
            color: #666;
            font-size: 1.4rem;
        }

        .btn-auth {
            margin: 10px 5px;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-primary.btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-success.btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .feature-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .feature-card-link:hover {
            text-decoration: none;
        }

        .feature-card-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .product-section {
            margin-top: 50px;
        }

        .product-section h2 {
            color: white;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
            text-align: center;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .product-card-body {
            padding: 20px;
            flex-grow: 1;
        }

        .product-card-body h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .product-card-body .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-color);
            margin: 10px 0;
        }

        .product-card-body .warranty {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .product-card-body .available {
            color: #28a745;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-add-cart,
        .btn-checkout {
            flex: 1;
            padding: 10px;
            font-size: 0.95rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-add-cart {
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-add-cart:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }

        .btn-checkout {
            background-color: var(--success-color);
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-checkout:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }

        .no-products {
            text-align: center;
            color: white;
            padding: 40px;
            font-size: 1.2rem;
        }

        .feature-menu-section {
            margin-bottom: 50px;
        }

        .feature-menu-section h2 {
            color: white;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .feature-menu-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .feature-menu-link:hover {
            text-decoration: none;
        }

        .feature-menu-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .feature-menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .feature-menu-card.locked-card {
            opacity: 0.85;
            position: relative;
        }

        .feature-menu-card.locked-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 12px;
        }

        .feature-menu-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .feature-menu-card.locked-card .feature-menu-icon {
            color: #ccc;
        }

        .feature-menu-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .feature-menu-text {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.5;
        }

        .lock-badge {
            display: block;
            background: #ffc107;
            color: #333;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            margin-top: 8px;
            font-style: italic;
        }

        .success-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--success-color);
            color: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            animation: slideInRight 0.5s ease, slideOutRight 0.5s ease 1.5s;
            opacity: 1;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .navbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .logout-btn:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .my-account-btn {
                padding: 6px 15px;
                font-size: 12px;
            }

            .product-section h2 {
                font-size: 1.8rem;
            }

            .jumbotron {
                padding: 40px 20px;
            }
        }

        /* User Dashboard Section Styles */
        .user-dashboard-section {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            padding: 40px 0;
            margin-bottom: 50px;
        }

        .user-dashboard-section h2 {
            color: white;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .user-dashboard-section h3 {
            color: white;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .user-info-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-bottom: 30px;
        }

        .user-info-card h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.3rem;
        }

        .user-info-item {
            margin-bottom: 20px;
        }

        .user-info-item label {
            color: #666;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            font-size: 0.9rem;
        }

        .user-info-item p {
            color: #333;
            font-size: 1.1rem;
            margin: 0;
        }

        .dashboard-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .dashboard-card-link:hover {
            text-decoration: none;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px 15px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .dashboard-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 12px;
        }

        .dashboard-card h6 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .dashboard-card p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bolt"></i> Smart Electric Shop
            </a>
            <div class="navbar-right">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                    <!-- My Account Dropdown for Users -->
                    <div class="my-account-dropdown">
                        <button class="my-account-btn">
                            <i class="fas fa-user-circle"></i>
                            <span><?= htmlspecialchars($_SESSION['name'] ?? 'Account') ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                        </button>
                        <div class="my-account-menu">
                            <div class="my-account-menu-header">
                                <p class="user-name"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['name'] ?? '') ?></p>
                                <p class="user-email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($_SESSION['email'] ?? '') ?></p>
                            </div>
                            <a href="cart.php" class="my-account-menu-item">
                                <i class="fas fa-shopping-cart"></i> Shopping Cart
                            </a>
                            <a href="my_orders.php" class="my-account-menu-item">
                                <i class="fas fa-receipt"></i> My Orders
                            </a>
                            <a href="my_warranty.php" class="my-account-menu-item">
                                <i class="fas fa-shield-alt"></i> Warranty Status
                            </a>
                            <a href="reward_points.php" class="my-account-menu-item">
                                <i class="fas fa-gift"></i> Reward Points
                            </a>
                            <a href="logout.php" class="my-account-menu-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary btn-sm">Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container container-main">
        <div class="jumbotron text-center">
            <h1 class="display-4">Smart Electric Shop</h1>
            <p class="lead">Your one-stop shop for all electrical products and services</p>
            <hr class="my-4">
            <p>Browse our premium collection of electrical products with warranty and energy usage information.</p>
        </div>

        <!-- Feature Cards removed (moved to navbar or feature menu) -->

        <!-- Feature Menu Section -->
        <div class="feature-menu-section mt-5">
            <h2 class="text-center mb-5"><i class="fas fa-th-large"></i> Shop Features</h2>
            <div class="row">
                <!-- Browse Products -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="#products" class="feature-menu-link">
                        <div class="feature-menu-card">
                            <div class="feature-menu-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h6 class="feature-menu-title">Browse Products</h6>
                            <p class="feature-menu-text">Explore our wide range of electrical products</p>
                        </div>
                    </a>
                </div>

                <!-- Shopping Cart -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php" class="feature-menu-link">
                        <?php else: ?>
                            <a href="login.php" class="feature-menu-link">
                            <?php endif; ?>
                            <div class="feature-menu-card <?php if (!isset($_SESSION['user_id'])) echo 'locked-card'; ?>">
                                <div class="feature-menu-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h6 class="feature-menu-title">Shopping Cart</h6>
                                <p class="feature-menu-text">View and manage your shopping cart <?php if (!isset($_SESSION['user_id'])) echo '<span class="lock-badge"><i class="fas fa-lock"></i> Login required</span>'; ?></p>
                            </div>
                            </a>
                </div>

                <!-- My Orders -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="my_orders.php" class="feature-menu-link">
                        <?php else: ?>
                            <a href="login.php" class="feature-menu-link">
                            <?php endif; ?>
                            <div class="feature-menu-card <?php if (!isset($_SESSION['user_id'])) echo 'locked-card'; ?>">
                                <div class="feature-menu-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <h6 class="feature-menu-title">My Orders</h6>
                                <p class="feature-menu-text">Track and manage your orders <?php if (!isset($_SESSION['user_id'])) echo '<span class="lock-badge"><i class="fas fa-lock"></i> Login required</span>'; ?></p>
                            </div>
                            </a>
                </div>





                <!-- Service Requests -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="service_request.php" class="feature-menu-link">
                        <?php else: ?>
                            <a href="login.php" class="feature-menu-link">
                            <?php endif; ?>
                            <div class="feature-menu-card <?php if (!isset($_SESSION['user_id'])) echo 'locked-card'; ?>">
                                <div class="feature-menu-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h6 class="feature-menu-title">Service Requests</h6>
                                <p class="feature-menu-text">Submit maintenance requests <?php if (!isset($_SESSION['user_id'])) echo '<span class="lock-badge"><i class="fas fa-lock"></i> Login required</span>'; ?></p>
                            </div>
                            </a>
                </div>

                <!-- Energy Calculator -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="energy_usage.php" class="feature-menu-link">
                        <div class="feature-menu-card">
                            <div class="feature-menu-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h6 class="feature-menu-title">Energy Calculator</h6>
                            <p class="feature-menu-text">Calculate energy usage and costs</p>
                        </div>
                    </a>
                </div>

                <!-- Contact Support -->
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="contact.php" class="feature-menu-link">
                        <div class="feature-menu-card">
                            <div class="feature-menu-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h6 class="feature-menu-title">Contact Support</h6>
                            <p class="feature-menu-text">Get help from our support team</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>



        <!-- Products Section -->
        <?php if (!empty($products)): ?>
            <div id="products" class="product-section">
                <h2><i class="fas fa-cube"></i> Available Products</h2>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product-card">
                                <div class="product-card-body">
                                    <?php
                                    $images = [];
                                    if (!empty($product['images'])) {
                                        $images = json_decode($product['images'], true);
                                    }
                                    $img_src = !empty($images) ? htmlspecialchars($images[0]) : 'images/default-product.png';
                                    ?>
                                    <a href="product_details.php?product_id=<?= $product['product_id'] ?>">
                                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;height:200px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                                    </a>
                                    <h5><a href="product_details.php?product_id=<?= $product['product_id'] ?>" style="color:inherit;text-decoration:none;"><?= htmlspecialchars($product['name']) ?></a></h5>
                                    <p class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                    <div class="price">৳ <?= number_format($product['price'], 2) ?></div>
                                    <div class="warranty">
                                        <i class="fas fa-clock"></i> Warranty: <?= htmlspecialchars($product['warranty_duration']) ?> months
                                    </div>
                                    <div class="available">
                                        <i class="fas fa-check-circle"></i> Available: <?= htmlspecialchars($product['available_quantity']) ?> units
                                    </div>
                                    <div class="product-actions">
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <form method="POST" action="cart.php" style="flex: 1;">
                                                <input type="hidden" name="prod_id" value="<?= $product['product_id'] ?>" />
                                                <input type="hidden" name="qty" value="1" />
                                                <button type="submit" class="btn-add-cart" style="width: 100%;">
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                            </form>
                                            <form method="POST" action="checkout.php" style="flex: 1;">
                                                <input type="hidden" name="prod_id" value="<?= $product['product_id'] ?>" />
                                                <input type="hidden" name="qty" value="1" />
                                                <button type="submit" class="btn-checkout" style="width: 100%;">
                                                    <i class="fas fa-money-check-alt"></i> Buy Now
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="login.php" class="btn-add-cart" style="text-align: center; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </a>
                                            <a href="login.php" class="btn-checkout" style="text-align: center; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-money-check-alt"></i> Buy Now
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-inbox"></i> No products available at the moment.
            </div>
        <?php endif; ?>
    </div>



    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Check for logout success message from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('logout_success')) {
            const popup = document.createElement('div');
            popup.className = 'success-popup';
            popup.innerHTML = '<i class="fas fa-check-circle"></i> Successfully logged out!';
            document.body.appendChild(popup);

            // Remove the popup after 2 seconds with animation
            setTimeout(() => {
                popup.remove();
            }, 2000);

            // Clean up the URL parameter
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Check for login success message from URL parameter
        if (urlParams.has('login_success')) {
            const popup = document.createElement('div');
            popup.className = 'success-popup';
            popup.innerHTML = '<i class="fas fa-check-circle"></i> Successfully logged in!';
            document.body.appendChild(popup);

            // Remove the popup after 2 seconds with animation
            setTimeout(() => {
                popup.remove();
            }, 2000);

            // Clean up the URL parameter
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>

</html>