<?php
// Require admin-only access
$require_role = 'admin';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';

$isEdit = isset($_GET['edit']);
$message = '';
$points_id = $user_id = $points = '';
if ($isEdit) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM RewardPoints WHERE points_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($r && $row = $r->fetch_assoc()) {
        $points_id = $row['points_id'];
        $user_id = $row['user_id'];
        $points = $row['points'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $points_id = intval($_POST['points_id'] ?? 0);
    $user_id = intval($_POST['user_id'] ?? 0);
    $points = intval($_POST['points'] ?? 0);
    if ($user_id && isset($points)) {
        if ($points_id) {
            $stmt = $conn->prepare('UPDATE RewardPoints SET points=? WHERE points_id=?');
            $stmt->bind_param('ii', $points, $points_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('INSERT INTO RewardPoints (user_id, points) VALUES (?, ?) ON DUPLICATE KEY UPDATE points=VALUES(points)');
            $stmt->bind_param('ii', $user_id, $points);
            $stmt->execute();
        }
        header('Location: manage_rewards.php');
        exit;
    } else {
        $message = 'All fields required!';
    }
}
// Get user list
$users = [];
$res = $conn->query('SELECT user_id, name FROM User ORDER BY name ASC');
if ($res) $users = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Reward Points - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_rewards.php" class="btn btn-secondary mb-2">Back to Rewards</a>
        <div class="card">
            <div class="card-header">
                <h4><?= $isEdit ? 'Edit' : 'Add' ?> Reward Points</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="points_id" value="<?= htmlspecialchars($points_id) ?>" />
                    <div class="form-group">
                        <label>User</label>
                        <select name="user_id" class="form-control" required <?= $isEdit ? 'disabled' : ''; ?>>
                            <option value="">Select User</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['user_id'] ?>" <?= ($u['user_id'] == $user_id) ? 'selected' : '' ?>><?= $u['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="points" value="<?= htmlspecialchars($points) ?>" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-success"><?= $isEdit ? 'Update' : 'Add' ?> Reward</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>