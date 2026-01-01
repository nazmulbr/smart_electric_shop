<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php?redirect=rewards');
    exit;
}

$page_title = 'My Reward Points - Smart Electric Shop';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE user_id = ?');
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($points);
    $stmt->fetch();
    $stmt->close();
}
if (!isset($points)) $points = 0;
?>

<div class="container mt-4">
    <div class="card">
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
</div>

</body>

</html>