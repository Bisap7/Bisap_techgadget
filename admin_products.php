<?php
// admin_products.php

require_once 'config.php';

if (!isAdmin()) {
    redirect('index.php');
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $category = sanitize($_POST['category']);

        // Handle image upload
        $image = 'default.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'images/';
            $file_name = basename($_FILES['image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $image = uniqid() . '.' . $file_ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $price, $stock, $category, $image])) {
            $_SESSION['message'] = 'Product added successfully';
            $_SESSION['message_type'] = 'success';
            redirect('admin_products.php');
        } else {
            $_SESSION['message'] = 'Failed to add product';
            $_SESSION['message_type'] = 'danger';
        }
    } elseif (isset($_POST['update_product'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $category = sanitize($_POST['category']);

        // Handle image update
        $image = $_POST['existing_image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'images/';
            $file_name = basename($_FILES['image']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $image = uniqid() . '.' . $file_ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);

                // Delete old image if not default
                if ($_POST['existing_image'] !== 'default.jpg') {
                    @unlink($upload_dir . $_POST['existing_image']);
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image = ? WHERE id = ?");
        if ($stmt->execute([$name, $description, $price, $stock, $category, $image, $id])) {
            $_SESSION['message'] = 'Product updated successfully';
            $_SESSION['message_type'] = 'success';
            redirect('admin_products.php');
        } else {
            $_SESSION['message'] = 'Failed to update product';
            $_SESSION['message_type'] = 'danger';
        }
    } elseif (isset($_POST['delete_product'])) {
        $id = (int)$_POST['id'];

        // Get image to delete
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Delete image if not default
            if ($product['image'] !== 'default.jpg') {
                @unlink('images/' . $product['image']);
            }

            $_SESSION['message'] = 'Product deleted successfully';
            $_SESSION['message_type'] = 'success';
            redirect('admin_products.php');
        } else {
            $_SESSION['message'] = 'Failed to delete product';
            $_SESSION['message_type'] = 'danger';
        }
    }
}

// Fetch all products
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Manage Products</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?>">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus"></i> Add Product
        </button>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">
            No products found.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><img src="images/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="50"></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['stock'] ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-product" 
                                    data-id="<?= $product['id'] ?>"
                                    data-name="<?= htmlspecialchars($product['name']) ?>"
                                    data-description="<?= htmlspecialchars($product['description']) ?>"
                                    data-price="<?= $product['price'] ?>"
                                    data-stock="<?= $product['stock'] ?>"
                                    data-category="<?= htmlspecialchars($product['category']) ?>"
                                    data-image="<?= $product['image'] ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Fields -->
                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label>Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
                        </div>
                        <div class="col">
                            <label>Stock</label>
                            <input type="number" class="form-control" name="stock" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label>Category</label>
                        <input type="text" class="form-control" name="category">
                    </div>
                    <div class="mb-3">
                        <label>Product Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="existing_image" id="existing_image">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Fields -->
                    <div class="mb-3">
                        <label>Product Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label>Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="edit_price" required>
                        </div>
                        <div class="col">
                            <label>Stock</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label>Category</label>
                        <input type="text" class="form-control" name="category" id="edit_category">
                    </div>
                    <div class="mb-3">
                        <label>Change Image (optional)</label>
                        <input type="file" class="form-control" name="image" id="edit_image" accept="image/*">
                        <div class="mt-2">
                            <img id="current_image" src="" width="100" class="img-thumbnail">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Product - Fill Modal with Current Data
document.querySelectorAll('.edit-product').forEach(button => {
    button.addEventListener('click', () => {
        document.getElementById('edit_id').value = button.dataset.id;
        document.getElementById('edit_name').value = button.dataset.name;
        document.getElementById('edit_description').value = button.dataset.description;
        document.getElementById('edit_price').value = button.dataset.price;
        document.getElementById('edit_stock').value = button.dataset.stock;
        document.getElementById('edit_category').value = button.dataset.category;
        document.getElementById('existing_image').value = button.dataset.image;
        document.getElementById('current_image').src = 'images/' + button.dataset.image;

        new bootstrap.Modal(document.getElementById('editProductModal')).show();
    });
});
</script>

<?php require_once 'footer.php'; ?>
