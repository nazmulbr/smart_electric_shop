<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

// Handle deletion
if (isset($_GET['delete'])) {
    $wid = intval($_GET['delete']);
    $stmt = $conn->prepare('DELETE FROM Warranty WHERE warranty_id=?');
    $stmt->bind_param('i', $wid);
    $stmt->execute();
    header('Location: manage_warranty.php');
    exit;
}

// Get warranties
$result = $conn->query('SELECT w.*, u.name as user_name FROM Warranty w LEFT JOIN User u ON w.warranty_id = u.warranty_id');
$warranties = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Warranties - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h4>Warranty Management</h4>
        <div class="mb-2">
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            <a href="warranty_form.php" class="btn btn-success">Add Warranty</a>
        </div>
        <table class="table table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th><th>Duration (months)</th><th>Purchase Date</th><th>User</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($warranties as $w): ?>
                <tr>
                    <td><?=htmlspecialchars($w['warranty_id'])?></td>
                    <td><?=htmlspecialchars($w['warranty_duration'])?></td>
                    <td><?=htmlspecialchars($w['purchase_date'])?></td>
                    <td><?=htmlspecialchars($w['user_name'])?></td>
                    <td>
                        <a href="warranty_form.php?edit=<?=$w['warranty_id']?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="manage_warranty.php?delete=<?=$w['warranty_id']?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this warranty?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

