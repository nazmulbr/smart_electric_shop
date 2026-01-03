<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$uid = $_SESSION['user_id'];
$mid = intval($_GET['message_id'] ?? 0);
$notif_id = intval($_GET['notif_id'] ?? 0);

if (!$mid) {
    header('Location: contact.php');
    exit;
}

// Optionally mark the notification as read
if ($notif_id) {
    $m = $conn->prepare('UPDATE Notifications SET is_read = 1 WHERE notifications_id = ? AND user_id = ?');
    if ($m) {
        $m->bind_param('ii', $notif_id, $uid);
        $m->execute();
        $m->close();
    }
}

// Fetch the contact message owned by this user
$stmt = $conn->prepare('SELECT cm.*, a.name AS responder_name FROM ContactMessages cm LEFT JOIN Admin a ON cm.responded_by = a.admin_id WHERE cm.message_id = ? AND cm.user_id = ? LIMIT 1');
$stmt->bind_param('ii', $mid, $uid);
$stmt->execute();
$res = $stmt->get_result();
$msg = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$msg) {
    $error = 'Message not found.';
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>View Support Reply - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <a href="contact.php" class="btn btn-secondary mb-3">Back to Contact</a>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <strong><?= htmlspecialchars($msg['subject']) ?></strong>
                    <div class="float-right text-muted small">Sent: <?= htmlspecialchars($msg['created_at']) ?></div>
                </div>
                <div class="card-body">
                    <h6>Your message</h6>
                    <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                    <hr>
                    <h6>Response</h6>
                    <?php if (!empty($msg['response_text'])): ?>
                        <p><?= nl2br(htmlspecialchars($msg['response_text'])) ?></p>
                        <p class="text-muted small">Responded by: <?= htmlspecialchars($msg['responder_name'] ?? 'Staff') ?></p>
                    <?php else: ?>
                        <div class="alert alert-info">No response yet. You will be notified when staff replies.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>