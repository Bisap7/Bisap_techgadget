<?php
require_once 'config.php';  // Ensure config.php is included, which has the redirect() function

// Ensure the user is logged in to checkout
if (!isLoggedIn()) {
    $_SESSION['message'] = 'Please login to checkout';
    $_SESSION['message_type'] = 'warning';
    redirect('login.php');  // Use the redirect function from config.php
}

// Get cart items with product details
$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.quantity, p.id, p.name, p.price, p.image, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if cart is empty
if (empty($cart_items)) {
    $_SESSION['message'] = 'Your cart is empty';
    $_SESSION['message_type'] = 'warning';
    redirect('cart.php');  // Use the redirect function from config.php
}

// Check stock availability
$out_of_stock = false;
foreach ($cart_items as $item) {
    if ($item['quantity'] > $item['stock']) {
        $out_of_stock = true;
        break;
    }
}

if ($out_of_stock) {
    $_SESSION['message'] = 'Some items in your cart are out of stock or quantity exceeds availability';
    $_SESSION['message_type'] = 'danger';
    redirect('cart.php');  // Use the redirect function from config.php
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, payment_method) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total, 'eSewa']);
        $order_id = $pdo->lastInsertId();
        
        // Add order items and update product stock
        foreach ($cart_items as $item) {
            // Add to order items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            
            // Update product stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        
        $_SESSION['message'] = 'Order placed successfully! Thank you for your purchase.';
        $_SESSION['message_type'] = 'success';
        redirect('order.php?id=' . $order_id);  // Use the redirect function from config.php
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = 'Checkout failed: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        redirect('checkout.php');  // Use the redirect function from config.php
    }
}

require_once 'header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
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
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" width="60" class="me-3">
                                                <div>
                                                    <h6 class="mb-0"><?= $item['name'] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong>$<?= number_format($total, 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td><strong>$0.00</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>$<?= number_format($total, 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="esewa" value="eSewa" checked>
                                <label class="form-check-label" for="esewa">
                                    <img src="esewa.png" alt="eSewa" class="img-fluid">
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p>If you have any questions about your order, please contact our customer service.</p>
                    <a href="contact.php" class="btn btn-outline-secondary">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
