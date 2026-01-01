<?php
// User Registration Page
session_start();
require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

// Enable mysqli exceptions for clearer error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if User table exists
if (!checkTableExists('User')) {
    die(showTableError('User', 'User Registration'));
}

$register_err = '';
$register_msg = '';
$name = '';
$email = '';
$phone_number = '';

// Check database connection
if ($conn->connect_error) {
    $register_err = 'Database connection failed. Please check your configuration.';
} else {
    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone_number = trim($_POST['phone_number'] ?? '');

            if (!$name || !$email || !$password) {
                $register_err = 'Please fill all required fields (Name, Email, Password)!';
            } else {
                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $register_err = 'Invalid email format.';
                } else {
                    // Enforce password policy: at least 8 chars, contains letters and digits
                    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) {
                        $register_err = 'Password must be at least 8 characters long and contain both letters and numbers.';
                    } else {
                        // Check if user exists
                        $check_stmt = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
                        if (!$check_stmt) throw new Exception("Prepare failed (check user): " . $conn->error);
                        $check_stmt->bind_param('s', $email);
                        $check_stmt->execute();
                        $check_stmt->store_result();

                        if ($check_stmt->num_rows == 0) {
                            $check_stmt->close();

                            // Hash password
                            $hashed = password_hash($password, PASSWORD_DEFAULT);

                            // Insert new user
                            $insert_stmt = $conn->prepare("INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)");
                            if (!$insert_stmt) throw new Exception("Prepare failed (insert user): " . $conn->error);
                            $insert_stmt->bind_param('ssss', $name, $email, $hashed, $phone_number);
                            if ($insert_stmt->execute()) {
                                $register_msg = 'Registration successful! <a href="login.php">Login Now</a>';
                                // Clear form data
                                $name = $email = $phone_number = '';
                            } else {
                                // Detailed DB error
                                $register_err = showDbError($conn, "User Registration");
                                $register_err .= "<strong>Attempted Query:</strong><br>INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?) <br>";
                                $register_err .= "<strong>Parameters:</strong> name='" . htmlspecialchars($name) . "', email='" . htmlspecialchars($email) . "', phone='" . htmlspecialchars($phone_number) . "'";
                            }
                            $insert_stmt->close();
                        } else {
                            $register_err = 'Email already registered. Please use a different email.';
                            $check_stmt->close();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $register_err = "<div class='alert alert-danger'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>User Registration - Smart Electric Shop</title>
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

        .register-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            padding: 60px 40px;
            margin-top: 60px;
            margin-bottom: 60px;
        }

        .register-card h3 {
            color: var(--success-color);
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

        .btn-register {
            background-color: var(--success-color);
            color: white;
            padding: 12px;
            font-size: 1rem;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
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

        .alert-success {
            background-color: #d4edda;
            color: #155724;
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

            .register-card {
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
            <div class="col-md-6">
                <div class="register-card">
                    <h3><i class="fas fa-user-plus"></i> User Registration</h3>
                    <?php if ($register_err): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php
                            // Check if it's already HTML formatted (from showDbError)
                            if (strpos($register_err, '<div') !== false || strpos($register_err, '<strong') !== false) {
                                echo $register_err;
                            } else {
                                echo htmlspecialchars($register_err);
                            }
                            ?>
                        </div>
                    <?php elseif ($register_msg): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= $register_msg ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required />
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required />
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}" title="At least 8 characters, including letters and numbers" />
                            <small class="form-text text-muted">Password must be at least 8 characters and contain both letters and numbers.</small>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($phone_number ?? '') ?>" />
                        </div>
                        <button type="submit" class="btn btn-register btn-block">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </form>
                    <div class="text-center-custom">
                        Already have an account? <a href="login.php">Login here</a>
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
</body>

</html>