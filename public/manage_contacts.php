<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Ensure table exists (safe to run)
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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create);

// Handle response/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $mid = intval($_POST['message_id'] ?? 0);
    if ($action === 'respond' && $mid) {
        $response = $_POST['response_text'] ?? '';
        $admin_id = $_SESSION['user_id'];
        $up = $conn->prepare('UPDATE ContactMessages SET status = ?, response_text = ?, responded_by = ? WHERE message_id = ?');
        $status = 'Responded';
        $up->bind_param('ssii', $status, $response, $admin_id, $mid);
        $up->execute();
    } elseif ($action === 'close' && $mid) {
        $up = $conn->prepare('UPDATE ContactMessages SET status = ? WHERE message_id = ?');
        $st = 'Closed';
        $up->bind_param('si', $st, $mid);
        $up->execute();
    }
}

$stmt = $conn->prepare('SELECT cm.*, u.name AS user_name FROM ContactMessages cm LEFT JOIN User u ON cm.user_id = u.user_id ORDER BY created_at DESC');
$stmt->execute();
$res = $stmt->get_result();
$messages = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Contact Messages - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .small-col {
            max-width: 140px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary mb-2">Back</a>
        <h4>Contact Messages</h4>
        <?php if (empty($messages)): ?>
            <div class="alert alert-info">No messages.</div>
        <?php else: ?>
            <table class="table table-bordered bg-white">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Name / Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Response</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td><?= $m['message_id'] ?></td>
                            <td><?= htmlspecialchars($m['user_name'] ?? '') ?></td>
                            <td class="small-col"><?= htmlspecialchars($m['name']) ?><br><small><?= htmlspecialchars($m['email']) ?></small></td>
                            <td><?= htmlspecialchars($m['subject']) ?></td>
                            <td><?= htmlspecialchars(substr($m['message'], 0, 120)) ?><?= strlen($m['message']) > 120 ? '...' : '' ?></td>
                            <td><?= htmlspecialchars($m['status']) ?></td>
                            <td><?= htmlspecialchars(substr($m['response_text'] ?? '', 0, 80)) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="showRespond(<?= $m['message_id'] ?>, <?= htmlspecialchars(json_encode($m['message'])) ?>)">Respond</button>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="message_id" value="<?= $m['message_id'] ?>">
                                    <input type="hidden" name="action" value="close">
                                    <button class="btn btn-sm btn-warning" type="submit">Close</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="modal" id="respondModal" tabindex="-1" role="dialog" style="display:none;position:fixed;top:10%;left:50%;transform:translateX(-50%);z-index:1050;background:#fff;padding:20px;border:1px solid #ccc;box-shadow:0 4px 12px rgba(0,0,0,.15)">
        <h5>Respond to Message</h5>
        <form method="POST">
            <input type="hidden" name="message_id" id="resp_mid">
            <input type="hidden" name="action" value="respond">
            <div class="form-group">
                <label>Response</label>
                <textarea name="response_text" id="resp_text" class="form-control" rows="6"></textarea>
            </div>
            <button class="btn btn-primary">Send Response</button>
            <button type="button" class="btn btn-secondary" onclick="hideRespond()">Cancel</button>
        </form>
    </div>

    <script>
        function showRespond(id, orig) {
            document.getElementById('resp_mid').value = id;
            document.getElementById('resp_text').value = '\n\n--- Original message ---\n' + (orig || '');
            document.getElementById('respondModal').style.display = 'block';
        }

        function hideRespond() {
            document.getElementById('respondModal').style.display = 'none';
        }
    </script>
</body>

</html>