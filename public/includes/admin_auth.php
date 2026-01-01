<?php
// Central admin/staff auth include
// Usage: optionally set $require_role = 'admin'|'staff'|'admin_staff' before including.
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($require_role)) $require_role = 'admin_staff';

$role = $_SESSION['role'] ?? null;

switch ($require_role) {
    case 'admin':
        if (!isset($_SESSION['user_id']) || $role !== 'admin') {
            header('Location: login.php');
            exit;
        }
        break;
    case 'staff':
        if (!isset($_SESSION['user_id']) || $role !== 'staff') {
            header('Location: login.php');
            exit;
        }
        break;
    default:
        // admin_staff
        if (!isset($_SESSION['user_id']) || !in_array($role, ['admin', 'staff'])) {
            header('Location: login.php');
            exit;
        }
        break;
}

// expose current admin id and name for convenience
$current_admin_id = $_SESSION['user_id'];
$current_admin_name = $_SESSION['name'] ?? '';
