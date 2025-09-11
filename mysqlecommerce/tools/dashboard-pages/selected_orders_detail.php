<?php
// selectedOrderDetail.php

header('Content-Type: application/json; charset=utf-8');
session_start();

// 1) Verify user is logged in
if (empty($_SESSION['id'])) {
    echo json_encode([
        "status"  => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

// 2) Verify POST and order_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['order_id'])) {
    echo json_encode([
        "status"  => "error",
        "message" => "Invalid request or missing order_id"
    ]);
    exit;
}

$user_id  = (int) $_SESSION['id'];
$order_id = (int) $_POST['order_id'];

// 3) Include your existing connection
//    db.php must define $pdo (no debug echoes!)
include __DIR__ . '/../../db.php';

// 4) Call stored procedure and collect both result sets
try {
    $stmt = $pdo->prepare("CALL user_order_detail(?, ?)");
    $stmt->bindValue(1, $user_id,  PDO::PARAM_INT);
    $stmt->bindValue(2, $order_id, PDO::PARAM_INT);
    $stmt->execute();

    $order_data = [];

    // First result set: products
    $order_data[] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Next result set: order info/details
    if ($stmt->nextRowset()) {
        $order_data[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $stmt->closeCursor();

    // 5) Return JSON
    echo json_encode([
        "status"   => "success",
        "products" => $order_data[0] ?? [],
        "order"    => $order_data[1] ?? []
    ]);

} catch (PDOException $e) {
    // Log internally, but return a generic error
    error_log("user_order_list_detail error: " . $e->getMessage());
    echo json_encode([
        "status"  => "error",
        "message" => "Failed to fetch order details"
    ]);
}
