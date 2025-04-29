<?php
require_once 'config.php';

// Ensure the user is logged in to view the cart
if (!isLoggedIn()) {
    $_SESSION['message'] = 'Please login to view your cart';
    $_SESSION['message_type'] = 'warning';
    redirect('login.php'); // Use the redirect function from config.php
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update quantities or remove items
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $cart_id => $quantity) {
            $cart_id = (int)$cart_id;
            $quantity = (int)$quantity;

            if ($quantity < 1) {
                // Remove item if quantity is 0
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $_SESSION['user_id']]);
            } else {
                // Update quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
            }
        }

        $_SESSION['message'] = 'Cart updated successfully';
        $_SESSION['message_type'] = 'success';
        redirect('cart.php'); // Use the redirect function from config.php
    } elseif (isset($_POST['remove_item'])) {
        $cart_id = (int)$_POST['cart_id'];
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);

        $_SESSION['message'] = 'Item removed from cart';
        $_SESSION['message_type'] = 'success';
        redirect('cart.php'); // Use the redirect function from config.php
    } elseif (isset($_POST['checkout'])) {
        redirect('checkout.php'); // Use the redirect function from config.php
    }
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

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Your Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="products.php">Continue shopping</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" width="80" class="me-3">
                                        <div>
                                            <h6 class="mb-0"><?= $item['name'] ?></h6>
                                            <?php if ($item['quantity'] > $item['stock']): ?>
                                                <small class="text-danger">Only <?= $item['stock'] ?> available</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <div class="input-group" style="width: 120px;">
                                        <input type="number" class="form-control quantity-input" 
                                            name="quantities[<?= $item['cart_id'] ?>]" 
                                            value="<?= $item['quantity'] ?>" 
                                            min="1" max="<?= $item['stock'] ?>">
                                    </div>
                                </td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <!-- Add cart_id in value for removal -->
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>"> <!-- Hidden cart_id -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$<?= number_format($total, 2) ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between">
                <a href="products.php" class="btn btn-outline-primary">Continue Shopping</a>
                <div>
                    <button type="submit" name="update_cart" class="btn btn-secondary me-2">Update Cart</button>
                    <button type="submit" name="checkout" class="btn btn-primary">Proceed to Checkout</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
