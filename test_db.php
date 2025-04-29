<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'adminmain'");
    $admin = $stmt->fetch();
    
    echo "<pre>Admin user: ";
    print_r($admin);
    echo "</pre>";
    
    echo "Password verify result: ";
    var_dump(password_verify('adminmaiho', $admin['password']));
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}