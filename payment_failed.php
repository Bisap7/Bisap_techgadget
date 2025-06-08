<?php
session_start();
require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Payment Successful</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php else: ?>
        <div class="alert alert-success">Your payment was processed successfully!</div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
    <a href="orders.php" class="btn btn-success mt-3 ms-2">View Your Orders</a>
</div>

<?php require_once 'footer.php'; ?>
