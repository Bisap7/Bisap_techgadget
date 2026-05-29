<?php
require_once 'config.php';
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount),0) as total_spent FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$total_orders = $stats['total_orders'];

$badge = "Bronze";
if ($total_orders >= 3) $badge = "Silver";
if ($total_orders >= 6) $badge = "Gold";
?>

<div class="container mt-5">
    <h2>👤 My Profile</h2>

    <div class="card p-4 mt-4">
        <h4>Welcome back, <?= htmlspecialchars($user['username']) ?> 👋</h4>
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Member Since: <?= $user['created_at'] ?></p>
        <p><strong>Loyalty Level:</strong> <?= $badge ?></p>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card p-3 text-center">
                <h5>Total Orders</h5>
                <h2><?= $stats['total_orders'] ?></h2>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 text-center">
                <h5>Total Spent</h5>
                <h2>Rs <?= number_format($stats['total_spent'],2) ?></h2>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="orders.php" class="btn btn-primary">My Orders</a>
        <a href="cart.php" class="btn btn-warning">My Cart</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>