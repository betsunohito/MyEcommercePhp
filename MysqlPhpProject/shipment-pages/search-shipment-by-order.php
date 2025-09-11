<?php
session_start();
header('Content-Type: application/json');
include '../db.php';

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success' => false, 'error' => 'Not authorized']);
  exit();
}

$admin_id = $_SESSION['admin_id'];

// Get the raw POST JSON body
$data = json_decode(file_get_contents("php://input"), true);
$order_id = intval($data['order_id'] ?? 0);

$results = [];

if ($order_id > 0) {
  try {
    $stmt = $pdo->prepare("CALL seller_shipment_by_order_id(:seller_id, :order_id)");
    $stmt->bindParam(':seller_id', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    foreach ($rows as $row) {
      $shipment_id = $row['shipment_id'];

      if (!isset($results[$shipment_id])) {
        $results[$shipment_id] = [
          'shipment_id' => $row['shipment_id'],
          'order_id' => $row['order_id'],
          'shipping_company' => $row['shipping_company'],
          'tracking_number' => $row['tracking_number'],
          'order_created_at' => $row['order_created_at'],
          'shipping_status' => $row['shipping_status'],
          'products' => []
        ];
      }

      $results[$shipment_id]['products'][] = [
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'product_link' => $row['product_link'],
        'product_GTIN' => $row['product_GTIN'],
        'brand_name' => $row['brand_name'],
        'image_filename' => $row['image_filename'],
        'product_quantity' => $row['product_quantity'],
        'type_name' => $row['type_name'],
        'product_total_paid' => $row['product_total_paid']
      ];
    }

    // Return the grouped results
    echo json_encode([
      'success' => !empty($results),
      'shipments' => array_values($results)
    ]);

  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB error']);
  }
} else {
  echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
}
