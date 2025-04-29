<?php
require_once 'config.php'; // Include config.php to access the redirect() function and others

if (!isset($_GET['id'])) {
    redirect('products.php');  // Use the redirect function from config.php
}

$product_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['message'] = 'Product not found';
    $_SESSION['message_type'] = 'danger';
    redirect('products.php'); // Redirect if product not found
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    if (!isLoggedIn()) {
        $_SESSION['message'] = 'Please login to add items to cart';
        $_SESSION['message_type'] = 'warning';
        redirect('login.php');  // Redirect if not logged in
    }
    
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity < 1 || $quantity > $product['stock']) {
        $_SESSION['message'] = 'Invalid quantity';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Check if product already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cart_item) {
            // Update quantity
            $new_quantity = $cart_item['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                $_SESSION['message'] = 'Not enough stock available';
                $_SESSION['message_type'] = 'danger';
            } else {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $cart_item['id']]);
                $_SESSION['message'] = 'Cart updated successfully';
                $_SESSION['message_type'] = 'success';
            }
        } else {
            // Add new item to cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
            $_SESSION['message'] = 'Product added to cart';
            $_SESSION['message_type'] = 'success';
        }
    }
    
    redirect("product.php?id=$product_id");  // Redirect back to the product page
}

require_once 'header.php';  // Include header
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <img src="images/<?= $product['image'] ?>" class="img-fluid rounded" alt="<?= $product['name'] ?>">
        </div>
        <div class="col-md-6">
            <h2><?= $product['name'] ?></h2>
            <p class="text-muted"><?= $product['category'] ?></p>
            <h3 class="text-primary">$<?= number_format($product['price'], 2) ?></h3>
            
            <div class="mb-3">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock (<?= $product['stock'] ?> available)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <p><?= nl2br($product['description']) ?></p>
            
            <?php if ($product['stock'] > 0): ?>
                <form method="POST">
                    <div class="input-group mb-3" style="width: 150px;">
                        <button class="btn btn-outline-secondary quantity-btn minus" type="button">-</button>
                        <input type="number" class="form-control text-center quantity-input" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button class="btn btn-outline-secondary quantity-btn plus" type="button">+</button>
                    </div>
                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Out of Stock</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>  <!-- Include footer -->
