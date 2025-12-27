<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/db.php';

$isEdit = isset($_GET['edit']);
$message = '';
$p = [
    'product_id'=>'', 'name'=>'', 'description'=>'', 'price'=>'', 'warranty_duration'=>'', 'available_quantity'=>''
];

// Edit - fetch product
if ($isEdit) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM Product WHERE product_id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) $p = $row;
}
// Handle POST (add or update)
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $warranty = intval($_POST['warranty_duration'] ?? 0);
    $qty = intval($_POST['available_quantity'] ?? 0);
    $admin_id = $_SESSION['user_id'];
    
    if ($name && $price > 0 && $qty >= 0) {
        if ($id) {
            // Update existing product
            $stmt = $conn->prepare('UPDATE Product SET name=?, description=?, price=?, warranty_duration=?, available_quantity=? WHERE product_id=?');
            if ($stmt) {
                $stmt->bind_param('ssdiii', $name, $desc, $price, $warranty, $qty, $id);
                if($stmt->execute()) {
                    $message = 'Product updated successfully!';
                    header('Location: manage_products.php?msg=updated');
                    exit;
                } else {
                    $message = 'Update failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $message = 'Database error: ' . $conn->error;
            }
        } else {
            // Insert new product
            $stmt = $conn->prepare('INSERT INTO Product (name, description, price, warranty_duration, available_quantity, admin_id) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssdiii', $name, $desc, $price, $warranty, $qty, $admin_id);
                if($stmt->execute()) {
                    $message = 'Product added successfully!';
                    header('Location: manage_products.php?msg=added');
                    exit;
                } else {
                    $message = 'Insert failed: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $message = 'Database error: ' . $conn->error;
            }
        }
    } else {
        $message = 'Please fill all required fields correctly! (Name, Price > 0, Quantity >= 0)';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Product - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <a href="manage_products.php" class="btn btn-secondary mb-2">Back to Products</a>
        <div class="card">
            <div class="card-header">
                <h4><?= $isEdit ? 'Edit' : 'Add' ?> Product</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['product_id']) ?>" />
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"><?= htmlspecialchars($p['description']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($p['price']) ?>" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Warranty Duration (months)</label>
                        <input type="number" name="warranty_duration" value="<?= htmlspecialchars($p['warranty_duration']) ?>" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Available Quantity</label>
                        <input type="number" name="available_quantity" value="<?= htmlspecialchars($p['available_quantity']) ?>" class="form-control" required />
                    </div>
                    <button type="submit" class="btn btn-success"><?= $isEdit ? 'Update' : 'Add' ?> Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

