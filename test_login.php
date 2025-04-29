<?php
require_once 'config.php';

// Test credentials
$test_user = [
    'username' => 'adminmain',
    'password' => 'adminmaiho'
];

// Test database connection
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$test_user['username']]);
    $user = $stmt->fetch();
    
    echo "<h2>Login Test Results</h2>";
    echo "<div style='background:#f0f0f0;padding:20px;'>";
    
    if ($user) {
        echo "<p style='color:green'>✓ User found</p>";
        echo "<pre>".print_r($user,true)."</pre>";
        
        if (password_verify($test_user['password'], $user['password'])) {
            echo "<p style='color:green'>✓ Password matches!</p>";
            
            // Manually login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: admin.php');
            exit();
        } else {
            echo "<p style='color:red'>✗ Password incorrect</p>";
            echo "<p>Hash in database: ".$user['password']."</p>";
        }
    } else {
        echo "<p style='color:red'>✗ User not found</p>";
    }
    echo "</div>";
} catch (PDOException $e) {
    die("<p style='color:red'>Database error: ".$e->getMessage()."</p>");
}
?>