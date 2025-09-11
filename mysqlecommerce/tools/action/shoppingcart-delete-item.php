<?php
include '../../db.php';
session_start();

$p_user_id = $_SESSION['id'] ?? 0;
$p_product_id = $_POST['product_id'] ?? 0;
$p_type_id = $_POST['type_id'] ?? 0;

if ($p_user_id > 0 && $p_product_id > 0 && is_numeric($p_type_id)) {
    try {
        $stmt = $pdo->prepare("CALL shoppingcart_item_delete(:user_id, :product_id, :type_id)");
        $stmt->bindParam(':user_id', $p_user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $p_product_id, PDO::PARAM_INT);
        $stmt->bindParam(':type_id', $p_type_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC); // Get success + coupon_removed
        $stmt->closeCursor();

        echo json_encode([
            'success' => $result['success'],
            'coupon_removed' => $result['coupon_removed']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting item.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}
