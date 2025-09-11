<?php
include '../db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$shipment_id = intval($input['shipment_id'] ?? 0);
$current_status = $input['current_status'] ?? '';

if (!$shipment_id || !in_array($current_status, ['waiting', 'shipped'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid shipment ID or status.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("CALL simulate_shipment_status(:shipment_id)");
    $stmt->bindValue(':shipment_id', $shipment_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
