<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Login required.']);
    exit;
}

$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;

if ($review_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid review ID.']);
    exit;
}

// Check if the user owns the review
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
$stmt->execute([$review_id, $_SESSION['user_id']]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    echo json_encode(['status' => 'error', 'message' => 'You are not allowed to delete this review.']);
    exit;
}

// Delete the review
$stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
$success = $stmt->execute([$review_id]);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Review deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Could not delete review.']);
}
