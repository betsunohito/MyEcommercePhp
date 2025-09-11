<?php
$page_css = ["product-detail.css", "product-preview.css", "product-pre-view.css"];
$page_js = ["toast.js", "product-detail.js", "scripts.js", "product-grid.js", "product-pre-view.js"];
include 'header.php';
include 'tools/action/categorynav.php';

$product_id = $_GET['id'] ?? 1;
$product_id = intval($product_id);

$admin_id = $_GET['sellerId'] ?? 0; // fallback to 0 if not logged in
$user_id = $_SESSION['id'] ?? 0;
// Read product type from GET (or POST if you prefer)
$product_type = $_GET['type'] ?? null;
if ($product_type !== null) {
    $product_type = intval($product_type);
}

try {
    $stmt = $pdo->prepare("CALL product_show(:product_id, :admin_id, :product_type,:user_id)");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    if ($product_type === null) {
        $stmt->bindValue(':product_type', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':product_type', $product_type, PDO::PARAM_INT);
    }

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
$page_title = $product['brand_name'] . ' ' . $product['product_name'];

?>


<script>
    document.title = "<?php echo addslashes($page_title); ?> | My Shop";
</script>
<!-- Breadcrumb Section Begin -->
<section class="breadcrumb-section set-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <div class="breadcrumb__option">
                        <?php if (!empty($product['category_name'])): ?>
                            <a href="#"><?= htmlspecialchars($product['category_name']) ?></a>
                        <?php endif; ?>

                        <?php if (!empty($product['product_sub_category_name'])): ?>
                            <a href="#"><?= htmlspecialchars($product['product_sub_category_name']) ?></a>
                        <?php endif; ?>

                        <?php if (!empty($product['product_tertiary_category_name'])): ?>
                            <a href="#"><?= htmlspecialchars($product['product_tertiary_category_name']) ?></a>
                        <?php endif; ?>

                        <span>
                            <?= isset($product['product_name']) ? htmlspecialchars($product['product_name']) : 'Product Detail' ?>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<!-- Breadcrumb Section End -->
<!-- Product Details Section Begin -->
<section class="product-details spad">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6">

                <div class="product__details__pic product-pics">
                    <div class="previous-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path fill="none" stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </div>
                    <div class="product__details__pic__item">
                        <?php if (!empty($images) && isset($images[0])): ?>
                            <img data-imgbigurl="<?= '../uploads/products/' . $product['product_link'] . '/' . $images[0]['image_filename'] ?>"
                                src="<?= '../uploads/products/' . $product['product_link'] . '/' . $images[0]['image_filename'] ?>"
                                alt="Product Image" />
                        <?php else: ?>
                            <img src="default-image.jpg" alt="No image available" />
                        <?php endif; ?>

                    </div>
                    <div class="next-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path fill="none" stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>

                    <div class="product__details__pic__slider owl-carousel">
                        <?php foreach ($images as $img): ?>
                            <img data-imgbigurl="<?= '../uploads/products/' . $product['product_link'] . '/' . $img['image_filename'] ?>"
                                src="<?= '../uploads/products/' . $product['product_link'] . '/' . $img['image_filename'] ?>"
                                alt="Product Image" />
                        <?php endforeach; ?>
                    </div>


                </div>

            </div>
            <div class="col-lg-9 col-md-9">
                <div class="product__details__text">
                    <h3>
                        <?php if (!empty($product['brand_name'])): ?>
                            <a href="/mysqlecommerce/product-grid.php?brand_ids[]=<?= urlencode($product['brand_id']) ?>"
                                class="brand-bold">
                                <?= htmlspecialchars($product['brand_name']) ?>
                            </a><?php endif; ?>
                        <?= isset($product['product_name']) ? htmlspecialchars($product['product_name']) : 'Vegetable’s Package' ?>
                    </h3>


                    <div class="product__details__rating">
                        <!-- Only define the half-star gradient once -->
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
                                $class = "star full";
                            } elseif ($rounded == $i - 0.5) {
                                $class = "star half";
                            } else {
                                $class = "star";
                            }

                            echo '<svg class="' . $class . '" viewBox="0 0 24 24">
                <path d="M12 2l3 7h7l-5.5 5 2.5 7-6-4-6 4 2.5-7L2 9h7z"/>
              </svg>';
                        }
                        ?>
                        <span class="review-count">(<?= $review_count ?> Yorum)</span>
                    </div>

                    <div class="product__details__seller">
                        Sold by:
                        <?php echo isset($prices[0]) ? htmlspecialchars($prices[0]['admin_company_name']) : 'Unknown Seller'; ?>
                    </div>

                    <div class="product__details__price">
                        <?php
                        if (!empty($prices)) {
                            $price = $prices[0]['product_price'];           // discounted (active) price
                            $original = $prices[0]['product_discount_price'] ?? 0;  // original price if available
                        
                            // Format price
                            $formattedPrice = (fmod($price, 1) == 0)
                                ? number_format($price, 0, ',', '') . ' ₺'
                                : number_format($price, 2, ',', '') . ' ₺';

                            // Format original price
                            $formattedOriginal = ($original > $price)
                                ? ((fmod($original, 1) == 0)
                                    ? number_format($original, 0, ',', '') . ' ₺'
                                    : number_format($original, 2, ',', '') . ' ₺')
                                : null;

                            if ($formattedOriginal) {
                                echo "<span class='original-price' style='text-decoration:line-through;color:#888;margin-right:8px;'>$formattedOriginal</span>";
                            }
                            echo "<span class='discounted-price' style='color:#e63946;font-weight:bold;'>$formattedPrice</span>";
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </div>




                    <?php if (!empty($types)): ?>
                        <div class="product__details__sizes">
                            <div class="selected-size">Size: <?php echo htmlspecialchars($prices[0]['type_name'] ?? ''); ?>
                            </div>
                            <div class="sizes-list">
                                <?php foreach ($types as $type): ?>
                                    <a href="?id=<?php echo $product_id; ?>&type=<?php echo $type['type_id']; ?>"
                                        class="size-btn <?php echo ($type['type_id'] == ($prices[0]['product_type'] ?? '')) ? 'selected' : ''; ?>"
                                        data-size="<?php echo htmlspecialchars($type['type_name']); ?>">
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>


                    <div class="product__details__quantity">
                        <div class="quantity">
                            <div class="pro-qty">
                                <span class="dec qtybtn qty-toggle">-</span>
                                <input type="text" id="qty-<?php echo $product_id; ?>" value="1">
                                <span class="inc qtybtn qty-toggle">+</span>
                            </div>
                        </div>
                    </div>
                    <?php
                    $has_types = count($types) > 0; // or however you're checking
                    $typesThereOrNot = $has_types ? $prices[0]['product_type'] : 0; // default selected
                    ?>

                    <button class="primary-btn" id="add-to-cart-<?php echo $product_id; ?>"
                        data-seller-id="<?php echo $prices[0]['admin_id']; ?>"
                        data-type-id="<?php echo $prices[0]['product_type']; ?>"
                        data-has-types="<?php echo $typesThereOrNot ? '1' : '0'; ?>"
                        onclick="addToCart(<?php echo $product_id; ?>)">
                        ADD TO CART
                    </button>


                    <div class="favorite fav-toggle" data-product-id="<?= $product_id ?>"
                        onclick="toggleFavorite(<?= $product_id ?>)">
                        <?php if ($product['is_favorited']): ?>
                            <img class="icon_heart_alt filtering" src="images/cancel.svg" alt="Favorite">
                        <?php else: ?>
                            <img class="icon_heart_alt filtering" src="images/star.svg" alt="Favorite">
                        <?php endif; ?>
                    </div>





                    <?php
                    $couponStmt = $pdo->prepare("CALL coupon_show_for_product(:product_id)");
                    $couponStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                    $couponStmt->execute();
                    $matchedCoupons = $couponStmt->fetchAll(PDO::FETCH_ASSOC);
                    $couponStmt->closeCursor();
                    ?>
                    <ul>
                        <li><b>Availability</b> <span>In Stock</span></li>
                        <li><b>Shipping</b> <span>01 day shipping. <samp>Free pickup today</samp></span></li>
                        <li><b>Weight</b> <span>0.5 kg</span></li>
                        <li><?php if (!empty($matchedCoupons)): ?>
                                <div class="coupon-scroll-wrapper">
                                    <div class="coupon-list">
                                        <?php foreach ($matchedCoupons as $coupon): ?>
                                            <div class="coupon-card">
                                                <div class="coupon-left">
                                                    <div class="coupon-amount">
                                                        ₺<?= number_format($coupon['discount_amount'], 0) ?></div>
                                                    <button class="coupon-button" data-coupon-id="<?= $coupon['coupon_id'] ?>">
                                                        Claim
                                                    </button>
                                                </div>
                                                <div class="coupon-details">
                                                    <?php if (!empty($coupon['min_limit'])): ?>
                                                        <div><span class="dot">•</span>Alt Limit
                                                            <strong>₺<?= number_format($coupon['min_limit'], 0) ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div><span class="dot">•</span>Ending Date
                                                        <strong><?= $coupon['end_date'] ?? '∞' ?></strong>
                                                    </div>
                                                    <a class="coupon-link" href="product-grid.php?special=with_coupon">Coupon
                                                        Products ›</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>



                        </li>
                    </ul>



                </div>
            </div>

            <div class="seller-section">
                <h4 class="seller-title">Another Seller for this product</h4>

                <?php foreach (array_slice($prices, 1) as $price): ?>
                    <a href="product-detail.php?id=<?= urlencode($product_id) ?>&sellerId=<?= urlencode($price['admin_id']) ?>"
                        class="seller-card" style="text-decoration: none; color: inherit;">
                        <div class="seller-info">
                            <div class="seller-name">
                                <?= htmlspecialchars($price['admin_company_name'] ?? 'Unknown Seller') ?>
                                <span style="color: #4caf50;">&#10003;</span>
                                <span style="color:#1565c0; font-weight: 600;">
                                    <?= number_format((float) ($price['avg_rating'] ?? 0), 1) ?>
                                </span>
                            </div>
                            <div class="seller-status">
                                Tahmini 3 Temmuz Perşembe kapında
                            </div>
                        </div>
                        <div class="price-tag">
                            <?= number_format((float) $price['product_price'], 2, ',', '.') ?> TL
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>





            <div class="col-lg-12">
                <div class="product__details__tab">
                    <div id="product-data" data-product-id="<?= (int) $_GET['id'] ?>"></div> <!-- for JS -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab"
                                aria-selected="true">Description</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab"
                                aria-selected="false">Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab"
                                aria-selected="false">Reviews
                                <span>(<?= $review_count ?>)</span></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tabs-1" role="tabpanel">
                            <div class="product__details__tab__desc">
                                <h6>Products Infomation</h6>
                                <p>
                                    <?php if (!empty($product['product_desc'])): ?>
                                        <?= nl2br(htmlspecialchars($product['product_desc'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="tab-pane" id="tabs-2" role="tabpanel">
                            <div class="product__details__tab__desc">
                                <h6>Stok kodu: <?php echo ($product['product_id']) ?></h6>
                            </div>
                        </div>

                        <div class="tab-pane" id="tabs-3" role="tabpanel">
                            <div class="product__details__tab__desc">
                                <h6>Kullanıcı Yorumları</h6>
                                <div id="reviews-list">
                                    <p>Yorumlar yüklenmedi. “Reviews” sekmesine tıklayın.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </div>
    </div>
</section>
<!-- Product Details Section End -->
<!-- Related Product Section Begin -->
<section class="related-product">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title related__product__title">
                    <h2>Related Product</h2>
                </div>
            </div>
        </div>
        <main class="pg-main">
            <div class="parent-container">
                <div class="product-preview">
                    <div class="previous-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path fill="none" stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </div>
                    <?php

                    echo '<div class="pg-container">';

                    try {
                        $user_id = $_SESSION['id'] ?? 0;
                        $product_id = $product['product_id']; // the current product
                    
                        $stmt = $pdo->prepare("CALL products_get_related(:product_id, :user_id)");
                        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
                        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt->execute();

                        // 1. PRODUCTS
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // 2. IMAGES
                        $stmt->nextRowset();
                        $images_by_product = [];
                        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
                            $images_by_product[$img['product_id']][] = $img;
                        }

                        // Output
                        if (count($products) === 0) {
                            echo '<div class="no-favorites-message">Ürün yok. ' . htmlspecialchars($product['product_name']) . '</div>';
                        } else {
                            foreach ($products as $product) {
                                $card_id = $product['product_id'];
                                $card_link = $product['product_link'];
                                $card_name = $product['product_name'];
                                $card_price = $product['product_price'];
                                $card_brand = $product['brand_name'];
                                $card_desc = $product['product_desc'] ?? '';
                                $fav_icon = ($product['favored'] == 0) ? 'star.svg' : 'cancel.svg';
                                $seller_id = $product['seller_id'] ?? 0;
                                $card_original_price = $product['original_price'] ?? null;
                                $product_images = $images_by_product[$card_id] ?? [];
                                $product_rating = $product['average_rating'] ?? 0;
                                $product_review_count = $product['review_count'] ?? 0;

                                include 'tools/product-card.php';
                            }
                        }

                    } catch (PDOException $e) {
                        echo "Hata: " . $e->getMessage();
                    }

                    echo '</div>';
                    ?>



                    <div class="next-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path fill="none" stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </div>
            </div>
        </main>

    </div>
</section>
<!-- Related Product Section End -->


<?php
include 'footer.php';
?>