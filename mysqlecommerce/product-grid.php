<?php
// product-grid.php
$page_css = ["product-grid.css", "product-preview.css", "product-pre-view.css"];
$page_js = ["toast.js","product-grid.js", "scripts.js", "product-pre-view.js"];
include 'header.php';
include 'tools/action/categorynav.php';
include 'db.php';

$user_id = $_SESSION['id'] ?? 0;
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : null;
$page_number = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$q        = isset($_GET['q']) ? trim((string)$_GET['q']) : '';          // NEW: free-text search
$barcode  = isset($_GET['barcode']) ? trim((string)$_GET['barcode']) : ''; // NEW: GTIN/barcode search

$sort_mode = $_GET['sort_by'] ?? 'most_selled';
// If user didn't choose a sort and it's a search, let proc use relevance by default:
if (($q !== '' || $barcode !== '') && !isset($_GET['sort_by'])) {
    $sort_mode = '';
}

$special = isset($_GET['special']) ? trim($_GET['special']) : '';
$validSpecial = ['discounted', 'new', 'most_carted', 'most_selled', 'with_coupon'];
$isSpecial = ($special !== '' && in_array($special, $validSpecial, true));

$min_price_filter = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
$max_price_filter = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;

$selected_subs = $_GET['sub_category_ids'] ?? [];
$selected_tertiaries = $_GET['tertiary_category_ids'] ?? [];
$selected_brands = $_GET['brand_ids'] ?? [];

$sub_ids_csv = implode(',', array_map('intval', (array) $selected_subs));
$ter_ids_csv = implode(',', array_map('intval', (array) $selected_tertiaries));
$brand_ids_csv = implode(',', array_map('intval', (array) $selected_brands));

$products = $images_by_product = $subcategories = $tertiaries = $brands = [];
$min_price = $max_price = $selected_min = $selected_max = 0;

try {
    if ($q !== '' || $barcode !== '') {
        // --- SEARCH MODE (text and/or barcode) ---
        $stmt = $pdo->prepare("CALL products_grid_search_similar(
            :user_id, :page, :sort_mode, :query, :barcode,
            :cat_id, :sub_ids, :ter_ids, :brand_ids, :min_price, :max_price
        )");

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':page', $page_number, PDO::PARAM_INT);
        $stmt->bindValue(':sort_mode', $sort_mode, PDO::PARAM_STR);

        if ($q === '') {
            $stmt->bindValue(':query', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':query', $q, PDO::PARAM_STR);
        }
        if ($barcode === '') {
            $stmt->bindValue(':barcode', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':barcode', $barcode, PDO::PARAM_STR);
        }

        $stmt->bindValue(':cat_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':sub_ids', $sub_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':ter_ids', $ter_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':brand_ids', $brand_ids_csv, PDO::PARAM_STR);

        if ($min_price_filter === null) $stmt->bindValue(':min_price', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':min_price', $min_price_filter, PDO::PARAM_STR);

        if ($max_price_filter === null) $stmt->bindValue(':max_price', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':max_price', $max_price_filter, PDO::PARAM_STR);

        $stmt->execute();

    } elseif ($isSpecial) {
        // --- SPECIAL MODE (your existing proc) ---
        $stmt = $pdo->prepare("CALL products_grid_show_special(
            :user_id, :page, :sort_mode, :special_mode,
            :cat_id, :sub_ids, :ter_ids, :brand_ids, :min_price, :max_price
        )");

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':page', $page_number, PDO::PARAM_INT);
        $stmt->bindValue(':sort_mode', $sort_mode, PDO::PARAM_STR);
        $stmt->bindValue(':special_mode', $special, PDO::PARAM_STR);

        $stmt->bindValue(':cat_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':sub_ids', $sub_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':ter_ids', $ter_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':brand_ids', $brand_ids_csv, PDO::PARAM_STR);

        if ($min_price_filter === null) $stmt->bindValue(':min_price', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':min_price', $min_price_filter, PDO::PARAM_STR);

        if ($max_price_filter === null) $stmt->bindValue(':max_price', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':max_price', $max_price_filter, PDO::PARAM_STR);

        $stmt->execute();

    } else {
        // --- NORMAL MODE (your existing proc) ---
        $stmt = $pdo->prepare("CALL products_grid_show(
            :cat_id, :sub_ids, :ter_ids, :brand_ids,
            :user_id, :page, :sort_mode,
            :min_price, :max_price
        )");
        $stmt->bindValue(':cat_id', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':sub_ids', $sub_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':ter_ids', $ter_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':brand_ids', $brand_ids_csv, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':page', $page_number, PDO::PARAM_INT);
        $stmt->bindValue(':sort_mode', $sort_mode, PDO::PARAM_STR);

        if ($min_price_filter === null) $stmt->bindValue(':min_price', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':min_price', $min_price_filter, PDO::PARAM_STR);

        if ($max_price_filter === null) $stmt->bindValue(':max_price', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':max_price', $max_price_filter, PDO::PARAM_STR);

        $stmt->execute();
    }

    // 1) PRODUCTS
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) IMAGES
    $stmt->nextRowset();
    $images_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $images_by_product = [];
    foreach ($images_data as $img) {
        $images_by_product[$img['product_id']][] = $img;
    }

    // 3) SUBCATEGORIES
    $stmt->nextRowset();
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4) TERTIARIES
    $stmt->nextRowset();
    $tertiaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5) BRANDS
    $stmt->nextRowset();
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6) MIN/MAX
    $stmt->nextRowset();
    $price_range = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $min_price = (float) ($price_range['actual_min_price'] ?? 0);
    $max_price = (float) ($price_range['actual_max_price'] ?? 0);
    $selected_min = ($min_price_filter !== null) ? $min_price_filter : $min_price;
    $selected_max = ($max_price_filter !== null) ? $max_price_filter : $max_price;

    $stmt->closeCursor();
} catch (PDOException $e) {
    echo "Database error: " . htmlspecialchars($e->getMessage());
    $products = $images_by_product = $subcategories = $tertiaries = $brands = [];
    $min_price = 0;
    $max_price = 1000;
    $selected_min = 0;
    $selected_max = 1000;
}

// Route to the correct view (don’t include header/footer inside partials)
include __DIR__ . '/tools/grid-pages/normal-grid.php';

include 'footer.php';
