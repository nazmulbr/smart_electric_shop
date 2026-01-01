<?php
session_start();
require_once '../config/db.php';
require_once '../config/db_check.php';

$message = '';
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
if (!$token) {
    $message = 'Invalid or missing token.';
} else {
    // find token
    $stmt = $conn->prepare('SELECT pr.id, pr.user_id, pr.expires_at, pr.used, u.email FROM PasswordReset pr JOIN User u ON pr.user_id = u.user_id WHERE pr.token = ? LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $record = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$record) {
        $message = 'Invalid token.';
    } elseif ($record['used']) {
        $message = 'This reset link has already been used.';
    } elseif (strtotime($record['expires_at']) < time()) {
        $message = 'This reset link has expired.';
    } else {
        // handle POST new password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pw = $_POST['password'] ?? '';
            $pw2 = $_POST['password_confirm'] ?? '';
            if (strlen($pw) < 6) {
                $message = 'Password must be at least 6 characters.';
            } elseif ($pw !== $pw2) {
                $message = 'Passwords do not match.';
            } else {
                $hash = password_hash($pw, PASSWORD_DEFAULT);
                $up = $conn->prepare('UPDATE User SET password = ? WHERE user_id = ?');
                $up->bind_param('si', $hash, $record['user_id']);
                if ($up->execute()) {
                    $up->close();
                    // mark token used
                    $m = $conn->prepare('UPDATE PasswordReset SET used = 1 WHERE id = ?');
                    $m->bind_param('i', $record['id']);
                    $m->execute();
                    $m->close();
                    $message = 'Password updated successfully. You can now <a href="login.php">login</a>.';
                } else {
                    $message = 'Database error updating password.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Reset Password - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Reset Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
                        <?php if (isset($record) && !$record['used'] && strtotime($record['expires_at']) >= time()): ?>
                            <form method="POST">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="password" class="form-control" required />
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input type="password" name="password_confirm" class="form-control" required />
                                </div>
                                <button class="btn btn-primary">Set New Password</button>
                            </form>
                        <?php else: ?>
                            <a href="forgot_password.php" class="btn btn-secondary">Request a new reset link</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>