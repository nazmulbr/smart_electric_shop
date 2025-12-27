<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php?redirect=rewards');
    exit;
}
require_once '../config/db.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($points);
$stmt->fetch();
$stmt->close();
if (!isset($points)) $points = 0;
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Reward Points - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Your Reward Points</h4>
        <table class="table table-bordered col-md-4 bg-white">
            <tr>
                <th>Points Balance</th>
                <td><?= htmlspecialchars($points) ?></td>
            </tr>
        </table>
        <div class="alert alert-info">You earn points from purchases. Points may be redeemed for discounts at checkout (if enabled).</div>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>