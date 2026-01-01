<?php
require_once '../config/db.php';
require_once '../config/error_handler.php';

echo "<h2>Database Check</h2>";

// Check if Product table exists
$result = $conn->query("SHOW TABLES LIKE 'Product'");
if ($result && $result->num_rows > 0) {
    echo "✓ Product table exists<br><br>";

    // Check all products
    $result = $conn->query("SELECT * FROM Product");
    if ($result) {
        $num_products = $result->num_rows;
        echo "Total products in database: <strong>$num_products</strong><br><br>";

        if ($num_products > 0) {
            echo "<table border='1' cellpadding='10'>";
            echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>Warranty (months)</th><th>Quantity</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['product_id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . number_format($row['price'], 2) . "</td>";
                echo "<td>" . $row['warranty_duration'] . "</td>";
                echo "<td>" . $row['available_quantity'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "Error querying products: " . $conn->error;
    }
} else {
    echo "✗ Product table does NOT exist<br>";
    echo "Please run: <a href='init_database.php'>init_database.php</a>";
}

$conn->close();
