<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

require_once '../../db.php';

$user_id = $_SESSION['id'];
$coupon_id = intval($_POST['coupon_id'] ?? 0);

if ($coupon_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon']);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL coupon_add_to_user_storage(:user_id, :coupon_id)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':coupon_id', $coupon_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
}
