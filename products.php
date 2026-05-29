<?php
require_once 'config.php';

// Sanitize input
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name_asc';

// Properly handle min_price and max_price to avoid filtering issues
$min_price = null;
if (isset($_GET['min_price']) && $_GET['min_price'] !== '') {
    $min_price = floatval($_GET['min_price']);
}

$max_price = null;
if (isset($_GET['max_price']) && $_GET['max_price'] !== '') {
    $max_price = floatval($_GET['max_price']);
}

// Build query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

if (!is_null($min_price)) {
    $query .= " AND price >= ?";
    $params[] = $min_price;
}

if (!is_null($max_price)) {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}

// Sorting
switch ($sort) {
    case 'name_asc':
        $query .= " ORDER BY name ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY name DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    default:
        $query .= " ORDER BY name ASC";
}

// Get categories for filter dropdown
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

// Get products
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="products.php">
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price Range (NRs)</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text">Min</span>
                                <input type="number" class="form-control" name="min_price" value="<?= is_null($min_price) ? '' : htmlspecialchars($min_price) ?>" placeholder="Min (NRs)" step="0.01" min="0">
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">Max</span>
                                <input type="number" class="form-control" name="max_price" value="<?= is_null($max_price) ? '' : htmlspecialchars($max_price) ?>" placeholder="Max (NRs)" step="0.01" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Products</h2>
                <div>
                    <?= count($products) ?> products found
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    No products found matching your criteria.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card product-card h-100">
                                <img src="images/<?= htmlspecialchars($product['image']) ?>" class="card-img-top p-3" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</p>
                                    <p class="card-text"><strong>NRs <?= number_format($product['price'], 2) ?></strong></p>
                                    <?php if ($product['stock'] <= 0): ?>

                                        <span class="badge bg-danger">Out of Stock</span>

                                    <?php elseif ($product['stock'] <= 3): ?>

                                        <span class="badge bg-danger">
                                            Only <?= $product['stock'] ?> left!
                                        </span>

                                        <p class="text-danger small mt-1 fw-bold">
                                            Hurry! Selling fast 🔥
                                        </p>

                                    <?php elseif ($product['stock'] <= 10): ?>

                                        <span class="badge bg-warning text-dark">
                                            Limited Stock (<?= $product['stock'] ?>)
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">
                                            In Stock (<?= $product['stock'] ?>)
                                        </span>

                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-white">

                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary w-100 mb-2">
                                        View Details
                                    </a>

                                    <form method="post" action="compare_add.php">
                                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="btn btn-warning w-100">
                                            ⚖ Compare
                                        </button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>