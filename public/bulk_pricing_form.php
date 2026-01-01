<?php
// Require admin-only access
$require_role = 'admin';
require_once 'includes/admin_auth.php';
require_once '../config/db.php';
require_once '../config/error_handler.php';

$isEdit = isset($_GET['edit']);
$message = '';
$data = ['product_no' => '', 'product_id' => '', 'min_quantity' => '', 'discount_percentage' => ''];
if ($isEdit) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM BulkPricing WHERE product_no=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) $data = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_no = intval($_POST['product_no'] ?? 0);
    $product_id = intval($_POST['product_id'] ?? 0);
    $minq = intval($_POST['min_quantity'] ?? 0);
    $discount = floatval($_POST['discount_percentage'] ?? 0);
    if ($product_id && $minq && $discount) {
        if ($product_no) {
            $stmt = $conn->prepare('UPDATE BulkPricing SET product_id=?, min_quantity=?, discount_percentage=? WHERE product_no=?');
            $stmt->bind_param('iidi', $product_id, $minq, $discount, $product_no);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('INSERT INTO BulkPricing (product_id,min_quantity,discount_percentage) VALUES (?,?,?)');
            $stmt->bind_param('iid', $product_id, $minq, $discount);
            $stmt->execute();
        }
        header('Location: bulk_pricing.php');
        exit;
    } else $message = 'Fill all fields!';
}
// Products for dropdown
$res = $conn->query('SELECT product_id, name FROM Product ORDER BY name ASC');
$products = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Bulk Pricing Rule - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-4">
        <a href="bulk_pricing.php" class="btn btn-secondary mb-2">Back to Bulk Pricing</a>
        <div class="card">
            <div class="card-header">
                <h4><?= $isEdit ? 'Edit' : 'Add' ?> Bulk Pricing</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="product_no" value="<?= htmlspecialchars($data['product_no']) ?>" />
                    <div class="form-group">
                        <label>Product</label>
                        <select name="product_id" class="form-control" required <?= $isEdit ? 'disabled' : '' ?>>
                            <option value="">Select Product</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['product_id'] ?>" <?= ($p['product_id'] == $data['product_id']) ? 'selected' : '' ?>><?= $p['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Min Quantity</label>
                        <input type="number" name="min_quantity" value="<?= htmlspecialchars($data['min_quantity']) ?>" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Discount Percentage</label>
                        <input type="number" name="discount_percentage" value="<?= htmlspecialchars($data['discount_percentage']) ?>" class="form-control" min="0" max="100" step="0.01" required />
                    </div>
                    <button type="submit" class="btn btn-success"><?= $isEdit ? 'Update' : 'Add' ?> Rule</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>