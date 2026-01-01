require_once '../config/error_handler.php';
<?php
session_start();
session_unset();
session_destroy();
header('Location: index.php?logout_success=1');
exit;
