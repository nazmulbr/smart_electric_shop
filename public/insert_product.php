<?php
// Direct database insertion script
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Product details for WFC-3F5-GDEL-XX
$product_name = "WFC-3F5-GDEL-XX (INVERTER)";
$description = "Category: Refrigerator

Specifications:
- Type: Direct Cool
- Door: Glass Door
- Gross Volume: 370 Ltr
- Net Volume: 367 Ltr
- Special Technology: Nano Healthcare
- Refrigerant: R600a

Warranty:
- Compressor Guarantee: 12 Years

Features:
- 12 Years Compressor Guarantee
- Intelligent Inverter Technology for Maximum Power Saving
- Nano Healthcare Technology
- Prevents Bacteria Growth
- Keeps Food Fresh for Longer";

$price = 54490.00;
$warranty_duration = 144; // 12 years in months
$available_quantity = 10;
$admin_id = 1; // Default admin

// Insert product
$sql = "INSERT INTO Product (name, description, price, warranty_duration, available_quantity, admin_id) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssdiii', $product_name, $description, $price, $warranty_duration, $available_quantity, $admin_id);

if ($stmt->execute()) {
    echo "<h3 style='color: green;'>✓ Product Added Successfully!</h3>";
    echo "<p><strong>Product Name:</strong> " . htmlspecialchars($product_name) . "</p>";
    echo "<p><strong>Price:</strong> ৳ " . number_format($price, 2) . "</p>";
    echo "<p><strong>Warranty:</strong> " . $warranty_duration . " months</p>";
    echo "<p><strong>Available Quantity:</strong> " . $available_quantity . " units</p>";
    echo "<p><a href='index.php'>View on Homepage</a> | <a href='index.php'>View All Products</a></p>";
} else {
    echo "<h3 style='color: red;'>✗ Error Adding Product</h3>";
    echo "<p>" . $conn->error . "</p>";
}

$stmt->close();
$conn->close();
