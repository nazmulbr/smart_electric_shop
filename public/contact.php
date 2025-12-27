<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message_text = $_POST['message'] ?? '';
    if ($name && $email && $subject && $message_text) {
        // Store contact info (you can create a Contact table or use ServiceRequest)
        // For now, we'll create a service request
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare('INSERT INTO ServiceRequest (user_id, issue, status) VALUES (?, ?, ?)');
        $issue = "Contact: $subject - $message_text";
        $status = 'Open';
        $stmt->bind_param('iss', $user_id, $issue, $status);
        $stmt->execute();
        $message = 'Your message has been sent! We will contact you soon.';
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