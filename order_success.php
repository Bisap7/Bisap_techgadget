<?php
require_once 'config.php';
session_start();

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = (int)$_GET['order_id'];

// Fetch order details + user info
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['message'] = "Order not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Order Confirmation</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <p>Thank you, <strong><?= htmlspecialchars($order['username']) ?></strong>, for your purchase!</p>
    <p><strong>Order ID:</strong> <?= $order['id'] ?></p>
    <p><strong>Total Amount:</strong> Rs. <?= number_format($order['total_amount'], 2) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
    <p><strong>Payment Reference:</strong> <?= htmlspecialchars($order['payment_ref'] ?? '-') ?></p>
    <p><strong>Order Date:</strong> <?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></p>

    <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
</div>

<?php require_once 'footer.php'; ?>
