<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$order_id = $_POST['order_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$signature = $_POST['signature'] ?? '';

if (!$order_id || !$amount || !$signature) {
    die("Missing parameters.");
}

// Verify signature
$data = "$order_id|$amount";
$expectedSignature = hash_hmac('sha256', $data, PAYMENT_SECRET);

if ($signature !== $expectedSignature) {
    die("Invalid payment signature.");
}

// Update order status to Completed
$stmt = $pdo->prepare("UPDATE orders SET status = 'Completed', payment_method = 'SimulatedPay', ref_id = ?, payment_reference = ? WHERE id = ?");
$refId = uniqid('REF');
$payRef = uniqid('SIMPAY');
$stmt->execute([$refId, $payRef, $order_id]);

echo "<h2>Payment Successful</h2>";
echo "<p>Your order #$order_id has been marked as Completed.</p>";
echo '<p><a href="orders.php">Go to My Orders</a></p>';
