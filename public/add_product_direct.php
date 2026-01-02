<?php
// Require admin or staff access
require_once 'includes/admin_auth.php';
require_once '../config/error_handler.php';
require_once '../config/db.php';

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
        echo "✓ Product '{$product_name}' added successfully!<br>";
        echo "Product ID: {$product_id}<br>";
        echo "Price: ৳ {$price}<br>";
        echo "Category: {$category}<br><br>";
        // Create warranty for this product and link it
        if (!empty($warranty_duration)) {
            $wst = $conn->prepare('INSERT INTO Warranty (warranty_duration, purchase_date) VALUES (?, NULL)');
            if ($wst) {
                $wst->bind_param('i', $warranty_duration);
                $wst->execute();
                $wid = $conn->insert_id;
                $wst->close();
                $ust = $conn->prepare('UPDATE Product SET warranty_id = ? WHERE product_id = ?');
                if ($ust) {
                    $ust->bind_param('ii', $wid, $product_id);
                    $ust->execute();
                    $ust->close();
                }
            }
        }
        echo "<a href='index.php'>View on Homepage</a>";
    } else {
        echo "Error adding product: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
