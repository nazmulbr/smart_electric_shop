<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$isEdit = isset($_GET['edit']);
$message = '';
$u = [
    'user_id'=>'', 'name'=>'', 'email'=>'', 'phone_number'=>''
];

// Editing?
if ($isEdit) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM User WHERE user_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) $u = $row;
}
// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['user_id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone_number'] ?? '';
    if ($name && $email) {
        if ($id) {
            $stmt = $conn->prepare('UPDATE User SET name=?, email=?, phone_number=? WHERE user_id=?');
            $stmt->bind_param('sssi', $name, $email, $phone, $id);
            if($stmt->execute()) $message = 'User updated!';
        } else {
            // Default password for direct create as admin
            $defaultpw = password_hash('userpass123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $name, $email, $defaultpw, $phone);
            if($stmt->execute()) $message = 'User added! Default password: userpass123';
        }
        header('Location: manage_users.php');
        exit;
    } else {
        $message = 'Fill all required fields!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $isEdit ? 'Edit' : 'Add' ?> User - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_users.php" class="btn btn-secondary mb-2">Back to Users</a>
        <div class="card">
            <div class="card-header">
                <h4><?= $isEdit ? 'Edit' : 'Add' ?> User</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['user_id']) ?>" />
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" value="<?= htmlspecialchars($u['phone_number']) ?>" class="form-control" />
                    </div>
                    <button type="submit" class="btn btn-success"><?= $isEdit ? 'Update' : 'Add' ?> User</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

