<?php
// Common Header with Error Handling
// Include this at the top of your pages: require_once 'includes/header.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include error handler
require_once '../config/error_handler.php';
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Smart Electric Shop' ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="js/error_handler.js"></script>
    <style>
        .error-details {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 10px 0;
            font-family: Arial;
        }
        .error-details strong {
            color: #d32f2f;
        }
        .error-details small {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

