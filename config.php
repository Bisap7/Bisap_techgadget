<?php
ob_start(); // Start output buffering
session_start();

// Database connection
$host = 'localhost';
$db   = 'tech_gadget_store'; // your real database name
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return (isset($_SESSION['username']) && $_SESSION['username'] === 'adminmain');
}

// Redirect helper
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
