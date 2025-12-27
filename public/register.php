<?php
// User Registration Page
require_once '../config/db.php';

$register_err = '';
$register_msg = '';

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    if ($name && $email && $password) {
        // Check if user exists
        $stmt = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $name, $email, $hashed, $phone_number);
            if ($stmt->execute()) {
                $register_msg = 'Registration successful. <a href=\'login.php\'>Login Now</a>';
            } else {
                $register_err = 'Registration failed.';
            }
        } else {
            $register_err = 'Email already registered.';
        }
    } else {
        $register_err = 'Fill all required fields!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-secondary text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <h3 class="text-center">User Registration</h3>
                <?php if ($register_err): ?>
                    <div class="alert alert-danger"><?=$register_err?></div>
                <?php elseif ($register_msg): ?>
                    <div class="alert alert-success"><?=$register_msg?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Register</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="login.php">Already have an account? Login here.</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

