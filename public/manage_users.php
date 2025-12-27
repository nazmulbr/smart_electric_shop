<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if User table exists
if (!checkTableExists('User')) {
    die(showTableError('User', 'User Management'));
}

$msg = '';
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'] == 'added' ? 'User added successfully!' : ($_GET['msg'] == 'updated' ? 'User updated successfully!' : ($_GET['msg'] == 'deleted' ? 'User deleted successfully!' : ''));
}

// Handle deletion
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM User WHERE user_id=?');
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        if ($stmt->execute()) {
            header('Location: manage_users.php?msg=deleted');
            exit;
        } else {
            $msg = 'Delete failed: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $msg = 'Database error: ' . $conn->error;
    }
}

// Get users
$users = [];
$result = $conn->query('SELECT * FROM User ORDER BY user_id DESC');
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $msg = 'Error loading users: ' . $conn->error;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h4>User Management</h4>
        <?php if ($msg): ?>
            <div class="alert alert-<?=strpos($msg, 'failed') !== false || strpos($msg, 'Error') !== false ? 'danger' : 'success'?>"><?=htmlspecialchars($msg)?></div>
        <?php endif; ?>
        <div class="mb-2">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?=htmlspecialchars($u['user_id'])?></td>
                    <td><?=htmlspecialchars($u['name'])?></td>
                    <td><?=htmlspecialchars($u['email'])?></td>
                    <td><?=htmlspecialchars($u['phone_number'])?></td>
                    <td>
                        <a href="user_form.php?edit=<?=$u['user_id']?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="manage_users.php?delete=<?=$u['user_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

