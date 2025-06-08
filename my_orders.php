<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id']; // Must be set during login

$res = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<h2>📦 My Orders</h2>
<table border="1">
    <tr>
        <th>Order ID</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Payment Method</th>
        <th>Ordered On</th>
    </tr>
    <?php while ($row = $res->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td>Rs. <?= $row['total_amount'] ?></td>
        <td><?= ucfirst($row['status']) ?></td>
        <td><?= $row['payment_method'] ?></td>
        <td><?= $row['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
