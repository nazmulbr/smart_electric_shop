<?php

/**
 * Admin Login Diagnostic & Fix Tool
 * Access: http://localhost/smart_electric_shop/public/admin_login_fix.php
 */

require_once '../config/db.php';
require_once '../config/db_check.php';

$message = '';
$status = '';

// Check if Admin table exists
if (!checkTableExists('Admin')) {
    die('<div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px;">
        <h3>❌ Error: Admin table does not exist!</h3>
        <p>Please initialize the database first by visiting <a href="init_database.php">init_database.php</a></p>
    </div>');
}

// Check admin count
$adminCheck = $conn->query("SELECT COUNT(*) as count FROM Admin");
$adminCount = $adminCheck ? $adminCheck->fetch_assoc()['count'] : 0;

// Check for specific admin
$defaultAdminCheck = $conn->query("SELECT * FROM Admin WHERE email = 'admin@smartelectric.com'");
$defaultAdminExists = $defaultAdminCheck && $defaultAdminCheck->num_rows > 0;

// Handle form to create default admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_default_admin'])) {
    // First, ensure Main_Admin exists
    $mainAdminCheck = $conn->query("SELECT main_id FROM Main_Admin WHERE main_id = 1");
    if (!$mainAdminCheck || $mainAdminCheck->num_rows == 0) {
        $conn->query("INSERT INTO Main_Admin (main_id, name) VALUES (1, 'System Administrator')");
    }

    // Create default admin account
    $email = 'admin@smartelectric.com';
    $password = 'admin123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $checkStmt = $conn->prepare("SELECT admin_id FROM Admin WHERE email = ?");
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows == 0) {
        $checkStmt->close();

        $insertStmt = $conn->prepare("INSERT INTO Admin (main_id, name, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
        $mainId = 1;
        $name = 'Administrator';
        $phone = '1234567890';

        $insertStmt->bind_param('issss', $mainId, $name, $email, $passwordHash, $phone);
        if ($insertStmt->execute()) {
            $message = '✅ Default admin account created successfully!';
            $status = 'success';
            $defaultAdminExists = true;
            $adminCount++;
        } else {
            $message = '❌ Error creating admin: ' . $conn->error;
            $status = 'error';
        }
        $insertStmt->close();
    } else {
        $message = '⚠️ Default admin account already exists!';
        $status = 'warning';
        $checkStmt->close();
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login Fix - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .btn-primary {
            background: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container" style="max-width: 600px;">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3><i class="fas fa-lock"></i> Admin Login Diagnostic</h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="status-box status-<?= $status ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <h4>Current Status</h4>
                <div class="status-box status-info">
                    <p><strong>Admin Table:</strong> ✅ Exists</p>
                    <p><strong>Admin Accounts:</strong> <?= $adminCount > 0 ? '✅ ' . $adminCount . ' account(s)' : '❌ No accounts' ?></p>
                    <p><strong>Default Admin (admin@smartelectric.com):</strong>
                        <?= $defaultAdminExists ? '✅ Exists' : '❌ Missing' ?>
                    </p>
                </div>

                <?php if (!$defaultAdminExists): ?>
                    <hr>
                    <h4>Fix Admin Login</h4>
                    <p>The default admin account does not exist. Click the button below to create it.</p>
                    <form method="POST">
                        <button type="submit" name="create_default_admin" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-plus"></i> Create Default Admin Account
                        </button>
                    </form>
                    <hr>
                    <div class="alert alert-info">
                        <strong>Default Credentials:</strong><br>
                        <strong>Email:</strong> admin@smartelectric.com<br>
                        <strong>Password:</strong> admin123<br>
                        <small>⚠️ Change the password immediately after first login!</small>
                    </div>
                <?php else: ?>
                    <hr>
                    <h4>✅ Admin Account Exists</h4>
                    <p>The admin account is already set up. If you're having login issues:</p>
                    <ul>
                        <li>Make sure you're using the correct email and password</li>
                        <li>Check that the password is correct (default: admin123)</li>
                        <li>Try resetting the password using create_admin.php</li>
                    </ul>
                    <div class="alert alert-warning">
                        <strong>To create a new admin account:</strong><br>
                        <a href="create_admin.php" class="btn btn-warning btn-sm">Create New Admin Account</a>
                    </div>
                <?php endif; ?>

                <hr>
                <h4>Troubleshooting</h4>
                <ol>
                    <li><a href="login.php">Go to Login Page</a></li>
                    <li><a href="init_database.php">Initialize Database</a></li>
                    <li><a href="create_admin.php">Create Admin Account</a></li>
                    <li><a href="test_db.php">Test Database Connection</a></li>
                </ol>
            </div>
        </div>
    </div>
</body>

</html>