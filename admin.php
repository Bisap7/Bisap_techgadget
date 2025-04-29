<?php
require_once 'config.php';

if (!isAdmin()) {
    $_SESSION['message'] = 'You do not have permission to access this page';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php');
}

// Get stats
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn();

// Recent orders
$recent_orders = $pdo->query("
    SELECT o.id, o.total_amount, o.status, o.created_at, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Low stock products
$low_stock_products = $pdo->query("
    SELECT * FROM products 
    WHERE stock < 5 
    ORDER BY stock ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Admin Dashboard</h2>
    
    <div class="row mt-4">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <h2 class="card-text"><?= $total_products ?></h2>
                    <a href="admin_products.php" class="text-white">View Products</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Orders</h5>
                    <h2 class="card-text"><?= $total_orders ?></h2>
                    <a href="admin_orders.php" class="text-white">View Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <h2 class="card-text"><?= $total_users ?></h2>
                    <a href="admin_users.php" class="text-white">View Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Revenue</h5>
                    <h2 class="card-text">$<?= number_format($revenue, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <p>No recent orders</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><a href="order.php?id=<?= $order['id'] ?>"><?= $order['id'] ?></a></td>
                                            <td><?= $order['username'] ?></td>
                                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?= $order['status'] === 'completed' ? 'bg-success' : 
                                                       ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="admin_orders.php" class="btn btn-sm btn-primary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Low Stock Products</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($low_stock_products)): ?>
                        <p>No low stock products</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($low_stock_products as $product): ?>
                                        <tr>
                                            <td><?= $product['name'] ?></td>
                                            <td>$<?= number_format($product['price'], 2) ?></td>
                                            <td class="<?= $product['stock'] < 1 ? 'text-danger' : 'text-warning' ?>">
                                                <?= $product['stock'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="admin_products.php" class="btn btn-sm btn-primary">View All Products</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>