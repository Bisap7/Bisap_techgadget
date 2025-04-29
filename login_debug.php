<?php
require 'config.php';

// Test credentials
$test = [
    'username' => 'adminmain',
    'password' => 'adminmaiho'
];

// Debug output
echo "<h2>Login Debug</h2>";
echo "<pre>Testing with username: {$test['username']}</pre>";

// 1. Check database connection
try {
    $pdo->query("SELECT 1");
    echo "<p style='color:green'>✓ Database connected</p>";
} catch (PDOException $e) {
    die("<p style='color:red'>✗ Database error: ".$e->getMessage()."</p>");
}

// 2. Check user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$test['username']]);
$user = $stmt->fetch();

if ($user) {
    echo "<p style='color:green'>✓ User found</p>";
    echo "<pre>".print_r($user, true)."</pre>";
    
    // 3. Verify password
    if (password_verify($test['password'], $user['password'])) {
        echo "<p style='color:green'>✓ Password correct</p>";
        
        // 4. Test session
        session_start();
        $_SESSION['test'] = 'success';
        if ($_SESSION['test'] === 'success') {
            echo "<p style='color:green'>✓ Sessions working</p>";
            echo "<a href='admin.php' style='padding:10px;background:green;color:white'>Proceed to Admin Panel</a>";
        } else {
            echo "<p style='color:red'>✗ Session test failed</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Password incorrect</p>";
        echo "<p>Stored hash: {$user['password']}</p>";
        echo "<p>Expected hash for 'adminmaiho': $2y$10$Wq3vRnE5eUTk5VZ1xYQZBeX4v6e3mJ9Xo1dR2gLhA7bNcKvYs1W</p>";
    }
} else {
    echo "<p style='color:red'>✗ User not found</p>";
    echo "<p>Run this SQL to create admin:</p>";
    echo "<pre style='background:#eee;padding:10px'>INSERT INTO users (id, username, email, password) VALUES (1, 'adminmain', 'admin@gmail.com', '\$2y\$10\$Wq3vRnE5eUTk5VZ1xYQZBeX4v6e3mJ9Xo1dR2gLhA7bNcKvYs1W');</pre>";
}
?>