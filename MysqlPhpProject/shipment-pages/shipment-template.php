<?php
session_start();
include '../db.php';
include '../base-link.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

$admin_id = $_SESSION['admin_id'];
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Call stored procedure with pagination
$stmt = $pdo->prepare("CALL seller_shipments_by_status(:seller_id, :status, :page, :page_size)");
$stmt->bindValue(':seller_id', $admin_id, PDO::PARAM_INT);
$stmt->bindValue(':status', $status, PDO::PARAM_STR);
$stmt->bindValue(':page', $page, PDO::PARAM_INT);
$stmt->bindValue(':page_size', $limit, PDO::PARAM_INT);
$stmt->execute();

// Fetch main shipment data
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->nextRowset(); // move to next result set
$totalShipments = $stmt->fetchColumn(); // get count
$totalPages = ceil($totalShipments / $limit);
$stmt->closeCursor();

// Group products by shipment ID
$grouped_orders = [];
foreach ($orders as $order) {
  $shipment_id = $order['shipment_id'];

  if (!isset($grouped_orders[$shipment_id])) {
    $grouped_orders[$shipment_id] = [
      'shipment_id' => $order['shipment_id'],
      'order_id' => $order['order_id'],
      'shipping_company' => $order['shipping_company'],
      'order_created_at' => $order['order_created_at'],
      'shipping_status' => $order['shipping_status'],
      'products' => []
    ];
  }

  $grouped_orders[$shipment_id]['products'][] = [
    'product_id' => $order['product_id'],
    'product_name' => $order['product_name'],
    'product_link' => $order['product_link'],
    'product_GTIN' => $order['product_GTIN'],
    'brand_name' => $order['brand_name'],
    'image_filename' => $order['image_filename'],
    'product_quantity' => $order['product_quantity'],
    'type_name' => $order['type_name'],
    'product_total_paid' => $order['product_total_paid']
  ];
}

$page_js = ["shipment.js"];
$page_css = ["shipment-list.css"];
include '../header.php';
?>

<div class="shipment-list-page">
  <div class="back-btn">
    <a href="../shipment.php" title="Back to Dashboard">&larr; Back to Dashboard</a>
  </div>
  <h2><?= ucfirst($status) ?> Shipments</h2>

  <?php if (empty($grouped_orders)): ?>
    <p>No <?= $status ?> shipments found.</p>
  <?php else: ?>
    <div class="shipment-grid">
      <?php foreach ($grouped_orders as $group): ?>
        <div class="shipment-card">
          <h4>Order ID: #<?= htmlspecialchars($group['order_id']) ?></h4>
          <p>Shipping Company: <?= htmlspecialchars($group['shipping_company'] ?? '—') ?></p>
          <p>Date: <?= date('d M Y', strtotime($group['order_created_at'])) ?></p>

          <?php foreach ($group['products'] as $product): ?>
            <hr>
            <p><strong><?= htmlspecialchars($product['brand_name']) ?> -
                <?= htmlspecialchars($product['product_name']) ?></strong></p>
            <p>GTIN: <?= htmlspecialchars($product['product_GTIN']) ?></p>
            <p>Quantity: <?= $product['product_quantity'] ?></p>
            <?php if (!empty($product['type_name'])): ?>
              <p class="type-label">Type: <?= htmlspecialchars($product['type_name']) ?></p>
            <?php endif; ?>
            <p>Total: ₺<?= number_format($product['product_total_paid'], 2) ?></p>
            <?php if (!empty($product['image_filename'])): ?>
              <img
                src="/uploads/products/<?= htmlspecialchars($product['product_link']) ?>/<?= htmlspecialchars($product['image_filename']) ?>"
                alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-thumb">
            <?php else: ?>
              <div class="product-thumb placeholder">No Image</div>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php if ($group['shipping_status'] === 'waiting' || $group['shipping_status'] === 'shipped'): ?>
            <button class="simulate-btn" data-shipment-id="<?= $group['shipment_id'] ?>"
              data-current-status="<?= $group['shipping_status'] ?>" title="Simulates the next status transition">
              Simulate
            </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 0): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
        <?php endif; ?>

        <span>Page <?= $page ?> of <?= $totalPages ?></span>

        <?php if ($page < $totalPages): ?>
          <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
