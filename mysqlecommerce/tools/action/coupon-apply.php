<?php
// tools/action/coupon-apply.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once '../../db.php';

$user_id = $_SESSION['id'] ?? 0;
if (!$user_id) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Not authenticated']);
  exit;
}

// 1) Read form-encoded first
$coupon_id   = isset($_POST['coupon_id'])   ? (int)$_POST['coupon_id'] : 0;
$coupon_code = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';

// 2) If still empty, optionally read JSON body (in case client sends JSON)
if ($coupon_id === 0 && $coupon_code === '') {
  $raw = file_get_contents('php://input');
  if ($raw) {
    $data = json_decode($raw, true);
    if (is_array($data)) {
      if (isset($data['coupon_id']))   $coupon_id   = (int)$data['coupon_id'];
      if (isset($data['coupon_code'])) $coupon_code = trim($data['coupon_code']);
    }
  }
}

// 3) Require at least one of them
if ($coupon_id === 0 && $coupon_code === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'coupon_id or coupon_code is required']);
  exit;
}

try {
  // If you pass code, send NULL for p_coupon_id so SP resolves it.
  // If you pass id, you can pass empty string for p_coupon_code (SP will prefer id).
  $stmt = $pdo->prepare("CALL coupon_active_set(:uid, :code, :cid)");
  $stmt->bindValue(':uid',  $user_id,                PDO::PARAM_INT);
  $stmt->bindValue(':code', $coupon_code ?: null,    PDO::PARAM_STR);
  // Use NULL for cid when no id provided
  if ($coupon_id > 0) {
    $stmt->bindValue(':cid', $coupon_id, PDO::PARAM_INT);
  } else {
    $stmt->bindValue(':cid', null, PDO::PARAM_NULL);
  }

  $stmt->execute();

  // Your SP does: SELECT v_message AS message;  (optionally add success flag)
  $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  while ($stmt->nextRowset()) { /* drain */ }

  $message = $row['message'] ?? 'Coupon processed.';
  $success = (stripos($message, 'applied') !== false) || (strpos($message, '✅') !== false);

  echo json_encode(['success' => $success, 'message' => $message]);

} catch (PDOException $e) {
  // While debugging: expose message. In prod, keep it generic.
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid or unavailable coupon']);
}
