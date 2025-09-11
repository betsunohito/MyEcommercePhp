<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

$page_css = ["product_list.css"];
$page_js  = ["product-list.js"];
include 'header.php';

$admin_id = (int)$_SESSION['admin_id'];

/* ---------------- Pagination params ---------------- */
$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = max(1, (int)($_GET['size'] ?? 12)); // allow ?size=...

try {
  require_once __DIR__ . '/db.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("CALL products_admin_show(:uid, :p, :s)");
  $stmt->execute([
    ':uid' => $admin_id,
    ':p'   => $page,
    ':s'   => $pageSize
  ]);

  // 1) total count
  $totalRow  = $stmt->fetch(PDO::FETCH_ASSOC);
  $totalRows = (int)($totalRow['total_rows'] ?? 0);

  // move to next result set
  $stmt->nextRowset();

  // 2) current page data
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Drain any additional result sets and close cursor (important for CALL)
  while ($stmt->nextRowset()) { /* no-op */ }
  $stmt->closeCursor();

  $totalPages = max(1, (int)ceil($totalRows / $pageSize));
} catch (PDOException $e) {
  echo "Database error: " . htmlspecialchars($e->getMessage());
  $products   = [];
  $totalRows  = 0;
  $totalPages = 1;
}

/* ---------------- Helpers ---------------- */
function pageUrl($p) {
  $qs = $_GET;
  $qs['page'] = $p;
  if (!isset($qs['size'])) {
    $qs['size'] = max(1, (int)($_GET['size'] ?? 12));
  }
  return htmlspecialchars('?' . http_build_query($qs));
}

/* compact numeric pagination window */
function renderPagination($current, $total) {
  if ($total <= 1) return '';
  $html = '<nav class="pag-nav" aria-label="Pagination"><ul class="pagination">';

  // Prev
  $prevDisabled = ($current <= 1) ? ' disabled' : '';
  $html .= '<li class="page-item'.$prevDisabled.'"><a class="page-link" href="'.pageUrl(max(1,$current-1)).'">&laquo;</a></li>';

  // window: first, left ellipsis, window, right ellipsis, last
  $window = 2; // pages around current
  $start  = max(1, $current - $window);
  $end    = min($total, $current + $window);

  if ($start > 1) {
    $html .= '<li class="page-item"><a class="page-link" href="'.pageUrl(1).'">1</a></li>';
    if ($start > 2) $html .= '<li class="page-item ellipsis"><span>…</span></li>';
  }

  for ($i = $start; $i <= $end; $i++) {
    $active = ($i === $current) ? ' active' : '';
    $html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.pageUrl($i).'">'.$i.'</a></li>';
  }

  if ($end < $total) {
    if ($end < $total - 1) $html .= '<li class="page-item ellipsis"><span>…</span></li>';
    $html .= '<li class="page-item"><a class="page-link" href="'.pageUrl($total).'">'.$total.'</a></li>';
  }

  // Next
  $nextDisabled = ($current >= $total) ? ' disabled' : '';
  $html .= '<li class="page-item'.$nextDisabled.'"><a class="page-link" href="'.pageUrl(min($total,$current+1)).'">&raquo;</a></li>';

  $html .= '</ul></nav>';
  return $html;
}
?>

