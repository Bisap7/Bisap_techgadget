<?php
require_once 'config.php'; // Include the config file

// Check if the user is an admin
if (!isAdmin()) {
    $_SESSION['message'] = 'Access denied. Admins only.';
    $_SESSION['message_type'] = 'danger';
    redirect('index.php'); // Redirect to homepage if not admin
}

// Fetch all users from the database
$stmt = $pdo->prepare("SELECT id, username, email, role FROM users ORDER BY username ASC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'header.php'; // Include the header file
?>

<div class="container mt-4">
    <h2>All Users</h2>

    <!-- Displaying any session messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="badge bg-<?php
                        // Display role with appropriate color
                        switch ($user['role']) {
                            case 'admin':
                                echo 'danger';
                                break;
                            case 'user':
                                echo 'primary';
                                break;
                            default:
                                echo 'secondary';
                        }
                        ?>"><?= htmlspecialchars($user['role']) ?></span>
                    </td>
                    <td>
                        <!-- Form to change user role -->
                        <form action="admin_users.php" method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </form>

                        <!-- Delete User Button (optional) -->
                        <form action="admin_users.php" method="POST" class="d-inline">
                            <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = sanitize($_POST['role']); // Sanitize the input for role

    // Update the user role in the database
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $user_id]);

    $_SESSION['message'] = 'User role updated successfully!';
    $_SESSION['message_type'] = 'success';

    // Refresh the page to reflect the updated role
    redirect('admin_users.php');
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_user_id = (int)$_POST['delete_user_id'];

    // Check if the user is not trying to delete themselves (admin can't delete themselves)
    if ($delete_user_id === $_SESSION['user_id']) {
        $_SESSION['message'] = 'You cannot delete yourself!';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Delete the user from the database
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_user_id]);

        $_SESSION['message'] = 'User deleted successfully!';
        $_SESSION['message_type'] = 'success';
    }

    // Refresh the page after deletion
    redirect('admin_users.php');
}

require_once 'footer.php'; // Include footer
?>
