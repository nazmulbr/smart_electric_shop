<?php
// Require admin-only access
$require_role = 'admin';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';
$message = '';
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($request_id && $status) {
        $stmt = $conn->prepare('UPDATE ServiceRequest SET status=? WHERE request_id=?');
        $stmt->bind_param('si', $status, $request_id);
        $stmt->execute();
        $message = 'Request updated!';
    }
}
$requests = $conn->query('SELECT s.*, u.name as user_name, w.purchase_date FROM ServiceRequest s LEFT JOIN User u ON s.user_id = u.user_id LEFT JOIN Warranty w ON s.warranty_id = w.warranty_id ORDER BY s.status, s.request_id DESC');
$requests = $requests ? $requests->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Service Requests Management - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <h4>All Service Requests</h4>
        <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
        <a href="admin_dashboard.php" class="btn btn-secondary mb-2">Back to Dashboard</a>
        <table class="table table-bordered bg-white col-md-12">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Warranty Purchase</th>
                    <th>Issue</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= $r['request_id'] ?></td>
                        <td><?= htmlspecialchars($r['user_name']) ?></td>
                        <td><?= $r['purchase_date'] ?></td>
                        <td><?= htmlspecialchars($r['issue']) ?></td>
                        <td><?= htmlspecialchars($r['status']) ?></td>
                        <td>
                            <form method="POST" class="form-inline">
                                <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                <select name="status" class="form-control mr-1">
                                    <?php foreach (['Open', 'In Progress', 'Resolved', 'Rejected'] as $stat): ?>
                                        <option value="<?= $stat ?>" <?= $r['status'] == $stat ? 'selected' : '' ?>><?= $stat ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>