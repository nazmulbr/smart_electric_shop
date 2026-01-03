<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message_text = $_POST['message'] ?? '';
    if ($name && $email && $subject && $message_text) {
        // Ensure ContactMessages table exists (with foreign key to User)
        $create = "CREATE TABLE IF NOT EXISTS ContactMessages (
            message_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NULL,
            name VARCHAR(100),
            email VARCHAR(150),
            subject VARCHAR(255),
            message TEXT,
            status VARCHAR(30) DEFAULT 'Open',
            response_text TEXT NULL,
            responded_by INT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES User(user_id)
        )";
        $conn->query($create);

        $user_id = $_SESSION['user_id'];
        $ins = $conn->prepare('INSERT INTO ContactMessages (user_id, name, email, subject, message, status) VALUES (?, ?, ?, ?, ?, ?)');
        $status = 'Open';
        $ins->bind_param('isssss', $user_id, $name, $email, $subject, $message_text, $status);
        if ($ins->execute()) {
            $message = 'Your message has been sent! We will contact you soon.';
        } else {
            $message = 'Failed to send message. Please try again later.';
        }
    } else {
        $message = 'Please fill all fields.';
    }
}
$user = $_SESSION;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Contact Support - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Contact Support</h4>
        <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Shop Contact Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Email:</strong> support@smartelectric.com</p>
                        <p><strong>Phone:</strong> +880-1234-567890</p>
                        <p><strong>Address:</strong> 123 Electric Street, Dhaka, Bangladesh</p>
                        <p><strong>Hours:</strong> Mon-Sat: 9AM-8PM</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Send Message</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required />
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required />
                            </div>
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" class="form-control" required />
                            </div>
                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" class="form-control" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>