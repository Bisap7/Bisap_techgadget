<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['message'] = 'Please log in to view your transactions.';
    $_SESSION['message_type'] = 'warning';
    redirect('login.php');
}

// Fetch user transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-5">
    <h3 class="mb-4">Your Transaction History</h3>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <?php if (count($transactions) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Amount (Rs)</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $txn): ?>
                        <tr>
                            <td><?= htmlspecialchars($txn['id']) ?></td>
                            <td><?= $txn['order_id'] ? htmlspecialchars($txn['order_id']) : 'N/A' ?></td>
                            <td><?= number_format($txn['amount'], 2) ?></td>
                            <td>
                                <span class="badge <?= $txn['type'] === 'credit' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($txn['type']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($txn['method']) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($txn['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You have no transaction history yet.
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
