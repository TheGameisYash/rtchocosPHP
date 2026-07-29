<?php
// includes/payment.php — Pluggable Payment Gateway
// Replace the body of processPayment() with your Razorpay / PayU / Cashfree SDK integration.

/**
 * Process a payment for an order.
 *
 * @param int    $orderId       The order ID from the `orders` table.
 * @param float  $amount        Total amount in INR.
 * @param string $customerEmail Customer's email for receipt.
 * @return array ['success' => bool, 'payment_id' => string|null, 'error' => string|null]
 *
 * --- RAZORPAY INTEGRATION GUIDE ---
 * 1. Install SDK: composer require razorpay/razorpay
 * 2. Set env vars: RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET
 * 3. Create Razorpay Order via API, return order_id to frontend JS
 * 4. On frontend success callback, verify signature server-side
 * 5. Update order payment_status = 'paid' and store payment_id
 */
function processPayment($orderId, $amount, $customerEmail) {
    // --- PLACEHOLDER: Marks order as paid for development/testing ---
    // Replace this block with your payment gateway SDK calls.
    
    $placeholderPaymentId = 'DEV_' . strtoupper(bin2hex(random_bytes(8)));
    
    try {
        require_once __DIR__ . '/db.php';
        $pdo = get_db();
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'paid', payment_id = ?, order_status = 'confirmed' WHERE id = ?");
        $stmt->execute([$placeholderPaymentId, $orderId]);
        
        return [
            'success' => true,
            'payment_id' => $placeholderPaymentId,
            'error' => null
        ];
    } catch (Exception $e) {
        error_log("Payment processing failed for order #$orderId: " . $e->getMessage());
        return [
            'success' => false,
            'payment_id' => null,
            'error' => 'Payment processing failed. Please try again.'
        ];
    }
}
?>
