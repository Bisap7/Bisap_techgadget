<?php
require_once 'config.php';

if (!isLoggedIn() || !isset($_GET['id'])) {
    redirect('login.php');
}

$order_id = (int)$_GET['id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND (o.user_id = ? OR ? = 1)
");
$is_admin = isAdmin() ? 1 : 0;
$stmt->execute([$order_id, $_SESSION['user_id'], $is_admin]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['message'] = 'Order not found or you do not have permission to view it';
    $_SESSION['message_type'] = 'danger';
    redirect('orders.php');
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #<?= $order['id'] ?></h2>
        <span class="badge 
            <?= $order['status'] === 'completed' ? 'bg-success' : 
               ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
            <?= ucfirst($order['status']) ?>
        </span>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" width="60" class="me-3">
                                                <div>
                                                    <h6 class="mb-0"><?= $item['name'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>रु <?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>रु <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>रु <?= number_format($order['total_amount'], 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td><strong>रु 0.00</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>रु <?= number_format($order['total_amount'], 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Customer Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= $order['username'] ?></p>
                    <p><strong>Email:</strong> <?= $order['email'] ?></p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Order Date:</strong> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
                    <p><strong>Payment Method:</strong> <?= $order['payment_method'] ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
                    
                    <?php if (isAdmin()): ?>
                        <form method="POST" action="update_order_status.php" class="mt-3">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <div class="mb-3">
                                <label for="status" class="form-label">Update Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
