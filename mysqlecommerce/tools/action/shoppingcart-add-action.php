<?php
include '../../db.php'; // Make sure $pdo is defined inside
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['product_id']) &&
    isset($_POST['quantity']) &&
    isset($_POST['seller_id']) &&
    isset($_POST['type_id'])
) {
    $user_id = $_SESSION['id'];
    $product_id = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity'])); // Ensure quantity >= 1
    $seller_id = intval($_POST['seller_id']);
    $type_id = intval($_POST['type_id']);

    try {
        $stmt = $pdo->prepare("CALL shoppingcart_add(:user_id, :product_id, :quantity, :seller_id, :type_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
        $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);

        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
