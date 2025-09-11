<?php
require_once __DIR__ . '/../../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['id'] ?? 0;

$coupon = null;
if ($user_id > 0) {
    try {
        $stmt = $pdo->prepare("CALL coupon_active_or_not(:uid)");
        $stmt->execute([':uid' => $user_id]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        error_log("❌ Coupon check failed: " . $e->getMessage());
    }
}
?>

<?php if (!empty($coupon)): ?>
    <div class="summary-line applied-coupon" id="applied-coupon-line">
        <span>
            (<?= htmlspecialchars($coupon['coupon_code']) ?>)
        </span>
        <span class="discount-amount">
            -₺<?= number_format($coupon['discount_amount'], 2) ?>
            <button type="button" id="remove-coupon-btn" title="Remove coupon">✕</button>
        </span>
    </div>
<?php endif; ?>