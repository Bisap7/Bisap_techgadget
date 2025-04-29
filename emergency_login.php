<?php
session_start();
require 'config.php';

// Force admin login
$_SESSION = [
    'user_id' => 1,
    'username' => 'adminmain',
    'email' => 'admin@gmail.com',
    'is_admin' => true
];

echo "Admin session forced. <a href='admin.php'>Go to Admin Panel</a>";
?>