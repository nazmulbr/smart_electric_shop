<?php
// Error Handler Configuration
// Include this file at the top of your PHP files to enable detailed error reporting

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = "<div style='background:#ffebee;border-left:4px solid #f44336;padding:15px;margin:10px;font-family:Arial;'>";
    $error_message .= "<strong style='color:#d32f2f;'>Error [$errno]:</strong> ";
    $error_message .= "<span style='color:#333;'>$errstr</span><br>";
    $error_message .= "<small style='color:#666;'>File: $errfile (Line: $errline)</small>";
    $error_message .= "</div>";
    echo $error_message;
    return true;
}

// Set exception handler
function customExceptionHandler($exception) {
    $error_message = "<div style='background:#ffebee;border-left:4px solid #f44336;padding:15px;margin:10px;font-family:Arial;'>";
    $error_message .= "<strong style='color:#d32f2f;'>Uncaught Exception:</strong> ";
    $error_message .= "<span style='color:#333;'>" . $exception->getMessage() . "</span><br>";
    $error_message .= "<small style='color:#666;'>File: " . $exception->getFile() . " (Line: " . $exception->getLine() . ")</small>";
    $error_message .= "</div>";
    echo $error_message;
}

set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");

// Database error display function
function showDbError($conn, $operation = "Database operation") {
    if ($conn->error) {
        $error = "<div class='alert alert-danger' style='margin:10px;'>";
        $error .= "<strong>❌ $operation Failed!</strong><br>";
        $error .= "<strong>Error Code:</strong> " . $conn->errno . "<br>";
        $error .= "<strong>Error Message:</strong> " . htmlspecialchars($conn->error) . "<br>";
        $error .= "<strong>SQL State:</strong> " . $conn->sqlstate . "<br>";
        $error .= "</div>";
        return $error;
    }
    return "";
}

// General error display function
function showError($message, $details = "") {
    $error = "<div class='alert alert-danger' style='margin:10px;'>";
    $error .= "<strong>❌ Error:</strong> " . htmlspecialchars($message);
    if ($details) {
        $error .= "<br><small style='color:#666;'>" . htmlspecialchars($details) . "</small>";
    }
    $error .= "</div>";
    return $error;
}

// Success message function
function showSuccess($message) {
    return "<div class='alert alert-success' style='margin:10px;'>✅ " . htmlspecialchars($message) . "</div>";
}

// Warning message function
function showWarning($message) {
    return "<div class='alert alert-warning' style='margin:10px;'>⚠️ " . htmlspecialchars($message) . "</div>";
}
?>

