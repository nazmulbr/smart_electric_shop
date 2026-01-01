<?php
session_start();
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Check if user is admin or staff
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$success = false;

// Product details for WFC-3F5-GDEL-XX
$product_name = "WFC-3F5-GDEL-XX (INVERTER)";
$category = "Refrigerator";
$product_type = "Direct Cool";
$door_type = "Glass Door";
$gross_volume = "370 Ltr";
$net_volume = "367 Ltr";
$special_technology = "Nano Healthcare";
$refrigerant = "R600a";
$compressor_guarantee = "12 Years";
$price = 54490.00;
$warranty_duration = 144; // 12 years in months
$available_quantity = 10; // Default quantity
$admin_id = $_SESSION['user_id'];

// Build detailed description
$description = "Category: $category\n\n";
$description .= "Specifications:\n";
$description .= "- Type: $product_type\n";
$description .= "- Door: $door_type\n";
$description .= "- Gross Volume: $gross_volume\n";
$description .= "- Net Volume: $net_volume\n";
$description .= "- Special Technology: $special_technology\n";
$description .= "- Refrigerant: $refrigerant\n\n";
$description .= "Warranty:\n";
$description .= "- Compressor Guarantee: $compressor_guarantee\n\n";
$description .= "Features:\n";
$description .= "- 12 Years Compressor Guarantee\n";
$description .= "- Intelligent Inverter Technology for Maximum Power Saving\n";
$description .= "- Nano Healthcare Technology\n";
$description .= "- Prevents Bacteria Growth\n";
$description .= "- Keeps Food Fresh for Longer";

// Insert product into database
$stmt = $conn->prepare('INSERT INTO Product (name, description, price, warranty_duration, available_quantity, admin_id) VALUES (?, ?, ?, ?, ?, ?)');

if ($stmt) {
    $stmt->bind_param('ssdiii', $product_name, $description, $price, $warranty_duration, $available_quantity, $admin_id);

    if ($stmt->execute()) {
        $product_id = $stmt->insert_id;
        $success = true;
        $message = "✓ Product '{$product_name}' added successfully!<br>";
        $message .= "Product ID: {$product_id}<br>";
        $message .= "Price: ৳ {$price}<br>";
        $message .= "Category: {$category}";
    } else {
        $message = showDbError($conn, "Product Insert");
        $message .= "<strong>Insert Details:</strong><br>";
        $message .= "Name: {$product_name}<br>";
        $message .= "Price: {$price}<br>";
    }
    $stmt->close();
} else {
    $message = showDbError($conn, "Preparing INSERT statement");
}

$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Refrigerator Product</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f5f5;
        }

        .card {
            margin-top: 20px;
        }

        .success-message {
            color: #28a745;
        }

        .error-message {
            color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Add Refrigerator Product</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $success ? 'success' : 'danger' ?>">
                                <?php
                                if (strpos($message, '<div') !== false || strpos($message, '<strong') !== false) {
                                    echo $message;
                                } else {
                                    echo nl2br(htmlspecialchars($message));
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <hr>
                            <h5>Product Details Added:</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Model Name:</strong></td>
                                    <td><?= htmlspecialchars($product_name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td><?= htmlspecialchars($category) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td><?= htmlspecialchars($product_type) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Door:</strong></td>
                                    <td><?= htmlspecialchars($door_type) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Gross Volume:</strong></td>
                                    <td><?= htmlspecialchars($gross_volume) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Net Volume:</strong></td>
                                    <td><?= htmlspecialchars($net_volume) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Special Technology:</strong></td>
                                    <td><?= htmlspecialchars($special_technology) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Refrigerant:</strong></td>
                                    <td><?= htmlspecialchars($refrigerant) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Compressor Guarantee:</strong></td>
                                    <td><?= htmlspecialchars($compressor_guarantee) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Price:</strong></td>
                                    <td>৳ <?= number_format($price, 2) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Warranty Duration:</strong></td>
                                    <td><?= $warranty_duration ?> months (12 years)</td>
                                </tr>
                                <tr>
                                    <td><strong>Available Quantity:</strong></td>
                                    <td><?= $available_quantity ?> units</td>
                                </tr>
                            </table>
                            <hr>
                            <p><strong>Next Steps:</strong></p>
                            <ul>
                                <li><a href="manage_products.php">View all products</a></li>
                                <li><a href="add_refrigerator_product.php">Add another product</a></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>