<?php
$page_css = ["style.css", "product-preview.css", "slider.css", "product-pre-view.css"];
$page_js = ["toast.js", "scripts.js", "product-grid.js", "product-pre-view.js"];
include 'header.php';
include 'tools/action/categorynav.php';
?>

<div class="wrapper">

  <div class="most-carted-section">
    <div class="circle-product-list">
      <a class="circle-item" href="/mysqlecommerce/product-grid.php?special=discounted">
        <div class="circle-image" style="background-image: url('images/indexspic/1.jpg');"></div>
        <p class="circle-label">Discounted Products</p>
      </a>
      <a class="circle-item" href="/mysqlecommerce/product-grid.php?special=new">
        <div class="circle-image" style="background-image: url('images/indexspic/2.jpg');"></div>
        <p class="circle-label">New Products</p>
      </a>
      <a class="circle-item" href="/mysqlecommerce/product-grid.php?special=most_carted">
        <div class="circle-image" style="background-image: url('images/indexspic/3.jpg');"></div>
        <p class="circle-label">Most Carted</p>
      </a>
      <a class="circle-item" href="/mysqlecommerce/product-grid.php?special=most_selled">
        <div class="circle-image" style="background-image: url('images/indexspic/4.jpg');"></div>
        <p class="circle-label">Most Selled</p>
      </a>
      <a class="circle-item" href="/mysqlecommerce/product-grid.php?special=with_coupon">
        <div class="circle-image" style="background-image: url('images/indexspic/5.jpg');"></div>
        <p class="circle-label">With Coupon</p>
      </a>
    </div>
  </div>









  <div class="psc-container">
    <!-- LEFT: Image Slider -->
    <div class="psc-left-slider" id="leftSlider">
      <button class="psc-arrow psc-prev previous-btn" onclick="prevSlide()"><svg xmlns="http://www.w3.org/2000/svg"
          fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg></button>
      <button class="psc-arrow psc-next next-btn" onclick="nextSlide()"><svg xmlns="http://www.w3.org/2000/svg"
          fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
        </svg></button>

      <div class="psc-slides">
        <a class="psc-slide" href="product-grid.php?category=1"
          style="background-image:url('/uploads/stockpics/womans.jpg')" aria-label="Women's">
        </a>

        <a class="psc-slide" href="product-grid.php?category=8&tertiary_category_ids[]=11126" style="background-image:url('/uploads/stockpics/phone-store.jpg')"
          aria-label="Phones">
        </a>

        <a class="psc-slide" href="product-grid.php?special=new"
          style="background-image:url('/uploads/stockpics/new-product.jpg')" aria-label="New products">
        </a>

      </div>

      <div class="psc-dots" id="sliderDots"></div>
    </div>

    <!-- RIGHT: Cards Scroller -->
    <div class="psc-right-cards-wrapper">
      <div class="psc-section-header">Editor's Selection</div> <!-- Add this line -->
      <button class="psc-arrow psc-left previous-btn" onclick="prevCard()"><svg xmlns="http://www.w3.org/2000/svg"
          fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg></button>
      <div class="psc-right-cards" id="cardContainer">

        <?php
        require_once __DIR__ . '/db.php';

        try {
          $stmt = $pdo->prepare("CALL product_editor_selection_list();");
          $stmt->execute();

          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $stmt->closeCursor();

        } catch (PDOException $e) {
          $msg = 'DB Error: ' . $e->getMessage();
          echo '<script>';
          echo 'console.log(' . json_encode($msg) . ');';
          echo '</script>';
          exit;
        }



        foreach ($products as $product):
          $id = $product['product_id'];
          $folder = $product['product_link'];
          $img = $product['image_path'];
          $name = $product['product_name'];
          $desc = $product['product_desc'];
          ?>
          <div class="psc-card">
            <a href="product-detail.php?id=<?= $id ?>" class="psc-card" style="text-decoration: none; color: inherit;">
              <img src="/uploads/products/<?= htmlspecialchars($folder) ?>/<?= htmlspecialchars($img) ?>"
                alt="<?= htmlspecialchars($name) ?>">
              <div class="psc-card-content">
                <h4><?= htmlspecialchars($name) ?>...</h4>
                <p><?= htmlspecialchars($desc) ?>...</p>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <button class="psc-arrow psc-right next-btn" onclick="nextCard()"><svg xmlns="http://www.w3.org/2000/svg"
          fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
          <path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path>
        </svg></button>
    </div>

  </div>

  <!-- Populer Product Ends -->
  <main class="pg-main">
    <div class="parent-container">
      <div class="product-preview">
        <div class="previous-btn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-6 h-6">
            <path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
          </svg>
        </div>
        <?php
        $card_title = 'Elektronik Ürünler';
        echo '<h2>' . $card_title . '</h2>';

        echo '<div class="pg-container">';
        include 'db.php';

        try {
          $user_id = $_SESSION['id'] ?? 0;
          $category_id = 8;
          $page_number = 1;
          $sort_mode = 'most_selled';
          $min_price_filter = null;
          $max_price_filter = null;

          $stmt = $pdo->prepare("CALL products_grid_show(
        :cat_id, :sub_ids, :ter_ids, :brand_ids,
        :user_id, :page, :sort_mode,
        :min_price, :max_price
    )");

          // All other filters are NULL
          $stmt->bindValue(':cat_id', $category_id, PDO::PARAM_INT);
          $stmt->bindValue(':sub_ids', '', PDO::PARAM_STR);
          $stmt->bindValue(':ter_ids', '', PDO::PARAM_STR);
          $stmt->bindValue(':brand_ids', '', PDO::PARAM_STR);
          $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
          $stmt->bindValue(':page', $page_number, PDO::PARAM_INT);
          $stmt->bindValue(':sort_mode', $sort_mode, PDO::PARAM_STR);
          $stmt->bindValue(':min_price', $min_price_filter, PDO::PARAM_INT);
          $stmt->bindValue(':max_price', $max_price_filter, PDO::PARAM_INT);
          $stmt->execute();

          // 1. PRODUCTS
          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // 2. IMAGES
          $stmt->nextRowset();
          $images_by_product = [];
          foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
            $images_by_product[$img['product_id']][] = $img;
          }

          // 3. SUBCATEGORIES
          $stmt->nextRowset();
          $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // 4. TERTIARIES
          $stmt->nextRowset();
          $tertiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // 5. BRANDS
          $stmt->nextRowset();
          $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

          // 6. PRICE RANGE
          $stmt->nextRowset();
          $price_range = $stmt->fetch(PDO::FETCH_ASSOC);
          $min_price = (float) ($price_range['actual_min_price'] ?? 0);
          $max_price = (float) ($price_range['actual_max_price'] ?? 1000);
          $selected_min = $min_price;
          $selected_max = $max_price;


          // Output
          if (count($products) === 0) {
            echo '<div class="no-favorites-message">Ürün yok.</div>';
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
            <path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </div>
      </div>
    </div>
  </main>
</div>

<?php
include 'footer.php';
?>