<?php
require_once __DIR__ . '/../../db.php';  // reliable absolute path

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    // User not logged in message stored in $results variable (or handle accordingly)
    $results = [];
} else {
    $user_id = $_SESSION['id'];

    try {
        $stmt = $pdo->prepare("CALL shoppingcart_items(:user_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sellers = [];

        foreach ($results as $item) {
            $sellerName = $item['admin_company_name'] ?? 'Unknown Seller';
            if (!isset($sellers[$sellerName])) {
                $sellers[$sellerName] = [];
            }
            $sellers[$sellerName][] = $item;
        }
        $stmt->closeCursor();
    } catch (PDOException $e) {
        // On error, you may want to log or handle this gracefully
        $results = [];
    }
}
?>


<?php if (!empty($sellers)): ?>
    <?php foreach ($sellers as $sellerName => $items): ?>
        <section class="cart-seller">
            <div class="cart-seller-header">Seller: <?php echo htmlspecialchars($sellerName); ?></div>

            <?php foreach ($items as $item): ?>
                <div class="cart-item-block">
                    <div class="cart-item-info">
                        <div class="cart-item-image">
                            <?php if (!empty($item['image_filename'])): ?>
                                <img src="/uploads/products/<?php echo htmlspecialchars($item['product_link']); ?>/<?php echo htmlspecialchars($item['image_filename']); ?>"
                                    alt="<?php echo htmlspecialchars($item['product_name']); ?>" width="80" height="80" />
                            <?php else: ?>
                                <div style="width:80px; height:80px; background:#ccc;"></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="cart-item-name">
                                <?php echo htmlspecialchars($item['brand_name']); ?>
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </div>
                            <div class="cart-quantity-control">
                                <?php
                                echo "
<div class='cart-item' data-cart-id='" . $item['cart_id'] . "'>
    <div class='quantity'>
        <div class='cart-qty-control'>
            <span class='dec qty-btn'>
                <img src='images/minus.svg' alt='Decrease'>
            </span>
            <input type='text' value='" . $item['quantity'] . "'>
            <span class='inc qty-btn'>
                <img src='images/plus.svg' alt='Increase'>
            </span>
        </div>
    </div>
</div>";
                                ?>



                                <span class="trash" data-product-id="<?= $item['product_id'] ?>"
                                    data-type-id="<?= $item['type_id'] ?>">
                                    <img src="images/trash.svg" alt="Trash">
                                </span>

                                <?php if (!empty($item['type_name'])): ?>
                                    <span class="product-type-label">
                                        Size: <?= htmlspecialchars($item['type_name'] ?? 'Type') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item['brand_name'])): ?>

                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="cart-item-price" data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>">
                        ₺<?php echo number_format($item['total_price'], 2, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>