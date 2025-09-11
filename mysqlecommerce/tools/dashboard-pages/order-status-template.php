<?php
if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['id'];

try {

    $stmt = $pdo->prepare("CALL order_list(:user_id,:status,:query)");
    $q = isset($_GET['q']) ? trim($_GET['q']) : null;
    if ($q === '')
        $q = null; // empty -> NULL

    $stmt->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':status', ($status === '' ? null : $status), $status === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':query', $q, $q === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->execute();

    // #1 orders
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // #2 images
    $stmt->nextRowset();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // #3 counts (rows: status_key, cnt)
    $stmt->nextRowset();
    $countRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt->closeCursor();

    // Build images map (max 3 per order)
    $orderImages = [];
    foreach ($images as $img) {
        $oid = (int) $img['order_id'];
        $orderImages[$oid] ??= [];
        if (count($orderImages[$oid]) < 3) {
            $orderImages[$oid][] = "/uploads/products/{$img['product_link']}/{$img['image_filename']}";
        }
    }



} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<div class="order-header-bar">
    <div class="order-status-tags">
        <a href="user-dashboard.php?page=all_orders">
            <div class="status-tag all" data-count="<?= (int) ($countRows[0]['all_cnt'] ?? 0) ?>">Tümü</div>
        </a>
        <a href="user-dashboard.php?page=waiting_orders">
            <div class="status-tag waiting" data-count="<?= (int) ($countRows[0]['waiting_cnt'] ?? 0) ?>">Hazırlanıyor
            </div>
        </a>
        <a href="user-dashboard.php?page=shipped_orders">
            <div class="status-tag shipped" data-count="<?= (int) ($countRows[0]['shipped_cnt'] ?? 0) ?>">Kargoda</div>
        </a>
        <a href="user-dashboard.php?page=delivered_orders">
            <div class="status-tag delivered" data-count="<?= (int) ($countRows[0]['delivered_cnt'] ?? 0) ?>">Teslim
            </div>
        </a>
    </div>

    <div class="order-search-box">
        <input type="text" placeholder="Sipariş ara..." id="order-search-input" autocomplete="off">
    </div>
</div>


<div class="order-list">
    <div class="huge-blank"></div>
    <?php foreach ($orders as $order):
        $order_id = $order['order_id'];
        $created_at = date("d M Y", strtotime($order['created_at']));
        $total_price = number_format($order['order_total_price'], 2);
        $imgs = $orderImages[$order_id] ?? [];
        ?>
        <div class="order-item">
            <div class="order-summary" onclick="toggleDetails(this, <?= $order_id ?>)">
                <div class="summary-left">
                    <?php foreach ($imgs as $imgSrc): ?>
                        <img src="<?= htmlspecialchars($imgSrc) ?>" width="50" height="50"
                            style="object-fit: contain; border-radius: 4px; border: 1px solid #ccc; background: #f9f9f9;">
                    <?php endforeach; ?>
                </div>
                <div class="summary-center">
                    <span class="custom-message">Sipariş no: <strong><?= $order_id ?></strong></span>
                </div>
                <div class="summary-right">
                    <small class="summary-date"><?= $created_at ?></small>
                    <span class="total">₺<?= $total_price ?></span>
                    <span class="arrow">&#9660;</span>
                </div>
            </div>
            <div class="order-details">
                <div class="details-grid">
                    <!-- AJAX-filled later -->
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>