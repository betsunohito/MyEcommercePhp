<?php
include '../../db.php';

header('Content-Type: application/json');

$product_id = $_GET['product_id'] ?? null;

try {
    $stmt = $pdo->prepare("CALL get_product_reviews(:product_id)");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($reviews);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
