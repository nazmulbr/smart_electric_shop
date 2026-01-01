<?php
// Require admin or staff access
require_once 'includes/admin_auth.php';
require_once '../config/error_handler.php';
require_once '../config/db.php';
require_once '../config/db_check.php';

// Check if Product table exists
if (!checkTableExists('Product')) {
    die(showTableError('Product', 'Product Management'));
}

$isEdit = isset($_GET['edit']);
$message = '';
$p = [
    'product_id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'warranty_duration' => '',
    'available_quantity' => ''
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                if ($stmt->execute()) {
                    // Handle uploaded images (if any)
                    $uploaded_paths = [];
                    if (!empty($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
                        $target_dir = __DIR__ . '/images/products/';
                        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
                        foreach ($_FILES['product_images']['name'] as $i => $origName) {
                            if ($_FILES['product_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                            $tmp = $_FILES['product_images']['tmp_name'][$i];
                            $info = @getimagesize($tmp);
                            if ($info === false) continue; // not an image
                            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                            if (!in_array($ext, $allowed_ext)) continue;
                            $safe = preg_replace('/[^a-z0-9-_\.]/i', '-', pathinfo($origName, PATHINFO_FILENAME));
                            $filename = 'product-' . $id . '-' . time() . '-' . $i . '.' . $ext;
                            $dest = $target_dir . $filename;
                            if (move_uploaded_file($tmp, $dest)) {
                                chmod($dest, 0644);
                                $uploaded_paths[] = 'images/products/' . $filename;
                            }
                        }
                    }

                    // If any uploaded images, merge with existing images (append)
                    if (!empty($uploaded_paths)) {
                        // fetch current images
                        $curImgs = [];
                        $gstmt = $conn->prepare('SELECT images FROM Product WHERE product_id = ? LIMIT 1');
                        if ($gstmt) {
                            $gstmt->bind_param('i', $id);
                            $gstmt->execute();
                            $gres = $gstmt->get_result();
                            if ($grow = $gres->fetch_assoc()) {
                                if (!empty($grow['images'])) {
                                    $curImgs = json_decode($grow['images'], true) ?: [];
                                }
                            }
                            $gstmt->close();
                        }
                        $newImgs = array_values(array_merge($curImgs, $uploaded_paths));
                        $ims = json_encode($newImgs);
                        $ust = $conn->prepare('UPDATE Product SET images = ? WHERE product_id = ?');
                        if ($ust) {
                            $ust->bind_param('si', $ims, $id);
                            $ust->execute();
                            $ust->close();
                        }
                    }

                    $message = 'Product updated successfully!';
                    header('Location: manage_products.php?msg=updated');
                    exit;
                } else {
                    $message = showDbError($conn, "Product Update");
                    $message .= "<strong>Update Details:</strong><br>";
                    $message .= "Product ID: $id<br>";
                    $message .= "Name: $name<br>";
                    $message .= "Price: $price<br>";
                    $message .= "Quantity: $qty<br>";
                }
                $stmt->close();
            } else {
                $message = showDbError($conn, "Preparing UPDATE statement");
            }
        } else {
            // Insert new product
            $stmt = $conn->prepare('INSERT INTO Product (name, description, price, warranty_duration, available_quantity, admin_id) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                $stmt->bind_param('ssdiii', $name, $desc, $price, $warranty, $qty, $admin_id);
                if ($stmt->execute()) {
                    $new_id = $stmt->insert_id;

                    // Handle uploaded images for new product
                    $uploaded_paths = [];
                    if (!empty($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
                        $target_dir = __DIR__ . '/images/products/';
                        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
                        foreach ($_FILES['product_images']['name'] as $i => $origName) {
                            if ($_FILES['product_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                            $tmp = $_FILES['product_images']['tmp_name'][$i];
                            $info = @getimagesize($tmp);
                            if ($info === false) continue; // not an image
                            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                            if (!in_array($ext, $allowed_ext)) continue;
                            $safe = preg_replace('/[^a-z0-9-_\.]/i', '-', pathinfo($origName, PATHINFO_FILENAME));
                            $filename = 'product-' . $new_id . '-' . time() . '-' . $i . '.' . $ext;
                            $dest = $target_dir . $filename;
                            if (move_uploaded_file($tmp, $dest)) {
                                chmod($dest, 0644);
                                $uploaded_paths[] = 'images/products/' . $filename;
                            }
                        }
                    }

                    if (!empty($uploaded_paths)) {
                        $ims = json_encode(array_values($uploaded_paths));
                        $ust = $conn->prepare('UPDATE Product SET images = ? WHERE product_id = ?');
                        if ($ust) {
                            $ust->bind_param('si', $ims, $new_id);
                            $ust->execute();
                            $ust->close();
                        }
                    }

                    $message = 'Product added successfully!';
                    header('Location: manage_products.php?msg=added');
                    exit;
                } else {
                    $message = showDbError($conn, "Product Insert");
                    $message .= "<strong>Insert Details:</strong><br>";
                    $message .= "Name: $name<br>";
                    $message .= "Description: " . substr($desc, 0, 50) . "...<br>";
                    $message .= "Price: $price<br>";
                    $message .= "Warranty: $warranty months<br>";
                    $message .= "Quantity: $qty<br>";
                    $message .= "Admin ID: $admin_id<br>";
                }
                $stmt->close();
            } else {
                $message = showDbError($conn, "Preparing INSERT statement");
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
                    <div class="alert alert-<?= strpos($message, 'successfully') !== false ? 'success' : 'danger' ?>">
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
                <form method="POST" enctype="multipart/form-data">
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
                    <div class="form-group">
                        <label>Product Images (multiple allowed)</label>
                        <input type="file" name="product_images[]" multiple accept="image/*" class="form-control-file" />
                        <small class="form-text text-muted">First image will be used as thumbnail on listings.</small>
                    </div>
                    <button type="submit" class="btn btn-success"><?= $isEdit ? 'Update' : 'Add' ?> Product</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>