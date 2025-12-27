<?php
// Create Admin Account Page
// Access: http://localhost/smart_electric_shop/public/create_admin.php

require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if tables exist
if (!checkTableExists('Main_Admin') || !checkTableExists('Admin')) {
    die(showTableError('Main_Admin or Admin', 'Admin Account Creation'));
}

$message = '';
$success = false;

// Check if admin already exists
$admin_check = $conn->query("SELECT COUNT(*) as count FROM Admin");
$admin_count = $admin_check ? $admin_check->fetch_assoc()['count'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone_number'] ?? '');
    
    if ($name && $email && $password) {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format!';
        } else {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT admin_id FROM Admin WHERE email = ?");
            $check_stmt->bind_param('s', $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows == 0) {
                $check_stmt->close();
                
                // Create Main_Admin if doesn't exist
                $main_admin_check = $conn->query("SELECT main_id FROM Main_Admin LIMIT 1");
                if (!$main_admin_check || $main_admin_check->num_rows == 0) {
                    $conn->query("INSERT INTO Main_Admin (main_id, name) VALUES (1, 'System Administrator')");
                }
                $main_id = 1;
                
                // Hash password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert admin
                $stmt = $conn->prepare("INSERT INTO Admin (main_id, name, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param('issss', $main_id, $name, $email, $hashed, $phone);
                    if ($stmt->execute()) {
                        $message = "✅ Admin account created successfully!<br>";
                        $message .= "<strong>Email:</strong> $email<br>";
                        $message .= "<strong>Password:</strong> (the one you entered)<br><br>";
                        $message .= "<a href='login.php' class='btn btn-success'>Go to Login</a>";
                        $success = true;
                        $name = $email = $phone = '';
                    } else {
                        $message = showDbError($conn, "Admin Account Creation");
                    }
                    $stmt->close();
                } else {
                    $message = showDbError($conn, "Preparing INSERT statement");
                }
            } else {
                $message = 'Email already exists! Please use a different email.';
                $check_stmt->close();
            }
        }
    } else {
        $message = 'Please fill all required fields (Name, Email, Password)!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Admin Account - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3>🔐 Create Admin Account</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($admin_count > 0 && !$success): ?>
                            <div class="alert alert-info">
                                <strong>ℹ️ Note:</strong> There are already <?= $admin_count ?> admin account(s) in the database.
                                You can still create additional admin accounts.
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $success ? 'success' : 'danger' ?>">
                                <?php 
                                if (strpos($message, '<div') !== false || strpos($message, '<strong') !== false) {
                                    echo $message;
                                } else {
                                    echo htmlspecialchars($message);
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$success): ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required />
                            </div>
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required />
                            </div>
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6" />
                                <small class="form-text text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($phone ?? '') ?>" />
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Create Admin Account</button>
                        </form>
                        <?php endif; ?>
                        
                        <hr>
                        <div class="text-center">
                            <a href="test_db.php" class="btn btn-secondary">Back to Database Test</a>
                            <a href="login.php" class="btn btn-info">Go to Login</a>
                            <a href="index.php" class="btn btn-secondary">Home</a>
                        </div>
                        
                        <?php if ($admin_count == 0): ?>
                        <div class="alert alert-warning mt-3">
                            <strong>⚠️ First Time Setup:</strong><br>
                            This appears to be your first admin account. Make sure to remember your credentials!
                            <br><br>
                            <strong>Default Admin (if using schema):</strong><br>
                            Email: <code>admin@smartelectric.com</code><br>
                            Password: <code>admin123</code><br>
                            <small>(Change this password after first login!)</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

