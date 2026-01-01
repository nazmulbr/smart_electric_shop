<?php
session_start();
require_once '../config/db.php';
require_once '../config/error_handler.php';
require_once '../config/db_check.php';

// Ensure PasswordReset table exists
$createSql = "CREATE TABLE IF NOT EXISTS PasswordReset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (token),
    INDEX (user_id)
);";
$conn->query($createSql);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        // find user
        $stmt = $conn->prepare('SELECT user_id, name FROM User WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $user_id = $row['user_id'];
            $name = $row['name'];
            // generate token
            $token = bin2hex(random_bytes(24));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $ins = $conn->prepare('INSERT INTO PasswordReset (user_id, token, expires_at) VALUES (?, ?, ?)');
            $ins->bind_param('iss', $user_id, $token, $expires);
            $ins->execute();
            $ins->close();

            // Build reset link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $reset_link = $protocol . '://' . $host . $path . '/reset_password.php?token=' . $token;

            // Try to send email (best effort). If mail isn't configured, display the link for testing.
            $subject = 'Password reset for Smart Electric Shop';
            $body = "Hello " . htmlspecialchars($name) . ",\n\n";
            $body .= "We received a request to reset your password. Click the link below to reset your password (valid for 1 hour):\n\n";
            $body .= $reset_link . "\n\nIf you did not request this, please ignore this email.\n\n-- Smart Electric Shop";
            $headers = 'From: no-reply@' . $_SERVER['HTTP_HOST'];
            @mail($email, $subject, $body, $headers);

            $message = 'If that email exists in our system an email has been sent. For local testing, here is the reset link:';
            $message .= "<br><a href=\"" . htmlspecialchars($reset_link) . "\">" . htmlspecialchars($reset_link) . "</a>";
        } else {
            // don't reveal which emails exist
            $message = 'If that email exists in our system an email has been sent.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Forgot Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= $message ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>Email address</label>
                                <input type="email" name="email" class="form-control" required />
                            </div>
                            <button class="btn btn-primary">Send Reset Link</button>
                            <a href="login.php" class="btn btn-secondary ml-2">Back to Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>