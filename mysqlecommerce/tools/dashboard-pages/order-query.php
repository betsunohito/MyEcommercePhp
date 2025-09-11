<?php
// NO whitespace/BOM before this line!
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
  http_response_code(401);
  echo json_encode(['error' => true, 'message' => 'Login required']);
  exit;
}

require_once __DIR__ . '/../../db.php'; // adjust path if needed

try {
  $user_id = (int)$_SESSION['id'];
  $status  = isset($_GET['status']) ? trim($_GET['status']) : null;
  if ($status === '') $status = null;

  // ---- ONLY APPLY QUERY WHEN LENGTH >= 3 ----
  $qRaw = isset($_GET['q']) ? trim($_GET['q']) : '';
  $len  = function_exists('mb_strlen') ? mb_strlen($qRaw, 'UTF-8') : strlen($qRaw);
  $q    = ($qRaw !== '' && $len >= 3) ? $qRaw : null;

  $stmt = $pdo->prepare("CALL order_list(:user_id, :status, :query)");
  $stmt->execute([
    ':user_id' => $user_id,
    ':status'  => $status,
    ':query'   => $q
  ]);

  // 1) main orders
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 2) images
  $stmt->nextRowset();
  $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 3) counts
  $stmt->nextRowset();
  $counts = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

  $stmt->closeCursor();

  // Build order_id => [img urls...], cap 3 like your UI
  $orderImages = [];
  foreach ($images as $row) {
    $oid = (int)$row['order_id'];
    $src = "/uploads/products/{$row['product_link']}/{$row['image_filename']}";
    if (!isset($orderImages[$oid])) $orderImages[$oid] = [];
    if (count($orderImages[$oid]) < 3) $orderImages[$oid][] = $src;
  }

  // Normalize output
  $outOrders = [];
  foreach ($orders as $o) {
    $oid = (int)$o['order_id'];
    $outOrders[] = [
      'order_id'           => $oid,
      'created_at'         => $o['created_at'],
      'order_total_price'  => (float)$o['order_total_price'],
      'shipping_status'    => $o['shipping_status'],
      'images'             => $orderImages[$oid] ?? []
    ];
  }

  echo json_encode([
    'status' => 'success',
    'orders' => $outOrders,
    'counts' => [
      'all'       => (int)($counts['all_cnt'] ?? 0),
      'waiting'   => (int)($counts['waiting_cnt'] ?? 0),
      'shipped'   => (int)($counts['shipped_cnt'] ?? 0),
      'delivered' => (int)($counts['delivered_cnt'] ?? 0),
    ],
    // Helpful meta if you want to show a hint in UI
    'meta' => [
      'query_applied' => $q !== null,
      'min_chars'     => 3,
    ]
  ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => true, 'message' => 'DB Error: '.$e->getMessage()]);
}
