<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';
require_once '../config/error_handler.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Handle new service request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue = $_POST['issue'] ?? '';
    $warranty_id = intval($_POST['warranty_id'] ?? 0);
    if ($issue) {
        $stmt = $conn->prepare('INSERT INTO ServiceRequest (user_id, warranty_id, issue, status) VALUES (?, ?, ?, ?)');
        $status = 'Open';
        $stmt->bind_param('iiss', $user_id, $warranty_id, $issue, $status);
        $stmt->execute();
        $message = 'Your service request has been submitted!';
    } else {
        $message = 'Please describe your issue.';
    }
}
// Get my requests
// Get my requests (use get_result once)
$stmt = $conn->prepare('SELECT s.*, w.purchase_date FROM ServiceRequest s LEFT JOIN Warranty w ON s.warranty_id = w.warranty_id WHERE s.user_id = ? ORDER BY s.request_id DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$requests = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
// Get warranties for dropdown (prepared statement)
$wstmt = $conn->prepare("SELECT w.warranty_id, w.purchase_date FROM Warranty w JOIN User u ON u.warranty_id = w.warranty_id WHERE u.user_id = ?");
if ($wstmt) {
    $wstmt->bind_param('i', $user_id);
    $wstmt->execute();
    $wres = $wstmt->get_result();
    $warranty_options = $wres ? $wres->fetch_all(MYSQLI_ASSOC) : [];
    $wstmt->close();
} else {
    $warranty_options = [];
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Service Requests - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>Submit Service Request</h4>
        <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
        <form method="POST" class="mb-3">
            <div class="form-group">
                <label>Warranty</label>
                <select name="warranty_id" class="form-control">
                    <option value="0">(optional)</option>
                    <?php foreach ($warranty_options as $w): ?>
                        <option value="<?= $w['warranty_id'] ?>">Purchased: <?= $w['purchase_date'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Issue</label>
                <textarea name="issue" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Submit Request</button>
        </form>
        <h4>My Service Requests</h4>
        <table class="table table-bordered bg-white col-md-10">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Warranty</th>
                    <th>Issue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= $r['request_id'] ?></td>
                        <td><?= $r['purchase_date'] ?></td>
                        <td><?= htmlspecialchars($r['issue']) ?></td>
                        <td><?= htmlspecialchars($r['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary mt-2">Back to Home</a>
    </div>
</body>

</html>