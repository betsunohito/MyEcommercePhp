<?php
include '../db.php';
session_start();
$product_id = $_GET['id'] ?? 1;
$product_id = intval($product_id);

$admin_id = $_GET['sellerId'] ?? 0; // fallback to 0 if not logged in

$user_id = $_SESSION['id'] ?? 0; // fallback to 0 if not logged in

// Read product type from GET (or POST if you prefer)
$product_type = $_GET['type'] ?? null;
if ($product_type !== null) {
    $product_type = intval($product_type);
}

try {
    $stmt = $pdo->prepare("CALL product_show(:product_id, :admin_id, :product_type,:user_id)");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_type', $product_type, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

    $stmt->execute();

    // 1. Fetch product details
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Fetch images
    $stmt->nextRowset();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch review summary
    $stmt->nextRowset();
    $review_summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Fetch prices (filtered by type if passed)
    $stmt->nextRowset();
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Fetch type
    $stmt->nextRowset();
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt->closeCursor();

    if (!$product) {
        // If product not found, show error instead of header redirect
        echo "<div class='error-message'>Ürün bulunamadı. <a href='index.php'>Anasayfa</a></div>";
        exit;
    }

} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit;
}

$page_title = $product['product_name'];
?>


<!-- Product Details Section Begin -->
<section class="pd-product-details">
    <div class="container">
        <div class="row">
            <!-- LEFT: Image area (matches .half-column in CSS) -->
            <div class="half-column">
                <div class="pd-product__details__pic pd-product-pics">

                    <!-- Big image wrapper (arrows live INSIDE this box) -->
                    <div class="pd-product__details__pic__item">

                        <!-- Prev -->
                        <div class="pd-prev-btn" aria-label="Previous" role="button" tabindex="0"
                            onclick="prevPic(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path fill="none" stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </div>

                        <!-- Main image -->
                        <?php if (!empty($images) && isset($images[0])): ?>
                            <img data-imgbigurl="<?= '../../uploads/products/' . $product['product_link'] . '/' . $images[0]['image_filename'] ?>"
                                src="<?= '../../uploads/products/' . $product['product_link'] . '/' . $images[0]['image_filename'] ?>"
                                alt="Product Image">
                        <?php else: ?>
                            <img src="default-image.jpg" alt="No image available">
                        <?php endif; ?>

                        <!-- Next -->
                        <div class="pd-next-btn" aria-label="Next" role="button" tabindex="0" onclick="nextPic(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-6 h-6">
                                <path fill="none" stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                    </div>

                    <!-- Thumbnails -->
                    <div class="pd-product__details__pic__slider owl-carousel">
                        <?php foreach ($images as $img): ?>
                            <img data-imgbigurl="<?= '../../uploads/products/' . $product['product_link'] . '/' . $img['image_filename'] ?>"
                                src="<?= '../../uploads/products/' . $product['product_link'] . '/' . $img['image_filename'] ?>"
                                alt="Product Image" onclick="selectPic(this)">
                        <?php endforeach; ?>
                    </div>

                </div>

            </div>

            <!-- RIGHT: Details (matches .three-quarter-column in CSS) -->
            <div class="three-quarter-column">
                <div class="pd-product__details__text">
                    <h3>
                        <?php if (!empty($product['brand_name'])): ?>
                            <a href="/mysqlecommerce/product-grid.php?brand=<?= urlencode($product['brand_id']) ?>"
                                class="pd-brand-bold">
                                <?= htmlspecialchars($product['brand_name']) ?>
                            </a>
                        <?php endif; ?>
                        <?= isset($product['product_name']) ? htmlspecialchars($product['product_name']) : 'Vegetable’s Package' ?>
                    </h3>

                    <div class="pd-product__details__rating">
                        <svg width="0" height="0">
                            <defs>
                                <linearGradient id="halfGradient">
                                    <stop offset="50%" stop-color="#FFD700" />
                                    <stop offset="50%" stop-color="#ccc" />
                                </linearGradient>
                            </defs>
                        </svg>

                        <?php
                        $review_count = $review_summary['review_count'] ?? 0;
                        $avg_rating = floatval($review_summary['avg_rating'] ?? 0);
                        $rounded = round($avg_rating * 2) / 2;

                        for ($i = 1; $i <= 5; $i++) {
                            if ($rounded >= $i) {
                                $class = "pd-star full";
                            } elseif ($rounded == $i - 0.5) {
                                $class = "pd-star half";
                            } else {
                                $class = "pd-star";
                            }
                            echo '<svg class="' . $class . '" viewBox="0 0 24 24">
                      <path d="M12 2l3 7h7l-5.5 5 2.5 7-6-4-6 4 2.5-7L2 9h7z"/>
                    </svg>';
                        }
                        ?>
                        <span class="pd-review-count">(<?= $review_count ?> Yorum)</span>
                    </div>

                    <div class="pd-product__details__seller">
                        Sold by:
                        <?= isset($prices[0]) ? htmlspecialchars($prices[0]['admin_company_name']) : 'Unknown Seller'; ?>
                    </div>

                    <div class="pd-product__details__price">
                        <?php
                        if (!empty($prices)) {
                            $price = $prices[0]['product_price'];
                            echo (fmod($price, 1) == 0)
                                ? number_format($price, 0, ',', '') . ' ₺'
                                : number_format($price, 2, ',', '') . ' ₺';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>

                    <?php if (!empty($types)): ?>
                        <div class="pd-product__details__sizes">
                            <div class="pd-selected-size">Size:
                                <?= htmlspecialchars($prices[0]['type_name'] ?? '') ?>
                            </div>
                            <div class="pd-sizes-list">
                                <?php foreach ($types as $type): ?>
                                    <button onclick="refreshPreviewModal(<?= $product_id ?>, <?= $type['type_id'] ?>)"
                                        class="pd-size-btn <?= ($type['type_id'] == ($prices[0]['product_type'] ?? '')) ? 'selected' : '' ?>"
                                        data-size="<?= htmlspecialchars($type['type_name']) ?>">
                                        <?= htmlspecialchars($type['type_name']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="pd-product__details__quantity">
                        <div class="quantity">
                            <div class="pd-pro-qty">
                                <span class="dec qtybtn qty-toggle">-</span>
                                <input type="text" id="qty-<?= $product_id; ?>" value="1">
                                <span class="inc qtybtn qty-toggle">+</span>
                            </div>
                        </div>
                    </div>

                    <?php
                    $has_types = count($types) > 0;
                    $typesThereOrNot = $has_types ? $prices[0]['product_type'] : 0;
                    ?>

                    <button class="primary-btn" id="add-to-cart-<?= $product_id; ?>"
                        data-seller-id="<?= $prices[0]['admin_id']; ?>"
                        data-type-id="<?= $prices[0]['product_type']; ?>"
                        data-has-types="<?= $typesThereOrNot ? '1' : '0'; ?>" onclick="addToCart(<?= $product_id; ?>)">
                        ADD TO CART
                    </button>

                    <div class="pd-favorite pd-heart-icon fav-toggle" data-product-id="<?= $product_id ?>"
                        onclick="toggleFavorite(<?= $product_id ?>)">
                        <?php $icon = ($product['is_favorited'] == 1) ? 'close.svg' : 'star.svg'; ?>
                        <img class="pd-filtering" src="images/<?= $icon ?>" alt="Favorite">
                    </div>

                    <ul>
                        <li><b>Availability</b> <span>In Stock</span></li>
                        <li><b>Shipping</b> <span>01 day shipping. <samp>Free pickup today</samp></span></li>
                        <li><b>Weight</b> <span>0.5 kg</span></li>
                        <li><b>Share on</b>
                            <div class="share">
                                <a href="#"><i class="fa fa-facebook"></i></a>
                                <a href="#"><i class="fa fa-twitter"></i></a>
                                <a href="#"><i class="fa fa-instagram"></i></a>
                                <a href="#"><i class="fa fa-pinterest"></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Product Details Section End -->