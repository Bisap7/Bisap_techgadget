<?php
// Include DB connection (make sure $pdo is set in db.php)
require_once 'db.php';

// Get order ID from GET parameter
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    echo "Invalid order ID.";
    exit;
}

// Fetch ordered items with product info
try {
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name AS product_name, p.image AS product_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage());
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order #<?= htmlspecialchars($order_id) ?> - Ordered Products</title>
    <style>
        .product {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            display: flex;
            align-items: center;
        }
        .product img {
            height: 100px;
            margin-right: 15px;
            object-fit: contain;
        }
        .product-details {
            flex: 1;
        }
    </style>
</head>
<body>
    <h2>Ordered Products (Order #<?= htmlspecialchars($order_id) ?>)</h2>

    <?php if (empty($order_items)): ?>
        <p>No products found in this order.</p>
    <?php else: ?>
        <?php foreach ($order_items as $item): ?>
            <div class="product">
                <?php
                $imagePath = 'images/' . $item['product_image'];
                if (!empty($item['product_image']) && file_exists($imagePath)) {
                    echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($item['product_name']) . '">';
                } else {
                    echo '<div style="width:100px; height:100px; background:#f0f0f0; color:#999; display:flex; align-items:center; justify-content:center; font-size:12px;">Image not found</div>';
                }
                ?>
                <div class="product-details">
                    <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                    <p>Quantity: <?= (int)$item['quantity'] ?></p>
                    <p>Price: Rs. <?= number_format($item['price'], 2) ?></p>
                    <p>Total: Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
