<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// ✅ Fetch only orders with 1 or more items
$stmt = $pdo->prepare("
    SELECT o.id, o.total_amount, o.status, o.created_at, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    HAVING item_count > 0
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">
            You haven't placed any valid orders yet. <a href="products.php">Start shopping</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td><?= (int)$order['item_count'] ?></td>
                            <td>Rs <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge 
                                    <?= $order['status'] === 'Completed' ? 'bg-success' : 
                                       ($order['status'] === 'Pending' ? 'bg-warning' : 
                                       ($order['status'] === 'Shipped' ? 'bg-info' : 'bg-secondary')) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="order.php?id=<?= urlencode($order['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
