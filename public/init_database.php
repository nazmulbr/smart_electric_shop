<?php
// Database Initialization Helper
// This page helps users set up the database if tables are missing
// Access: http://localhost/smart_electric_shop/public/init_database.php

require_once '../config/db.php';
require_once '../config/error_handler.php';
require_once '../config/db_check.php';

$message = '';
$success = false;

// Check current status
$tableCheck = checkRequiredTables();

// Handle manual table creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables'])) {
    $schemaFile = '../database_schema.sql';

    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);

        // Remove comments and split by semicolon
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        $created = 0;
        $errors = [];

        foreach ($statements as $statement) {
            if (!empty($statement) && stripos($statement, 'CREATE') !== false) {
                if ($conn->query($statement)) {
                    $created++;
                } else {
                    // Ignore "table already exists" errors
                    if ($conn->errno != 1050) {
                        $errors[] = $conn->error;
                    }
                }
            }
        }

        if ($created > 0 || empty($errors)) {
            $message = "✅ Database tables created successfully! ($created tables)";
            $success = true;

            // Now import initial data (default admin account)
            $initialDataFile = '../database_initial_data.sql';
            if (file_exists($initialDataFile)) {
                $initialSql = file_get_contents($initialDataFile);
                $initialSql = preg_replace('/--.*$/m', '', $initialSql);
                $initialStatements = array_filter(array_map('trim', explode(';', $initialSql)));

                $imported = 0;
                $importErrors = [];

                foreach ($initialStatements as $statement) {
                    if (!empty($statement)) {
                        if ($conn->query($statement)) {
                            $imported++;
                        } else {
                            if ($conn->errno != 1062) { // Ignore duplicate key errors
                                $importErrors[] = $conn->error;
                            }
                        }
                    }
                }

                if ($imported > 0) {
                    $message .= "<br>✅ Default admin account created! (Email: admin@smartelectric.com, Password: admin123)";
                }
            }

            // Refresh check
            $tableCheck = checkRequiredTables();
        } else {
            $message = "⚠️ Some tables were created, but errors occurred: " . implode(', ', $errors);
        }
    } else {
        $message = "❌ Schema file not found: $schemaFile";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Database Initialization - Smart Electric Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .table-status {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }

        .table-exists {
            background: #d4edda;
            color: #155724;
        }

        .table-missing {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>🔧 Database Initialization</h3>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $success ? 'success' : 'warning' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <h4>Database Status</h4>
                <p><strong>Database:</strong> smart_electric_shop</p>
                <p><strong>Connection:</strong>
                    <?php if ($conn->connect_error): ?>
                        <span class="text-danger">❌ Failed</span>
                    <?php else: ?>
                        <span class="text-success">✅ Connected</span>
                    <?php endif; ?>
                </p>

                <hr>

                <h4>Required Tables Status</h4>
                <?php if ($tableCheck['all_exist']): ?>
                    <div class="alert alert-success">
                        ✅ All required tables exist!
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        ⚠️ Some tables are missing. Please create them.
                    </div>
                <?php endif; ?>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $allTables = [
                            'User' => 'User accounts and authentication',
                            'Admin' => 'Admin accounts',
                            'Staff' => 'Staff accounts',
                            'Product' => 'Product catalog',
                            'Order' => 'Customer orders',
                            'OrderItem' => 'Order line items',
                            'Warranty' => 'Product warranties',
                            'RewardPoints' => 'Customer reward points',
                            'ServiceRequest' => 'Customer service requests',
                            'BulkPricing' => 'Bulk pricing rules',
                            'EnergyUsage' => 'Energy usage data'
                        ];

                        foreach ($allTables as $table => $desc):
                            $exists = checkTableExists($table);
                        ?>
                            <tr>
                                <td><strong><?= $table ?></strong></td>
                                <td>
                                    <?php if ($exists): ?>
                                        <span class="badge badge-success">✅ Exists</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">❌ Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $desc ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (!$tableCheck['all_exist']): ?>
                    <hr>
                    <h4>Create Missing Tables</h4>
                    <div class="alert alert-info">
                        <strong>Option 1: Automatic Creation</strong><br>
                        Click the button below to automatically create all missing tables from the schema file.
                    </div>

                    <form method="POST" onsubmit="return confirm('This will create all missing tables. Continue?');">
                        <button type="submit" name="create_tables" class="btn btn-primary btn-lg">
                            🚀 Create Missing Tables
                        </button>
                    </form>

                    <hr>

                    <div class="alert alert-info">
                        <strong>Option 2: Manual Import</strong><br>
                        <ol>
                            <li>Open <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a></li>
                            <li>Select database: <strong>smart_electric_shop</strong></li>
                            <li>Go to <strong>Import</strong> tab</li>
                            <li>Choose file: <code>database_schema.sql</code></li>
                            <li>Click <strong>Go</strong></li>
                        </ol>
                        <p><strong>Schema file location:</strong><br>
                            <code><?= realpath('../database_schema.sql') ?: '../database_schema.sql' ?></code>
                        </p>
                    </div>
                <?php else: ?>
                    <?php
                    // Check for admin accounts
                    $admin_check = $conn->query("SELECT COUNT(*) as count FROM Admin");
                    $admin_count = $admin_check ? $admin_check->fetch_assoc()['count'] : 0;
                    ?>
                    <div class="alert alert-success">
                        <h5>✅ Database is Ready!</h5>
                        <p>All required tables exist. You can now use the application.</p>

                        <?php if ($admin_count == 0): ?>
                            <div class="alert alert-warning mt-3">
                                <strong>⚠️ No Admin Account Found</strong><br>
                                You need to create an admin account to access admin features.
                                <br><br>
                                <strong>Option 1:</strong> Use default admin (if schema included initial data):<br>
                                Email: <code>admin@smartelectric.com</code><br>
                                Password: <code>admin123</code>
                                <br><br>
                                <strong>Option 2:</strong> Create a new admin account
                                <br><br>
                                <a href="create_admin.php" class="btn btn-warning">🔐 Create Admin Account</a>
                            </div>
                        <?php else: ?>
                            <p><strong>Admin Accounts:</strong> <?= $admin_count ?> found</p>
                            <a href="create_admin.php" class="btn btn-info">Create Another Admin</a>
                        <?php endif; ?>

                        <hr>
                        <a href="index.php" class="btn btn-success">Go to Homepage</a>
                        <a href="test_db.php" class="btn btn-info">Test Database</a>
                        <a href="login.php" class="btn btn-primary">Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>