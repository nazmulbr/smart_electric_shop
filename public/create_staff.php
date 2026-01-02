<?php
// Admin-only page to create staff accounts
$require_role = 'admin';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone_number'] ?? '');

    if (!$name || !$email || !$password) {
        $message = 'Name, email and password are required.';
    } else {
        // check duplicate email
        $chk = $conn->prepare('SELECT staff_id FROM Staff WHERE email = ? LIMIT 1');
        if ($chk) {
            $chk->bind_param('s', $email);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows > 0) {
                $message = 'A staff with that email already exists.';
                $chk->close();
            } else {
                $chk->close();
                // Store staff password as provided (no hashing) per admin request
                $ins = $conn->prepare('INSERT INTO Staff (name, email, password, phone_number) VALUES (?, ?, ?, ?)');
                if ($ins) {
                    $ins->bind_param('ssss', $name, $email, $password, $phone);
                    if ($ins->execute()) {
                        $success = true;
                        $message = 'Staff account created successfully.';
                    } else {
                        $message = 'Database error: ' . $ins->error;
                    }
                    $ins->close();
                } else {
                    $message = 'Database error: ' . $conn->error;
                }
            }
        } else {
            $message = 'Database error: ' . $conn->error;
        }
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Staff - Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h3>Create Staff Account</h3>
        <?php if ($message): ?>
            <div class="alert alert-<?= $success ? 'success' : 'danger' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" style="max-width:600px;">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>">
            </div>
            <button class="btn btn-primary">Create Staff</button>
            <a href="manage_users.php" class="btn btn-secondary ml-2">Back</a>
        </form>
    </div>
</body>

</html>