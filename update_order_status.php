<?php
require_once 'config.php'; // config.php already has redirect()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_id']) && isset($_POST['status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            $_SESSION['message'] = 'Order status updated successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to update order status.';
            $_SESSION['message_type'] = 'danger';
        }

        redirect('orders.php');
    } else {
        $_SESSION['message'] = 'Invalid request. Please provide the order ID and status.';
        $_SESSION['message_type'] = 'danger';
        redirect('orders.php');
    }
} else {
    $_SESSION['message'] = 'Invalid request method.';
    $_SESSION['message_type'] = 'danger';
    redirect('orders.php');
}
?>
