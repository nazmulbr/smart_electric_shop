<?php
// Login page for Admin, Staff, and User
session_start();
require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if required tables exist
if (!checkTableExists('User') || !checkTableExists('Admin') || !checkTableExists('Staff')) {
    $missing = [];
    if (!checkTableExists('User')) $missing[] = 'User';
    if (!checkTableExists('Admin')) $missing[] = 'Admin';
    if (!checkTableExists('Staff')) $missing[] = 'Staff';
    die(showTableError(implode(', ', $missing), 'User Login'));
}

$login_err = '';

// Check database connection
if ($conn->connect_error) {
    $login_err = 'Database connection failed. Please check your configuration.';
} else {
    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email && $password) {
            $user_found = false;
            $user_data = null;

            // Try Admin login first
            $stmt = $conn->prepare("SELECT admin_id AS id, email, password, name FROM Admin WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $row = $result->fetch_assoc()) {
                    if (password_verify($password, $row['password']) || $password === $row['password']) {
                        $user_data = [
                            'id' => $row['id'],
                            'role' => 'admin',
                            'email' => $row['email'],
                            'name' => $row['name']
                        ];
                        $user_found = true;
                    } else {
                        $login_err = 'Invalid password.';
                    }
                }
                $stmt->close();
            }

            // Admin login requires both matching email and password. No fallback by password alone.

            // Try Staff login if admin not found
            if (!$user_found) {
                $stmt = $conn->prepare("SELECT staff_id AS id, email, password, name FROM Staff WHERE email = ?");
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $row = $result->fetch_assoc()) {
                        if (password_verify($password, $row['password'])) {
                            $user_data = [
                                'id' => $row['id'],
                                'role' => 'staff',
                                'email' => $row['email'],
                                'name' => $row['name']
                            ];
                            $user_found = true;
                        } else {
                            $login_err = 'Invalid password.';
                        }
                    }
                    $stmt->close();
                }
            }

            // Try User login if staff not found
            if (!$user_found) {
                $stmt = $conn->prepare("SELECT user_id AS id, email, password, name FROM User WHERE email = ?");
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $row = $result->fetch_assoc()) {
                        if (password_verify($password, $row['password'])) {
                            $user_data = [
                                'id' => $row['id'],
                                'role' => 'user',
                                'email' => $row['email'],
                                'name' => $row['name']
                            ];
                            $user_found = true;
                        } else {
                            $login_err = 'Invalid password.';
                            $login_err .= "<br><small>Email: $email</small>";
                        }
                    } else {
                        if (empty($login_err)) {
                            $login_err = 'No account found for this email.';
                            $login_err .= "<br><small>Email: $email</small>";
                        }
                    }
                    if ($stmt) $stmt->close();
                } else {
                    if (empty($login_err)) {
                        $login_err = showDbError($conn, "User Login Query");
                        $login_err .= "<strong>Query Details:</strong><br>";
                        $login_err .= "Email: $email<br>";
                        $login_err .= "Table: User";
                    }
                }
            }

            // If user found and authenticated, set session and redirect
            if ($user_found && $user_data) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['role'] = $user_data['role'];
                $_SESSION['email'] = $user_data['email'];
                $_SESSION['name'] = $user_data['name'];

                // Redirect based on role
                if ($user_data['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } elseif ($user_data['role'] === 'staff') {
                    header('Location: staff_dashboard.php');
                } else {
                    // Regular users go to homepage instead of dashboard
                    header('Location: index.php?login_success=1');
                }
                exit;
            }
        } else {
            $login_err = 'Please enter both email and password.';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login - Smart Electric Shop</title>
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

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            padding: 60px 40px;
            margin-top: 60px;
            margin-bottom: 60px;
        }

        .login-card h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group label {
            color: #333;
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-login {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            font-size: 1rem;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 123, 255, 0.4);
            color: white;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 25px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .text-center-custom {
            text-align: center;
            margin-top: 20px;
        }

        .text-center-custom a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .text-center-custom a:hover {
            color: #0056b3;
            text-decoration: underline;
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

            .login-card {
                padding: 40px 20px;
                margin-top: 30px;
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
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <h3><i class="fas fa-sign-in-alt"></i> Login</h3>
                    <?php if ($login_err): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= $login_err ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Password</label>
                            <input id="password" type="password" name="password" class="form-control" required />
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="" id="showPassword">
                                <label class="form-check-label" for="showPassword">Show password</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-login btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="forgot_password.php">Forgot password?</a>
                    </div>
                    <hr>
                    <div class="text-center-custom">
                        Don't have an account? <a href="register.php">Register here</a>
                    </div>
                    <div class="text-center-custom">
                        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
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
        (function() {
            var cb = document.getElementById('showPassword');
            if (cb) {
                cb.addEventListener('change', function() {
                    var p = document.getElementById('password');
                    if (p) p.type = this.checked ? 'text' : 'password';
                });
            }
        })();
    </script>
</body>

</html>