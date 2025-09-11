<?php
include '../../db.php'; // Should define $pdo (PDO object)
session_start();

header('Content-Type: application/json');

// 1) Auth check
if (!isset($_SESSION['id'])) {
    echo json_encode(['success'=>false,'message'=>'User not logged in']);
    exit;
}
$userId = (int)$_SESSION['id'];

// 2) Input check
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['address_id'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid input: address_id required']);
    exit;
}
$addressId = (int)$input['address_id'];

try {
    // 3) Call stored procedure using PDO
    $stmt = $pdo->prepare("CALL user_address_show(:user_id, :address_id)");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':address_id', $addressId, PDO::PARAM_INT);
    $stmt->execute();

    // 4) Fetch result
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!$address) {
        echo json_encode(['success'=>false,'message'=>'Address not found']);
    } else {
        echo json_encode(['success'=>true,'data'=>$address]);
    }

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    echo json_encode(['success'=>false,'message'=>'Server error']);
}
