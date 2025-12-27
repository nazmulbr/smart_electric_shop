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
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 999;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .my-account-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            color: white;
            text-decoration: none;
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
                width: 50px;
                height: 50px;
                font-size: 20px;
                bottom: 20px;
                right: 20px;
            }

            .product-section h2 {
                font-size: 1.8rem;
            }

            .jumbotron {
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bolt"></i> Smart Electric Shop
            </a>
            <div class="navbar-right">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="navbar-text mr-3">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? '') ?></span>
                    <a href="logout.php" class="logout-btn">Logout</a>
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
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="btn btn-primary btn-auth btn-lg" href="login.php" role="button">Login</a>
                <a class="btn btn-success btn-auth btn-lg" href="register.php" role="button">Register</a>
            <?php else: ?>
                <p class="text-success"><i class="fas fa-check-circle"></i> You're logged in. Browse and shop!</p>
            <?php endif; ?>
        </div>

        <!-- Feature Cards -->
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="card-body text-center">
                        <div class="feature-card-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h5 class="card-title">Quality Products</h5>
                        <p class="card-text">Browse and purchase electrical products with detailed specifications.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="my_warranty.php" class="feature-card-link">
                        <div class="feature-card">
                            <div class="card-body text-center">
                                <div class="feature-card-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h5 class="card-title">Warranty Tracking</h5>
                                <p class="card-text">Track your product warranties and get notified before expiry.</p>
                            </div>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="feature-card-link">
                        <div class="feature-card">
                            <div class="card-body text-center">
                                <div class="feature-card-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h5 class="card-title">Warranty Tracking</h5>
                                <p class="card-text">Track your product warranties and get notified before expiry. <span class="text-warning"><strong>(Login required)</strong></span></p>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="reward_points.php" class="feature-card-link">
                        <div class="feature-card">
                            <div class="card-body text-center">
                                <div class="feature-card-icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <h5 class="card-title">Reward Points</h5>
                                <p class="card-text">Earn and redeem reward points with every purchase.</p>
                            </div>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="feature-card-link">
                        <div class="feature-card">
                            <div class="card-body text-center">
                                <div class="feature-card-icon">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <h5 class="card-title">Reward Points</h5>
                                <p class="card-text">Earn and redeem reward points with every purchase. <span class="text-warning"><strong>(Login required)</strong></span></p>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Products Section -->
            <?php if (!empty($products)): ?>
                <div class="product-section">
                    <h2><i class="fas fa-cube"></i> Available Products</h2>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="product-card">
                                    <div class="product-card-body">
                                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                                        <p class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                        <div class="price">$<?= number_format($product['price'], 2) ?></div>
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

        <!-- My Account Button (Only visible when logged in) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="user_dashboard.php" class="my-account-btn" title="My Account">
                <i class="fas fa-user"></i>
            </a>
        <?php endif; ?>

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
        </script>
</body>

</html>