<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in by verifying the session variable
if (!isset($_SESSION['id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
$order_id = $_GET['order_id'] ?? 'N/A';
$page_css = ["purchase-complete.css", "footer.css"];
// If logged in, you can safely include your header and rest of the page
include '../header.php';
?>

<div class="success-wrapper">
    <div class="success-container">
        <div class="checkmark">✔</div>
        <h1>Order Complete!</h1>
        <p>Your order has been placed successfully.</p>
        <div class="order-id">Order ID: #<?= htmlspecialchars($order_id) ?></div>
        <a href="../index.php" class="button">Go to Homepage</a>
    </div>
</div>



<?php include '../footer.php'; ?>