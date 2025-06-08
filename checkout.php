<?php
require 'config.php';  // DB and session setup

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Calculate total amount from cart
function getCartTotal($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT SUM(c.quantity * p.price) AS total
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

$amount = getCartTotal($pdo, $userId);

if ($amount <= 0) {
    echo "Cart is empty or invalid amount.";
    exit;
}

// Generate unique order reference
$orderRef = uniqid("ORD_");

// Insert order with status Pending
$stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, created_at, ref_id) VALUES (?, ?, 'Pending', 'eSewa', NOW(), ?)");
$stmtOrder->execute([$userId, $amount, $orderRef]);

// Get the last inserted order ID
$orderId = $pdo->lastInsertId();

if (!$orderId) {
    echo "Failed to create order.";
    exit;
}

// Fetch cart items for insertion into order_items
$stmtCartItems = $pdo->prepare("
    SELECT c.product_id, c.quantity, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmtCartItems->execute([$userId]);
$cartItems = $stmtCartItems->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    echo "Cart is empty. Cannot create order items.";
    exit;
}

// Prepare statement once for inserting order items
$stmtInsertItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

// Insert each cart item
foreach ($cartItems as $item) {
    $stmtInsertItem->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
}

// Clear the user's cart after order creation
$stmtClearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
$stmtClearCart->execute([$userId]);

// eSewa integration parameters
$productCode = "EPAYTEST";
$secretKey = "8gBm/:&EnhH.1/q";
$signedFields = "total_amount,transaction_uuid,product_code";

$signatureData = "total_amount=$amount,transaction_uuid=$orderRef,product_code=$productCode";
$signature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));
?>

<form method="POST" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form">
    <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
    <input type="hidden" name="tax_amount" value="0">
    <input type="hidden" name="total_amount" value="<?= htmlspecialchars($amount) ?>">
    <input type="hidden" name="transaction_uuid" value="<?= htmlspecialchars($orderRef) ?>">
    <input type="hidden" name="product_code" value="<?= htmlspecialchars($productCode) ?>">
    <input type="hidden" name="product_service_charge" value="0">
    <input type="hidden" name="product_delivery_charge" value="0">
    <input type="hidden" name="success_url" value="http://localhost/bisap/esewa_success.php">
    <input type="hidden" name="failure_url" value="http://localhost/bisap/esewa_failure.php">
    <input type="hidden" name="signed_field_names" value="<?= htmlspecialchars($signedFields) ?>">
    <input type="hidden" name="signature" value="<?= htmlspecialchars($signature) ?>">

    <button type="submit" class="btn btn-success">Pay via eSewa</button>
</form>
