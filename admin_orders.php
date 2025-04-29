<?php
require_once 'config.php'; // Include the config file

// Check if the user is an admin
if (!isAdmin()) {
    $_SESSION['message'] = 'Access denied. Admins only.';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php'); // Redirect to homepage if not admin
}

// Fetch all orders from the database
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php'; // Include the header file
?>

<div class="container mt-4">
    <h2>All Orders</h2>

    <!-- Displaying any session messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= htmlspecialchars($order['email']) ?></td>
                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <span class="badge bg-<?php
                        // Display order status with appropriate color
                        switch ($order['status']) {
                            case 'Processing':
                                echo 'warning';
                                break;
                            case 'Shipped':
                                echo 'info';
                                break;
                            case 'Completed':
                                echo 'success';
                                break;
                            case 'Cancelled':
                                echo 'danger';
                                break;
                            default:
                                echo 'secondary';
                        }
                        ?>"><?= htmlspecialchars($order['status']) ?></span>
                    </td>
                    <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                    <td>
                        <!-- Link to view order details -->
                        <a href="admin_order_details.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm">View</a>

                        <!-- Dropdown to change the order status -->
                        <form action="admin_orders.php" method="POST" class="d-inline">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Completed" <?= $order['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']); // Sanitize the input for status

    // Update the order status in the database
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    $_SESSION['message'] = 'Order status updated successfully!';
    $_SESSION['message_type'] = 'success';

    // Refresh the page to reflect the updated status
    redirect('admin_orders.php');
}

require_once 'footer.php'; // Include footer
?>
