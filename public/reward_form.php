<?php
// Require admin-only access
$require_role = 'admin';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Ensure Product table has reward_points column
$check_column = $conn->query("SHOW COLUMNS FROM Product LIKE 'reward_points'");
if (!$check_column || $check_column->num_rows == 0) {
    $conn->query("ALTER TABLE Product ADD COLUMN reward_points INT DEFAULT 0 AFTER available_quantity");
}

$type = isset($_GET['type']) ? $_GET['type'] : 'user'; // 'user' or 'product'
$isEdit = isset($_GET['edit']);
$isAdjust = isset($_GET['adjust']);
$message = '';
$points_id = $user_id = $product_id = $points = $current_points = '';
$action_type = 'set'; // 'set', 'add', 'reduce'

// Load existing data for edit or adjust
if ($isEdit || $isAdjust) {
    $id = intval($_GET['edit'] ?? $_GET['adjust']);
    
    if ($type === 'user') {
        $stmt = $conn->prepare('SELECT * FROM RewardPoints WHERE points_id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result();
        if ($r && $row = $r->fetch_assoc()) {
            $points_id = $row['points_id'];
            $user_id = $row['user_id'];
            $points = $row['points'];
            $current_points = $row['points'];
        }
    } else { // product
        $stmt = $conn->prepare('SELECT product_id, name, COALESCE(reward_points, 0) as reward_points FROM Product WHERE product_id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result();
        if ($r && $row = $r->fetch_assoc()) {
            $product_id = $row['product_id'];
            $product_name = $row['name'];
            $points = $row['reward_points'];
            $current_points = $row['reward_points'];
        }
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? 'user';
    $action_type = $_POST['action_type'] ?? 'set';
    $points_id = intval($_POST['points_id'] ?? 0);
    $user_id = intval($_POST['user_id'] ?? 0);
    $product_id = intval($_POST['product_id'] ?? 0);
    $points_value = intval($_POST['points'] ?? 0);
    
    if ($type === 'user') {
        if ($user_id && isset($points_value)) {
            // Get current points
            $current = 0;
            if ($points_id) {
                $stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE points_id=?');
                $stmt->bind_param('i', $points_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $current = intval($row['points']);
                }
            } else {
                $stmt = $conn->prepare('SELECT points FROM RewardPoints WHERE user_id=?');
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $current = intval($row['points']);
                    $stmt2 = $conn->prepare('SELECT points_id FROM RewardPoints WHERE user_id=?');
                    $stmt2->bind_param('i', $user_id);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    if ($row2 = $result2->fetch_assoc()) {
                        $points_id = intval($row2['points_id']);
                    }
                }
            }
            
            // Calculate new points based on action
            if ($action_type === 'add') {
                $new_points = $current + $points_value;
            } elseif ($action_type === 'reduce') {
                $new_points = max(0, $current - $points_value);
            } else { // set
                $new_points = $points_value;
            }
            
            if ($points_id) {
                $stmt = $conn->prepare('UPDATE RewardPoints SET points=? WHERE points_id=?');
                $stmt->bind_param('ii', $new_points, $points_id);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare('INSERT INTO RewardPoints (user_id, points) VALUES (?, ?) ON DUPLICATE KEY UPDATE points=VALUES(points)');
                $stmt->bind_param('ii', $user_id, $new_points);
                $stmt->execute();
            }
            
            // Link to admin (Handles table)
            $admin_id = $_SESSION['user_id'];
            $check_handle = $conn->prepare('SELECT points_id FROM Handles WHERE points_id=? AND admin_id=?');
            if ($points_id) {
                $check_handle->bind_param('ii', $points_id, $admin_id);
            } else {
                $new_points_id = $conn->insert_id;
                $check_handle->bind_param('ii', $new_points_id, $admin_id);
            }
            $check_handle->execute();
            $handle_result = $check_handle->get_result();
            if ($handle_result->num_rows == 0) {
                $points_id_for_handle = $points_id ? $points_id : $conn->insert_id;
                $insert_handle = $conn->prepare('INSERT INTO Handles (points_id, admin_id) VALUES (?, ?)');
                $insert_handle->bind_param('ii', $points_id_for_handle, $admin_id);
                $insert_handle->execute();
            }
            
            header('Location: manage_rewards.php?tab=users');
            exit;
        } else {
            $message = 'All fields required!';
        }
    } else { // product
        if ($product_id && isset($points_value)) {
            // Get current points
            $stmt = $conn->prepare('SELECT COALESCE(reward_points, 0) as reward_points FROM Product WHERE product_id=?');
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current = 0;
            if ($row = $result->fetch_assoc()) {
                $current = intval($row['reward_points']);
            }
            
            // Calculate new points based on action
            if ($action_type === 'add') {
                $new_points = $current + $points_value;
            } elseif ($action_type === 'reduce') {
                $new_points = max(0, $current - $points_value);
            } else { // set
                $new_points = $points_value;
            }
            
            $stmt = $conn->prepare('UPDATE Product SET reward_points=? WHERE product_id=?');
            $stmt->bind_param('ii', $new_points, $product_id);
            $stmt->execute();
            
            header('Location: manage_rewards.php?tab=products');
            exit;
        } else {
            $message = 'All fields required!';
        }
    }
}

// Get user list
$users = [];
$res = $conn->query('SELECT user_id, name, email FROM User ORDER BY name ASC');
if ($res) $users = $res->fetch_all(MYSQLI_ASSOC);

// Get product list
$products = [];
$res = $conn->query('SELECT product_id, name, price, COALESCE(reward_points, 0) as reward_points FROM Product ORDER BY name ASC');
if ($res) $products = $res->fetch_all(MYSQLI_ASSOC);

$page_title = ($isEdit ? 'Edit' : ($isAdjust ? 'Adjust' : 'Add')) . ' ' . ucfirst($type) . ' Reward Points';
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $page_title ?> - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_rewards.php?tab=<?= $type === 'user' ? 'users' : 'products' ?>" class="btn btn-secondary mb-2">Back to Rewards</a>
        <div class="card">
            <div class="card-header">
                <h4><?= $page_title ?></h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-danger"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                
                <?php if ($isAdjust && $current_points !== ''): ?>
                    <div class="alert alert-info">
                        <strong>Current Points:</strong> <?= htmlspecialchars($current_points) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>" />
                    <input type="hidden" name="points_id" value="<?= htmlspecialchars($points_id) ?>" />
                    
                    <?php if ($type === 'user'): ?>
                        <div class="form-group">
                            <label>User</label>
                            <select name="user_id" class="form-control" required <?= ($isEdit || $isAdjust) ? 'disabled' : ''; ?>>
                                <option value="">Select User</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['user_id'] ?>" <?= ($u['user_id'] == $user_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isEdit || $isAdjust): ?>
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>" />
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Product</label>
                            <select name="product_id" class="form-control" required <?= ($isEdit || $isAdjust) ? 'disabled' : ''; ?>>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['product_id'] ?>" <?= ($p['product_id'] == $product_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?> - <?= number_format($p['price'], 2) ?> BDT (Current: <?= $p['reward_points'] ?> pts)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isEdit || $isAdjust): ?>
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>" />
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Action Type</label>
                        <select name="action_type" class="form-control" id="action_type" required>
                            <option value="set" <?= $action_type === 'set' ? 'selected' : '' ?>>Set Points (Replace current value)</option>
                            <option value="add" <?= $action_type === 'add' ? 'selected' : '' ?>>Add Points (Increase)</option>
                            <option value="reduce" <?= $action_type === 'reduce' ? 'selected' : '' ?>>Reduce Points (Decrease)</option>
                        </select>
                        <small class="form-text text-muted">
                            <span id="action_hint">Set the exact number of points</span>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Points Value</label>
                        <input type="number" name="points" value="<?= htmlspecialchars($points) ?>" class="form-control" min="0" required />
                        <small class="form-text text-muted" id="points_hint">
                            Enter the number of points to set
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <?php
                        if ($isAdjust) {
                            echo 'Apply Adjustment';
                        } elseif ($isEdit) {
                            echo 'Update Reward Points';
                        } else {
                            echo 'Add Reward Points';
                        }
                        ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('action_type').addEventListener('change', function() {
            const actionType = this.value;
            const actionHint = document.getElementById('action_hint');
            const pointsHint = document.getElementById('points_hint');
            
            if (actionType === 'set') {
                actionHint.textContent = 'Set the exact number of points';
                pointsHint.textContent = 'Enter the exact number of points to set';
            } else if (actionType === 'add') {
                actionHint.textContent = 'Add points to the current balance';
                pointsHint.textContent = 'Enter the number of points to add';
            } else if (actionType === 'reduce') {
                actionHint.textContent = 'Reduce points from the current balance';
                pointsHint.textContent = 'Enter the number of points to reduce';
            }
        });
    </script>
</body>

</html>