<div class="col-lg-12 grid-margin stretch-card">
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Users Product Table</h4>

      <!-- Optional: Page size selector -->
      <form method="get" class="page-size-form" style="margin-bottom:10px;">
        <?php foreach($_GET as $k=>$v){ if($k==='size'||$k==='page') continue; ?>
          <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars((string)$v) ?>">
        <?php } ?>
        <label>Page size:
          <select name="size" onchange="this.form.submit()">
            <?php foreach([12,24,48,96] as $opt): ?>
              <option value="<?= $opt ?>" <?= $opt===$pageSize?'selected':'' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </form>

      <div class="table-responsive">
        <table id="admin-product-table" class="table table-striped">
          <thead>
            <tr>
              <th>Image</th>
              <th>Title</th>
              <th>Rating</th>
              <th>Type</th>
              <th>Quantity</th>
              <th>Discounted Price</th>
              <th>Original Price</th>
              <th>Updated</th>
              <th>Created</th>
            </tr>
          </thead>

          <tbody>
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $row): ?>
              <?php
                $pid        = (int)($row['product_id'] ?? 0);
                $ptype      = isset($row['product_type']) ? (int)$row['product_type'] : 0;

                $img        = '../uploads/products/' . ($row['product_link'] ?? '') . '/' . ($row['image_filename'] ?? '');
                $name       = (string)($row['product_name'] ?? '');

                $avg        = (float)($row['average_rating'] ?? 0);
                $bar        = max(0, min(100, $avg * 20));

                // normalize numeric fields to plain numbers
                $price      = is_numeric($row['product_price'] ?? null) ? (0 + $row['product_price']) : '';
                $qty        = is_numeric($row['product_quantity'] ?? null) ? (int)$row['product_quantity'] : '';
                $oldPrice   = is_numeric($row['product_discount_price'] ?? null) ? (0 + $row['product_discount_price']) : '';

                $updated_at = isset($row['product_updated_at']) ? date("F d, Y H:i", strtotime($row['product_updated_at'])) : '';
                $created_at = isset($row['product_created_at']) ? date("F d, Y",     strtotime($row['product_created_at'])) : '';
              ?>
              <tr>
                <td class="tdoftable">
                  <img src="<?= htmlspecialchars($img) ?>"
                       alt="<?= htmlspecialchars(($name ?: 'Product') . ' image') ?>"
                       class="imagoftable"
                       onerror="this.src='https://picsum.photos/80/60?random=<?= $pid ?>'"/>
                </td>

                <td class="product-name-cell" title="<?= htmlspecialchars($name) ?>">
                  <?= htmlspecialchars(mb_strimwidth($name, 0, 70, '...')) ?>
                </td>

                <td>
                  <div class="text-center mb-1">
                    <?= number_format($avg, 1) ?> / 5
                  </div>
                  <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $bar ?>%"></div>
                  </div>
                </td>

                <td><?= htmlspecialchars($row['type_name'] ?? '—') ?></td>

                <!-- Quantity -->
                <td class="editable-cell">
                  <input
                    type="number"
                    id="quantity-input-<?= $pid ?>"
                    class="quantity-input"
                    value="<?= htmlspecialchars((string)$qty) ?>"
                    min="0"
                  />
                  <button
                    class="apply-btn quantity-btn"
                    data-product-id="<?= $pid ?>"
                    data-product-type="<?= $ptype ?>"
                    style="display:none;"
                  >Apply</button>
                </td>

                <!-- Discounted Price (customer pays / active price) -->
                <td class="editable-cell">
                  <input
                    type="number"
                    id="price-input-<?= $pid ?>"
                    class="price-input"
                    value="<?= htmlspecialchars((string)$price) ?>"
                    min="0" step="0.01"
                    title="Customer pays"
                  />
                  <button
                    class="apply-btn price-btn"
                    data-product-id="<?= $pid ?>"
                    data-product-type="<?= $ptype ?>"
                    style="display:none;"
                  >Apply</button>
                  <br><small class="text-muted">Customer pays</small>
                </td>

                <!-- Original Price (crossed-out) -->
                <td class="editable-cell original-cell">
                  <input
                    type="number"
                    id="original-input-<?= $pid ?>"
                    class="original-input"
                    value="<?= htmlspecialchars((string)$oldPrice) ?>"
                    placeholder="Original Price"
                    min="0" step="0.01"
                    title="Old crossed-out price"
                  />
                  <button
                    class="apply-btn original-btn"
                    data-product-id="<?= $pid ?>"
                    data-product-type="<?= $ptype ?>"
                    style="display:none;"
                  >Apply</button>
                  <br><small class="text-muted">Old visual price (0 to clear)</small>
                </td>

                <td class="status-cell"><?= htmlspecialchars($updated_at ?: '—') ?></td>
                <td><?= htmlspecialchars($created_at) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="9">No products found.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-wrapper">
        <?= renderPagination($page, $totalPages) ?>
        <div class="pagination-meta">
          <?php
            $from = ($totalRows === 0) ? 0 : (($page - 1) * $pageSize + 1);
            $to   = min($totalRows, $page * $pageSize);
          ?>
          <span>Showing <?= $from ?>–<?= $to ?> of <?= $totalRows ?></span>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>