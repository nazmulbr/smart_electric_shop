<?php
// Create Default Admin Account
// Access: http://localhost/smart_electric_shop/public/create_default_admin.php
// This creates the default admin account with full access to all admin features

require_once '../config/db.php';
require_once '../config/db_check.php';

$message = '';
$success = false;

// Check if tables exist
if (!checkTableExists('Admin') || !checkTableExists('Main_Admin')) {
    die("❌ Error: Admin tables don't exist. Please run database initialization first at init_database.php");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_default'])) {
    // Create Main_Admin if doesn't exist
    $main_admin_check = $conn->query("SELECT main_id FROM Main_Admin LIMIT 1");
    if (!$main_admin_check || $main_admin_check->num_rows == 0) {
        $conn->query("INSERT INTO Main_Admin (main_id, name) VALUES (1, 'System Administrator')");
    }

    $main_id = 1;
    $name = 'Administrator';
    $email = 'admin@smartelectric.com';
    $password = 'admin123';
    $phone = '1234567890';

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin already exists
    $check_stmt = $conn->prepare("SELECT admin_id FROM Admin WHERE email = ?");
    $check_stmt->bind_param('s', $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $message = "⚠️ Default admin account already exists! (Email: admin@smartelectric.com)";
        $success = false;
    } else {
        $check_stmt->close();

        // Insert admin
        $stmt = $conn->prepare("INSERT INTO Admin (main_id, name, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('issss', $main_id, $name, $email, $hashed, $phone);
            if ($stmt->execute()) {
                $message = "✅ Default admin account created successfully!<br>";
                $message .= "<strong>Email:</strong> admin@smartelectric.com<br>";
                $message .= "<strong>Password:</strong> admin123<br>";
                $message .= "<strong>⚠️ Important:</strong> Change this password after your first login!<br><br>";
                $message .= "<a href='login.php' class='btn btn-success'>Go to Login</a>";
                $success = true;
            } else {
                $message = "❌ Error creating admin account: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Check if default admin exists
$admin_check = $conn->query("SELECT admin_id, name, email FROM Admin WHERE email = 'admin@smartelectric.com'");
$default_admin_exists = ($admin_check && $admin_check->num_rows > 0);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Default Admin - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            border: none;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-radius: 10px 10px 0 0 !important;
        }

        .btn {
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-white">
                        <h3><i class="fas fa-shield-alt"></i> Create Default Admin Account</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $success ? 'success' : 'warning' ?>" role="alert">
                                <?= $message ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($default_admin_exists): ?>
                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle"></i> Default Admin Already Exists</strong><br>
                                The default admin account is already set up and ready to use.
                                <br><br>
                                <strong>Login Credentials:</strong><br>
                                Email: <code>admin@smartelectric.com</code><br>
                                Password: <code>admin123</code>
                                <br><br>
                                <a href="login.php" class="btn btn-primary">Go to Login</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong><i class="fas fa-exclamation-triangle"></i> Default Admin Not Found</strong><br>
                                Click the button below to create the default admin account that has access to all admin features.
                            </div>

                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6>Default Admin Credentials:</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Email:</strong> admin@smartelectric.com</li>
                                        <li><strong>Password:</strong> admin123</li>
                                        <li><strong>Name:</strong> Administrator</li>
                                        <li><strong>Access:</strong> All admin features</li>
                                    </ul>
                                    <hr>
                                    <p class="text-danger small">
                                        <i class="fas fa-lock"></i> <strong>Security Note:</strong>
                                        Please change the password after your first login.
                                    </p>
                                </div>
                            </div>

                            <form method="POST">
                                <button type="submit" name="create_default" class="btn btn-success btn-block btn-lg">
                                    <i class="fas fa-user-plus"></i> Create Default Admin Account
                                </button>
                            </form>
                        <?php endif; ?>

                        <hr>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-secondary"><i class="fas fa-home"></i> Back to Home</a>
                            <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>