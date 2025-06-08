<?php
require_once 'config.php';

// Define how old pending orders must be to be deleted (e.g., older than 1 hour)
$timeLimit = "1 HOUR"; // You can change this to '30 MINUTE', '2 HOUR', etc.

try {
    // Delete pending orders older than the time limit with total Rs 10.00 and 0 items
    $stmt = $pdo->prepare("
        DELETE FROM orders 
        WHERE status = 'Pending' 
          AND total = 10.00 
          AND created_at < NOW() - INTERVAL $timeLimit
    ");

    $stmt->execute();

    echo "Old pending test orders cleaned up successfully.";
} catch (PDOException $e) {
    echo "Error cleaning orders: " . $e->getMessage();
}
