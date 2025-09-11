<?php
include __DIR__ . '/../../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    if (defined('INCLUDE_MODE')) {
        return ['success' => false, 'message' => 'User not logged in'];
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
}

$user_id = (int) $_SESSION['id'];
$response = [
    'delivery_address' => [],
    'billing_address' => []
];

try {
    // Call the stored procedure
    $stmt = $pdo->prepare("CALL user_selected_address(:user_id)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // First result set: delivery address
    $response['delivery_address'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Move to second result set: billing address
    if ($stmt->nextRowset()) {
        $response['billing_address'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt->closeCursor();

    // ✅ If included, return array; if called directly, echo JSON
    if (defined('INCLUDE_MODE')) {
        return $response;
    } else {
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

} catch (PDOException $e) {
    if (defined('INCLUDE_MODE')) {
        return ['error' => 'Failed to execute procedure.'];
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to execute procedure.']);
        error_log('PDO error: ' . $e->getMessage());
        exit;
    }
}
