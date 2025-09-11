<?php
include '../../db.php'; // Ensure $pdo is defined inside this file
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON body from fetch()
$input = json_decode(file_get_contents('php://input'), true);

// Check if input is valid
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Prepare variables
$userId = $_SESSION['id'];
$deliveryId = isset($input['deliveryId']) ? $input['deliveryId'] : null;
$billingId = isset($input['billingId']) ? $input['billingId'] : null;

// Make sure at least one address is provided
if (is_null($deliveryId) && is_null($billingId)) {
    echo json_encode(['success' => false, 'message' => 'No address selected']);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL address_user_selected(:user_id, :delivery_id, :billing_id)");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':delivery_id', $deliveryId, PDO::PARAM_INT);
    $stmt->bindParam(':billing_id', $billingId, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Address saved successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
