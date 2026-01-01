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

        /* Position nav items in the top-right of the navbar container on wide screens */
        .navbar {
            position: relative;
        }

        .navbar .navbar-nav {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Ensure brand remains on the left and doesn't get overlapped */
        .navbar .navbar-brand {
            margin-right: 0.5rem;
        }

        /* On small screens, revert to normal stacked behavior */
        @media (max-width: 991px) {
            .navbar .navbar-nav {
                position: static;
                transform: none;
                margin-top: 0.5rem;
            }
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
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>