<div class="shipment-card">
  <h4>Order ID: #<?= htmlspecialchars($order['order_id']) ?></h4>
  <p>Shipping Company: <?= htmlspecialchars($order['shipping_company'] ?? '—') ?></p>
  <p>Total: ₺<?= number_format($order['product_total_paid'], 2) ?></p>
  <p>Date: <?= date('d M Y', strtotime($order['order_created_at'])) ?></p>
  <p>GTIN: <?= htmlspecialchars($order['product_GTIN'] ?? '—') ?></p>

  <!-- Quantity -->
  <?php
    $qty = (int) ($order['product_quantity'] ?? 0);
    $qtyClass = $qty > 1 ? 'quantity-visible' : '';
  ?>
  <p class="<?= $qtyClass ?>">Quantity: <?= $qty ?></p>

  <!-- Type -->
  <?php if (!empty($order['type_name'])): ?>
    <p class="type-label">Type: <?= htmlspecialchars($order['type_name']) ?></p>
  <?php endif; ?>

<?php if ($order['shipping_status'] === 'waiting'||$order['shipping_status'] === 'shipped'): ?>
  <button class="simulate-btn"
          data-shipment-id="<?= $order['shipment_id'] ?>"
          data-current-status="<?= $order['shipping_status'] ?>"
          title="Simulates the next status transition">
    Simulate
  </button>
<?php endif; ?>


  <!-- Product Info -->
  <div class="product-inline">
    <?php if (!empty($order['image_filename'])): ?>
      <img
        src="/uploads/products/<?= htmlspecialchars($order['product_link']) ?>/<?= htmlspecialchars($order['image_filename']) ?>"
        alt="<?= htmlspecialchars($order['product_name']) ?>" class="product-thumb">
    <?php else: ?>
      <div class="product-thumb placeholder">No Image</div>
    <?php endif; ?>

    <div class="product-details">
      <span class="product-name">
        <?= htmlspecialchars($order['brand_name']) ?>
        <?= htmlspecialchars($order['product_name']) ?>
      </span>
    </div>
  </div>
</div>
