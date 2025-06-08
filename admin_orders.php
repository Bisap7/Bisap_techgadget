<?php
require_once 'config.php'; // Include DB + helpers

function formatNPR($amount) {
    return 'रु ' . number_format($amount, 2);
}

// Only admin can access
if (!isAdmin()) {
    $_SESSION['message'] = 'Access denied. Admins only.';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

// Define allowed statuses
$allowed_statuses = ['Processing', 'Shipped', 'Completed', 'Cancelled'];

// Handle status update with Admin PIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'], $_POST['admin_pin'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    $admin_pin = sanitize($_POST['admin_pin']);

    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['message'] = '❌ Invalid status selected.';
        $_SESSION['message_type'] = 'danger';
        redirect('admin_orders.php');
    }

    $expected_pin = '123456'; // 🔐 Change this or store in config/env securely
    if ($admin_pin !== $expected_pin) {
        $_SESSION['message'] = '❌ Invalid Admin PIN. Status update denied.';
        $_SESSION['message_type'] = 'danger';
        redirect('admin_orders.php');
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = ?, status_updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    $_SESSION['message'] = '✅ Order status updated successfully!';
    $_SESSION['message_type'] = 'success';
    redirect('admin_orders.php');
}

// Fetch all orders
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>All Orders</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <table class="table table-bordered table-striped align-middle">
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
                <td><?= formatNPR($order['total_amount']) ?></td>
                <td>
                    <span class="badge bg-<?php
                        switch ($order['status']) {
                            case 'Processing': echo 'warning'; break;
                            case 'Shipped': echo 'info'; break;
                            case 'Completed': echo 'success'; break;
                            case 'Cancelled': echo 'danger'; break;
                            default: echo 'secondary';
                        }
                    ?>"><?= htmlspecialchars($order['status']) ?></span>
                </td>
                <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                <td>
                    <!-- FIXED: Pass 'id' as GET param -->
                    <a href="admin_order_details.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm mb-1">View</a>

                    <form action="admin_orders.php" method="POST" class="d-inline" onsubmit="return confirmStatusChange(this)">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="admin_pin" value="">
                        <select name="status" class="form-select form-select-sm d-inline w-auto" required>
                            <?php foreach ($allowed_statuses as $status): ?>
                                <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary ms-1">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmStatusChange(form) {
    const newStatus = form.status.value;
    // Find selected option from the select element
    const currentStatus = form.querySelector('option[selected]')?.value || '';

    if (newStatus === currentStatus) {
        alert('Status unchanged.');
        return false;
    }

    const confirmMsg = `Change order status from "${currentStatus}" to "${newStatus}"?`;
    if (!confirm(confirmMsg)) {
        return false;
    }

    const pin = prompt("Enter Admin PIN to confirm this action:");
    if (!pin || pin.trim() === '') {
        alert("Status change cancelled. Admin PIN required.");
        return false;
    }

    form.admin_pin.value = pin.trim();
    return true; // allow form submit
}
</script>

<?php require_once 'footer.php'; ?>
