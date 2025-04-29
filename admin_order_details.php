<?php
require_once 'config.php';

// Ensure admin is logged in
if (!isAdmin()) {
    $_SESSION['message'] = 'You must be an admin to access this page';
    $_SESSION['message_type'] = 'warning';
    redirect('login.php');
}

// Get order details
$order_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT o.id as order_id, o.total_amount, o.payment_method, o.created_at, u.username, oi.product_id, oi.quantity, oi.price, p.name as product_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.id = ?
");

$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the order exists
if (!$order_details) {
    $_SESSION['message'] = 'Order not found';
    $_SESSION['message_type'] = 'danger';
    redirect('admin_orders.php');
}

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Order Details</h2>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Order #<?= $order_details[0]['order_id'] ?> - Placed by <?= $order_details[0]['username'] ?> on <?= $order_details[0]['created_at'] ?></h5>
        </div>
        <div class="card-body">
            <p><strong>Payment Method:</strong> <?= $order_details[0]['payment_method'] ?></p>
            <p><strong>Total Amount:</strong> $<?= number_format($order_details[0]['total_amount'], 2) ?></p>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_details as $item): ?>
                        <tr>
                            <td><?= $item['product_name'] ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$<?= number_format($order_details[0]['total_amount'], 2) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
