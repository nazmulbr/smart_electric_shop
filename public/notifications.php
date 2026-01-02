<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$user_id = $_SESSION['user_id'];

// Mark as read if requested
if (isset($_GET['mark_read'])) {
    $nid = intval($_GET['mark_read']);
    $m = $conn->prepare('UPDATE Notifications SET is_read = 1 WHERE notifications_id = ? AND user_id = ?');
    if ($m) {
        $m->bind_param('ii', $nid, $user_id);
        $m->execute();
        $m->close();
    }
    header('Location: notifications.php');
    exit;
}

// Fetch notifications
$stmt = $conn->prepare('SELECT * FROM Notifications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$notifications = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Notifications - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Unread notification emphasis */
        .list-group-item.unread {
            background: #fff9e6;
            border-left: 4px solid #ffc107;
        }

        .list-group-item.read {
            background: #ffffff;
        }

        .list-group-item .mark-read-btn {
            min-width: 88px;
        }

        .list-group-item:hover {
            background: #f1f3f5;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h4>Your Notifications</h4>
        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">You have no notifications.</div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($notifications as $n): ?>
                    <?php $cls = $n['is_read'] ? 'read' : 'unread font-weight-bold'; ?>
                    <li class="list-group-item <?= $cls ?>" data-id="<?= $n['notifications_id'] ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?= htmlspecialchars($n['message']) ?><br>
                                <small class="text-muted"><?= $n['created_at'] ?></small>
                            </div>
                            <div>
                                <?php if (!$n['is_read']): ?>
                                    <a href="notifications.php?mark_read=<?= $n['notifications_id'] ?>" class="btn btn-sm btn-primary mark-read-btn" data-id="<?= $n['notifications_id'] ?>">Mark read</a>
                                <?php else: ?>
                                    <span class="text-muted">Read</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <script>
                // Immediate UI feedback when 'Mark read' clicked: reduce contrast before navigation
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.mark-read-btn').forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            // find list item and update styles
                            var id = this.getAttribute('data-id');
                            var li = document.querySelector('li.list-group-item[data-id="' + id + '"]');
                            if (li) {
                                li.classList.remove('unread');
                                li.classList.add('read');
                                li.style.opacity = '0.7';
                            }
                            // allow navigation to proceed (server will mark read and reload)
                        });
                    });
                });
            </script>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>
</body>

</html>