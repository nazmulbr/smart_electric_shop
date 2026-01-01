<?php
require_once '../config/db.php';
require_once '../config/error_handler.php';
require_once '../config/db_check.php';

$message = '';
$success = false;

echo "<h2>Adding Refrigerator Product</h2>";

// Check if Product table exists
if (!checkTableExists('Product')) {
    echo "<div style='background:#ffebee;padding:15px;margin:10px 0;border-radius:5px;'>";
    echo "<h3 style='color:#d32f2f;'>✗ Error: Product table does not exist</h3>";
    echo "<p>Please initialize the database first: <a href='init_database.php'>Initialize Database</a></p>";
    echo "</div>";
    exit;
}

echo "<div style='background:#e8f5e9;padding:15px;margin:10px 0;border-radius:5px;'>";
echo "<h3 style='color:#388e3c;'>✓ Product table exists</h3>";
echo "</div>";

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

// Check if product already exists
$checkStmt = $conn->prepare("SELECT product_id FROM Product WHERE name = ?");
if ($checkStmt) {
    $checkStmt->bind_param('s', $product_name);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div style='background:#fff3cd;padding:15px;margin:10px 0;border-radius:5px;'>";
        echo "<h3 style='color:#856404;'>⚠ Product Already Exists</h3>";
        echo "<p>The product '{$product_name}' is already in the database.</p>";
        echo "</div>";
        $checkStmt->close();
    } else {
        // Insert new product
        $insertStmt = $conn->prepare("INSERT INTO Product (name, description, price, warranty_duration, available_quantity, admin_id) VALUES (?, ?, ?, ?, ?, ?)");

        if ($insertStmt) {
            $insertStmt->bind_param('ssdiii', $product_name, $description, $price, $warranty_duration, $available_quantity, $admin_id);

            if ($insertStmt->execute()) {
                $product_id = $insertStmt->insert_id;
                echo "<div style='background:#d4edda;padding:15px;margin:10px 0;border-radius:5px;'>";
                echo "<h3 style='color:#155724;'>✓ Product Added Successfully!</h3>";
                echo "<table style='margin-top:15px;'>";
                echo "<tr><td><strong>Product ID:</strong></td><td>" . $product_id . "</td></tr>";
                echo "<tr><td><strong>Product Name:</strong></td><td>" . htmlspecialchars($product_name) . "</td></tr>";
                echo "<tr><td><strong>Price:</strong></td><td>৳ " . number_format($price, 2) . "</td></tr>";
                echo "<tr><td><strong>Warranty:</strong></td><td>" . $warranty_duration . " months (12 years)</td></tr>";
                echo "<tr><td><strong>Available Quantity:</strong></td><td>" . $available_quantity . " units</td></tr>";
                echo "</table>";
                echo "</div>";
                $success = true;
            } else {
                echo "<div style='background:#ffebee;padding:15px;margin:10px 0;border-radius:5px;'>";
                echo "<h3 style='color:#d32f2f;'>✗ Error Inserting Product</h3>";
                echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
                echo "</div>";
            }
            $insertStmt->close();
        } else {
            echo "<div style='background:#ffebee;padding:15px;margin:10px 0;border-radius:5px;'>";
            echo "<h3 style='color:#d32f2f;'>✗ Error Preparing Insert Statement</h3>";
            echo "<p><strong>Error:</strong> " . $conn->error . "</p>";
            echo "</div>";
        }
        $checkStmt->close();
    }
}

// Show all products in database
echo "<h3 style='margin-top:30px;'>All Products in Database:</h3>";
$allProducts = $conn->query("SELECT * FROM Product");
if ($allProducts && $allProducts->num_rows > 0) {
    echo "<table style='border-collapse:collapse;width:100%;margin-top:10px;'>";
    echo "<tr style='background:#f5f5f5;'><th style='border:1px solid #ddd;padding:8px;'>ID</th><th style='border:1px solid #ddd;padding:8px;'>Name</th><th style='border:1px solid #ddd;padding:8px;'>Price</th><th style='border:1px solid #ddd;padding:8px;'>Warranty</th><th style='border:1px solid #ddd;padding:8px;'>Quantity</th></tr>";
    while ($row = $allProducts->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>" . $row['product_id'] . "</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>৳ " . number_format($row['price'], 2) . "</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>" . $row['warranty_duration'] . " months</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>" . $row['available_quantity'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='background:#ffebee;padding:15px;margin:10px 0;border-radius:5px;'>";
    echo "<p>No products found in database</p>";
    echo "</div>";
}

echo "<hr style='margin-top:30px;'>";
echo "<p><a href='index.php' style='margin-right:10px;padding:8px 15px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>View Homepage</a>";
echo "<a href='index.php' style='padding:8px 15px;background:#28a745;color:white;text-decoration:none;border-radius:4px;'>View All Products</a></p>";

$conn->close();
