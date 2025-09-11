<?php
session_start();
header('Content-Type: application/json');
require '../db.php'; // assumes $pdo is defined


$productId = $_POST['product_id'] ?? null;
$adminId = $_SESSION['admin_id'] ?? 0;
$price = $_POST['product_price'] ?? null;
$quantity = $_POST['product_quantity'] ?? null;
$typeId = $_POST['product_type'] ?? null;

error_reporting(0);
ini_set('display_errors', 0);


if (!$productId || !$adminId || $price === null || $price === '' || $quantity === null || $quantity === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . 
            'productId=' . var_export($productId, true) . ', ' .
            'adminId=' . var_export($adminId, true) . ', ' .
            'price=' . var_export($price, true) . ', ' .
            'quantity=' . var_export($quantity, true) . ', ' .
            'typeId=' . var_export($typeId, true)
    ]);
    exit;
}



try {
    $stmt = $pdo->prepare("CALL add_product_price(?, ?, ?, ?, ?)");
    $stmt->execute([
        $productId,
        $adminId,
        $price,
        $quantity,
        $typeId ?: null // use NULL if empty string
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->errorInfo[2] ?? 'Database error'
    ]);
}
?>