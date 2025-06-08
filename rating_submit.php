<?php
require_once 'config.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to submit a review.']);
    exit;
}

// Validate and sanitize input
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review = trim($_POST['review'] ?? '');

if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($review)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Insert the review
$stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
$success = $stmt->execute([$user_id, $product_id, $rating, $review]);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Review submitted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit review. Please try again.']);
}
