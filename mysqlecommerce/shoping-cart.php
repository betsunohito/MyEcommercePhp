<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id'])) {
    header("Location: Index.php");
    exit;
}
$page_css = ["shoping-cart.css", "product-preview.css", "product-pre-view.css"];
$page_js = ["toast.js", "product-grid.js", "shopping-cart.js", "scripts.js"];
include 'header.php';
include 'db.php';
?>

<div class="shopping-cart-wrapper">
    <div class="cart-wrapper">
        <div class="cart-container">

            <?php
            $user_id = $_SESSION['id'] ?? 0;
            $userCoupons = [];

            if ($user_id > 0) {
                try {
                    $stmt = $pdo->prepare("CALL coupons_get_applicable_user(:user_id)");
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $userCoupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                }
            } else {
                echo '<div class="no-favorites-message">User not logged in.</div>';
            }
            ?>

            <?php if (!empty($userCoupons)): ?>
                <section class="my-coupons-section">
                    <div class="my-coupons-header">🎟️ My Coupons</div>
                    <div class="my-coupons-list">
                        <div class="coupon-bar">
                            <?php foreach ($userCoupons as $coupon): ?>
                                <div class="my-coupon-card apply-coupon-btn <?= ($coupon['is_active'] ?? 0) ? 'selected-coupon' : '' ?>"
                                    data-coupon-id="<?= $coupon['coupon_id'] ?>">
                                    <span class="my-coupon-code"><?= htmlspecialchars($coupon['coupon_code']) ?></span>
                                    <span class="my-coupon-discount">₺<?= number_format($coupon['discount_amount'], 2) ?>
                                        OFF</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <div id="cart-products-container">
                <?php include 'tools/action/shoppingcart_product_fill.php'; ?>
            </div>

            <section class="favorites-section">
                <div class="favorites-header cart-seller-header">Favorites</div>
                <div class="favorites-carousel-wrapper">
                    <div class="favorites-carousel">
                        <div class="favorites-items" id="favorites-items">
                            <?php
                            if (isset($_SESSION['id'])) {
                                try {
                                    $stmt = $pdo->prepare('CALL favorites_show(:user_id)');
                                    $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
                                    $stmt->execute();

                                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    $images_by_product = [];
                                    if ($stmt->nextRowset()) {
                                        $images_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($images_raw as $img) {
                                            $images_by_product[$img['product_id']][] = $img;
                                        }
                                    }

                                    $review_stats = [];
                                    if ($stmt->nextRowset()) {
                                        $ratings_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($ratings_raw as $r) {
                                            $review_stats[$r['product_id']] = [
                                                'review_count' => $r['review_count'],
                                                'average_rating' => $r['average_rating'],
                                            ];
                                        }
                                    }

                                    if (count($products) === 0) {
                                        echo '<div class="no-favorites-message">You have no favorite products yet.</div>';
                                    } else {
                                        foreach ($products as $product) {
                                            $card_id = $product['product_id'];
                                            $card_link = $product['product_link'];
                                            $card_name = $product['product_name'];
                                            $card_brand = $product['brand_name'];
                                            $card_price = $product['lowest_price'];
                                            $seller_id = $product['admin_id'];
                                            $card_desc = $product['product_desc'] ?? '';
                                            $card_original_price = $product['original_price'] ?? null;
                                            $product_rating = $review_stats[$card_id]['average_rating'] ?? 0;
                                            $product_review_count = $review_stats[$card_id]['review_count'] ?? 0;
                                            $product_images = $images_by_product[$card_id] ?? [];
                                            $fav_icon = ($product['is_favorite'] == 0) ? 'star.svg' : 'cancel.svg';

                                            include 'tools/product-card.php';
                                        }
                                    }

                                } catch (PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                            } else {
                                echo '<div class="no-favorites-message">User not logged in.</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php if (count($products) != 0) {
                    echo '
                    <button class="carousel-btn carousel-btn-prev" onclick="scrollFavorites(-1)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke="currentColor"
                            fill="none">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button class="carousel-btn carousel-btn-next" onclick="scrollFavorites(1)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" viewBox="0 0 24 24" stroke="currentColor"
                            fill="none">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>';
                } ?>
            </section>
        </div>

        <aside class="cart-summary-box">
            <?php
            $user_id = $_SESSION['id'] ?? 0;

            $stmt = $pdo->prepare("CALL shoppingcart_user_shipping_total(:uid)");
            $stmt->execute([':uid' => $user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $subtotal = (float) ($row['subtotal'] ?? 0);
            $shipping_total = (float) ($row['shipping_total'] ?? 0);
            $coupon_discount = (float) ($row['coupon_discount'] ?? 0);
            $grand_total = (float) ($row['grand_total'] ?? 0);

            $tl = fn($n) => '₺' . number_format($n, 2);
            ?>

            <h2>Your Cart</h2>

            <div class="summary-line">
                <span>Subtotal</span>
                <span><?= $tl($subtotal) ?></span>
            </div>

            <div class="summary-line shipping-line">
                <span>Shipping</span>
                <span><?= $tl($shipping_total) ?></span>
            </div>

            <div class="coupon-applied-box" id="coupon-applied-box">
                <?php include 'tools/action/shoppingcart-coupon.php'; ?>
            </div>


            <div class="coupon-apply-box">
                <label for="coupon-code">Have a Coupon?</label>
                <input type="text" id="coupon-code" name="coupon_code" placeholder="Enter coupon code">
                <button type="button" class="apply-coupon-btn-by-click" id="apply-coupon-btn-by-click">Apply</button>
                <div id="coupon-feedback" class="coupon-feedback"></div>
            </div>

            <div class="summary-line summary-total">
                <span>Total</span>
                <span class="cart-total"><?= $tl($grand_total) ?></span>
            </div>

            <a href="/Mysqlecommerce/checkout.php" class="cart-checkout-button">
                Proceed to Checkout
            </a>
        </aside>



    </div>
</div>

<script>
    function scrollFavorites(direction) {
        const container = document.getElementById('favorites-items');
        const scrollAmount = 300;
        container.scrollLeft += direction * scrollAmount;
    }
</script>

<?php include 'footer.php'; ?>