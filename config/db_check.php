<?php
// Database Table Existence Checker
// This file checks if required tables exist and provides helpful error messages

require_once 'db.php';

function checkTableExists($tableName) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

function checkRequiredTables() {
    global $conn;
    
    $requiredTables = [
        'User' => 'User table is required for user registration and login',
        'Admin' => 'Admin table is required for admin functionality',
        'Product' => 'Product table is required for product management',
        'Order' => 'Order table is required for order processing',
        'OrderItem' => 'OrderItem table is required for order details',
        'Warranty' => 'Warranty table is required for warranty management',
        'RewardPoints' => 'RewardPoints table is required for reward system',
        'ServiceRequest' => 'ServiceRequest table is required for service requests',
        'BulkPricing' => 'BulkPricing table is required for bulk pricing',
        'EnergyUsage' => 'EnergyUsage table is required for energy calculator'
    ];
    
    $missingTables = [];
    $existingTables = [];
    
    foreach ($requiredTables as $table => $description) {
        if (!checkTableExists($table)) {
            $missingTables[$table] = $description;
        } else {
            $existingTables[] = $table;
        }
    }
    
    return [
        'missing' => $missingTables,
        'existing' => $existingTables,
        'all_exist' => empty($missingTables)
    ];
}

function showTableError($tableName, $operation = "operation") {
    $check = checkRequiredTables();
    
    $error = "<div class='alert alert-danger' style='margin:20px;padding:20px;border-left:4px solid #f44336;'>";
    $error .= "<h3 style='color:#d32f2f;margin-top:0;'>❌ Table '$tableName' Not Found</h3>";
    $error .= "<p><strong>Error:</strong> The table '$tableName' does not exist in the database.</p>";
    
    if (!empty($check['missing'])) {
        $error .= "<h4>Missing Tables:</h4>";
        $error .= "<ul>";
        foreach ($check['missing'] as $table => $desc) {
            $error .= "<li><strong>$table</strong> - $desc</li>";
        }
        $error .= "</ul>";
    }
    
    $error .= "<hr>";
    $error .= "<h4>🔧 How to Fix:</h4>";
    $error .= "<ol>";
    $error .= "<li><strong>Open phpMyAdmin:</strong> <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
    $error .= "<li><strong>Select database:</strong> Click on 'smart_electric_shop' database</li>";
    $error .= "<li><strong>Import schema:</strong> Go to 'Import' tab and select 'database_schema.sql' file</li>";
    $error .= "<li><strong>Or run SQL manually:</strong> Copy and paste the contents of database_schema.sql</li>";
    $error .= "<li><strong>Verify:</strong> Check that all tables are created successfully</li>";
    $error .= "</ol>";
    
    $error .= "<h4>📁 Schema File Location:</h4>";
    $error .= "<p><code>/Applications/XAMPP/xamppfiles/htdocs/smart_electric_shop/database_schema.sql</code></p>";
    
    $error .= "<h4>✅ Quick Test:</h4>";
    $error .= "<p>After importing, visit: <a href='test_db.php'>test_db.php</a> to verify all tables exist.</p>";
    
    $error .= "</div>";
    
    return $error;
}

// Auto-check on include (optional - can be disabled)
if (defined('AUTO_CHECK_TABLES') && AUTO_CHECK_TABLES) {
    $tableCheck = checkRequiredTables();
    if (!$tableCheck['all_exist']) {
        // Don't die, just set a flag that pages can check
        define('TABLES_MISSING', true);
        define('MISSING_TABLES_LIST', $tableCheck['missing']);
    }
}
?>

