<?php
// Migration script to add reward_points column to Product table
require_once '../config/db.php';
require_once '../config/error_handler.php';

// Check if column already exists
$check_column = $conn->query("SHOW COLUMNS FROM Product LIKE 'reward_points'");
if ($check_column && $check_column->num_rows > 0) {
    echo "Column 'reward_points' already exists in Product table.<br>";
} else {
    // Add reward_points column
    $alter_query = "ALTER TABLE Product ADD COLUMN reward_points INT DEFAULT 0 AFTER available_quantity";
    if ($conn->query($alter_query)) {
        echo "Successfully added 'reward_points' column to Product table.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
}

echo "<a href='manage_rewards.php'>Go to Reward Points Management</a>";
?>

