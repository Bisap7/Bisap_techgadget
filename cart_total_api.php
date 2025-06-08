<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Database connection (adjust with your config)
require_once 'config.php';

try {
    $stmt = $pdo->prepare("
        SELECT SUM(p.price * c.quantity) AS total 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = $row['total'] ?? 0;

    echo json_encode(['total' => $total]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
