<?php
require_once 'config.php';

if (!isset($_GET['order_id'])) {
    redirect('index.php');
}

$order_id = intval($_GET['order_id']);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['message'] = "Order not found.";
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Order Confirmation</h2>
    <p>Order ID: <?= htmlspecialchars($order['id']) ?></p>
    <p>Status: <?= htmlspecialchars($order['status']) ?></p>
    <p>Total Amount: Rs <?= number_format($order['total_amount'], 2) ?></p>

    <h4>Items:</h4>
    <ul>
        <?php foreach ($order_items as $item): ?>
            <li><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?> - Rs <?= number_format($item['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
</div>

<?php require_once 'footer.php'; ?>
