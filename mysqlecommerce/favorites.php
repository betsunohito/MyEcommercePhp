<?php
$page_css = ["favorites.css", "product-preview.css"];
$page_js = ["toast.js","scripts.js"];
include 'header.php';
include 'tools/action/categorynav.php';
?>

<div class="favorites-wrapper">
    <div class="favorites-container">
        <h2 class="favorites-title">Favorilerim</h2>
        <div class="favorites-scroll-area">
            <?php
            include 'db.php';

            if (isset($_SESSION['id'])) {
                try {
                    $stmt = $pdo->prepare('CALL favorites_show(:user_id)');
                    $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
                    $stmt->execute();

                    // 1. Fetch products (first result set)
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // 2. Move to next result set for images
                    $images_by_product = [];
                    if ($stmt->nextRowset()) {
                        $images_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($images_raw as $img) {
                            $images_by_product[$img['product_id']][] = $img;
                        }
                    }

                    // 3. Fetch review stats
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
                            $card_price = $product['lowest_price'] ?? 0;
                            $card_original_price = $product['original_price'] ?? 0;
                            $card_desc = $product['product_desc'] ?? '';
                            $product_images = $images_by_product[$card_id] ?? [];
                            $fav_icon = ($product['is_favorite'] == 0) ? 'star.svg' : 'cancel.svg';
                            $card_brand = $product['brand_name'];
                            $seller_id = $product['admin_id'] ?? 0;
                            // Review info (set default if not found)
                            $in_sort_by_most_selled = isset($_GET['in_sort_by_most_selled']) ? (bool) $_GET['in_sort_by_most_selled'] : null;
                            $in_page_number = 1;
                            $product_rating = $review_stats[$card_id]['average_rating'] ?? 0;
                            $product_review_count = $review_stats[$card_id]['review_count'] ?? 0;
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




<?php
include 'footer.php';
?>