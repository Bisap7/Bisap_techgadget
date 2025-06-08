<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $_SESSION['message'] = 'Product not found';
    $_SESSION['message_type'] = 'danger';
    redirect('products.php');
}

// Fetch average rating and total reviews
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = ?");
$stmt->execute([$product_id]);
$rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];

// Fetch all reviews
$stmt = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $_SESSION['message'] = 'Please login to add items to cart';
        $_SESSION['message_type'] = 'warning';
        redirect('login.php');
    }

    $quantity = (int)$_POST['quantity'];

    if ($quantity < 1 || $quantity > $product['stock']) {
        $_SESSION['message'] = 'Invalid quantity';
        $_SESSION['message_type'] = 'danger';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
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
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
            $_SESSION['message'] = 'Product added to cart';
            $_SESSION['message_type'] = 'success';
        }
    }

    redirect("product.php?id=$product_id");
}

require_once 'header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <img src="images/<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($product['category']) ?></p>
            <h3 class="text-primary">Nrs <?= number_format($product['price']) ?></h3>
            <p>⭐ <?= $avg_rating ?> / 5 (<?= $total_reviews ?> reviews)</p>

            <div class="mb-3">
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success">In Stock (<?= $product['stock'] ?> available)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </div>

            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

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

    <hr>

    <h4>Reviews</h4>

    <?php if (isLoggedIn()): ?>
        <form id="ratingForm">
            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <div class="mb-2">
                <label>Rate this product:</label><br>
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>">
                        <label for="star<?= $i ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            <textarea name="review" class="form-control mb-2" placeholder="Write a review..." required></textarea>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    <?php else: ?>
        <p class="text-muted">Please <a href="login.php">login</a> to leave a review.</p>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
        <div id="ratingDisplay" class="mt-3">
            <?php foreach ($reviews as $review): ?>
                <div class="border p-2 mb-2 rounded">
                    <strong><?= htmlspecialchars($review['username']) ?></strong> 
                    <span class="text-warning">
                        <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                    </span><br>
                    <small class="text-muted"><?= date('F j, Y', strtotime($review['created_at'])) ?></small>
                    <p><?= nl2br(htmlspecialchars($review['review'])) ?></p>

                    <?php if ($review['user_id'] == $_SESSION['user_id']): ?>
                        <form class="d-inline delete-review-form" method="POST" data-id="<?= $review['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">Please <a href="login.php">login</a> to see reviews.</p>
    <?php endif; ?>
</div>

<style>
.star-rating {
    direction: rtl;
    display: inline-flex;
}
.star-rating input[type="radio"] {
    display: none;
}
.star-rating label {
    font-size: 1.5rem;
    color: #ccc;
    cursor: pointer;
}
.star-rating input[type="radio"]:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: gold;
}
</style>

<script>
document.getElementById('ratingForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('rating_submit.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error('Review error:', err));
});
</script>

<script>
document.querySelectorAll('.delete-review-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (confirm("Are you sure you want to delete this review?")) {
            const reviewId = this.getAttribute('data-id');
            fetch('delete_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'review_id=' + reviewId
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    });
});
</script>

<?php require_once 'footer.php'; ?>
