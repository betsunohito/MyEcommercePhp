<?php
session_start();

// Check if admin is logged in by verifying the session variable
if (!isset($_SESSION['admin_id'])) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
$page_css = ["coupon.css"];
$page_js = ["coupon.js"];
// If logged in, you can safely include your header and rest of the page
include 'header.php';

$admin_id = $_SESSION['admin_id'];

include 'db.php';

?>

<!-- coupon-form.html -->
<div class="coupon-container">
    <div class="coupon-card">
        <div class="coupon-header">Create New Coupon</div>
        <div class="coupon-body">

            <div class="coupon-group">
                <label for="coupon-code">Coupon Code</label>
                <input type="text" id="coupon-code" class="coupon-input" placeholder="E.g., SUMMER2025">
            </div>

            <div class="coupon-group">
                <label for="coupon-discount">Discount Amount (₺)</label>
                <input type="number" id="coupon-discount" class="coupon-input" placeholder="E.g., 50" step="0.01">
            </div>

            <div class="coupon-group">
                <label for="coupon-expiration">Expires in (days)</label>
                <input type="number" id="coupon-expiration" class="coupon-input" placeholder="E.g., 2" min="1"
                    value="2">
            </div>

            <!-- Category -->
            <div class="coupon-group autocomplete-wrapper">
                <label for="coupon-category">Category (optional)</label>
                <input type="text" id="coupon-category" class="category-main-input coupon-input category1-input"
                    placeholder="Start typing category..." autocomplete="off">
                <input type="hidden" name="category_id">
                <div class="category-main-suggestions suggestions"></div>
            </div>

            <!-- Subcategory -->
            <div class="coupon-group autocomplete-wrapper">
                <label for="coupon-subcategory">Subcategory (optional)</label>
                <input type="text" id="coupon-subcategory" class="coupon-input category-sub-input"
                    placeholder="Start typing subcategory..." autocomplete="off">
                <input type="hidden" name="subcategory_id">
                <div class="suggestions category-sub-suggestions"></div>
            </div>

            <!-- Tertiary Category -->
            <div class="coupon-group autocomplete-wrapper">
                <label for="coupon-tertiary">Tertiary Category (optional)</label>
                <input type="text" id="coupon-tertiary" class="coupon-input category-ter-input"
                    placeholder="Start typing tertiary category..." autocomplete="off">
                <input type="hidden" name="tertiary_id">
                <div class="suggestions category-ter-suggestions"></div>
            </div>


            <div class="coupon-group">
                <label for="coupon-product">Product ID (optional)</label>
                <input type="number" id="coupon-product" class="coupon-input" placeholder="e.g., 1234">
            </div>

            <div class="coupon-group">
                <label for="coupon-user">User ID (optional)</label>
                <input type="number" id="coupon-user" class="coupon-input" placeholder="e.g., 567">
            </div>


            <div id="coupon-target" class="coupon-group"></div>

            <button class="coupon-submit" id="create-coupon-btn">Create Coupon</button>

        </div>
    </div>
</div>

<?php
// Fetch open coupons from stored procedure
$openCoupons = [];
try {
    require_once __DIR__ . '/db.php'; // make sure path is correct
    $stmt = $pdo->prepare("CALL coupon_show_opens()");
    $stmt->execute();
    $openCoupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    error_log("❌ Error fetching open coupons: " . $e->getMessage());
}
?>

<div class="coupon-list-section">
    <h2 class="coupon-list-title">🎁 Open Coupons</h2>
    <div class="coupon-list">
        <?php if (!empty($openCoupons)): ?>
            <?php foreach ($openCoupons as $coupon): ?>
                <div class="coupon-card-list">
                    <div class="coupon-header">
                        <span class="coupon-code"><?php echo htmlspecialchars($coupon['coupon_code']); ?></span>
                        <span class="coupon-amount">₺<?php echo number_format($coupon['discount_amount'], 2); ?> OFF</span>
                    </div>
                    <div class="coupon-details">
                        <?php if (!empty($coupon['target_type']) && !empty($coupon['target_name'])): ?>
                            <p><strong><?php echo ucfirst($coupon['target_type']); ?>:</strong>
                                <?php echo htmlspecialchars($coupon['target_name']); ?></p>
                        <?php endif; ?>
                        <p><strong>Valid Until:</strong>
                            <?php echo $coupon['end_date'] ?? '∞'; ?></p>
                        <p><strong>Assigned To:</strong>
                            <?php echo !empty($coupon['user_id']) ? 'User #' . $coupon['user_id'] : 'All Users'; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No open coupons available.</p>
        <?php endif; ?>
    </div>
</div>





<?php include 'footer.php'; ?>