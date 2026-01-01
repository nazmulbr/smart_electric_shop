<?php
// Simple navbar with logo linking to homepage
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <style>
        /* Slightly stronger navbar text for better contrast */
        .navbar .nav-link {
            font-weight: 600;
            color: #222 !important;
        }

        .navbar .nav-link:hover {
            color: #007bff !important;
        }

        .navbar .navbar-brand img {
            filter: none;
        }
    </style>
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo.png" alt="Smart Electric Shop" style="height:42px;" onerror="this.style.display='none'">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                    <li class="nav-item"><a class="nav-link" href="my_warranty.php">Warranty</a></li>
                    <li class="nav-item"><a class="nav-link" href="reward_points.php">Rewards</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_orders.php">My Orders</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle" style="margin-right:6px;"></i> <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userMenu">
                            <a class="dropdown-item" href="user_dashboard.php">Dashboard</a>
                            <a class="dropdown-item" href="cart.php">Cart</a>
                            <a class="dropdown-item" href="my_orders.php">My Orders</a>
                            <a class="dropdown-item" href="my_warranty.php">Warranty</a>
                            <a class="dropdown-item" href="reward_points.php">Rewards</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>