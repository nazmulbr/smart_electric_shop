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
    $product_id = intval($_POST['product_id'] ?? 0);
    $warranty_id = intval($_POST['warranty_id'] ?? 0);

    // If a product was selected, prefer its warranty_id (if available)
    if ($product_id > 0) {
        $pstmt = $conn->prepare('SELECT warranty_id FROM Product WHERE product_id = ? LIMIT 1');
        if ($pstmt) {
            $pstmt->bind_param('i', $product_id);
            $pstmt->execute();
            $pres = $pstmt->get_result();
            if ($pres && $row = $pres->fetch_assoc()) {
                if (!empty($row['warranty_id'])) {
                    $warranty_id = intval($row['warranty_id']);
                }
            }
            $pstmt->close();
        }
    }

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

// Also load product list so user can select a product and we can use its warranty
$prodStmt = $conn->prepare('SELECT product_id, name, warranty_id FROM Product');
$product_options = [];
if ($prodStmt) {
    $prodStmt->execute();
    $prodRes = $prodStmt->get_result();
    $product_options = $prodRes ? $prodRes->fetch_all(MYSQLI_ASSOC) : [];
    $prodStmt->close();
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
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Product (optional)</label>
                    <select name="product_id" id="product_id" class="form-control">
                        <option value="0">(optional) Select product</option>
                        <?php foreach ($product_options as $p): ?>
                            <option value="<?= $p['product_id'] ?>" data-warranty="<?= intval($p['warranty_id']) ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">If you select a product, its warranty (if any) will be used for this request.</small>
                </div>
                <div class="form-group col-md-6">
                    <label>Warranty</label>
                    <select name="warranty_id" id="warranty_id" class="form-control">
                        <option value="0">(optional)</option>
                        <?php foreach ($warranty_options as $w): ?>
                            <option value="<?= $w['warranty_id'] ?>">Purchased: <?= $w['purchase_date'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Issue</label>
                <textarea name="issue" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Submit Request</button>
        </form>
        <script>
            // When a product is selected, set warranty dropdown to its warranty (if any)
            document.addEventListener('DOMContentLoaded', function() {
                const prod = document.getElementById('product_id');
                const wsel = document.getElementById('warranty_id');
                if (!prod) return;
                prod.addEventListener('change', function() {
                    const opt = prod.options[prod.selectedIndex];
                    const wid = opt ? opt.getAttribute('data-warranty') : 0;
                    if (wsel && wid && parseInt(wid) > 0) {
                        // if this warranty exists in the warranty select, choose it, else set to 0
                        let found = false;
                        for (let i = 0; i < wsel.options.length; i++) {
                            if (wsel.options[i].value == wid) {
                                wsel.selectedIndex = i;
                                found = true;
                                break;
                            }
                        }
                        if (!found) wsel.value = 0;
                    }
                });
            });
        </script>
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