<?php
include "../../db.php";
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $order_detail_id = $_POST['order_detail_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? '';

    if (!$order_detail_id || !$rating) {
        echo json_encode(["status" => "error", "message" => "Puan ve sipariş detayı zorunludur."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("CALL review_add(:user_id, :order_detail_id, :rating, :comment)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':order_detail_id', $order_detail_id, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Yorum başarıyla kaydedildi."]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Hata: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Geçersiz istek."]);
}
