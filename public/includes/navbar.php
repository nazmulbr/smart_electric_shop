<?php
// Simple navbar with logo linking to homepage
if (session_status() === PHP_SESSION_NONE) session_start();
// Load DB when navbar needs notification counts
$unread_notifications = 0;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    require_once __DIR__ . '/../../config/db.php';
    $uid = $_SESSION['user_id'];
    // If the user is currently viewing notifications or viewing a contact reply, don't show the unread badge
    $current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $suppress_badge = in_array($current_script, ['notifications.php', 'contact_view.php']);
    // Only query Notifications if the table exists to avoid uncaught exceptions
    $tbl_check = $conn->query("SHOW TABLES LIKE 'Notifications'");
    if ($tbl_check && $tbl_check->num_rows > 0) {
        $nstmt = $conn->prepare('SELECT COUNT(*) FROM Notifications WHERE user_id = ? AND is_read = 0');
        if ($nstmt) {
            $nstmt->bind_param('i', $uid);
            $nstmt->execute();
            $nstmt->bind_result($unread_notifications);
            $nstmt->fetch();
            $nstmt->close();
        }
        if (!empty($suppress_badge)) $unread_notifications = 0;
    }
}
?>
<?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo.png" alt="Smart Electric Shop" style="height:32px;filter:grayscale(100%);" onerror="this.style.display='none'">
            </a>
            <div class="ml-auto">
                <a class="btn btn-sm btn-outline-light mr-2" href="index.php">Home</a>
                <a class="btn btn-sm btn-outline-danger" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
<?php else: ?>
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Notifications <?php if ($unread_notifications > 0): ?><span class="badge badge-danger"><?= $unread_notifications ?></span><?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notifDropdown" style="min-width:300px;">
                                <a class="dropdown-item" href="notifications.php">View notifications</a>
                            </div>
                        </li>
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
<?php endif; ?>