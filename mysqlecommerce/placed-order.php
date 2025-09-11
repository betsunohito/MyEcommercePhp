<?php
$page_css = ["placed-order.css"];
$page_js = ["user-dashboard.js"];
include 'header.php';

require_once __DIR__ . '/db.php';

$order_id = $_GET['order_id'] ?? null;
$user_id = $_SESSION['id'] ?? null;

if (!$order_id || !$user_id) {
    echo "Invalid access.";
    include 'footer.php';
    exit;
}

// Fetch order info (we only need to verify it's valid)
try {
    $stmt = $pdo->prepare("CALL get_order_summary(:order_id, :user_id)");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    echo "Error loading order.";
    include 'footer.php';
    exit;
}

if (!$order) {
    echo "Order not found.";
    include 'footer.php';
    exit;
}
?>
<div class="thank-you-container-wrapper">
  <div class="thank-you-container">
      <h1>🎉 Thank you for your purchase!</h1>
      <p>Your order has been placed successfully.</p>
      <p><strong>Order Number:</strong> <?= htmlspecialchars($order['order_id']) ?></p>
      <a href="shop.php" class="btn">← Continue Shopping</a>
  </div>
</div>



<?php include 'footer.php'; ?>
