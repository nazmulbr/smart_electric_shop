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
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    
    if ($name && $email) {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email format!';
        } else {
            if ($id) {
                // Update existing user
                $stmt = $conn->prepare('UPDATE User SET name=?, email=?, phone_number=? WHERE user_id=?');
                if ($stmt) {
                    $stmt->bind_param('sssi', $name, $email, $phone, $id);
                    if($stmt->execute()) {
                        header('Location: manage_users.php?msg=updated');
                        exit;
                    } else {
                        $message = showDbError($conn, "User Update");
                        $message .= "<strong>Update Details:</strong><br>";
                        $message .= "User ID: $id<br>";
                        $message .= "Name: $name<br>";
                        $message .= "Email: $email<br>";
                    }
                    $stmt->close();
                } else {
                    $message = showDbError($conn, "Preparing UPDATE statement");
                }
            } else {
                // Check if email already exists
                $check_stmt = $conn->prepare('SELECT user_id FROM User WHERE email = ?');
                if ($check_stmt) {
                    $check_stmt->bind_param('s', $email);
                    $check_stmt->execute();
                    $check_stmt->store_result();
                    
                    if ($check_stmt->num_rows == 0) {
                        $check_stmt->close();
                        // Default password for direct create as admin
                        $defaultpw = password_hash('userpass123', PASSWORD_DEFAULT);
                        $stmt = $conn->prepare('INSERT INTO User (name, email, password, phone_number) VALUES (?, ?, ?, ?)');
                        if ($stmt) {
                            $stmt->bind_param('ssss', $name, $email, $defaultpw, $phone);
                            if($stmt->execute()) {
                                header('Location: manage_users.php?msg=added');
                                exit;
                            } else {
                                $message = showDbError($conn, "User Insert");
                                $message .= "<strong>Insert Details:</strong><br>";
                                $message .= "Name: $name<br>";
                                $message .= "Email: $email<br>";
                                $message .= "Phone: $phone<br>";
                            }
                            $stmt->close();
                        } else {
                            $message = showDbError($conn, "Preparing INSERT statement");
                        }
                    } else {
                        $message = 'Email already exists!';
                        $check_stmt->close();
                    }
                } else {
                    $message = 'Database error: ' . $conn->error;
                }
            }
        }
    } else {
        $message = 'Please fill all required fields (Name and Email)!';
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
                    <div class="alert alert-<?=strpos($message, 'failed') !== false || strpos($message, 'Error') !== false || strpos($message, 'error') !== false ? 'danger' : 'info'?>">
                        <?php 
                        // Check if it's already HTML formatted (from showDbError)
                        if (strpos($message, '<div') !== false || strpos($message, '<strong') !== false) {
                            echo $message;
                        } else {
                            echo htmlspecialchars($message);
                        }
                        ?>
                    </div>
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

