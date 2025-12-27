<?php
// Login page for Admin, Staff, and User
session_start();
require_once '../config/db.php';

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
                    if (password_verify($password, $row['password'])) {
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
                        }
                    } else {
                        if (empty($login_err)) {
                            $login_err = 'No account found for this email.';
                        }
                    }
                    $stmt->close();
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
                    header('Location: user_dashboard.php');
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
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h3 class="text-center">Login</h3>
                <?php if ($login_err): ?>
                    <div class="alert alert-danger"><?=$login_err?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="register.php">User Registration</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

