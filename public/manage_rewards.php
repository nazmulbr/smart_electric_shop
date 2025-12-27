<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); exit;
}
require_once '../config/db.php';
$result = $conn->query('SELECT r.points_id, u.name as user_name, r.points FROM RewardPoints r JOIN User u ON r.user_id = u.user_id');
$rewards = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reward Points Management - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h4>Manage Reward Points</h4>
        <div class="mb-2">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="reward_form.php" class="btn btn-success">Add/Update Reward</a>
        </div>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr><th>#</th><th>User</th><th>Points</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rewards as $rw): ?>
                <tr>
                    <td><?=htmlspecialchars($rw['points_id'])?></td>
                    <td><?=htmlspecialchars($rw['user_name'])?></td>
                    <td><?=htmlspecialchars($rw['points'])?></td>
                    <td>
                        <a href="reward_form.php?edit=<?=$rw['points_id']?>" class="btn btn-primary btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

