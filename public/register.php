<?php
// User Registration Page
session_start();
require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

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
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone_number = trim($_POST['phone_number'] ?? '');
        
        if ($name && $email && $password) {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $register_err = 'Invalid email format.';
            } else {
                // Check if user exists
                $check_stmt = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
                if ($check_stmt) {
                    $check_stmt->bind_param('s', $email);
                    $check_stmt->execute();
                    $check_stmt->store_result();
                    
                    if ($check_stmt->num_rows == 0) {
                        $check_stmt->close();
                        
                        // Hash password
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $insert_stmt = $conn->prepare("INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)");
                        if ($insert_stmt) {
                            $insert_stmt->bind_param('ssss', $name, $email, $hashed, $phone_number);
                            if ($insert_stmt->execute()) {
                                $register_msg = 'Registration successful! <a href="login.php">Login Now</a>';
                                // Clear form data
                                $name = $email = $phone_number = '';
                            } else {
                                $register_err = showDbError($conn, "User Registration");
                                $register_err .= "<strong>Details:</strong><br>";
                                $register_err .= "Error Code: " . $conn->errno . "<br>";
                                $register_err .= "SQL State: " . $conn->sqlstate . "<br>";
                                $register_err .= "Attempted Query: INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)<br>";
                                $register_err .= "Parameters: name='$name', email='$email', phone='$phone_number'";
                            }
                            $insert_stmt->close();
                        } else {
                            $register_err = showDbError($conn, "Preparing INSERT statement");
                        }
                    } else {
                        $register_err = 'Email already registered. Please use a different email.';
                        $check_stmt->close();
                    }
                } else {
                    $register_err = showDbError($conn, "Checking for existing user");
                }
            }
        } else {
            $register_err = 'Please fill all required fields (Name, Email, Password)!';
        }
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
                    <div class="alert alert-danger">
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
                    <div class="alert alert-success"><?=$register_msg?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($name ?? '')?>" required />
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?=htmlspecialchars($email ?? '')?>" required />
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="<?=htmlspecialchars($phone_number ?? '')?>" />
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

