<?php require_once 'header.php'; ?>

<div class="hero-section bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold">Welcome to Tech Gadget Store</h1>
                <p class="lead">Discover the latest and greatest tech gadgets at unbeatable prices.</p>
                <a href="products.php" class="btn btn-light btn-lg">Shop Now</a>
            </div>
            <div class="col-md-6">
                <img src="vec.png" alt="Tech Gadgets" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Featured Products -->
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row">
        <?php
        $stmt = $pdo->query("SELECT * FROM products ORDER BY RAND() LIMIT 4");
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <img src="images/<?= htmlspecialchars($product['image']) ?>" class="card-img-top p-3" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text">Rs. <?= number_format($product['price'], 2) ?></p>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge bg-success">In Stock</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Recommended Products -->
    <h2 class="text-center mt-5 mb-4">Recommended for You</h2>
    <div class="row">
        <?php
        $stmt = $pdo->query("
            SELECT p.*, AVG(r.rating) as avg_rating
            FROM products p
            JOIN reviews r ON p.id = r.product_id 
            GROUP BY p.id
            HAVING COUNT(r.id) >= 2 AND AVG(r.rating) >= 3.5
            ORDER BY avg_rating DESC
            LIMIT 4
        ");
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <div class="col-md-3 mb-4">
                <div class="card product-card h-100">
                    <img src="images/<?= htmlspecialchars($product['image']) ?>" class="card-img-top p-3" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text">Rs. <?= number_format($product['price'], 2) ?></p>
                        <p class="mb-1 text-warning">
                            ⭐ <?= round($product['avg_rating'], 1) ?> / 5
                        </p>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="badge bg-success">In Stock</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h2 class="text-center mt-5 mb-4">🔥 Trending This Week</h2>

    <div class="row">
        <?php
        $stmt = $pdo->query("
    SELECT * FROM products
    ORDER BY
    (sold_count * 5 + view_count * 2 + cart_count * 3 + rating * 4) DESC
    LIMIT 6
");

        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
            <div class="col-md-4 col-lg-2 mb-4">
                <div class="card product-card h-100">
                    <img src="images/<?= htmlspecialchars($product['image']) ?>"
                        class="card-img-top p-3"
                        alt="<?= htmlspecialchars($product['name']) ?>">

                    <div class="card-body text-center">
                        <h6 class="card-title">
                            <?= htmlspecialchars($product['name']) ?>
                        </h6>

                        <p class="card-text">
                            Rs. <?= number_format($product['price'], 2) ?>
                        </p>
                    </div>

                    <div class="card-footer bg-white">
                        <a href="product.php?id=<?= $product['id'] ?>"
                            class="btn btn-primary w-100">
                            View
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Features Section -->
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-truck text-primary" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-3">Free Shipping</h5>
                    <p class="card-text">On all orders over Rs. 5000</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-repeat text-primary" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-3">Easy Returns</h5>
                    <p class="card-text">30-day return policy</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-shield-check text-primary" style="font-size: 2rem;"></i>
                    <h5 class="card-title mt-3">Secure Payment</h5>
                    <p class="card-text">100% secure checkout</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>