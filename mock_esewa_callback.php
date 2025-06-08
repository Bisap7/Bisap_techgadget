<?php
// This simulates the data eSewa would send back after successful payment

// You can hardcode or pass these via GET/POST for flexibility
$transaction_uuid = $_GET['transaction_uuid'] ?? 'ORD_123456789';
$total_amount = $_GET['total_amount'] ?? 10; // Should match your order amount
$product_code = $_GET['product_code'] ?? 'EPAYTEST';

// Prepare a fake signature exactly like your real one
$secretKey = "8gBm/:&EnhH.1/q";
$signedFields = "total_amount,transaction_uuid,product_code";
$signatureData = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
$signature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));

// Now simulate redirect to your success page with required GET params
header("Location: esewa_success.php?transaction_uuid=$transaction_uuid&total_amount=$total_amount&product_code=$product_code&signature=$signature");
exit;
