<?php
require_once 'config.php';

$order_id = $_GET['order_id'] ?? '';
$amount = $_GET['amount'] ?? '';
$signature = $_GET['signature'] ?? '';

if (!$order_id || !$amount || !$signature) {
    die("Invalid access.");
}

// Optional: Verify signature here to ensure it's from checkout
$expected = hash_hmac('sha256', "$order_id|$amount", PAYMENT_SECRET);
if (!hash_equals($expected, $signature)) {
    die("Signature mismatch. Tampering suspected.");
}
?>

<h2>Simulated Payment Gateway</h2>
<p>You're paying Rs <?= $amount ?> for Order ID: <?= $order_id ?></p>

<form action="verify_payment.php" method="POST">
    <input type="hidden" name="order_id" value="<?= $order_id ?>">
    <input type="hidden" name="amount" value="<?= $amount ?>">
    <input type="hidden" name="signature" value="<?= $signature ?>">
    <button type="submit" name="pay">Simulate Payment</button>
</form>
