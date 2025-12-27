<?php
// Login page for Admin, Staff, and User
session_start();
require_once '../config/db.php';

$login_err = '';

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        // Try Admin login
        $sql = "SELECT 'admin' AS role, admin_id AS id, email, password, name FROM Admin WHERE email = ? UNION ALL
                SELECT 'staff', staff_id, email, password, name FROM Staff WHERE email = ? UNION ALL
                SELECT 'user', user_id, email, password, name FROM User WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $email, $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['name'] = $row['name'];
                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } elseif ($row['role'] === 'staff') {
                    header('Location: staff_dashboard.php');
                } else {
                    header('Location: user_dashboard.php');
                }
                exit;
            } else {
                $login_err = 'Invalid password.';
            }
        } else {
            $login_err = 'No account found for this email.';
        }
    } else {
        $login_err = 'Please enter both email and password.';
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

