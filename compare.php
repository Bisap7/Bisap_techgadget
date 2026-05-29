<?php
require_once 'config.php';
require_once 'header.php';

echo '<div class="container mt-5">';
echo '<h2>⚖ Compare Products</h2>';

echo '<a href="clear_compare.php" 
class="btn btn-danger mb-3"
onclick="return confirm(\'Clear all compared products?\')">
🗑 Clear Comparison
</a>';

if (empty($_SESSION['compare'])) {
    echo "<p>No products selected.</p>";
} else {

$ids = implode(",", $_SESSION['compare']);

$stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<table class="table table-bordered text-center mt-4">';

echo '<tr><th>Name</th>';
foreach($products as $p){
    echo "<td>{$p['name']}</td>";
}
echo '</tr>';

echo '<tr><th>Price</th>';
foreach($products as $p){
    echo "<td>NRs ".number_format($p['price'])."</td>";
}
echo '</tr>';

echo '<tr><th>Stock</th>';
foreach($products as $p){
    echo "<td>{$p['stock']}</td>";
}
echo '</tr>';

echo '<tr><th>Category</th>';
foreach($products as $p){
    echo "<td>{$p['category']}</td>";
}
echo '</tr>';

echo '</table>';
}

echo '</div>';

require_once 'footer.php';
?>