<?php
include "db.php";
$user_id = $_SESSION['id'];
$stmt = $pdo->prepare("CALL reviews_get_pending(:user_id)");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$pendingReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="fullscreen-review">
    <!-- Tabs -->
    <div class="review-tabs">
        <a class="tab active" href="user-dashboard.php?page=reviews-make">🕒 Değerlendirme Bekleyenler</a>
        <a class="tab" href="user-dashboard.php?page=reviews-show">✅ Değerlendirdiklerim</a>
    </div>

    <!-- Tab: Pending -->
    <div class="review-content" id="pending">
        <?php foreach ($pendingReviews as $review): ?>
            <div class="review-container">
                <img src="/uploads/products/<?= htmlspecialchars($review['product_link']) ?>/<?= htmlspecialchars($review['image_filename']) ?>"
                    alt="<?= htmlspecialchars($review['product_full_name']) ?>" class="product-image" />

                <div class="review-right">
                        <?= htmlspecialchars($review['product_full_name']) ?></h2>
                    <p class="product-subtitle">Sipariş Tarihi: <?= date("d F Y", strtotime($review['order_date'])) ?></p>

                    <label for="review-text-<?= $review['order_detail_id'] ?>" class="review-label">Ürün hakkındaki
                        düşünceleriniz (İsteğe Bağlı)</label>
                    <textarea id="review-text-<?= $review['order_detail_id'] ?>" class="review-text"
                        placeholder="Ürün hakkında yorumunuzu yazın..."></textarea>

                    <div class="star-rating" data-id="<?= $review['order_detail_id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-value="<?= $i ?>">☆</span>
                        <?php endfor; ?>
                    </div>

                    <button class="submit-review-btn" data-id="<?= $review['order_detail_id'] ?>">Gönder</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>