<?php
require_once 'config.php';  // DB + helpers
session_start();

// Debugging (optional): Log incoming request data for troubleshooting
// file_put_contents('esewa_debug.log', "GET: ".print_r($_GET, true)."\nPOST: ".print_r($_POST, true)."\n", FILE_APPEND);

// Detect mock mode via GET parameter ?mock=1 (for testing locally)
$mock_mode = isset($_GET['mock']) && $_GET['mock'] == '1';

// Get eSewa payment data from POST or GET (eSewa usually sends via GET redirect)
$oid = $_POST['oid'] ?? $_GET['oid'] ?? '';
$amt = $_POST['amt'] ?? $_GET['amt'] ?? '';
$refId = $_POST['refId'] ?? $_GET['refId'] ?? '';

// In mock mode, override the refId for testing if not provided
if ($mock_mode && empty($refId)) {
    $refId = 'MOCK_REF123';
}

// Basic validation of payment details
if (!$oid || !$amt || !$refId) {
    $_SESSION['message'] = "Invalid payment details received.";
    $_SESSION['message_type'] = "danger";
    header("Location: payment_failed.php");
    exit;
}

// Your actual eSewa merchant code here
$merchant_code = 'YOUR_MERCHANT_CODE';

if ($mock_mode) {
    // Simulate payment verification success without calling eSewa
    $verification_success = true;
} else {
    // Prepare data for eSewa verification API
    $post_data = http_build_query([
        'amt' => $amt,
        'pid' => $oid,
        'scd' => $merchant_code,
        'rid' => $refId,
    ]);

    // Call eSewa verification API (sandbox environment)
    $ch = curl_init("https://uat.esewa.com.np/epay/transrec");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if verification succeeded
    $verification_success = ($httpcode == 200 && trim($response) === 'Success');
}

if ($verification_success) {
    // Fetch order from DB where status is Pending
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND total_amount = ? AND status = 'Pending'");
    $stmt->execute([$oid, $amt]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['message'] = "Order not found or already processed.";
        $_SESSION['message_type'] = "danger";
        header("Location: payment_failed.php");
        exit;
    }

    // Update order status to Completed
    $updateStmt = $pdo->prepare("UPDATE orders SET status = 'Completed', payment_ref = ?, paid_at = NOW() WHERE id = ?");
    $updateStmt->execute([$refId, $oid]);

    $_SESSION['message'] = "Payment successful! Your order #$oid has been confirmed.";
    $_SESSION['message_type'] = "success";

    header("Location: order_success.php?order_id=$oid");
    exit;
} else {
    // Verification failed
    $_SESSION['message'] = "Payment verification failed with eSewa.";
    $_SESSION['message_type'] = "danger";
    header("Location: payment_failed.php");
    exit;
}
