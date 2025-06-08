<?php
ob_start(); // Start output buffering

// ✅ Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = 'localhost';
$db   = 'tech_gadget_store'; // your actual database name
$user = 'root';
$pass = ''; // your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Helper function to check if user is logged in
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

// Helper function to check if user is admin
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return (isset($_SESSION['username']) && $_SESSION['username'] === 'adminmain');
    }
}

// Redirect helper
if (!function_exists('redirect')) {
    function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
}

// Sanitize input
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

// Payment secret key (keep this safe)
define('PAYMENT_SECRET', '5gTz$Lm!8wK2@#Fz1'); 

// Admin confirmation PIN for sensitive actions like changing order status
define('ADMIN_CONFIRMATION_PIN', '123456'); // Change this to a strong PIN
?>
