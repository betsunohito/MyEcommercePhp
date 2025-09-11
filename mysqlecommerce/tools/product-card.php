<div class="pg-card">
    <div class="pg-badge">
        <?php if (!empty($fav_icon)): ?>
            <div class="favorite fav-toggle" data-product-id="<?= $card_id ?>" onclick="toggleFavorite(<?= $card_id ?>)">
                <img class="filtering" src="images/<?= htmlspecialchars($fav_icon) ?>" alt="Favorite">
            </div>
        <?php endif; ?>

    </div>
    <button class="preview-btn" onclick="openPreviewModal(<?= $card_id ?>,<?= $seller_id ?>)">Preview</button>
    <div class="pg-image-container" data-product-id="<?= $card_id ?>">
        <?php foreach (array_slice($product_images, 0, 4) as $i => $img): ?>
            <img src="../uploads/products/<?= htmlspecialchars($card_link) ?>/<?= htmlspecialchars($img['image_filename']) ?>"
                alt="<?= htmlspecialchars($card_name) ?>" class="hover-image <?= $i === 0 ? 'active' : '' ?>"
                data-index="<?= $i ?>" />
        <?php endforeach; ?>

        <div class="pg-image-indicators">
            <?php foreach (array_slice($product_images, 0, 4) as $i => $img): ?>
                <div class="dot <?= $i === 0 ? 'active' : '' ?>" data-dot-index="<?= $i ?>"></div>
            <?php endforeach; ?>


        </div>

    </div>

    <?php
    $short_name = mb_strimwidth($card_name, 0, 45, '...');
    ?>

    <div class="pg-card-body">

        <a href="product-detail.php?id=<?= $card_id ?>&sellerId=<?= $seller_id ?>" class="no-underline">
            <h3 class="pg-card-title" title="<?= htmlspecialchars($card_name) ?>">
                <span class="pg-brand-inline"><?= htmlspecialchars($card_brand) ?></span>
                <?= htmlspecialchars($short_name) ?>
            </h3>
        </a>

        <div class="pg-stars" data-rating="<?= $product_rating ?>">
            <span class="star" data-index="1">★</span>
            <span class="star" data-index="2">★</span>
            <span class="star" data-index="3">★</span>
            <span class="star" data-index="4">★</span>
            <span class="star" data-index="5">★</span>
            <span class="review-count">(<?= $product_review_count ?>)</span>
        </div>




        <p class="pg-desc"><!--<?= htmlspecialchars($card_desc) ?>--></p>
        <div class="pg-price">
            <?php if (!empty($card_original_price) && $card_original_price > $card_price): ?>
                <span class="pg-discounted">₺<?= number_format($card_price, 2) ?></span>
                <span class="pg-original">₺<?= number_format($card_original_price, 2) ?></span>
            <?php else: ?>
                <span class="pg-regular">₺<?= number_format($card_price, 2) ?></span>
            <?php endif; ?>
        </div>



    </div>



</div>