<?php
require_once 'config.php';

// Admin only access
if (!isAdmin()) {
    $_SESSION['message'] = 'Access denied. Admins only.';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

// Validate order ID from GET parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid order ID.';
    $_SESSION['message_type'] = 'danger';
    redirect('admin_orders.php');
}

$order_id = (int)$_GET['id'];

// Debug: show order ID received
//echo "<pre>DEBUG ORDER ID: $order_id</pre>";

function formatNPR($amount) {
    return 'रु ' . number_format($amount, 2);
}

// Fetch order and user info
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['message'] = 'Order not found.';
    $_SESSION['message_type'] = 'danger';
    redirect('admin_orders.php');
}

// Fetch ordered products/items for this order
$stmt = $pdo->prepare("
    SELECT oi.*, p.name AS product_name, p.image AS product_image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug order items to see if any are fetched
//echo "<pre>DEBUG ORDER ITEMS:\n";
//print_r($order_items);
//echo "</pre>";

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Order Details - Order #<?= htmlspecialchars($order['id']) ?></h2>

    <div class="mb-4">
        <p><strong>Username:</strong> <?= htmlspecialchars($order['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        <p><strong>Total Amount:</strong> <?= formatNPR($order['total_amount']) ?></p>
        <p><strong>Status:</strong>
            <span class="badge bg-<?php
                switch (strtolower($order['status'])) {
                    case 'processing': echo 'warning'; break;
                    case 'shipped': echo 'info'; break;
                    case 'completed': echo 'success'; break;
                    case 'cancelled': echo 'danger'; break;
                    default: echo 'secondary';
                }
            ?>">
                <?= ucfirst($order['status']) ?>
            </span>
        </p>
        <p><strong>Created At:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
    </div>

    <h4>Ordered Products</h4>

    <?php if (empty($order_items)): ?>
        <div class="alert alert-warning">No products found in this order.</div>
        <div class="alert alert-info">
            Please check:<br>
            - Does the <code>order_items</code> table have entries for <code>order_id = <?= $order_id ?></code>?<br>
            - Is the order placement process inserting order items correctly?<br>
            - Try running this query in your database:<br>
            <code>SELECT * FROM order_items WHERE order_id = <?= $order_id ?>;</code>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px;">Image</th>
                        <th>Product Name</th>
                        <th>Price (Each)</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <?php
                                $imageFile = $item['product_image'];
                                $imageAlt = htmlspecialchars($item['product_name']);
                                $imageUrl = '/bisap/images/' . $imageFile;  // URL path to images
                                $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/bisap/images/' . $imageFile;  // Physical path on server

                                if (!empty($imageFile) && file_exists($imagePath)) {
                                    echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . $imageAlt . '" style="max-width:80px; max-height:80px;" loading="lazy">';
                                } else {
                                    echo '<span class="text-danger">Image not found</span>';
                                }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= formatNPR($item['price']) ?></td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td><?= formatNPR($item['price'] * $item['quantity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                        <td><strong><?= formatNPR($order['total_amount']) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <a href="admin_orders.php" class="btn btn-secondary mt-4">← Back to Orders</a>
</div>

<?php require_once 'footer.php'; ?>
